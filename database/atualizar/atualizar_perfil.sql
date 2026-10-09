-- Foto de perfil do administrador (execute uma vez)
USE receitas_db;
ALTER TABLE usuarios ADD COLUMN foto VARCHAR(255) NULL AFTER senha_hash;   -- nome do arquivo em /uploads
