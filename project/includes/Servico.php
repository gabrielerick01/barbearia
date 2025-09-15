<?php
require_once(__DIR__ . '/../config/database.php');

class Servico {
    private $conn;
    private $table_name = "servicos";

    public $id;
    public $nome;
    public $duracao_minutos;
    public $preco;
    public $ativo;

    public function __construct($db) {
        $this->conn = $db;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET nome=:nome, duracao_minutos=:duracao_minutos, preco=:preco";
        
        $stmt = $this->conn->prepare($query);
        
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->duracao_minutos = htmlspecialchars(strip_tags($this->duracao_minutos));
        $this->preco = htmlspecialchars(strip_tags($this->preco));
        
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":duracao_minutos", $this->duracao_minutos);
        $stmt->bindParam(":preco", $this->preco);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    function readAll() {
        $query = "SELECT id, nome, duracao_minutos, preco, created_at 
                 FROM " . $this->table_name . " 
                 WHERE ativo = 1 
                 ORDER BY nome ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    function readOne() {
        $query = "SELECT id, nome, duracao_minutos, preco, ativo 
                 FROM " . $this->table_name . " 
                 WHERE id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->nome = $row['nome'];
            $this->duracao_minutos = $row['duracao_minutos'];
            $this->preco = $row['preco'];
            $this->ativo = $row['ativo'];
            return true;
        }
        return false;
    }

    function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET nome = :nome, duracao_minutos = :duracao_minutos, preco = :preco 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->duracao_minutos = htmlspecialchars(strip_tags($this->duracao_minutos));
        $this->preco = htmlspecialchars(strip_tags($this->preco));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(':nome', $this->nome);
        $stmt->bindParam(':duracao_minutos', $this->duracao_minutos);
        $stmt->bindParam(':preco', $this->preco);
        $stmt->bindParam(':id', $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    function delete() {
        $query = "UPDATE " . $this->table_name . " SET ativo = 0 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>