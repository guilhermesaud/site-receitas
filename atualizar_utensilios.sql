-- Utensílios das receitas (execute UMA vez)
USE receitas_db;

-- 1) Lista de utensílios cadastrados (só o nome, mesmo modelo de categorias)
CREATE TABLE IF NOT EXISTS utensilios (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(40) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Vínculo receita <-> utensílio, com a quantidade (uma receita pode ter vários utensílios)
CREATE TABLE IF NOT EXISTS receita_utensilios (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  receita_id   INT UNSIGNED NOT NULL,
  utensilio_id INT UNSIGNED NOT NULL,
  quantidade   SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  UNIQUE KEY uq_receita_utensilio (receita_id, utensilio_id),            -- cada utensílio uma vez por receita
  CONSTRAINT fk_ru_receita   FOREIGN KEY (receita_id)   REFERENCES receitas(id)    ON DELETE CASCADE,   -- apagar a receita apaga os vínculos
  CONSTRAINT fk_ru_utensilio FOREIGN KEY (utensilio_id) REFERENCES utensilios(id)  ON DELETE RESTRICT   -- não exclui utensílio em uso
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
