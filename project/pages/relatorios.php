<?php
require_once '../auth/check_auth.php';
checkAuthentication();

require_once(__DIR__ . '/../config/database.php');
require_once '../includes/Barbeiro.php';

$database = new Database();
$db = $database->getConnection();
$barbeiro = new Barbeiro($db);

$stmt_barbeiros = $barbeiro->readAll();

// Processar relatório se solicitado
$relatorio_data = null;
if ($_POST && isset($_POST['gerar_relatorio'])) {
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
    $barbeiro_id = $_POST['barbeiro_id'] ?? null;
    
    // Query para receitas
    $query_receitas = "SELECT COALESCE(SUM(valor_total), 0) as total_receitas, COUNT(*) as total_atendimentos
                      FROM receitas r 
                      WHERE DATE(data_atendimento) BETWEEN :data_inicio AND :data_fim";
    
    if ($barbeiro_id) {
        $query_receitas .= " AND barbeiro_id = :barbeiro_id";
    }
    
    $stmt_receitas = $db->prepare($query_receitas);
    $stmt_receitas->bindParam(':data_inicio', $data_inicio);
    $stmt_receitas->bindParam(':data_fim', $data_fim);
    if ($barbeiro_id) {
        $stmt_receitas->bindParam(':barbeiro_id', $barbeiro_id);
    }
    $stmt_receitas->execute();
    $dados_receitas = $stmt_receitas->fetch(PDO::FETCH_ASSOC);
    
    // Query para despesas
    $query_despesas = "SELECT COALESCE(SUM(valor), 0) as total_despesas
                      FROM despesas d 
                      WHERE data_despesa BETWEEN :data_inicio AND :data_fim";
    
    if ($barbeiro_id) {
        $query_despesas .= " AND barbeiro_id = :barbeiro_id";
    }
    
    $stmt_despesas = $db->prepare($query_despesas);
    $stmt_despesas->bindParam(':data_inicio', $data_inicio);
    $stmt_despesas->bindParam(':data_fim', $data_fim);
    if ($barbeiro_id) {
        $stmt_despesas->bindParam(':barbeiro_id', $barbeiro_id);
    }
    $stmt_despesas->execute();
    $dados_despesas = $stmt_despesas->fetch(PDO::FETCH_ASSOC);
    
    // Query para detalhes dos atendimentos
    $query_detalhes = "SELECT r.data_atendimento, c.nome_completo as cliente, 
                             b.nome as barbeiro, r.valor_total,
                             GROUP_CONCAT(s.nome SEPARATOR ', ') as servicos
                      FROM receitas r
                      JOIN clientes c ON r.cliente_id = c.id
                      JOIN barbeiros b ON r.barbeiro_id = b.id
                      LEFT JOIN receita_servicos rs ON r.id = rs.receita_id
                      LEFT JOIN servicos s ON rs.servico_id = s.id
                      WHERE DATE(r.data_atendimento) BETWEEN :data_inicio AND :data_fim";
    
    if ($barbeiro_id) {
        $query_detalhes .= " AND r.barbeiro_id = :barbeiro_id";
    }
    
    $query_detalhes .= " GROUP BY r.id ORDER BY r.data_atendimento DESC";
    
    $stmt_detalhes = $db->prepare($query_detalhes);
    $stmt_detalhes->bindParam(':data_inicio', $data_inicio);
    $stmt_detalhes->bindParam(':data_fim', $data_fim);
    if ($barbeiro_id) {
        $stmt_detalhes->bindParam(':barbeiro_id', $barbeiro_id);
    }
    $stmt_detalhes->execute();
    
    $relatorio_data = [
        'data_inicio' => $data_inicio,
        'data_fim' => $data_fim,
        'barbeiro_id' => $barbeiro_id,
        'receitas' => $dados_receitas,
        'despesas' => $dados_despesas,
        'detalhes' => $stmt_detalhes->fetchAll(PDO::FETCH_ASSOC),
        'lucro' => $dados_receitas['total_receitas'] - $dados_despesas['total_despesas']
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Sistema Barbearia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Relatórios Financeiros</h1>
                </div>

                <!-- Formulário de Filtros -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Filtros do Relatório</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-md-4">
                                <label for="data_inicio" class="form-label">Data Início *</label>
                                <input type="date" class="form-control" id="data_inicio" name="data_inicio" 
                                       value="<?php echo $relatorio_data['data_inicio'] ?? date('Y-m-01'); ?>" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="data_fim" class="form-label">Data Fim *</label>
                                <input type="date" class="form-control" id="data_fim" name="data_fim" 
                                       value="<?php echo $relatorio_data['data_fim'] ?? date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="barbeiro_id" class="form-label">Barbeiro</label>
                                <select class="form-select" id="barbeiro_id" name="barbeiro_id">
                                    <option value="">Todos os Barbeiros</option>
                                    <?php
                                    while ($row = $stmt_barbeiros->fetch(PDO::FETCH_ASSOC)) {
                                        $selected = ($relatorio_data && $relatorio_data['barbeiro_id'] == $row['id']) ? 'selected' : '';
                                        echo "<option value='{$row['id']}' {$selected}>{$row['nome']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" name="gerar_relatorio" class="btn btn-primary">
                                    <i class="fas fa-chart-bar me-2"></i>Gerar Relatório
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($relatorio_data): ?>
                <!-- Resumo Financeiro -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total Receitas</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            R$ <?php echo number_format($relatorio_data['receitas']['total_receitas'], 2, ',', '.'); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Total Despesas</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            R$ <?php echo number_format($relatorio_data['despesas']['total_despesas'], 2, ',', '.'); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-credit-card fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card <?php echo $relatorio_data['lucro'] >= 0 ? 'border-left-primary' : 'border-left-warning'; ?> shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold <?php echo $relatorio_data['lucro'] >= 0 ? 'text-primary' : 'text-warning'; ?> text-uppercase mb-1">
                                            Lucro Líquido</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            R$ <?php echo number_format($relatorio_data['lucro'], 2, ',', '.'); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Atendimentos</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $relatorio_data['receitas']['total_atendimentos']; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-cut fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ações do Relatório -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Detalhes dos Atendimentos</h4>
                    <div>
                        <button class="btn btn-success me-2" onclick="exportarPDF()">
                            <i class="fas fa-file-pdf me-2"></i>Exportar PDF
                        </button>
                        <button class="btn btn-info" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Imprimir
                        </button>
                    </div>
                </div>

                <!-- Detalhes dos Atendimentos -->
                <div class="card" id="relatorio-detalhes">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            Período: <?php echo date('d/m/Y', strtotime($relatorio_data['data_inicio'])); ?> a <?php echo date('d/m/Y', strtotime($relatorio_data['data_fim'])); ?>
                            <?php if ($relatorio_data['barbeiro_id']): ?>
                                - Barbeiro Selecionado
                            <?php endif; ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (count($relatorio_data['detalhes']) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Data/Hora</th>
                                            <th>Cliente</th>
                                            <th>Barbeiro</th>
                                            <th>Serviços</th>
                                            <th>Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($relatorio_data['detalhes'] as $item): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y H:i', strtotime($item['data_atendimento'])); ?></td>
                                                <td><?php echo $item['cliente']; ?></td>
                                                <td><?php echo $item['barbeiro']; ?></td>
                                                <td><?php echo $item['servicos']; ?></td>
                                                <td>R$ <?php echo number_format($item['valor_total'], 2, ',', '.'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                <h5>Nenhum atendimento encontrado</h5>
                                <p class="text-muted">Não há registros para o período selecionado.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        function exportarPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Título
            doc.setFontSize(16);
            doc.text('Relatório Financeiro - Barbearia', 20, 20);
            
            // Período
            const periodo = 'Período: <?php echo $relatorio_data ? date('d/m/Y', strtotime($relatorio_data['data_inicio'])) . ' a ' . date('d/m/Y', strtotime($relatorio_data['data_fim'])) : ''; ?>';
            doc.setFontSize(12);
            doc.text(periodo, 20, 30);
            
            <?php if ($relatorio_data): ?>
            // Resumo
            doc.setFontSize(14);
            doc.text('Resumo Financeiro:', 20, 45);
            
            doc.setFontSize(10);
            const resumo = [
                'Total Receitas: R$ <?php echo number_format($relatorio_data['receitas']['total_receitas'], 2, ',', '.'); ?>',
                'Total Despesas: R$ <?php echo number_format($relatorio_data['despesas']['total_despesas'], 2, ',', '.'); ?>',
                'Lucro Líquido: R$ <?php echo number_format($relatorio_data['lucro'], 2, ',', '.'); ?>',
                'Total Atendimentos: <?php echo $relatorio_data['receitas']['total_atendimentos']; ?>'
            ];
            
            resumo.forEach((item, index) => {
                doc.text(item, 20, 55 + (index * 8));
            });
            <?php endif; ?>
            
            // Salvar
            doc.save('relatorio-barbearia.pdf');
        }
        
        // Estilos para impressão
        const style = document.createElement('style');
        style.textContent = `
            @media print {
                body * { visibility: hidden; }
                #relatorio-detalhes, #relatorio-detalhes * { visibility: visible; }
                #relatorio-detalhes { position: absolute; left: 0; top: 0; width: 100%; }
                .btn { display: none !important; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>