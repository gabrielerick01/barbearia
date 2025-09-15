<?php
require_once(__DIR__ . '/../config/database.php');

class Receita {
    private $conn;
    private $table_name = "receitas";

    public $id;
    public $cliente_id;
    public $barbeiro_id;
    public $valor_total;
    public $observacoes;
    public $data_atendimento;

    public function __construct($db) {
        $this->conn = $db;
    }

    function create() {
        try {
            $this->conn->beginTransaction();
            
            // Inserir receita
            $query = "INSERT INTO " . $this->table_name . " 
                     SET cliente_id=:cliente_id, barbeiro_id=:barbeiro_id, 
                         valor_total=:valor_total, observacoes=:observacoes, 
                         data_atendimento=:data_atendimento";
            
            $stmt = $this->conn->prepare($query);
            
            $this->cliente_id = htmlspecialchars(strip_tags($this->cliente_id));
            $this->barbeiro_id = htmlspecialchars(strip_tags($this->barbeiro_id));
            $this->valor_total = htmlspecialchars(strip_tags($this->valor_total));
            $this->observacoes = htmlspecialchars(strip_tags($this->observacoes));
            
            $stmt->bindParam(":cliente_id", $this->cliente_id);
            $stmt->bindParam(":barbeiro_id", $this->barbeiro_id);
            $stmt->bindParam(":valor_total", $this->valor_total);
            $stmt->bindParam(":observacoes", $this->observacoes);
            $stmt->bindParam(":data_atendimento", $this->data_atendimento);
            
            if($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                $this->conn->commit();
                return true;
            }
            
            $this->conn->rollBack();
            return false;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    function addServico($servico_id, $quantidade, $preco_unitario) {
        $query = "INSERT INTO receita_servicos 
                 SET receita_id=:receita_id, servico_id=:servico_id, 
                     quantidade=:quantidade, preco_unitario=:preco_unitario, 
                     subtotal=:subtotal";
        
        $stmt = $this->conn->prepare($query);
        
        $subtotal = $quantidade * $preco_unitario;
        
        $stmt->bindParam(":receita_id", $this->id);
        $stmt->bindParam(":servico_id", $servico_id);
        $stmt->bindParam(":quantidade", $quantidade);
        $stmt->bindParam(":preco_unitario", $preco_unitario);
        $stmt->bindParam(":subtotal", $subtotal);
        
        return $stmt->execute();
    }

    function readAll($limit = 50) {
        $query = "SELECT r.id, r.valor_total, r.observacoes, r.data_atendimento,
                         c.nome_completo as cliente_nome, b.nome as barbeiro_nome
                  FROM " . $this->table_name . " r
                  LEFT JOIN clientes c ON r.cliente_id = c.id
                  LEFT JOIN barbeiros b ON r.barbeiro_id = b.id
                  ORDER BY r.data_atendimento DESC
                  LIMIT " . $limit;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    function readOne() {
        $query = "SELECT r.id, r.cliente_id, r.barbeiro_id, r.valor_total, 
                         r.observacoes, r.data_atendimento,
                         c.nome_completo as cliente_nome, b.nome as barbeiro_nome
                  FROM " . $this->table_name . " r
                  LEFT JOIN clientes c ON r.cliente_id = c.id
                  LEFT JOIN barbeiros b ON r.barbeiro_id = b.id
                  WHERE r.id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function getServicos() {
        $query = "SELECT rs.quantidade, rs.preco_unitario, rs.subtotal,
                         s.nome as servico_nome
                  FROM receita_servicos rs
                  LEFT JOIN servicos s ON rs.servico_id = s.id
                  WHERE rs.receita_id = ?
                  ORDER BY s.nome ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        return $stmt;
    }

    // Estatísticas do dashboard
    function getReceitaHoje() {
        $query = "SELECT COALESCE(SUM(valor_total), 0) as total
                  FROM " . $this->table_name . "
                  WHERE DATE(data_atendimento) = CURDATE()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    function getAtendimentosHoje() {
        $query = "SELECT COUNT(*) as total
                  FROM " . $this->table_name . "
                  WHERE DATE(data_atendimento) = CURDATE()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>