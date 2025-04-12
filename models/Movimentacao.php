class Movimentacao {
    private $conn;
    private $table_name = "movimentacoes";

    public $id;
    public $id_produto;
    public $tipo;
    public $quantidade;
    public $motivo;
    public $nota_fiscal;
    public $id_usuario;
    public $data_movimentacao;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para registrar uma movimentação
    public function registrar() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_produto, tipo, quantidade, motivo, nota_fiscal, id_usuario) 
                  VALUES 
                  (:id_produto, :tipo, :quantidade, :motivo, :nota_fiscal, :id_usuario)";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->id_produto = htmlspecialchars(strip_tags($this->id_produto));
        $this->tipo = htmlspecialchars(strip_tags($this->tipo));
        $this->quantidade = htmlspecialchars(strip_tags($this->quantidade));
        $this->motivo = htmlspecialchars(strip_tags($this->motivo));
        $this->nota_fiscal = htmlspecialchars(strip_tags($this->nota_fiscal));
        $this->id_usuario = htmlspecialchars(strip_tags($this->id_usuario));

        // Bind values
        $stmt->bindParam(":id_produto", $this->id_produto);
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":quantidade", $this->quantidade);
        $stmt->bindParam(":motivo", $this->motivo);
        $stmt->bindParam(":nota_fiscal", $this->nota_fiscal);
        $stmt->bindParam(":id_usuario", $this->id_usuario);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Método para listar todas as movimentações
    public function listar() {
        $query = "SELECT m.*, p.nome as produto_nome, u.nome as usuario_nome 
                FROM " . $this->table_name . " m
                JOIN produtos p ON m.id_produto = p.id
                JOIN usuarios u ON m.id_usuario = u.id
                ORDER BY m.data_movimentacao DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Método para listar movimentações de um produto específico
    public function listarPorProduto() {
        $query = "SELECT m.*, p.nome as produto_nome, u.nome as usuario_nome 
                FROM " . $this->table_name . " m
                JOIN produtos p ON m.id_produto = p.id
                JOIN usuarios u ON m.id_usuario = u.id
                WHERE m.id_produto = ?
                ORDER BY m.data_movimentacao DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_produto);
        $stmt->execute();

        return $stmt;
    }

    // Método para listar movimentações por período
    public function listarPorPeriodo($data_inicio, $data_fim) {
        $query = "SELECT m.*, p.nome as produto_nome, u.nome as usuario_nome 
                FROM " . $this->table_name . " m
                JOIN produtos p ON m.id_produto = p.id
                JOIN usuarios u ON m.id_usuario = u.id
                WHERE m.data_movimentacao BETWEEN :data_inicio AND :data_fim
                ORDER BY m.data_movimentacao DESC";

        $stmt = $this->conn->prepare($query);
        
        $data_inicio = htmlspecialchars(strip_tags($data_inicio)) . " 00:00:00";
        $data_fim = htmlspecialchars(strip_tags($data_fim)) . " 23:59:59";
        
        $stmt->bindParam(":data_inicio", $data_inicio);
        $stmt->bindParam(":data_fim", $data_fim);
        
        $stmt->execute();
        
        return $stmt;
    }
}