<?php
// api/produtos/criar.php (continuação)
if(
    !empty($data->codigo) &&
    !empty($data->nome) &&
    !empty($data->preco_custo) &&
    !empty($data->preco_venda)
){
    // Definir valores
    $produto->codigo = $data->codigo;
    $produto->nome = $data->nome;
    $produto->descricao = $data->descricao ?? "";
    $produto->preco_custo = $data->preco_custo;
    $produto->preco_venda = $data->preco_venda;
    $produto->quantidade_minima = $data->quantidade_minima ?? 5;
    $produto->quantidade_atual = $data->quantidade_atual ?? 0;
    $produto->unidade_medida = $data->unidade_medida ?? "UN";
    $produto->id_categoria = $data->id_categoria ?? null;
    $produto->id_fornecedor = $data->id_fornecedor ?? null;

    // Criar o produto
    if($produto->criar()){
        // Se tiver quantidade inicial, registrar como entrada no estoque
        if($produto->quantidade_atual > 0){
            $movimentacao = new Movimentacao($db);
            $movimentacao->id_produto = $db->lastInsertId();
            $movimentacao->tipo = "entrada";
            $movimentacao->quantidade = $produto->quantidade_atual;
            $movimentacao->motivo = "Estoque inicial";
            $movimentacao->id_usuario = $_SESSION['usuario_id'];
            $movimentacao->registrar();
        }

        http_response_code(201);
        echo json_encode(array("message" => "Produto criado com sucesso."));
    }
    else{
        http_response_code(503);
        echo json_encode(array("message" => "Não foi possível criar o produto."));
    }
}
else{
    http_response_code(400);
    echo json_encode(array("message" => "Dados incompletos."));
}
?>

<?php
// api/produtos/listar.php - API para listar produtos
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Verificar sessão
session_start();
if(!isset($_SESSION['usuario_id'])){
    http_response_code(401);
    echo json_encode(array("message" => "Não autorizado."));
    exit;
}

// incluir arquivos de conexão e modelo
include_once '../../config/database.php';
include_once '../../models/Produto.php';

// Instanciação da conexão com o banco
$database = new Database();
$db = $database->getConnection();

// Instanciação do objeto produto
$produto = new Produto($db);

// Obter produtos
$stmt = $produto->listar();
$num = $stmt->rowCount();

if($num > 0){
    $produtos_arr = array();
    $produtos_arr["registros"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);

        $produto_item = array(
            "id" => $id,
            "codigo" => $codigo,
            "nome" => $nome,
            "descricao" => $descricao,
            "preco_custo" => $preco_custo,
            "preco_venda" => $preco_venda,
            "quantidade_minima" => $quantidade_minima,
            "quantidade_atual" => $quantidade_atual,
            "unidade_medida" => $unidade_medida,
            "categoria_nome" => $categoria_nome,
            "fornecedor_nome" => $fornecedor_nome,
            "ativo" => $ativo
        );

        array_push($produtos_arr["registros"], $produto_item);
    }

    http_response_code(200);
    echo json_encode($produtos_arr);
}
else{
    http_response_code(404);
    echo json_encode(array("message" => "Nenhum produto encontrado."));
}
?>

<?php
// api/produtos/buscar.php - API para buscar produtos
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Verificar sessão
session_start();
if(!isset($_SESSION['usuario_id'])){
    http_response_code(401);
    echo json_encode(array("message" => "Não autorizado."));
    exit;
}

// incluir arquivos de conexão e modelo
include_once '../../config/database.php';
include_once '../../models/Produto.php';

// Instanciação da conexão com o banco
$database = new Database();
$db = $database->getConnection();

// Instanciação do objeto produto
$produto = new Produto($db);

// Verificar se foi enviado um termo de busca
$termo = isset($_GET['termo']) ? $_GET['termo'] : "";

if(!empty($termo)){
    $stmt = $produto->buscar($termo);
    $num = $stmt->rowCount();

    if($num > 0){
        $produtos_arr = array();
        $produtos_arr["registros"] = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);

            $produto_item = array(
                "id" => $id,
                "codigo" => $codigo,
                "nome" => $nome,
                "descricao" => $descricao,
                "preco_custo" => $preco_custo,
                "preco_venda" => $preco_venda,
                "quantidade_minima" => $quantidade_minima,
                "quantidade_atual" => $quantidade_atual,
                "unidade_medida" => $unidade_medida,
                "categoria_nome" => $categoria_nome,
                "fornecedor_nome" => $fornecedor_nome,
                "ativo" => $ativo
            );

            array_push($produtos_arr["registros"], $produto_item);
        }

        http_response_code(200);
        echo json_encode($produtos_arr);
    }
    else{
        http_response_code(404);
        echo json_encode(array("message" => "Nenhum produto encontrado."));
    }
}
else{
    http_response_code(400);
    echo json_encode(array("message" => "Termo de busca não informado."));
}
?>

<?php
// api/produtos/atualizar.php - API para atualizar produtos
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Verificar sessão
session_start();
if(!isset($_SESSION['usuario_id'])){
    http_response_code(401);
    echo json_encode(array("message" => "Não autorizado."));
    exit;
}

