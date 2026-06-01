-- SGFE migration validation queries.

SELECT 'instituicoes' AS tabela, COUNT(*) AS total FROM instituicoes
UNION ALL SELECT 'users', COUNT(*) FROM users
UNION ALL SELECT 'classificacoes_economicas', COUNT(*) FROM classificacoes_economicas
UNION ALL SELECT 'orcamentos', COUNT(*) FROM orcamentos
UNION ALL SELECT 'transacoes_receitas', COUNT(*) FROM transacoes_receitas
UNION ALL SELECT 'transacoes_despesas', COUNT(*) FROM transacoes_despesas
UNION ALL SELECT 'audit_logs', COUNT(*) FROM audit_logs;

SELECT ano_fiscal, SUM(valor_total) AS tecto_total
FROM orcamentos
GROUP BY ano_fiscal
ORDER BY ano_fiscal;

SELECT YEAR(data_registro) AS ano, id_inst, SUM(valor_arrecadado) AS total_receita
FROM transacoes_receitas
GROUP BY YEAR(data_registro), id_inst
ORDER BY ano, id_inst;

SELECT YEAR(data_registro) AS ano, id_inst, estado, SUM(valor_bruto) AS total_despesa
FROM transacoes_despesas
GROUP BY YEAR(data_registro), id_inst, estado
ORDER BY ano, id_inst, estado;

SELECT codigo_rupe, COUNT(*) AS repeticoes
FROM transacoes_receitas
GROUP BY codigo_rupe
HAVING COUNT(*) > 1;

SELECT d.id_despesa
FROM transacoes_despesas d
LEFT JOIN users u ON u.id_user = d.id_user
LEFT JOIN instituicoes i ON i.id_inst = d.id_inst
WHERE u.id_user IS NULL OR i.id_inst IS NULL;

SELECT o.id_inst, o.ano_fiscal, o.valor_total,
       COALESCE(SUM(d.valor_bruto), 0) AS comprometido
FROM orcamentos o
LEFT JOIN transacoes_despesas d ON d.id_inst = o.id_inst
    AND YEAR(d.data_registro) = o.ano_fiscal
    AND d.estado IN ('PENDENTE_CABIMENTADA', 'LIQUIDADA_APROVADA', 'PAGA')
GROUP BY o.id_inst, o.ano_fiscal, o.valor_total
HAVING comprometido > o.valor_total;
