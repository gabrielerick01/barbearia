<?php
session_start();
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../includes/Usuario.php');

// Se já estiver logado, redirecionar para dashboard
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: ../index.php');
    exit();
}

$error_message = '';

if ($_POST) {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    
    if (!empty($usuario) && !empty($senha)) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            if ($db) {
                $user = new Usuario($db);
                
                if ($user->authenticate($usuario, $senha)) {
                    // Login válido - criar sessão
                    $_SESSION['user_logged_in'] = true;
                    $_SESSION['user_id'] = $user->id;
                    $_SESSION['user_name'] = $user->nome;
                    $_SESSION['user_usuario'] = $user->usuario;
                    $_SESSION['user_perfil'] = $user->perfil;
                    $_SESSION['login_time'] = time();
                    
                    // Redirecionar para dashboard
                    header('Location: ../index.php');
                    exit();
                } else {
                    $error_message = 'Usuário ou senha incorretos!';
                }
            } else {
                $error_message = 'Erro de conexão com o banco de dados!';
            }
        } catch (Exception $e) {
            $error_message = 'Erro interno do sistema. Tente novamente.';
            error_log("Erro de login: " . $e->getMessage());
        }
    } else {
        $error_message = 'Preencha todos os campos!';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Barbearia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            width: 100%;
            max-width: 400px;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: #333;
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 2rem;
        }

        .login-header .subtitle {
            color: #666;
            font-size: 0.9rem;
        }

        .login-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .form-floating {
            margin-bottom: 1.5rem;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 1.5rem;
        }

        .credentials-info {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.85rem;
            color: #666;
        }

        .credentials-info strong {
            color: #333;
        }

        .loading {
            display: none;
        }

        .loading.show {
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-icon">
                <i class="fas fa-cut"></i>
            </div>
            <h1>Sistema Barbearia</h1>
            <p class="subtitle">Faça login para acessar o sistema</p>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <div class="form-floating">
                <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Usuário" required autocomplete="username">
                <label for="usuario"><i class="fas fa-user me-2"></i>Usuário</label>
            </div>

            <div class="form-floating">
                <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required autocomplete="current-password">
                <label for="senha"><i class="fas fa-lock me-2"></i>Senha</label>
            </div>

            <button type="submit" class="btn btn-login" id="btnLogin">
                <span class="loading" id="loading">
                    <i class="fas fa-spinner fa-spin me-2"></i>
                </span>
                <span class="btn-text" id="btnText">
                    <i class="fas fa-sign-in-alt me-2"></i>Entrar
                </span>
            </button>
        </form>

        <div class="credentials-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Credenciais de teste:</strong><br>
            <strong>Admin:</strong> admin / 123456<br>
            <strong>Usuário:</strong> usuario / 123456
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Efeito de loading no botão
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loading = document.getElementById('loading');
            const btnText = document.getElementById('btnText');
            const btnLogin = document.getElementById('btnLogin');
            
            // Mostrar loading
            loading.classList.add('show');
            btnText.style.display = 'none';
            btnLogin.disabled = true;
        });

        // Efeito de foco nos campos
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Auto-focus no campo usuário
        document.getElementById('usuario').focus();
    </script>
</body>
</html>