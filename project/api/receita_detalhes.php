<?php
require_once '../config/database.php';
require_once '../includes/Receita.php';

if (isset($_GET['id'])) {
    $database = new Database();
    $db = $database->getConnection();
    $receita = new Receita($db);
    
    $receita->id = $_GET['id'];
    $dados = $receita->readOne();
    $servicos = $receita->getServicos();
    
    if ($dados) {
        echo "<div class='mb-3'>";
        echo "<strong>Cliente:</strong> " . $dados['cliente_nome'] . "<br>";
        echo "<strong>Barbeiro:</strong> " . $dados['barbeiro_nome'] . "<br>";
        echo "<strong>Data/Hora:</strong> " . date('d/m/Y H:i', strtotime($dados['data_atendimento'])) . "<br>";
        echo "<strong>Valor Total:</strong> R$ " . number_format($dados['valor_total'], 2, ',', '.') . "<br>";
        echo "</div>";
        
        echo "<div class='mb-3'>";
        echo "<strong>Serviços:</strong>";
        echo "<ul class='list-group list-group-flush mt-2'>";
        while ($servico = $servicos->fetch(PDO::FETCH_ASSOC)) {
            echo "<li class='list-group-item d-flex justify-content-between align-items-center'>";
            echo $servico['servico_nome'];
            echo "<span class='badge bg-primary rounded-pill'>R$ " . number_format($servico['preco_unitario'], 2, ',', '.') . "</span>";
            echo "</li>";
        }
        echo "</ul>";
        echo "</div>";
        
        if ($dados['observacoes']) {
            echo "<div class='mb-3'>";
            echo "<strong>Observações:</strong><br>";
            echo nl2br(htmlspecialchars($dados['observacoes']));
            echo "</div>";
        }
    } else {
        echo "<p>Atendimento não encontrado.</p>";
    }
} else {
    echo "<p>ID do atendimento não informado.</p>";
}
?>