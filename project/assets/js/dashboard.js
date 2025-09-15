// JavaScript para o Dashboard
document.addEventListener('DOMContentLoaded', function() {
    carregarDashboard();
    carregarUltimosAtendimentos();
});

function carregarDashboard() {
    const barbeiroFiltro = document.getElementById('filtroBarbeiro')?.value || '';
    const url = `api/dashboard_data.php${barbeiroFiltro ? '?barbeiro_id=' + barbeiroFiltro : ''}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            document.getElementById('receitaHoje').textContent = 'R$ ' + formatarMoeda(data.receita_hoje);
            document.getElementById('atendimentosHoje').textContent = data.atendimentos_hoje;
            document.getElementById('despesasHoje').textContent = 'R$ ' + formatarMoeda(data.despesas_hoje);
            
            const lucro = data.receita_hoje - data.despesas_hoje;
            document.getElementById('lucroHoje').textContent = 'R$ ' + formatarMoeda(lucro);
            
            // Alterar cor do lucro baseado no valor
            const lucroElement = document.getElementById('lucroHoje');
            if (lucro >= 0) {
                lucroElement.parentElement.parentElement.className = 'card border-left-success shadow h-100 py-2';
            } else {
                lucroElement.parentElement.parentElement.className = 'card border-left-danger shadow h-100 py-2';
            }
        })
        .catch(error => {
            console.error('Erro ao carregar dados do dashboard:', error);
        });
}

function carregarUltimosAtendimentos() {
    const barbeiroFiltro = document.getElementById('filtroBarbeiro')?.value || '';
    const url = `api/ultimos_atendimentos.php${barbeiroFiltro ? '?barbeiro_id=' + barbeiroFiltro : ''}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('ultimosAtendimentos');
            tbody.innerHTML = '';
            
            data.forEach(atendimento => {
                const row = tbody.insertRow();
                row.innerHTML = `
                    <td>${formatarDataHora(atendimento.data_atendimento)}</td>
                    <td>${atendimento.cliente_nome}</td>
                    <td>${atendimento.barbeiro_nome}</td>
                    <td>${atendimento.servicos}</td>
                    <td>R$ ${formatarMoeda(atendimento.valor_total)}</td>
                `;
            });
        })
        .catch(error => {
            console.error('Erro ao carregar últimos atendimentos:', error);
        });
}

function formatarMoeda(valor) {
    return parseFloat(valor).toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatarDataHora(dataHora) {
    const data = new Date(dataHora);
    return data.toLocaleDateString('pt-BR') + ' ' + data.toLocaleTimeString('pt-BR', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Atualizar dashboard a cada 5 minutos
setInterval(carregarDashboard, 300000);