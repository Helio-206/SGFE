CREATE TABLE instituicoes (
    id_inst BIGINT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    codigo VARCHAR(20) NOT NULL,
    responsavel VARCHAR(100) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'ATIVA',
    created_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    updated_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (id_inst),
    CONSTRAINT uk_instituicoes_codigo UNIQUE (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id_user BIGINT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    email_verified_at TIMESTAMP(6) NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('ADMIN','GESTOR','AUDITOR') NOT NULL DEFAULT 'GESTOR',
    status ENUM('ATIVO','INATIVO') NOT NULL DEFAULT 'ATIVO',
    id_inst BIGINT NOT NULL,
    created_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    updated_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (id_user),
    CONSTRAINT uk_users_username UNIQUE (username),
    CONSTRAINT uk_users_email UNIQUE (email),
    CONSTRAINT fk_users_instituicoes FOREIGN KEY (id_inst) REFERENCES instituicoes (id_inst) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE refresh_tokens (
    id BIGINT NOT NULL AUTO_INCREMENT,
    id_user BIGINT NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at TIMESTAMP(6) NOT NULL,
    revoked_at TIMESTAMP(6) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    PRIMARY KEY (id),
    CONSTRAINT uk_refresh_tokens_hash UNIQUE (token_hash),
    CONSTRAINT fk_refresh_tokens_users FOREIGN KEY (id_user) REFERENCES users (id_user) ON DELETE CASCADE,
    INDEX idx_refresh_tokens_user_active (id_user, revoked_at, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE classificacoes_economicas (
    id_classe BIGINT NOT NULL AUTO_INCREMENT,
    descricao VARCHAR(100) NOT NULL,
    cod_classe VARCHAR(20) NOT NULL,
    tipo VARCHAR(80) NOT NULL,
    created_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    updated_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (id_classe),
    CONSTRAINT uk_classificacoes_cod_classe UNIQUE (cod_classe)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orcamentos (
    id_orcamento BIGINT NOT NULL AUTO_INCREMENT,
    id_user BIGINT NOT NULL,
    id_inst BIGINT NOT NULL,
    valor_total DECIMAL(15,2) NOT NULL,
    ano_fiscal SMALLINT NOT NULL,
    created_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    updated_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (id_orcamento),
    CONSTRAINT fk_orcamentos_users FOREIGN KEY (id_user) REFERENCES users (id_user) ON DELETE RESTRICT,
    CONSTRAINT fk_orcamentos_instituicoes FOREIGN KEY (id_inst) REFERENCES instituicoes (id_inst) ON DELETE RESTRICT,
    CONSTRAINT uk_orcamentos_inst_ano UNIQUE (id_inst, ano_fiscal),
    CONSTRAINT chk_orcamentos_valor_total CHECK (valor_total >= 0),
    INDEX idx_orcamentos_ano (ano_fiscal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE transacoes_receitas (
    id_receita BIGINT NOT NULL AUTO_INCREMENT,
    font_receita ENUM('PETROLIFERA','NAO_PETROLIFERA','PATRIMONIAL') NOT NULL,
    codigo_rupe VARCHAR(40) NOT NULL,
    data_registro DATE NOT NULL,
    valor_arrecadado DECIMAL(15,2) NOT NULL,
    id_classe BIGINT NOT NULL,
    id_inst BIGINT NOT NULL,
    created_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    updated_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (id_receita),
    CONSTRAINT uk_transacoes_receitas_rupe UNIQUE (codigo_rupe),
    CONSTRAINT fk_receitas_classificacoes FOREIGN KEY (id_classe) REFERENCES classificacoes_economicas (id_classe) ON DELETE RESTRICT,
    CONSTRAINT fk_receitas_instituicoes FOREIGN KEY (id_inst) REFERENCES instituicoes (id_inst) ON DELETE RESTRICT,
    CONSTRAINT chk_receitas_valor CHECK (valor_arrecadado > 0),
    CONSTRAINT chk_receitas_rupe_20_digitos CHECK (codigo_rupe REGEXP '^[0-9]{20}$'),
    INDEX idx_receitas_inst_data (id_inst, data_registro),
    INDEX idx_receitas_data (data_registro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE transacoes_despesas (
    id_despesa BIGINT NOT NULL AUTO_INCREMENT,
    estado ENUM('PENDENTE_CABIMENTADA','LIQUIDADA_APROVADA','PAGA','REJEITADA','CANCELADA','EM_ANALISE') NOT NULL DEFAULT 'PENDENTE_CABIMENTADA',
    descricao VARCHAR(150) NOT NULL,
    valor_bruto DECIMAL(15,2) NOT NULL,
    data_registro DATE NOT NULL,
    id_inst BIGINT NOT NULL,
    id_user BIGINT NOT NULL,
    id_classe BIGINT NULL,
    created_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    updated_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (id_despesa),
    CONSTRAINT fk_despesas_instituicoes FOREIGN KEY (id_inst) REFERENCES instituicoes (id_inst) ON DELETE RESTRICT,
    CONSTRAINT fk_despesas_users FOREIGN KEY (id_user) REFERENCES users (id_user) ON DELETE RESTRICT,
    CONSTRAINT fk_despesas_classificacoes FOREIGN KEY (id_classe) REFERENCES classificacoes_economicas (id_classe) ON DELETE SET NULL,
    CONSTRAINT chk_despesas_valor CHECK (valor_bruto > 0),
    INDEX idx_despesas_inst_data_estado (id_inst, data_registro, estado),
    INDEX idx_despesas_data_estado (data_registro, estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_logs (
    id BIGINT NOT NULL AUTO_INCREMENT,
    id_user BIGINT NULL,
    id_inst BIGINT NULL,
    acao VARCHAR(120) NOT NULL,
    entidade VARCHAR(80) NULL,
    entidade_id VARCHAR(80) NULL,
    resultado ENUM('SUCESSO','FALHA','NEGADO') NOT NULL DEFAULT 'SUCESSO',
    severidade ENUM('INFO','ALERTA','CRITICO') NOT NULL DEFAULT 'INFO',
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    correlation_id VARCHAR(80) NULL,
    contexto JSON NULL,
    created_at TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    PRIMARY KEY (id),
    CONSTRAINT fk_audit_logs_users FOREIGN KEY (id_user) REFERENCES users (id_user) ON DELETE SET NULL,
    CONSTRAINT fk_audit_logs_instituicoes FOREIGN KEY (id_inst) REFERENCES instituicoes (id_inst) ON DELETE SET NULL,
    INDEX idx_audit_acao_created (acao, created_at),
    INDEX idx_audit_user_created (id_user, created_at),
    INDEX idx_audit_entidade (entidade, entidade_id),
    INDEX idx_audit_inst_created (id_inst, created_at),
    INDEX idx_audit_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
