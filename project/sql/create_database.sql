-- Script de criação do banco de dados para Sistema da Barbearia
-- Este arquivo é usado pelo install.php para configurar o banco automaticamente

-- Tabela de barbeiros
CREATE TABLE IF NOT EXISTS barbeiros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(15),
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserindo os 2 barbeiros padrão
INSERT INTO barbeiros (nome, telefone) VALUES 
('Gabriel', '(11) 99999-9999'),
('Samuel', '(11) 88888-8888');

-- Tabela de clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_completo VARCHAR(150) NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de serviços
CREATE TABLE IF NOT EXISTS servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    duracao_minutos INT NOT NULL DEFAULT 30,
    preco DECIMAL(10,2) NOT NULL,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserindo alguns serviços padrão
INSERT INTO servicos (nome, duracao_minutos, preco) VALUES 
('Corte Simples', 30, 25.00),
('Corte + Barba', 45, 35.00),
('Apenas Barba', 20, 15.00),
('Sobrancelha', 15, 10.00),
('Pigmentação', 60, 80.00);

-- Tabela de receitas (atendimentos)
CREATE TABLE IF NOT EXISTS receitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    barbeiro_id INT NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    observacoes TEXT,
    data_atendimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (barbeiro_id) REFERENCES barbeiros(id)
);

-- Tabela intermediária para serviços da receita (permitir múltiplos serviços por atendimento)
CREATE TABLE IF NOT EXISTS receita_servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receita_id INT NOT NULL,
    servico_id INT NOT NULL,
    quantidade INT DEFAULT 1,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (receita_id) REFERENCES receitas(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servicos(id)
);

-- Tabela de despesas
CREATE TABLE IF NOT EXISTS despesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(200) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    barbeiro_id INT NULL, -- NULL significa despesa geral
    data_despesa DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (barbeiro_id) REFERENCES barbeiros(id)
);

-- Tabela de usuários (sem email)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    perfil ENUM('admin', 'usuario') DEFAULT 'usuario',
    ativo TINYINT(1) DEFAULT 1,
    ultimo_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir usuários padrão com senhas corretas (senha: 123456)
INSERT INTO usuarios (nome, usuario, senha_hash, perfil, ativo) VALUES 
('Administrador', 'admin', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'admin', 1),
('Usuário Comum', 'usuario', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'usuario', 1);

-- Índices para melhor performance
CREATE INDEX IF NOT EXISTS idx_receitas_data ON receitas(data_atendimento);
CREATE INDEX IF NOT EXISTS idx_receitas_barbeiro ON receitas(barbeiro_id);
CREATE INDEX IF NOT EXISTS idx_despesas_data ON despesas(data_despesa);
CREATE INDEX IF NOT EXISTS idx_despesas_barbeiro ON despesas(barbeiro_id);
CREATE INDEX IF NOT EXISTS idx_usuarios_usuario ON usuarios(usuario);
CREATE INDEX IF NOT EXISTS idx_usuarios_perfil ON usuarios(perfil);
CREATE INDEX IF NOT EXISTS idx_usuarios_ativo ON usuarios(ativo);

-- Inserindo alguns dados de exemplo para teste
INSERT INTO clientes (nome_completo, telefone) VALUES 
('João Silva', '(11) 99999-1111'),
('Maria Santos', '(11) 99999-2222'),
('Pedro Oliveira', '(11) 99999-3333');

-- Exemplo de receita
INSERT INTO receitas (cliente_id, barbeiro_id, valor_total, observacoes, data_atendimento) VALUES 
(1, 1, 35.00, 'Cliente regular', NOW());

-- Exemplo de serviços da receita
INSERT INTO receita_servicos (receita_id, servico_id, quantidade, preco_unitario, subtotal) VALUES 
(1, 2, 1, 35.00, 35.00);

-- Exemplo de despesas
INSERT INTO despesas (descricao, valor, barbeiro_id, data_despesa) VALUES 
('Shampoo profissional', 45.00, 1, CURDATE()),
('Conta de luz', 120.00, NULL, CURDATE());
