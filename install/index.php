<?php
// install/index.php - Script de instalação do sistema
// Definir constantes básicas
define('SYSTEM_NAME', 'Simple Stock');
define('SYSTEM_VERSION', '1.0.0');

// Mostrar erros durante a instalação
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir configurações de banco de dados
require_once '../config/database.php';

// Função para executar um script SQL
function executarSQL($db, $sql) {
    try {
        $db->exec($sql);
        return true;
    } catch (PDOException $e) {
        return $e->getMessage();
    }
}

// Processar a instalação
$mensagem = '';
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Conectar ao banco de dados sem especificar o nome do banco
    try {
        $host = $_POST['host'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $dbname = $_POST['dbname'];
        
        $conn = new PDO("mysql:host=$host", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Criar o banco de dados se não existir
        $sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $conn->exec($sql);
        
        // Selecionar o banco de dados
        $conn->exec("USE $dbname");
        
        // Criar tabelas do sistema
        $sql_users = "
        CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            senha VARCHAR(255) NOT NULL,
            nivel_acesso ENUM('admin', 'gerente', 'operador') DEFAULT 'operador',
            ativo BOOLEAN DEFAULT TRUE,
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        executarSQL($conn, $sql_users);
        
        $sql_categorias = "
        CREATE TABLE IF NOT EXISTS categorias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(50) NOT NULL,
            descricao TEXT,
            ativo BOOLEAN DEFAULT TRUE,
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        executarSQL($conn, $sql_categorias);
        
        $sql_fornecedores = "
        CREATE TABLE IF NOT EXISTS fornecedores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            cnpj VARCHAR(20) UNIQUE,
            email VARCHAR(100),
            telefone VARCHAR(20),
            endereco TEXT,
            contato VARCHAR(100),
            ativo BOOLEAN DEFAULT TRUE,
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        executarSQL($conn, $sql_fornecedores);
        
        $sql_produtos = "
        CREATE TABLE IF NOT EXISTS produtos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo VARCHAR(50) UNIQUE,
            nome VARCHAR(100) NOT NULL,
            descricao TEXT,
            preco_custo DECIMAL(10,2) NOT NULL,
            preco_venda DECIMAL(10,2) NOT NULL,
            quantidade_minima INT DEFAULT 5,
            quantidade_atual INT DEFAULT 0,
            unidade_medida VARCHAR(10) DEFAULT 'UN',
            id_categoria INT,
            id_fornecedor INT,
            ativo BOOLEAN DEFAULT TRUE,
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_categoria) REFERENCES categorias(id),
            FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id)
        )";
        executarSQL($conn, $sql_produtos);
        
        $sql_movimentacoes = "
        CREATE TABLE IF NOT EXISTS movimentacoes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_produto INT NOT NULL,
            tipo ENUM('entrada', 'saida', 'ajuste') NOT NULL,
            quantidade INT NOT NULL,
            motivo TEXT,
            nota_fiscal VARCHAR(50),
            id_usuario INT NOT NULL,
            data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_produto) REFERENCES produtos(id),
            FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
        )";
        executarSQL($conn, $sql_movimentacoes);
        
        $sql_log = "
        CREATE TABLE IF NOT EXISTS log_atividades (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT,
            acao VARCHAR(100) NOT NULL,
            detalhes TEXT,
            ip VARCHAR(45),
            data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        )";
        executarSQL($conn, $sql_log);
        
        // Criar usuário administrador padrão (senha: admin123)
        $admin_nome = $_POST['admin_nome'];
        $admin_email = $_POST['admin_email'];
        $admin_senha = password_hash($_POST['admin_senha'], PASSWORD_BCRYPT);
        
        $sql_admin = "INSERT INTO usuarios (nome, email, senha, nivel_acesso) 
                    VALUES ('$admin_nome', '$admin_email', '$admin_senha', 'admin')";
        executarSQL($conn, $sql_admin);
        
        // Inserir categorias iniciais
        $sql_cat = "INSERT INTO categorias (nome, descricao) VALUES 
            ('Eletrônicos', 'Produtos eletrônicos em geral'),
            ('Alimentos', 'Produtos alimentícios'),
            ('Vestuário', 'Roupas e acessórios'),
            ('Papelaria', 'Material de escritório e escolar')";
        executarSQL($conn, $sql_cat);
        
        // Criar arquivo de configuração
        $config_content = '<?php
