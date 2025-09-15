<?php
/**
 * Script de instalação automática do Sistema Barbearia
 * Execute este arquivo uma única vez após fazer upload dos arquivos
 */

// Configurações padrão do banco de dados
$host = 'localhost';
$db_name = 'barbearia_db';
$username = 'root';  // Altere conforme seu servidor
$password = '';      // Altere conforme seu servidor
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - Sistema Barbearia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mt-5 shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-cut me-2"></i>Instalação - Sistema Barbearia</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        if ($_POST && isset($_POST['instalar'])) {
                            $host = $_POST['host'];
                            $db_name = $_POST['db_name'];
                            $username = $_POST['username'];
                            $password = $_POST['password'];
                            
                            try {
                                // Conectar ao MySQL
                                $pdo = new PDO("mysql:host=$host", $username, $password);
                                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                
                                echo '<div class="alert alert-success"><i class="fas fa-check me-2"></i>Conectado ao MySQL com sucesso!</div>';
                                
                                // Criar banco de dados
                                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
                                $pdo->exec("USE `$db_name`");
                                
                                echo '<div class="alert alert-success"><i class="fas fa-database me-2"></i>Banco de dados criado com sucesso!</div>';
                                
                                // Caminho do arquivo SQL (dentro da pasta project/sql)
                                $sqlFile = __DIR__ . '/sql/create_database.sql';

                                if (!file_exists($sqlFile)) {
                                    die('<div class="alert alert-danger">Erro: Arquivo SQL não encontrado em <code>' . $sqlFile . '</code></div>');
                                }

                                // Lê o conteúdo do arquivo
                                $sql = file_get_contents($sqlFile);

                                // Divide as instruções SQL por ponto e vírgula
                                $statements = explode(';', $sql);

                                // Executa cada instrução
                                foreach ($statements as $statement) {
                                    $statement = trim($statement);
                                    if (!empty($statement) && !preg_match('/^(CREATE DATABASE|USE)/i', $statement)) {
                                        $pdo->exec($statement);
                                    }
                                }

                                echo '<div class="alert alert-success"><i class="fas fa-table me-2"></i>Tabelas criadas com sucesso!</div>';
                                
                                // Atualizar arquivo de configuração
                                $config_content = "<?php
class Database {
    private \$host = '$host';
    private \$db_name = '$db_name';
    private \$username = '$username';
    private \$password = '$password';
    public \$conn;

    public function getConnection() {
        \$this->conn = null;
        
        try {
            \$this->conn = new PDO(\"mysql:host=\" . \$this->host . \";dbname=\" . \$this->db_name, 
                                \$this->username, \$this->password);
            \$this->conn->exec(\"set names utf8\");
            \$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException \$exception) {
            echo \"Erro de conexão: \" . \$exception->getMessage();
        }
        
        return \$this->conn;
    }
}
?>";
                                
                                // Salva o config/database.php
                                if (!is_dir(__DIR__ . '/config')) {
                                    mkdir(__DIR__ . '/config', 0777, true);
                                }
                                file_put_contents(__DIR__ . '/config/database.php', $config_content);
                                
                                echo '<div class="alert alert-success"><i class="fas fa-cog me-2"></i>Configuração salva com sucesso!</div>';
                                echo '<div class="alert alert-info mt-4">
                                        <h5><i class="fas fa-info-circle me-2"></i>Instalação Concluída!</h5>
                                        <p class="mb-2">O sistema foi instalado com sucesso. Você pode agora:</p>
                                        <ul class="mb-3">
                                            <li>Acessar o sistema através do <a href="auth/login.php" class="text-decoration-none">Login</a></li>
                                           <li>Usar as credenciais: <strong>admin</strong> / <strong>123456</strong> (Admin)</li>
                                           <li>Ou: <strong>usuario</strong> / <strong>123456</strong> (Usuário comum)</li>
                                            <li>Cadastrar clientes na seção <strong>Clientes</strong></li>
                                            <li>Gerenciar serviços na seção <strong>Serviços</strong></li>
                                            <li>Registrar atendimentos na seção <strong>Atendimentos</strong></li>
                                            <li>Controlar despesas na seção <strong>Despesas</strong></li>
                                            <li>Visualizar relatórios na seção <strong>Relatórios</strong></li>
                                        </ul>
                                        <p class="mb-0"><strong>Importante:</strong> Delete este arquivo (install.php) por motivos de segurança.</p>
                                      </div>';
                                
                            } catch (PDOException $e) {
                                echo '<div class="alert alert-danger"><i class="fas fa-times me-2"></i>Erro: ' . $e->getMessage() . '</div>';
                            }
                        } else {
                        ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Host do MySQL</label>
                                    <input type="text" class="form-control" name="host" value="localhost" required>
                                    <div class="form-text">Geralmente é 'localhost' em servidores locais</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Nome do Banco de Dados</label>
                                    <input type="text" class="form-control" name="db_name" value="barbearia_db" required>
                                    <div class="form-text">Nome que será dado ao banco de dados</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Usuário do MySQL</label>
                                    <input type="text" class="form-control" name="username" required>
                                    <div class="form-text">Usuário fornecido pelo seu provedor de hospedagem</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Senha do MySQL</label>
                                    <input type="password" class="form-control" name="password">
                                    <div class="form-text">Senha fornecida pelo seu provedor de hospedagem</div>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Atenção:</strong> Certifique-se de que as informações do banco de dados estão corretas. 
                                    Este processo irá criar o banco de dados e todas as tabelas necessárias.
                                </div>
                                
                                <button type="submit" name="instalar" class="btn btn-primary btn-lg">
                                    <i class="fas fa-play me-2"></i>Instalar Sistema
                                </button>
                            </form>
                        <?php } ?>
                    </div>
                </div>
                
                <div class="text-center mt-4 text-muted">
                    <p>Sistema de Controle para Barbearia - Versão 1.0</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
