<?php
/**
 * =====================================================
 * FUNÇÕES AUXILIARES - IZI STYLE E-COMMERCE
 * =====================================================
 * Funções para validação, segurança e utilidades
 * Arquivo: includes/funcoes.php
 * =====================================================
 */

// Incluir conexão
require_once __DIR__ . '/../config/conexao.php';

// Iniciar sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================================
// FUNÇÕES DE VALIDAÇÃO
// =====================================================

/**
 * Valida formato de email
 * @param string $email Email a ser validado
 * @return bool True se válido
 */
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida senha (mínimo 6 caracteres)
 * @param string $senha Senha a ser validada
 * @return bool True se válida
 */
function validarSenha($senha) {
    return strlen($senha) >= 6;
}

/**
 * Valida CPF
 * @param string $cpf CPF a ser validado
 * @return bool True se válido
 */
function validarCPF($cpf) {
    // Remove caracteres não numéricos
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Verifica se tem 11 dígitos
    if (strlen($cpf) != 11) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    // Validação do primeiro dígito verificador
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += $cpf[$i] * (10 - $i);
    }
    $resto = $soma % 11;
    $digito1 = ($resto < 2) ? 0 : 11 - $resto;
    
    if ($cpf[9] != $digito1) {
        return false;
    }
    
    // Validação do segundo dígito verificador
    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += $cpf[$i] * (11 - $i);
    }
    $resto = $soma % 11;
    $digito2 = ($resto < 2) ? 0 : 11 - $resto;
    
    return $cpf[10] == $digito2;
}

/**
 * Valida telefone brasileiro
 * @param string $telefone Telefone a ser validado
 * @return bool True se válido
 */
function validarTelefone($telefone) {
    // Remove caracteres não numéricos
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    
    // Verifica se tem 10 ou 11 dígitos (com DDD)
    return strlen($telefone) >= 10 && strlen($telefone) <= 11;
}

// =====================================================
// FUNÇÕES DE SANITIZAÇÃO
// =====================================================

/**
 * Limpa e sanitiza string
 * @param string $string String a ser limpa
 * @return string String sanitizada
 */
function limparString($string) {
    $string = trim($string);
    $string = strip_tags($string);
    $string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    return $string;
}

/**
 * Formata CPF (###.###.###-##)
 * @param string $cpf CPF a ser formatado
 * @return string CPF formatado
 */
function formatarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
}

/**
 * Formata telefone brasileiro
 * @param string $telefone Telefone a ser formatado
 * @return string Telefone formatado
 */
function formatarTelefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    
    if (strlen($telefone) == 11) {
        // Celular: (##) #####-####
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
    } else if (strlen($telefone) == 10) {
        // Fixo: (##) ####-####
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
    }
    
    return $telefone;
}

// =====================================================
// FUNÇÕES DE SEGURANÇA
// =====================================================

/**
 * Gera hash seguro da senha
 * @param string $senha Senha em texto plano
 * @return string Hash da senha
 */
function hashSenha($senha) {
    return password_hash($senha, PASSWORD_DEFAULT);
}

/**
 * Verifica se a senha corresponde ao hash
 * @param string $senha Senha em texto plano
 * @param string $hash Hash armazenado
 * @return bool True se a senha está correta
 */
function verificarSenha($senha, $hash) {
    return password_verify($senha, $hash);
}

/**
 * Gera token aleatório seguro
 * @param int $length Tamanho do token
 * @return string Token gerado
 */
function gerarToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Gera token de recuperação de senha
 * @return string Token de 64 caracteres
 */
function gerarTokenRecuperacao() {
    return gerarToken(32);
}

// =====================================================
// FUNÇÕES DE SESSÃO
// =====================================================

/**
 * Faz login do usuário (cria sessão)
 * @param array $usuario Dados do usuário
 */
function fazerLogin($usuario) {
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome_completo'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_logado'] = true;
    $_SESSION['login_time'] = time();
    
    // Atualizar último acesso no banco
    $query = "UPDATE usuarios SET ultimo_acesso = CURRENT_TIMESTAMP WHERE id = $1";
    executarQueryPreparada($query, [$usuario['id']]);
}

/**
 * Faz logout do usuário (destroi sessão)
 */
