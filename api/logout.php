<?php
/**
 * =====================================================
 * API DE LOGOUT - IZI STYLE E-COMMERCE
 * =====================================================
 * Destroi sessão do usuário e faz logout
 * Arquivo: api/logout.php
 * =====================================================
 */

// Headers para permitir CORS e JSON
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir arquivos necessários
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/funcoes.php';

try {
    // Fazer logout
    fazerLogout();
    
    // Retornar sucesso
    respostaSucesso('Logout realizado com sucesso!');
    
} catch (Exception $e) {
    // Log do erro
    error_log('Erro no logout: ' . $e->getMessage());
    
    // Retornar erro genérico
    respostaErro('Erro ao fazer logout. Tente novamente.', 500);
}
?>