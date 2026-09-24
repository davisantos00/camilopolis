<?php
require_once('funcoes.php');
if (!isset($_SESSION['usuario_email'])) {
    header("Location: login.php");
    exit();
}

$email_usuario = $_SESSION['usuario_email'];

// Conexão para buscar o nome real do usuário logado
$conn = new mysqli('localhost', 'root', '', 'camilopolis_db');
$nome_exibicao = "Usuário";
$noticias = [];
$placares = [];

if (!$conn->connect_error) {
    $conn->set_charset('utf8mb4');
    garantir_estrutura($conn);

    $stmt = $conn->prepare("SELECT nome FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email_usuario);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $nome_exibicao = $row['nome'];
    }
    $stmt->close();

    // Notícias e placares são cadastrados pelo aplicativo administrativo
    $noticias = $conn->query("SELECT * FROM noticias ORDER BY destaque DESC, criado_em DESC, id DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
    $placares = $conn->query("SELECT * FROM placares ORDER BY data_jogo IS NULL, data_jogo DESC, id DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);

    // Aviso de respostas novas do suporte
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM suporte_mensagens WHERE usuario_email = ? AND resposta IS NOT NULL AND resposta_lida = 0");
    $stmt->bind_param("s", $email_usuario);
    $stmt->execute();
    $respostas_suporte = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    $conn->close();
}

// Formata a data da notícia como "Hoje", "Ontem" ou dd/mm/aaaa
function rotulo_data($data_hora) {
    $data = date('Y-m-d', strtotime($data_hora));
    if ($data === date('Y-m-d')) return 'Hoje';
    if ($data === date('Y-m-d', strtotime('-1 day'))) return 'Ontem';
    return date('d/m/Y', strtotime($data_hora));
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

        .placar-empate { font-weight: bold; color: #d35400; }
        .placar-derrota { font-weight: bold; color: #dc3545; }
        .placar-campeonato { display: block; font-size: 11px; color: #888; font-weight: normal; }
        .mural-vazio { color: #888; font-style: italic; text-align: center; padding: 15px; }
        .nav-contador { background: #dc3545; color: white; border-radius: 10px; padding: 1px 7px; font-size: 11px; margin-left: 4px; }

        @media (max-width: 900px) {
            .container-principal { grid-template-columns: 1fr; }
        }

        @media (max-width: 600px) {
            .header { padding: 12px 16px; }
            .logo-img { width: 38px; height: 38px; }
            .logo-texto { font-size: 15px; }
            .usuario-info { font-size: 12px; }
            .container-principal { margin: 16px auto; padding: 0 16px; gap: 16px; }
            .secao-card, .menu-lateral { padding: 16px; }
            .tabela-placares th, .tabela-placares td { padding: 8px 4px; font-size: 13px; }
        }
    </style>
    <link rel="stylesheet" href="comum.css">
    <script src="comum.js" defer></script>
</head>
<body>
<?php exibir_aviso(); ?>

    <!-- CABEÇALHO COM A LOGO ATUALIZADA -->
    <div class="header">
        <a href="painel.php" class="logo-container">
            <img src="img/logo_sac.jpg" alt="Logo Associação Camilópolis" class="logo-img">
            <span class="logo-texto">Associação Camilópolis</span>
        </a>
        <div class="usuario-info">
            Olá, <span><?php echo htmlspecialchars($nome_exibicao); ?></span><br>
            <a href="logout.php" class="btn-sair">Sair do Sistema</a>
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

            <a href="pagamento_associado.php" class="nav-card">
                <div class="nav-icone">💳</div>
                <div class="nav-detalhes">
                    <h4>Mensalidade de Sócio</h4>
                    <p>Pague sua mensalidade</p>
                </div>
            </a>

            <a href="suporte.php" class="nav-card">
                <div class="nav-icone">💬</div>
                <div class="nav-detalhes">
                    <h4>Suporte<?php if (!empty($respostas_suporte)): ?><span class="nav-contador"><?php echo $respostas_suporte; ?></span><?php endif; ?></h4>
                    <p>Fale com a Associação</p>
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
            
            <!-- MURAL DE NOVIDADES (cadastrado pelo aplicativo administrativo) -->
            <div class="secao-card">
                <div class="secao-titulo">📰 Mural de Novidades</div>

                <?php if (empty($noticias)): ?>
                    <p class="mural-vazio">Nenhuma novidade no momento.</p>
                <?php endif; ?>

                <?php foreach ($noticias as $noticia): ?>
                    <div class="novidade-item<?php echo $noticia['destaque'] ? ' destaque' : ''; ?>">
                        <span class="novidade-tag"><?php echo htmlspecialchars($noticia['tag']); ?> · <?php echo rotulo_data($noticia['criado_em']); ?></span>
                        <h3 class="novidade-titulo"><?php echo htmlspecialchars($noticia['titulo']); ?></h3>
                        <p class="novidade-texto"><?php echo nl2br(htmlspecialchars($noticia['texto'])); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- PLACARES DA FEDERAÇÃO (cadastrados pelo aplicativo administrativo) -->
            <div class="secao-card">
                <div class="secao-titulo">🏆 Placares da Federação</div>

                <?php if (empty($placares)): ?>
                    <p class="mural-vazio">Nenhum placar cadastrado.</p>
                <?php else: ?>
                <table class="tabela-placares">
                    <thead>
                        <tr>
                            <th>Time Casa</th>
                            <th>Placar</th>
                            <th>Visitante</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($placares as $jogo):
                            $casa_eh_nosso = stripos($jogo['time_casa'], 'Camil') !== false;
                            $fora_eh_nosso = stripos($jogo['time_visitante'], 'Camil') !== false;
                            $nossos_gols = $casa_eh_nosso ? $jogo['gols_casa'] : $jogo['gols_visitante'];
                            $gols_adversario = $casa_eh_nosso ? $jogo['gols_visitante'] : $jogo['gols_casa'];

                            // Verde = vitória do Camilópolis, laranja = empate, vermelho = derrota
                            $classe_placar = 'placar-empate';
                            if ($nossos_gols > $gols_adversario) $classe_placar = 'placar-destaque';
                            if ($nossos_gols < $gols_adversario) $classe_placar = 'placar-derrota';
                            if (!$casa_eh_nosso && !$fora_eh_nosso) $classe_placar = '';
                        ?>
                        <tr>
                            <td><?php echo $casa_eh_nosso ? '<strong>' . htmlspecialchars($jogo['time_casa']) . '</strong>' : htmlspecialchars($jogo['time_casa']); ?></td>
                            <td>
                                <span class="<?php echo $classe_placar; ?>"><?php echo (int) $jogo['gols_casa']; ?> x <?php echo (int) $jogo['gols_visitante']; ?></span>
                                <?php if (!empty($jogo['campeonato']) || !empty($jogo['data_jogo'])): ?>
                                    <span class="placar-campeonato"><?php echo htmlspecialchars(trim($jogo['campeonato'] . ' ' . ($jogo['data_jogo'] ? date('d/m', strtotime($jogo['data_jogo'])) : ''))); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $fora_eh_nosso ? '<strong>' . htmlspecialchars($jogo['time_visitante']) . '</strong>' : htmlspecialchars($jogo['time_visitante']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

        </div>

    </div>

    <div class="rodape-interno"><?php echo htmlspecialchars(texto_direitos()); ?></div>

</body>
</html>
