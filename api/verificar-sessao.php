<?php
/**
 * =====================================================
 * API DE VERIFICAÇÃO DE SESSÃO - IZI STYLE E-COMMERCE
 * =====================================================
 * Verifica se usuário está logado e retorna dados
 * Arquivo: api/verificar-sessao.php
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
    // Verificar se usuário está logado
    if (estaLogado()) {
        // Pegar dados do usuário logado
        $usuario = obterUsuarioLogado();
        
        if ($usuario) {
            // Retornar sucesso com dados do usuário
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'sucesso' => true,
                'logado' => true,
                'usuario' => [
                    'id' => $usuario['id'],
                    'nome' => $usuario['nome_completo'],
                    'email' => $usuario['email']
                ]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            // Sessão existe mas usuário não foi encontrado no banco
            fazerLogout();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'sucesso' => true,
                'logado' => false
            ], JSON_UNESCAPED_UNICODE);
        }
    } else {
        // Usuário não está logado
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'sucesso' => true,
            'logado' => false
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    // Log do erro
    error_log('Erro ao verificar sessão: ' . $e->getMessage());
    
    // Retornar erro
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'sucesso' => false,
        'logado' => false,
        'mensagem' => 'Erro ao verificar sessão.'
    ], JSON_UNESCAPED_UNICODE);
}
?>