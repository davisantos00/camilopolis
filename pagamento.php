<?php
// pagamento.php - Tela de pagamento (simulado) por PIX ou cartão de crédito
// Aceita: ?id=X (pagamento), ?tipo=quadra|churrasqueira&reserva=X ou ?tipo=mensalidade&mes=AAAA-MM
require_once('funcoes.php');
if (!isset($_SESSION['usuario_email'])) {
    header("Location: login.php");
    exit();
}

$email_usuario = $_SESSION['usuario_email'];
$conn = conectar_banco();

// ==========================================
// 1. VEIO DE UMA RESERVA OU DA MENSALIDADE: CRIA O PAGAMENTO E ABRE A TELA
// ==========================================
if (isset($_GET['tipo'])) {
    $tipo = $_GET['tipo'];

    if ($tipo === 'quadra' || $tipo === 'churrasqueira') {
        $reserva_id = intval($_GET['reserva'] ?? 0);
        $tabela = ($tipo === 'quadra') ? 'reservas' : 'reservas_churrasqueira';

        $stmt = $conn->prepare("SELECT * FROM $tabela WHERE id = ? AND usuario_email = ?");
        $stmt->bind_param("is", $reserva_id, $email_usuario);
        $stmt->execute();
        $reserva = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$reserva) {
            redirecionar('minhas_reservas.php', 'Reserva não encontrada.', 'erro');
        }

        $data_formatada = date('d/m/Y', strtotime($reserva['data']));
        if ($tipo === 'quadra') {
            $descricao = "Aluguel da quadra - $data_formatada às {$reserva['horario']} ({$reserva['tipo_reserva']})";
            $valor = $reserva['valor'];
        } else {
            $descricao = "Aluguel da churrasqueira - $data_formatada ({$reserva['convidados']} convidados)";
            $valor = $reserva['valor'] > 0 ? $reserva['valor'] : PRECO_CHURRASQUEIRA;
        }
        $id = obter_pagamento($conn, $email_usuario, $tipo, $reserva_id, null, $descricao, $valor);

    } elseif ($tipo === 'mensalidade') {
        $mes = $_GET['mes'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
            redirecionar('pagamento_associado.php', 'Mês inválido.', 'erro');
        }
        $descricao = 'Mensalidade de sócio - ' . nome_mes($mes);
        $id = obter_pagamento($conn, $email_usuario, 'mensalidade', null, $mes, $descricao, PRECO_MENSALIDADE_SOCIO);

    } else {
        redirecionar('painel.php');
    }

    header("Location: pagamento.php?id=$id");
    exit();
}

// ==========================================
// 2. CARREGA O PAGAMENTO
// ==========================================
$id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM pagamentos WHERE id = ? AND usuario_email = ?");
$stmt->bind_param("is", $id, $email_usuario);
$stmt->execute();
$pagamento = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pagamento) {
    redirecionar('painel.php', 'Pagamento não encontrado.', 'erro');
}

$link_voltar = ($pagamento['tipo'] === 'mensalidade') ? 'pagamento_associado.php' : 'minhas_reservas.php';
$texto_voltar = ($pagamento['tipo'] === 'mensalidade') ? 'Voltar para Mensalidades' : 'Voltar para Minhas Reservas';
$codigo = 'CAMI' . str_pad($pagamento['id'], 6, '0', STR_PAD_LEFT);
$erro_cartao = '';

// ==========================================
// 3. CONFIRMAÇÃO DO PAGAMENTO (SIMULADA - nenhum valor é cobrado)
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && $pagamento['status'] === 'pendente') {
    $metodo = $_POST['metodo'] ?? '';
    $descricao_metodo = '';

    if ($metodo === 'pix') {
        $descricao_metodo = 'PIX';
    } elseif ($metodo === 'cartao') {
        $numero = preg_replace('/\D/', '', $_POST['numero_cartao'] ?? '');
        $nome_cartao = trim($_POST['nome_cartao'] ?? '');
        $validade = $_POST['validade'] ?? '';
        $cvv = $_POST['cvv'] ?? '';

        if (!cartao_valido($numero)) {
            $erro_cartao = 'Número do cartão inválido. Confira os dígitos.';
        } elseif ($nome_cartao === '') {
            $erro_cartao = 'Informe o nome impresso no cartão.';
        } elseif (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $validade, $partes) || ('20' . $partes[2] . $partes[1]) < date('Ym')) {
            $erro_cartao = 'Validade inválida ou cartão vencido.';
        } elseif (!preg_match('/^\d{3,4}$/', $cvv)) {
            $erro_cartao = 'CVV inválido.';
        } else {
            // Só os 4 últimos dígitos são guardados. Os dados do cartão nunca vão para o banco
            $descricao_metodo = 'Cartão final ' . substr($numero, -4);
        }
    }

    if ($descricao_metodo !== '') {
        $stmt = $conn->prepare("UPDATE pagamentos SET status = 'pago', metodo = ?, pago_em = NOW() WHERE id = ? AND status = 'pendente'");
        $stmt->bind_param("si", $descricao_metodo, $id);
        $stmt->execute();
        $stmt->close();
        redirecionar("pagamento.php?id=$id", 'Pagamento confirmado! Obrigado.');
    }
}

