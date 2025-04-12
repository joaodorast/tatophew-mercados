<?php
// config/database.php - Arquivo completo de conexão com o banco de dados
class Database {
    // Credenciais do banco de dados
    private $host = "localhost";
    private $db_name = "simple_stock";
    private $username = "root";
    private $password = "";
    public $conn;

    // Método para obter a conexão com o banco
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
}