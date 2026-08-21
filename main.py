import sys
from PySide6 import QtWidgets
from PySide6.QtUiTools import QUiLoader
import mysql.connector

def conectar_banco():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="camilopolis_db"
    )

class PainelAdmin:
    def __init__(self):
        loader = QUiLoader()
        self.ui = loader.load("painel_admin.ui", None)
        
        # Abre o painel administrativo em tela cheia automaticamente
        self.ui.showMaximized()
        
        # Conecta o botão de Sair se ele existir na tela
        if hasattr(self.ui, "btn_sair"):
            self.ui.btn_sair.clicked.connect(self.fechar_painel)

        # Carrega os dados nas tabelas
        self.carregar_clientes()
        self.carregar_reservas_detalhadas()

    def carregar_clientes(self):
        try:
            conexao = conectar_banco()
            cursor = conexao.cursor()
            
            # Puxa todas as colunas da tabela de usuários sem risco de erro de nome de coluna
            cursor.execute("SELECT * FROM usuarios")
            resultados = cursor.fetchall()
            
            colunas = [desc[0] for desc in cursor.description]
            
            self.ui.tableWidget_usuarios.setRowCount(len(resultados))
            self.ui.tableWidget_usuarios.setColumnCount(len(colunas))
            self.ui.tableWidget_usuarios.setHorizontalHeaderLabels(colunas)

            for row_idx, row_data in enumerate(resultados):
                for col_idx, data in enumerate(row_data):
                    item = QtWidgets.QTableWidgetItem(str(data) if data is not None else "Não Informado")
                    self.ui.tableWidget_usuarios.setItem(row_idx, col_idx, item)
            
            self.ui.tableWidget_usuarios.resizeColumnsToContents()
            cursor.close()
            conexao.close()
        except Exception as e:
            QtWidgets.QMessageBox.critical(self.ui, "Erro ao Carregar Clientes", f"Detalhes:\n{e}")

    def carregar_reservas_detalhadas(self):
        try:
            conexao = conectar_banco()
            cursor = conexao.cursor()
            
            # Query segura para quadra e churrasqueira
            sql = """
                SELECT r.id, u.nome, r.data, r.horario, r.tipo_reserva, r.valor, 'Quadra' AS local 
                FROM reservas r
                JOIN usuarios u ON r.usuario_email = u.email
                UNION ALL
                SELECT rc.id, u.nome, rc.data, 'Dia Inteiro' AS horario, rc.tipo_reserva, rc.valor, 'Churrasqueira' AS local 
                FROM reservas_churrasqueira rc
                JOIN usuarios u ON rc.usuario_email = u.email
                ORDER BY data DESC
            """
            
            cursor.execute(sql)
            resultados = cursor.fetchall()
            
            self.ui.tableWidget_reservas.setRowCount(len(resultados))
            self.ui.tableWidget_reservas.setColumnCount(7) 
            self.ui.tableWidget_reservas.setHorizontalHeaderLabels([
                "ID", "Responsável", "Data", "Horário", "Modalidade/Plano", "Valor", "Local"
            ])

            for row_idx, row_data in enumerate(resultados):
                for col_idx, data in enumerate(row_data):
                    if col_idx == 5:
                        try:
                            val = float(data) if data is not None else 0.0
                        except ValueError:
                            val = 0.0
                        item = QtWidgets.QTableWidgetItem(f"R$ {val:.2f}".replace('.', ','))
                    else:
                        item = QtWidgets.QTableWidgetItem(str(data) if data is not None else "")
                    
                    self.ui.tableWidget_reservas.setItem(row_idx, col_idx, item)
            
            self.ui.tableWidget_reservas.resizeColumnsToContents()
            cursor.close()
            conexao.close()
        except Exception as e:
            QtWidgets.QMessageBox.critical(self.ui, "Erro ao Carregar Reservas", f"Detalhes:\n{e}")

    def fechar_painel(self):
        self.ui.close()


class TelaLogin:
    def __init__(self):
        loader = QUiLoader()
        self.ui = loader.load("login_admin.ui", None)
        
        # Abre a tela de login em tela cheia automaticamente
        self.ui.showMaximized()
        
        # Conexões dos botões
        self.ui.btn_login.clicked.connect(self.fazer_login)
        self.ui.btn_cadastrar.clicked.connect(self.fazer_cadastro)
        
        if hasattr(self.ui, "btn_esqueceu_senha"):
            self.ui.btn_esqueceu_senha.clicked.connect(self.recuperar_senha)

    def fazer_login(self):
        email = self.ui.txt_email_login.text()
        senha = self.ui.txt_senha_login.text()
        
        if not email or not senha:
            QtWidgets.QMessageBox.warning(self.ui, "Aviso", "Preencha o e-mail e a senha!")
            return

        try:
            conexao = conectar_banco()
            cursor = conexao.cursor(dictionary=True)
            cursor.execute("SELECT * FROM usuarios WHERE email = %s AND senha = %s", (email, senha))
            resultado = cursor.fetchone()
            
            if resultado:
                self.ui.close()
                self.painel = PainelAdmin()
            else:
                QtWidgets.QMessageBox.warning(self.ui, "Erro", "E-mail ou senha incorretos!")
            cursor.close()
            conexao.close()
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
            conexao = conectar_banco()
            cursor = conexao.cursor()
            cursor.execute("INSERT INTO usuarios (nome, email, telefone, senha) VALUES (%s, %s, %s, %s)", (nome, email, telefone, senha))
            conexao.commit()
            QtWidgets.QMessageBox.information(self.ui, "Sucesso", "Cadastro realizado com sucesso!")
            cursor.close()
            conexao.close()
        except Exception as e:
            QtWidgets.QMessageBox.critical(self.ui, "Erro", f"Erro ao cadastrar:\n{e}")

    def recuperar_senha(self):
        email, ok = QtWidgets.QInputDialog.getText(self.ui, "Recuperação", "Digite seu e-mail:")
        if ok and email:
            QtWidgets.QMessageBox.information(self.ui, "Recuperação", f"Instruções enviadas para {email}.")


if __name__ == "__main__":
    app = QtWidgets.QApplication(sys.argv)
    login = TelaLogin()
    sys.exit(app.exec())