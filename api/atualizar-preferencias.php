<?php
/**
 * =====================================================
 * API ATUALIZAR PREFERÊNCIAS - IZI STYLE E-COMMERCE
 * =====================================================
 * Atualiza preferências do usuário (newsletter, notificações)
 * Arquivo: api/atualizar-preferencias.php
 * =====================================================
 */

// Headers para permitir CORS e JSON
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir arquivos necessários
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/funcoes.php';

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respostaErro('Método não permitido. Use POST.', 405);
}

try {
    // Verificar se está logado
    if (!estaLogado()) {
        http_response_code(401);
        respostaErro('Usuário não autenticado.', 401);
    }
    
    // Pegar ID do usuário logado
    $usuarioId = obterUsuarioId();
    
    // Pegar dados do POST
    $inputJSON = file_get_contents('php://input');
    $dados = json_decode($inputJSON, true);
    
    if (!$dados) {
        respostaErro('Dados inválidos.');
    }
    
    // Converter booleanos para formato PostgreSQL
    $aceitaNewsletter = isset($dados['aceita_newsletter']) && $dados['aceita_newsletter'] ? 't' : 'f';
    
    // Atualizar no banco
    $query = "
        UPDATE usuarios 
        SET aceita_newsletter = $1,
            data_atualizacao = CURRENT_TIMESTAMP
        WHERE id = $2
    ";
    
    $resultado = executarQueryPreparada($query, [$aceitaNewsletter, $usuarioId]);
    
    if (!$resultado) {
        respostaErro('Erro ao atualizar preferências.', 500);
    }
    
    respostaSucesso('Preferências atualizadas com sucesso!');
    
} catch (Exception $e) {
    // Log do erro
    error_log('Erro ao atualizar preferências: ' . $e->getMessage());
    
    // Retornar erro genérico
    respostaErro('Erro ao atualizar preferências. Tente novamente.', 500);
}
?>