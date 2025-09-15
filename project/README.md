# Sistema Web para Barbearia - LAMP Stack

Sistema completo de controle de caixa e relatórios para barbearia, desenvolvido em PHP + MySQL com interface responsiva em Bootstrap.

## 🚀 Características

- **Arquitetura LAMP Stack** (Linux, Apache, MySQL, PHP)
- **Sistema de Login** com controle de sessões PHP
- **Interface Responsiva** com Bootstrap 5
- **Controle de Caixa Completo**
- **Despesas Parceladas** com geração automática
- **Relatórios Financeiros**
- **Fácil Implantação via FTP/Git**

## 📋 Funcionalidades

### Autenticação
- **Sistema de Login**: Tela de login com validação de credenciais por email/senha
- **Login com Google**: Autenticação via Google OAuth para facilitar acesso
- **Criptografia de Senhas**: Senhas armazenadas com hash seguro (bcrypt)
- **Gestão de Usuários**: Cadastro, edição e remoção de usuários do sistema
- **Controle de Sessão**: Sessões PHP para manter usuário autenticado
- **Proteção de Rotas**: Redirecionamento automático para login se não autenticado
- **Logout Seguro**: Encerramento de sessão e limpeza de dados

### Cadastros
- **Clientes**: Nome completo e telefone
- **Serviços**: Nome, duração e preço
- **Barbeiros**: Gabriel e Samuel pré-cadastrados

### Operacional
- **Atendimentos**: Registro com múltiplos serviços
- **Despesas**: Controle por barbeiro ou geral, com opção de parcelamento
- **Dashboard**: Estatísticas em tempo real
- **Filtros**: Por barbeiro no dashboard e relatórios

### Relatórios
- Receitas e despesas diárias/mensais
- Filtros por barbeiro
- Exportação em PDF
- Lucro líquido

## 🛠️ Instalação

### 1. Requisitos do Servidor
- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Apache com mod_rewrite habilitado

### 2. Instalação Automática
1. Faça upload de todos os arquivos para o servidor
2. Acesse `install.php` no navegador
3. Configure os dados do MySQL
4. Execute a instalação
5. Delete o arquivo `install.php`

### 3. Acesso ao Sistema
- URL: `auth/login.php` 
- Email: `admin@barbearia.com`
- Senha: `123456`
- Ou use o login com Google (após configuração)

### 4. Configuração do Google OAuth (Opcional)
1. Acesse [Google Cloud Console](https://console.cloud.google.com/)
2. Crie um projeto ou selecione um existente
3. Ative a Google+ API
4. Crie credenciais OAuth 2.0
5. Configure as URLs de redirecionamento:
   - `http://seudominio.com/auth/google_callback.php`
6. Edite `config/google_config.php` com suas credenciais
7. Atualize o `data-client_id` nos arquivos de login

### 5. Implantação
- Via FTP: Envie todos os arquivos para a pasta raiz do site
- Via Git: Clone o repositório diretamente no servidor

### 6. Permissões (se necessário)
```bash
chmod 755 -R /caminho/do/sistema
chmod 644 config/database.php
```

## 📁 Estrutura do Projeto

```
sistema-barbearia/
├── index.php              # Dashboard principal
├── config/
│   └── database.php        # Configuração do banco
├── includes/
│   ├── header.php          # Cabeçalho
│   ├── sidebar.php         # Menu lateral
│   ├── Cliente.php         # Classe Cliente
│   ├── Servico.php         # Classe Serviço
│   ├── Receita.php         # Classe Receita
│   ├── Despesa.php         # Classe Despesa
│   └── Barbeiro.php        # Classe Barbeiro
├── pages/
│   ├── clientes.php        # Gestão de clientes
│   ├── servicos.php        # Gestão de serviços
│   ├── receitas.php        # Registro de atendimentos
│   ├── despesas.php        # Controle de despesas
│   └── relatorios.php      # Relatórios financeiros
├── api/
│   ├── dashboard_data.php  # Dados do dashboard
│   ├── ultimos_atendimentos.php
│   └── receita_detalhes.php
├── assets/
│   ├── css/
│   │   └── style.css       # Estilos customizados
│   └── js/
│       └── dashboard.js    # JavaScript do dashboard
└── sql/
    └── create_database.sql # Script de criação
```

## 💾 Banco de Dados

### Tabelas Principais
- `usuarios` - Usuários do sistema com autenticação
- `barbeiros` - Dados dos barbeiros
- `clientes` - Dados dos clientes
- `servicos` - Serviços oferecidos
- `receitas` - Atendimentos realizados
- `receita_servicos` - Serviços por atendimento
- `despesas` - Gastos da barbearia

### Recursos de Segurança
- Criptografia de senhas com bcrypt
- Autenticação via Google OAuth
- Controle de sessões seguras
- Validação de dados de entrada
- Soft delete para clientes e serviços
- Validação de dados com PDO
- Proteção contra SQL Injection
- Índices otimizados para performance

## 🎨 Interface

### Design
- **Cores**: Preto, cinza e branco com detalhes em azul
- **Responsivo**: Funciona em desktop, tablet e mobile
- **Moderno**: Interface limpa com Bootstrap 5
- **Intuitivo**: UX focada na produtividade

### Componentes
- Dashboard com estatísticas
- Modais para cadastros
- Tabelas responsivas
- Alertas informativos
- Botões com ícones

## 📊 Relatórios

### Dashboard
- Receita do dia
- Número de atendimentos
- Despesas do dia
- Lucro líquido

### Filtros Disponíveis
- Por período (dia/mês)
- Por barbeiro
- Geral da barbearia

## 🔧 Manutenção

### Backup Regular
```sql
-- Backup do banco de dados
mysqldump -u usuario -p barbearia_db > backup_$(date +%Y%m%d).sql
```

### Limpeza de Logs
- Monitorar espaço em disco
- Limpar logs antigos do Apache/MySQL

### Atualizações
- Sempre teste em ambiente de desenvolvimento
- Faça backup antes de aplicar updates

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique os logs do Apache/MySQL
2. Teste a conexão com o banco
3. Confirme as permissões dos arquivos
4. Valide a configuração do PHP

## 📄 Licença

Este projeto está sob licença MIT. Livre para uso comercial e modificação.

---

**Desenvolvido especificamente para servidores DDR com suporte PHP/MySQL**