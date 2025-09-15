<?php
require_once(__DIR__ . '/../config/database.php');

class Cliente {
    private $conn;
    private $table_name = "clientes";

    public $nome_completo;
    public $telefone;
    public $ativo;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Criar cliente
    function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET nome_completo=:nome_completo, telefone=:telefone";
        
        $stmt = $this->conn->prepare($query);
        
        $this->nome_completo = htmlspecialchars(strip_tags($this->nome_completo));
        $this->telefone = htmlspecialchars(strip_tags($this->telefone));
        
        $stmt->bindParam(":nome_completo", $this->nome_completo);
        $stmt->bindParam(":telefone", $this->telefone);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Listar todos os clientes ativos
    function readAll() {
        $query = "SELECT  id ,nome_completo, telefone, created_at 
                 FROM " . $this->table_name . " 
                 WHERE ativo = 1 
                 ORDER BY nome_completo ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Ler um cliente específico
    function readOne() {
        $query = "SELECT id, nome_completo, telefone, ativo 
                 FROM " . $this->table_name . " 
                 WHERE id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->nome_completo = $row['nome_completo'];
            $this->telefone = $row['telefone'];
            $this->ativo = $row['ativo'];
            return true;
        }
        return false;
    }

    // Atualizar cliente
    function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET nome_completo = :nome_completo, telefone = :telefone 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->nome_completo = htmlspecialchars(strip_tags($this->nome_completo));
        $this->telefone = htmlspecialchars(strip_tags($this->telefone));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(':nome_completo', $this->nome_completo);
        $stmt->bindParam(':telefone', $this->telefone);
        $stmt->bindParam(':id', $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Inativar cliente (soft delete)
    function delete() {
        $query = "UPDATE " . $this->table_name . " SET ativo = 0 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Buscar clientes por nome
    function search($keywords) {
        $query = "SELECT id, nome_completo, telefone 
                 FROM " . $this->table_name . " 
                 WHERE nome_completo LIKE ? AND ativo = 1 
                 ORDER BY nome_completo ASC";
        
        $stmt = $this->conn->prepare($query);
        $keywords = "%{$keywords}%";
        $stmt->bindParam(1, $keywords);
        $stmt->execute();
        
        return $stmt;
    }
}
?>