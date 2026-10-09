-- Atualização do banco (execute UMA vez sobre o banco criado pelo database.sql)
USE receitas_db;

-- 1) Administrador
CREATE TABLE IF NOT EXISTS usuarios (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario    VARCHAR(50)  NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,                 -- hash bcrypt (password_hash do PHP)
  criado_em  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2) Foto de capa (a coluna "imagem" passa a se chamar "imagem_capa")
ALTER TABLE receitas CHANGE imagem imagem_capa VARCHAR(255) NULL;

-- 3) Passos do modo de preparo (cada um com texto e imagem opcional)
CREATE TABLE IF NOT EXISTS passos (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  receita_id INT UNSIGNED NOT NULL,
  ordem      SMALLINT UNSIGNED NOT NULL,
  instrucao  TEXT NOT NULL,
  imagem     VARCHAR(255) NULL,                     -- arquivo em /uploads
  INDEX idx_receita_ordem (receita_id, ordem),
  CONSTRAINT fk_passos_receita FOREIGN KEY (receita_id)
    REFERENCES receitas(id) ON DELETE CASCADE       -- apagar a receita apaga os passos
) ENGINE=InnoDB;

-- 4) Migração: o texto antigo do preparo vira o passo 1 de cada receita
INSERT INTO passos (receita_id, ordem, instrucao) SELECT id, 1, modo_preparo FROM receitas;
ALTER TABLE receitas DROP COLUMN modo_preparo;

-- 5) Seu usuário. Gere o hash no terminal e cole abaixo:
--    php -r "echo password_hash('SUA_SENHA_FORTE', PASSWORD_DEFAULT);"
-- INSERT INTO usuarios (usuario, senha_hash) VALUES ('admin', 'COLE_AQUI_O_HASH');
