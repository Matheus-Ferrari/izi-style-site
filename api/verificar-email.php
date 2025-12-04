<?php
/**
 * =====================================================
 * API VERIFICAR EMAIL - IZI STYLE E-COMMERCE
 * =====================================================
 * Processa verificação de email via token
 * Arquivo: api/verificar-email.php
 * =====================================================
 */

// Headers para permitir CORS e JSON
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir arquivos necessários
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/funcoes.php';

try {
    // Pegar token da URL
    $token = isset($_GET['token']) ? limparString($_GET['token']) : '';
    
    if (empty($token)) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Token não fornecido.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Buscar usuário pelo token (usando campo token_recuperacao)
    $query = "
        SELECT id, nome_completo, email, email_verificado, status
        FROM usuarios 
        WHERE token_recuperacao = $1
        LIMIT 1
    ";
    
    $resultado = executarQueryPreparada($query, [$token]);
    
    if (!$resultado || pg_num_rows($resultado) === 0) {
        // Token não encontrado
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Token inválido ou expirado.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $usuario = pg_fetch_assoc($resultado);
    
    // Verificar se email já está verificado
    if ($usuario['email_verificado'] === 't' || $usuario['email_verificado'] === true) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Este email já foi verificado anteriormente.',
            'ja_verificado' => true
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Verificar se conta está ativa
    if ($usuario['status'] !== 't' && $usuario['status'] !== true && $usuario['status'] !== '1') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Conta inativa. Entre em contato com o suporte.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Marcar email como verificado e limpar token
    $queryUpdate = "
        UPDATE usuarios 
        SET email_verificado = 't',
            token_recuperacao = NULL,
            data_atualizacao = CURRENT_TIMESTAMP
        WHERE id = $1
    ";
    
    $resultadoUpdate = executarQueryPreparada($queryUpdate, [$usuario['id']]);
    
    if (!$resultadoUpdate) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro ao verificar email. Tente novamente.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Log de sucesso
    error_log("Email verificado com sucesso: " . $usuario['email']);
    
    // Sucesso!
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Email verificado com sucesso!',
        'email' => $usuario['email']
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Log do erro
    error_log('Erro ao verificar email: ' . $e->getMessage());
    
    // Retornar erro
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao verificar email.'
    ], JSON_UNESCAPED_UNICODE);
}
?>