$conn->close();

$pix_copia_cola = gerar_pix_copia_cola($pagamento['valor'], $codigo);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento | Associação Camilópolis</title>
    <style>
        :root { --azul: #0A3D73; --amarelo: #FFC107; --fundo: #f4f7f6; --texto: #333; --borda: #e0e0e0; --verde: #28a745; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--fundo); margin: 0; color: var(--texto); }

        .header { width: 100%; padding: 20px; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; box-sizing: border-box; }
        .btn-voltar { color: var(--azul); text-decoration: none; font-weight: bold; font-size: 14px; }
        .btn-voltar:hover { text-decoration: underline; }
        .usuario-badge { background: var(--fundo); padding: 5px 15px; border-radius: 20px; font-size: 14px; font-weight: 500; color: var(--azul); }

        .container { max-width: 560px; margin: 30px auto; padding: 0 16px; box-sizing: border-box; }
        .card { background: white; border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); padding: 28px; margin-bottom: 20px; }

        .aviso-demo { background: #fff9e6; border: 1px dashed var(--amarelo); color: #7a5b00; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; text-align: center; }

        /* Resumo */
        .resumo-rotulo { font-size: 12px; text-transform: uppercase; color: #888; font-weight: bold; letter-spacing: 0.5px; }
        .resumo-descricao { font-size: 16px; color: var(--azul); font-weight: 600; margin: 6px 0 16px; }
        .resumo-valor { font-size: 34px; font-weight: 800; color: var(--azul); }
        .resumo-linha { display: flex; justify-content: space-between; align-items: flex-end; gap: 10px; flex-wrap: wrap; }
        .status-badge { padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .status-pendente { background: #fff9e6; color: #b38600; border: 1px solid var(--amarelo); }
        .status-pago { background: #e6f9ed; color: #1e7e34; border: 1px solid #b7e4c7; }
        .status-cancelado { background: #f0f0f0; color: #777; border: 1px solid #ccc; }

        /* Abas PIX / Cartão */
        .abas { display: flex; gap: 8px; margin-bottom: 20px; }
        .aba { flex: 1; padding: 12px; border: 2px solid var(--borda); background: #fafafa; border-radius: 10px; font-weight: bold; font-size: 15px; color: #666; cursor: pointer; transition: 0.2s; }
        .aba.ativa { border-color: var(--azul); background: #eef4fc; color: var(--azul); }
        .painel-metodo { display: none; }
        .painel-metodo.ativo { display: block; }

        /* PIX */
        .pix-qr { display: flex; justify-content: center; padding: 16px; background: white; border: 1px solid var(--borda); border-radius: 10px; margin-bottom: 16px; min-height: 200px; align-items: center; }
        .pix-qr img, .pix-qr canvas { max-width: 100%; height: auto; }
        .pix-instrucoes { font-size: 14px; color: #555; margin: 0 0 12px; text-align: center; }
        .copia-cola { width: 100%; box-sizing: border-box; font-family: Consolas, monospace; font-size: 12px; padding: 10px; border: 1px solid var(--borda); border-radius: 8px; background: #fafafa; resize: none; height: 70px; word-break: break-all; }
        .btn-copiar { margin-top: 8px; width: 100%; padding: 10px; border: 2px solid var(--azul); background: white; color: var(--azul); border-radius: 8px; font-weight: bold; cursor: pointer; }
        .btn-copiar:hover { background: #eef4fc; }

        /* Cartão */
        .form-group { margin-bottom: 16px; }
        .form-linha { display: flex; gap: 12px; }
        .form-linha .form-group { flex: 1; }
        label { display: block; font-weight: 600; font-size: 14px; color: var(--azul); margin-bottom: 6px; }
        input[type="text"] { width: 100%; padding: 12px; border: 1px solid var(--borda); border-radius: 8px; box-sizing: border-box; font-size: 15px; background: #fafafa; outline: none; }
        input[type="text"]:focus { border-color: var(--amarelo); background: white; box-shadow: 0 0 5px rgba(255, 193, 7, 0.3); }
        .dica-teste { font-size: 12px; color: #888; margin: -4px 0 16px; }
        .erro { background: #ffe6e6; color: #900; padding: 12px; border-radius: 8px; margin-bottom: 16px; text-align: center; font-size: 14px; }

        .btn-confirmar { width: 100%; padding: 15px; background: var(--verde); color: white; border: none; border-radius: 8px; font-size: 17px; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 8px; }
        .btn-confirmar:hover { background: #218838; }

        /* Comprovante */
        .comprovante { text-align: center; }
        .comprovante-icone { font-size: 56px; }
        .comprovante h2 { color: var(--verde); margin: 8px 0 20px; }
        .comprovante-dados { text-align: left; border-top: 1px dashed var(--borda); padding-top: 16px; }
        .comprovante-dados p { display: flex; justify-content: space-between; gap: 12px; margin: 0 0 10px; font-size: 14px; }
        .comprovante-dados span:first-child { color: #777; }
        .comprovante-dados span:last-child { font-weight: 600; text-align: right; }
        .acoes-comprovante { display: flex; gap: 10px; margin-top: 20px; }
        .acoes-comprovante a, .acoes-comprovante button { flex: 1; padding: 12px; border-radius: 8px; font-weight: bold; font-size: 14px; text-decoration: none; text-align: center; cursor: pointer; }
        .btn-imprimir { background: white; border: 2px solid var(--azul); color: var(--azul); }
        .btn-continuar { background: var(--azul); border: 2px solid var(--azul); color: white; }

        @media (max-width: 600px) {
            .container { margin: 16px auto; }
            .card { padding: 20px; }
            .resumo-valor { font-size: 28px; }
            .acoes-comprovante { flex-direction: column; }
        }

        @media print {
            .header, .aviso-demo, .acoes-comprovante, .rodape-interno { display: none; }
            .card { box-shadow: none; border: 1px solid #ccc; }
        }
    </style>
    <link rel="stylesheet" href="comum.css">
    <script src="comum.js" defer></script>
</head>
<body>
<?php exibir_aviso(); ?>

    <div class="header">
        <a href="<?php echo $link_voltar; ?>" class="btn-voltar">← <?php echo $texto_voltar; ?></a>
        <div class="usuario-badge">👤 <?php echo htmlspecialchars($email_usuario); ?></div>
    </div>

    <div class="container">

        <div class="aviso-demo">🧪 Ambiente de demonstração: nenhum valor é cobrado de verdade.</div>

        <?php if ($pagamento['status'] === 'pago'): ?>
            <!-- COMPROVANTE -->
            <div class="card comprovante">
                <div class="comprovante-icone">✅</div>
                <h2>Pagamento Confirmado!</h2>
                <div class="comprovante-dados">
                    <p><span>Descrição</span><span><?php echo htmlspecialchars($pagamento['descricao']); ?></span></p>
                    <p><span>Valor</span><span><?php echo formatar_dinheiro($pagamento['valor']); ?></span></p>
                    <p><span>Forma de pagamento</span><span><?php echo htmlspecialchars($pagamento['metodo']); ?></span></p>
                    <p><span>Data</span><span><?php echo date('d/m/Y H:i', strtotime($pagamento['pago_em'])); ?></span></p>
                    <p><span>Código</span><span><?php echo $codigo; ?></span></p>
                </div>
                <div class="acoes-comprovante">
                    <button type="button" class="btn-imprimir" onclick="window.print()">🖨️ Imprimir</button>
                    <a href="<?php echo $link_voltar; ?>" class="btn-continuar">Continuar</a>
                </div>
            </div>

        <?php elseif ($pagamento['status'] === 'cancelado'): ?>
            <div class="card">
                <p class="resumo-rotulo">Pagamento</p>
                <p class="resumo-descricao"><?php echo htmlspecialchars($pagamento['descricao']); ?></p>
                <span class="status-badge status-cancelado">Reserva cancelada</span>
                <p style="font-size: 14px; color: #666; margin-top: 16px;">Esta reserva foi cancelada. Para o reembolso, fale com a Associação pela página de <a href="suporte.php">Suporte</a>.</p>
            </div>

        <?php else: ?>
            <!-- RESUMO -->
            <div class="card">
                <p class="resumo-rotulo">Você está pagando</p>
                <p class="resumo-descricao"><?php echo htmlspecialchars($pagamento['descricao']); ?></p>
                <div class="resumo-linha">
                    <span class="resumo-valor"><?php echo formatar_dinheiro($pagamento['valor']); ?></span>
                    <span class="status-badge status-pendente">Aguardando pagamento</span>
                </div>
            </div>

            <!-- FORMAS DE PAGAMENTO -->
            <div class="card">
                <div class="abas">
                    <button type="button" class="aba <?php echo $erro_cartao ? '' : 'ativa'; ?>" data-painel="painel-pix">⚡ PIX</button>
                    <button type="button" class="aba <?php echo $erro_cartao ? 'ativa' : ''; ?>" data-painel="painel-cartao">💳 Cartão de Crédito</button>
                </div>

                <!-- PIX -->
                <div id="painel-pix" class="painel-metodo <?php echo $erro_cartao ? '' : 'ativo'; ?>">
                    <p class="pix-instrucoes">Abra o app do seu banco, escolha <strong>Pagar com PIX</strong> e leia o QR Code ou use o código abaixo.</p>
                    <div class="pix-qr" id="qrcode"></div>
                    <textarea class="copia-cola" id="pix-codigo" readonly><?php echo htmlspecialchars($pix_copia_cola); ?></textarea>
                    <button type="button" class="btn-copiar" id="btn-copiar">📋 Copiar código PIX</button>

                    <form method="POST" action="pagamento.php?id=<?php echo $id; ?>">
                        <input type="hidden" name="metodo" value="pix">
                        <button type="submit" class="btn-confirmar">Já fiz o PIX, confirmar pagamento</button>
                    </form>
                </div>

                <!-- CARTÃO -->
                <div id="painel-cartao" class="painel-metodo <?php echo $erro_cartao ? 'ativo' : ''; ?>">
                    <?php if ($erro_cartao): ?>
                        <div class="erro">⚠️ <?php echo htmlspecialchars($erro_cartao); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="pagamento.php?id=<?php echo $id; ?>" autocomplete="on">
                        <input type="hidden" name="metodo" value="cartao">
                        <div class="form-group">
                            <label for="numero_cartao">Número do Cartão</label>
                            <input type="text" id="numero_cartao" name="numero_cartao" inputmode="numeric" autocomplete="cc-number" placeholder="0000 0000 0000 0000" maxlength="23" required>
                        </div>
                        <p class="dica-teste">Para testar, use 4111 1111 1111 1111, qualquer validade futura e CVV 123.</p>
                        <div class="form-group">
                            <label for="nome_cartao">Nome Impresso no Cartão</label>
                            <input type="text" id="nome_cartao" name="nome_cartao" autocomplete="cc-name" placeholder="Como está no cartão" required value="<?php echo htmlspecialchars($_POST['nome_cartao'] ?? ''); ?>">
                        </div>
                        <div class="form-linha">
                            <div class="form-group">
                                <label for="validade">Validade</label>
                                <input type="text" id="validade" name="validade" inputmode="numeric" autocomplete="cc-exp" placeholder="MM/AA" maxlength="5" required>
                            </div>
                            <div class="form-group">
                                <label for="cvv">CVV</label>
                                <input type="text" id="cvv" name="cvv" inputmode="numeric" autocomplete="cc-csc" placeholder="123" maxlength="4" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-confirmar">Pagar <?php echo formatar_dinheiro($pagamento['valor']); ?></button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <div class="rodape-interno"><?php echo htmlspecialchars(texto_direitos()); ?></div>

    <?php if ($pagamento['status'] === 'pendente'): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // Troca entre as abas PIX e Cartão
        document.querySelectorAll('.aba').forEach(function (aba) {
            aba.addEventListener('click', function () {
                document.querySelectorAll('.aba').forEach(function (a) { a.classList.remove('ativa'); });
                document.querySelectorAll('.painel-metodo').forEach(function (p) { p.classList.remove('ativo'); });
                aba.classList.add('ativa');
                document.getElementById(aba.dataset.painel).classList.add('ativo');
            });
        });

        // QR Code do PIX
        var codigoPix = document.getElementById('pix-codigo').value;
        if (window.QRCode) {
            new QRCode(document.getElementById('qrcode'), { text: codigoPix, width: 200, height: 200, correctLevel: QRCode.CorrectLevel.M });
        } else {
            document.getElementById('qrcode').textContent = 'Não foi possível gerar o QR Code. Use o código abaixo.';
        }

        // Copiar código PIX
        document.getElementById('btn-copiar').addEventListener('click', function () {
            var botao = this;
            var campo = document.getElementById('pix-codigo');
            campo.select();
            (navigator.clipboard ? navigator.clipboard.writeText(campo.value) : Promise.reject())
                .catch(function () { document.execCommand('copy'); })
                .finally(function () {
                    botao.textContent = '✅ Código copiado!';
                    setTimeout(function () { botao.textContent = '📋 Copiar código PIX'; }, 2500);
                });
        });

        // Máscaras dos campos do cartão
        function mascarar(id, formatar) {
            var campo = document.getElementById(id);
            campo.addEventListener('input', function () { campo.value = formatar(campo.value.replace(/\D/g, '')); });
        }
        mascarar('numero_cartao', function (v) { return v.slice(0, 19).replace(/(\d{4})(?=\d)/g, '$1 '); });
        mascarar('validade', function (v) { return v.length > 2 ? v.slice(0, 2) + '/' + v.slice(2, 4) : v; });
        mascarar('cvv', function (v) { return v.slice(0, 4); });
    </script>
    <?php endif; ?>

</body>
</html>
