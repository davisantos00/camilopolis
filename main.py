import os
import sys
import ctypes

from PySide6 import QtCore, QtGui, QtWidgets
from PySide6.QtUiTools import QUiLoader

try:
    import bcrypt
    import mysql.connector
except ImportError:
    bcrypt = None

# Pasta do projeto: permite abrir o aplicativo pelo atalho da Área de Trabalho
PASTA_APP = os.path.dirname(os.path.abspath(__file__))
os.chdir(PASTA_APP)

NOME_ASSOCIACAO = "Associação Amigos de Camilópolis"
MASCARA_SENHA = "••••••••"


def conectar_banco():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="camilopolis_db"
    )


def consultar(sql, parametros=()):
    conexao = conectar_banco()
    cursor = conexao.cursor()
    cursor.execute(sql, parametros)
    resultados = cursor.fetchall()
    colunas = [desc[0] for desc in cursor.description]
    cursor.close()
    conexao.close()
    return colunas, resultados


def executar(sql, parametros=()):
    conexao = conectar_banco()
    cursor = conexao.cursor()
    cursor.execute(sql, parametros)
    conexao.commit()
    cursor.close()
    conexao.close()


def garantir_estrutura():
    """Cria as tabelas novas (pagamentos, suporte, notícias, placares) usando o mesmo arquivo do site."""
    with open(os.path.join(PASTA_APP, "atualizacao_banco.sql"), encoding="utf-8") as arquivo:
        linhas = [linha for linha in arquivo if not linha.strip().startswith("--")]
    comandos = [comando.strip() for comando in "".join(linhas).split(";") if comando.strip()]

    conexao = conectar_banco()
    cursor = conexao.cursor()
    for comando in comandos:
        cursor.execute(comando)
    conexao.commit()
    cursor.close()
    conexao.close()


# ==========================================
# SENHAS (mesma criptografia do site: password_hash do PHP)
# ==========================================
def senha_confere(senha_digitada, senha_salva):
    if senha_salva.startswith("$2"):
        # O PHP usa o prefixo $2y$ e a biblioteca bcrypt do Python usa $2b$ (o algoritmo é o mesmo)
        return bcrypt.checkpw(senha_digitada.encode(), senha_salva.replace("$2y$", "$2b$", 1).encode())
    return senha_digitada == senha_salva  # Senhas antigas, ainda em texto puro


def criptografar_senha(senha):
    return bcrypt.hashpw(senha.encode(), bcrypt.gensalt(10)).decode().replace("$2b$", "$2y$", 1)


# ==========================================
# FUNÇÕES DE INTERFACE
# ==========================================
def configurar_tabela(tabela):
    tabela.setSelectionBehavior(QtWidgets.QAbstractItemView.SelectionBehavior.SelectRows)
    tabela.setSelectionMode(QtWidgets.QAbstractItemView.SelectionMode.SingleSelection)
    tabela.setEditTriggers(QtWidgets.QAbstractItemView.EditTrigger.NoEditTriggers)
    tabela.setAlternatingRowColors(True)
    tabela.verticalHeader().setVisible(False)
    tabela.horizontalHeader().setStretchLastSection(True)


def preencher_tabela(tabela, cabecalhos, linhas):
    tabela.setRowCount(len(linhas))
    tabela.setColumnCount(len(cabecalhos))
    tabela.setHorizontalHeaderLabels(cabecalhos)
    for row_idx, row_data in enumerate(linhas):
        for col_idx, data in enumerate(row_data):
            tabela.setItem(row_idx, col_idx, QtWidgets.QTableWidgetItem("" if data is None else str(data)))
    tabela.resizeColumnsToContents()


def id_selecionado(tabela):
    linha = tabela.currentRow()
    if linha < 0 or tabela.item(linha, 0) is None:
        return None
    return int(tabela.item(linha, 0).text())


def botao(texto, classe=None):
    novo = QtWidgets.QPushButton(texto)
    novo.setCursor(QtCore.Qt.CursorShape.PointingHandCursor)
    if classe:
        novo.setProperty("classe", classe)
    return novo


def icone_texto(texto):
    """Desenha um emoji como ícone (usado no botão de mostrar senha)."""
    imagem = QtGui.QPixmap(32, 32)
    imagem.fill(QtCore.Qt.GlobalColor.transparent)
    pintor = QtGui.QPainter(imagem)
    fonte = pintor.font()
    fonte.setPointSize(16)
    pintor.setFont(fonte)
    pintor.drawText(imagem.rect(), QtCore.Qt.AlignmentFlag.AlignCenter, texto)
    pintor.end()
    return QtGui.QIcon(imagem)


def formatar_dinheiro(valor):
    valor = float(valor or 0)
    return "R$ " + f"{valor:,.2f}".replace(",", "X").replace(".", ",").replace("X", ".")


def formatar_data(data, com_hora=False):
    if data is None:
        return ""
    return data.strftime("%d/%m/%Y %H:%M" if com_hora else "%d/%m/%Y")


