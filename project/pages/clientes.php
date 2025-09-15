<?php
require_once '../auth/check_auth.php';
checkAuthentication();

require_once(__DIR__ . '/../config/database.php');
require_once '../includes/Cliente.php';

$database = new Database();
$db = $database->getConnection();
$cliente = new Cliente($db);

// Processar ações
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $cliente->nome_completo = $_POST['nome_completo'];
                $cliente->telefone = $_POST['telefone'];
                if ($cliente->create()) {
                    $message = "Cliente cadastrado com sucesso!";
                    $message_type = "success";
                } else {
                    $message = "Erro ao cadastrar cliente.";
                    $message_type = "danger";
                }
                break;

            case 'update':
                $cliente->id = $_POST['id'];
                $cliente->nome_completo = $_POST['nome_completo'];
                $cliente->telefone = $_POST['telefone'];
                if ($cliente->update()) {
                    $message = "Cliente atualizado com sucesso!";
                    $message_type = "success";
                } else {
                    $message = "Erro ao atualizar cliente.";
                    $message_type = "danger";
                }
                break;

            case 'delete':
                $cliente->id = $_POST['id'];
                if ($cliente->delete()) {
                    $message = "Cliente removido com sucesso!";
                    $message_type = "success";
                } else {
                    $message = "Erro ao remover cliente.";
                    $message_type = "danger";
                }
                break;
        }
    }
}

$stmt = $cliente->readAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Sistema Barbearia</title>
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
                    <h1 class="h2">Clientes</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clienteModal">
                        <i class="fas fa-plus me-2"></i>Novo Cliente
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
                                        <th>Nome Completo</th>
                                        <th>Telefone</th>
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
                                        echo "<td>{$nome_completo}</td>";
                                        echo "<td>{$telefone}</td>";
                                        echo "<td>" . date('d/m/Y H:i', strtotime($created_at)) . "</td>";
                                        echo "<td>";
                                        echo "<button class='btn btn-sm btn-outline-primary me-1' onclick='editarCliente({$id}, \"{$nome_completo}\", \"{$telefone}\")'>";
                                        echo "<i class='fas fa-edit'></i>";
                                        echo "</button>";
                                        echo "<button class='btn btn-sm btn-outline-danger' onclick='confirmarExclusao({$id}, \"{$nome_completo}\")'>";
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

    <!-- Modal Cliente -->
    <div class="modal fade" id="clienteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Cadastrar Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="id" id="clienteId">
                        
                        <div class="mb-3">
                            <label for="nome_completo" class="form-label">Nome Completo *</label>
                            <input type="text" class="form-control" id="nome_completo" name="nome_completo" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="telefone" class="form-label">Telefone *</label>
                            <input type="text" class="form-control" id="telefone" name="telefone" required>
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
                        <input type="hidden" name="id" id="excluirClienteId">
                        <p>Tem certeza que deseja remover o cliente <strong id="excluirClienteNome"></strong>?</p>
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
        function editarCliente(id, nome, telefone) {
            document.getElementById('clienteId').value = id;
            document.getElementById('nome_completo').value = nome;
            document.getElementById('telefone').value = telefone;
            document.querySelector('input[name="action"]').value = 'update';
            document.querySelector('.modal-title').textContent = 'Editar Cliente';
            
            new bootstrap.Modal(document.getElementById('clienteModal')).show();
        }

        function confirmarExclusao(id, nome) {
            document.getElementById('excluirClienteId').value = id;
            document.getElementById('excluirClienteNome').textContent = nome;
            
            new bootstrap.Modal(document.getElementById('confirmarExclusaoModal')).show();
        }

        // Resetar modal ao fechar
        document.getElementById('clienteModal').addEventListener('hidden.bs.modal', function() {
            document.querySelector('form').reset();
            document.querySelector('input[name="action"]').value = 'create';
            document.querySelector('.modal-title').textContent = 'Cadastrar Cliente';
        });
    </script>
</body>
</html>