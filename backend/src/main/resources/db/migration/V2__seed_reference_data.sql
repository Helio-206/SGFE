INSERT INTO instituicoes (nome, tipo, codigo, responsavel)
VALUES ('Ministerio das Financas', 'Ministerio', 'UO-001', 'Ministro das Financas')
ON DUPLICATE KEY UPDATE nome = VALUES(nome), tipo = VALUES(tipo), responsavel = VALUES(responsavel);

INSERT INTO classificacoes_economicas (descricao, cod_classe, tipo)
VALUES
    ('Impostos sobre o Rendimento', '1.1.00', 'Receitas - Impostos'),
    ('Imposto sobre Salarios (IRT)', '1.1.01', 'Receitas - Impostos'),
    ('Imposto Industrial', '1.1.02', 'Receitas - Impostos'),
    ('Impostos sobre Bens e Servicos (IVA)', '1.2.00', 'Receitas - Impostos'),
    ('Taxas de Servicos Publicos', '2.2.00', 'Receitas - Taxas'),
    ('Receitas de Petroleo', '6.1.00', 'Receitas Petroliferas'),
    ('Despesas com Pessoal', '01.01', 'Despesas Correntes - Pessoal'),
    ('Bens e Servicos Correntes', '02.01', 'Despesas Correntes - Bens e Servicos'),
    ('Investimentos Publicos', '11.01', 'Despesas de Capital - Investimentos')
ON DUPLICATE KEY UPDATE descricao = VALUES(descricao), tipo = VALUES(tipo);
