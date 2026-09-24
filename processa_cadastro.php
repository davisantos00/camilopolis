<?php
require_once('funcoes.php');
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
        redirecionar('cadastro.php', 'As senhas não coincidem!', 'erro');
    }

    // Verifica se o email já existe no banco
    $sql_verifica = "SELECT * FROM usuarios WHERE email = '$email'";
    $resultado_verifica = mysqli_query($conn, $sql_verifica);

    if (mysqli_num_rows($resultado_verifica) > 0) {
        redirecionar('cadastro.php', 'Este e-mail já está cadastrado!', 'erro');
    } else {
        // Inserindo no banco de dados incluindo o telefone
        // A senha é salva criptografada (nunca em texto puro)
        $senha_hash = mysqli_real_escape_string($conn, criptografar_senha($senha));
        $sql_inserir = "INSERT INTO usuarios (nome, email, telefone, senha) VALUES ('$nome', '$email', '$telefone', '$senha_hash')";

        if (mysqli_query($conn, $sql_inserir)) {
            redirecionar('login.php', 'Cadastro realizado com sucesso! Faça seu login.');
        } else {
            echo "Erro ao cadastrar: " . mysqli_error($conn);
        }
    }
}
?>