<?php
require_once(__DIR__ . '/../config/database.php');

class Despesa {
    private $conn;
    private $table_name = "despesas";

    public $id;
    public $descricao;
    public $valor;
    public $barbeiro_id;
    public $data_despesa;

    public function __construct($db) {
        $this->conn = $db;
    }

    function create() {
        // Verificar se é despesa parcelada
        if (isset($_POST['despesa_parcelada']) && $_POST['despesa_parcelada'] == '1') {
            return $this->createParcelada();
        } else {
            return $this->createSimples();
        }
    }
    
    private function createSimples() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET descricao=:descricao, valor=:valor, barbeiro_id=:barbeiro_id, data_despesa=:data_despesa";
        
        $stmt = $this->conn->prepare($query);
        
        $this->descricao = htmlspecialchars(strip_tags($this->descricao));
        $this->valor = htmlspecialchars(strip_tags($this->valor));
        $this->barbeiro_id = $this->barbeiro_id == '' ? null : htmlspecialchars(strip_tags($this->barbeiro_id));
        $this->data_despesa = htmlspecialchars(strip_tags($this->data_despesa));
        
        $stmt->bindParam(":descricao", $this->descricao);
        $stmt->bindParam(":valor", $this->valor);
        $stmt->bindParam(":barbeiro_id", $this->barbeiro_id);
        $stmt->bindParam(":data_despesa", $this->data_despesa);
        
        return $stmt->execute();
    }
    
    private function createParcelada() {
    $quantidade_parcelas = intval($_POST['quantidade_parcelas']);
    $data_inicio = $_POST['data_inicio_parcela'] ?: $this->data_despesa;
    $valor_parcela = $this->valor / $quantidade_parcelas;
    
    try {
        $this->conn->beginTransaction();
        
        for ($i = 0; $i < $quantidade_parcelas; $i++) {
            $data_vencimento = date('Y-m-d', strtotime($data_inicio . " +{$i} months"));

            // 🔹 corrigido: soma feita fora da interpolação
            $parcela_num = $i + 1;
            $descricao_parcela = $this->descricao . " ({$parcela_num}/{$quantidade_parcelas})";
            
            $query = "INSERT INTO " . $this->table_name . " 
                     SET descricao=:descricao, valor=:valor, barbeiro_id=:barbeiro_id, data_despesa=:data_despesa";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":descricao", $descricao_parcela);
            $stmt->bindParam(":valor", $valor_parcela);
            $stmt->bindParam(":barbeiro_id", $this->barbeiro_id);
            $stmt->bindParam(":data_despesa", $data_vencimento);
            
            if (!$stmt->execute()) {
                $this->conn->rollBack();
                return false;
            }
        }
        
        $this->conn->commit();
        return true;
        
    } catch (Exception $e) {
        $this->conn->rollBack();
        return false;
    }
}

    function readAll() {
    $query = "SELECT d.id, d.descricao, d.valor, d.data_despesa, d.barbeiro_id,
                     COALESCE(b.nome, 'Despesa Geral') as barbeiro_nome
              FROM " . $this->table_name . " d
              LEFT JOIN barbeiros b ON d.barbeiro_id = b.id
              ORDER BY d.data_despesa DESC, d.id DESC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    
    return $stmt;
}

    function readOne() {
        $query = "SELECT id, descricao, valor, barbeiro_id, data_despesa
                 FROM " . $this->table_name . " 
                 WHERE id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->descricao = $row['descricao'];
            $this->valor = $row['valor'];
            $this->barbeiro_id = $row['barbeiro_id'];
            $this->data_despesa = $row['data_despesa'];
            return true;
        }
        return false;
    }

    function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET descricao = :descricao, valor = :valor, barbeiro_id = :barbeiro_id, data_despesa = :data_despesa
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->descricao = htmlspecialchars(strip_tags($this->descricao));
        $this->valor = htmlspecialchars(strip_tags($this->valor));
        $this->barbeiro_id = $this->barbeiro_id == '' ? null : htmlspecialchars(strip_tags($this->barbeiro_id));
        $this->data_despesa = htmlspecialchars(strip_tags($this->data_despesa));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(':descricao', $this->descricao);
        $stmt->bindParam(':valor', $this->valor);
        $stmt->bindParam(':barbeiro_id', $this->barbeiro_id);
        $stmt->bindParam(':data_despesa', $this->data_despesa);
        $stmt->bindParam(':id', $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    function getDespesasHoje() {
        $query = "SELECT COALESCE(SUM(valor), 0) as total
                  FROM " . $this->table_name . "
                  WHERE data_despesa = CURDATE()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>