<?php
session_start();
include_once('conexao.php');

$mensagem = "";
$sucesso = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['usuario_email'];
    $data = mysqli_real_escape_string($conn, $_POST['data']);
    $horario = mysqli_real_escape_string($conn, $_POST['horario']);

    $sql_check = "SELECT * FROM agendamentos WHERE data = '$data' AND horario = '$horario'";
    $res_check = mysqli_query($conn, $sql_check);

    if (mysqli_num_rows($res_check) > 0) {
        $mensagem = "Este horário já está reservado!";
    } else {
        $sql = "INSERT INTO agendamentos (email_usuario, data, horario) VALUES ('$email', '$data', '$horario')";
        if (mysqli_query($conn, $sql)) {
            $sucesso = true;
            $mensagem = "Reserva da quadra confirmada com sucesso!";
        } else {
            $mensagem = "Erro: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Reserva Concluída</title>
    <style>
        :root { --azul-escuro: #0A3D73; --azul-claro: #1A5B9C; --amarelo: #FFC107; --branco: #FFFFFF; }
        body { font-family: 'Segoe UI', sans-serif; background: #0A3D73; height: 100vh; display: flex; justify-content: center; align-items: center; margin: 0; }
        .card { background: var(--branco); padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); text-align: center; width: 90%; max-width: 450px; border-top: 8px solid <?php echo $sucesso ? '#28a745' : '#d9534f'; ?>; }
        .promo { background: #fff3cd; padding: 15px; border-radius: 8px; border: 1px solid #ffeeba; margin: 20px 0; color: #856404; }
        .btn-acao { display: block; width: 100%; padding: 12px; margin-top: 10px; border-radius: 6px; text-decoration: none; font-weight: bold; }
        .btn-sucesso { background: var(--azul-claro); color: white; }
        .btn-promo { background: var(--amarelo); color: var(--azul-escuro); }
    </style>
</head>
<body>
<div class="card">
    <h2><?php echo $sucesso ? "Tudo certo!" : "Atenção"; ?></h2>
    <p><?php echo $mensagem; ?></p>
    <?php if ($sucesso): ?>
        <div class="promo"><strong>🔥 Combo:</strong> Inclua a Churrasqueira e ganhe 20% de desconto!</div>
        <a href="agendar_churrasqueira.php" class="btn-acao btn-promo">Quero incluir a Churrasqueira</a>
        <a href="painel.php" class="btn-acao btn-sucesso">Finalizar apenas com a quadra</a>
    <?php else: ?>
        <a href="agendar.php" class="btn-acao btn-sucesso">Tentar outro horário</a>
    <?php endif; ?>
</div>
</body>
</html>