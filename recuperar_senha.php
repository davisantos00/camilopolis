<?php
require_once('funcoes.php');
@include_once('conexao.php');

$mensagem = "";
$etapa = isset($_SESSION['etapa_recuperacao']) ? $_SESSION['etapa_recuperacao'] : 1;

// Permite ao usuário reiniciar o processo ou alterar o e-mail
if (isset($_GET['cancelar'])) {
    unset($_SESSION['etapa_recuperacao']);
    unset($_SESSION['email_recuperacao']);
    header("Location: recuperar_senha.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // ==========================================
    // ETAPA 1: SOLICITAR CÓDIGO
    // ==========================================
    if (isset($_POST['acao_enviar_codigo'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);

        $sql = "SELECT * FROM usuarios WHERE email = '$email'";
        $resultado = mysqli_query($conn, $sql);

        if (mysqli_num_rows($resultado) > 0) {
            // Gera um código de 6 dígitos aleatórios
            $codigo = sprintf("%06d", mt_rand(1, 999999));
            
            // Salva o código e define validade de 15 minutos a partir de agora
            $sql_code = "UPDATE usuarios SET 
                            codigo_recuperacao = '$codigo', 
                            codigo_expiracao = DATE_ADD(NOW(), INTERVAL 15 MINUTE) 
                         WHERE email = '$email'";
            
            if (mysqli_query($conn, $sql_code)) {
                // Envia o e-mail com o código de verificação
                $assunto = "Codigo de Confirmacao - A.A. Camilopolis";
                $mensagem_email = "Voce solicitou a redefinicao de senha.\n\nSeu codigo de verificacao e: $codigo\n\nEste codigo e valido por 15 minutos.";
                $headers = "From: suporte@camilopolis.com.br\r\n" .
                           "Reply-To: suporte@camilopolis.com.br\r\n" .
                           "X-Mailer: PHP/" . phpversion();

                @mail($email, $assunto, $mensagem_email, $headers);

                $_SESSION['etapa_recuperacao'] = 2;
                $_SESSION['email_recuperacao'] = $email;
                $etapa = 2;
                $mensagem = "<p style='color: #28a745; font-weight:bold; text-align:center; background: #e8f5e9; padding: 10px; border-radius: 6px; font-size: 13px;'>Código enviado para $email! Verifique sua caixa de entrada/spam.</p>";
            } else {
                $mensagem = "<p style='color: #d9534f; font-weight:bold; text-align:center;'>Erro ao gerar código. Tente novamente.</p>";
            }
        } else {
            $mensagem = "<p style='color: #d9534f; font-weight:bold; text-align:center;'>E-mail não encontrado no sistema!</p>";
        }
    }

    // ==========================================
    // ETAPA 2: VALIDAR CÓDIGO E DEFINIR NOVA SENHA
    // ==========================================
    if (isset($_POST['acao_validar_codigo'])) {
        $email = $_SESSION['email_recuperacao'] ?? '';
        $codigo_digitado = mysqli_real_escape_string($conn, $_POST['codigo']);
        $nova_senha = $_POST['nova_senha'];
        $confirma_senha = $_POST['confirma_senha'];

        if ($nova_senha !== $confirma_senha) {
            $mensagem = "<p style='color: #d9534f; font-weight:bold; text-align:center;'>As senhas digitadas não coincidem!</p>";
        } else {
            // Verifica se o código está correto e dentro do prazo de 15 minutos
            $sql_valida = "SELECT * FROM usuarios 
                           WHERE email = '$email' 
                           AND codigo_recuperacao = '$codigo_digitado' 
                           AND codigo_expiracao >= NOW()";
            
            $resultado_valida = mysqli_query($conn, $sql_valida);

            if (mysqli_num_rows($resultado_valida) > 0) {
                // Atualiza a senha e invalida o código usado
                $senha_final = mysqli_real_escape_string($conn, criptografar_senha($nova_senha));
                
                $sql_update = "UPDATE usuarios SET 
                                senha = '$senha_final', 
                                codigo_recuperacao = NULL, 
                                codigo_expiracao = NULL 
                               WHERE email = '$email'";

                if (mysqli_query($conn, $sql_update)) {
                    unset($_SESSION['etapa_recuperacao']);
                    unset($_SESSION['email_recuperacao']);
                    redirecionar('login.php', 'Senha alterada com sucesso! Faça login com a nova senha.');
                } else {
                    $mensagem = "<p style='color: #d9534f; font-weight:bold; text-align:center;'>Erro ao atualizar a senha.</p>";
                }
            } else {
                $mensagem = "<p style='color: #d9534f; font-weight:bold; text-align:center;'>Código incorreto ou expirado! Verifique e tente novamente.</p>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - Associação Amigos de Camilópolis</title>
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
            position: absolute; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background-color: rgba(10, 61, 115, 0.75); 
            backdrop-filter: blur(12px); 
            -webkit-backdrop-filter: blur(12px); 
            z-index: 1; 
        }

        .login-card { position: relative; z-index: 2; background: var(--branco); padding: 40px; border-radius: 12px; box-shadow: 0 15px 35px rgba(0,0,0,0.4); width: 100%; max-width: 400px; border-top: 6px solid var(--amarelo); }
        
        h2 { color: var(--azul-escuro); text-align: center; margin-bottom: 8px; }
        p.instrucao { text-align: center; color: #666; font-size: 13px; margin-bottom: 20px; }
        
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; color: #333; font-size: 14px; }
        input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        input:focus { border-color: var(--azul-claro); outline: none; box-shadow: 0 0 5px rgba(26, 91, 156, 0.3); }
        
        .codigo-input { font-size: 20px; letter-spacing: 6px; text-align: center; font-weight: bold; }

        .btn { width: 100%; padding: 12px; background-color: var(--azul-claro); color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 15px; margin-top: 5px; }
        .btn:hover { background-color: var(--azul-escuro); }
        
        .opcoes { margin-top: 25px; text-align: center; font-size: 14px; border-top: 1px solid #eee; padding-top: 20px; }
        .opcoes a { color: var(--azul-claro); text-decoration: none; font-weight: bold; display: block; margin-bottom: 6px; }
        .opcoes a:hover { text-decoration: underline; }
    </style>
    <link rel="stylesheet" href="comum.css">
    <script src="comum.js" defer></script>
</head>
<body class="pagina-acesso">
<?php exibir_aviso(); ?>
<div class="overlay"></div>

<div class="login-card">
    <h2>Recuperar Senha</h2>
    
    <?php echo $mensagem; ?>

    <?php if ($etapa == 1): ?>
        <!-- FORMULÁRIO ETAPA 1 -->
        <p class="instrucao">Digite seu e-mail cadastrado para receber um código de verificação.</p>
        <form action="recuperar_senha.php" method="POST">
            <div class="form-group">
                <label>E-mail Cadastrado</label>
                <input type="email" name="email" required placeholder="seu@email.com">
            </div>
            <button type="submit" name="acao_enviar_codigo" class="btn">Enviar Código de Confirmação</button>
        </form>
    <?php else: ?>
        <!-- FORMULÁRIO ETAPA 2 -->
        <p class="instrucao">Digite o código enviado por e-mail e crie sua nova senha.</p>
        <form action="recuperar_senha.php" method="POST">
            <div class="form-group">
                <label>Código de Verificação (6 dígitos)</label>
                <input type="text" name="codigo" class="codigo-input" maxlength="6" required placeholder="000000">
            </div>
            <div class="form-group">
                <label>Nova Senha</label>
                <input type="password" name="nova_senha" required placeholder="Digite a nova senha">
            </div>
            <div class="form-group">
                <label>Confirme a Nova Senha</label>
                <input type="password" name="confirma_senha" required placeholder="Repita a nova senha">
            </div>
            <button type="submit" name="acao_validar_codigo" class="btn">Confirmar e Alterar Senha</button>
        </form>
        <p style="text-align:center; margin-top: 15px;">
            <a href="recuperar_senha.php?cancelar=1" style="color: #666; font-size: 12px; text-decoration: underline;">Digitar outro e-mail</a>
        </p>
    <?php endif; ?>

    <div class="opcoes">
        <a href="login.php">Voltar ao Login</a>
    </div>
</div>
</body>
</html>