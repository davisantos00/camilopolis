<?php
require_once('funcoes.php');
if (!isset($_SESSION['usuario_email'])) {
    header("Location: login.php");
    exit();
}

$email_usuario = $_SESSION['usuario_email'];
$mensagem = "";
$tipo_msg = "";

$conn = new mysqli('localhost', 'root', '', 'camilopolis_db');

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

garantir_estrutura($conn);

// Garante que a coluna 'foto' existe na tabela usuarios sem dar erro se já existir
$conn->query("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS foto VARCHAR(255)");

// LÓGICA DE EXCLUSÃO DA CONTA
if (isset($_GET['excluir_conta']) && $_GET['excluir_conta'] == '1') {
    // Exclui reservas e o usuário para manter a consistência do banco
    $conn->query("DELETE FROM reservas WHERE usuario_email = '$email_usuario'");
    $conn->query("DELETE FROM reservas_churrasqueira WHERE usuario_email = '$email_usuario'");
    
    $stmt_del = $conn->prepare("DELETE FROM usuarios WHERE email = ?");
    $stmt_del->bind_param("s", $email_usuario);
    $stmt_del->execute();
    $stmt_del->close();

    // Pagamentos ainda não feitos deixam de existir junto com a conta
    $stmt_pg = $conn->prepare("DELETE FROM pagamentos WHERE usuario_email = ? AND status = 'pendente'");
    $stmt_pg->bind_param("s", $email_usuario);
    $stmt_pg->execute();
    $stmt_pg->close();

    session_unset();
    redirecionar('login.php', 'Sua conta foi excluída permanentemente.', 'info');
}

// Processamento de Atualizações do Perfil, Foto ou Senha
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Atualizar Dados Pessoais e Foto
    if (isset($_POST['atualizar_perfil'])) {
        $novo_nome = trim($_POST['nome']);
        
        // Upload da Foto de Perfil
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($extensao, $permitidas)) {
                if (!is_dir('uploads')) {
                    mkdir('uploads', 0777, true);
                }
                $nome_arquivo = md5($email_usuario . time()) . '.' . $extensao;
                $caminho_destino = 'uploads/' . $nome_arquivo;
                
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminho_destino)) {
                    $stmt_f = $conn->prepare("UPDATE usuarios SET foto = ? WHERE email = ?");
                    $stmt_f->bind_param("ss", $caminho_destino, $email_usuario);
                    $stmt_f->execute();
                    $stmt_f->close();
                }
            }
        }
        
        $stmt = $conn->prepare("UPDATE usuarios SET nome = ? WHERE email = ?");
        $stmt->bind_param("ss", $novo_nome, $email_usuario);
        
        if ($stmt->execute()) {
            $mensagem = "Perfil e foto atualizados com sucesso!";
            $tipo_msg = "sucesso";
        } else {
            $mensagem = "Erro ao atualizar os dados.";
            $tipo_msg = "erro";
        }
        $stmt->close();
    }
    
    // Atualizar Senha
    if (isset($_POST['atualizar_senha'])) {
        $senha_atual = $_POST['senha_atual'];
        $nova_senha = $_POST['nova_senha'];
        
        $stmt = $conn->prepare("SELECT senha FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email_usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($row = $res->fetch_assoc()) {
            $senha_cadastrada = $row['senha'];
            
            if ($senha_atual === $senha_cadastrada || password_verify($senha_atual, $senha_cadastrada)) {
                $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                
                $stmt_up = $conn->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
                $stmt_up->bind_param("ss", $nova_senha_hash, $email_usuario);
                $stmt_up->execute();
                $stmt_up->close();
                
                $mensagem = "Senha alterada com sucesso!";
                $tipo_msg = "sucesso";
            } else {
                $mensagem = "A senha atual está incorreta.";
                $tipo_msg = "erro";
            }
        }
        $stmt->close();
    }
}

// Busca os dados atualizados do usuário
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email_usuario);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Estatísticas da Conta (Total de Reservas)
$stmt_q = $conn->prepare("SELECT COUNT(*) as total FROM reservas WHERE usuario_email = ?");
$stmt_q->bind_param("s", $email_usuario);
$stmt_q->execute();
$total_quadras = $stmt_q->get_result()->fetch_assoc()['total'];
$stmt_q->close();

$stmt_c = $conn->prepare("SELECT COUNT(*) as total FROM reservas_churrasqueira WHERE usuario_email = ?");
$stmt_c->bind_param("s", $email_usuario);
$stmt_c->execute();
$total_churras = $stmt_c->get_result()->fetch_assoc()['total'];
$stmt_c->close();

$conn->close();

