<?php
session_start();
if (!isset($_SESSION['usuario_email'])) {
    header("Location: login.php");
    exit();
}

$mensagem = "";
$sucesso = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_usuario = $_SESSION['usuario_email'];
    $data = $_POST['data'];
    $horario = $_POST['horario'];
    $plano = $_POST['plano'];

    // Descobre o valor com base na escolha do plano do usuário
    $valor = 0.00;
    if (strpos($plano, 'Avulso') !== false) {
        $valor = 120.00;
    } elseif (strpos($plano, '2h') !== false) {
        $valor = 700.00;
    } elseif (strpos($plano, '1h30min') !== false) {
        $valor = 580.00;
    }

    // Prepara o PHP para capturar os erros do banco de dados sem quebrar a tela
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli('localhost', 'root', '', 'camilopolis_db');

        $sql = "INSERT INTO reservas (usuario_email, data, horario, tipo_reserva, valor) 
                VALUES ('$email_usuario', '$data', '$horario', '$plano', '$valor')";
        
        // Tenta executar o agendamento
        $conn->query($sql);
        $sucesso = true;
        
        $conn->close();

    } catch (mysqli_sql_exception $e) {
        // Se o banco gritar "Erro!", nós capturamos aqui.
        // O código 1062 é o bloqueio do UNIQUE funcionando
        if ($e->getCode() == 1062) {
            $mensagem = "<div class='erro'>⚠️ Desculpe, este horário já está reservado para esta data. Por favor, escolha outro!</div>";
        } else {
            $mensagem = "<div class='erro'>Erro ao agendar: " . $e->getMessage() . "</div>";
        }
    } catch (Exception $e) {
        // Se der erro de conexão com o banco de dados
        $mensagem = "<div class='erro'>Erro de conexão: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Quadra | Associação Camilópolis</title>
    <style>
        :root { --azul: #0A3D73; --amarelo: #FFC107; --fundo: #f4f7f6; --texto: #333; --borda: #e0e0e0; --verde-quadra: #2E8B57; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--fundo); margin: 0; color: var(--texto); }
        
        .header { width: 100%; padding: 20px; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; box-sizing: border-box; }
        .btn-voltar { color: var(--azul); text-decoration: none; font-weight: bold; font-size: 14px; }
        .usuario-badge { background: var(--fundo); padding: 5px 15px; border-radius: 20px; font-size: 14px; font-weight: 500; color: var(--azul); }

        .container { max-width: 800px; margin: 40px auto; background: white; border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); overflow: hidden; display: flex; }
        
        /* Lado do Desenho (CSS PURO) */
        .lado-desenho { background-color: #1b263b; width: 40%; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .lado-desenho h3 { color: white; margin-bottom: 20px; text-align: center; font-size: 18px; }
        
        /* A Quadra de Futsal em CSS */
        .quadra-css { width: 160px; height: 260px; background: var(--verde-quadra); border: 4px solid white; position: relative; border-radius: 4px; box-shadow: 0 10px 20px rgba(0,0,0,0.3); }
        .quadra-meio { position: absolute; top: 50%; width: 100%; height: 4px; background: white; transform: translateY(-50%); }
        .quadra-circulo { position: absolute; top: 50%; left: 50%; width: 50px; height: 50px; border: 4px solid white; border-radius: 50%; transform: translate(-50%, -50%); }
        .quadra-area-top, .quadra-area-bottom { position: absolute; width: 80px; height: 35px; border: 4px solid white; left: 50%; transform: translateX(-50%); }
        .quadra-area-top { top: 0; border-top: none; }
        .quadra-area-bottom { bottom: 0; border-bottom: none; }

        /* Lado do Formulário */
        .lado-form { width: 60%; padding: 40px; box-sizing: border-box; }
        .lado-form h2 { color: var(--azul); margin-top: 0; margin-bottom: 25px; }
        
        .form-group { margin-bottom: 25px; }
        label.titulo-campo { font-weight: bold; display: block; margin-bottom: 10px; color: var(--azul); font-size: 15px; }
        input[type="date"] { width: 100%; padding: 15px; border: 2px solid var(--borda); border-radius: 8px; font-size: 16px; outline: none; background: #fafafa; font-family: inherit; font-weight: bold; color: #555; cursor: text; box-sizing: border-box; }
        input[type="date"]:focus { border-color: var(--amarelo); background: white; }
        
        /* Botões de Horário */
        .horarios-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .horarios-grid input[type="radio"] { display: none; }
        .horarios-grid label.btn-horario { background: white; border: 2px solid var(--borda); padding: 12px; text-align: center; border-radius: 8px; cursor: pointer; transition: 0.2s; font-weight: bold; color: #666; font-size: 15px; }
        .horarios-grid label.btn-horario:hover { border-color: var(--azul); color: var(--azul); background: #f0f4f8; }
        .horarios-grid input[type="radio"]:checked + label.btn-horario { background: var(--azul); color: white; border-color: var(--azul); box-shadow: 0 4px 10px rgba(10, 61, 115, 0.3); transform: scale(1.02); }
        
        /* Seção de Preços e Planos (Oculta até selecionar o horário) */
        #secao-precos { display: none; margin-top: 25px; padding-top: 20px; border-top: 2px dashed var(--borda); animation: fadeIn 0.4s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

        .planos-grid { display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; }
        .plano-opcao { display: none; }
        .plano-card { display: flex; justify-content: space-between; align-items: center; background: #fafafa; border: 2px solid var(--borda); padding: 12px 15px; border-radius: 8px; cursor: pointer; transition: 0.2s; }
        .plano-card:hover { border-color: var(--azul); background: #f0f4f8; }
        .plano-opcao:checked + .plano-card { border-color: var(--azul); background: #eef4fc; box-shadow: 0 4px 10px rgba(10, 61, 115, 0.15); }
        
        .plano-info span { display: block; font-weight: bold; color: var(--azul); font-size: 14px; }
        .plano-info small { color: #666; font-size: 12px; }
        .plano-preco { font-weight: bold; color: #28a745; font-size: 15px; }

        .btn-agendar { width: 100%; padding: 15px; background: #28a745; color: white; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer; transition: 0.3s; text-transform: uppercase; margin-top: 10px; }
        .btn-agendar:hover { background: #218838; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3); }
        .erro { background: #ffe6e6; color: #900; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; }

        /* Tela de Sucesso */
        .tela-sucesso { padding: 40px; text-align: center; width: 100%; }
        .icone-sucesso { font-size: 60px; margin-bottom: 10px; }
        .titulo-sucesso { color: #28a745; margin-bottom: 25px; }
        .promo-box { background: linear-gradient(135deg, #fff3cd 0%, #ffe8a1 100%); border: 2px dashed #ffc107; padding: 30px; border-radius: 12px; margin-bottom: 25px; }
        .promo-box h3 { color: #b38600; font-size: 20px; margin-top: 0; }
        .promo-box p { color: #665000; margin-bottom: 20px; font-size: 15px; }
        .btn-promo { display: inline-block; background: #d35400; color: white; text-decoration: none; padding: 14px 30px; border-radius: 8px; font-weight: bold; font-size: 16px; transition: 0.3s; }
        .btn-promo:hover { background: #a84300; transform: scale(1.05); }
        .btn-secundario { color: var(--azul); text-decoration: underline; font-weight: bold; margin-top: 15px; display: inline-block; }

        @media (max-width: 768px) { .container { flex-direction: column; } .lado-desenho, .lado-form { width: 100%; } }
    </style>
    <script>
        function mostrarPrecos() {
            document.getElementById('secao-precos').style.display = 'block';
        }
    </script>
</head>
<body>

    <div class="header">
        <a href="painel.php" class="btn-voltar">← Voltar ao Painel</a>
        <div class="usuario-badge">👤 <?php echo htmlspecialchars($_SESSION['usuario_email']); ?></div>
    </div>

    <div class="container">
        <?php if ($sucesso): ?>
            <div class="tela-sucesso">
                <div class="icone-sucesso">✅</div>
                <h2 class="titulo-sucesso">Quadra Agendada com Sucesso!</h2>
                
                <div class="promo-box">
                    <h3>🔥 Que tal um churrasco depois do jogo?</h3>
                    <p>Aproveite nossa área de churrasqueiras para confraternizar com os amigos e o time logo após a partida. Garanta seu espaço agora mesmo!</p>
                    <a href="agendar_churrasqueira.php" class="btn-promo">🍖 Reservar Churrasqueira</a>
                </div>
                <a href="minhas_reservas.php" class="btn-secundario">Ver minhas reservas</a>
            </div>
        <?php else: ?>
            
            <div class="lado-desenho">
                <h3>Sua Partida</h3>
                <div class="quadra-css">
                    <div class="quadra-meio"></div>
                    <div class="quadra-circulo"></div>
                    <div class="quadra-area-top"></div>
                    <div class="quadra-area-bottom"></div>
                </div>
            </div>

            <div class="lado-form">
                <h2>⚽ Agendar Quadra</h2>
                <?php echo $mensagem; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="titulo-campo">📅 Escolha a Data:</label>
                        <input type="date" name="data" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="titulo-campo">⏰ Selecione o Horário:</label>
                        <div class="horarios-grid">
                            <input type="radio" name="horario" id="h18" value="18:00" required onclick="mostrarPrecos()">
                            <label for="h18" class="btn-horario">18:00 às 19:00</label>
                            
                            <input type="radio" name="horario" id="h19" value="19:00" onclick="mostrarPrecos()">
                            <label for="h19" class="btn-horario">19:00 às 20:00</label>
                            
                            <input type="radio" name="horario" id="h20" value="20:00" onclick="mostrarPrecos()">
                            <label for="h20" class="btn-horario">20:00 às 21:00</label>
                            
                            <input type="radio" name="horario" id="h21" value="21:00" onclick="mostrarPrecos()">
                            <label for="h21" class="btn-horario">21:00 às 22:00</label>
                        </div>
                    </div>

                    <!-- SEÇÃO DE PREÇOS E MODALIDADES QUE APARECE APÓS ESCOLHER O HORÁRIO -->
                    <div id="secao-precos">
                        <label class="titulo-campo">💳 Selecione a Modalidade / Plano:</label>
                        <div class="planos-grid">
                            
                            <!-- Avulso -->
                            <input type="radio" name="plano" id="plano_avulso" value="Avulso (1h)" class="plano-opcao" required>
                            <label for="plano_avulso" class="plano-card">
                                <div class="plano-info">
                                    <span>Aluguel Avulso</span>
                                    <small>Reserva única para a data escolhida</small>
                                </div>
                                <div class="plano-preco">R$ 120,00</div>
                            </label>

                            <!-- Mensalidade 2 horas (1x por semana) -->
                            <input type="radio" name="plano" id="plano_700" value="Mensalidade 2h (1x por semana)" class="plano-opcao">
                            <label for="plano_700" class="plano-card">
                                <div class="plano-info">
                                    <span>Mensalidade (2 Horas)</span>
                                    <small>1 vez por semana no mês</small>
                                </div>
                                <div class="plano-preco">R$ 700,00</div>
                            </label>

                            <!-- Mensalidade 1h30min (1x por semana) -->
                            <input type="radio" name="plano" id="plano_580" value="Mensalidade 1h30min (1x por semana)" class="plano-opcao">
                            <label for="plano_580" class="plano-card">
                                <div class="plano-info">
                                    <span>Mensalidade (1h30min)</span>
                                    <small>1 vez por semana no mês</small>
                                </div>
                                <div class="plano-preco">R$ 580,00</div>
                            </label>

                        </div>

                        <button type="submit" class="btn-agendar">Confirmar Partida</button>
                    </div>

                </form>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>