// incluir arquivos de conexão e modelo
include_once '../../config/database.php';
include_once '../../models/Produto.php';

// Instanciação da conexão com o banco
$database = new Database();
$db = $database->getConnection();

// Instanciação do objeto produto
$produto = new Produto($db);

// Obter ID do produto a ser editado
$data = json_decode(file_get_contents("php://input"));

// Verificar se ID foi enviado
if(!empty($data->id)){
    // Definir ID do produto
    $produto->id = $data->id;

    // Definir valores
    $produto->codigo = $data->codigo;
    $produto->nome = $data->nome;
    $produto->descricao = $data->descricao ?? "";
    $produto->preco_custo = $data->preco_custo;
    $produto->preco_venda = $data->preco_venda;
    $produto->quantidade_minima = $data->quantidade_minima;
    $produto->unidade_medida = $data->unidade_medida;
    $produto->id_categoria = $data->id_categoria ?? null;
    $produto->id_fornecedor = $data->id_fornecedor ?? null;
    $produto->ativo = $data->ativo ?? 1;

    // Atualizar o produto
    if($produto->atualizar()){
        http_response_code(200);
        echo json_encode(array("message" => "Produto atualizado com sucesso."));
    }
    else{
        http_response_code(503);
        echo json_encode(array("message" => "Não foi possível atualizar o produto."));
    }
}
else{
    http_response_code(400);
    echo json_encode(array("message" => "ID do produto não informado."));
}
?>

<?php
// api/movimentacoes/registrar.php - API para registrar movimentações
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Verificar sessão
session_start();
if(!isset($_SESSION['usuario_id'])){
    http_response_code(401);
    echo json_encode(array("message" => "Não autorizado."));
    exit;
}

// incluir arquivos de conexão e modelo
include_once '../../config/database.php';
include_once '../../models/Produto.php';
include_once '../../models/Movimentacao.php';

// Instanciação da conexão com o banco
$database = new Database();
$db = $database->getConnection();

// Obter dados enviados
$data = json_decode(file_get_contents("php://input"));

// Validar dados
if(
    !empty($data->id_produto) &&
    !empty($data->tipo) &&
    !empty($data->quantidade)
){
    // Instanciar produto para atualizar estoque
    $produto = new Produto($db);
    $produto->id = $data->id_produto;
    
    // Verificar se o produto existe
    $produto->buscarPorId();
    
    if(!$produto->nome){
        http_response_code(404);
        echo json_encode(array("message" => "Produto não encontrado."));
        exit;
    }
    
    // Validar se é possível realizar a saída
    if($data->tipo == "saida" && $data->quantidade > $produto->quantidade_atual){
        http_response_code(400);
        echo json_encode(array("message" => "Quantidade insuficiente em estoque."));
        exit;
    }
    
    // Instanciar movimentação
    $movimentacao = new Movimentacao($db);
    $movimentacao->id_produto = $data->id_produto;
    $movimentacao->tipo = $data->tipo;
    $movimentacao->quantidade = $data->quantidade;
    $movimentacao->motivo = $data->motivo ?? "";
    $movimentacao->nota_fiscal = $data->nota_fiscal ?? "";
    $movimentacao->id_usuario = $_SESSION['usuario_id'];
    
    // Iniciar transação
    $db->beginTransaction();
    
    try {
        // Registrar movimentação
        if($movimentacao->registrar()){
            // Atualizar estoque do produto
            if($produto->atualizarEstoque($data->quantidade, $data->tipo)){
                $db->commit();
                http_response_code(201);
                echo json_encode(array("message" => "Movimentação registrada com sucesso."));
            }
            else{
                $db->rollBack();
                http_response_code(503);
                echo json_encode(array("message" => "Não foi possível atualizar o estoque."));
            }
        }
        else{
            $db->rollBack();
            http_response_code(503);
            echo json_encode(array("message" => "Não foi possível registrar a movimentação."));
        }
    }
    catch(Exception $e){
        $db->rollBack();
        http_response_code(503);
        echo json_encode(array("message" => "Erro ao processar a operação: " . $e->getMessage()));
    }
}
else{
    http_response_code(400);
    echo json_encode(array("message" => "Dados incompletos."));
}
?>

<?php
// api/movimentacoes/listar.php - API para listar movimentações
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Verificar sessão
session_start();
if(!isset($_SESSION['usuario_id'])){
    http_response_code(401);
    echo json_encode(array("message" => "Não autorizado."));
    exit;
}

// incluir arquivos de conexão e modelo
include_once '../../config/database.php';
include_once '../../models/Movimentacao.php';

// Instanciação da conexão com o banco
$database = new Database();
$db = $database->getConnection();

// Instanciação do objeto movimentação
$movimentacao = new Movimentacao($db);

