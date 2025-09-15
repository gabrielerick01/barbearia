<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$barbeiro_id = $_GET['barbeiro_id'] ?? null;

// Receita do mês
$query_receita = "SELECT COALESCE(SUM(valor_total), 0) as total
                  FROM receitas
                  WHERE YEAR(data_atendimento) = YEAR(CURDATE()) 
                  AND MONTH(data_atendimento) = MONTH(CURDATE())";

if ($barbeiro_id) {
    $query_receita .= " AND barbeiro_id = :barbeiro_id";
}

$stmt_receita = $db->prepare($query_receita);
if ($barbeiro_id) {
    $stmt_receita->bindParam(':barbeiro_id', $barbeiro_id);
}
$stmt_receita->execute();
$receita_mes = $stmt_receita->fetch(PDO::FETCH_ASSOC)['total'];

// Atendimentos hoje
$query_atendimentos = "SELECT COUNT(*) as total
                       FROM receitas
                       WHERE DATE(data_atendimento) = CURDATE()";

if ($barbeiro_id) {
    $query_atendimentos .= " AND barbeiro_id = :barbeiro_id";
}

$stmt_atendimentos = $db->prepare($query_atendimentos);
if ($barbeiro_id) {
    $stmt_atendimentos->bindParam(':barbeiro_id', $barbeiro_id);
}
$stmt_atendimentos->execute();
$atendimentos_hoje = $stmt_atendimentos->fetch(PDO::FETCH_ASSOC)['total'];

// Despesas do mês
$query_despesas = "SELECT COALESCE(SUM(valor), 0) as total
                   FROM despesas
                   WHERE YEAR(data_despesa) = YEAR(CURDATE()) 
                   AND MONTH(data_despesa) = MONTH(CURDATE())";

if ($barbeiro_id) {
    $query_despesas .= " AND (barbeiro_id = :barbeiro_id OR barbeiro_id IS NULL)";
}

$stmt_despesas = $db->prepare($query_despesas);
if ($barbeiro_id) {
    $stmt_despesas->bindParam(':barbeiro_id', $barbeiro_id);
}
$stmt_despesas->execute();
$despesas_mes = $stmt_despesas->fetch(PDO::FETCH_ASSOC)['total'];

$data = array(
    "receita_hoje" => floatval($receita_mes),
    "atendimentos_hoje" => intval($atendimentos_hoje),
    "despesas_hoje" => floatval($despesas_mes)
);

echo json_encode($data);
?>