<?php
// pagamento_associado.php - Situação e pagamento da mensalidade de sócio
require_once('funcoes.php');
if (!isset($_SESSION['usuario_email'])) {
    header("Location: login.php");
    exit();
}

$email_usuario = $_SESSION['usuario_email'];
$conn = conectar_banco();

// Mensalidades já registradas para este sócio, indexadas pelo mês ('AAAA-MM')
$stmt = $conn->prepare("SELECT * FROM pagamentos WHERE usuario_email = ? AND tipo = 'mensalidade' ORDER BY referencia DESC");
$stmt->bind_param("s", $email_usuario);
$stmt->execute();
$mensalidades = [];
foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $linha) {
    $mensalidades[$linha['referencia']] = $linha;
}
$stmt->close();
$conn->close();

// Mês atual e os próximos 2 (para adiantar)
$mes_atual = date('Y-m');
$meses_exibidos = [];
for ($i = 0; $i < 3; $i++) {
    $meses_exibidos[] = date('Y-m', strtotime("first day of +$i month"));
}

$atual_pago = isset($mensalidades[$mes_atual]) && $mensalidades[$mes_atual]['status'] === 'pago';

// Último mês pago em sequência a partir do mês atual ("em dia até...")
$em_dia_ate = null;
$mes = $mes_atual;
while (isset($mensalidades[$mes]) && $mensalidades[$mes]['status'] === 'pago') {
    $em_dia_ate = $mes;
    $mes = date('Y-m', strtotime("$mes-01 +1 month"));
}

