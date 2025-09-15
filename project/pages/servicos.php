<?php
require_once '../auth/check_auth.php';
checkAuthentication();

require_once(__DIR__ . '/../config/database.php');
require_once '../includes/Servico.php';

$database = new Database();
$db = $database->getConnection();
$servico = new Servico($db);

// Processar ações
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $servico->nome = $_POST['nome'];
                $servico->duracao_minutos = $_POST['duracao_minutos'];
                $servico->preco = $_POST['preco'];
                if ($servico->create()) {
                    $message = "Serviço cadastrado com sucesso!";
                    $message_type = "success";
                } else {
                    $message = "Erro ao cadastrar serviço.";
                    $message_type = "danger";
                }
                break;

            case 'update':
                $servico->id = $_POST['id'];
                $servico->nome = $_POST['nome'];
                $servico->duracao_minutos = $_POST['duracao_minutos'];
                $servico->preco = $_POST['preco'];
                if ($servico->update()) {
                    $message = "Serviço atualizado com sucesso!";
                    $message_type = "success";
                } else {
                    $message = "Erro ao atualizar serviço.";
                    $message_type = "danger";
                }
                break;

            case 'delete':
                $servico->id = $_POST['id'];
                if ($servico->delete()) {
                    $message = "Serviço removido com sucesso!";
                    $message_type = "success";
                } else {
                    $message = "Erro ao remover serviço.";
                    $message_type = "danger";
                }
                break;
        }
    }
}

$stmt = $servico->readAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serviços - Sistema Barbearia</title>
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
                    <h1 class="h2">Serviços</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#servicoModal">
                        <i class="fas fa-plus me-2"></i>Novo Serviço
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
                                        <th>Nome</th>
                                        <th>Duração (min)</th>
                                        <th>Preço</th>
                                        <th>Cadastrado em</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        extract($row);
                                        echo "<tr>";
                                        echo "<td>{$id}</td>";
                                        echo "<td>{$nome}</td>";
                                        echo "<td>{$duracao_minutos}</td>";
                                        echo "<td>R$ " . number_format($preco, 2, ',', '.') . "</td>";
                                        echo "<td>" . date('d/m/Y H:i', strtotime($created_at)) . "</td>";
                                        echo "<td>";
                                        echo "<button class='btn btn-sm btn-outline-primary me-1' onclick='editarServico({$id}, \"{$nome}\", {$duracao_minutos}, {$preco})'>";
                                        echo "<i class='fas fa-edit'></i>";
                                        echo "</button>";
                                        echo "<button class='btn btn-sm btn-outline-danger' onclick='confirmarExclusao({$id}, \"{$nome}\")'>";
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

    <!-- Modal Serviço -->
    <div class="modal fade" id="servicoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Cadastrar Serviço</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="id" id="servicoId">
                        
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome do Serviço *</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="duracao_minutos" class="form-label">Duração (minutos) *</label>
                            <input type="number" class="form-control" id="duracao_minutos" name="duracao_minutos" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="preco" class="form-label">Preço (R$) *</label>
                            <input type="number" step="0.01" class="form-control" id="preco" name="preco" required>
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
                        <input type="hidden" name="id" id="excluirServicoId">
                        <p>Tem certeza que deseja remover o serviço <strong id="excluirServicoNome"></strong>?</p>
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
        function editarServico(id, nome, duracao, preco) {
            document.getElementById('servicoId').value = id;
            document.getElementById('nome').value = nome;
            document.getElementById('duracao_minutos').value = duracao;
            document.getElementById('preco').value = preco;
            document.querySelector('input[name="action"]').value = 'update';
            document.querySelector('.modal-title').textContent = 'Editar Serviço';
            
            new bootstrap.Modal(document.getElementById('servicoModal')).show();
        }

        function confirmarExclusao(id, nome) {
            document.getElementById('excluirServicoId').value = id;
            document.getElementById('excluirServicoNome').textContent = nome;
            
            new bootstrap.Modal(document.getElementById('confirmarExclusaoModal')).show();
        }

        // Resetar modal ao fechar
        document.getElementById('servicoModal').addEventListener('hidden.bs.modal', function() {
            document.querySelector('form').reset();
            document.querySelector('input[name="action"]').value = 'create';
            document.querySelector('.modal-title').textContent = 'Cadastrar Serviço';
        });
    </script>
</body>
</html>