<?php
session_start();
include_once('conexao.php'); // Verifica se o nome do seu arquivo de conexão é esse mesmo

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturando os dados do formulário
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // CAPTURANDO O NOVO CAMPO DE TELEFONE
    $telefone = mysqli_real_escape_string($conn, $_POST['telefone']); 
    
    $senha = $_POST['senha'];
    $confirma_senha = $_POST['confirma_senha'];

    // Verifica se as senhas batem
    if ($senha !== $confirma_senha) {
        echo "<script>alert('As senhas não coincidem!'); window.location.href='cadastro.php';</script>";
        exit();
    }

    // Verifica se o email já existe no banco
    $sql_verifica = "SELECT * FROM usuarios WHERE email = '$email'";
    $resultado_verifica = mysqli_query($conn, $sql_verifica);

    if (mysqli_num_rows($resultado_verifica) > 0) {
        echo "<script>alert('Este e-mail já está cadastrado!'); window.location.href='cadastro.php';</script>";
    } else {
        // Inserindo no banco de dados incluindo o telefone
        // Importante: a tabela 'usuarios' precisa ter a coluna 'telefone' criada!
        $sql_inserir = "INSERT INTO usuarios (nome, email, telefone, senha) VALUES ('$nome', '$email', '$telefone', '$senha')";
        
        if (mysqli_query($conn, $sql_inserir)) {
            echo "<script>alert('Cadastro realizado com sucesso! Faça seu login.'); window.location.href='login.php';</script>";
        } else {
            echo "Erro ao cadastrar: " . mysqli_error($conn);
        }
    }
}
?>