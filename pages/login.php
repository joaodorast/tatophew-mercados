<?php

if (!defined('SYSTEM_NAME')) {
    header('Location: ../index.php');
    exit;
}

// Processar o formulário de login
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Incluir arquivos necessários
    include_once 'config/database.php';
    include_once 'models/Usuario.php';
    
    // Obter conexão com o banco
    $database = new Database();
    $db = $database->getConnection();
    
    // Instanciar usuário
    $usuario = new Usuario($db);
    
    // Definir valores
    $usuario->email = sanitizarInput($_POST['email']);
    $usuario->senha = $_POST['senha'];
    
    // Verificar se o usuário existe
    if ($usuario->emailExiste()) {
        // Verificar a senha
        if (password_verify($usuario->senha, $usuario->senha)) {
            // Senha correta, criar sessão
            $_SESSION['usuario_id'] = $usuario->id;
            $_SESSION['usuario_nome'] = $usuario->nome;
            $_SESSION['usuario_email'] = $usuario->email;
            $_SESSION['usuario_nivel'] = $usuario->nivel_acesso;
            
            // Registrar atividade de login
            logAtividade($usuario->id, 'login', 'Login no sistema');
            
            // Redirecionar para a página inicial
            header('Location: index.php');
            exit;
        } else {
            $error = 'Email ou senha incorretos.';
        }
    } else {
        $error = 'Email ou senha incorretos.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SYSTEM_NAME; ?> - Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS personalizado de login -->
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            background-color: #f5f5f5;
        }
        .form-signin {
            width: 100%;
            max-width: 330px;
            padding: 15px;
            margin: auto;
        }
        .form-signin .form-floating:focus-within {
            z-index: 2;
        }
        .form-signin input[type="email"] {
            margin-bottom: -1px;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
        }
        .form-signin input[type="password"] {
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
        .logo {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="text-center">
    <main class="form-signin">
        <form method="post" action="">
            <i class="fas fa-box-open fa-4x mb-4 text-primary"></i>
            <h1 class="h3 mb-3 fw-normal"><?php echo SYSTEM_NAME; ?></h1>
            <h2 class="h5 mb-3 fw-normal">Controle de Estoque</h2>
            
            <?php if($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="nome@exemplo.com" required>
                <label for="email">Email</label>
            </div>
            <div class="form-floating">
                <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required>
                <label for="senha">Senha</label>
            </div>
            
            <button class="w-100 btn btn-lg btn-primary" type="submit">Entrar</button>
            <p class="mt-5 mb-3 text-muted">&copy; <?php echo date('Y'); ?> - <?php echo SYSTEM_NAME; ?> v<?php echo SYSTEM_VERSION; ?></p>
        </form>
    </main>
</body>
</html>