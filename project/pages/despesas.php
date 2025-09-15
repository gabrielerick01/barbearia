<?php
require_once '../auth/check_auth.php';
checkAuthentication();

require_once(__DIR__ . '/../config/database.php');
require_once '../includes/Despesa.php';
require_once '../includes/Barbeiro.php';

$database = new Database();
$db = $database->getConnection();
$despesa = new Despesa($db);
$barbeiro = new Barbeiro($db);

// Processar ações
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $despesa->descricao = $_POST['descricao'];
                $despesa->valor = $_POST['valor'];
                $despesa->barbeiro_id = $_POST['barbeiro_id'];
                $despesa->data_despesa = $_POST['data_despesa'];
                
                // Adicionar dados de parcelamento se necessário
                if (isset($_POST['despesa_parcelada'])) {
                    $_POST['quantidade_parcelas'] = $_POST['quantidade_parcelas'] ?? 2;
                    $_POST['data_inicio_parcela'] = $_POST['data_inicio_parcela'] ?? $_POST['data_despesa'];
                }
                
                if ($despesa->create()) {
                    if (isset($_POST['despesa_parcelada'])) {
                        $parcelas = $_POST['quantidade_parcelas'];
                        $message = "Despesa parcelada em {$parcelas}x cadastrada com sucesso!";
                    } else {
                        $message = "Despesa cadastrada com sucesso!";
                    }
                    $message_type = "success";
                } else {
                    $message = "Erro ao cadastrar despesa.";
                    $message_type = "danger";
                }
                break;

            case 'update':
                $despesa->id = $_POST['id'];
                $despesa->descricao = $_POST['descricao'];
                $despesa->valor = $_POST['valor'];
                $despesa->barbeiro_id = $_POST['barbeiro_id'];
                $despesa->data_despesa = $_POST['data_despesa'];
                if ($despesa->update()) {
                    $message = "Despesa atualizada com sucesso!";
                    $message_type = "success";
                } else {
                    $message = "Erro ao atualizar despesa.";
                    $message_type = "danger";
                }
                break;

            case 'delete':
                $despesa->id = $_POST['id'];
                if ($despesa->delete()) {
                    $message = "Despesa removida com sucesso!";
                    $message_type = "success";
                } else {
                    $message = "Erro ao remover despesa.";
                    $message_type = "danger";
                }
                break;
        }
    }
}

$stmt = $despesa->readAll();
$stmt_barbeiros = $barbeiro->readAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Despesas - Sistema Barbearia</title>
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
                    <h1 class="h2">Despesas</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#despesaModal">
                        <i class="fas fa-plus me-2"></i>Nova Despesa
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
                                        <th>Data</th>
                                        <th>Descrição</th>
                                        <th>Valor</th>
                                        <th>Barbeiro</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        extract($row);
                                        echo "<tr>";
                                        echo "<td>{$id}</td>";
                                        echo "<td>" . date('d/m/Y', strtotime($data_despesa)) . "</td>";
                                        echo "<td>{$descricao}</td>";
                                        echo "<td>R$ " . number_format($valor, 2, ',', '.') . "</td>";
                                        echo "<td>{$barbeiro_nome}</td>";
                                        echo "<td>";
                                        echo "<button class='btn btn-sm btn-outline-primary me-1' onclick='editarDespesa({$id}, \"{$descricao}\", {$valor}, \"{$barbeiro_id}\", \"{$data_despesa}\")'>";
                                        echo "<i class='fas fa-edit'></i>";
                                        echo "</button>";
                                        echo "<button class='btn btn-sm btn-outline-danger' onclick='confirmarExclusao({$id}, \"{$descricao}\")'>";
                                        echo "<i class='fas fa-trash'></i>";
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

    <!-- Modal Despesa -->
    <div class="modal fade" id="despesaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Nova Despesa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="id" id="despesaId">
                        
                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição *</label>
                            <input type="text" class="form-control" id="descricao" name="descricao" placeholder="Ex: shampoo, navalha, loção" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="valor" class="form-label">Valor (R$) *</label>
                            <input type="number" step="0.01" class="form-control" id="valor" name="valor" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="barbeiro_id" class="form-label">Barbeiro</label>
                            <select class="form-select" id="barbeiro_id" name="barbeiro_id">
                                <option value="">Despesa Geral</option>
                                <?php
                                while ($row = $stmt_barbeiros->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$row['id']}'>{$row['nome']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="data_despesa" class="form-label">Data *</label>
                            <input type="date" class="form-control" id="data_despesa" name="data_despesa" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="despesaParcelada" name="despesa_parcelada" value="1" onchange="toggleParcelamento()">
                                <label class="form-check-label" for="despesaParcelada">
                                    Despesa parcelada?
                                </label>
                            </div>
                        </div>
                        
                        <div id="parcelamentoFields" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="quantidadeParcelas" class="form-label">Quantidade de Parcelas *</label>
                                        <select class="form-select" id="quantidadeParcelas" name="quantidade_parcelas">
                                            <option value="2">2x</option>
                                            <option value="3">3x</option>
                                            <option value="4">4x</option>
                                            <option value="5">5x</option>
                                            <option value="6">6x</option>
                                            <option value="12">12x</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="dataInicioParcela" class="form-label">Data da 1ª Parcela</label>
                                        <input type="date" class="form-control" id="dataInicioParcela" name="data_inicio_parcela">
                                        <div class="form-text">Se não informado, usará a data da despesa</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Confirmar Exclusão -->
    <div class="modal fade" id="confirmarExclusaoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Exclusão</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="excluirDespesaId">
                        <p>Tem certeza que deseja remover a despesa <strong id="excluirDespesaDescricao"></strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleParcelamento() {
            const checkbox = document.getElementById('despesaParcelada');
            const fields = document.getElementById('parcelamentoFields');
            
            if (checkbox.checked) {
                fields.style.display = 'block';
                // Definir data padrão como próximo mês
                const proximoMes = new Date();
                proximoMes.setMonth(proximoMes.getMonth() + 1);
                document.getElementById('dataInicioParcela').value = proximoMes.toISOString().split('T')[0];
            } else {
                fields.style.display = 'none';
            }
        }
        
        function editarDespesa(id, descricao, valor, barbeiro_id, data_despesa) {
            document.getElementById('despesaId').value = id;
            document.getElementById('descricao').value = descricao;
            document.getElementById('valor').value = valor;
            document.getElementById('barbeiro_id').value = barbeiro_id || '';
            document.getElementById('data_despesa').value = data_despesa;
            document.querySelector('input[name="action"]').value = 'update';
            document.querySelector('.modal-title').textContent = 'Editar Despesa';
            
            new bootstrap.Modal(document.getElementById('despesaModal')).show();
        }

        function confirmarExclusao(id, descricao) {
            document.getElementById('excluirDespesaId').value = id;
            document.getElementById('excluirDespesaDescricao').textContent = descricao;
            
            new bootstrap.Modal(document.getElementById('confirmarExclusaoModal')).show();
        }

        // Resetar modal ao fechar
        document.getElementById('despesaModal').addEventListener('hidden.bs.modal', function() {
            document.querySelector('form').reset();
            document.querySelector('input[name="action"]').value = 'create';
            document.querySelector('.modal-title').textContent = 'Nova Despesa';
            document.getElementById('data_despesa').value = '<?php echo date('Y-m-d'); ?>';
            document.getElementById('parcelamentoFields').style.display = 'none';
        });
    </script>
</body>
</html>