$historico = array_filter($mensalidades, function ($m) { return $m['status'] === 'pago'; });
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensalidade de Sócio | Associação Camilópolis</title>
    <style>
        :root { --azul: #0A3D73; --amarelo: #FFC107; --fundo: #f4f7f6; --texto: #333; --borda: #e0e0e0; --verde: #28a745; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--fundo); margin: 0; color: var(--texto); }

        .header { width: 100%; padding: 20px; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; box-sizing: border-box; }
        .btn-voltar { color: var(--azul); text-decoration: none; font-weight: bold; font-size: 14px; }
        .btn-voltar:hover { text-decoration: underline; }
        .usuario-badge { background: var(--fundo); padding: 5px 15px; border-radius: 20px; font-size: 14px; font-weight: 500; color: var(--azul); }

        .container { max-width: 720px; margin: 30px auto; padding: 0 16px; box-sizing: border-box; }
        h2 { color: var(--azul); border-bottom: 3px solid var(--amarelo); display: inline-block; padding-bottom: 5px; margin: 0 0 20px; }
        .card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); padding: 24px; margin-bottom: 24px; }

        /* Situação do sócio */
        .situacao { display: flex; align-items: center; gap: 18px; border-left: 6px solid var(--verde); }
        .situacao.em-aberto { border-left-color: var(--amarelo); }
        .situacao-icone { font-size: 42px; }
        .situacao h3 { margin: 0 0 4px; color: var(--azul); }
        .situacao p { margin: 0; color: #666; font-size: 14px; }

        /* Lista de meses */
        .mes-item { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 16px 0; border-bottom: 1px solid var(--borda); }
        .mes-item:last-child { border-bottom: none; }
        .mes-nome { font-weight: bold; color: var(--azul); font-size: 16px; }
        .mes-valor { font-size: 13px; color: #777; }
        .status-badge { padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .status-pago { background: #e6f9ed; color: #1e7e34; border: 1px solid #b7e4c7; }
        .btn-pagar { background: var(--verde); color: white; text-decoration: none; padding: 10px 18px; border-radius: 8px; font-weight: bold; font-size: 14px; transition: 0.3s; white-space: nowrap; }
        .btn-pagar:hover { background: #218838; }
        .btn-pagar.secundario { background: white; color: var(--azul); border: 2px solid var(--azul); padding: 8px 16px; }
        .btn-pagar.secundario:hover { background: #eef4fc; }

        /* Vantagens */
        .vantagens { list-style: none; padding: 0; margin: 0; }
        .vantagens li { padding: 6px 0; font-size: 14px; color: #555; }

        /* Histórico */
        .tabela-historico { width: 100%; border-collapse: collapse; font-size: 14px; }
        .tabela-historico th { text-align: left; color: #777; font-size: 12px; text-transform: uppercase; padding: 8px 6px; border-bottom: 2px solid var(--borda); }
        .tabela-historico td { padding: 10px 6px; border-bottom: 1px solid var(--borda); }
        .tabela-historico a { color: var(--azul); font-weight: bold; }
        .vazio { color: #888; font-style: italic; margin: 0; }

        @media (max-width: 600px) {
            .container { margin: 16px auto; }
            .card { padding: 18px; }
            .situacao { gap: 12px; }
            .situacao-icone { font-size: 34px; }
            .tabela-historico th:nth-child(3), .tabela-historico td:nth-child(3) { display: none; }
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
        <h2>💳 Mensalidade de Sócio</h2>

        <!-- SITUAÇÃO ATUAL -->
        <div class="card situacao <?php echo $atual_pago ? '' : 'em-aberto'; ?>">
            <div class="situacao-icone"><?php echo $atual_pago ? '✅' : '⏳'; ?></div>
            <div>
                <?php if ($atual_pago): ?>
                    <h3>Você está em dia!</h3>
                    <p>Mensalidade paga até <strong><?php echo nome_mes($em_dia_ate); ?></strong>. Obrigado por apoiar a Associação.</p>
                <?php else: ?>
                    <h3>Mensalidade de <?php echo nome_mes($mes_atual); ?> em aberto</h3>
                    <p>Valor: <strong><?php echo formatar_dinheiro(PRECO_MENSALIDADE_SOCIO); ?>/mês</strong>. Pague por PIX ou cartão de crédito.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- MESES PARA PAGAR -->
        <div class="card">
            <?php foreach ($meses_exibidos as $i => $mes): ?>
                <?php $pago = isset($mensalidades[$mes]) && $mensalidades[$mes]['status'] === 'pago'; ?>
                <div class="mes-item">
                    <div>
                        <div class="mes-nome"><?php echo nome_mes($mes); ?><?php echo $i === 0 ? ' (mês atual)' : ''; ?></div>
                        <div class="mes-valor"><?php echo formatar_dinheiro(PRECO_MENSALIDADE_SOCIO); ?></div>
                    </div>
                    <?php if ($pago): ?>
                        <span class="status-badge status-pago">✔ Pago</span>
                    <?php else: ?>
                        <a href="pagamento.php?tipo=mensalidade&mes=<?php echo $mes; ?>" class="btn-pagar <?php echo $i === 0 ? '' : 'secundario'; ?>"><?php echo $i === 0 ? 'Pagar agora' : 'Adiantar'; ?></a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- VANTAGENS -->
        <div class="card">
            <h3 style="margin-top: 0; color: var(--azul);">⭐ Vantagens de ser sócio</h3>
            <ul class="vantagens">
                <li>✔ Descontos na locação de Salões de Festas e Quadra</li>
                <li>✔ Acesso ao pátio de estacionamento privativo</li>
                <li>✔ Livre utilização das áreas de lazer do clube</li>
            </ul>
        </div>

        <!-- HISTÓRICO -->
        <div class="card">
            <h3 style="margin-top: 0; color: var(--azul);">📜 Histórico de Pagamentos</h3>
            <?php if (empty($historico)): ?>
                <p class="vazio">Nenhuma mensalidade paga ainda.</p>
            <?php else: ?>
                <table class="tabela-historico">
                    <thead>
                        <tr><th>Mês</th><th>Valor</th><th>Pago em</th><th>Comprovante</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historico as $m): ?>
                            <tr>
                                <td><?php echo nome_mes($m['referencia']); ?></td>
                                <td><?php echo formatar_dinheiro($m['valor']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($m['pago_em'])); ?></td>
                                <td><a href="pagamento.php?id=<?php echo $m['id']; ?>">Ver</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <div class="rodape-interno"><?php echo htmlspecialchars(texto_direitos()); ?></div>

</body>
</html>
