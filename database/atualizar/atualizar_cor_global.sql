-- A cor de destaque passa a valer para todo o site (execute UMA vez)
-- Copia a cor que o administrador já escolheu para a tabela de ajustes do site.
-- (a coluna usuarios.cor_destaque deixa de ser usada; pode ficar onde está)
USE receitas_db;
INSERT IGNORE INTO ajustes (chave, valor)
SELECT 'cor_destaque', cor_destaque FROM usuarios ORDER BY id LIMIT 1;