function fazerLogout() {
    session_unset();
    session_destroy();
}

/**
 * Verifica se o usuário está logado
 * @return bool True se logado
 */
function estaLogado() {
    return isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true;
}

/**
 * Redireciona se não estiver logado
 * @param string $url URL para redirecionar
 */
function requerLogin($url = '../auth/login.html') {
    if (!estaLogado()) {
        header("Location: $url");
        exit;
    }
}

/**
 * Obtém ID do usuário logado
 * @return int|null ID do usuário ou null
 */
function obterUsuarioId() {
    return $_SESSION['usuario_id'] ?? null;
}

/**
 * Obtém dados do usuário logado
 * @return array|null Array com dados do usuário
 */
function obterUsuarioLogado() {
    if (!estaLogado()) {
        return null;
    }
    
    $id = obterUsuarioId();
    $query = "SELECT id, nome_completo, email, telefone, status FROM usuarios WHERE id = $1";
    $resultado = executarQueryPreparada($query, [$id]);
    
    if ($resultado && pg_num_rows($resultado) > 0) {
        return pg_fetch_assoc($resultado);
    }
    
    return null;
}

// =====================================================
// FUNÇÕES DE BANCO DE DADOS
// =====================================================

/**
 * Busca usuário por email
 * @param string $email Email do usuário
 * @return array|null Dados do usuário ou null
 */
function buscarUsuarioPorEmail($email) {
    $query = "SELECT * FROM usuarios WHERE email = $1 LIMIT 1";
    $resultado = executarQueryPreparada($query, [$email]);
    
    if ($resultado && pg_num_rows($resultado) > 0) {
        return pg_fetch_assoc($resultado);
    }
    
    return null;
}

/**
 * Busca usuário por ID
 * @param int $id ID do usuário
 * @return array|null Dados do usuário ou null
 */
function buscarUsuarioPorId($id) {
    $query = "SELECT * FROM usuarios WHERE id = $1 LIMIT 1";
    $resultado = executarQueryPreparada($query, [$id]);
    
    if ($resultado && pg_num_rows($resultado) > 0) {
        return pg_fetch_assoc($resultado);
    }
    
    return null;
}

/**
 * Verifica se email já está cadastrado
 * @param string $email Email a verificar
 * @return bool True se já existe
 */
function emailJaCadastrado($email) {
    return buscarUsuarioPorEmail($email) !== null;
}

/**
 * Verifica se CPF já está cadastrado
 * @param string $cpf CPF a verificar
 * @return bool True se já existe
 */
function cpfJaCadastrado($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    $query = "SELECT id FROM usuarios WHERE cpf = $1 LIMIT 1";
    $resultado = executarQueryPreparada($query, [$cpf]);
    
    return $resultado && pg_num_rows($resultado) > 0;
}

// =====================================================
// FUNÇÕES DE RESPOSTA JSON
// =====================================================

/**
 * Retorna resposta JSON de sucesso
 * @param string $mensagem Mensagem de sucesso
 * @param array $dados Dados adicionais
 */
function respostaSucesso($mensagem, $dados = []) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'sucesso' => true,
        'mensagem' => $mensagem,
        'dados' => $dados
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Retorna resposta JSON de erro
 * @param string $mensagem Mensagem de erro
 * @param int $codigo Código HTTP do erro
 */
function respostaErro($mensagem, $codigo = 400) {
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $mensagem
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// =====================================================
// FUNÇÕES UTILITÁRIAS
// =====================================================

/**
 * Obtém IP do cliente
 * @return string IP do cliente
 */
function obterIPCliente() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }
}

/**
 * Formata data para padrão brasileiro
 * @param string $data Data no formato do banco
 * @return string Data formatada (dd/mm/yyyy)
 */
function formatarData($data) {
    if (empty($data)) return '';
    $timestamp = strtotime($data);
    return date('d/m/Y', $timestamp);
}

/**
 * Formata data e hora para padrão brasileiro
 * @param string $data Data e hora no formato do banco
 * @return string Data e hora formatada (dd/mm/yyyy HH:mm)
 */
function formatarDataHora($data) {
    if (empty($data)) return '';
    $timestamp = strtotime($data);
    return date('d/m/Y H:i', $timestamp);
}

?>