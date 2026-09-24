<?php
// suporte.php - O sócio envia mensagens que chegam no aplicativo administrativo (main.py)
require_once('funcoes.php');
if (!isset($_SESSION['usuario_email'])) {
    header("Location: login.php");
    exit();
}

$email_usuario = $_SESSION['usuario_email'];
$conn = conectar_banco();

$assuntos = ['Reservas', 'Pagamentos', 'Cadastro / Conta', 'Sugestão', 'Outro'];

// Envio de nova mensagem
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assunto = $_POST['assunto'] ?? '';
    $mensagem = trim($_POST['mensagem'] ?? '');

    if (!in_array($assunto, $assuntos) || $mensagem === '') {
        redirecionar('suporte.php', 'Escolha um assunto e escreva sua mensagem.', 'erro');
    }

    $stmt = $conn->prepare("INSERT INTO suporte_mensagens (usuario_email, assunto, mensagem) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email_usuario, $assunto, $mensagem);
    $stmt->execute();
    $stmt->close();

    redirecionar('suporte.php', 'Mensagem enviada! A equipe da Associação vai responder por aqui.');
}

// Histórico de mensagens do sócio
$stmt = $conn->prepare("SELECT * FROM suporte_mensagens WHERE usuario_email = ? ORDER BY criado_em DESC, id DESC");
$stmt->bind_param("s", $email_usuario);
$stmt->execute();
$mensagens = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Ao abrir a página, as respostas passam a contar como lidas
$stmt = $conn->prepare("UPDATE suporte_mensagens SET resposta_lida = 1 WHERE usuario_email = ? AND resposta IS NOT NULL");
$stmt->bind_param("s", $email_usuario);
$stmt->execute();
$stmt->close();
$conn->close();

