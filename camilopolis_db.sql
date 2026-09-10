
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 10/09/2026 às 16:48
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `camilopolis_db`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamentos`
--

CREATE TABLE `agendamentos` (
  `id` int(11) NOT NULL,
  `email_usuario` varchar(100) DEFAULT NULL,
  `data` date DEFAULT NULL,
  `horario` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `agendamentos`
--

INSERT INTO `agendamentos` (`id`, `email_usuario`, `data`, `horario`) VALUES
(4, 'teste@gmail.com', '2026-06-21', '18:00'),
(5, 'lucas@gmail.com', '2026-06-22', '18:00'),
(6, 'marcia@gmail.com', '2026-06-23', '18:00'),
(7, 'ds.805479@gmail.com', '2026-07-31', '18:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamentos_churrasqueira`
--

CREATE TABLE `agendamentos_churrasqueira` (
  `id` int(11) NOT NULL,
  `email_usuario` varchar(100) DEFAULT NULL,
  `data` date DEFAULT NULL,
  `convidados` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `agendamentos_churrasqueira`
--

INSERT INTO `agendamentos_churrasqueira` (`id`, `email_usuario`, `data`, `convidados`) VALUES
(1, 'teste@gmail.com', '2026-06-21', -20),
(2, 'lucas@gmail.com', '2026-06-22', -20),
(3, 'marcia@gmail.com', '2026-06-23', -20),
(4, 'ds.805479@gmail.com', '2026-07-31', 20);

-- --------------------------------------------------------

--
-- Estrutura para tabela `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `usuario_email` varchar(255) NOT NULL,
  `data` date NOT NULL,
  `horario` varchar(50) NOT NULL,
  `tipo_reserva` varchar(50) DEFAULT 'Avulso',
  `valor` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `reservas`
--

INSERT INTO `reservas` (`id`, `usuario_email`, `data`, `horario`, `tipo_reserva`, `valor`) VALUES
(2, 'ds.805479@gmail.com', '2026-07-31', '18:00', 'Avulso (1h)', 120.00),
(5, 'lucas@gmail.com', '2026-07-31', '19:00', 'Avulso (1h)', 120.00),
(7, 'lucas@gmail.com', '2026-07-30', '19:00', 'Avulso (1h)', 120.00),
(9, 'ds.805479@gmail.com', '2026-09-10', '18:00', 'Avulso (1h)', 120.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `reservas_churrasqueira`
--

CREATE TABLE `reservas_churrasqueira` (
  `id` int(11) NOT NULL,
  `usuario_email` varchar(255) NOT NULL,
  `data` date NOT NULL,
  `convidados` int(11) NOT NULL,
  `tipo_reserva` varchar(50) DEFAULT 'Avulso',
  `valor` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `reservas_churrasqueira`
--

INSERT INTO `reservas_churrasqueira` (`id`, `usuario_email`, `data`, `convidados`, `tipo_reserva`, `valor`) VALUES
(3, 'ds.805479@gmail.com', '2026-07-31', 20, 'Avulso', 0.00),
(6, 'lucas@gmail.com', '2026-08-01', 15, 'Avulso', 0.00),
(7, 'lucas@gmail.com', '2026-08-14', 20, 'Avulso', 0.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `codigo_recuperacao` varchar(6) DEFAULT NULL,
  `codigo_expiracao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `telefone`, `senha`, `foto`, `codigo_recuperacao`, `codigo_expiracao`) VALUES
(1, 'Davi Pinheiro dos Santos', 'ds.805479@gmail.com', '', '12345', 'uploads/d74b4f73fb2b420460a5949a34002749.jpg', NULL, NULL),
(3, 'lucas', 'lucas@gmail.com', '', '12345', 'uploads/cc7f5a62438f7b4e2a64e64e1e3746d3.jpg', NULL, NULL),
(4, 'yan', 'yan@gmail.com', '', '12345', NULL, NULL, NULL),
(5, 'davi', 'davi', '', '123', NULL, NULL, NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `agendamentos_churrasqueira`
--
ALTER TABLE `agendamentos_churrasqueira`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bloqueio_quadra` (`data`,`horario`);

--
-- Índices de tabela `reservas_churrasqueira`
--
ALTER TABLE `reservas_churrasqueira`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bloqueio_churras` (`data`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `agendamentos_churrasqueira`
--
ALTER TABLE `agendamentos_churrasqueira`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `reservas_churrasqueira`
--
ALTER TABLE `reservas_churrasqueira`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