// config/database.php - Configurações de conexão com o banco de dados
class Database {
    private $host = "' . $host . '";
    private $db_name = "' . $dbname . '";
    private $username = "' . $username . '";
    private $password = "' . $password . '";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                )
            );
        } catch(PDOException $exception) {
            echo "Erro de conexão: " . $exception->getMessage();
        }

        return $this->conn;
    }
    
    // Método para executar uma consulta SQL simples
    public function executeQuery($sql) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt;
        } catch(PDOException $exception) {
            echo "Erro na execução da consulta: " . $exception->getMessage();
            return false;
        }
    }
    
    // Método para obter um único registro
    public function getRow($sql, $params = array()) {
        try {
            $stmt = $this->conn->prepare($sql);
            
            foreach($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Erro ao buscar registro: " . $exception->getMessage();
            return false;
        }
    }
    
    // Método para obter múltiplos registros
    public function getRows($sql, $params = array()) {
        try {
            $stmt = $this->conn->prepare($sql);
            
            foreach($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Erro ao buscar registros: " . $exception->getMessage();
            return false;
        }
    }
    
    // Método para inserir registro
    public function insert($table, $data) {
        try {
            $fields = implode(", ", array_keys($data));
            $placeholders = ":" . implode(", :", array_keys($data));
            
            $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";
            $stmt = $this->conn->prepare($sql);
            
            foreach($data as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }
            
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch(PDOException $exception) {
            echo "Erro ao inserir registro: " . $exception->getMessage();
            return false;
        }
    }
    
    // Método para atualizar registro
    public function update($table, $data, $where) {
        try {
            $fields = array();
            
            foreach($data as $key => $value) {
                $fields[] = "{$key} = :{$key}";
            }
            
            $fields = implode(", ", $fields);
            $where_clause = implode(" AND ", $where);
            
            $sql = "UPDATE {$table} SET {$fields} WHERE {$where_clause}";
            $stmt = $this->conn->prepare($sql);
            
            foreach($data as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }
            
            $stmt->execute();
            return $stmt->rowCount();
        } catch(PDOException $exception) {
            echo "Erro ao atualizar registro: " . $exception->getMessage();
            return false;
        }
    }
    
    // Método para excluir registro
    public function delete($table, $where) {
        try {
            $where_clause = implode(" AND ", $where);
            
            $sql = "DELETE FROM {$table} WHERE {$where_clause}";
            $stmt = $this->conn->prepare($sql);
            
            $stmt->execute();
            return $stmt->rowCount();
        } catch(PDOException $exception) {
            echo "Erro ao excluir registro: " . $exception->getMessage();
            return false;
        }
    }
}';
        
        // Salvar arquivo de configuração
        file_put_contents('../config/database.php', $config_content);
        
        $mensagem = 'Instalação concluída com sucesso!';
        $sucesso = true;
        
    } catch(PDOException $e) {
        $mensagem = 'Erro durante a instalação: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - <?php echo SYSTEM_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            padding-top: 40px;
            padding-bottom: 40px;
            background-color: #f5f5f5;
        }
        .form-install {
            width: 100%;
            max-width: 600px;
            padding: 15px;
            margin: auto;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-install">
            <div class="logo">
                <i class="fas fa-box-open fa-4x text-primary"></i>
                <h1><?php echo SYSTEM_NAME; ?></h1>
                <h2 class="h4">Instalação do Sistema</h2>
            </div>
            
            <?php if($mensagem): ?>
            <div class="alert alert-<?php echo $sucesso ? 'success' : 'danger'; ?>" role="alert">
                <?php echo $mensagem; ?>
                <?php if($sucesso): ?>
                <p class="mt-3">
                    <a href="../index.php" class="btn btn-primary">Ir para o sistema</a>
                </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if(!$sucesso): ?>
            <div class="card">
                <div class="card-body">
                    <form method="post" action="">
                        <h3 class="card-title">Configurações do Banco de Dados</h3>
                        <div class="mb-3">
                            <label for="host" class="form-label">Host</label>
                            <input type="text" class="form-control" id="host" name="host" value="localhost" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Usuário</label>
                            <input type="text" class="form-control" id="username" name="username" value="root" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                        <div class="mb-3">
                            <label for="dbname" class="form-label">Nome do Banco de Dados</label>
                            <input type="text" class="form-control" id="dbname" name="dbname" value="simple_stock" required>
                        </div>
                        
                        <h3 class="card-title mt-4">Configurações do Administrador</h3>
                        <div class="mb-3">
                            <label for="admin_nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="admin_nome" name="admin_nome" value="Administrador" required>
                        </div>
                        <div class="mb-3">
                            <label for="admin_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="admin_email" name="admin_email" value="admin@simplestock.com" required>
                        </div>
                        <div class="mb-3">
                            <label for="admin_senha" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="admin_senha" name="admin_senha" value="admin123" required>
                            <div class="form-text">A senha padrão é 'admin123', você pode alterá-la depois.</div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">Instalar Sistema</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            
            <p class="mt-5 mb-3 text-muted text-center">&copy; <?php echo date('Y'); ?> - <?php echo SYSTEM_NAME; ?> v<?php echo SYSTEM_VERSION; ?></p>
        </div>
    </div>
</body>
</html>