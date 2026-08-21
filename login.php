<?php
session_start();
@include_once('conexao.php');

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $senha = $_POST['senha']; 

    $sql = "SELECT * FROM usuarios WHERE email = '$email'";
    $resultado = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($resultado) > 0) {
        $dados = mysqli_fetch_assoc($resultado);
        if ($senha === $dados['senha']) {
            $_SESSION['usuario_nome'] = $dados['nome'];
            $_SESSION['usuario_email'] = $dados['email'];
            header("Location: painel.php");
            exit(); 
        } else {
            $mensagem = "<p style='color: #ffcc00; font-weight:bold; text-align:center;'>Senha incorreta!</p>";
        }
    } else {
        $mensagem = "<p style='color: #ffcc00; font-weight:bold; text-align:center;'>E-mail não cadastrado!</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - Associação Amigos de Camilópolis</title>
    <style>
        :root { --azul-escuro: #0A3D73; --azul-claro: #1A5B9C; --amarelo: #FFC107; --branco: #FFFFFF; }
        
        body { 
            font-family: 'Segoe UI', sans-serif; 
            margin: 0; 
            height: 100vh; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            background: url('img/logo_sac.jpg') center/cover no-repeat; 
        }
        
        /* A MÁGICA DO CÓDIGO AQUI: O backdrop-filter desfoca os pixels do fundo */
        .overlay { 
            position: absolute; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background-color: rgba(10, 61, 115, 0.75); /* Deixei um pouco mais transparente para o efeito funcionar bem */
            backdrop-filter: blur(12px); /* Desfoca a imagem de fundo escondendo os pixels */
            -webkit-backdrop-filter: blur(12px); /* Suporte para navegadores Safari */
            z-index: 1; 
        }

        .login-card { position: relative; z-index: 2; background: var(--branco); padding: 40px; border-radius: 12px; box-shadow: 0 15px 35px rgba(0,0,0,0.4); width: 100%; max-width: 400px; border-top: 6px solid var(--amarelo); }
        
        h2 { color: var(--azul-escuro); text-align: center; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 14px; }
        
        input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        input:focus { border-color: var(--azul-claro); outline: none; box-shadow: 0 0 5px rgba(26, 91, 156, 0.3); }
        
        .btn { width: 100%; padding: 12px; background-color: var(--azul-claro); color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 15px; }
        .btn:hover { background-color: var(--azul-escuro); }
        
        .opcoes { margin-top: 25px; text-align: center; font-size: 14px; border-top: 1px solid #eee; padding-top: 20px; }
        .opcoes a { color: var(--azul-claro); text-decoration: none; font-weight: bold; display: block; margin-bottom: 8px; }
        .opcoes a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="overlay"></div>

<div class="login-card">
    <h2>Login de Sócio</h2>
    <?php echo $mensagem; ?>
    
    <form action="login.php" method="POST">
        <div class="form-group">
            <label>E-mail</label>
            <input type="email" name="email" required placeholder="seu@email.com">
        </div>
        <div class="form-group">
            <label>Senha</label>
            <input type="password" name="senha" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn">Entrar no Sistema</button>
    </form>

    <div class="opcoes">
        <a href="cadastro.php">Não possui cadastro? Clique aqui</a>
        <a href="recuperar_senha.php" style="color: #666; font-weight: normal;">Esqueceu sua senha?</a>
        <a href="index.php" style="color: #666; font-size: 12px; margin-top: 20px;">Voltar para o site</a>
    </div>
</div>
</body>
</html>