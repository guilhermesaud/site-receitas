-- Categorias dinâmicas (execute UMA vez sobre o banco atual)
USE receitas_db;

CREATE TABLE IF NOT EXISTS categorias (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(40) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1) Categorias iniciais + qualquer categoria que já esteja em uso nas receitas
INSERT IGNORE INTO categorias (nome) VALUES ('Sobremesa'), ('Salgado'), ('Lanche'), ('Bebida');
INSERT IGNORE INTO categorias (nome) SELECT DISTINCT categoria FROM receitas;

-- 2) Nova coluna + migração dos dados (texto -> id)
ALTER TABLE receitas ADD COLUMN categoria_id INT UNSIGNED NULL AFTER titulo;
UPDATE receitas r JOIN categorias c ON c.nome = r.categoria SET r.categoria_id = c.id;

-- 3) Chave estrangeira (RESTRICT = não deixa excluir categoria em uso) e remoção da coluna antiga
ALTER TABLE receitas MODIFY categoria_id INT UNSIGNED NOT NULL;
ALTER TABLE receitas ADD CONSTRAINT fk_receitas_categoria
  FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT;
ALTER TABLE receitas DROP INDEX idx_categoria, DROP COLUMN categoria;
