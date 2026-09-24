<?php
require_once('funcoes.php');
if (!isset($_SESSION['usuario_email'])) {
    header("Location: login.php");
    exit();
}

$email_usuario = $_SESSION['usuario_email'];

// Configuração de Conexão com o Banco de Dados
$conn = new mysqli('localhost', 'root', '', 'camilopolis_db');

if ($conn->connect_error) {
    die("<div style='font-family:sans-serif; padding:20px; background:#ffe6e6; color:#900; border-radius:8px; margin:20px;'>
            <strong>Erro ao conectar com o banco de dados:</strong> " . $conn->connect_error . "
         </div>");
}

garantir_estrutura($conn);

// LÓGICA DE CANCELAMENTO
if (isset($_GET['cancelar']) && isset($_GET['id']) && isset($_GET['tipo'])) {
    $id_cancelar = intval($_GET['id']);
    $tipo_reserva = $_GET['tipo'];
    $cancelou = false;

    if ($tipo_reserva == 'quadra') {
        $stmt = $conn->prepare("DELETE FROM reservas WHERE id = ? AND usuario_email = ?");
        $stmt->bind_param("is", $id_cancelar, $email_usuario);
        $stmt->execute();
        $cancelou = $stmt->affected_rows > 0;
        $stmt->close();
    } elseif ($tipo_reserva == 'churrasqueira') {
        $stmt = $conn->prepare("DELETE FROM reservas_churrasqueira WHERE id = ? AND usuario_email = ?");
        $stmt->bind_param("is", $id_cancelar, $email_usuario);
        $stmt->execute();
        $cancelou = $stmt->affected_rows > 0;
        $stmt->close();
    }

    if ($cancelou) {
        // Pagamento pendente é descartado. Pagamento já feito fica registrado como cancelado (para o reembolso)
        $stmt = $conn->prepare("DELETE FROM pagamentos WHERE tipo = ? AND reserva_id = ? AND status = 'pendente'");
        $stmt->bind_param("si", $tipo_reserva, $id_cancelar);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE pagamentos SET status = 'cancelado' WHERE tipo = ? AND reserva_id = ?");
        $stmt->bind_param("si", $tipo_reserva, $id_cancelar);
        $stmt->execute();
        $stmt->close();
    }

    redirecionar('minhas_reservas.php', 'Reserva cancelada com sucesso.', 'info');
}

// Busca as reservas de QUADRA do usuário logado (com a situação do pagamento)
$stmt = $conn->prepare("SELECT r.*, p.status AS status_pagamento FROM reservas r
                        LEFT JOIN pagamentos p ON p.tipo = 'quadra' AND p.reserva_id = r.id
                        WHERE r.usuario_email = ? ORDER BY r.data DESC");
$stmt->bind_param("s", $email_usuario);
$stmt->execute();
$resultado_quadra = $stmt->get_result();

// Busca as reservas de CHURRASQUEIRA
$stmt = $conn->prepare("SELECT r.*, p.status AS status_pagamento FROM reservas_churrasqueira r
                        LEFT JOIN pagamentos p ON p.tipo = 'churrasqueira' AND p.reserva_id = r.id
                        WHERE r.usuario_email = ? ORDER BY r.data DESC");
$stmt->bind_param("s", $email_usuario);
$stmt->execute();
$resultado_churras = $stmt->get_result();

$hoje = date("Y-m-d");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Reservas | Associação Camilópolis</title>
    <style>
        :root { --azul: #0A3D73; --amarelo: #FFC107; --cinza-fundo: #f4f7f6; --cinza-borda: #e0e0e0; --texto: #333; --vermelho: #dc3545; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--cinza-fundo); margin: 0; color: var(--texto); }
        
        .header { width: 100%; padding: 20px; box-sizing: border-box; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; }
        .btn-voltar { color: var(--azul); text-decoration: none; font-weight: bold; font-size: 14px; }
        .btn-voltar:hover { text-decoration: underline; }
        .usuario-badge { background: var(--cinza-fundo); padding: 5px 15px; border-radius: 20px; font-size: 14px; font-weight: 500; color: var(--azul); }

        .container { max-width: 800px; margin: 30px auto; padding: 0 20px; }
        h2 { color: var(--azul); border-bottom: 3px solid var(--amarelo); display: inline-block; padding-bottom: 5px; margin-bottom: 25px; }

        /* Estilo dos Cards de Reserva */
        .reserva-card { background: white; border-radius: 12px; padding: 20px; margin-bottom: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; border-left: 5px solid var(--azul); transition: 0.3s; }
        .reserva-card:hover { transform: translateX(3px); box-shadow: 0 6px 20px rgba(0,0,0,0.1); }
        
        .reserva-info { display: flex; flex-direction: column; gap: 5px; }
        .reserva-titulo { font-size: 18px; font-weight: bold; color: var(--azul); margin: 0; }
        .reserva-detalhes { font-size: 14px; color: #666; }
        .reserva-detalhes strong { color: var(--texto); }

        .acoes-card { display: flex; align-items: center; gap: 15px; }

        /* Etiquetas (Status) */
        .status-badge { padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: bold; text-transform: uppercase; text-align: center; }
        .status-agendado { background-color: #fff9e6; color: #b38600; border: 1px solid var(--amarelo); }
        .status-concluido { background-color: #f0f0f0; color: #777; border: 1px solid #ccc; }
        .status-pago { background-color: #e6f9ed; color: #1e7e34; border: 1px solid #b7e4c7; }

        /* Botão de Pagar */
        .btn-pagar { background: #28a745; color: white; border: 1px solid #28a745; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: bold; text-decoration: none; display: inline-block; transition: 0.3s; }
        .btn-pagar:hover { background: #218838; }

        /* Botão de Cancelar */
        .btn-cancelar { background: #ffe6e6; color: var(--vermelho); border: 1px solid #ffcccc; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: bold; cursor: pointer; transition: 0.3s; text-decoration: none; display: inline-block; }
        .btn-cancelar:hover { background: var(--vermelho); color: white; border-color: var(--vermelho); }

        .sem-reservas { text-align: center; padding: 30px; background: white; border-radius: 12px; color: #777; font-style: italic; border: 1px dashed var(--cinza-borda); }

        /* =========================================
           ESTILO DO MODAL INTERNO DE CANCELAMENTO
           ========================================= */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); display: none; justify-content: center; align-items: center; z-index: 1000; backdrop-filter: blur(2px); }
        .modal-box { background: white; padding: 30px; border-radius: 14px; max-width: 420px; width: 90%; box-shadow: 0 10px 30px rgba(0,0,0,0.2); text-align: center; animation: modalEntrada 0.3s ease; }
        
        @keyframes modalEntrada {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .modal-icone { font-size: 40px; margin-bottom: 10px; }
        .modal-box h3 { color: var(--vermelho); margin-top: 0; margin-bottom: 15px; font-size: 20px; }
        .modal-box p { color: #555; font-size: 14px; line-height: 1.6; text-align: left; background: #fff8f8; padding: 15px; border-radius: 8px; border: 1px solid #ffcccc; margin-bottom: 25px; }
        .modal-botoes { display: flex; gap: 10px; }
        .btn-modal-voltar { flex: 1; padding: 12px; background: #e0e0e0; color: #333; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.2s; }
        .btn-modal-voltar:hover { background: #d0d0d0; }
        .btn-modal-confirmar { flex: 1; padding: 12px; background: var(--vermelho); color: white; border: none; border-radius: 8px; font-weight: bold; text-decoration: none; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
        .btn-modal-confirmar:hover { background: #b02a37; }

        @media (max-width: 600px) {
            .container { margin: 20px auto; padding: 0 16px; }
            .reserva-card { flex-direction: column; align-items: flex-start; gap: 12px; }
            .acoes-card { flex-wrap: wrap; gap: 8px; }
            .modal-botoes { flex-direction: column; }
        }
    </style>
    <link rel="stylesheet" href="comum.css">
    <script src="comum.js" defer></script>
    <script>
        function abrirModalCancelamento(tipo, id) {
            let linkAcao = "minhas_reservas.php?cancelar=1&tipo=" + tipo + "&id=" + id;
            document.getElementById('linkConfirmar').href = linkAcao;
            document.getElementById('modalCancelamento').style.display = 'flex';
        }

        function fecharModalCancelamento() {
            document.getElementById('modalCancelamento').style.display = 'none';
        }
    </script>
</head>
<body>
<?php exibir_aviso(); ?>

    <div class="header">
        <a href="painel.php" class="btn-voltar">← Voltar ao Painel</a>
        <div class="usuario-badge">👤 <?php echo htmlspecialchars($email_usuario); ?></div>
    </div>

    <div class="container">
        
        <!-- SEÇÃO DE QUADRAS -->
        <h2>⚽ Reservas de Quadra</h2>
        <?php if ($resultado_quadra && $resultado_quadra->num_rows > 0): ?>
            <?php while($row = $resultado_quadra->fetch_assoc()): 
                $data_formatada = date("d/m/Y", strtotime($row['data']));
                $passou = ($row['data'] < $hoje);
                
                $classe_status = $passou ? "status-concluido" : "status-agendado";
                $texto_status = $passou ? "Concluído" : "Agendado";
            ?>
                <div class="reserva-card">
                    <div class="reserva-info">
                        <p class="reserva-titulo">Quadra Poliesportiva</p>
                        <p class="reserva-detalhes">📅 Data: <strong><?php echo $data_formatada; ?></strong> | ⏰ Horário: <strong><?php echo htmlspecialchars($row['horario']); ?></strong> | 💰 <strong><?php echo formatar_dinheiro($row['valor']); ?></strong></p>
                    </div>
                    <div class="acoes-card">
                        <div class="status-badge <?php echo $classe_status; ?>">
                            <?php echo $texto_status; ?>
                        </div>
                        <?php if ($row['status_pagamento'] === 'pago'): ?>
                            <div class="status-badge status-pago">💳 Pago</div>
                        <?php else: ?>
                            <a href="pagamento.php?tipo=quadra&reserva=<?php echo $row['id']; ?>" class="btn-pagar">💳 Pagar</a>
                        <?php endif; ?>
                        <?php if (!$passou): ?>
                            <button onclick="abrirModalCancelamento('quadra', <?php echo $row['id']; ?>)" class="btn-cancelar">Cancelar</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="sem-reservas">Você ainda não tem reservas de quadra.</div>
        <?php endif; ?>

        <br><br>

        <!-- SEÇÃO DE CHURRASQUEIRAS -->
        <h2>🍖 Reservas de Churrasqueira</h2>
        <?php if ($resultado_churras && $resultado_churras->num_rows > 0): ?>
            <?php while($row = $resultado_churras->fetch_assoc()): 
                $data_formatada = date("d/m/Y", strtotime($row['data']));
                $passou = ($row['data'] < $hoje);
                
                $classe_status = $passou ? "status-concluido" : "status-agendado";
                $texto_status = $passou ? "Concluído" : "Agendado";
            ?>
                <div class="reserva-card" style="border-left-color: #d35400;">
                    <div class="reserva-info">
                        <p class="reserva-titulo" style="color: #d35400;">Área de Churrasco</p>
                        <p class="reserva-detalhes">📅 Data: <strong><?php echo $data_formatada; ?></strong> | 👥 Convidados: <strong><?php echo htmlspecialchars($row['convidados']); ?> pessoas</strong> | 💰 <strong><?php echo formatar_dinheiro($row['valor'] > 0 ? $row['valor'] : PRECO_CHURRASQUEIRA); ?></strong></p>
                    </div>
                    <div class="acoes-card">
                        <div class="status-badge <?php echo $classe_status; ?>">
                            <?php echo $texto_status; ?>
                        </div>
                        <?php if ($row['status_pagamento'] === 'pago'): ?>
                            <div class="status-badge status-pago">💳 Pago</div>
                        <?php else: ?>
                            <a href="pagamento.php?tipo=churrasqueira&reserva=<?php echo $row['id']; ?>" class="btn-pagar">💳 Pagar</a>
                        <?php endif; ?>
                        <?php if (!$passou): ?>
                            <button onclick="abrirModalCancelamento('churrasqueira', <?php echo $row['id']; ?>)" class="btn-cancelar">Cancelar</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="sem-reservas">Você ainda não tem reservas de churrasqueira.</div>
        <?php endif; ?>

    </div>

    <!-- MODAL PERSONALIZADO DE AVISO DE CANCELAMENTO -->
    <div id="modalCancelamento" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-icone">⚠️</div>
            <h3>Atenção ao Cancelar!</h3>
            <p>
                Tem certeza de que deseja prosseguir? O cancelamento do aluguel da quadra ou da churrasqueira está sujeito à aplicação de uma <strong>taxa administrativa de 20%</strong> sobre o valor contratado.
            </p>
            <div class="modal-botoes">
                <button onclick="fecharModalCancelamento()" class="btn-modal-voltar">Voltar</button>
                <a id="linkConfirmar" href="#" class="btn-modal-confirmar">Sim, Cancelar</a>
            </div>
        </div>
    </div>

    <div class="rodape-interno"><?php echo htmlspecialchars(texto_direitos()); ?></div>

</body>
</html>
<?php 
if (isset($conn) && $conn->ping()) {
    $conn->close(); 
}
?>