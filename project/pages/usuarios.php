<?php
require_once(__DIR__ . '/../auth/check_auth.php');
checkAdminAccess(); // Só admin pode acessar esta página

require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../includes/Usuario.php');

$database = new Database();
$db = $database->getConnection();
$usuario = new Usuario($db);

// Processar ações
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                // Verificar se usuário já existe
                if ($usuario->usuarioExists($_POST['usuario'])) {
                    $message = "Este usuário já está cadastrado!";
                    $message_type = "danger";
                } else {
                    $usuario->nome = $_POST['nome'];
                    $usuario->usuario = $_POST['usuario'];
                    $usuario->perfil = $_POST['perfil'];
                    $usuario->senha_hash = Usuario::hashPassword($_POST['senha']);
                    
                    if ($usuario->create()) {
                        $message = "Usuário cadastrado com sucesso!";
                        $message_type = "success";
                    } else {
                        $message = "Erro ao cadastrar usuário.";
                        $message_type = "danger";
                    }
                }
                break;

            case 'update':
                $usuario->id = $_POST['id'];
                $usuario->nome = $_POST['nome'];
                $usuario->usuario = $_POST['usuario'];
                $usuario->perfil = $_POST['perfil'];
                
                // Verificar se usuário já existe (excluindo o próprio usuário)
                if ($usuario->usuarioExists($_POST['usuario'], $_POST['id'])) {
                    $message = "Este usuário já está sendo usado!";
                    $message_type = "danger";
                } else {
                    // Só atualizar senha se foi fornecida
                    if (!empty($_POST['senha'])) {
                        $usuario->senha_hash = Usuario::hashPassword($_POST['senha']);
                    }
                    
                    if ($usuario->update()) {
                        $message = "Usuário atualizado com sucesso!";
                        $message_type = "success";
                    } else {
                        $message = "Erro ao atualizar usuário.";
                        $message_type = "danger";
                    }
                }
                break;

            case 'delete':
                $usuario->id = $_POST['id'];
                
                // Não permitir excluir o próprio usuário
                if ($_POST['id'] == $_SESSION['user_id']) {
                    $message = "Você não pode excluir sua própria conta!";
                    $message_type = "danger";
                } else {
                    if ($usuario->delete()) {
                        $message = "Usuário removido com sucesso!";
                        $message_type = "success";
                    } else {
                        $message = "Erro ao remover usuário.";
                        $message_type = "danger";
                    }
                }
                break;
        }
    }
}

