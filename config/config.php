<?php
// config/config.php - Configurações gerais do sistema
// Nome do sistema
define('SYSTEM_NAME', 'Simple Stock');
define('SYSTEM_VERSION', '1.0.0');

// Configurações de diretórios
define('ROOT_DIR', dirname(__DIR__));
define('UPLOADS_DIR', ROOT_DIR . '/uploads');

// Configurações gerais
define('DEBUG_MODE', true);

// Controle de erros
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Função para formatar moeda
function formatarMoeda($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

// Função para formatar data
function formatarData($data) {
    return date('d/m/Y H:i', strtotime($data));
}

// Função para validar CNPJ
function validarCNPJ($cnpj) {
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    
    if (strlen($cnpj) != 14) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (preg_match('/^(\d)\1+$/', $cnpj)) {
        return false;
    }
    
    // Calcula dígitos verificadores
    for ($t = 12; $t < 14; $t++) {
        $d = 0;
        $c = 0;
        
        for ($m = $t - 7; $m >= 2; $m--, $c++) {
            $d += $cnpj[$c] * $m;
        }
        
        for ($m = 9; $m >= 2; $m--, $c++) {
            $d += $cnpj[$c] * $m;
        }
        
        $d = ((10 * $d) % 11) % 10;
        
        if ($cnpj[$t] != $d) {
            return false;
        }
    }
    
    return true;
}

// Função para gerar código de produto aleatório
function gerarCodigoProduto() {
    return 'PROD' . strtoupper(substr(md5(uniqid()), 0, 8));
}

// Função para log de atividades
function logAtividade($usuario_id, $acao, $detalhes = '') {
    $db = (new Database())->getConnection();
    
    $query = "INSERT INTO log_atividades (usuario_id, acao, detalhes, ip) 
              VALUES (:usuario_id, :acao, :detalhes, :ip)";
    
    $stmt = $db->prepare($query);
    
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':acao', $acao);
    $stmt->bindParam(':detalhes', $detalhes);
    $stmt->bindParam(':ip', $ip);
    
    return $stmt->execute();
}

// Verificação de permissões
function verificarPermissao($nivel_minimo) {
    if(!isset($_SESSION['usuario_id'])) {
        return false;
    }
    
    $nivel_usuario = $_SESSION['usuario_nivel'];
    
    // Admin tem acesso a tudo
    if($nivel_usuario == 'admin') {
        return true;
    }
    
    // Gerente tem acesso às permissões de gerente e operador
    if($nivel_usuario == 'gerente' && ($nivel_minimo == 'gerente' || $nivel_minimo == 'operador')) {
        return true;
    }
    
    // Operador só tem acesso às permissões de operador
    if($nivel_usuario == 'operador' && $nivel_minimo == 'operador') {
        return true;
    }
    
    return false;
}

// Função para validar e sanitizar entradas
function sanitizarInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    return $input;
}