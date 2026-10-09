-- Título opcional do passo (execute UMA vez)
USE receitas_db;
ALTER TABLE passos ADD COLUMN titulo VARCHAR(100) NULL AFTER ordem;   -- NULL = passo sem título
