<?php
session_start();
include_once('conexao.php');

$mensagem = "";
$sucesso = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['usuario_email'];
    $data = mysqli_real_escape_string($conn, $_POST['data']);
    $convidados = mysqli_real_escape_string($conn, $_POST['convidados']);

    // Insere a reserva da churrasqueira no banco
    $sql = "INSERT INTO agendamentos_churrasqueira (email_usuario, data, convidados) VALUES ('$email', '$data', '$convidados')";
    
    if (mysqli_query($conn, $sql)) {
        $sucesso = true;
        $mensagem = "Combo Quadra + Churrasqueira reservado com sucesso! Aproveite!";
    } else {
        $mensagem = "Erro ao salvar: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Confirmação do Combo</title>
    <style>
        :root { --azul-escuro: #0A3D73; --amarelo: #FFC107; --branco: #FFFFFF; }
        body { font-family: 'Segoe UI', sans-serif; background: #0A3D73; height: 100vh; display: flex; justify-content: center; align-items: center; margin: 0; }
        .card { background: var(--branco); padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); text-align: center; width: 90%; max-width: 400px; border-top: 8px solid <?php echo $sucesso ? '#28a745' : '#d9534f'; ?>; }
        h2 { color: var(--azul-escuro); }
        .btn-voltar { display: block; width: 100%; padding: 12px; margin-top: 20px; background: var(--azul-escuro); color: white; text-decoration: none; border-radius: 6px; font-weight: bold; }
        .btn-voltar:hover { background: #0d4a8a; }
    </style>
</head>
<body>
<div class="card">
    <h2><?php echo $sucesso ? "Combo Reservado!" : "Ops, houve um erro"; ?></h2>
    <p><?php echo $mensagem; ?></p>
    <a href="painel.php" class="btn-voltar">Voltar para o Início</a>
</div>
</body>
</html>