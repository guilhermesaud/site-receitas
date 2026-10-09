-- Cor de destaque do administrador (execute uma vez)
USE receitas_db;
ALTER TABLE usuarios ADD COLUMN cor_destaque VARCHAR(10) NOT NULL DEFAULT 'neutro' AFTER foto;   -- neutro | vermelho | verde | azul | laranja
