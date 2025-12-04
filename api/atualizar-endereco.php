<?php
/**
 * =====================================================
 * API ATUALIZAR ENDEREÇO - IZI STYLE E-COMMERCE
 * =====================================================
 * Atualiza endereço do usuário logado
 * Arquivo: api/atualizar-endereco.php
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
    
    // Sanitizar dados
    $cep = preg_replace('/[^0-9]/', '', $dados['cep'] ?? '');
    $endereco = limparString($dados['endereco'] ?? '');
    $numero = limparString($dados['numero'] ?? '');
    $complemento = limparString($dados['complemento'] ?? '');
    $bairro = limparString($dados['bairro'] ?? '');
    $cidade = limparString($dados['cidade'] ?? '');
    $estado = strtoupper(limparString($dados['estado'] ?? ''));
    
    // Validações básicas
    if ($cep && strlen($cep) !== 8) {
        respostaErro('CEP inválido.');
    }
    
    if ($estado && strlen($estado) !== 2) {
        respostaErro('Estado deve ter 2 caracteres (ex: SP).');
    }
    
    // Atualizar no banco
    $query = "
        UPDATE usuarios 
        SET cep = $1,
            endereco = $2,
            numero = $3,
            complemento = $4,
            bairro = $5,
            cidade = $6,
            estado = $7,
            data_atualizacao = CURRENT_TIMESTAMP
        WHERE id = $8
    ";
    
    $params = [
        $cep ?: null,
        $endereco ?: null,
        $numero ?: null,
        $complemento ?: null,
        $bairro ?: null,
        $cidade ?: null,
        $estado ?: null,
        $usuarioId
    ];
    
    $resultado = executarQueryPreparada($query, $params);
    
    if (!$resultado) {
        respostaErro('Erro ao atualizar endereço.', 500);
    }
    
    respostaSucesso('Endereço atualizado com sucesso!');
    
} catch (Exception $e) {
    // Log do erro
    error_log('Erro ao atualizar endereço: ' . $e->getMessage());
    
    // Retornar erro genérico
    respostaErro('Erro ao atualizar endereço. Tente novamente.', 500);
}
?>