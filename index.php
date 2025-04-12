<?php
// index.php - Página principal do sistema
// Iniciar a sessão
session_start();

// Incluir arquivos necessários
require_once 'config/config.php';
require_once 'config/database.php';

// Verificar se o usuário está logado
$logged_in = isset($_SESSION['usuario_id']);

// Se não estiver logado e não estiver na página de login, redirecionar
if(!$logged_in && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header('Location: login.php');
    exit;
}

// Definir a página atual
$pagina = isset($_GET['pagina']) ? $_GET['pagina'] : 'dashboard';

// Verificar se o arquivo da página existe
$arquivo_pagina = 'pages/' . $pagina . '.php';
if(!file_exists($arquivo_pagina)) {
    $arquivo_pagina = 'pages/404.php';
}

// Conexão com o banco de dados
$database = new Database();
$db = $database->getConnection();

// Título da página
$titulos = [
    'dashboard' => 'Painel de Controle',
    'produtos' => 'Gerenciar Produtos',
    'categorias' => 'Gerenciar Categorias',
    'fornecedores' => 'Gerenciar Fornecedores',
    'movimentacoes' => 'Movimentações de Estoque',
    'usuarios' => 'Gerenciar Usuários',
    'relatorios' => 'Relatórios',
    'configuracoes' => 'Configurações',
    '404' => 'Página não encontrada'
];

$titulo_pagina = isset($titulos[$pagina]) ? $titulos[$pagina] : 'Simple Stock';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SYSTEM_NAME . ' - ' . $titulo_pagina; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS personalizado -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php if($logged_in): ?>
    <!-- Cabeçalho -->
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="index.php">
            <i class="fas fa-box-open"></i> <?php echo SYSTEM_NAME; ?>
        </a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="w-100"></div>
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <span class="nav-link px-3 text-white">
                    <i class="fas fa-user"></i> <?php echo $_SESSION['usuario_nome']; ?>
                </span>
            </div>
        </div>
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <a class="nav-link px-3" href="api/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <!-- Menu lateral -->
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $pagina == 'dashboard' ? 'active' : ''; ?>" href="index.php?pagina=dashboard">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $pagina == 'produtos' ? 'active' : ''; ?>" href="index.php?pagina=produtos">
                                <i class="fas fa-boxes"></i> Produtos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $pagina == 'categorias' ? 'active' : ''; ?>" href="index.php?pagina=categorias">
                                <i class="fas fa-tags"></i> Categorias
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $pagina == 'fornecedores' ? 'active' : ''; ?>" href="index.php?pagina=fornecedores">
                                <i class="fas fa-truck"></i> Fornecedores
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $pagina == 'movimentacoes' ? 'active' : ''; ?>" href="index.php?pagina=movimentacoes">
                                <i class="fas fa-exchange-alt"></i> Movimentações
                            </a>
                        </li>
                        
                        <?php if($_SESSION['usuario_nivel'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $pagina == 'usuarios' ? 'active' : ''; ?>" href="index.php?pagina=usuarios">
                                <i class="fas fa-users"></i> Usuários
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <li class="nav-item">
                            <a class="nav-link <?php echo $pagina == 'relatorios' ? 'active' : ''; ?>" href="index.php?pagina=relatorios">
                                <i class="fas fa-chart-bar"></i> Relatórios
                            </a>
                        </li>
                        
                        <?php if($_SESSION['usuario_nivel'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $pagina == 'configuracoes' ? 'active' : ''; ?>" href="index.php?pagina=configuracoes">
                                <i class="fas fa-cog"></i> Configurações
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>

            <!-- Conteúdo principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo $titulo_pagina; ?></h1>
                </div>
                
                <?php include $arquivo_pagina; ?>
                
            </main>
        </div>
    </div>
<?php else: ?>
    <?php include 'pages/login.php'; ?>
<?php endif; ?>

<!-- Bootstrap JS e Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- JS personalizado -->
<script src="assets/js/script.js"></script>

</body>
</html>