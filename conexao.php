<?php
// conexao.php
$servidor = "localhost";
$usuario = "root"; 
$senha = "";       
$banco = "camilopolis_db";

// Cria a conexão
$conn = mysqli_connect($servidor, $usuario, $senha, $banco);

// Verifica se houve erro na conexão
if (!$conn) {
    die("Falha na conexão com o banco de dados: " . mysqli_connect_error());
}
?>