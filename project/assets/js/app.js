// Sistema de Barbearia - JavaScript Principal
class SistemaBarbearia {
    constructor() {
        // Verificar autenticação
        this.checkAuthentication();
        
        this.clientes = JSON.parse(localStorage.getItem('clientes')) || [];
        this.servicos = JSON.parse(localStorage.getItem('servicos')) || [
            { id: 1, nome: 'Corte Simples', duracao: 30, preco: 25.00 },
            { id: 2, nome: 'Corte + Barba', duracao: 45, preco: 35.00 },
            { id: 3, nome: 'Apenas Barba', duracao: 20, preco: 15.00 },
            { id: 4, nome: 'Sobrancelha', duracao: 15, preco: 10.00 },
            { id: 5, nome: 'Pigmentação', duracao: 60, preco: 80.00 }
        ];
        this.atendimentos = JSON.parse(localStorage.getItem('atendimentos')) || [];
        this.despesas = JSON.parse(localStorage.getItem('despesas')) || [];
        this.usuarios = JSON.parse(localStorage.getItem('usuarios')) || [
            { 
                id: 1, 
                nome: 'Administrador', 
                tipo_login: 'Local', 
                ultimo_acesso: new Date().toISOString(),
                avatar: null 
            }
        ];
        this.barbeiros = [
            { id: 1, nome: 'Barbeiro 1' },
            { id: 2, nome: 'Barbeiro 2' }
        ];
        
        this.init();
    }

    checkAuthentication() {
        if (localStorage.getItem('userLoggedIn') !== 'true') {
            window.location.href = 'login.html';
            return;
        }
        
        // Atualizar nome do usuário na interface
        const userName = localStorage.getItem('userName') || 'Admin';
        const userAvatar = localStorage.getItem('userAvatar');
        const userNameElements = document.querySelectorAll('#userName');
        const userAvatarElements = document.querySelectorAll('#userAvatar');
        
        userNameElements.forEach(element => {
            if (element) {
                element.textContent = userName;
            }
        });
        
        userAvatarElements.forEach(element => {
            if (element && userAvatar) {
                element.src = userAvatar;
                element.style.display = 'inline-block';
                // Esconder ícone padrão se houver avatar
                const defaultIcon = element.parentElement.querySelector('.fas.fa-user-circle');
                if (defaultIcon) {
                    defaultIcon.style.display = 'none';
                }
            }
        });
    }

    // Método para atualizar nome do usuário após login
    atualizarNomeUsuario() {
        const userName = localStorage.getItem('userName') || 'Admin';
        const userAvatar = localStorage.getItem('userAvatar');
        const userNameElements = document.querySelectorAll('#userName');
        const userAvatarElements = document.querySelectorAll('#userAvatar');
        
        userNameElements.forEach(element => {
            if (element) {
                element.textContent = userName;
            }
        });
        
        userAvatarElements.forEach(element => {
            if (element && userAvatar) {
                element.src = userAvatar;
                element.style.display = 'inline-block';
            }
        });
    }

    // Método para debug dos filtros
    debugFiltros() {
        console.log('Atendimentos:', this.atendimentos);
        console.log('Despesas:', this.despesas);
        const barbeiroFiltro = document.getElementById('filtroBarbeiro')?.value;
        console.log('Filtro barbeiro:', barbeiroFiltro);
        
        if (barbeiroFiltro) {
            const atendimentosFiltrados = this.atendimentos.filter(a => a.barbeiroId == barbeiroFiltro);
            const despesasFiltradas = this.despesas.filter(d => {
                if (!d.barbeiroId) return true;
                return d.barbeiroId == barbeiroFiltro;
            });
            console.log('Atendimentos filtrados:', atendimentosFiltrados);
            console.log('Despesas filtradas:', despesasFiltradas);
        }
    }

    init() {
        this.atualizarDashboard();
        this.carregarClientes();
        this.carregarServicos();
        this.carregarAtendimentos();
        this.carregarDespesas();
        this.carregarUsuarios();
        this.configurarDatasDefault();
        this.configurarEventos();
    }

    configurarEventos() {
        // Configurar eventos dos modais
        document.getElementById('clienteModal').addEventListener('hidden.bs.modal', () => {
            document.getElementById('clienteForm').reset();
        });

        document.getElementById('servicoModal').addEventListener('hidden.bs.modal', () => {
            document.getElementById('servicoForm').reset();
        });

        document.getElementById('atendimentoModal').addEventListener('hidden.bs.modal', () => {
            document.getElementById('atendimentoForm').reset();
            document.getElementById('valorTotal').textContent = 'R$ 0,00';
        });

        document.getElementById('despesaModal').addEventListener('hidden.bs.modal', () => {
            document.getElementById('despesaForm').reset();
            this.configurarDatasDefault();
        });
    }

