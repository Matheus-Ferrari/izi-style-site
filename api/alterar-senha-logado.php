<?php
/**
 * =====================================================
 * API ALTERAR SENHA LOGADO - IZI STYLE E-COMMERCE
 * =====================================================
 * Permite usuário logado alterar sua senha
 * Arquivo: api/alterar-senha-logado.php
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
    
    // Validar campos obrigatórios
    if (empty($dados['senha_atual']) || empty($dados['nova_senha'])) {
        respostaErro('Senha atual e nova senha são obrigatórias.');
    }
    
    $senhaAtual = $dados['senha_atual'];
    $novaSenha = $dados['nova_senha'];
    
    // Validar nova senha
    if (!validarSenha($novaSenha)) {
        respostaErro('A nova senha deve ter no mínimo 6 caracteres.');
    }
    
    // Buscar usuário e verificar senha atual
    $usuario = buscarUsuarioPorId($usuarioId);
    
    if (!$usuario) {
        respostaErro('Usuário não encontrado.', 404);
    }
    
    // Verificar se senha atual está correta
    if (!verificarSenha($senhaAtual, $usuario['senha'])) {
        respostaErro('Senha atual incorreta.');
    }
    
    // Gerar hash da nova senha
    $novaSenhaHash = hashSenha($novaSenha);
    
    // Atualizar senha no banco
    $query = "
        UPDATE usuarios 
        SET senha = $1,
            data_atualizacao = CURRENT_TIMESTAMP
        WHERE id = $2
    ";
    
    $resultado = executarQueryPreparada($query, [$novaSenhaHash, $usuarioId]);
    
    if (!$resultado) {
        respostaErro('Erro ao alterar senha.', 500);
    }
    
    // Log de segurança
    error_log("Senha alterada pelo usuário logado: " . $usuario['email']);
    
    respostaSucesso('Senha alterada com sucesso!');
    
} catch (Exception $e) {
    // Log do erro
    error_log('Erro ao alterar senha: ' . $e->getMessage());
    
    // Retornar erro genérico
    respostaErro('Erro ao alterar senha. Tente novamente.', 500);
}
?>