class PainelAdmin:
    def __init__(self):
        loader = QUiLoader()
        self.ui = loader.load(os.path.join(PASTA_APP, "painel_admin.ui"), None)
        self.ui.setWindowTitle("Painel Administrativo - Camilópolis")

        self.organizar_layout()
        self.criar_aba_suporte()
        self.criar_aba_noticias()
        self.criar_aba_placares()
        self.criar_aba_pagamentos()

        # Abre o painel administrativo em tela cheia automaticamente
        self.ui.showMaximized()

        # Conecta o botão de Sair se ele existir na tela
        if hasattr(self.ui, "btn_sair"):
            self.ui.btn_sair.clicked.connect(self.fechar_painel)
        self.ui.btn_pesquisar_cliente.clicked.connect(self.pesquisar_cliente)
        self.ui.txt_busca_cliente.returnPressed.connect(self.pesquisar_cliente)
        self.ui.btn_atualizar_reservas.clicked.connect(self.carregar_reservas_detalhadas)

        # Carrega os dados nas tabelas
        self.carregar_clientes()
        self.carregar_reservas_detalhadas()
        self.carregar_suporte()
        self.carregar_noticias()
        self.carregar_placares()
        self.carregar_pagamentos()
        self.carregar_totais()

        # Verifica novas mensagens de suporte a cada 30 segundos
        self.total_abertas = self.contar_mensagens_abertas()
        self.atualizar_titulo_suporte()
        self.timer_suporte = QtCore.QTimer(self.ui)
        self.timer_suporte.timeout.connect(self.verificar_novas_mensagens)
        self.timer_suporte.start(30000)

    # ------------------------------------------
    # LAYOUT: faz a tela ocupar a janela inteira
    # ------------------------------------------
    def organizar_layout(self):
        ui = self.ui
        central = ui.centralWidget()

        ui.lbl_logo_topo.setFixedSize(80, 80)
        ui.lbl_logo_topo.setScaledContents(True)
        ui.label.setProperty("classe", "titulo")

        topo = QtWidgets.QHBoxLayout()
        topo.setSpacing(16)
        topo.addWidget(ui.lbl_logo_topo)
        topo.addWidget(ui.label, 1)
        topo.addWidget(ui.btn_sair, 0, QtCore.Qt.AlignmentFlag.AlignTop)
        ui.btn_sair.setProperty("classe", "perigo")

        # Cartões com os totais
        self.lbl_total_recebido = QtWidgets.QLabel()
        cartoes = QtWidgets.QHBoxLayout()
        cartoes.setSpacing(16)
        for cartao in (ui.lbl_total_clientes, ui.lbl_total_quadra, ui.lbl_total_churras, self.lbl_total_recebido):
            cartao.setProperty("classe", "card")
            cartao.setMinimumHeight(60)
            cartoes.addWidget(cartao, 1)

        layout = QtWidgets.QVBoxLayout(central)
        layout.setContentsMargins(24, 16, 24, 16)
        layout.setSpacing(16)
        layout.addLayout(topo)
        layout.addLayout(cartoes)
        layout.addWidget(ui.tabWidget, 1)

        # Aba de clientes
        busca = QtWidgets.QHBoxLayout()
        ui.txt_busca_cliente.setPlaceholderText("Buscar por nome, e-mail ou telefone...")
        ui.txt_busca_cliente.setMinimumWidth(320)
        busca.addWidget(ui.txt_busca_cliente)
        busca.addWidget(ui.btn_pesquisar_cliente)
        busca.addStretch()
        acoes = QtWidgets.QHBoxLayout()
        acoes.addWidget(ui.btn_novo_cliente)
        acoes.addWidget(ui.btn_editar_cliente)
        acoes.addWidget(ui.btn_excluir)
        acoes.addStretch()
        ui.btn_excluir.setProperty("classe", "perigo")
        aba_clientes = QtWidgets.QVBoxLayout(ui.tab)
        aba_clientes.addLayout(busca)
        aba_clientes.addWidget(ui.tableWidget_usuarios, 1)
        aba_clientes.addLayout(acoes)

        # Aba de reservas
        acoes_reservas = QtWidgets.QHBoxLayout()
        acoes_reservas.addWidget(ui.btn_atualizar_reservas)
        acoes_reservas.addWidget(ui.btn_cancelar_reserva)
        acoes_reservas.addStretch()
        ui.btn_cancelar_reserva.setProperty("classe", "perigo")
        aba_reservas = QtWidgets.QVBoxLayout(ui.tab_2)
        aba_reservas.addWidget(ui.tableWidget_reservas, 1)
        aba_reservas.addLayout(acoes_reservas)

        configurar_tabela(ui.tableWidget_usuarios)
        configurar_tabela(ui.tableWidget_reservas)

        # Direitos reservados no rodapé
        rodape = QtWidgets.QLabel(f"© {QtCore.QDate.currentDate().year()} {NOME_ASSOCIACAO} - Todos os direitos reservados.")
        ui.statusBar().addPermanentWidget(rodape)

    def carregar_totais(self):
        try:
            _, clientes = consultar("SELECT COUNT(*) FROM usuarios")
            _, quadra = consultar("SELECT COUNT(*) FROM reservas")
            _, churras = consultar("SELECT COUNT(*) FROM reservas_churrasqueira")
            _, recebido = consultar("SELECT COALESCE(SUM(valor), 0) FROM pagamentos WHERE status = 'pago'")
            self.ui.lbl_total_clientes.setText(f"👥 Clientes\n{clientes[0][0]}")
            self.ui.lbl_total_quadra.setText(f"⚽ Reservas de Quadra\n{quadra[0][0]}")
            self.ui.lbl_total_churras.setText(f"🍖 Reservas de Churrasqueira\n{churras[0][0]}")
            self.lbl_total_recebido.setText(f"💰 Total Recebido\n{formatar_dinheiro(recebido[0][0])}")
        except Exception as e:
            QtWidgets.QMessageBox.critical(self.ui, "Erro ao Carregar Totais", f"Detalhes:\n{e}")

    # ------------------------------------------
    # CLIENTES
    # ------------------------------------------
    def carregar_clientes(self):
        try:
            # Puxa todas as colunas da tabela de usuários sem risco de erro de nome de coluna
            colunas, resultados = consultar("SELECT * FROM usuarios")

            self.ui.tableWidget_usuarios.setRowCount(len(resultados))
            self.ui.tableWidget_usuarios.setColumnCount(len(colunas))
            self.ui.tableWidget_usuarios.setHorizontalHeaderLabels(colunas)

            for row_idx, row_data in enumerate(resultados):
                for col_idx, data in enumerate(row_data):
                    if colunas[col_idx] == "senha":
                        texto = MASCARA_SENHA  # A senha nunca aparece na tela
                    else:
                        texto = str(data) if data is not None else "Não Informado"
                    self.ui.tableWidget_usuarios.setItem(row_idx, col_idx, QtWidgets.QTableWidgetItem(texto))

            self.ui.tableWidget_usuarios.resizeColumnsToContents()
        except Exception as e:
            QtWidgets.QMessageBox.critical(self.ui, "Erro ao Carregar Clientes", f"Detalhes:\n{e}")

    def pesquisar_cliente(self):
        termo = self.ui.txt_busca_cliente.text().strip().lower()
        tabela = self.ui.tableWidget_usuarios
        for linha in range(tabela.rowCount()):
            textos = [tabela.item(linha, col).text().lower() for col in range(tabela.columnCount()) if tabela.item(linha, col)]
            tabela.setRowHidden(linha, bool(termo) and not any(termo in texto for texto in textos))

    # ------------------------------------------
    # RESERVAS
    # ------------------------------------------
    def carregar_reservas_detalhadas(self):
        try:
            # Query segura para quadra e churrasqueira, com a situação do pagamento
            sql = """
                SELECT r.id, u.nome, r.data, r.horario, r.tipo_reserva, r.valor, 'Quadra' AS local,
                       COALESCE(p.status, 'pendente') AS pagamento
                FROM reservas r
                JOIN usuarios u ON r.usuario_email = u.email
                LEFT JOIN pagamentos p ON p.tipo = 'quadra' AND p.reserva_id = r.id
                UNION ALL
                SELECT rc.id, u.nome, rc.data, 'Dia Inteiro' AS horario, rc.tipo_reserva, rc.valor, 'Churrasqueira' AS local,
                       COALESCE(p.status, 'pendente') AS pagamento
                FROM reservas_churrasqueira rc
                JOIN usuarios u ON rc.usuario_email = u.email
                LEFT JOIN pagamentos p ON p.tipo = 'churrasqueira' AND p.reserva_id = rc.id
                ORDER BY data DESC
            """
            _, resultados = consultar(sql)

            linhas = []
            for id_reserva, nome, data, horario, tipo, valor, local, pagamento in resultados:
                linhas.append([id_reserva, nome, formatar_data(data), horario, tipo, formatar_dinheiro(valor), local, pagamento.capitalize()])

            preencher_tabela(self.ui.tableWidget_reservas,
                             ["ID", "Responsável", "Data", "Horário", "Modalidade/Plano", "Valor", "Local", "Pagamento"],
                             linhas)
        except Exception as e:
            QtWidgets.QMessageBox.critical(self.ui, "Erro ao Carregar Reservas", f"Detalhes:\n{e}")

    # ------------------------------------------
    # SUPORTE (mensagens enviadas pelo site)
    # ------------------------------------------
    def criar_aba_suporte(self):
        aba = QtWidgets.QWidget()
        layout = QtWidgets.QHBoxLayout(aba)
        layout.setSpacing(16)

        # Lista de mensagens
        esquerda = QtWidgets.QVBoxLayout()
        filtro = QtWidgets.QHBoxLayout()
        self.cmb_filtro_suporte = QtWidgets.QComboBox()
        self.cmb_filtro_suporte.addItems(["Todas", "Aguardando resposta", "Respondidas", "Resolvidas"])
        self.cmb_filtro_suporte.currentIndexChanged.connect(self.carregar_suporte)
        btn_atualizar = botao("Atualizar", "secundario")
        btn_atualizar.clicked.connect(self.carregar_suporte)
        filtro.addWidget(QtWidgets.QLabel("Mostrar:"))
        filtro.addWidget(self.cmb_filtro_suporte)
        filtro.addStretch()
        filtro.addWidget(btn_atualizar)
        self.tabela_suporte = QtWidgets.QTableWidget()
        configurar_tabela(self.tabela_suporte)
        self.tabela_suporte.itemSelectionChanged.connect(self.mostrar_mensagem_suporte)
        esquerda.addLayout(filtro)
        esquerda.addWidget(self.tabela_suporte, 1)

        # Detalhes e resposta
        direita = QtWidgets.QVBoxLayout()
        self.lbl_suporte_titulo = QtWidgets.QLabel("Selecione uma mensagem")
        self.lbl_suporte_titulo.setProperty("classe", "subtitulo")
        self.lbl_suporte_titulo.setWordWrap(True)
        self.txt_suporte_mensagem = QtWidgets.QTextBrowser()
        self.txt_suporte_resposta = QtWidgets.QTextEdit()
        self.txt_suporte_resposta.setPlaceholderText("Escreva a resposta que o sócio verá no site...")
        botoes = QtWidgets.QHBoxLayout()
        self.btn_responder = botao("Enviar Resposta", "sucesso")
        self.btn_responder.clicked.connect(self.responder_suporte)
        self.btn_resolver = botao("Marcar como Resolvido", "secundario")
        self.btn_resolver.clicked.connect(self.resolver_suporte)
        botoes.addWidget(self.btn_responder)
        botoes.addWidget(self.btn_resolver)
        direita.addWidget(self.lbl_suporte_titulo)
        direita.addWidget(self.txt_suporte_mensagem, 2)
        direita.addWidget(QtWidgets.QLabel("Resposta:"))
        direita.addWidget(self.txt_suporte_resposta, 1)
        direita.addLayout(botoes)

        layout.addLayout(esquerda, 3)
        layout.addLayout(direita, 2)
        self.mensagens_suporte = {}
        self.indice_aba_suporte = self.ui.tabWidget.addTab(aba, "Suporte")

    def carregar_suporte(self):
        filtros = {1: "WHERE s.status = 'aberto'", 2: "WHERE s.status = 'respondido'", 3: "WHERE s.status = 'resolvido'"}
        filtro = filtros.get(self.cmb_filtro_suporte.currentIndex(), "")
        try:
            _, resultados = consultar(f"""
                SELECT s.id, s.criado_em, COALESCE(u.nome, s.usuario_email), s.usuario_email, u.telefone,
                       s.assunto, s.status, s.mensagem, s.resposta
                FROM suporte_mensagens s
                LEFT JOIN usuarios u ON u.email = s.usuario_email
                {filtro}
                ORDER BY s.status = 'aberto' DESC, s.criado_em DESC
            """)
        except Exception as e:
            QtWidgets.QMessageBox.critical(self.ui, "Erro ao Carregar Suporte", f"Detalhes:\n{e}")
            return

        rotulos = {"aberto": "Aguardando resposta", "respondido": "Respondida", "resolvido": "Resolvida"}
        self.mensagens_suporte = {linha[0]: linha for linha in resultados}
        linhas = [[m[0], formatar_data(m[1], True), m[2], m[5], rotulos.get(m[6], m[6])] for m in resultados]
        preencher_tabela(self.tabela_suporte, ["ID", "Recebida em", "Sócio", "Assunto", "Situação"], linhas)
        self.mostrar_mensagem_suporte()

    def mostrar_mensagem_suporte(self):
        mensagem = self.mensagens_suporte.get(id_selecionado(self.tabela_suporte))
        self.btn_responder.setEnabled(mensagem is not None)
        self.btn_resolver.setEnabled(mensagem is not None)
        if mensagem is None:
            self.lbl_suporte_titulo.setText("Selecione uma mensagem")
            self.txt_suporte_mensagem.clear()
            self.txt_suporte_resposta.clear()
            return

        _, criado_em, nome, email, telefone, assunto, _, texto, resposta = mensagem
        self.lbl_suporte_titulo.setText(f"{assunto} — {nome}")
        detalhes = f"De: {nome} <{email}>\n"
        if telefone:
            detalhes += f"Telefone: {telefone}\n"
        detalhes += f"Recebida em: {formatar_data(criado_em, True)}\n\n{texto}"
        self.txt_suporte_mensagem.setPlainText(detalhes)
        self.txt_suporte_resposta.setPlainText(resposta or "")

    def responder_suporte(self):
        id_mensagem = id_selecionado(self.tabela_suporte)
        resposta = self.txt_suporte_resposta.toPlainText().strip()
        if id_mensagem is None or not resposta:
            QtWidgets.QMessageBox.warning(self.ui, "Aviso", "Selecione uma mensagem e escreva a resposta!")
            return
        try:
            executar("""UPDATE suporte_mensagens
                        SET resposta = %s, status = 'respondido', respondido_em = NOW(), resposta_lida = 0
                        WHERE id = %s""", (resposta, id_mensagem))
            self.ui.statusBar().showMessage("Resposta enviada! O sócio verá no site.", 5000)
            self.carregar_suporte()
            self.verificar_novas_mensagens()
        except Exception as e:
            QtWidgets.QMessageBox.critical(self.ui, "Erro", f"Erro ao responder:\n{e}")

    def resolver_suporte(self):
        id_mensagem = id_selecionado(self.tabela_suporte)
        if id_mensagem is None:
            return
        try:
            executar("UPDATE suporte_mensagens SET status = 'resolvido' WHERE id = %s", (id_mensagem,))
            self.carregar_suporte()
            self.verificar_novas_mensagens()
        except Exception as e:
            QtWidgets.QMessageBox.critical(self.ui, "Erro", f"Erro ao atualizar:\n{e}")

    def contar_mensagens_abertas(self):
        try:
            _, resultado = consultar("SELECT COUNT(*) FROM suporte_mensagens WHERE status = 'aberto'")
            return resultado[0][0]
        except Exception:
            return 0

    def atualizar_titulo_suporte(self):
        titulo = f"Suporte ({self.total_abertas})" if self.total_abertas else "Suporte"
        self.ui.tabWidget.setTabText(self.indice_aba_suporte, titulo)

    def verificar_novas_mensagens(self):
        total = self.contar_mensagens_abertas()
        if total > self.total_abertas:
            self.ui.statusBar().showMessage("💬 Nova mensagem de suporte recebida pelo site!", 10000)
            QtWidgets.QApplication.alert(self.ui)  # Pisca o ícone na barra de tarefas
            if not self.txt_suporte_resposta.toPlainText().strip():
                self.carregar_suporte()
        self.total_abertas = total
        self.atualizar_titulo_suporte()

    # ------------------------------------------
    # NOTÍCIAS (mural do painel do sócio)
    # ------------------------------------------
    def criar_aba_noticias(self):
        aba = QtWidgets.QWidget()
        layout = QtWidgets.QHBoxLayout(aba)
        layout.setSpacing(16)

        self.tabela_noticias = QtWidgets.QTableWidget()
        configurar_tabela(self.tabela_noticias)
        self.tabela_noticias.itemSelectionChanged.connect(self.selecionar_noticia)

        formulario = QtWidgets.QFormLayout()
        self.txt_noticia_tag = QtWidgets.QLineEdit()
        self.txt_noticia_tag.setPlaceholderText("Ex: Aviso Importante, Evento, Campeonato")
        self.txt_noticia_titulo = QtWidgets.QLineEdit()
        self.txt_noticia_texto = QtWidgets.QTextEdit()
        self.chk_noticia_destaque = QtWidgets.QCheckBox("Mostrar em destaque (no topo, com borda amarela)")
        formulario.addRow("Etiqueta:", self.txt_noticia_tag)
        formulario.addRow("Título:", self.txt_noticia_titulo)
        formulario.addRow("Texto:", self.txt_noticia_texto)
        formulario.addRow("", self.chk_noticia_destaque)

        botoes = QtWidgets.QHBoxLayout()
        btn_nova = botao("Nova Notícia", "secundario")
        btn_nova.clicked.connect(self.limpar_noticia)
        btn_salvar = botao("Salvar", "sucesso")
        btn_salvar.clicked.connect(self.salvar_noticia)
        btn_excluir = botao("Excluir", "perigo")
        btn_excluir.clicked.connect(self.excluir_noticia)
        botoes.addWidget(btn_nova)
        botoes.addWidget(btn_salvar)
        botoes.addWidget(btn_excluir)

        direita = QtWidgets.QVBoxLayout()
        self.lbl_noticia_modo = QtWidgets.QLabel("Nova notícia")
        self.lbl_noticia_modo.setProperty("classe", "subtitulo")
        direita.addWidget(self.lbl_noticia_modo)
        direita.addLayout(formulario, 1)
        direita.addLayout(botoes)

        layout.addWidget(self.tabela_noticias, 3)
        layout.addLayout(direita, 2)
        self.noticia_id = None
        self.ui.tabWidget.addTab(aba, "Notícias")

    def carregar_noticias(self):
        try:
            _, resultados = consultar("SELECT id, criado_em, tag, titulo, destaque, texto FROM noticias ORDER BY destaque DESC, criado_em DESC, id DESC")
        except Exception as e:
            QtWidgets.QMessageBox.critical(self.ui, "Erro ao Carregar Notícias", f"Detalhes:\n{e}")
            return
        self.noticias = {linha[0]: linha for linha in resultados}
        linhas = [[n[0], formatar_data(n[1]), n[2], n[3], "⭐ Sim" if n[4] else "Não"] for n in resultados]
        preencher_tabela(self.tabela_noticias, ["ID", "Data", "Etiqueta", "Título", "Destaque"], linhas)

    def selecionar_noticia(self):
        noticia = self.noticias.get(id_selecionado(self.tabela_noticias))
        if noticia is None:
            return
        self.noticia_id = noticia[0]
        self.lbl_noticia_modo.setText(f"Editando notícia #{noticia[0]}")
        self.txt_noticia_tag.setText(noticia[2])
        self.txt_noticia_titulo.setText(noticia[3])
        self.chk_noticia_destaque.setChecked(bool(noticia[4]))
        self.txt_noticia_texto.setPlainText(noticia[5])

    def limpar_noticia(self):
        self.noticia_id = None
        self.tabela_noticias.clearSelection()
        self.lbl_noticia_modo.setText("Nova notícia")
        self.txt_noticia_tag.clear()
        self.txt_noticia_titulo.clear()
        self.txt_noticia_texto.clear()
        self.chk_noticia_destaque.setChecked(False)

    def salvar_noticia(self):
        tag = self.txt_noticia_tag.text().strip() or "Aviso"
        titulo = self.txt_noticia_titulo.text().strip()
        texto = self.txt_noticia_texto.toPlainText().strip()
        destaque = 1 if self.chk_noticia_destaque.isChecked() else 0
        if not titulo or not texto:
            QtWidgets.QMessageBox.warning(self.ui, "Aviso", "Preencha o título e o texto da notícia!")
            return
        try:
            if self.noticia_id is None:
                executar("INSERT INTO noticias (tag, titulo, texto, destaque) VALUES (%s, %s, %s, %s)",
                         (tag, titulo, texto, destaque))
            else:
                executar("UPDATE noticias SET tag = %s, titulo = %s, texto = %s, destaque = %s WHERE id = %s",
                         (tag, titulo, texto, destaque, self.noticia_id))
            self.ui.statusBar().showMessage("Notícia salva! Ela já aparece no painel do sócio.", 5000)
            self.carregar_noticias()
            self.limpar_noticia()
        except Exception as e:
            QtWidgets.QMessageBox.critical(self.ui, "Erro", f"Erro ao salvar notícia:\n{e}")

    def excluir_noticia(self):
        if self.noticia_id is None:
            QtWidgets.QMessageBox.warning(self.ui, "Aviso", "Selecione uma notícia na lista!")
            return
        confirmar = QtWidgets.QMessageBox.question(self.ui, "Excluir", "Deseja excluir esta notícia?")
        if confirmar != QtWidgets.QMessageBox.StandardButton.Yes:
            return
        try:
            executar("DELETE FROM noticias WHERE id = %s", (self.noticia_id,))
            self.carregar_noticias()
            self.limpar_noticia()
        except Exception as e:
            QtWidgets.QMessageBox.critical(self.ui, "Erro", f"Erro ao excluir notícia:\n{e}")

    # ------------------------------------------
    # PLACARES DA FEDERAÇÃO
    # ------------------------------------------
    def criar_aba_placares(self):
        aba = QtWidgets.QWidget()
        layout = QtWidgets.QHBoxLayout(aba)
        layout.setSpacing(16)

        self.tabela_placares = QtWidgets.QTableWidget()
        configurar_tabela(self.tabela_placares)
        self.tabela_placares.itemSelectionChanged.connect(self.selecionar_placar)

        formulario = QtWidgets.QFormLayout()
        self.txt_placar_campeonato = QtWidgets.QLineEdit()
        self.txt_placar_campeonato.setPlaceholderText("Ex: Federação, Copa Santo André")
        self.dt_placar_data = QtWidgets.QDateEdit(QtCore.QDate.currentDate())
        self.dt_placar_data.setCalendarPopup(True)
        self.dt_placar_data.setDisplayFormat("dd/MM/yyyy")
        self.txt_placar_casa = QtWidgets.QLineEdit()
        self.spn_gols_casa = QtWidgets.QSpinBox()
        self.spn_gols_casa.setRange(0, 99)
        self.spn_gols_visitante = QtWidgets.QSpinBox()
        self.spn_gols_visitante.setRange(0, 99)
        self.txt_placar_visitante = QtWidgets.QLineEdit()
        placar = QtWidgets.QHBoxLayout()
        placar.addWidget(self.spn_gols_casa)
        placar.addWidget(QtWidgets.QLabel("x"))
        placar.addWidget(self.spn_gols_visitante)
        placar.addStretch()
        formulario.addRow("Campeonato:", self.txt_placar_campeonato)
        formulario.addRow("Data do jogo:", self.dt_placar_data)
        formulario.addRow("Time da casa:", self.txt_placar_casa)
        formulario.addRow("Placar:", placar)
        formulario.addRow("Visitante:", self.txt_placar_visitante)

        botoes = QtWidgets.QHBoxLayout()
        btn_novo = botao("Novo Placar", "secundario")
        btn_novo.clicked.connect(self.limpar_placar)
        btn_salvar = botao("Salvar", "sucesso")
        btn_salvar.clicked.connect(self.salvar_placar)
        btn_excluir = botao("Excluir", "perigo")
        btn_excluir.clicked.connect(self.excluir_placar)
        botoes.addWidget(btn_novo)
        botoes.addWidget(btn_salvar)
        botoes.addWidget(btn_excluir)

        direita = QtWidgets.QVBoxLayout()
        self.lbl_placar_modo = QtWidgets.QLabel("Novo placar")
        self.lbl_placar_modo.setProperty("classe", "subtitulo")
        direita.addWidget(self.lbl_placar_modo)
        direita.addLayout(formulario)
        direita.addStretch()
        direita.addLayout(botoes)

        layout.addWidget(self.tabela_placares, 3)
        layout.addLayout(direita, 2)
        self.placar_id = None
        self.limpar_placar()
        self.ui.tabWidget.addTab(aba, "Placares")

    def carregar_placares(self):
        try:
            _, resultados = consultar("""SELECT id, data_jogo, campeonato, time_casa, gols_casa, gols_visitante, time_visitante
                                         FROM placares ORDER BY data_jogo IS NULL, data_jogo DESC, id DESC""")
        except Exception as e:
            QtWidgets.QMessageBox.critical(self.ui, "Erro ao Carregar Placares", f"Detalhes:\n{e}")
            return
        self.placares = {linha[0]: linha for linha in resultados}
        linhas = [[p[0], formatar_data(p[1]), p[2], p[3], f"{p[4]} x {p[5]}", p[6]] for p in resultados]
        preencher_tabela(self.tabela_placares, ["ID", "Data", "Campeonato", "Casa", "Placar", "Visitante"], linhas)

    def selecionar_placar(self):
        placar = self.placares.get(id_selecionado(self.tabela_placares))
        if placar is None:
            return
        self.placar_id = placar[0]
        self.lbl_placar_modo.setText(f"Editando placar #{placar[0]}")
        self.dt_placar_data.setDate(QtCore.QDate(placar[1].year, placar[1].month, placar[1].day) if placar[1] else QtCore.QDate.currentDate())
        self.txt_placar_campeonato.setText(placar[2] or "")
        self.txt_placar_casa.setText(placar[3])
        self.spn_gols_casa.setValue(placar[4])
        self.spn_gols_visitante.setValue(placar[5])
        self.txt_placar_visitante.setText(placar[6])

    def limpar_placar(self):
        self.placar_id = None
        self.tabela_placares.clearSelection()
        self.lbl_placar_modo.setText("Novo placar")
        self.txt_placar_campeonato.setText("Federação")
        self.dt_placar_data.setDate(QtCore.QDate.currentDate())
        self.txt_placar_casa.setText("Camilópolis FC")
        self.spn_gols_casa.setValue(0)
        self.spn_gols_visitante.setValue(0)
        self.txt_placar_visitante.clear()

    def salvar_placar(self):
        casa = self.txt_placar_casa.text().strip()
        visitante = self.txt_placar_visitante.text().strip()
        if not casa or not visitante:
            QtWidgets.QMessageBox.warning(self.ui, "Aviso", "Preencha o nome dos dois times!")
            return
        dados = (self.txt_placar_campeonato.text().strip() or None,
                 self.dt_placar_data.date().toString("yyyy-MM-dd"),
                 casa, self.spn_gols_casa.value(), self.spn_gols_visitante.value(), visitante)
        try:
            if self.placar_id is None:
                executar("""INSERT INTO placares (campeonato, data_jogo, time_casa, gols_casa, gols_visitante, time_visitante)
                            VALUES (%s, %s, %s, %s, %s, %s)""", dados)
            else:
                executar("""UPDATE placares SET campeonato = %s, data_jogo = %s, time_casa = %s, gols_casa = %s,
                            gols_visitante = %s, time_visitante = %s WHERE id = %s""", dados + (self.placar_id,))
            self.ui.statusBar().showMessage("Placar salvo! Ele já aparece no painel do sócio.", 5000)
            self.carregar_placares()
            self.limpar_placar()
        except Exception as e:
            QtWidgets.QMessageBox.critical(self.ui, "Erro", f"Erro ao salvar placar:\n{e}")

    def excluir_placar(self):
        if self.placar_id is None:
            QtWidgets.QMessageBox.warning(self.ui, "Aviso", "Selecione um placar na lista!")
            return
        confirmar = QtWidgets.QMessageBox.question(self.ui, "Excluir", "Deseja excluir este placar?")
        if confirmar != QtWidgets.QMessageBox.StandardButton.Yes:
            return
        try:
            executar("DELETE FROM placares WHERE id = %s", (self.placar_id,))
            self.carregar_placares()
            self.limpar_placar()
        except Exception as e:
            QtWidgets.QMessageBox.critical(self.ui, "Erro", f"Erro ao excluir placar:\n{e}")

    # ------------------------------------------
    # PAGAMENTOS (aba "Relatórios e Métricas")
    # ------------------------------------------
    def criar_aba_pagamentos(self):
        layout = QtWidgets.QVBoxLayout(self.ui.tab_3)
        topo = QtWidgets.QHBoxLayout()
        titulo = QtWidgets.QLabel("Pagamentos recebidos pelo site (mensalidades e aluguéis)")
        titulo.setProperty("classe", "subtitulo")
        btn_atualizar = botao("Atualizar", "secundario")
        btn_atualizar.clicked.connect(self.carregar_pagamentos)
        topo.addWidget(titulo)
        topo.addStretch()
        topo.addWidget(btn_atualizar)
        self.tabela_pagamentos = QtWidgets.QTableWidget()
        configurar_tabela(self.tabela_pagamentos)
        layout.addLayout(topo)
        layout.addWidget(self.tabela_pagamentos, 1)
        self.ui.tabWidget.setTabText(self.ui.tabWidget.indexOf(self.ui.tab_3), "Pagamentos")

    def carregar_pagamentos(self):
        try:
            _, resultados = consultar("""
                SELECT p.id, COALESCE(u.nome, p.usuario_email), p.descricao, p.valor, p.status, p.metodo, p.pago_em
                FROM pagamentos p
                LEFT JOIN usuarios u ON u.email = p.usuario_email
                ORDER BY p.status = 'pago' DESC, p.pago_em DESC, p.id DESC
            """)
        except Exception as e:
            QtWidgets.QMessageBox.critical(self.ui, "Erro ao Carregar Pagamentos", f"Detalhes:\n{e}")
            return
        linhas = [[p[0], p[1], p[2], formatar_dinheiro(p[3]), p[4].capitalize(), p[5] or "", formatar_data(p[6], True)]
                  for p in resultados]
        preencher_tabela(self.tabela_pagamentos, ["ID", "Sócio", "Descrição", "Valor", "Situação", "Forma", "Pago em"], linhas)

    def fechar_painel(self):
        self.ui.close()


