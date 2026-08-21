<?php
session_start();
if (!isset($_SESSION['usuario_email'])) {
    header("Location: login.php");
    exit();
}

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_usuario = $_SESSION['usuario_email'];
    $data = $_POST['data'];
    $convidados = $_POST['convidados'];

    // Prepara o PHP para tratar os erros do banco sem dar "Fatal error" na tela
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli('localhost', 'root', '', 'camilopolis_db');

        $sql = "INSERT INTO reservas_churrasqueira (usuario_email, data, convidados) VALUES ('$email_usuario', '$data', '$convidados')";

        $conn->query($sql);
        
        // Se deu tudo certo, mostra a mensagem e redireciona
        echo "<script>alert('Churrasqueira reservada com sucesso!'); window.location.href='minhas_reservas.php';</script>";
        exit();

    } catch (mysqli_sql_exception $e) {
        // Se bater na regra do banco que proíbe duas reservas no mesmo dia (Erro 1062)
        if ($e->getCode() == 1062) {
            $mensagem = "<div class='erro'>⚠️ Desculpe, a churrasqueira já está reservada para este dia. Por favor, escolha outra data!</div>";
        } else {
            $mensagem = "<div class='erro'>Erro ao reservar: " . $e->getMessage() . "</div>";
        }
    } catch (Exception $e) {
        // Erros de conexão em geral
        $mensagem = "<div class='erro'>Erro na conexão: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Churrasqueira | Associação Camilópolis</title>
    <style>
        :root { --azul: #0A3D73; --laranja: #d35400; --fundo: #f4f7f6; --texto: #333; --borda: #e0e0e0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--fundo); margin: 0; color: var(--texto); }
        
        .header { width: 100%; padding: 20px; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; box-sizing: border-box; }
        .btn-voltar { color: var(--azul); text-decoration: none; font-weight: bold; font-size: 14px; }
        .usuario-badge { background: var(--fundo); padding: 5px 15px; border-radius: 20px; font-size: 14px; font-weight: 500; color: var(--azul); }

        .container { max-width: 800px; margin: 40px auto; background: white; border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); overflow: hidden; display: flex; border-top: 5px solid var(--laranja); }
        
        /* Lado do Desenho (CSS PURO) */
        .lado-desenho { background-color: #2c3e50; width: 40%; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; overflow: hidden; }
        .lado-desenho h3 { color: #f39c12; margin-bottom: 30px; text-align: center; font-size: 18px; z-index: 2; }
        
        /* A Churrasqueira em CSS */
        .churras-css { display: flex; flex-direction: column; align-items: center; z-index: 2; }
        .churras-chamine { width: 60px; height: 50px; background: #8b4513; clip-path: polygon(20% 0%, 80% 0%, 100% 100%, 0% 100%); }
        .churras-meio { width: 140px; height: 110px; background: #34495e; border-radius: 10px 10px 0 0; position: relative; display: flex; justify-content: center; align-items: flex-end; padding-bottom: 5px; box-shadow: inset 0 -10px 20px rgba(0,0,0,0.5); }
        .churras-grelha { width: 120px; height: 6px; background: repeating-linear-gradient(90deg, #bdc3c7, #bdc3c7 4px, transparent 4px, transparent 12px); position: absolute; top: 40px; }
        .churras-fogo { width: 70px; height: 40px; background: #e67e22; border-radius: 50% 50% 0 0; filter: blur(3px); animation: fogoAnime 0.8s infinite alternate; }
        .churras-base { width: 140px; height: 100px; background: repeating-linear-gradient(45deg, #a0522d, #a0522d 15px, #8b4513 15px, #8b4513 30px); border-top: 5px solid #222; }

        @keyframes fogoAnime {
            0% { transform: scale(0.9) translateY(0); opacity: 0.8; background: #d35400; }
            100% { transform: scale(1.1) translateY(-5px); opacity: 1; background: #f1c40f; }
        }

        /* Lado do Formulário */
        .lado-form { width: 60%; padding: 40px; box-sizing: border-box; }
        .lado-form h2 { color: var(--laranja); margin-top: 0; margin-bottom: 25px; }
        
        .form-group { margin-bottom: 25px; }
        label.titulo-campo { font-weight: bold; display: block; margin-bottom: 10px; color: var(--texto); font-size: 15px; }
        
        /* Campos Estilizados */
        input[type="date"], input[type="number"] { width: 100%; padding: 15px; border: 2px solid var(--borda); border-radius: 8px; font-size: 16px; outline: none; background: #fafafa; font-family: inherit; font-weight: bold; color: #555; transition: 0.3s; box-sizing: border-box; }
        input[type="date"]:focus, input[type="number"]:focus { border-color: var(--laranja); background: white; box-shadow: 0 0 8px rgba(211, 84, 0, 0.2); }
        
        .btn-agendar { width: 100%; padding: 15px; background: var(--laranja); color: white; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer; transition: 0.3s; text-transform: uppercase; margin-top: 10px; }
        .btn-agendar:hover { background: #b04600; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(211, 84, 0, 0.3); }
        .erro { background: #ffe6e6; color: #900; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: bold; }

        @media (max-width: 768px) { .container { flex-direction: column; } .lado-desenho, .lado-form { width: 100%; } }
    </style>
</head>
<body>

    <div class="header">
        <a href="painel.php" class="btn-voltar">← Voltar ao Painel</a>
        <div class="usuario-badge">👤 <?php echo htmlspecialchars($_SESSION['usuario_email']); ?></div>
    </div>

    <div class="container">
        
        <div class="lado-desenho">
            <h3>Preparando o Braseiro...</h3>
            <div class="churras-css">
                <div class="churras-chamine"></div>
                <div class="churras-meio">
                    <div class="churras-grelha"></div>
                    <div class="churras-fogo"></div>
                </div>
                <div class="churras-base"></div>
            </div>
        </div>

        <div class="lado-form">
            <h2>🍖 Reservar Churrasqueira</h2>
            <?php echo $mensagem; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="titulo-campo">📅 Data do Churrasco:</label>
                    <input type="date" name="data" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label class="titulo-campo">👥 Convidados (Quantidade Estimada):</label>
                    <input type="number" name="convidados" required min="1" max="50" placeholder="Ex: 20">
                </div>
                
                <button type="submit" class="btn-agendar">Garantir Churrasqueira</button>
            </form>
        </div>

    </div>

</body>
</html>