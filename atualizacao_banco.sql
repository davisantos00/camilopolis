-- =====================================================================
-- ATUALIZAÇÃO DO BANCO - Pagamentos, Suporte, Notícias e Placares
-- ---------------------------------------------------------------------
-- Este arquivo é executado automaticamente pelo site (funcoes.php) e
-- pelo aplicativo (main.py), então não é preciso importar manualmente.
-- Todos os comandos podem ser rodados mais de uma vez sem dar erro.
-- (Não use ponto e vírgula dentro de textos: ele separa os comandos.)
-- =====================================================================

-- Pagamentos de mensalidade de sócio e de aluguel de quadra/churrasqueira
CREATE TABLE IF NOT EXISTS `pagamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_email` varchar(255) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `reserva_id` int(11) DEFAULT NULL,
  `referencia` varchar(7) DEFAULT NULL,
  `descricao` varchar(255) NOT NULL,
  `valor` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'pendente',
  `metodo` varchar(50) DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `pago_em` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pagamento_reserva` (`tipo`, `reserva_id`),
  UNIQUE KEY `pagamento_mensalidade` (`usuario_email`, `tipo`, `referencia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Mensagens enviadas pelo site e respondidas pelo aplicativo
CREATE TABLE IF NOT EXISTS `suporte_mensagens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_email` varchar(255) NOT NULL,
  `assunto` varchar(100) NOT NULL,
  `mensagem` text NOT NULL,
  `resposta` text DEFAULT NULL,
  `resposta_lida` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'aberto',
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `respondido_em` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Mural de novidades do painel do sócio (editado pelo aplicativo)
CREATE TABLE IF NOT EXISTS `noticias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(50) NOT NULL DEFAULT 'Aviso',
  `titulo` varchar(150) NOT NULL,
  `texto` text NOT NULL,
  `destaque` tinyint(1) NOT NULL DEFAULT 0,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Placares da federação (editados pelo aplicativo)
CREATE TABLE IF NOT EXISTS `placares` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campeonato` varchar(100) DEFAULT NULL,
  `data_jogo` date DEFAULT NULL,
  `time_casa` varchar(100) NOT NULL,
  `gols_casa` int(11) NOT NULL DEFAULT 0,
  `gols_visitante` int(11) NOT NULL DEFAULT 0,
  `time_visitante` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Conteúdo inicial (o mesmo que estava fixo no painel), só se as tabelas estiverem vazias
INSERT INTO `noticias` (`tag`, `titulo`, `texto`, `destaque`)
SELECT 'Destaque', 'Inscrições para o Campeonato Interno 2026', 'Estão abertas as inscrições para o campeonato de futsal deste ano! Monte seu time e venha participar. As vagas são limitadas e haverá premiação em dinheiro para os primeiros colocados.', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `noticias`);

INSERT INTO `noticias` (`tag`, `titulo`, `texto`, `destaque`)
SELECT 'Aviso Importante', 'Reforma da Churrasqueira Concluída', 'A nova área de lazer está pronta! Adicionamos novos espetos, uma grelha maior e reformamos as mesas. Aproveite para agendar seu churrasco com a galera do futebol.', 0
FROM DUAL WHERE (SELECT COUNT(*) FROM `noticias`) = 1;

INSERT INTO `placares` (`campeonato`, `time_casa`, `gols_casa`, `gols_visitante`, `time_visitante`)
SELECT 'Federação', 'Camilópolis FC', 4, 2, 'Juventude AC' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `placares`);

INSERT INTO `placares` (`campeonato`, `time_casa`, `gols_casa`, `gols_visitante`, `time_visitante`)
SELECT 'Federação', 'Real Santo André', 1, 1, 'Camilópolis FC' FROM DUAL
WHERE (SELECT COUNT(*) FROM `placares`) = 1;

INSERT INTO `placares` (`campeonato`, `time_casa`, `gols_casa`, `gols_visitante`, `time_visitante`)
SELECT 'Federação', 'Camilópolis FC', 3, 0, 'Vila Nova Futsal' FROM DUAL
WHERE (SELECT COUNT(*) FROM `placares`) = 2;
