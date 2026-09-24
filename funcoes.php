<?php
// funcoes.php - Funções e configurações compartilhadas pelas páginas do site
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// CONFIGURAÇÕES DA ASSOCIAÇÃO
// ==========================================
define('NOME_ASSOCIACAO', 'Associação Amigos de Camilópolis');
define('CNPJ_ASSOCIACAO', ''); // Preencha com o CNPJ (ex: '00.000.000/0001-00') para aparecer no rodapé

define('PRECO_MENSALIDADE_SOCIO', 25.00);
define('PRECO_CHURRASQUEIRA', 150.00); // Confirme o valor com a Associação

// Dados do PIX (pagamento simulado). Troque pela chave PIX real da Associação se quiser usar de verdade.
define('CHAVE_PIX', 'chave-pix-da-associacao');
define('NOME_RECEBEDOR_PIX', 'ASSOC AMIGOS CAMILOPOLIS');
define('CIDADE_PIX', 'SANTO ANDRE');

// ==========================================
// BANCO DE DADOS
// ==========================================
function conectar_banco() {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli('localhost', 'root', '', 'camilopolis_db');
    $conn->set_charset('utf8mb4');
    garantir_estrutura($conn);
    return $conn;
}

// Cria as tabelas novas (pagamentos, suporte, notícias, placares) caso ainda não existam
function garantir_estrutura($conn) {
    if (!empty($_SESSION['estrutura_banco_ok'])) {
        return;
    }
    $sql = file_get_contents(__DIR__ . '/atualizacao_banco.sql');
    $conn->multi_query($sql);
    do {
        if ($resultado = $conn->store_result()) {
            $resultado->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    $_SESSION['estrutura_banco_ok'] = true;
}

// ==========================================
// AVISOS (substituem os pop-ups alert() do navegador)
// ==========================================
function definir_aviso($texto, $tipo = 'sucesso') {
    $_SESSION['aviso'] = ['texto' => $texto, 'tipo' => $tipo];
}

function redirecionar($url, $texto_aviso = null, $tipo = 'sucesso') {
    if ($texto_aviso !== null) {
        definir_aviso($texto_aviso, $tipo);
    }
    header("Location: $url");
    exit();
}

// Mostra o aviso guardado na sessão (se houver) e o apaga para não repetir
function exibir_aviso() {
    if (empty($_SESSION['aviso'])) {
        return;
    }
    $aviso = $_SESSION['aviso'];
    unset($_SESSION['aviso']);

    $icones = ['sucesso' => '✅', 'erro' => '⚠️', 'info' => 'ℹ️'];
    $tipo = isset($icones[$aviso['tipo']]) ? $aviso['tipo'] : 'info';

    echo '<div class="aviso-toast aviso-' . $tipo . '" role="status">'
       . '<span class="aviso-icone">' . $icones[$tipo] . '</span>'
       . '<span class="aviso-texto">' . htmlspecialchars($aviso['texto']) . '</span>'
       . '<button type="button" class="aviso-fechar" aria-label="Fechar aviso">&times;</button>'
       . '</div>';
}

// ==========================================
// SENHAS (criptografia com password_hash)
// ==========================================
function criptografar_senha($senha) {
    return password_hash($senha, PASSWORD_DEFAULT);
}

// Aceita senhas já criptografadas e também as antigas, salvas em texto puro
function senha_confere($senha_digitada, $senha_salva) {
    if (senha_esta_criptografada($senha_salva)) {
        return password_verify($senha_digitada, $senha_salva);
    }
    return hash_equals((string) $senha_salva, (string) $senha_digitada);
}

function senha_esta_criptografada($senha_salva) {
    return !empty(password_get_info((string) $senha_salva)['algo']);
}

// ==========================================
// PAGAMENTOS
// ==========================================
function formatar_dinheiro($valor) {
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

// Converte '2026-09' em 'Setembro/2026'
function nome_mes($ano_mes) {
    $meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho',
              'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    list($ano, $mes) = explode('-', $ano_mes);
    return $meses[(int) $mes - 1] . '/' . $ano;
}

// Cria o pagamento pendente (ou devolve o que já existe) e retorna o id
function obter_pagamento($conn, $email, $tipo, $reserva_id, $referencia, $descricao, $valor) {
    $stmt = $conn->prepare(
        "INSERT INTO pagamentos (usuario_email, tipo, reserva_id, referencia, descricao, valor)
         VALUES (?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)"
    );
    $stmt->bind_param("ssissd", $email, $tipo, $reserva_id, $referencia, $descricao, $valor);
    $stmt->execute();
    $id = $conn->insert_id;
    $stmt->close();
    return $id;
}

// Gera o código "PIX Copia e Cola" no padrão BR Code do Banco Central
function gerar_pix_copia_cola($valor, $identificador) {
    $conta = pix_campo('00', 'br.gov.bcb.pix') . pix_campo('01', CHAVE_PIX);
    $txid = substr(preg_replace('/[^A-Za-z0-9]/', '', $identificador), 0, 25);

    $payload = pix_campo('00', '01')
             . pix_campo('26', $conta)
             . pix_campo('52', '0000')
             . pix_campo('53', '986')
             . pix_campo('54', number_format((float) $valor, 2, '.', ''))
             . pix_campo('58', 'BR')
             . pix_campo('59', substr(NOME_RECEBEDOR_PIX, 0, 25))
             . pix_campo('60', substr(CIDADE_PIX, 0, 15))
             . pix_campo('62', pix_campo('05', $txid))
             . '6304';

    return $payload . pix_crc16($payload);
}

function pix_campo($id, $valor) {
    return $id . str_pad(strlen($valor), 2, '0', STR_PAD_LEFT) . $valor;
}

function pix_crc16($texto) {
    $crc = 0xFFFF;
    for ($i = 0; $i < strlen($texto); $i++) {
        $crc ^= ord($texto[$i]) << 8;
        for ($bit = 0; $bit < 8; $bit++) {
            $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) & 0xFFFF : ($crc << 1) & 0xFFFF;
        }
    }
    return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
}

// Validação do número do cartão (algoritmo de Luhn)
function cartao_valido($numero) {
    $numero = preg_replace('/\D/', '', $numero);
    if (strlen($numero) < 13 || strlen($numero) > 19) {
        return false;
    }
    $soma = 0;
    $dobrar = false;
    for ($i = strlen($numero) - 1; $i >= 0; $i--) {
        $digito = (int) $numero[$i];
        if ($dobrar) {
            $digito *= 2;
            if ($digito > 9) {
                $digito -= 9;
            }
        }
        $soma += $digito;
        $dobrar = !$dobrar;
    }
    return $soma % 10 === 0;
}

// ==========================================
// RODAPÉ
// ==========================================
function texto_direitos() {
    $texto = '© ' . date('Y') . ' ' . NOME_ASSOCIACAO;
    if (CNPJ_ASSOCIACAO !== '') {
        $texto .= ' - CNPJ ' . CNPJ_ASSOCIACAO;
    }
    return $texto . ' - Todos os direitos reservados.';
}
