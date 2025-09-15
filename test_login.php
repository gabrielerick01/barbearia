<?php
/**
 * Script de teste para verificar o sistema de login
 * Execute este arquivo para testar se o login está funcionando
 */

require_once 'config/database.php';
require_once 'includes/Usuario.php';

echo "<h2>Teste do Sistema de Login</h2>";

// Teste 1: Conexão com banco
echo "<h3>1. Testando conexão com banco de dados...</h3>";
try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "✅ Conexão com banco estabelecida com sucesso!<br>";
    } else {
        echo "❌ Erro na conexão com banco de dados!<br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
    exit;
}

// Teste 2: Verificar usuários na base
echo "<h3>2. Verificando usuários cadastrados...</h3>";
try {
    $query = "SELECT id, nome, usuario, perfil, ativo FROM usuarios WHERE ativo = 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
    
    if (count($usuarios) > 0) {
        echo "✅ Usuários encontrados:<br>";
        foreach ($usuarios as $user) {
            echo "- ID: {$user['id']}, Nome: {$user['nome']}, Usuário: {$user['usuario']}, Perfil: {$user['perfil']}<br>";
        }
    } else {
        echo "❌ Nenhum usuário ativo encontrado!<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro ao buscar usuários: " . $e->getMessage() . "<br>";
}

// Teste 3: Testar autenticação
echo "<h3>3. Testando autenticação...</h3>";
$usuario = new Usuario($db);

// Teste com admin
echo "<strong>Testando usuário 'admin' com senha '123456':</strong><br>";
if ($usuario->authenticate('admin', '123456')) {
    echo "✅ Login do admin funcionando!<br>";
    echo "- ID: {$usuario->id}<br>";
    echo "- Nome: {$usuario->nome}<br>";
    echo "- Perfil: {$usuario->perfil}<br>";
} else {
    echo "❌ Falha no login do admin!<br>";
}

echo "<br>";

// Teste com usuário comum
echo "<strong>Testando usuário 'usuario' com senha '123456':</strong><br>";
$usuario2 = new Usuario($db);
if ($usuario2->authenticate('usuario', '123456')) {
    echo "✅ Login do usuário comum funcionando!<br>";
    echo "- ID: {$usuario2->id}<br>";
    echo "- Nome: {$usuario2->nome}<br>";
    echo "- Perfil: {$usuario2->perfil}<br>";
} else {
    echo "❌ Falha no login do usuário comum!<br>";
}

echo "<br>";

// Teste com credenciais inválidas
echo "<strong>Testando credenciais inválidas:</strong><br>";
$usuario3 = new Usuario($db);
if ($usuario3->authenticate('admin', 'senha_errada')) {
    echo "❌ PROBLEMA: Login com senha errada foi aceito!<br>";
} else {
    echo "✅ Credenciais inválidas rejeitadas corretamente!<br>";
}

// Teste 4: Verificar hash das senhas
echo "<h3>4. Verificando hash das senhas...</h3>";
$senha_teste = '123456';
$hash_correto = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

if (password_verify($senha_teste, $hash_correto)) {
    echo "✅ Hash da senha está correto!<br>";
} else {
    echo "❌ Hash da senha está incorreto!<br>";
    echo "Hash esperado: $hash_correto<br>";
    echo "Novo hash gerado: " . password_hash($senha_teste, PASSWORD_DEFAULT) . "<br>";
}

echo "<h3>5. Resumo do Teste</h3>";
echo "<p><strong>Se todos os testes acima passaram (✅), o sistema de login está funcionando corretamente!</strong></p>";
echo "<p>Você pode acessar o sistema em: <a href='auth/login.php'>auth/login.php</a></p>";
echo "<p><strong>Credenciais para teste:</strong></p>";
echo "<ul>";
echo "<li>Admin: usuário = 'admin', senha = '123456'</li>";
echo "<li>Usuário: usuário = 'usuario', senha = '123456'</li>";
echo "</ul>";

echo "<hr>";
echo "<p><em>Após confirmar que tudo está funcionando, delete este arquivo (test_login.php) por segurança.</em></p>";
?>