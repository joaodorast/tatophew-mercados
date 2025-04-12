
class Produto {
    private $conn;
    private $table_name = "produtos";

    public $id;
    public $codigo;
    public $nome;
    public $descricao;
    public $preco_custo;
    public $preco_venda;
    public $quantidade_minima;
    public $quantidade_atual;
    public $unidade_medida;
    public $id_categoria;
    public $id_fornecedor;
    public $ativo;
    public $data_cadastro;
    public $data_atualizacao;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para criar um novo produto
    public function criar() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (codigo, nome, descricao, preco_custo, preco_venda, 
                   quantidade_minima, quantidade_atual, unidade_medida, 
                   id_categoria, id_fornecedor) 
                  VALUES 
                  (:codigo, :nome, :descricao, :preco_custo, :preco_venda, 
                   :quantidade_minima, :quantidade_atual, :unidade_medida, 
                   :id_categoria, :id_fornecedor)";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->codigo = htmlspecialchars(strip_tags($this->codigo));
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->descricao = htmlspecialchars(strip_tags($this->descricao));
        $this->preco_custo = htmlspecialchars(strip_tags($this->preco_custo));
        $this->preco_venda = htmlspecialchars(strip_tags($this->preco_venda));
        $this->quantidade_minima = htmlspecialchars(strip_tags($this->quantidade_minima));
        $this->quantidade_atual = htmlspecialchars(strip_tags($this->quantidade_atual));
        $this->unidade_medida = htmlspecialchars(strip_tags($this->unidade_medida));
        $this->id_categoria = htmlspecialchars(strip_tags($this->id_categoria));
        $this->id_fornecedor = htmlspecialchars(strip_tags($this->id_fornecedor));