// Verificar se está filtrando por produto
if(isset($_GET['id_produto']) && !empty($_GET['id_produto'])){
    $movimentacao->id_produto = $_GET['id_produto'];
    $stmt = $movimentacao->listarPorProduto();
}
// Verificar se está filtrando por período
else if(isset($_GET['data_inicio']) && isset($_GET['data_fim']) && !empty($_GET['data_inicio']) && !empty($_GET['data_fim'])){
    $stmt = $movimentacao->listarPorPeriodo($_GET['data_inicio'], $_GET['data_fim']);
}
// Listar todos
else{
    $stmt = $movimentacao->listar();
}

$num = $stmt->rowCount();

if($num > 0){
    $movimentacoes_arr = array();
    $movimentacoes_arr["registros"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);

        $movimentacao_item = array(
            "id" => $id,
            "produto_nome" => $produto_nome,
            "tipo" => $tipo,
            "quantidade" => $quantidade,
            "motivo" => $motivo,
            "nota_fiscal" => $nota_fiscal,
            "usuario_nome" => $usuario_nome,
            "data_movimentacao" => $data_movimentacao
        );

        array_push($movimentacoes_arr["registros"], $movimentacao_item);
    }

    http_response_code(200);
    echo json_encode($movimentacoes_arr);
}
else{
    http_response_code(404);
    echo json_encode(array("message" => "Nenhuma movimentação encontrada."));
}
?>

<?php
// api/relatorios/estoque_minimo.php - API para relatório de estoque mínimo
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Verificar sessão
session_start();
if(!isset($_SESSION['usuario_id'])){
    http_response_code(401);
    echo json_encode(array("message" => "Não autorizado."));
    exit;
}

// incluir arquivos de conexão e modelo
include_once '../../config/database.php';
include_once '../../models/Produto.php';

// Instanciação da conexão com o banco
$database = new Database();
$db = $database->getConnection();

// Instanciação do objeto produto
$produto = new Produto($db);

// Obter produtos com estoque abaixo do mínimo
$stmt = $produto->estoqueMinimo();
$num = $stmt->rowCount();

if($num > 0){
    $produtos_arr = array();
    $produtos_arr["registros"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);

        $produto_item = array(
            "id" => $id,
            "codigo" => $codigo,
            "nome" => $nome,
            "quantidade_minima" => $quantidade_minima,
            "quantidade_atual" => $quantidade_atual,
            "categoria_nome" => $categoria_nome
        );

        array_push($produtos_arr["registros"], $produto_item);
    }

    http_response_code(200);
    echo json_encode($produtos_arr);
}
else{
    http_response_code(404);
    echo json_encode(array("message" => "Nenhum produto com estoque abaixo do mínimo."));
}
?>

<?php
// api/categorias/listar.php - API para listar categorias
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Verificar sessão
session_start();
if(!isset($_SESSION['usuario_id'])){
    http_response_code(401);
    echo json_encode(array("message" => "Não autorizado."));
    exit;
}

// incluir arquivos de conexão e modelo
include_once '../../config/database.php';

// Instanciação da conexão com o banco
$database = new Database();
$db = $database->getConnection();

// Query para listar categorias
$query = "SELECT id, nome, descricao FROM categorias WHERE ativo = 1 ORDER BY nome";
$stmt = $db->prepare($query);
$stmt->execute();
$num = $stmt->rowCount();

if($num > 0){
    $categorias_arr = array();
    $categorias_arr["registros"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);

        $categoria_item = array(
            "id" => $id,
            "nome" => $nome,
            "descricao" => $descricao
        );

        array_push($categorias_arr["registros"], $categoria_item);
    }

    http_response_code(200);
    echo json_encode($categorias_arr);
}
else{
    http_response_code(404);
    echo json_encode(array("message" => "Nenhuma categoria encontrada."));
}
?>

<?php
// api/fornecedores/listar.php - API para listar fornecedores
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Verificar sessão
session_start();
if(!isset($_SESSION['usuario_id'])){
    http_response_code(401);
    echo json_encode(array("message" => "Não autorizado."));
    exit;
}

// incluir arquivos de conexão e modelo
include_once '../../config/database.php';

// Instanciação da conexão com o banco
$database = new Database();
$db = $database->getConnection();

// Query para listar fornecedores
$query = "SELECT id, nome, cnpj, email, telefone FROM fornecedores WHERE ativo = 1 ORDER BY nome";
$stmt = $db->prepare($query);
$stmt->execute();
$num = $stmt->rowCount();

if($num > 0){
    $fornecedores_arr = array();
    $fornecedores_arr["registros"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);

        $fornecedor_item = array(
            "id" => $id,
            "nome" => $nome,
            "cnpj" => $cnpj,
            "email" => $email,
            "telefone" => $telefone
        );

        array_push($fornecedores_arr["registros"], $fornecedor_item);
    }

    http_response_code(200);
    echo json_encode($fornecedores_arr);
}
else{
    http_response_code(404);
    echo json_encode(array("message" => "Nenhum fornecedor encontrado."));
}
?>

<?php
// api/logout.php - API para logout
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Iniciar sessão
session_start();

// Destruir todas as variáveis de sessão
$_SESSION = array();

// Destruir a sessão
session_destroy();

// Resposta
echo json_encode(array("message" => "Logout realizado com sucesso."));
?>