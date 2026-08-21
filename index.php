<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Associação Amigos de Camilópolis | Tradição e Lazer</title>
    <style>
        :root { 
            --azul-principal: #072a50; 
            --azul-medio: #0A3D73; 
            --amarelo: #FFC107; 
            --amarelo-hover: #e0a800;
            --fundo: #f4f7f6; 
            --texto-cor: #2c3e50; 
            --branco: #ffffff; 
            --whatsapp: #25d366;
            --instagram: #E1306C;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        html { scroll-behavior: smooth; }
        body { color: var(--texto-cor); background-color: var(--fundo); line-height: 1.7; }
        a { text-decoration: none; }

        /* Barra de Navegação Moderna */
        .navbar { 
            background: rgba(7, 42, 80, 0.95); 
            backdrop-filter: blur(10px);
            padding: 15px 6%; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            position: fixed; 
            width: 100%; 
            top: 0; 
            z-index: 1000; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.15); 
            box-sizing: border-box; 
        }
        .nav-brand { display: flex; align-items: center; gap: 14px; }
        .nav-logo { height: 48px; width: 48px; border-radius: 50%; border: 2px solid var(--amarelo); object-fit: cover; background: var(--branco); box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
        .nav-title-group { display: flex; flex-direction: column; }
        .nav-title { color: var(--amarelo); font-size: 19px; font-weight: 800; letter-spacing: 0.5px; line-height: 1.1; }
        .nav-subtitle { color: #cbd5e1; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
        
        .nav-links { display: flex; gap: 24px; align-items: center; }
        .nav-links a { color: var(--branco); font-weight: 500; font-size: 15px; transition: color 0.3s; }
        .nav-links a:hover { color: var(--amarelo); }
        
        .social-icon-nav { background: rgba(255,255,255,0.1); width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; transition: all 0.3s; }
        .social-icon-nav:hover { background: var(--instagram); transform: translateY(-2px); }

        .btn-login { background-color: var(--amarelo); color: var(--azul-principal) !important; padding: 10px 22px; border-radius: 25px; font-weight: 700; transition: all 0.3s; box-shadow: 0 4px 10px rgba(255, 193, 7, 0.3); }
        .btn-login:hover { transform: translateY(-2px); background-color: var(--amarelo-hover); box-shadow: 0 6px 15px rgba(255, 193, 7, 0.4); }

        /* Banner Hero Profissional */
        .hero { 
            background: linear-gradient(135deg, rgba(7, 42, 80, 0.88), rgba(10, 61, 115, 0.92)), 
                        url('https://images.unsplash.com/photo-1574629810360-7efbb1925846?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat; 
            min-height: 92vh; 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
            align-items: center; 
            text-align: center; 
            color: var(--branco); 
            padding: 120px 20px 80px 20px; 
            position: relative;
        }
        
        .hero-badge {
            background: rgba(255, 193, 7, 0.15);
            color: var(--amarelo);
            border: 1px solid rgba(255, 193, 7, 0.4);
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 20px;
            backdrop-filter: blur(5px);
        }

        .hero h1 { font-size: 52px; margin-bottom: 20px; color: var(--branco); font-weight: 800; max-width: 900px; line-height: 1.2; text-shadow: 0 4px 12px rgba(0,0,0,0.4); }
        .hero h1 span { color: var(--amarelo); }
        .hero p { font-size: 19px; max-width: 750px; margin-bottom: 40px; font-weight: 400; color: #e2e8f0; text-shadow: 0 2px 6px rgba(0,0,0,0.4); }
        .hero-btns { display: flex; gap: 16px; flex-wrap: wrap; justify-content: center; }
        
        .btn-primary { background: var(--amarelo); color: var(--azul-principal); padding: 15px 32px; border-radius: 10px; font-size: 16px; font-weight: 700; transition: all 0.3s; box-shadow: 0 6px 20px rgba(0,0,0,0.25); }
        .btn-primary:hover { background: var(--amarelo-hover); transform: translateY(-3px); box-shadow: 0 10px 25px rgba(255,193,7,0.35); }
        
        .btn-secondary { background: rgba(255, 255, 255, 0.08); border: 2px solid rgba(255, 255, 255, 0.3); color: var(--branco); padding: 15px 32px; border-radius: 10px; font-size: 16px; font-weight: 600; backdrop-filter: blur(5px); transition: all 0.3s; }
        .btn-secondary:hover { background: rgba(255, 255, 255, 0.2); border-color: var(--amarelo); color: var(--amarelo); transform: translateY(-3px); }

        /* Barra de Destaques Rápidos abaixo do Hero */
        .features-bar {
            background: white;
            max-width: 1100px;
            margin: -45px auto 0 auto;
            position: relative;
            z-index: 10;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            padding: 30px;
            gap: 25px;
            border-bottom: 4px solid var(--amarelo);
        }
        .feature-item { display: flex; align-items: center; gap: 18px; text-align: left; }
        .feature-icon { width: 55px; height: 55px; background: #eef4fc; color: var(--azul-medio); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; }
        .feature-text h4 { font-size: 16px; color: var(--azul-principal); margin-bottom: 4px; font-weight: 700; }
        .feature-text p { font-size: 13px; color: #64748b; margin: 0; }

        /* Seções Gerais */
        .section { padding: 90px 8%; text-align: center; }
        .section-title { font-size: 34px; color: var(--azul-principal); margin-bottom: 15px; font-weight: 800; position: relative; display: inline-block; }
        .section-title::after { content: ''; display: block; width: 60px; height: 4px; background: var(--amarelo); margin: 8px auto 0 auto; border-radius: 2px; }
        .section-subtitle { color: #64748b; font-size: 16px; margin-bottom: 40px; }
        
        .sobre-content { max-width: 850px; margin: 0 auto; font-size: 17px; color: #475569; line-height: 1.8; background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); }

        /* Estrutura (Cards) */
        .estrutura-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; margin-top: 30px; }
        .card { background: var(--branco); border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.06); transition: all 0.3s ease; text-align: left; border: 1px solid #e2e8f0; display: flex; flex-direction: column; }
        .card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.12); border-color: var(--amarelo); }
        .card-img-wrapper { position: relative; overflow: hidden; height: 220px; }
        .card-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
        .card:hover .card-img { transform: scale(1.05); }
        .card-body { padding: 25px; flex: 1; display: flex; flex-direction: column; justify-content: space-between; }
        .card-body h3 { color: var(--azul-principal); margin-bottom: 12px; font-size: 21px; font-weight: 700; }
        .card-body p { color: #64748b; font-size: 14px; line-height: 1.6; }

        /* Localização e Mapa */
        .mapa-box { background: white; padding: 20px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); max-width: 1100px; margin: 0 auto; }
        .mapa-container { margin-top: 20px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .endereco-texto { font-size: 17px; margin-bottom: 10px; color: var(--azul-principal); font-weight: 700; }

        /* Botão Flutuante WhatsApp */
        .whatsapp-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: var(--whatsapp);
            color: white;
            border-radius: 50px;
            text-align: center;
            font-size: 32px;
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.4);
            z-index: 1000;
            width: 65px;
            height: 65px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        .whatsapp-float:hover { transform: scale(1.1); background-color: #1ebe5d; box-shadow: 0 12px 30px rgba(37, 211, 102, 0.6); }

        /* Footer */
        .footer { background: var(--azul-principal); color: var(--branco); text-align: center; padding: 50px 20px 30px 20px; margin-top: 60px; border-top: 5px solid var(--amarelo); }
        .footer-content { max-width: 700px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px; align-items: center; }
        .footer-socials { display: flex; gap: 15px; margin-top: 5px; }
        .footer-social-btn { background: rgba(255,255,255,0.1); color: white; padding: 10px 20px; border-radius: 30px; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 10px; transition: all 0.3s; }
        .footer-social-btn.insta:hover { background: var(--instagram); transform: translateY(-2px); }
        .footer-social-btn.whats:hover { background: var(--whatsapp); transform: translateY(-2px); }

        /* Responsividade */
        @media (max-width: 768px) {
            .nav-links { display: none; }
            .hero h1 { font-size: 34px; }
            .hero p { font-size: 16px; }
            .hero-btns { flex-direction: column; width: 100%; }
            .btn-primary, .btn-secondary { width: 100%; text-align: center; }
            .features-bar { margin: -20px 20px 0 20px; padding: 20px; grid-template-columns: 1fr; }
            .section { padding: 60px 5%; }
        }
    </style>
    <!-- Importação de ícones do FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- NAVEGAÇÃO -->
    <nav class="navbar">
        <div class="nav-brand">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRUevjg4nk0uzl6lHsFBPkPu30hw4n5C5X_7Q&s" alt="Logo Camilópolis" class="nav-logo">
            <div class="nav-title-group">
                <span class="nav-title">A.A. Camilópolis</span>
                <span class="nav-subtitle">Associação Oficial</span>
            </div>
        </div>
        <div class="nav-links">
            <a href="#sobre">Sobre Nós</a>
            <a href="#estrutura">Estrutura</a>
            <a href="#localizacao">Localização</a>
            <a href="https://www.instagram.com/associacao.amigosdecamilopolis/" target="_blank" class="social-icon-nav" title="Siga no Instagram">
                <i class="fab fa-instagram"></i>
            </a>
            <a href="login.php" class="btn-login">Área do Sócio</a>
        </div>
    </nav>

    <!-- BOTÃO FLUTUANTE WHATSAPP -->
    <a href="https://wa.me/551144613996?text=Olá,%20gostaria%20de%20saber%20mais%20sobre%20a%20Associação%20Camilópolis." target="_blank" class="whatsapp-float" title="Fale conosco pelo WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- BANNER HERO PROFISSIONAL -->
    <header class="hero">
        <div class="hero-badge">Tradição, Esporte e Lazer em Santo André</div>
        <h1>Conectando Pessoas através do <span>Esporte e da Convivência</span></h1>
        <p>A Associação Amigos de Camilópolis oferece o espaço perfeito para sua família, campeonatos de futsal e confraternizações inesquecíveis.</p>
        <div class="hero-btns">
            <a href="cadastro.php" class="btn-primary">Quero ser Sócio</a>
            <a href="#estrutura" class="btn-secondary">Conheça o Clube</a>
        </div>
    </header>

    <!-- BARRA DE DESTAQUES RÁPIDOS -->
    <div class="features-bar">
        <div class="feature-item">
            <div class="feature-icon"><i class="fas fa-futbol"></i></div>
            <div class="feature-text">
                <h4>Quadra Poliesportiva</h4>
                <p>Estrutura coberta e equipada</p>
            </div>
        </div>
        <div class="feature-item">
            <div class="feature-icon"><i class="fas fa-fire"></i></div>
            <div class="feature-text">
                <h4>Área de Churrasco</h4>
                <p>Espaço completo para eventos</p>
            </div>
        </div>
        <div class="feature-item">
            <div class="feature-icon"><i class="fas fa-trophy"></i></div>
            <div class="feature-text">
                <h4>Campeonatos Internos</h4>
                <p>Tradição e premiações</p>
            </div>
        </div>
    </div>

    <!-- SOBRE NÓS -->
    <section id="sobre" class="section">
        <h2 class="section-title">Quem Somos</h2>
        <p class="section-subtitle">Conheça a história e a paixão que movem a nossa associação</p>
        <div class="sobre-content">
            Fundada com o propósito de unir a comunidade de Santo André, a <strong>Associação Amigos de Camilópolis</strong> é mais do que um clube, é uma verdadeira família. Há anos promovemos o esporte amador, a integração social e a qualidade de vida. Nosso espaço é dedicado aos apaixonados por futebol, pelos torneios disputados e pelo tradicional churrasco de domingo com os amigos.
        </div>
    </section>

    <!-- ESTRUTURA / CARDS -->
    <section id="estrutura" class="section" style="background-color: #e9ecef;">
        <h2 class="section-title">Nossa Estrutura</h2>
        <p class="section-subtitle">Ambientes modernos e preparados para receber você e seus convidados</p>
        <div class="estrutura-grid">
            <div class="card">
                <div class="card-img-wrapper">
                    <img src="https://img.freepik.com/fotos-premium/quadra-de-futsal-coberta-vista-do-topo_958619-322.jpg" alt="Quadra de Futsal" class="card-img">
                </div>
                <div class="card-body">
                    <h3>⚽ Quadra Poliesportiva</h3>
                    <p>Quadra coberta com piso de alta qualidade, ideal para futsal e vôlei. Totalmente iluminada para jogos noturnos, arquibancada e placar eletrônico.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-img-wrapper">
                    <img src="https://img.freepik.com/fotos-premium/area-de-churrasco-moderna_123.jpg" alt="Churrasqueira" class="card-img">
                </div>
                <div class="card-body">
                    <h3>🍖 Área de Churrasco</h3>
                    <p>Espaço amplo e reformado com grelhas novas, pias, freezers e mesas confortáveis. O ambiente perfeito para a confraternização pós-jogo.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-img-wrapper">
                    <img src="https://img.freepik.com/fotos-gratis/jogador-de-futebol-masculino-com-bola-no-campo-verde_1150-5023.jpg" alt="Eventos" class="card-img">
                </div>
                <div class="card-body">
                    <h3>🏆 Eventos e Torneios</h3>
                    <p>Sediamos campeonatos amadores e ligas da federação. Junte seu time, participe das nossas competições e faça história na nossa quadra.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- LOCALIZAÇÃO -->
    <section id="localizacao" class="section">
        <h2 class="section-title">Onde Estamos</h2>
        <p class="section-subtitle">Venha nos fazer uma visita ou entre em contato</p>
        
        <div class="mapa-box">
            <p class="endereco-texto">📍 R. Boa Vista, 860 - Vila Camilópolis, Santo André - SP, 09240-110</p>
            <div class="mapa-container">
                <iframe 
                    src="https://maps.google.com/maps?q=R.+Boa+Vista,+860+-+Vila+Camil%C3%B3polis,+Santo+Andr%C3%A9+-+SP&t=&z=16&ie=UTF8&iwloc=&output=embed" 
                    width="100%" 
                    height="420" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-content">
            <p style="font-size: 18px; font-weight: bold;">Associação Amigos de Camilópolis</p>
            <p style="font-size: 14px; color: #cbd5e1;">Desenvolvido para conectar nossa comunidade através do esporte.</p>
            
            <div class="footer-socials">
                <a href="https://www.instagram.com/associacao.amigosdecamilopolis/" target="_blank" class="footer-social-btn insta">
                    <i class="fab fa-instagram"></i> Instagram
                </a>
                <a href="https://wa.me/551144613996?text=Olá,%20gostaria%20de%20saber%20mais%20sobre%20a%20Associação%20Camilópolis." target="_blank" class="footer-social-btn whats">
                    <i class="fab fa-whatsapp"></i> Fale Conosco
                </a>
            </div>

            <p style="margin-top: 15px; font-size: 13px; color: #94a3b8;">© <?php echo date("Y"); ?> - Todos os direitos reservados.</p>
        </div>
    </footer>

</body>
</html>s