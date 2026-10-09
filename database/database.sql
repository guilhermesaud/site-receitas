-- Banco de dados do site de receitas (MySQL 5.7+ / MariaDB 10+)
CREATE DATABASE IF NOT EXISTS receitas_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE receitas_db;

CREATE TABLE IF NOT EXISTS receitas (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  titulo        VARCHAR(150) NOT NULL,
  categoria     VARCHAR(30)  NOT NULL,                       -- Sobremesa, Salgado, Lanche, Bebida
  dificuldade   ENUM('Fácil','Médio','Difícil') NOT NULL DEFAULT 'Fácil',
  tempo_preparo SMALLINT UNSIGNED NOT NULL,                  -- em minutos
  rendimento    VARCHAR(60)  NOT NULL,                       -- ex.: "4 porções"
  imagem        VARCHAR(255) NULL,                           -- nome do arquivo em /uploads
  video_url     VARCHAR(255) NULL,                           -- link do YouTube
  ingredientes  TEXT NOT NULL,                               -- um ingrediente por linha
  modo_preparo  TEXT NOT NULL,                               -- um passo por linha
  criado_em     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_categoria (categoria),
  INDEX idx_titulo (titulo)
) ENGINE=InnoDB;

-- Receita de exemplo (opcional)
INSERT INTO receitas (titulo, categoria, dificuldade, tempo_preparo, rendimento, ingredientes, modo_preparo) VALUES
('Brigadeiro de panela', 'Sobremesa', 'Fácil', 20, '30 unidades',
 '1 lata de leite condensado\n3 colheres (sopa) de chocolate em pó\n1 colher (sopa) de manteiga\nChocolate granulado para enrolar',
 'Misture o leite condensado, o chocolate em pó e a manteiga em uma panela.\nCozinhe em fogo baixo, mexendo sempre, até desgrudar do fundo.\nDeixe esfriar, enrole em bolinhas e passe no granulado.');