class TelaLogin:
    def __init__(self):
        loader = QUiLoader()
        self.ui = loader.load(os.path.join(PASTA_APP, "login_admin.ui"), None)
        self.ui.setWindowTitle("Acesso Administrativo - Camilópolis")
        self.organizar_layout()

        # Abre a tela de login em tela cheia automaticamente
        self.ui.showMaximized()

        # Conexões dos botões
        self.ui.btn_login.clicked.connect(self.fazer_login)
        self.ui.btn_cadastrar.clicked.connect(self.fazer_cadastro)
        self.ui.txt_senha_login.returnPressed.connect(self.fazer_login)

        if hasattr(self.ui, "btn_esqueceu_senha"):
            self.ui.btn_esqueceu_senha.clicked.connect(self.recuperar_senha)

        # Botão de mostrar/ocultar nos campos de senha
        for campo in (self.ui.txt_senha_login, self.ui.txt_senha_cad):
            acao = campo.addAction(icone_texto("👁"), QtWidgets.QLineEdit.ActionPosition.TrailingPosition)
            acao.setToolTip("Mostrar/ocultar senha")
            acao.triggered.connect(lambda _=False, c=campo: c.setEchoMode(
                QtWidgets.QLineEdit.EchoMode.Normal if c.echoMode() == QtWidgets.QLineEdit.EchoMode.Password else QtWidgets.QLineEdit.EchoMode.Password))

    def organizar_layout(self):
        ui = self.ui

        # Lado esquerdo azul com a logo
        marca = QtWidgets.QFrame()
        marca.setProperty("classe", "marca")
        ui.lbl_logo.setFixedSize(200, 200)
        ui.lbl_logo.setScaledContents(True)
        ui.label.setAlignment(QtCore.Qt.AlignmentFlag.AlignCenter)
        ui.label.setWordWrap(True)
        lado_marca = QtWidgets.QVBoxLayout(marca)
        lado_marca.setContentsMargins(40, 40, 40, 40)
        lado_marca.addStretch()
        lado_marca.addWidget(ui.lbl_logo, 0, QtCore.Qt.AlignmentFlag.AlignHCenter)
        lado_marca.addSpacing(20)
        lado_marca.addWidget(ui.label)
        lado_marca.addStretch()

        # Lado direito com o formulário
        ui.tabWidget_login.setFixedSize(ui.tabWidget_login.size())
        ui.label_2.setAlignment(QtCore.Qt.AlignmentFlag.AlignCenter)
        conteudo = QtWidgets.QVBoxLayout()
        conteudo.addStretch()
        conteudo.addWidget(ui.label_2, 0, QtCore.Qt.AlignmentFlag.AlignHCenter)
        conteudo.addSpacing(10)
        conteudo.addWidget(ui.tabWidget_login, 0, QtCore.Qt.AlignmentFlag.AlignHCenter)
        conteudo.addStretch()

        layout = QtWidgets.QHBoxLayout(ui)
        layout.setContentsMargins(0, 0, 0, 0)
        layout.addWidget(marca, 2)
        layout.addLayout(conteudo, 3)

    def fazer_login(self):
        email = self.ui.txt_email_login.text()
        senha = self.ui.txt_senha_login.text()

        if not email or not senha:
            QtWidgets.QMessageBox.warning(self.ui, "Aviso", "Preencha o e-mail e a senha!")
            return

        try:
            _, resultado = consultar("SELECT id, senha FROM usuarios WHERE email = %s", (email,))

            if resultado and senha_confere(senha, resultado[0][1]):
                # Senhas antigas (texto puro) são criptografadas no primeiro login
                if not resultado[0][1].startswith("$2"):
                    executar("UPDATE usuarios SET senha = %s WHERE id = %s", (criptografar_senha(senha), resultado[0][0]))
                self.painel = PainelAdmin()
                self.ui.close()
            else:
                QtWidgets.QMessageBox.warning(self.ui, "Erro", "E-mail ou senha incorretos!")
        except Exception as e:
            QtWidgets.QMessageBox.critical(self.ui, "Erro de Conexão", f"Erro:\n{e}")

    def fazer_cadastro(self):
        nome = self.ui.txt_nome_cad.text()
        email = self.ui.txt_email_cad.text()
        telefone = self.ui.txt_tel_cad.text() if hasattr(self.ui, "txt_tel_cad") else ""
        senha = self.ui.txt_senha_cad.text()

        if not nome or not email or not senha:
            QtWidgets.QMessageBox.warning(self.ui, "Aviso", "Preencha os campos obrigatórios!")
            return

        try:
            executar("INSERT INTO usuarios (nome, email, telefone, senha) VALUES (%s, %s, %s, %s)",
                     (nome, email, telefone, criptografar_senha(senha)))
            QtWidgets.QMessageBox.information(self.ui, "Sucesso", "Cadastro realizado com sucesso!")
        except Exception as e:
            QtWidgets.QMessageBox.critical(self.ui, "Erro", f"Erro ao cadastrar:\n{e}")

    def recuperar_senha(self):
        email, ok = QtWidgets.QInputDialog.getText(self.ui, "Recuperação", "Digite seu e-mail:")
        if ok and email:
            QtWidgets.QMessageBox.information(self.ui, "Recuperação", f"Instruções enviadas para {email}.")


if __name__ == "__main__":
    # Faz o Windows mostrar o ícone do aplicativo na barra de tarefas
    if sys.platform == "win32":
        ctypes.windll.shell32.SetCurrentProcessExplicitAppUserModelID("camilopolis.painel.admin")

    app = QtWidgets.QApplication(sys.argv)
    app.setWindowIcon(QtGui.QIcon(os.path.join(PASTA_APP, "img", "icone_app.ico")))
    with open(os.path.join(PASTA_APP, "estilo_app.qss"), encoding="utf-8") as arquivo_estilo:
        app.setStyleSheet(arquivo_estilo.read())

    if bcrypt is None:
        QtWidgets.QMessageBox.critical(None, "Bibliotecas faltando",
                                       "Execute o arquivo instalar_aplicativo.bat para instalar as bibliotecas do aplicativo.")
        sys.exit(1)

    try:
        garantir_estrutura()
    except Exception as e:
        QtWidgets.QMessageBox.critical(None, "Erro de Conexão",
                                       f"Não foi possível acessar o banco de dados.\nVerifique se o MySQL do XAMPP está ligado.\n\n{e}")
        sys.exit(1)

    login = TelaLogin()
    sys.exit(app.exec())
