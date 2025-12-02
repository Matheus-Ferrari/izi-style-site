<?php
/**
 * =====================================================
 * API DE LOGIN - IZI STYLE E-COMMERCE
 * =====================================================
 * Processa autenticação de usuários
 * Arquivo: api/login.php
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
    // Pegar dados do POST
    $inputJSON = file_get_contents('php://input');
    $dados = json_decode($inputJSON, true);
    
    // Verificar se os dados foram enviados
    if (!$dados) {
        respostaErro('Dados inválidos.');
    }
    
    // Validar campos obrigatórios
    if (empty($dados['email']) || empty($dados['senha'])) {
        respostaErro('E-mail e senha são obrigatórios.');
    }
    
    $email = limparString($dados['email']);
    $senha = $dados['senha'];
    
    // Validar formato do email
    if (!validarEmail($email)) {
        respostaErro('E-mail inválido.');
    }
    
    // Validar tamanho da senha
    if (strlen($senha) < 6) {
        respostaErro('Senha deve ter no mínimo 6 caracteres.');
    }
    
    // Buscar usuário no banco
    $usuario = buscarUsuarioPorEmail($email);
    
    if (!$usuario) {
        respostaErro('E-mail ou senha incorretos.');
    }
    
    // Verificar se a conta está ativa
    if ($usuario['status'] !== 't' && $usuario['status'] !== true && $usuario['status'] !== '1') {
        respostaErro('Conta inativa. Entre em contato com o suporte.');
    }
    
    // Verificar senha
    if (!verificarSenha($senha, $usuario['senha'])) {
        respostaErro('E-mail ou senha incorretos.');
    }
    
    // Login bem-sucedido - Criar sessão
    fazerLogin($usuario);
    
    // Retornar sucesso
    respostaSucesso('Login realizado com sucesso!', [
        'usuario_id' => $usuario['id'],
        'nome' => $usuario['nome_completo'],
        'email' => $usuario['email']
    ]);
    
} catch (Exception $e) {
    // Log do erro (em produção, use um sistema de logs adequado)
    error_log('Erro no login: ' . $e->getMessage());
    
    // Retornar erro genérico
    respostaErro('Erro ao processar login. Tente novamente mais tarde.', 500);
}
?>