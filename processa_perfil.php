<?php
require_once('funcoes.php');
include_once('conexao.php');

if (!isset($_SESSION['usuario_email'])) {
    header("Location: login.php");
    exit();
}

$email_atual = $_SESSION['usuario_email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $acao = $_POST['acao'];

    // 1. ATUALIZAR DADOS (Nome e E-mail)
    if ($acao == "atualizar_dados") {
        $novo_nome = mysqli_real_escape_string($conn, $_POST['nome']);
        $novo_email = mysqli_real_escape_string($conn, $_POST['email']);

        $sql = "UPDATE usuarios SET nome = '$novo_nome', email = '$novo_email' WHERE email = '$email_atual'";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['usuario_nome'] = $novo_nome; // Atualiza a sessão
            $_SESSION['usuario_email'] = $novo_email;
            redirecionar('meu_perfil.php', 'Dados atualizados com sucesso!', 'sucesso');
        } else {
            redirecionar('meu_perfil.php', 'Erro ao atualizar.', 'erro');
        }
    }

    // 2. ALTERAR SENHA
    elseif ($acao == "alterar_senha") {
        $senha_atual = $_POST['senha_atual'];
        $nova_senha = $_POST['nova_senha'];

        // Primeiro, busca a senha atual cadastrada no banco de dados
        $sql_verifica = "SELECT senha FROM usuarios WHERE email = '$email_atual'";
        $resultado_verifica = mysqli_query($conn, $sql_verifica);
        $dados_usuario = mysqli_fetch_assoc($resultado_verifica);

        // Verifica se a senha que ele digitou no campo "Senha Atual" bate com a do banco
        if (senha_confere($senha_atual, $dados_usuario['senha'])) {
            
            // Se estiver correta, atualiza para a nova senha
            $nova_senha_hash = mysqli_real_escape_string($conn, criptografar_senha($nova_senha));
            $sql = "UPDATE usuarios SET senha = '$nova_senha_hash' WHERE email = '$email_atual'";
            if (mysqli_query($conn, $sql)) {
                redirecionar('meu_perfil.php', 'Senha alterada com sucesso!', 'sucesso');
            } else {
                redirecionar('meu_perfil.php', 'Erro ao alterar senha no banco de dados.', 'erro');
            }

        } else {
            // Se a senha atual estiver errada, bloqueia a ação
            redirecionar('meu_perfil.php', 'ERRO: A senha atual está incorreta. Nenhuma alteração foi feita.', 'erro');
        }
    }

    // 3. APAGAR CONTA
    elseif ($acao == "apagar_conta") {
        // Primeiro, exclui as reservas da pessoa (opcional, mas recomendado)
        mysqli_query($conn, "DELETE FROM agendamentos WHERE email_usuario = '$email_atual'");
        mysqli_query($conn, "DELETE FROM agendamentos_churrasqueira WHERE email_usuario = '$email_atual'");
        
        // Depois exclui o usuário
        $sql = "DELETE FROM usuarios WHERE email = '$email_atual'";
        if (mysqli_query($conn, $sql)) {
            session_unset(); // Limpa a sessão (mantém só o aviso de despedida)
            redirecionar('index.php', 'Conta apagada com sucesso. Sentiremos sua falta!', 'sucesso');
        } else {
            redirecionar('meu_perfil.php', 'Erro ao apagar conta.', 'erro');
        }
    }
    
    // 4. ALTERAR FOTO (Requer banco de dados modificado)
    elseif ($acao == "alterar_foto") {
        redirecionar('meu_perfil.php', 'Para o upload de fotos funcionar, precisamos adicionar uma coluna de foto no banco de dados. Funcionalidade em construção!', 'info');
    }
}
?>