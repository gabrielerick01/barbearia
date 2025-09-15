<?php
require_once(__DIR__ . '/../config/database.php');

class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $nome;
    public $usuario;
    public $senha_hash;
    public $perfil;
    public $ativo;
    public $ultimo_login;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Criar usuário
    function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET nome=:nome, usuario=:usuario, 
                     senha_hash=:senha_hash, perfil=:perfil";
        
        $stmt = $this->conn->prepare($query);
        
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->usuario = htmlspecialchars(strip_tags($this->usuario));
        $this->perfil = htmlspecialchars(strip_tags($this->perfil));
        
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":usuario", $this->usuario);
        $stmt->bindParam(":senha_hash", $this->senha_hash);
        $stmt->bindParam(":perfil", $this->perfil);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Autenticar usuário por usuário e senha
    function authenticate($usuario, $senha) {
        $query = "SELECT id, nome, usuario, senha_hash, perfil, ativo 
                 FROM " . $this->table_name . " 
                 WHERE usuario = :usuario AND ativo = 1 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar senha
            if(password_verify($senha, $row['senha_hash'])) {
                $this->id = $row['id'];
                $this->nome = $row['nome'];
                $this->usuario = $row['usuario'];
                $this->perfil = $row['perfil'];
                
                // Atualizar último login
                $this->updateLastLogin();
                
                return true;
            }
        }
        return false;
    }

    // Atualizar último login
    function updateLastLogin() {
        $query = "UPDATE " . $this->table_name . " 
                 SET ultimo_login = CURRENT_TIMESTAMP 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
    }

    // Listar todos os usuários
    function readAll() {
        $query = "SELECT id, nome, usuario, perfil, ativo, ultimo_login, created_at
                 FROM " . $this->table_name . " 
                 WHERE ativo = 1 
                 ORDER BY nome ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Ler um usuário específico
    function readOne() {
        $query = "SELECT id, nome, usuario, perfil, ativo, ultimo_login
                 FROM " . $this->table_name . " 
                 WHERE id = ? LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->nome = $row['nome'];
            $this->usuario = $row['usuario'];
            $this->perfil = $row['perfil'];
            $this->ativo = $row['ativo'];
            $this->ultimo_login = $row['ultimo_login'];
            return true;
        }
        return false;
    }

    // Atualizar usuário
    function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET nome = :nome, usuario = :usuario, perfil = :perfil";
        
        // Só atualizar senha se foi fornecida
        if($this->senha_hash) {
            $query .= ", senha_hash = :senha_hash";
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->usuario = htmlspecialchars(strip_tags($this->usuario));
        $this->perfil = htmlspecialchars(strip_tags($this->perfil));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':usuario', $this->usuario);
        $stmt->bindParam(':perfil', $this->perfil);
        $stmt->bindParam(':id', $this->id);
        
        if($this->senha_hash) {
            $stmt->bindParam(':senha_hash', $this->senha_hash);
        }
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Inativar usuário (soft delete)
    function delete() {
        $query = "UPDATE " . $this->table_name . " SET ativo = 0 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Verificar se usuário já existe
    function usuarioExists($usuario, $exclude_id = null) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE usuario = :usuario";
        
        if($exclude_id) {
            $query .= " AND id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario', $usuario);
        
        if($exclude_id) {
            $stmt->bindParam(':exclude_id', $exclude_id);
        }
        
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    // Verificar se é admin
    function isAdmin() {
        return $this->perfil === 'admin';
    }

    // Gerar hash da senha
    static function hashPassword($senha) {
        return password_hash($senha, PASSWORD_DEFAULT);
    }

    // Verificar senha
    static function verifyPassword($senha, $hash) {
        return password_verify($senha, $hash);
    }
}
?>