        // Bind values
        $stmt->bindParam(":codigo", $this->codigo);
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":descricao", $this->descricao);
        $stmt->bindParam(":preco_custo", $this->preco_custo);
        $stmt->bindParam(":preco_venda", $this->preco_venda);
        $stmt->bindParam(":quantidade_minima", $this->quantidade_minima);
        $stmt->bindParam(":quantidade_atual", $this->quantidade_atual);
        $stmt->bindParam(":unidade_medida", $this->unidade_medida);
        $stmt->bindParam(":id_categoria", $this->id_categoria);
        $stmt->bindParam(":id_fornecedor", $this->id_fornecedor);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Método para listar todos os produtos
    public function listar() {
        $query = "SELECT p.*, c.nome as categoria_nome, f.nome as fornecedor_nome 
                FROM " . $this->table_name . " p
                LEFT JOIN categorias c ON p.id_categoria = c.id
                LEFT JOIN fornecedores f ON p.id_fornecedor = f.id
                WHERE p.ativo = 1
                ORDER BY p.nome";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Método para buscar produtos com estoque abaixo do mínimo
    public function estoqueMinimo() {
        $query = "SELECT p.*, c.nome as categoria_nome 
                FROM " . $this->table_name . " p
                LEFT JOIN categorias c ON p.id_categoria = c.id
                WHERE p.quantidade_atual <= p.quantidade_minima
                AND p.ativo = 1
                ORDER BY p.nome";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Método para buscar um produto pelo ID
    public function buscarPorId() {
        $query = "SELECT p.*, c.nome as categoria_nome, f.nome as fornecedor_nome 
                FROM " . $this->table_name . " p
                LEFT JOIN categorias c ON p.id_categoria = c.id
                LEFT JOIN fornecedores f ON p.id_fornecedor = f.id
                WHERE p.id = ?
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->codigo = $row['codigo'];
        $this->nome = $row['nome'];
        $this->descricao = $row['descricao'];
        $this->preco_custo = $row['preco_custo'];
        $this->preco_venda = $row['preco_venda'];
        $this->quantidade_minima = $row['quantidade_minima'];
        $this->quantidade_atual = $row['quantidade_atual'];
        $this->unidade_medida = $row['unidade_medida'];
        $this->id_categoria = $row['id_categoria'];
        $this->id_fornecedor = $row['id_fornecedor'];
        $this->ativo = $row['ativo'];
        $this->categoria_nome = $row['categoria_nome'];
        $this->fornecedor_nome = $row['fornecedor_nome'];
    }

    // Método para atualizar um produto
    public function atualizar() {
        $query = "UPDATE " . $this->table_name . " 
                SET codigo=:codigo, nome=:nome, descricao=:descricao, 
                    preco_custo=:preco_custo, preco_venda=:preco_venda, 
                    quantidade_minima=:quantidade_minima, unidade_medida=:unidade_medida, 
                    id_categoria=:id_categoria, id_fornecedor=:id_fornecedor, ativo=:ativo 
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->codigo = htmlspecialchars(strip_tags($this->codigo));
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->descricao = htmlspecialchars(strip_tags($this->descricao));
        $this->preco_custo = htmlspecialchars(strip_tags($this->preco_custo));
        $this->preco_venda = htmlspecialchars(strip_tags($this->preco_venda));
        $this->quantidade_minima = htmlspecialchars(strip_tags($this->quantidade_minima));
        $this->unidade_medida = htmlspecialchars(strip_tags($this->unidade_medida));
        $this->id_categoria = htmlspecialchars(strip_tags($this->id_categoria));
        $this->id_fornecedor = htmlspecialchars(strip_tags($this->id_fornecedor));
        $this->ativo = htmlspecialchars(strip_tags($this->ativo));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(":codigo", $this->codigo);
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":descricao", $this->descricao);
        $stmt->bindParam(":preco_custo", $this->preco_custo);
        $stmt->bindParam(":preco_venda", $this->preco_venda);
        $stmt->bindParam(":quantidade_minima", $this->quantidade_minima);
        $stmt->bindParam(":unidade_medida", $this->unidade_medida);
        $stmt->bindParam(":id_categoria", $this->id_categoria);
        $stmt->bindParam(":id_fornecedor", $this->id_fornecedor);
        $stmt->bindParam(":ativo", $this->ativo);
        $stmt->bindParam(":id", $this->id);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Método para atualizar a quantidade do produto (movimentação)
    public function atualizarEstoque($quantidade, $tipo) {
        if ($tipo == 'entrada') {
            $query = "UPDATE " . $this->table_name . " 
                    SET quantidade_atual = quantidade_atual + :quantidade 
                    WHERE id = :id";
        } else if ($tipo == 'saida') {
            $query = "UPDATE " . $this->table_name . " 
                    SET quantidade_atual = quantidade_atual - :quantidade 
                    WHERE id = :id AND quantidade_atual >= :quantidade";
        } else { // ajuste
            $query = "UPDATE " . $this->table_name . " 
                    SET quantidade_atual = :quantidade 
                    WHERE id = :id";
        }

        $stmt = $this->conn->prepare($query);
        
        $quantidade = htmlspecialchars(strip_tags($quantidade));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(":quantidade", $quantidade);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute() && $stmt->rowCount() > 0) {
            return true;
        }
        
        return false;
    }

    // Método para excluir um produto (exclusão lógica)
    public function excluir() {
        $query = "UPDATE " . $this->table_name . " SET ativo = 0 WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Método para buscar produtos por nome ou código
    public function buscar($termo) {
        $query = "SELECT p.*, c.nome as categoria_nome, f.nome as fornecedor_nome 
                FROM " . $this->table_name . " p
                LEFT JOIN categorias c ON p.id_categoria = c.id
                LEFT JOIN fornecedores f ON p.id_fornecedor = f.id
                WHERE p.ativo = 1 AND (p.nome LIKE ? OR p.codigo LIKE ?)
                ORDER BY p.nome";

        $stmt = $this->conn->prepare($query);
        
        $termo = "%{$termo}%";
        $stmt->bindParam(1, $termo);
        $stmt->bindParam(2, $termo);
        
        $stmt->execute();
        
        return $stmt;
    }
}