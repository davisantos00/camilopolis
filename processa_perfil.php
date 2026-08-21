<?php
session_start();
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
            echo "<script>alert('Dados atualizados com sucesso!'); window.location.href='meu_perfil.php';</script>";
        } else {
            echo "<script>alert('Erro ao atualizar.'); window.location.href='meu_perfil.php';</script>";
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
        if ($dados_usuario['senha'] == $senha_atual) {
            
            // Se estiver correta, atualiza para a nova senha
            $sql = "UPDATE usuarios SET senha = '$nova_senha' WHERE email = '$email_atual'";
            if (mysqli_query($conn, $sql)) {
                echo "<script>alert('Senha alterada com sucesso!'); window.location.href='meu_perfil.php';</script>";
            } else {
                echo "<script>alert('Erro ao alterar senha no banco de dados.'); window.location.href='meu_perfil.php';</script>";
            }

        } else {
            // Se a senha atual estiver errada, bloqueia a ação
            echo "<script>alert('ERRO: A senha atual está incorreta. Nenhuma alteração foi feita.'); window.location.href='meu_perfil.php';</script>";
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
            session_destroy(); // Limpa a sessão
            echo "<script>alert('Conta apagada com sucesso. Sentiremos sua falta!'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Erro ao apagar conta.'); window.location.href='meu_perfil.php';</script>";
        }
    }
    
    // 4. ALTERAR FOTO (Requer banco de dados modificado)
    elseif ($acao == "alterar_foto") {
        echo "<script>alert('Para o upload de fotos funcionar, precisamos adicionar uma coluna de foto no banco de dados. Funcionalidade em construção!'); window.location.href='meu_perfil.php';</script>";
    }
}
?>