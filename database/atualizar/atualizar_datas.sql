-- Data da última atualização da receita (execute UMA vez)
-- (a data de criação já existe: receitas.criado_em é preenchida sozinha ao cadastrar)
USE receitas_db;
ALTER TABLE receitas ADD COLUMN atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER criado_em;
UPDATE receitas SET atualizado_em = criado_em;   -- receitas antigas: a atualização começa igual à criação
