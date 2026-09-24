<?php
// A lógica pesada vai ficar no arquivo processa_cadastro.php
require_once('funcoes.php');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Sócio - Associação Amigos de Camilópolis</title>
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
        
        .overlay { 
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
            background-color: rgba(10, 61, 115, 0.75); 
            backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); 
            z-index: 1; 
        }
        
        .cadastro-card { 
            position: relative; z-index: 2; background: var(--branco); padding: 40px; 
            border-radius: 12px; box-shadow: 0 15px 35px rgba(0,0,0,0.4); 
            width: 100%; max-width: 450px; border-top: 6px solid var(--amarelo); 
        }
        
        h2 { color: var(--azul-escuro); text-align: center; margin-bottom: 25px; }
        .form-group { margin-bottom: 15px; }
        
        label { display: block; margin-bottom: 5px; color: #333; font-weight: 600; font-size: 14px; }
        input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        input:focus { border-color: var(--azul-claro); outline: none; box-shadow: 0 0 5px rgba(26, 91, 156, 0.3); }
        
        .btn-cadastro { 
            width: 100%; padding: 12px; background-color: var(--azul-claro); color: white; 
            border: none; border-radius: 6px; font-weight: bold; cursor: pointer; margin-top: 10px; transition: 0.3s; font-size: 15px;
        }
        .btn-cadastro:hover { background-color: var(--azul-escuro); }
        
        .footer-link { text-align: center; margin-top: 25px; font-size: 14px; border-top: 1px solid #eee; padding-top: 20px; }
        .footer-link a { color: var(--azul-claro); text-decoration: none; font-weight: bold; }
        .footer-link a:hover { text-decoration: underline; }
    </style>
    <link rel="stylesheet" href="comum.css">
    <script src="comum.js" defer></script>
</head>
<body class="pagina-acesso">
<?php exibir_aviso(); ?>
<div class="overlay"></div>

<div class="cadastro-card">
    <h2>Criar seu Cadastro</h2>
    
    <form action="processa_cadastro.php" method="POST">
        <div class="form-group">
            <label>Nome Completo</label>
            <input type="text" name="nome" required placeholder="Digite seu nome">
        </div>
        <div class="form-group">
            <label>E-mail</label>
            <input type="email" name="email" required placeholder="seu@email.com">
        </div>
        
        <!-- NOVO CAMPO DE TELEFONE ADICIONADO AQUI -->
        <div class="form-group">
            <label>WhatsApp / Telefone</label>
            <input type="tel" name="telefone" required placeholder="(11) 90000-0000">
        </div>

        <div class="form-group">
            <label>Senha</label>
            <input type="password" name="senha" required placeholder="Crie uma senha">
        </div>
        <div class="form-group">
            <label>Confirmar Senha</label>
            <input type="password" name="confirma_senha" required placeholder="Repita a senha">
        </div>
        
        <button type="submit" class="btn-cadastro">Finalizar Cadastro</button>
    </form>

    <div class="footer-link">
        Já possui uma conta? <a href="login.php">Faça o login aqui</a>
    </div>
</div>
</body>
</html>