// Gera iniciais para caso não tenha foto
$nome_completo = $usuario['nome'] ?? 'Usuário';
$partes_nome = explode(' ', trim($nome_completo));
$iniciais = strtoupper(substr($partes_nome[0], 0, 1) . (isset($partes_nome[1]) ? substr(end($partes_nome), 0, 1) : ''));
$foto_perfil = $usuario['foto'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Painel | Associação Camilópolis</title>
    <style>
        :root { --azul: #0A3D73; --amarelo: #FFC107; --fundo: #f4f7f6; --texto: #333; --borda: #e0e0e0; --vermelho: #dc3545; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--fundo); margin: 0; color: var(--texto); }
        
        .header { width: 100%; padding: 20px; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; box-sizing: border-box; }
        .btn-voltar { color: var(--azul); text-decoration: none; font-weight: bold; font-size: 14px; }
        .btn-voltar:hover { text-decoration: underline; }
        .usuario-badge { background: var(--fundo); padding: 5px 15px; border-radius: 20px; font-size: 14px; font-weight: 500; color: var(--azul); }

        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; box-sizing: border-box; }
        
        .painel-grid { display: grid; grid-template-columns: 320px 1fr; gap: 30px; }

        .coluna-lateral { display: flex; flex-direction: column; gap: 20px; }
        .card-perfil-resumo { background: white; padding: 30px 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center; border-top: 5px solid var(--azul); }
        
        /* Avatar com Imagem ou Iniciais */
        .avatar-container { width: 85px; height: 85px; margin: 0 auto 15px auto; border-radius: 50%; overflow: hidden; background: var(--azul); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(10, 61, 115, 0.3); border: 3px solid #fff; }
        .avatar-container img { width: 100%; height: 100%; object-fit: cover; }
        .avatar-iniciais { color: white; font-size: 28px; font-weight: bold; }

        .card-perfil-resumo h3 { margin: 0 0 5px 0; color: var(--azul); font-size: 18px; }
        .card-perfil-resumo p { margin: 0; color: #666; font-size: 13px; word-break: break-all; }

        .stats-box { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .stats-box h4 { margin: 0 0 15px 0; color: var(--azul); font-size: 15px; border-bottom: 2px solid var(--fundo); padding-bottom: 8px; }
        .stat-item { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; font-size: 14px; color: #555; }
        .stat-badge { background: #eef4fc; color: var(--azul); padding: 4px 10px; border-radius: 6px; font-weight: bold; font-size: 13px; }

        .coluna-principal { display: flex; flex-direction: column; gap: 25px; }
        .card-painel { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .card-painel h3 { margin-top: 0; color: var(--azul); font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .card-perigo { border-left: 5px solid var(--vermelho); background: #fffdfd; }
        .card-perigo h3 { color: var(--vermelho); }

        .form-group { margin-bottom: 20px; }
        label { font-weight: 600; display: block; margin-bottom: 8px; color: var(--azul); font-size: 14px; }
        input[type="text"], input[type="email"], input[type="password"], input[type="file"] { width: 100%; padding: 12px; border: 1px solid var(--borda); border-radius: 8px; box-sizing: border-box; font-size: 15px; outline: none; background-color: #fafafa; transition: 0.3s; }
        input[type="text"]:focus, input[type="password"]:focus { border-color: var(--amarelo); background-color: white; box-shadow: 0 0 5px rgba(255, 193, 7, 0.3); }
        input[disabled] { background-color: #eef2f5; color: #777; cursor: not-allowed; }
        input[type="file"] { background: white; padding: 10px; cursor: pointer; }

        .btn-acao { background: var(--azul); color: white; border: none; padding: 12px 20px; border-radius: 8px; font-size: 15px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-acao:hover { background: #072a50; box-shadow: 0 4px 10px rgba(10, 61, 115, 0.2); }
        .btn-perigo { background: #ffe6e6; color: var(--vermelho); border: 1px solid #ffcccc; padding: 12px 20px; border-radius: 8px; font-size: 15px; font-weight: bold; cursor: pointer; transition: 0.3s; width: 100%; }
        .btn-perigo:hover { background: var(--vermelho); color: white; }

        .alerta-feedback { padding: 15px; border-radius: 8px; margin-bottom: 25px; text-align: center; font-size: 14px; font-weight: 500; }
        .sucesso { background: #e6f9ed; color: #155724; border: 1px solid #c3e6cb; }
        .erro { background: #ffe6e6; color: #900; border: 1px solid #ffcccc; }

        /* MODAL DE PERIGO (EXCLUSÃO DE CONTA) */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); display: none; justify-content: center; align-items: center; z-index: 1000; backdrop-filter: blur(2px); }
        .modal-box { background: white; padding: 30px; border-radius: 14px; max-width: 440px; width: 90%; box-shadow: 0 10px 30px rgba(0,0,0,0.3); text-align: center; animation: modalEntrada 0.3s ease; }
        
        @keyframes modalEntrada {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .modal-icone { font-size: 45px; margin-bottom: 10px; }
        .modal-box h3 { color: var(--vermelho); margin-top: 0; margin-bottom: 15px; font-size: 22px; }
        .modal-box p { color: #555; font-size: 14px; line-height: 1.6; text-align: left; background: #fff5f5; padding: 15px; border-radius: 8px; border: 1px solid #ffcccc; margin-bottom: 25px; }
        .modal-botoes { display: flex; gap: 10px; }
        .btn-modal-voltar { flex: 1; padding: 12px; background: #e0e0e0; color: #333; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.2s; }
        .btn-modal-voltar:hover { background: #d0d0d0; }
        .btn-modal-confirmar { flex: 1; padding: 12px; background: var(--vermelho); color: white; border: none; border-radius: 8px; font-weight: bold; text-decoration: none; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
        .btn-modal-confirmar:hover { background: #b02a37; }

        @media (max-width: 768px) {
            .painel-grid { grid-template-columns: 1fr; }
            .container { margin: 20px auto; padding: 0 16px; }
            .card-painel { padding: 20px; }
        }
    </style>
    <link rel="stylesheet" href="comum.css">
    <script src="comum.js" defer></script>
    <script>
        function abrirModalExclusao() {
            document.getElementById('modalExclusao').style.display = 'flex';
        }
        function fecharModalExclusao() {
            document.getElementById('modalExclusao').style.display = 'none';
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
        
        <?php if (!empty($mensagem)): ?>
            <div class="alerta-feedback <?php echo $tipo_msg; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <div class="painel-grid">
            
            <!-- COLUNA ESQUERDA: PERFIL E ESTATÍSTICAS -->
            <div class="coluna-lateral">
                <div class="card-perfil-resumo">
                    <div class="avatar-container">
                        <?php if (!empty($foto_perfil) && file_exists($foto_perfil)): ?>
                            <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de Perfil">
                        <?php else: ?>
                            <div class="avatar-iniciais"><?php echo $iniciais; ?></div>
                        <?php endif; ?>
                    </div>
                    <h3><?php echo htmlspecialchars($usuario['nome'] ?? 'Sócio'); ?></h3>
                    <p><?php echo htmlspecialchars($email_usuario); ?></p>
                </div>

                <div class="stats-box">
                    <h4>📊 Atividade na Conta</h4>
                    <div class="stat-item">
                        <span>Quadras Reservadas:</span>
                        <span class="stat-badge"><?php echo $total_quadras; ?></span>
                    </div>
                    <div class="stat-item">
                        <span>Churrasqueiras:</span>
                        <span class="stat-badge"><?php echo $total_churras; ?></span>
                    </div>
                </div>
            </div>

            <!-- COLUNA DIREITA: FORMULÁRIOS DE CONFIGURAÇÃO -->
            <div class="coluna-principal">
                
                <!-- DADOS PESSOAIS E FOTO -->
                <div class="card-painel">
                    <h3>✏️ Informações Pessoais & Foto</h3>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Nome Completo:</label>
                            <input type="text" name="nome" value="<?php echo htmlspecialchars($usuario['nome'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>E-mail Cadastrado (Login):</label>
                            <input type="email" value="<?php echo htmlspecialchars($email_usuario); ?>" disabled>
                        </div>

                        <div class="form-group">
                            <label>Alterar Foto de Perfil:</label>
                            <input type="file" name="foto" accept="image/png, image/jpeg, image/webp">
                        </div>

                        <button type="submit" name="atualizar_perfil" class="btn-acao">Salvar Alterações</button>
                    </form>
                </div>

                <!-- SEGURANÇA / SENHA -->
                <div class="card-painel">
                    <h3>🔒 Alterar Senha de Acesso</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Senha Atual:</label>
                            <input type="password" name="senha_atual" placeholder="Digite sua senha atual" required>
                        </div>

                        <div class="form-group">
                            <label>Nova Senha:</label>
                            <input type="password" name="nova_senha" placeholder="Digite a nova senha" required>
                        </div>

                        <button type="submit" name="atualizar_senha" class="btn-acao" style="background: #2c3e50;">Atualizar Senha</button>
                    </form>
                </div>

                <!-- ZONA DE PERIGO (EXCLUSÃO DE CONTA) -->
                <div class="card-painel card-perigo">
                    <h3>⚠️ Zona de Perigo</h3>
                    <p style="font-size: 14px; color: #666; margin-bottom: 20px;">
                        A exclusão da conta é uma ação definitiva. Todo o seu histórico de cadastro, dados pessoais e reservas agendadas serão apagados permanentemente dos nossos servidores.
                    </p>
                    <button type="button" onclick="abrirModalExclusao()" class="btn-perigo">Excluir Minha Conta</button>
                </div>

            </div>

        </div>

    </div>

    <!-- MODAL DE ALERTA DE EXCLUSÃO DE CONTA -->
    <div id="modalExclusao" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-icone">🚨</div>
            <h3>ALERTA DE PERIGO!</h3>
            <p>
                <strong>Você tem certeza absoluta de que deseja excluir sua conta?</strong><br><br>
                Esta operação é <u>irreversível</u>. Todos os seus registros na Associação Camilópolis, incluindo o histórico de agendamentos de quadras e churrasqueiras, serão permanentemente destruídos e você perderá o acesso imediato ao sistema.
            </p>
            <div class="modal-botoes">
                <button onclick="fecharModalExclusao()" class="btn-modal-voltar">Cancelar</button>
                <a href="meu_perfil.php?excluir_conta=1" class="btn-modal-confirmar">Sim, Excluir Conta</a>
            </div>
        </div>
    </div>

    <div class="rodape-interno"><?php echo htmlspecialchars(texto_direitos()); ?></div>

</body>
</html>