$rotulos_status = [
    'aberto' => ['Aguardando resposta', 'status-aberto'],
    'respondido' => ['Respondido', 'status-respondido'],
    'resolvido' => ['Resolvido', 'status-resolvido'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suporte | Associação Camilópolis</title>
    <style>
        :root { --azul: #0A3D73; --amarelo: #FFC107; --fundo: #f4f7f6; --texto: #333; --borda: #e0e0e0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--fundo); margin: 0; color: var(--texto); }

        .header { width: 100%; padding: 20px; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; box-sizing: border-box; }
        .btn-voltar { color: var(--azul); text-decoration: none; font-weight: bold; font-size: 14px; }
        .btn-voltar:hover { text-decoration: underline; }
        .usuario-badge { background: var(--fundo); padding: 5px 15px; border-radius: 20px; font-size: 14px; font-weight: 500; color: var(--azul); }

        .container { max-width: 760px; margin: 30px auto; padding: 0 16px; box-sizing: border-box; }
        h2 { color: var(--azul); border-bottom: 3px solid var(--amarelo); display: inline-block; padding-bottom: 5px; margin: 0 0 20px; }
        .card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); padding: 24px; margin-bottom: 24px; }
        .card h3 { margin-top: 0; color: var(--azul); }

        .form-group { margin-bottom: 16px; }
        label { display: block; font-weight: 600; font-size: 14px; color: var(--azul); margin-bottom: 6px; }
        select, textarea { width: 100%; padding: 12px; border: 1px solid var(--borda); border-radius: 8px; box-sizing: border-box; font-size: 15px; font-family: inherit; background: #fafafa; outline: none; }
        select:focus, textarea:focus { border-color: var(--amarelo); background: white; box-shadow: 0 0 5px rgba(255, 193, 7, 0.3); }
        textarea { min-height: 120px; resize: vertical; }
        .btn-enviar { background: var(--azul); color: white; border: none; padding: 13px 24px; border-radius: 8px; font-size: 15px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-enviar:hover { background: #072a50; }

        .contatos { font-size: 13px; color: #666; margin: 14px 0 0; }
        .contatos a { color: var(--azul); font-weight: bold; }

        /* Conversas */
        .conversa { border: 1px solid var(--borda); border-radius: 12px; padding: 16px; margin-bottom: 16px; }
        .conversa-topo { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 12px; }
        .conversa-assunto { font-weight: bold; color: var(--azul); }
        .conversa-data { font-size: 12px; color: #888; }
        .balao { padding: 12px 14px; border-radius: 12px; font-size: 14px; line-height: 1.5; white-space: pre-wrap; word-wrap: break-word; }
        .balao-socio { background: #eef4fc; margin-right: 15%; }
        .balao-associacao { background: #fff9e6; border: 1px solid #ffe8a1; margin: 10px 0 0 15%; }
        .balao small { display: block; font-weight: bold; font-size: 12px; margin-bottom: 4px; color: #777; white-space: normal; }

        .status-badge { padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .status-aberto { background: #fff9e6; color: #b38600; border: 1px solid var(--amarelo); }
        .status-respondido { background: #e6f0ff; color: var(--azul); border: 1px solid #b6d0f5; }
        .status-resolvido { background: #e6f9ed; color: #1e7e34; border: 1px solid #b7e4c7; }
        .vazio { color: #888; font-style: italic; margin: 0; }

        @media (max-width: 600px) {
            .container { margin: 16px auto; }
            .card { padding: 18px; }
            .btn-enviar { width: 100%; }
            .balao-socio { margin-right: 0; }
            .balao-associacao { margin-left: 0; }
        }
    </style>
    <link rel="stylesheet" href="comum.css">
    <script src="comum.js" defer></script>
</head>
<body>
<?php exibir_aviso(); ?>

    <div class="header">
        <a href="painel.php" class="btn-voltar">← Voltar ao Painel</a>
        <div class="usuario-badge">👤 <?php echo htmlspecialchars($email_usuario); ?></div>
    </div>

    <div class="container">
        <h2>💬 Suporte</h2>

        <!-- NOVA MENSAGEM -->
        <div class="card">
            <h3>Envie sua mensagem</h3>
            <form method="POST" action="suporte.php">
                <div class="form-group">
                    <label for="assunto">Assunto</label>
                    <select id="assunto" name="assunto" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($assuntos as $opcao): ?>
                            <option value="<?php echo htmlspecialchars($opcao); ?>"><?php echo htmlspecialchars($opcao); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="mensagem">Mensagem</label>
                    <textarea id="mensagem" name="mensagem" maxlength="2000" required placeholder="Conte para a gente como podemos ajudar..."></textarea>
                </div>
                <button type="submit" class="btn-enviar">Enviar Mensagem</button>
            </form>
            <p class="contatos">Prefere falar direto? <a href="https://wa.me/551144613996" target="_blank">WhatsApp (11) 4461-3996</a></p>
        </div>

        <!-- HISTÓRICO -->
        <div class="card">
            <h3>Minhas mensagens</h3>
            <?php if (empty($mensagens)): ?>
                <p class="vazio">Você ainda não enviou nenhuma mensagem.</p>
            <?php endif; ?>

            <?php foreach ($mensagens as $msg): ?>
                <?php list($rotulo, $classe) = $rotulos_status[$msg['status']] ?? $rotulos_status['aberto']; ?>
                <div class="conversa">
                    <div class="conversa-topo">
                        <div>
                            <div class="conversa-assunto"><?php echo htmlspecialchars($msg['assunto']); ?></div>
                            <div class="conversa-data">Enviada em <?php echo date('d/m/Y \à\s H:i', strtotime($msg['criado_em'])); ?></div>
                        </div>
                        <span class="status-badge <?php echo $classe; ?>"><?php echo $rotulo; ?></span>
                    </div>
                    <div class="balao balao-socio"><small>Você</small><?php echo htmlspecialchars($msg['mensagem']); ?></div>
                    <?php if (!empty($msg['resposta'])): ?>
                        <div class="balao balao-associacao"><small>Associação Camilópolis · <?php echo date('d/m/Y H:i', strtotime($msg['respondido_em'])); ?></small><?php echo htmlspecialchars($msg['resposta']); ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="rodape-interno"><?php echo htmlspecialchars(texto_direitos()); ?></div>

</body>
</html>
