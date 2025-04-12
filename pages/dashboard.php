<?php
// pages/dashboard.php - Dashboard do sistema
// Não permitir acesso direto
if (!defined('SYSTEM_NAME')) {
    header('Location: ../index.php');
    exit;
}

// Consultas para o dashboard
// Total de produtos
$sql_total_produtos = "SELECT COUNT(*) as total FROM produtos WHERE ativo = 1";
$total_produtos = $db->getRow($sql_total_produtos)['total'] ?? 0;

// Total de produtos em estoque baixo
$sql_estoque_baixo = "SELECT COUNT(*) as total FROM produtos WHERE quantidade_atual <= quantidade_minima AND ativo = 1";
$total_estoque_baixo = $db->getRow($sql_estoque_baixo)['total'] ?? 0;

// Valor total do estoque
$sql_valor_estoque = "SELECT SUM(quantidade_atual * preco_venda) as total FROM produtos WHERE ativo = 1";
$valor_total_estoque = $db->getRow($sql_valor_estoque)['total'] ?? 0;

// Produtos mais vendidos
$sql_mais_vendidos = "SELECT p.nome, SUM(v.quantidade) as total_vendido 
                      FROM vendas_itens v 
                      JOIN produtos p ON v.produto_id = p.id 
                      WHERE p.ativo = 1 
                      GROUP BY p.id 
                      ORDER BY total_vendido DESC 
                      LIMIT 5";
$produtos_mais_vendidos = $db->getRows($sql_mais_vendidos) ?? [];

// Vendas dos últimos 30 dias
$sql_vendas_mes = "SELECT SUM(valor_total) as total FROM vendas 
                  WHERE data_venda >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
$vendas_mes = $db->getRow($sql_vendas_mes)['total'] ?? 0;

// Últimas movimentações de estoque
$sql_ultimas_movimentacoes = "SELECT p.nome, m.tipo, m.quantidade, m.data_movimentacao 
                             FROM estoque_movimentacoes m 
                             JOIN produtos p ON m.produto_id = p.id 
                             ORDER BY m.data_movimentacao DESC 
                             LIMIT 10";
$ultimas_movimentacoes = $db->getRows($sql_ultimas_movimentacoes) ?? [];
?>

<div class="dashboard-container">
    <h1>Dashboard</h1>
    <p>Bem-vindo ao painel de controle do sistema, <?php echo $_SESSION['usuario_nome'] ?? 'Usuário'; ?>!</p>
    
    <!-- Cards informativos -->
    <div class="dashboard-cards">
        <div class="card bg-primary">
            <div class="card-body">
                <h5 class="card-title">Total de Produtos</h5>
                <h2><?php echo number_format($total_produtos, 0, ',', '.'); ?></h2>
                <p><a href="index.php?page=produtos" class="text-white">Ver produtos</a></p>
            </div>
        </div>
        
        <div class="card <?php echo $total_estoque_baixo > 0 ? 'bg-danger' : 'bg-success'; ?>">
            <div class="card-body">
                <h5 class="card-title">Estoque Baixo</h5>
                <h2><?php echo number_format($total_estoque_baixo, 0, ',', '.'); ?></h2>
                <p><a href="index.php?page=relatorios&type=estoque_baixo" class="text-white">Ver detalhes</a></p>
            </div>
        </div>
        
        <div class="card bg-info">
            <div class="card-body">
                <h5 class="card-title">Valor do Estoque</h5>
                <h2>R$ <?php echo number_format($valor_total_estoque, 2, ',', '.'); ?></h2>
                <p><a href="index.php?page=relatorios&type=valor_estoque" class="text-white">Ver relatório</a></p>
            </div>
        </div>
        
        <div class="card bg-success">
            <div class="card-body">
                <h5 class="card-title">Vendas (30 dias)</h5>
                <h2>R$ <?php echo number_format($vendas_mes, 2, ',', '.'); ?></h2>
                <p><a href="index.php?page=relatorios&type=vendas_periodo" class="text-white">Ver relatório</a></p>
            </div>
        </div>
    </div>
    
    <!-- Gráficos e tabelas -->
    <div class="dashboard-charts">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Produtos Mais Vendidos</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($produtos_mais_vendidos) > 0): ?>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th>Quantidade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($produtos_mais_vendidos as $produto): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                        <td><?php echo number_format($produto['total_vendido'], 0, ',', '.'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-center">Nenhum produto vendido ainda.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Últimas Movimentações de Estoque</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($ultimas_movimentacoes) > 0): ?>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th>Tipo</th>
                                        <th>Qtd</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimas_movimentacoes as $mov): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($mov['nome']); ?></td>
                                        <td>
                                            <?php if ($mov['tipo'] == 'entrada'): ?>
                                                <span class="badge bg-success">Entrada</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Saída</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo number_format($mov['quantidade'], 0, ',', '.'); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($mov['data_movimentacao'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-center">Nenhuma movimentação de estoque registrada.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ações rápidas -->
    <div class="dashboard-actions mt-4">
        <div class="card">
            <div class="card-header">
                <h5>Ações Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="index.php?page=produtos_add" class="btn btn-primary w-100">
                            <i class="fas fa-plus-circle"></i> Novo Produto
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="index.php?page=vendas_add" class="btn btn-success w-100">
                            <i class="fas fa-shopping-cart"></i> Nova Venda
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="index.php?page=estoque_entrada" class="btn btn-info w-100">
                            <i class="fas fa-box"></i> Entrada de Estoque
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="index.php?page=relatorios" class="btn btn-secondary w-100">
                            <i class="fas fa-chart-bar"></i> Relatórios
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($total_estoque_baixo > 0): ?>
    <!-- Alerta de estoque baixo -->
    <div class="alert alert-warning mt-4" role="alert">
        <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Atenção!</h4>
        <p>Existem <?php echo $total_estoque_baixo; ?> produtos com estoque abaixo do mínimo. Verifique o relatório de estoque para mais detalhes.</p>
        <hr>
        <p class="mb-0">
            <a href="index.php?page=relatorios&type=estoque_baixo" class="btn btn-sm btn-warning">Ver produtos com estoque baixo</a>
        </p>
    </div>
    <?php endif; ?>
</div>

<!-- Script para inicializar gráficos se necessário -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Aqui você pode adicionar inicialização de gráficos se estiver usando bibliotecas como Chart.js
    // Por exemplo:
    // const ctx = document.getElementById('graficoVendas').getContext('2d');
    // const meuGrafico = new Chart(ctx, { ... });
});
</script>
<body>
 <html>