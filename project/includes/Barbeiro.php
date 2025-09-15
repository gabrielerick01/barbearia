<?php
require_once(__DIR__ . '/../config/database.php');

class Barbeiro {
    private $conn;
    private $table_name = "barbeiros";

    public $id;
    public $nome;
    public $telefone;
    public $ativo;

    public function __construct($db) {
        $this->conn = $db;
    }

    function readAll() {
        $query = "SELECT id, nome, telefone 
                 FROM " . $this->table_name . " 
                 WHERE ativo = 1 
                 ORDER BY nome ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    function readOne() {
        $query = "SELECT id, nome, telefone, ativo 
                 FROM " . $this->table_name . " 
                 WHERE id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->nome = $row['nome'];
            $this->telefone = $row['telefone'];
            $this->ativo = $row['ativo'];
            return true;
        }
        return false;
    }
}
?>