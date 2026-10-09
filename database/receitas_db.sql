-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 09/10/2026 às 00:42
-- Versão do servidor: 9.1.0
-- Versão do PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `receitas_db`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `culinarias`
--

DROP TABLE IF EXISTS `culinarias`;
CREATE TABLE IF NOT EXISTS `culinarias` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `custos`
--

DROP TABLE IF EXISTS `custos`;
CREATE TABLE IF NOT EXISTS `custos` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ocasioes`
--

DROP TABLE IF EXISTS `ocasioes`;
CREATE TABLE IF NOT EXISTS `ocasioes` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `passos`
--

DROP TABLE IF EXISTS `passos`;
CREATE TABLE IF NOT EXISTS `passos` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `receita_id` int UNSIGNED NOT NULL,
  `ordem` smallint UNSIGNED NOT NULL,
  `titulo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instrucao` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `imagem` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_receita_ordem` (`receita_id`,`ordem`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `receitas`
--

DROP TABLE IF EXISTS `receitas`;
CREATE TABLE IF NOT EXISTS `receitas` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria_id` int UNSIGNED NOT NULL,
  `ocasiao_id` int UNSIGNED DEFAULT NULL,
  `culinaria_id` int UNSIGNED DEFAULT NULL,
  `custo_id` int UNSIGNED DEFAULT NULL,
  `dificuldade` enum('Fácil','Médio','Difícil') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Fácil',
  `tempo_preparo` smallint UNSIGNED NOT NULL,
  `rendimento` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `imagem_capa` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ingredientes` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_titulo` (`titulo`),
  KEY `fk_receitas_categoria` (`categoria_id`),
  KEY `fk_receitas_ocasiao` (`ocasiao_id`),
  KEY `fk_receitas_culinaria` (`culinaria_id`),
  KEY `fk_receitas_custo` (`custo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `receita_utensilios`
--

DROP TABLE IF EXISTS `receita_utensilios`;
CREATE TABLE IF NOT EXISTS `receita_utensilios` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `receita_id` int UNSIGNED NOT NULL,
  `utensilio_id` int UNSIGNED NOT NULL,
  `quantidade` smallint UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_receita_utensilio` (`receita_id`,`utensilio_id`),
  KEY `fk_ru_utensilio` (`utensilio_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cor_destaque` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'neutro',
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `senha_hash`, `foto`, `cor_destaque`, `criado_em`) VALUES
(1, 'Saud', '$2y$12$KrWbEZdq95B3rbK0EPlBO.omvxzsi8asY6GLsLd/gyeqI9EcRBQ12', 'users/cee06276f7360ae8.jpg', 'azul', '2026-10-05 19:51:43');

-- --------------------------------------------------------

--
-- Estrutura para tabela `utensilios`
--

DROP TABLE IF EXISTS `utensilios`;
CREATE TABLE IF NOT EXISTS `utensilios` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `passos`
--
ALTER TABLE `passos`
  ADD CONSTRAINT `fk_passos_receita` FOREIGN KEY (`receita_id`) REFERENCES `receitas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `receitas`
--
ALTER TABLE `receitas`
  ADD CONSTRAINT `fk_receitas_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_receitas_culinaria` FOREIGN KEY (`culinaria_id`) REFERENCES `culinarias` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_receitas_custo` FOREIGN KEY (`custo_id`) REFERENCES `custos` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_receitas_ocasiao` FOREIGN KEY (`ocasiao_id`) REFERENCES `ocasioes` (`id`) ON DELETE RESTRICT;

--
-- Restrições para tabelas `receita_utensilios`
--
ALTER TABLE `receita_utensilios`
  ADD CONSTRAINT `fk_ru_receita` FOREIGN KEY (`receita_id`) REFERENCES `receitas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ru_utensilio` FOREIGN KEY (`utensilio_id`) REFERENCES `utensilios` (`id`) ON DELETE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
