-- Ajustes gerais do site, começando pelo layout dos ingredientes (execute UMA vez)
USE receitas_db;

CREATE TABLE IF NOT EXISTS ajustes (
  chave VARCHAR(50)  NOT NULL PRIMARY KEY,
  valor VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Layout dos ingredientes na página da receita: subtitulo | cartoes | etiqueta
INSERT IGNORE INTO ajustes (chave, valor) VALUES ('layout_ingredientes', 'etiqueta');
