<?php
/**
 * =====================================================
 * API OBTER DADOS DO USUÁRIO - IZI STYLE E-COMMERCE
 * =====================================================
 * Retorna dados completos do usuário logado
 * Arquivo: api/obter-dados-usuario.php
 * =====================================================
 */

// Limpar qualquer output anterior
ob_start();

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Headers para permitir CORS e JSON
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir arquivos necessários
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/funcoes.php';

// Limpar buffer
ob_clean();

try {
    // Verificar se está logado
    if (!estaLogado()) {
        http_response_code(401);
        die(json_encode([
            'sucesso' => false,
            'mensagem' => 'Usuário não autenticado.'
        ], JSON_UNESCAPED_UNICODE));
    }
    
    // Pegar ID do usuário logado
    $usuarioId = obterUsuarioId();
    
    if (!$usuarioId) {
        http_response_code(401);
        die(json_encode([
            'sucesso' => false,
            'mensagem' => 'ID do usuário não encontrado na sessão.'
        ], JSON_UNESCAPED_UNICODE));
    }
    
    // Buscar dados completos no banco
    $query = "
        SELECT 
            id,
            uuid,
            nome_completo,
            email,
            telefone,
            cpf,
            cep,
            endereco,
            numero,
            complemento,
            bairro,
            cidade,
            estado,
            status,
            email_verificado,
            aceita_newsletter,
            aceita_termos,
            data_cadastro,
            data_atualizacao,
            ultimo_acesso,
            ip_cadastro
        FROM usuarios 
        WHERE id = $1
        LIMIT 1
    ";
    
    // Executar query
    $resultado = @pg_query_params($conexao, $query, [$usuarioId]);
    
    if (!$resultado) {
        $erro = pg_last_error($conexao);
        error_log('Erro na query: ' . $erro);
        http_response_code(500);
        die(json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro ao buscar dados no banco.'
        ], JSON_UNESCAPED_UNICODE));
    }
    
    if (pg_num_rows($resultado) === 0) {
        http_response_code(404);
        die(json_encode([
            'sucesso' => false,
            'mensagem' => 'Usuário não encontrado.'
        ], JSON_UNESCAPED_UNICODE));
    }
    
    $usuario = pg_fetch_assoc($resultado);
    
    // Garantir que todos os campos existem
    $usuario['nome_completo'] = $usuario['nome_completo'] ?? '';
    $usuario['email'] = $usuario['email'] ?? '';
    $usuario['telefone'] = $usuario['telefone'] ?? '';
    $usuario['cpf'] = $usuario['cpf'] ?? '';
    
    // Retornar dados (sem senha!)
    http_response_code(200);
    die(json_encode([
        'sucesso' => true,
        'mensagem' => 'Dados carregados com sucesso.',
        'usuario' => $usuario
    ], JSON_UNESCAPED_UNICODE));
    
} catch (Exception $e) {
    // Log do erro
    error_log('Erro ao obter dados do usuário: ' . $e->getMessage());
    error_log('Linha: ' . $e->getLine());
    error_log('Arquivo: ' . $e->getFile());
    
    // Limpar buffer antes de retornar erro
    ob_clean();
    
    // Retornar erro genérico
    http_response_code(500);
    die(json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao carregar dados do usuário.'
    ], JSON_UNESCAPED_UNICODE));
}
?>