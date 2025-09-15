<?php
require_once '../auth/check_auth.php';
checkAuthentication();

require_once(__DIR__ . '/../config/database.php');
require_once '../includes/Receita.php';
require_once '../includes/Cliente.php';
require_once '../includes/Servico.php';
require_once '../includes/Barbeiro.php';

$database = new Database();
$db = $database->getConnection();
$receita = new Receita($db);
$cliente = new Cliente($db);
$servico = new Servico($db);
$barbeiro = new Barbeiro($db);

// Processar novo atendimento
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'create') {
    $receita->cliente_id = $_POST['cliente_id'];
    $receita->barbeiro_id = $_POST['barbeiro_id'];
    $receita->observacoes = $_POST['observacoes'];
    $receita->data_atendimento = $_POST['data_atendimento'] . ' ' . $_POST['hora_atendimento'];
    
    $valor_total = 0;
    $servicos_selecionados = $_POST['servicos'] ?? [];
    
    // Calcular valor total
    foreach ($servicos_selecionados as $servico_id) {
        $servico->id = $servico_id;
        $servico->readOne();
        $valor_total += $servico->preco;
    }
    
    $receita->valor_total = $valor_total;
    
    if ($receita->create()) {
        // Adicionar serviços
        foreach ($servicos_selecionados as $servico_id) {
            $servico->id = $servico_id;
            $servico->readOne();
            $receita->addServico($servico_id, 1, $servico->preco);
        }
        
        $message = "Atendimento registrado com sucesso!";
        $message_type = "success";
    } else {
        $message = "Erro ao registrar atendimento.";
        $message_type = "danger";
    }
}

$stmt_receitas = $receita->readAll();
$stmt_clientes = $cliente->readAll();
$stmt_servicos = $servico->readAll();
$stmt_barbeiros = $barbeiro->readAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atendimentos - Sistema Barbearia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Atendimentos</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#receitaModal">
                        <i class="fas fa-plus me-2"></i>Novo Atendimento
                    </button>
                </div>

                <?php if (isset($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Data/Hora</th>
                                        <th>Cliente</th>
                                        <th>Barbeiro</th>
                                        <th>Valor Total</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($row = $stmt_receitas->fetch(PDO::FETCH_ASSOC)) {
                                        extract($row);
                                        echo "<tr>";
                                        echo "<td>{$id}</td>";
                                        echo "<td>" . date('d/m/Y H:i', strtotime($data_atendimento)) . "</td>";
                                        echo "<td>{$cliente_nome}</td>";
                                        echo "<td>{$barbeiro_nome}</td>";
                                        echo "<td>R$ " . number_format($valor_total, 2, ',', '.') . "</td>";
                                        echo "<td>";
                                        echo "<button class='btn btn-sm btn-outline-info' onclick='verDetalhes({$id})'>";
                                        echo "<i class='fas fa-eye'></i>";
                                        echo "</button>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Novo Atendimento -->
    <div class="modal fade" id="receitaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Novo Atendimento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cliente_id" class="form-label">Cliente *</label>
                                    <select class="form-select" id="cliente_id" name="cliente_id" required>
                                        <option value="">Selecione um cliente</option>
                                        <?php
                                        while ($row = $stmt_clientes->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<option value='{$row['id']}'>{$row['nome_completo']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="barbeiro_id" class="form-label">Barbeiro *</label>
                                    <select class="form-select" id="barbeiro_id" name="barbeiro_id" required>
                                        <option value="">Selecione um barbeiro</option>
                                        <?php
                                        while ($row = $stmt_barbeiros->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<option value='{$row['id']}'>{$row['nome']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="data_atendimento" class="form-label">Data *</label>
                                    <input type="date" class="form-control" id="data_atendimento" name="data_atendimento" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="hora_atendimento" class="form-label">Hora *</label>
                                    <input type="time" class="form-control" id="hora_atendimento" name="hora_atendimento" value="<?php echo date('H:i'); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Serviços *</label>
                            <div class="row" id="servicos-container">
                                <?php
                                $stmt_servicos_rewind = $servico->readAll();
                                while ($row = $stmt_servicos_rewind->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<div class='col-md-6'>";
                                    echo "<div class='form-check'>";
                                    echo "<input class='form-check-input servico-check' type='checkbox' name='servicos[]' value='{$row['id']}' data-preco='{$row['preco']}' id='servico_{$row['id']}'>";
                                    echo "<label class='form-check-label' for='servico_{$row['id']}'>";
                                    echo "{$row['nome']} - R$ " . number_format($row['preco'], 2, ',', '.');
                                    echo "</label>";
                                    echo "</div>";
                                    echo "</div>";
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Valor Total: <span id="valor-total" class="text-success fw-bold">R$ 0,00</span></label>
                        </div>
                        
                        <div class="mb-3">
                            <label for="observacoes" class="form-label">Observações</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Registrar Atendimento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detalhes -->
    <div class="modal fade" id="detalhesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalhes do Atendimento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detalhes-content">
                    <!-- Conteúdo carregado via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Calcular valor total dos serviços selecionados
        document.addEventListener('DOMContentLoaded', function() {
            const servicoChecks = document.querySelectorAll('.servico-check');
            const valorTotalSpan = document.getElementById('valor-total');
            
            servicoChecks.forEach(function(check) {
                check.addEventListener('change', calcularTotal);
            });
            
            function calcularTotal() {
                let total = 0;
                servicoChecks.forEach(function(check) {
                    if (check.checked) {
                        total += parseFloat(check.dataset.preco);
                    }
                });
                
                valorTotalSpan.textContent = 'R$ ' + total.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        });
        
        function verDetalhes(id) {
            // Aqui você pode implementar uma chamada AJAX para carregar os detalhes
            fetch(`../api/receita_detalhes.php?id=${id}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('detalhes-content').innerHTML = data;
                    new bootstrap.Modal(document.getElementById('detalhesModal')).show();
                });
        }
        
        // Resetar modal ao fechar
        document.getElementById('receitaModal').addEventListener('hidden.bs.modal', function() {
            document.querySelector('form').reset();
            document.getElementById('valor-total').textContent = 'R$ 0,00';
        });
    </script>
</body>
</html>