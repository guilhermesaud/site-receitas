-- Novos campos da receita: Ocasião, Culinária e Custo (execute UMA vez)
USE receitas_db;

-- 1) Listas de opções (mesmo modelo da tabela categorias)
CREATE TABLE IF NOT EXISTS ocasioes (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(40) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS culinarias (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(40) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS custos (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(40) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Opções iniciais (edite à vontade na tela de Configurações)
INSERT IGNORE INTO ocasioes (nome) VALUES ('Café da manhã'), ('Almoço'), ('Lanche'), ('Jantar'), ('Festa');
INSERT IGNORE INTO culinarias (nome) VALUES ('Brasileira'), ('Mineira'), ('Italiana'), ('Japonesa'), ('Chinesa'), ('Mexicana'), ('Árabe'), ('Francesa');
INSERT IGNORE INTO custos (nome) VALUES ('$'), ('$$'), ('$$$'), ('$$$$'), ('$$$$$');

-- 3) Colunas opcionais em receitas (receitas antigas ficam com NULL)
ALTER TABLE receitas
  ADD COLUMN ocasiao_id   INT UNSIGNED NULL AFTER categoria_id,
  ADD COLUMN culinaria_id INT UNSIGNED NULL AFTER ocasiao_id,
  ADD COLUMN custo_id     INT UNSIGNED NULL AFTER culinaria_id;

-- 4) Chaves estrangeiras (RESTRICT = não deixa excluir uma opção em uso)
ALTER TABLE receitas
  ADD CONSTRAINT fk_receitas_ocasiao   FOREIGN KEY (ocasiao_id)   REFERENCES ocasioes(id)   ON DELETE RESTRICT,
  ADD CONSTRAINT fk_receitas_culinaria FOREIGN KEY (culinaria_id) REFERENCES culinarias(id) ON DELETE RESTRICT,
  ADD CONSTRAINT fk_receitas_custo     FOREIGN KEY (custo_id)     REFERENCES custos(id)     ON DELETE RESTRICT;
