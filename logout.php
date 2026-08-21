<?php
// logout.php
session_start();      // Inicia a sessão para poder destruí-la
session_unset();      // Limpa todas as variáveis da sessão
session_destroy();    // Destrói a sessão no servidor

// Redireciona o usuário para a página inicial (index.php)
header("Location: index.php");
exit();
?>