    configurarDatasDefault() {
        const hoje = new Date().toISOString().split('T')[0];
        const agora = new Date().toTimeString().split(' ')[0].substring(0, 5);
        
        document.getElementById('atendimentoData').value = hoje;
        document.getElementById('atendimentoHora').value = agora;
        document.getElementById('despesaData').value = hoje;
        document.getElementById('dataInicio').value = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];
        document.getElementById('dataFim').value = hoje;
    }

    // Navegação entre páginas
    showPage(pageId) {
        // Esconder todas as páginas
        document.querySelectorAll('.page-content').forEach(page => {
            page.style.display = 'none';
        });
        
        // Mostrar página selecionada
        document.getElementById(pageId + '-page').style.display = 'block';
        
        // Atualizar menu ativo
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelector(`[data-page="${pageId}"]`).classList.add('active');

        // Carregar dados específicos da página
        if (pageId === 'atendimentos') {
            this.carregarClientesSelect();
            this.carregarServicosCheckbox();
        }
    }

    // Funções de formatação
    formatarMoeda(valor) {
        return parseFloat(valor || 0).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    formatarDataHora(dataHora) {
        return new Date(dataHora).toLocaleString('pt-BR');
    }

    formatarData(data) {
        return new Date(data).toLocaleDateString('pt-BR');
    }

    // Dashboard
    atualizarDashboard() {
        const hoje = new Date();
        const primeiroDiaDoMes = new Date(hoje.getFullYear(), hoje.getMonth(), 1).toISOString().split('T')[0];
        const ultimoDiaDoMes = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0).toISOString().split('T')[0];
        
        const barbeiroFiltro = document.getElementById('filtroBarbeiro')?.value;
        
        // Calcular receitas do mês
        let atendimentosFiltrados = this.atendimentos
            .filter(a => {
                const dataAtendimento = a.data.split('T')[0];
                return dataAtendimento >= primeiroDiaDoMes && dataAtendimento <= ultimoDiaDoMes;
            });
        
        if (barbeiroFiltro) {
            atendimentosFiltrados = atendimentosFiltrados.filter(a => a.barbeiroId == barbeiroFiltro);
        }
        
        const receitasHoje = atendimentosFiltrados
            .reduce((total, a) => total + a.valorTotal, 0);
        
        // Calcular despesas do mês
        let despesasFiltradas = this.despesas
            .filter(d => {
                return d.data >= primeiroDiaDoMes && d.data <= ultimoDiaDoMes;
            });
        
        if (barbeiroFiltro) {
            despesasFiltradas = despesasFiltradas.filter(d => {
                // Se não tem barbeiro específico (despesa geral), incluir sempre
                if (!d.barbeiroId) return true;
                // Se tem barbeiro específico, comparar com o filtro
                return d.barbeiroId == barbeiroFiltro;
            });
        }
        
        const despesasHoje = despesasFiltradas
            .reduce((total, d) => total + d.valor, 0);
        
        // Calcular atendimentos do mês
        const atendimentosHoje = atendimentosFiltrados.length;
        
        // Calcular lucro
        const lucroHoje = receitasHoje - despesasHoje;
        
        // Atualizar interface
        document.getElementById('receitaHoje').textContent = 'R$ ' + this.formatarMoeda(receitasHoje);
        document.getElementById('atendimentosHoje').textContent = atendimentosHoje;
        document.getElementById('despesasHoje').textContent = 'R$ ' + this.formatarMoeda(despesasHoje);
        document.getElementById('lucroHoje').textContent = 'R$ ' + this.formatarMoeda(lucroHoje);
        
        // Carregar últimos atendimentos
        this.carregarUltimosAtendimentos();
    }
    
    filtrarDashboard() {
        this.atualizarDashboard();
    }

    carregarUltimosAtendimentos() {
        const tbody = document.getElementById('ultimosAtendimentos');
        tbody.innerHTML = '';
        
        const ultimosAtendimentos = this.atendimentos
            .sort((a, b) => new Date(b.data) - new Date(a.data))
            .slice(0, 10);
        
        ultimosAtendimentos.forEach(atendimento => {
            const cliente = this.clientes.find(c => c.id === atendimento.clienteId);
            const barbeiro = this.barbeiros.find(b => b.id === atendimento.barbeiroId);
            const servicosNomes = atendimento.servicos.map(s => {
                const servico = this.servicos.find(srv => srv.id === s);
                return servico ? servico.nome : '';
            }).join(', ');
            
            const row = tbody.insertRow();
            row.innerHTML = `
                <td>${this.formatarDataHora(atendimento.data)}</td>
                <td>${cliente ? cliente.nome : 'Cliente não encontrado'}</td>
                <td>${barbeiro ? barbeiro.nome : 'Barbeiro não encontrado'}</td>
                <td>${servicosNomes}</td>
                <td>R$ ${this.formatarMoeda(atendimento.valorTotal)}</td>
            `;
        });
    }

    // Clientes
    carregarClientes() {
        const tbody = document.getElementById('clientesTable');
        tbody.innerHTML = '';
        
        this.clientes.forEach(cliente => {
            const row = tbody.insertRow();
            row.innerHTML = `
                <td>${cliente.id}</td>
                <td>${cliente.nome}</td>
                <td>${cliente.telefone}</td>
                <td>${this.formatarDataHora(cliente.criadoEm)}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="sistema.editarCliente(${cliente.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="sistema.excluirCliente(${cliente.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
        });
    }

    salvarCliente() {
        const nome = document.getElementById('clienteNome').value;
        const telefone = document.getElementById('clienteTelefone').value;
        
        if (!nome || !telefone) {
            alert('Preencha todos os campos obrigatórios!');
            return;
        }
        
        const cliente = {
            id: Date.now(),
            nome: nome,
            telefone: telefone,
            criadoEm: new Date().toISOString()
        };
        
        this.clientes.push(cliente);
        localStorage.setItem('clientes', JSON.stringify(this.clientes));
        
        this.carregarClientes();
        bootstrap.Modal.getInstance(document.getElementById('clienteModal')).hide();
        
        this.mostrarAlerta('Cliente cadastrado com sucesso!', 'success');
    }

    editarCliente(id) {
        const cliente = this.clientes.find(c => c.id === id);
        if (cliente) {
            document.getElementById('clienteNome').value = cliente.nome;
            document.getElementById('clienteTelefone').value = cliente.telefone;
            
            // Alterar comportamento do botão salvar
            const btnSalvar = document.querySelector('#clienteModal .btn-primary');
            btnSalvar.onclick = () => this.atualizarCliente(id);
            
            new bootstrap.Modal(document.getElementById('clienteModal')).show();
        }
    }

    atualizarCliente(id) {
        const nome = document.getElementById('clienteNome').value;
        const telefone = document.getElementById('clienteTelefone').value;
        
        const clienteIndex = this.clientes.findIndex(c => c.id === id);
        if (clienteIndex !== -1) {
            this.clientes[clienteIndex].nome = nome;
            this.clientes[clienteIndex].telefone = telefone;
            
            localStorage.setItem('clientes', JSON.stringify(this.clientes));
            this.carregarClientes();
            bootstrap.Modal.getInstance(document.getElementById('clienteModal')).hide();
            
            // Restaurar comportamento original do botão
            const btnSalvar = document.querySelector('#clienteModal .btn-primary');
            btnSalvar.onclick = () => this.salvarCliente();
            
            this.mostrarAlerta('Cliente atualizado com sucesso!', 'success');
        }
    }

    excluirCliente(id) {
        if (confirm('Tem certeza que deseja excluir este cliente?')) {
            this.clientes = this.clientes.filter(c => c.id !== id);
            localStorage.setItem('clientes', JSON.stringify(this.clientes));
            this.carregarClientes();
            this.mostrarAlerta('Cliente excluído com sucesso!', 'success');
        }
    }

    // Serviços
    carregarServicos() {
        const tbody = document.getElementById('servicosTable');
        tbody.innerHTML = '';
        
        this.servicos.forEach(servico => {
            const row = tbody.insertRow();
            row.innerHTML = `
                <td>${servico.id}</td>
                <td>${servico.nome}</td>
                <td>${servico.duracao}</td>
                <td>R$ ${this.formatarMoeda(servico.preco)}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="sistema.editarServico(${servico.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="sistema.excluirServico(${servico.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
        });
    }

    salvarServico() {
        const nome = document.getElementById('servicoNome').value;
        const duracao = parseInt(document.getElementById('servicoDuracao').value);
        const preco = parseFloat(document.getElementById('servicoPreco').value);
        
        if (!nome || !duracao || !preco) {
            alert('Preencha todos os campos obrigatórios!');
            return;
        }
        
        const servico = {
            id: Date.now(),
            nome: nome,
            duracao: duracao,
            preco: preco
        };
        
        this.servicos.push(servico);
        localStorage.setItem('servicos', JSON.stringify(this.servicos));
        
        this.carregarServicos();
        bootstrap.Modal.getInstance(document.getElementById('servicoModal')).hide();
        
        this.mostrarAlerta('Serviço cadastrado com sucesso!', 'success');
    }

    editarServico(id) {
        const servico = this.servicos.find(s => s.id === id);
        if (servico) {
            document.getElementById('servicoNome').value = servico.nome;
            document.getElementById('servicoDuracao').value = servico.duracao;
            document.getElementById('servicoPreco').value = servico.preco;
            
            const btnSalvar = document.querySelector('#servicoModal .btn-primary');
            btnSalvar.onclick = () => this.atualizarServico(id);
            
            new bootstrap.Modal(document.getElementById('servicoModal')).show();
        }
    }

    atualizarServico(id) {
        const nome = document.getElementById('servicoNome').value;
        const duracao = parseInt(document.getElementById('servicoDuracao').value);
        const preco = parseFloat(document.getElementById('servicoPreco').value);
        
        const servicoIndex = this.servicos.findIndex(s => s.id === id);
        if (servicoIndex !== -1) {
            this.servicos[servicoIndex].nome = nome;
            this.servicos[servicoIndex].duracao = duracao;
            this.servicos[servicoIndex].preco = preco;
            
            localStorage.setItem('servicos', JSON.stringify(this.servicos));
            this.carregarServicos();
            bootstrap.Modal.getInstance(document.getElementById('servicoModal')).hide();
            
            const btnSalvar = document.querySelector('#servicoModal .btn-primary');
            btnSalvar.onclick = () => this.salvarServico();
            
            this.mostrarAlerta('Serviço atualizado com sucesso!', 'success');
        }
    }

    excluirServico(id) {
        if (confirm('Tem certeza que deseja excluir este serviço?')) {
            this.servicos = this.servicos.filter(s => s.id !== id);
            localStorage.setItem('servicos', JSON.stringify(this.servicos));
            this.carregarServicos();
            this.mostrarAlerta('Serviço excluído com sucesso!', 'success');
        }
    }

    // Atendimentos
    carregarClientesSelect() {
        const select = document.getElementById('atendimentoCliente');
        select.innerHTML = '<option value="">Selecione um cliente</option>';
        
        this.clientes.forEach(cliente => {
            const option = document.createElement('option');
            option.value = cliente.id;
            option.textContent = cliente.nome;
            select.appendChild(option);
        });
    }

    carregarServicosCheckbox() {
        const container = document.getElementById('servicosCheckbox');
        container.innerHTML = '';
        
        this.servicos.forEach(servico => {
            const div = document.createElement('div');
            div.className = 'col-md-6';
            div.innerHTML = `
                <div class="form-check">
                    <input class="form-check-input servico-check" type="checkbox" value="${servico.id}" 
                           data-preco="${servico.preco}" id="servico_${servico.id}" onchange="sistema.calcularTotal()">
                    <label class="form-check-label" for="servico_${servico.id}">
                        ${servico.nome} - R$ ${this.formatarMoeda(servico.preco)}
                    </label>
                </div>
            `;
            container.appendChild(div);
        });
    }

    calcularTotal() {
        let total = 0;
        document.querySelectorAll('.servico-check:checked').forEach(checkbox => {
            total += parseFloat(checkbox.dataset.preco);
        });
        
        document.getElementById('valorTotal').textContent = 'R$ ' + this.formatarMoeda(total);
    }

    carregarAtendimentos() {
        const tbody = document.getElementById('atendimentosTable');
        tbody.innerHTML = '';
        
        this.atendimentos.forEach(atendimento => {
            const cliente = this.clientes.find(c => c.id === atendimento.clienteId);
            const barbeiro = this.barbeiros.find(b => b.id === atendimento.barbeiroId);
            
            const row = tbody.insertRow();
            row.innerHTML = `
                <td>${atendimento.id}</td>
                <td>${this.formatarDataHora(atendimento.data)}</td>
                <td>${cliente ? cliente.nome : 'Cliente não encontrado'}</td>
                <td>${barbeiro ? barbeiro.nome : 'Barbeiro não encontrado'}</td>
                <td>R$ ${this.formatarMoeda(atendimento.valorTotal)}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="sistema.editarAtendimento(${atendimento.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-info" onclick="sistema.verDetalhesAtendimento(${atendimento.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="sistema.excluirAtendimento(${atendimento.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
        });
    }

    salvarAtendimento() {
        const clienteId = parseInt(document.getElementById('atendimentoCliente').value);
        const barbeiroId = parseInt(document.getElementById('atendimentoBarbeiro').value);
        const data = document.getElementById('atendimentoData').value;
        const hora = document.getElementById('atendimentoHora').value;
        const observacoes = document.getElementById('atendimentoObservacoes').value;
        
        const servicosSelecionados = [];
        let valorTotal = 0;
        
        document.querySelectorAll('.servico-check:checked').forEach(checkbox => {
            const servicoId = parseInt(checkbox.value);
            const preco = parseFloat(checkbox.dataset.preco);
            servicosSelecionados.push(servicoId);
            valorTotal += preco;
        });
        
        if (!clienteId || !barbeiroId || !data || !hora || servicosSelecionados.length === 0) {
            alert('Preencha todos os campos obrigatórios e selecione pelo menos um serviço!');
            return;
        }
        
        const atendimento = {
            id: Date.now(),
            clienteId: clienteId,
            barbeiroId: barbeiroId,
            data: data + 'T' + hora,
            servicos: servicosSelecionados,
            valorTotal: valorTotal,
            observacoes: observacoes,
            criadoEm: new Date().toISOString()
        };
        
        this.atendimentos.push(atendimento);
        localStorage.setItem('atendimentos', JSON.stringify(this.atendimentos));
        
        this.carregarAtendimentos();
        this.atualizarDashboard();
        bootstrap.Modal.getInstance(document.getElementById('atendimentoModal')).hide();
        
        this.mostrarAlerta('Atendimento registrado com sucesso!', 'success');
    }
    
    editarAtendimento(id) {
        const atendimento = this.atendimentos.find(a => a.id === id);
        if (atendimento) {
            // Preencher formulário com dados existentes
            document.getElementById('atendimentoCliente').value = atendimento.clienteId;
            document.getElementById('atendimentoBarbeiro').value = atendimento.barbeiroId;
            
            const dataHora = new Date(atendimento.data);
            document.getElementById('atendimentoData').value = dataHora.toISOString().split('T')[0];
            document.getElementById('atendimentoHora').value = dataHora.toTimeString().split(' ')[0].substring(0, 5);
            document.getElementById('atendimentoObservacoes').value = atendimento.observacoes || '';
            
            // Marcar serviços selecionados
            document.querySelectorAll('.servico-check').forEach(checkbox => {
                checkbox.checked = atendimento.servicos.includes(parseInt(checkbox.value));
            });
            
            this.calcularTotal();
            
            // Alterar comportamento do botão salvar
            const btnSalvar = document.querySelector('#atendimentoModal .btn-primary');
            btnSalvar.onclick = () => this.atualizarAtendimento(id);
            btnSalvar.textContent = 'Atualizar Atendimento';
            
            // Alterar título do modal
            document.querySelector('#atendimentoModal .modal-title').textContent = 'Editar Atendimento';
            
            new bootstrap.Modal(document.getElementById('atendimentoModal')).show();
        }
    }
    
    atualizarAtendimento(id) {
        const clienteId = parseInt(document.getElementById('atendimentoCliente').value);
        const barbeiroId = parseInt(document.getElementById('atendimentoBarbeiro').value);
        const data = document.getElementById('atendimentoData').value;
        const hora = document.getElementById('atendimentoHora').value;
        const observacoes = document.getElementById('atendimentoObservacoes').value;
        
        const servicosSelecionados = [];
        let valorTotal = 0;
        
        document.querySelectorAll('.servico-check:checked').forEach(checkbox => {
            const servicoId = parseInt(checkbox.value);
            const preco = parseFloat(checkbox.dataset.preco);
            servicosSelecionados.push(servicoId);
            valorTotal += preco;
        });
        
        if (!clienteId || !barbeiroId || !data || !hora || servicosSelecionados.length === 0) {
            alert('Preencha todos os campos obrigatórios e selecione pelo menos um serviço!');
            return;
        }
        
        const atendimentoIndex = this.atendimentos.findIndex(a => a.id === id);
        if (atendimentoIndex !== -1) {
            this.atendimentos[atendimentoIndex] = {
                ...this.atendimentos[atendimentoIndex],
                clienteId: clienteId,
                barbeiroId: barbeiroId,
                data: data + 'T' + hora,
                servicos: servicosSelecionados,
                valorTotal: valorTotal,
                observacoes: observacoes
            };
            
            localStorage.setItem('atendimentos', JSON.stringify(this.atendimentos));
            
            this.carregarAtendimentos();
            this.atualizarDashboard();
            bootstrap.Modal.getInstance(document.getElementById('atendimentoModal')).hide();
            
            // Restaurar comportamento original do botão
            const btnSalvar = document.querySelector('#atendimentoModal .btn-primary');
            btnSalvar.onclick = () => this.salvarAtendimento();
            btnSalvar.textContent = 'Registrar Atendimento';
            document.querySelector('#atendimentoModal .modal-title').textContent = 'Novo Atendimento';
            
            this.mostrarAlerta('Atendimento atualizado com sucesso!', 'success');
        }
    }
    
    excluirAtendimento(id) {
        if (confirm('Tem certeza que deseja excluir este atendimento?')) {
            this.atendimentos = this.atendimentos.filter(a => a.id !== id);
            localStorage.setItem('atendimentos', JSON.stringify(this.atendimentos));
            this.carregarAtendimentos();
            this.atualizarDashboard();
            this.mostrarAlerta('Atendimento excluído com sucesso!', 'success');
        }
    }

    verDetalhesAtendimento(id) {
        const atendimento = this.atendimentos.find(a => a.id === id);
        if (atendimento) {
            const cliente = this.clientes.find(c => c.id === atendimento.clienteId);
            const barbeiro = this.barbeiros.find(b => b.id === atendimento.barbeiroId);
            const servicosNomes = atendimento.servicos.map(s => {
                const servico = this.servicos.find(srv => srv.id === s);
                return servico ? servico.nome : '';
            }).join(', ');
            
            alert(`Detalhes do Atendimento #${atendimento.id}\n\n` +
                  `Cliente: ${cliente ? cliente.nome : 'N/A'}\n` +
                  `Barbeiro: ${barbeiro ? barbeiro.nome : 'N/A'}\n` +
                  `Data/Hora: ${this.formatarDataHora(atendimento.data)}\n` +
                  `Serviços: ${servicosNomes}\n` +
                  `Valor Total: R$ ${this.formatarMoeda(atendimento.valorTotal)}\n` +
                  `Observações: ${atendimento.observacoes || 'Nenhuma'}`);
        }
    }

    // Despesas
    carregarDespesas() {
        const tbody = document.getElementById('despesasTable');
        tbody.innerHTML = '';
        
        this.despesas.forEach(despesa => {
            const barbeiro = despesa.barbeiroId ? 
                this.barbeiros.find(b => b.id === despesa.barbeiroId) : null;
            
            const row = tbody.insertRow();
            row.innerHTML = `
                <td>${despesa.id}</td>
                <td>${this.formatarData(despesa.data)}</td>
                <td>${despesa.descricao}</td>
                <td>R$ ${this.formatarMoeda(despesa.valor)}</td>
                <td>${barbeiro ? barbeiro.nome : 'Despesa Geral'}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="sistema.editarDespesa(${despesa.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="sistema.excluirDespesa(${despesa.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
        });
    }

    salvarDespesa() {
        const descricao = document.getElementById('despesaDescricao').value;
        const valor = parseFloat(document.getElementById('despesaValor').value);
        const barbeiroId = document.getElementById('despesaBarbeiro').value;
        const data = document.getElementById('despesaData').value;
        const parcelada = document.getElementById('despesaParcelada').checked;
        
        if (!descricao || !valor || !data) {
            alert('Preencha todos os campos obrigatórios!');
            return;
        }
        
        if (parcelada) {
            const quantidadeParcelas = parseInt(document.getElementById('quantidadeParcelas').value);
            const dataInicioParcela = document.getElementById('dataInicioParcela').value || data;
            const valorParcela = valor / quantidadeParcelas;
            
            // Criar múltiplas despesas (uma para cada parcela)
            for (let i = 0; i < quantidadeParcelas; i++) {
                const dataVencimento = new Date(dataInicioParcela);
                dataVencimento.setMonth(dataVencimento.getMonth() + i);
                
                const despesa = {
                    id: Date.now() + i,
                    descricao: `${descricao} (${i + 1}/${quantidadeParcelas})`,
                    valor: valorParcela,
                    barbeiroId: barbeiroId || null,
                    data: dataVencimento.toISOString().split('T')[0],
                    parcelada: true,
                    parcelaAtual: i + 1,
                    totalParcelas: quantidadeParcelas,
                    criadoEm: new Date().toISOString()
                };
                
                this.despesas.push(despesa);
            }
            
            this.mostrarAlerta(`Despesa parcelada em ${quantidadeParcelas}x criada com sucesso!`, 'success');
        } else {
            const despesa = {
                id: Date.now(),
                descricao: descricao,
                valor: valor,
                barbeiroId: barbeiroId || null,
                data: data,
                parcelada: false,
                criadoEm: new Date().toISOString()
            };
            
            this.despesas.push(despesa);
            this.mostrarAlerta('Despesa cadastrada com sucesso!', 'success');
        }
        
        localStorage.setItem('despesas', JSON.stringify(this.despesas));
        
        this.carregarDespesas();
        this.atualizarDashboard();
        bootstrap.Modal.getInstance(document.getElementById('despesaModal')).hide();
    }

    editarDespesa(id) {
        const despesa = this.despesas.find(d => d.id === id);
        if (despesa) {
            document.getElementById('despesaDescricao').value = despesa.descricao;
            document.getElementById('despesaValor').value = despesa.valor;
            document.getElementById('despesaBarbeiro').value = despesa.barbeiroId || '';
            document.getElementById('despesaData').value = despesa.data;
            
            const btnSalvar = document.querySelector('#despesaModal .btn-primary');
            btnSalvar.onclick = () => this.atualizarDespesa(id);
            
            new bootstrap.Modal(document.getElementById('despesaModal')).show();
        }
    }

    atualizarDespesa(id) {
        const descricao = document.getElementById('despesaDescricao').value;
        const valor = parseFloat(document.getElementById('despesaValor').value);
        const barbeiroId = document.getElementById('despesaBarbeiro').value;
        const data = document.getElementById('despesaData').value;
        
        const despesaIndex = this.despesas.findIndex(d => d.id === id);
        if (despesaIndex !== -1) {
            this.despesas[despesaIndex].descricao = descricao;
            this.despesas[despesaIndex].valor = valor;
            this.despesas[despesaIndex].barbeiroId = barbeiroId || null;
            this.despesas[despesaIndex].data = data;
            
            localStorage.setItem('despesas', JSON.stringify(this.despesas));
            this.carregarDespesas();
            this.atualizarDashboard();
            bootstrap.Modal.getInstance(document.getElementById('despesaModal')).hide();
            
            const btnSalvar = document.querySelector('#despesaModal .btn-primary');
            btnSalvar.onclick = () => this.salvarDespesa();
            
            this.mostrarAlerta('Despesa atualizada com sucesso!', 'success');
        }
    }

    excluirDespesa(id) {
        if (confirm('Tem certeza que deseja excluir esta despesa?')) {
            this.despesas = this.despesas.filter(d => d.id !== id);
            localStorage.setItem('despesas', JSON.stringify(this.despesas));
            this.carregarDespesas();
            this.atualizarDashboard();
            this.mostrarAlerta('Despesa excluída com sucesso!', 'success');
        }
    }

    // Usuários
    carregarUsuarios() {
        const tbody = document.getElementById('usuariosTable');
        if (!tbody) return; // Elemento não existe na página atual
        
        tbody.innerHTML = '';
        
        this.usuarios.forEach(usuario => {
            const row = tbody.insertRow();
            row.innerHTML = `
                <td>
                    ${usuario.avatar ? 
                        `<img src="${usuario.avatar}" alt="Avatar" class="rounded-circle" style="width: 40px; height: 40px;">` :
                        `<div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-user text-white"></i>
                        </div>`
                    }
                </td>
                <td>${usuario.nome}</td>
                <td>
                    ${usuario.tipo_login === 'Google' ? 
                        '<span class="badge bg-info"><i class="fab fa-google me-1"></i>Google</span>' :
                        '<span class="badge bg-secondary"><i class="fas fa-key me-1"></i>Local</span>'
                    }
                </td>
                <td>${usuario.ultimo_acesso ? this.formatarDataHora(usuario.ultimo_acesso) : 'Nunca'}</td>
                <td>
                    ${usuario.id !== 1 ? // Não permitir editar/excluir admin principal
                        `<button class="btn btn-sm btn-outline-primary me-1" onclick="sistema.editarUsuario(${usuario.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="sistema.excluirUsuario(${usuario.id})">
                            <i class="fas fa-trash"></i>
                        </button>` :
                        '<span class="badge bg-warning">Admin Principal</span>'
                    }
                </td>
            `;
        });
    }

    editarUsuario(id) {
        const usuario = this.usuarios.find(u => u.id === id);
        if (usuario) {
            document.getElementById('usuarioNome').value = usuario.nome;
            
            // Tornar senha opcional na edição
            document.getElementById('usuarioSenha').required = false;
            document.getElementById('usuarioConfirmarSenha').required = false;
            document.querySelector('label[for="usuarioSenha"]').innerHTML = 'Nova Senha <small class="text-muted">(deixe em branco para manter atual)</small>';
            
            // Alterar comportamento do botão salvar
            const btnSalvar = document.querySelector('#usuarioModal .btn-primary');
            btnSalvar.onclick = () => this.atualizarUsuario(id);
            
            // Alterar título do modal
            document.querySelector('#usuarioModal .modal-title').textContent = 'Editar Usuário';
            
            new bootstrap.Modal(document.getElementById('usuarioModal')).show();
        }
    }

    atualizarUsuario(id) {
        const nome = document.getElementById('usuarioNome').value;
        const senha = document.getElementById('usuarioSenha').value;
        const confirmarSenha = document.getElementById('usuarioConfirmarSenha').value;
        
        if (!nome) {
            alert('Preencha todos os campos obrigatórios!');
            return;
        }
        
        // Se senha foi fornecida, validar
        if (senha) {
            if (senha.length < 6) {
                alert('A senha deve ter pelo menos 6 caracteres!');
                return;
            }
            
            if (senha !== confirmarSenha) {
                alert('As senhas não coincidem!');
                return;
            }
        }
        
        const usuarioIndex = this.usuarios.findIndex(u => u.id === id);
        if (usuarioIndex !== -1) {
            this.usuarios[usuarioIndex].nome = nome;
            
            localStorage.setItem('usuarios', JSON.stringify(this.usuarios));
            this.carregarUsuarios();
            bootstrap.Modal.getInstance(document.getElementById('usuarioModal')).hide();
            
            // Restaurar comportamento original do botão
            const btnSalvar = document.querySelector('#usuarioModal .btn-primary');
            btnSalvar.onclick = () => salvarUsuario();
            
            this.mostrarAlerta('Usuário atualizado com sucesso!', 'success');
        }
    }

    excluirUsuario(id) {
        if (id === 1) {
            alert('Não é possível excluir o administrador principal!');
            return;
        }
        
        if (confirm('Tem certeza que deseja excluir este usuário?')) {
            this.usuarios = this.usuarios.filter(u => u.id !== id);
            localStorage.setItem('usuarios', JSON.stringify(this.usuarios));
            this.carregarUsuarios();
            this.mostrarAlerta('Usuário excluído com sucesso!', 'success');
        }
    }

    // Relatórios
    gerarRelatorio() {
        const dataInicio = document.getElementById('dataInicio').value;
        const dataFim = document.getElementById('dataFim').value;
        const barbeiroId = document.getElementById('barbeiro').value;
        
        if (!dataInicio || !dataFim) {
            alert('Selecione o período para o relatório!');
            return;
        }
        
        // Filtrar atendimentos
        let atendimentosFiltrados = this.atendimentos.filter(a => {
            const dataAtendimento = a.data.split('T')[0];
            return dataAtendimento >= dataInicio && dataAtendimento <= dataFim;
        });
        
        if (barbeiroId) {
            atendimentosFiltrados = atendimentosFiltrados.filter(a => a.barbeiroId == barbeiroId);
        }
        
        // Filtrar despesas
        let despesasFiltradas = this.despesas.filter(d => {
            return d.data >= dataInicio && d.data <= dataFim;
        });
        
        if (barbeiroId) {
            despesasFiltradas = despesasFiltradas.filter(d => {
                // Se não tem barbeiro específico (despesa geral), incluir sempre quando não há filtro específico
                if (!d.barbeiroId) return true;
                // Se tem barbeiro específico, comparar com o filtro
                return d.barbeiroId == barbeiroId;
            });
        }
        
        // Calcular totais
        const totalReceitas = atendimentosFiltrados.reduce((total, a) => total + a.valorTotal, 0);
        const totalDespesas = despesasFiltradas.reduce((total, d) => total + d.valor, 0);
        const lucroLiquido = totalReceitas - totalDespesas;
        const totalAtendimentos = atendimentosFiltrados.length;
        
        // Atualizar interface
        document.getElementById('totalReceitas').textContent = 'R$ ' + this.formatarMoeda(totalReceitas);
        document.getElementById('totalDespesas').textContent = 'R$ ' + this.formatarMoeda(totalDespesas);
        document.getElementById('lucroLiquido').textContent = 'R$ ' + this.formatarMoeda(lucroLiquido);
        document.getElementById('totalAtendimentos').textContent = totalAtendimentos;
        
        // Carregar detalhes
        const tbody = document.getElementById('relatorioDetalhes');
        tbody.innerHTML = '';
        
        atendimentosFiltrados.forEach(atendimento => {
            const cliente = this.clientes.find(c => c.id === atendimento.clienteId);
            const barbeiro = this.barbeiros.find(b => b.id === atendimento.barbeiroId);
            const servicosNomes = atendimento.servicos.map(s => {
                const servico = this.servicos.find(srv => srv.id === s);
                return servico ? servico.nome : '';
            }).join(', ');
            
            const row = tbody.insertRow();
            row.innerHTML = `
                <td>${this.formatarDataHora(atendimento.data)}</td>
                <td>${cliente ? cliente.nome : 'N/A'}</td>
                <td>${barbeiro ? barbeiro.nome : 'N/A'}</td>
                <td>${servicosNomes}</td>
                <td>R$ ${this.formatarMoeda(atendimento.valorTotal)}</td>
            `;
        });
        
        // Mostrar resultado
        document.getElementById('relatorioResultado').style.display = 'block';
    }

    exportarPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        // Título
        doc.setFontSize(16);
        doc.text('Relatório Financeiro - Barbearia', 20, 20);
        
        // Período
        const dataInicio = document.getElementById('dataInicio').value;
        const dataFim = document.getElementById('dataFim').value;
        const periodo = `Período: ${this.formatarData(dataInicio)} a ${this.formatarData(dataFim)}`;
        doc.setFontSize(12);
        doc.text(periodo, 20, 30);
        
        // Resumo
        doc.setFontSize(14);
        doc.text('Resumo Financeiro:', 20, 45);
        
        doc.setFontSize(10);
        const totalReceitas = document.getElementById('totalReceitas').textContent;
        const totalDespesas = document.getElementById('totalDespesas').textContent;
        const lucroLiquido = document.getElementById('lucroLiquido').textContent;
        const totalAtendimentos = document.getElementById('totalAtendimentos').textContent;
        
        const resumo = [
            `Total Receitas: ${totalReceitas}`,
            `Total Despesas: ${totalDespesas}`,
            `Lucro Líquido: ${lucroLiquido}`,
            `Total Atendimentos: ${totalAtendimentos}`
        ];
        
        resumo.forEach((item, index) => {
            doc.text(item, 20, 55 + (index * 8));
        });
        
        // Salvar
        doc.save('relatorio-barbearia.pdf');
    }

    // Utilitários
    mostrarAlerta(mensagem, tipo) {
        // Criar elemento de alerta
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${tipo} alert-dismissible fade show position-fixed`;
        alertDiv.style.top = '80px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `
            ${mensagem}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Remover após 3 segundos
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 3000);
    }
}

// Função de logout
function logout() {
    if (confirm('Tem certeza que deseja sair do sistema?')) {
        // Limpar dados de autenticação
        localStorage.removeItem('userLoggedIn');
        localStorage.removeItem('userName');
        localStorage.removeItem('loginTime');
        
        // Redirecionar para login
        window.location.href = 'login.html';
    }
}

// Funções globais para compatibilidade
function toggleParcelamento() {
    const checkbox = document.getElementById('despesaParcelada');
    const fields = document.getElementById('parcelamentoFields');
    
    if (checkbox.checked) {
        fields.style.display = 'block';
        // Definir data padrão como próximo mês
        const proximoMes = new Date();
        proximoMes.setMonth(proximoMes.getMonth() + 1);
        document.getElementById('dataInicioParcela').value = proximoMes.toISOString().split('T')[0];
    } else {
        fields.style.display = 'none';
    }
}

function showPage(pageId) {
    sistema.showPage(pageId);
}

function salvarCliente() {
    sistema.salvarCliente();
}

function salvarServico() {
    sistema.salvarServico();
}

function salvarAtendimento() {
    sistema.salvarAtendimento();
}

function salvarDespesa() {
    sistema.salvarDespesa();
}

function gerarRelatorio() {
    sistema.gerarRelatorio();
}

function exportarPDF() {
    sistema.exportarPDF();
}

function salvarUsuario() {
    const nome = document.getElementById('usuarioNome').value;
    const senha = document.getElementById('usuarioSenha').value;
    const confirmarSenha = document.getElementById('usuarioConfirmarSenha').value;
    
    if (!nome || !senha || !confirmarSenha) {
        alert('Preencha todos os campos obrigatórios!');
        return;
    }
    
    if (senha.length < 6) {
        alert('A senha deve ter pelo menos 6 caracteres!');
        return;
    }
    
    if (senha !== confirmarSenha) {
        alert('As senhas não coincidem!');
        return;
    }
    
    const novoUsuario = {
        id: Date.now(),
        nome: nome,
        tipo_login: 'Local',
        ultimo_acesso: null,
        avatar: null,
        criadoEm: new Date().toISOString()
    };
    
    sistema.usuarios.push(novoUsuario);
    localStorage.setItem('usuarios', JSON.stringify(sistema.usuarios));
    
    sistema.carregarUsuarios();
    bootstrap.Modal.getInstance(document.getElementById('usuarioModal')).hide();
    
    sistema.mostrarAlerta('Usuário cadastrado com sucesso!', 'success');
}
// Inicializar sistema
const sistema = new SistemaBarbearia();

// Configurar página inicial
document.addEventListener('DOMContentLoaded', function() {
    // Atualizar nome do usuário após carregamento da página
    sistema.atualizarNomeUsuario();
    showPage('dashboard');
});