$stmt = $usuario->readAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - Sistema Barbearia</title>
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
                    <h1 class="h2">Usuários do Sistema</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#usuarioModal">
                        <i class="fas fa-plus me-2"></i>Novo Usuário
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
                                        <th>Nome</th>
                                        <th>Usuário</th>
                                        <th>Perfil</th>
                                        <th>Último Acesso</th>
                                        <th>Cadastrado em</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        extract($row);
                                        echo "<tr>";
                                        echo "<td>{$nome}</td>";
                                        echo "<td>{$usuario}</td>";
                                        echo "<td>";
                                        if ($perfil === 'admin') {
                                            echo "<span class='badge bg-warning'><i class='fas fa-crown me-1'></i>Admin</span>";
                                        } else {
                                            echo "<span class='badge bg-secondary'><i class='fas fa-user me-1'></i>Usuário</span>";
                                        }
                                        echo "</td>";
                                        echo "<td>" . ($ultimo_login ? date('d/m/Y H:i', strtotime($ultimo_login)) : 'Nunca') . "</td>";
                                        echo "<td>" . date('d/m/Y H:i', strtotime($created_at)) . "</td>";
                                        echo "<td>";
                                        if ($id != $_SESSION['user_id']) {
                                            echo "<button class='btn btn-sm btn-outline-primary me-1' onclick='editarUsuario({$id}, \"{$nome}\", \"{$usuario}\", \"{$perfil}\")'>";
                                            echo "<i class='fas fa-edit'></i>";
                                            echo "</button>";
                                            echo "<button class='btn btn-sm btn-outline-danger' onclick='confirmarExclusao({$id}, \"{$nome}\")'>";
                                            echo "<i class='fas fa-trash'></i>";
                                            echo "</button>";
                                        } else {
                                            echo "<span class='badge bg-warning'>Você</span>";
                                        }
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

    <!-- Modal Usuário -->
    <div class="modal fade" id="usuarioModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Cadastrar Usuário</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="id" id="usuarioId">
                        
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome Completo *</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="usuario" class="form-label">Usuário *</label>
                            <input type="text" class="form-control" id="usuario" name="usuario" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="perfil" class="form-label">Perfil *</label>
                            <select class="form-select" id="perfil" name="perfil" required>
                                <option value="usuario">Usuário</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha *</label>
                            <input type="password" class="form-control" id="senha" name="senha" required>
                            <div class="form-text">Mínimo 6 caracteres</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirmar_senha" class="form-label">Confirmar Senha *</label>
                            <input type="password" class="form-control" id="confirmar_senha" required>
                            <div class="form-text">Digite a senha novamente</div>
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
                        <input type="hidden" name="id" id="excluirUsuarioId">
                        <p>Tem certeza que deseja remover o usuário <strong id="excluirUsuarioNome"></strong>?</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Esta ação não pode ser desfeita!
                        </div>
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
        function editarUsuario(id, nome, usuario, perfil) {
            document.getElementById('usuarioId').value = id;
            document.getElementById('nome').value = nome;
            document.getElementById('usuario').value = usuario;
            document.getElementById('perfil').value = perfil;
            document.querySelector('input[name="action"]').value = 'update';
            document.querySelector('.modal-title').textContent = 'Editar Usuário';
            
            // Tornar senha opcional na edição
            document.getElementById('senha').required = false;
            document.getElementById('confirmar_senha').required = false;
            document.querySelector('label[for="senha"]').innerHTML = 'Nova Senha <small class="text-muted">(deixe em branco para manter atual)</small>';
            
            new bootstrap.Modal(document.getElementById('usuarioModal')).show();
        }

        function confirmarExclusao(id, nome) {
            document.getElementById('excluirUsuarioId').value = id;
            document.getElementById('excluirUsuarioNome').textContent = nome;
            
            new bootstrap.Modal(document.getElementById('confirmarExclusaoModal')).show();
        }

        // Validar senhas iguais
        document.getElementById('confirmar_senha').addEventListener('input', function() {
            const senha = document.getElementById('senha').value;
            const confirmar = this.value;
            
            if (senha !== confirmar) {
                this.setCustomValidity('As senhas não coincidem');
            } else {
                this.setCustomValidity('');
            }
        });

        // Resetar modal ao fechar
        document.getElementById('usuarioModal').addEventListener('hidden.bs.modal', function() {
            document.querySelector('form').reset();
            document.querySelector('input[name="action"]').value = 'create';
            document.querySelector('.modal-title').textContent = 'Cadastrar Usuário';
            document.getElementById('perfil').value = 'usuario';
            document.getElementById('senha').required = true;
            document.getElementById('confirmar_senha').required = true;
            document.querySelector('label[for="senha"]').innerHTML = 'Senha *';
        });

        // Validar formulário antes de enviar
        document.querySelector('#usuarioModal form').addEventListener('submit', function(e) {
            const senha = document.getElementById('senha').value;
            const confirmar = document.getElementById('confirmar_senha').value;
            const isEdit = document.querySelector('input[name="action"]').value === 'update';
            
            // Se é edição e senha está vazia, não validar
            if (isEdit && !senha && !confirmar) {
                return true;
            }
            
            // Validar tamanho da senha
            if (senha && senha.length < 6) {
                e.preventDefault();
                alert('A senha deve ter pelo menos 6 caracteres!');
                return false;
            }
            
            // Validar se senhas coincidem
            if (senha !== confirmar) {
                e.preventDefault();
                alert('As senhas não coincidem!');
                return false;
            }
        });
    </script>
</body>
</html>