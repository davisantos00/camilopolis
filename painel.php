<?php
session_start();
if (!isset($_SESSION['usuario_email'])) {
    header("Location: login.php");
    exit();
}

$email_usuario = $_SESSION['usuario_email'];

// Conexão para buscar o nome real do usuário logado
$conn = new mysqli('localhost', 'root', '', 'camilopolis_db');
$nome_exibicao = "Usuário";

if (!$conn->connect_error) {
    $stmt = $conn->prepare("SELECT nome FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email_usuario);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $nome_exibicao = $row['nome'];
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Principal | Associação Camilópolis</title>
    <style>
        :root { --azul-topo: #072a50; --azul: #0A3D73; --amarelo: #FFC107; --fundo: #f4f7f6; --texto: #333; --borda: #e0e0e0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--fundo); margin: 0; color: var(--texto); }
        
        /* Topo / Header */
        .header { width: 100%; background: var(--azul-topo); padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-sizing: border-box; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .logo-container { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .logo-img { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid var(--amarelo); }
        .logo-texto { color: white; font-size: 20px; font-weight: bold; letter-spacing: 0.5px; }
        
        .usuario-info { text-align: right; color: white; font-size: 14px; }
        .usuario-info span { font-weight: bold; color: var(--amarelo); }
        .btn-sair { display: inline-block; color: #ff9999; text-decoration: none; font-size: 13px; font-weight: bold; margin-top: 2px; transition: 0.2s; }
        .btn-sair:hover { color: #ff4d4d; text-decoration: underline; }

        /* Layout Principal */
        .container-principal { max-width: 1200px; margin: 30px auto; padding: 0 20px; display: grid; grid-template-columns: 280px 1fr; gap: 30px; box-sizing: border-box; }

        /* Menu de Navegação Lateral */
        .menu-lateral { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); height: fit-content; }
        .menu-titulo { font-size: 16px; font-weight: bold; color: var(--azul); margin-bottom: 15px; border-bottom: 2px solid var(--amarelo); padding-bottom: 8px; }
        
        .nav-card { display: flex; align-items: center; gap: 15px; background: #fff; border: 2px solid var(--borda); padding: 15px; border-radius: 10px; margin-bottom: 12px; text-decoration: none; transition: 0.3s; }
        .nav-card:hover { border-color: var(--azul); background: #f0f4f8; transform: translateX(3px); }
        .nav-icone { font-size: 22px; width: 40px; height: 40px; background: #eef4fc; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .nav-detalhes h4 { margin: 0 0 3px 0; color: var(--azul); font-size: 15px; }
        .nav-detalhes p { margin: 0; color: #666; font-size: 12px; }

        /* Conteúdo Central */
        .conteudo-central { display: flex; flex-direction: column; gap: 25px; }
        
        /* Mural de Novidades */
        .secao-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .secao-titulo { font-size: 18px; font-weight: bold; color: var(--azul); margin-top: 0; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid var(--amarelo); padding-bottom: 8px; }
        
        .novidade-item { background: #fafafa; border-left: 4px solid var(--azul); padding: 15px 20px; border-radius: 0 8px 8px 0; margin-bottom: 15px; border-top: 1px solid var(--borda); border-right: 1px solid var(--borda); border-bottom: 1px solid var(--borda); }
        .novidade-item.destaque { border-left-color: var(--amarelo); background: #fffdf5; }
        .novidade-tag { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #777; margin-bottom: 5px; display: block; }
        .novidade-titulo { font-size: 16px; font-weight: bold; color: var(--azul); margin: 0 0 8px 0; }
        .novidade-texto { font-size: 14px; color: #555; margin: 0; line-height: 1.5; }

        /* Tabela de Placares */
        .tabela-placares { width: 100%; border-collapse: collapse; margin-top: 10px; text-align: center; border-radius: 8px; overflow: hidden; border: 1px solid var(--borda); }
        .tabela-placares th { background: var(--azul); color: white; padding: 12px; font-size: 14px; }
        .tabela-placares td { padding: 12px; font-size: 14px; border-bottom: 1px solid var(--borda); background: white; color: #444; }
        .tabela-placares tr:last-child td { border-bottom: none; }
        .placar-destaque { font-weight: bold; color: #28a745; }

        @media (max-width: 900px) {
            .container-principal { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- CABEÇALHO COM A LOGO ATUALIZADA -->
    <div class="header">
        <a href="painel.php" class="logo-container">
            <img src="img/logo_sac.jpg" alt="Logo Associação Camilópolis" class="logo-img">
            <span class="logo-texto">Associação Camilópolis</span>
        </a>
        <div class="usuario-info">
            Olá, <span><?php echo htmlspecialchars($nome_exibicao); ?></span><br>
            <a href="login.php" class="btn-sair">Sair do Sistema</a>
        </div>
    </div>

    <!-- CONTEÚDO DA PÁGINA -->
    <div class="container-principal">
        
        <!-- MENU LATERAL DE NAVEGAÇÃO -->
        <div class="menu-lateral">
            <div class="menu-titulo">Navegação</div>
            
            <a href="agendar.php" class="nav-card">
                <div class="nav-icone">⚽</div>
                <div class="nav-detalhes">
                    <h4>Agendar Quadra</h4>
                    <p>Verifique os horários livres</p>
                </div>
            </a>

            <a href="agendar_churrasqueira.php" class="nav-card">
                <div class="nav-icone">🍖</div>
                <div class="nav-detalhes">
                    <h4>Reserva Churrasqueira</h4>
                    <p>Agende sua confraternização</p>
                </div>
            </a>

            <a href="minhas_reservas.php" class="nav-card">
                <div class="nav-icone">📋</div>
                <div class="nav-detalhes">
                    <h4>Minhas Reservas</h4>
                    <p>Consulte seus agendamentos</p>
                </div>
            </a>

            <a href="meu_perfil.php" class="nav-card">
                <div class="nav-icone">👤</div>
                <div class="nav-detalhes">
                    <h4>Meu Perfil</h4>
                    <p>Atualize seus dados</p>
                </div>
            </a>
        </div>

        <!-- PAINEL CENTRAL -->
        <div class="conteudo-central">
            
            <!-- MURAL DE NOVIDADES -->
            <div class="secao-card">
                <div class="secao-titulo">📰 Mural de Novidades</div>
                
                <div class="novidade-item destaque">
                    <span class="novidade-tag">Hoje</span>
                    <h3 class="novidade-titulo">Inscrições para o Campeonato Interno 2026</h3>
                    <p class="novidade-texto">Estão abertas as inscrições para o campeonato de futsal deste ano! Monte seu time e venha participar. As vagas são limitadas e haverá premiação em dinheiro para os primeiros colocados.</p>
                </div>

                <div class="novidade-item">
                    <span class="novidade-tag">Aviso Importante</span>
                    <h3 class="novidade-titulo">Reforma da Churrasqueira Concluída</h3>
                    <p class="novidade-texto">A nova área de lazer está pronta! Adicionamos novos espetos, uma grelha maior e reformamos as mesas. Aproveite para agendar seu churrasco com a galera do futebol.</p>
                </div>
            </div>

            <!-- PLACARES DA FEDERAÇÃO -->
            <div class="secao-card">
                <div class="secao-titulo">🏆 Placares da Federação</div>
                
                <table class="tabela-placares">
                    <thead>
                        <tr>
                            <th>Time Casa</th>
                            <th>Placar</th>
                            <th>Visitante</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Camilópolis FC</strong></td>
                            <td><span class="placar-destaque">4 x 2</span></td>
                            <td>Juventude AC</td>
                        </tr>
                        <tr>
                            <td>Real Santo André</td>
                            <td><span style="color:#d35400; font-weight:bold;">1 x 1</span></td>
                            <td><strong>Camilópolis FC</strong></td>
                        </tr>
                        <tr>
                            <td><strong>Camilópolis FC</strong></td>
                            <td><span class="placar-destaque">3 x 0</span></td>
                            <td>Vila Nova Futsal</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

    </div>

</body>
</html>