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

        /* Barra de Navegação */
        .navbar { background: rgba(7, 42, 80, 0.95); backdrop-filter: blur(10px); padding: 15px 6%; display: flex; justify-content: space-between; align-items: center; position: fixed; width: 100%; top: 0; z-index: 1000; box-shadow: 0 4px 20px rgba(0,0,0,0.15); box-sizing: border-box; }
        .nav-brand { display: flex; align-items: center; gap: 14px; }
        .nav-logo { height: 48px; width: 48px; border-radius: 50%; border: 2px solid var(--amarelo); object-fit: cover; background: var(--branco); }
        .nav-title-group { display: flex; flex-direction: column; }
        .nav-title { color: var(--amarelo); font-size: 19px; font-weight: 800; letter-spacing: 0.5px; line-height: 1.1; }
        .nav-subtitle { color: #cbd5e1; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
        
        .nav-links { display: flex; gap: 20px; align-items: center; }
        .nav-links a { color: var(--branco); font-weight: 500; font-size: 14px; transition: color 0.3s; }
        .nav-links a:hover { color: var(--amarelo); }
        
        .btn-login { background-color: var(--amarelo); color: var(--azul-principal) !important; padding: 10px 20px; border-radius: 25px; font-weight: 700; transition: all 0.3s; }
        .btn-login:hover { transform: translateY(-2px); background-color: var(--amarelo-hover); }

        /* Banner Hero */
        .hero { 
            background: linear-gradient(135deg, rgba(7, 42, 80, 0.88), rgba(10, 61, 115, 0.92)), 
                        url('https://images.unsplash.com/photo-1574629810360-7efbb1925846?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat; 
            min-height: 92vh; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; color: var(--branco); padding: 120px 20px 80px 20px; position: relative;
        }
        .hero-badge { background: rgba(255, 193, 7, 0.15); color: var(--amarelo); border: 1px solid rgba(255, 193, 7, 0.4); padding: 6px 16px; border-radius: 30px; font-size: 13px; font-weight: 600; text-transform: uppercase; margin-bottom: 20px; }
        .hero h1 { font-size: 52px; margin-bottom: 20px; color: var(--branco); font-weight: 800; max-width: 900px; line-height: 1.2; }
        .hero h1 span { color: var(--amarelo); }
        .hero p { font-size: 19px; max-width: 750px; margin-bottom: 40px; font-weight: 400; color: #e2e8f0; }
        .hero-btns { display: flex; gap: 16px; flex-wrap: wrap; justify-content: center; }
        
        .btn-primary { background: var(--amarelo); color: var(--azul-principal); padding: 15px 32px; border-radius: 10px; font-size: 16px; font-weight: 700; transition: all 0.3s; }
        .btn-primary:hover { background: var(--amarelo-hover); transform: translateY(-3px); }
        .btn-secondary { background: rgba(255, 255, 255, 0.08); border: 2px solid rgba(255, 255, 255, 0.3); color: var(--branco); padding: 15px 32px; border-radius: 10px; font-size: 16px; font-weight: 600; transition: all 0.3s; }
        .btn-secondary:hover { background: rgba(255, 255, 255, 0.2); border-color: var(--amarelo); color: var(--amarelo); transform: translateY(-3px); }

        /* Seções Gerais */
        .section { padding: 80px 8%; text-align: center; }
        .section-title { font-size: 34px; color: var(--azul-principal); margin-bottom: 15px; font-weight: 800; position: relative; display: inline-block; }
        .section-title::after { content: ''; display: block; width: 60px; height: 4px; background: var(--amarelo); margin: 8px auto 0 auto; border-radius: 2px; }
        .section-subtitle { color: #64748b; font-size: 16px; margin-bottom: 40px; }
        
        /* História */
        .sobre-content { max-width: 900px; margin: 0 auto; font-size: 16px; color: #475569; line-height: 1.8; background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); text-align: left; }
        .sobre-content p { margin-bottom: 15px; }
        .history-tags { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 20px; }
        .h-tag { background: #eef4fc; color: var(--azul-medio); padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; }

        /* Grid Padrão (Cards) */
        .grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-top: 30px; }
        .grid-4 { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px; }
        
        .card { background: var(--branco); border-radius: 12px; overflow: hidden; box-shadow: 0 8px 25px rgba(0,0,0,0.05); transition: all 0.3s; text-align: left; border: 1px solid #e2e8f0; display: flex; flex-direction: column; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.1); border-color: var(--amarelo); }
        .card-header { background: var(--azul-principal); color: white; padding: 20px; font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .card-body { padding: 20px; flex: 1; }
        .card-body ul { list-style: none; }
        .card-body ul li { margin-bottom: 10px; font-size: 14.5px; color: #475569; display: flex; gap: 8px; align-items: flex-start; }
        .card-body ul li i { color: var(--amarelo); margin-top: 4px; }
        .price-tag { font-size: 24px; color: var(--azul-principal); font-weight: 800; margin-bottom: 15px; display: block; }
        
        /* Banner Sócio */
        .socio-banner { background: var(--azul-medio); color: white; padding: 50px 8%; border-radius: 16px; margin: 40px 8%; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 30px; box-shadow: 0 15px 40px rgba(10, 61, 115, 0.2); }
        .socio-info h2 { font-size: 32px; color: var(--amarelo); margin-bottom: 10px; }
        .socio-info p { font-size: 16px; margin-bottom: 20px; opacity: 0.9; }
        .socio-price { text-align: center; background: white; color: var(--azul-principal); padding: 30px; border-radius: 12px; min-width: 250px; }
        .socio-price h3 { font-size: 40px; margin: 10px 0; }
        .socio-price span { font-size: 14px; color: #64748b; }

        /* Botões Flutuantes */
        .whatsapp-float { position: fixed; bottom: 30px; right: 30px; background-color: var(--whatsapp); color: white; border-radius: 50px; font-size: 32px; box-shadow: 0 8px 25px rgba(37, 211, 102, 0.4); z-index: 1000; width: 65px; height: 65px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; }
        .whatsapp-float:hover { transform: scale(1.1); background-color: #1ebe5d; }

        .instagram-float { position: fixed; bottom: 105px; right: 30px; background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); color: white; border-radius: 50px; font-size: 32px; box-shadow: 0 8px 25px rgba(225, 48, 108, 0.4); z-index: 1000; width: 65px; height: 65px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; }
        .instagram-float:hover { transform: scale(1.1); filter: brightness(1.1); }

        /* Footer e Mapa */
        .mapa-box { background: white; padding: 20px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); max-width: 1100px; margin: 0 auto; }
        .mapa-container { margin-top: 20px; border-radius: 12px; overflow: hidden; }
        .footer { background: var(--azul-principal); color: var(--branco); text-align: center; padding: 50px 20px 30px 20px; margin-top: 60px; border-top: 5px solid var(--amarelo); }
        .footer-socials a { display: inline-block; color: var(--branco); font-size: 24px; margin: 10px 12px; transition: 0.3s; }
        .footer-socials a:hover { color: var(--amarelo); transform: translateY(-3px); }

        @media (max-width: 768px) {
            .nav-links { display: none; }
            .hero h1 { font-size: 34px; }
            .socio-banner { flex-direction: column; text-align: center; }
            .section { padding: 60px 5%; }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- NAVEGAÇÃO -->
    <nav class="navbar">
        <div class="nav-brand">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRUevjg4nk0uzl6lHsFBPkPu30hw4n5C5X_7Q&s" alt="Logo" class="nav-logo">
            <div class="nav-title-group">
                <span class="nav-title">A.A. Camilópolis</span>
                <span class="nav-subtitle">Desde 1957</span>
            </div>
        </div>
        <div class="nav-links">
            <a href="#sobre">História</a>
            <a href="#atividades">Atividades</a>
            <a href="#servicos">Saúde & Bem-Estar</a>
            <a href="#locacoes">Locações</a>
            <a href="#socio">Seja Sócio</a>
            <a href="login.php" class="btn-login">Faça Login ou Cadastre-se</a>
        </div>
    </nav>

    <!-- BOTÕES FLUTUANTES -->
    <a href="https://www.instagram.com/associacao.amigosdecamilopolis/" target="_blank" class="instagram-float" title="Siga nosso Instagram"><i class="fab fa-instagram"></i></a>
    <a href="https://wa.me/551144613996" target="_blank" class="whatsapp-float" title="Fale pelo WhatsApp"><i class="fab fa-whatsapp"></i></a>

    <!-- HERO -->
    <header class="hero">
        <div class="hero-badge">Tradição, Esporte e Lazer em Santo André</div>
        <h1>Conectando Pessoas através do <span>Esporte e da Convivência</span></h1>
        <p>A Associação Amigos de Camilópolis oferece esporte, serviços de saúde, atividades para a terceira idade e o espaço perfeito para seus eventos.</p>
        <div class="hero-btns">
            <a href="#socio" class="btn-primary">Quero ser Sócio (R$ 25/mês)</a>
            <a href="#atividades" class="btn-secondary">Nossas Atividades</a>
        </div>
    </header>

    <!-- HISTÓRIA E SOCIAL -->
    <section id="sobre" class="section">
        <h2 class="section-title">Nossa História</h2>
        <p class="section-subtitle">Muito mais que um clube, uma família a serviço da comunidade.</p>
        <div class="sobre-content">
            <p><strong>Fundada em 14 de abril de 1957</strong>, as primeiras reuniões da associação aconteceram na casa de um associado. Nascemos com um propósito nobre e urgente: lutar pelo desenvolvimento da Vila Camilópolis, reivindicando desde as primeiras ligações de água e luz para a região até melhorias estruturais para a população.</p>
            <p>Ao longo das décadas, fomos construindo nossa tradição. Antigamente, abrigávamos grupos de escoteiros e formamos um dos melhores times de Bocha da região (esporte que está conosco desde o início!). Hoje, além do lazer, mantemos nossa vocação social: cedemos espaço para o posto de saúde local e campanhas de vacinação, promovemos atendimento jurídico esporádico e mantemos um sistema de empréstimo de itens para idosos.</p>
            <div class="history-tags">
                <span class="h-tag"><i class="fas fa-tint"></i> Luta por Água e Luz (1957)</span>
                <span class="h-tag"><i class="fas fa-campground"></i> Antigo Lar dos Escoteiros</span>
                <span class="h-tag"><i class="fas fa-heartbeat"></i> Apoio a Campanhas de Saúde</span>
                <span class="h-tag"><i class="fas fa-hands-helping"></i> Empréstimo de Itens a Idosos</span>
            </div>
        </div>
    </section>

    <!-- ATIVIDADES E ESPORTES -->
    <section id="atividades" class="section" style="background-color: #e9ecef;">
        <h2 class="section-title">Esporte e Cultura</h2>
        <p class="section-subtitle">Opções para todas as idades. Consulte vagas e horários!</p>
        
        <div class="grid-3">
            <div class="card">
                <div class="card-header"><i class="fas fa-futbol"></i> Quadra e Escolinha</div>
                <div class="card-body">
                    <ul>
                        <li><i class="fas fa-check"></i> <strong>Locação de Quadra:</strong> Avulsa ou Mensal. Poucos horários vagos (Sextas das 17h às 19h / Domingos a partir das 13h).</li>
                        <li><i class="fas fa-check"></i> <strong>Escolinha de Futsal:</strong> Segunda a Quinta (17h às 19h) e Sábado (12h às 14h).</li>
                        <li><i class="fas fa-check"></i> Eventos esportivos em diversas datas.</li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="fas fa-user-friends"></i> Terceira Idade e Bocha</div>
                <div class="card-body">
                    <ul>
                        <li><i class="fas fa-check"></i> <strong>Encontro da 3ª Idade:</strong> Terças, das 14h às 16h (Grupo Maria Quitéria de Camilópolis, com comida, bingo e diversão).</li>
                        <li><i class="fas fa-check"></i> <strong>Bocha:</strong> Todos os dias! Uma das nossas maiores tradições (pode jogar não sendo sócio).</li>
                        <li><i class="fas fa-check"></i> <strong>Dominó e Baralho:</strong> Todos os dias, das 09h às 21h.</li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="fas fa-book-open"></i> Caminhar para Crescer</div>
                <div class="card-body">
                    <p style="font-size: 14.5px; color: #475569; margin-bottom: 10px;">Aulas educacionais e culturais focadas no desenvolvimento:</p>
                    <ul>
                        <li><i class="fas fa-music"></i> Ballet e Jazz</li>
                        <li><i class="fas fa-language"></i> Curso de Inglês</li>
                        <li><i class="fas fa-laptop"></i> Música Virtual</li>
                        <li><i class="fas fa-om"></i> <strong>Yoga:</strong> Terças, das 14h às 15h.</li>
                        <li><i class="fas fa-shopping-bag"></i> <strong>Bazar Beneficente:</strong> Terças, das 14h às 17h.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- SAÚDE E BEM-ESTAR -->
    <section id="servicos" class="section">
        <h2 class="section-title">Saúde e Bem-Estar</h2>
        <p class="section-subtitle">Profissionais à disposição na Associação mediante agendamento</p>
        <div class="grid-4">
            <div class="card" style="text-align: center; padding: 20px;">
                <i class="fas fa-cut" style="font-size: 30px; color: var(--azul-medio); margin-bottom: 10px;"></i>
                <h4>Barbearia Masculina</h4>
            </div>
            <div class="card" style="text-align: center; padding: 20px;">
                <i class="fas fa-paint-roller" style="font-size: 30px; color: var(--azul-medio); margin-bottom: 10px;"></i>
                <h4>Manicure e Pedicure</h4>
            </div>
            <div class="card" style="text-align: center; padding: 20px;">
                <i class="fas fa-eye" style="font-size: 30px; color: var(--azul-medio); margin-bottom: 10px;"></i>
                <h4>Designer de Sobrancelha</h4>
            </div>
            <div class="card" style="text-align: center; padding: 20px;">
                <i class="fas fa-shoe-prints" style="font-size: 30px; color: var(--azul-medio); margin-bottom: 10px;"></i>
                <h4>Podóloga</h4>
            </div>
            <div class="card" style="text-align: center; padding: 20px;">
                <i class="fas fa-brain" style="font-size: 30px; color: var(--azul-medio); margin-bottom: 10px;"></i>
                <h4>Atendimento Psicológico</h4>
            </div>
        </div>
    </section>

    <!-- LOCAÇÕES DE ESPAÇO -->
    <section id="locacoes" class="section" style="background-color: #e9ecef;">
        <h2 class="section-title">Locação para Eventos</h2>
        <p class="section-subtitle">Temos 2 salões de festas e 1 churrasqueira. O formato do aniversário fica a seu critério!</p>
        <div class="grid-3" style="max-width: 900px; margin: 0 auto;">
            <div class="card">
                <div class="card-header" style="background: var(--amarelo); color: var(--azul-principal);"><i class="fas fa-star"></i> Salão Principal</div>
                <div class="card-body" style="text-align: center;">
                    <span class="price-tag">R$ 600,00</span>
                    <p style="color: #64748b; margin-bottom: 15px;">Período de locação: <strong>5 horas</strong></p>
                    <p style="font-size: 13px; font-weight: bold; color: var(--azul-principal);">Sócio possui desconto especial!</p>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><i class="fas fa-glass-cheers"></i> Salão Secundário</div>
                <div class="card-body" style="text-align: center;">
                    <span class="price-tag">R$ 500,00</span>
                    <p style="color: #64748b; margin-bottom: 15px;">Período de locação: <strong>5 horas</strong></p>
                    <p style="font-size: 13px; font-weight: bold; color: var(--azul-principal);">Sócio possui desconto especial!</p>
                </div>
            </div>
        </div>
    </section>

    <!-- SEJA SÓCIO BANNER (ÚNICO LOCAL DE CADASTRO) -->
    <section id="socio">
        <div class="socio-banner">
            <div class="socio-info">
                <h2>Faça parte da nossa Associação!</h2>
                <p>Valorize a comunidade, apoie o esporte e garanta vantagens exclusivas para você e sua família.</p>
                <ul style="list-style: none; margin-left: 0; padding-left: 0;">
                    <li style="margin-bottom: 8px;"><i class="fas fa-check-circle" style="color: var(--amarelo);"></i> Descontos na locação de Salões de Festas e Quadra</li>
                    <li style="margin-bottom: 8px;"><i class="fas fa-check-circle" style="color: var(--amarelo);"></i> Acesso ao pátio de estacionamento privativo</li>
                    <li style="margin-bottom: 8px;"><i class="fas fa-check-circle" style="color: var(--amarelo);"></i> Livre utilização das áreas de lazer do clube</li>
                </ul>
            </div>
            <div class="socio-price">
                <span>Mensalidade Sócio</span>
                <h3>R$ 25<span style="font-size: 20px;">/mês</span></h3>
                <a href="cadastro.php" class="btn-primary" style="display: block; margin-top: 15px; width: 100%;">Cadastre-se na Associação</a>
            </div>
        </div>
    </section>

    <!-- LOCALIZAÇÃO -->
    <section id="localizacao" class="section">
        <h2 class="section-title">Onde Estamos</h2>
        <div class="mapa-box">
            <p style="font-size: 17px; margin-bottom: 10px; color: var(--azul-principal); font-weight: 700;">📍 R. Boa Vista, 860 - Vila Camilópolis, Santo André - SP</p>
            <div class="mapa-container">
                <iframe src="https://maps.google.com/maps?q=R.+Boa+Vista,+860+-+Vila+Camil%C3%B3polis,+Santo+Andr%C3%A9+-+SP&t=&z=16&ie=UTF8&iwloc=&output=embed" width="100%" height="420" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <p style="font-size: 18px; font-weight: bold; margin-bottom: 10px;">Associação Amigos de Camilópolis</p>
        <p style="font-size: 14px; color: #cbd5e1; margin-bottom: 15px;">Desenvolvido para conectar nossa comunidade através do esporte e do bem-estar.</p>
        <div class="footer-socials">
            <a href="https://www.instagram.com/associacao.amigosdecamilopolis/" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="https://wa.me/551144613996" target="_blank" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
        </div>
        <p style="margin-top: 15px; font-size: 13px; color: #94a3b8;">© <?php echo date("Y"); ?> - Todos os direitos reservados.</p>
    </footer>

</body>
</html>
