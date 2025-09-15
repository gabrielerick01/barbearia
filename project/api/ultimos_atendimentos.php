<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$barbeiro_id = $_GET['barbeiro_id'] ?? null;

$query = "SELECT r.data_atendimento, r.valor_total,
                 c.nome_completo as cliente_nome,
                 b.nome as barbeiro_nome,
                 GROUP_CONCAT(s.nome SEPARATOR ', ') as servicos
          FROM receitas r
          LEFT JOIN clientes c ON r.cliente_id = c.id
          LEFT JOIN barbeiros b ON r.barbeiro_id = b.id
          LEFT JOIN receita_servicos rs ON r.id = rs.receita_id
          LEFT JOIN servicos s ON rs.servico_id = s.id
          GROUP BY r.id
if ($barbeiro_id) {
    $query .= " WHERE r.barbeiro_id = :barbeiro_id";
}

$query .= " GROUP BY r.id
          ORDER BY r.data_atendimento DESC
          LIMIT 10";

$stmt = $db->prepare($query);
if ($barbeiro_id) {
    $stmt->bindParam(':barbeiro_id', $barbeiro_id);
}
$stmt->execute();

$atendimentos = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $atendimentos[] = array(
        "data_atendimento" => $row['data_atendimento'],
        "cliente_nome" => $row['cliente_nome'],
        "barbeiro_nome" => $row['barbeiro_nome'],
        "servicos" => $row['servicos'],
        "valor_total" => floatval($row['valor_total'])
    );
}

echo json_encode($atendimentos);
?>