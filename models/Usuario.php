<?php

class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $nome;
    public $email;
    public $senha;
    public $nivel_acesso;
    public $ativo;
    public $data_cadastro;
    public $data_atualizacao;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para criar um novo usuário
    public function criar() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET nome=:nome, email=:email, senha=:senha, nivel_acesso=:nivel_acesso";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->senha = htmlspecialchars(strip_tags($this->senha));
        $this->nivel_acesso = htmlspecialchars(strip_tags($this->nivel_acesso));

        // Bind values
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":email", $this->email);
        
        // Hash da senha
        $senha_hash = password_hash($this->senha, PASSWORD_BCRYPT);
        $stmt->bindParam(":senha", $senha_hash);
        
        $stmt->bindParam(":nivel_acesso", $this->nivel_acesso);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Verificar se o e-mail já existe
    public function emailExiste() {
        $query = "SELECT id, nome, email, senha, nivel_acesso 
                FROM " . $this->table_name . " 
                WHERE email = ? 
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        $num = $stmt->rowCount();

        if($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->nome = $row['nome'];
            $this->email = $row['email'];
            $this->senha = $row['senha'];
            $this->nivel_acesso = $row['nivel_acesso'];
            
            return true;
        }

        return false;
    }

    // Método para autenticar usuário
    public function login() {
        if($this->emailExiste()) {
            if(password_verify($this->senha, $this->senha_hash)) {
                return true;
            }
        }
        return false;
    }

    // Método para listar todos os usuários
    public function listar() {
        $query = "SELECT id, nome, email, nivel_acesso, ativo, data_cadastro, data_atualizacao 
                FROM " . $this->table_name . " 
                ORDER BY nome";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Método para buscar um usuário pelo ID
    public function buscarPorId() {
        $query = "SELECT nome, email, nivel_acesso, ativo 
                FROM " . $this->table_name . " 
                WHERE id = ? 
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->nome = $row['nome'];
        $this->email = $row['email'];
        $this->nivel_acesso = $row['nivel_acesso'];
        $this->ativo = $row['ativo'];
    }

    // Método para atualizar um usuário
    public function atualizar() {
        $query = "UPDATE " . $this->table_name . " 
                SET nome=:nome, email=:email, 
                    nivel_acesso=:nivel_acesso, ativo=:ativo 
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->nivel_acesso = htmlspecialchars(strip_tags($this->nivel_acesso));
        $this->ativo = htmlspecialchars(strip_tags($this->ativo));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":nivel_acesso", $this->nivel_acesso);
        $stmt->bindParam(":ativo", $this->ativo);
        $stmt->bindParam(":id", $this->id);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Método para alterar a senha
    public function alterarSenha() {
        $query = "UPDATE " . $this->table_name . " 
                SET senha = :senha 
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->senha = htmlspecialchars(strip_tags($this->senha));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $senha_hash = password_hash($this->senha, PASSWORD_BCRYPT);
        $stmt->bindParam(":senha", $senha_hash);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Método para excluir um usuário
    public function excluir() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }
}

