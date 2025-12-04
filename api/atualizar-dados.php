<?php
/**
 * =====================================================
 * API ATUALIZAR DADOS - IZI STYLE E-COMMERCE
 * =====================================================
 * Atualiza dados pessoais do usuário logado
 * Arquivo: api/atualizar-dados.php
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
    $nomeCompleto = limparString($dados['nome_completo']);
    $email = limparString($dados['email']);
    $telefone = preg_replace('/[^0-9]/', '', $dados['telefone']);
    $cpf = !empty($dados['cpf']) ? preg_replace('/[^0-9]/', '', $dados['cpf']) : null;
    
    // Validações
    if (empty($nomeCompleto) || empty($email) || empty($telefone)) {
        respostaErro('Nome, email e telefone são obrigatórios.');
    }
    
    if (!validarEmail($email)) {
        respostaErro('E-mail inválido.');
    }
    
    if (!validarTelefone($telefone)) {
        respostaErro('Telefone inválido.');
    }
    
    if ($cpf && !validarCPF($cpf)) {
        respostaErro('CPF inválido.');
    }
    
    // Buscar email/CPF atual do usuário
    $usuarioAtual = buscarUsuarioPorId($usuarioId);
    $emailMudou = ($usuarioAtual['email'] !== $email);
    
    // Verificar se novo email já está em uso por outro usuário
    if ($emailMudou && emailJaCadastrado($email)) {
        // Verificar se não é o próprio usuário
        $usuarioEmail = buscarUsuarioPorEmail($email);
        if ($usuarioEmail && $usuarioEmail['id'] != $usuarioId) {
            respostaErro('Este e-mail já está em uso por outra conta.');
        }
    }
    
    // Verificar se CPF já está em uso (se fornecido)
    if ($cpf && cpfJaCadastrado($cpf)) {
        $usuarioCPF = pg_fetch_assoc(executarQueryPreparada(
            "SELECT id FROM usuarios WHERE cpf = $1 LIMIT 1",
            [$cpf]
        ));
        if ($usuarioCPF && $usuarioCPF['id'] != $usuarioId) {
            respostaErro('Este CPF já está em uso por outra conta.');
        }
    }
    
    // Se email mudou, marcar como não verificado
    $emailVerificado = $emailMudou ? 'f' : null;
    
    // Atualizar no banco
    $query = "
        UPDATE usuarios 
        SET nome_completo = $1,
            email = $2,
            telefone = $3,
            cpf = $4,
            data_atualizacao = CURRENT_TIMESTAMP
    ";
    
    $params = [$nomeCompleto, $email, $telefone, $cpf];
    
    // Se email mudou, atualizar também o campo email_verificado
    if ($emailMudou) {
        $query .= ", email_verificado = $5 WHERE id = $6";
        $params[] = 'f';
        $params[] = $usuarioId;
    } else {
        $query .= " WHERE id = $5";
        $params[] = $usuarioId;
    }
    
    $resultado = executarQueryPreparada($query, $params);
    
    if (!$resultado) {
        respostaErro('Erro ao atualizar dados.', 500);
    }
    
    // Se email mudou, enviar email de verificação
    if ($emailMudou) {
        // Gerar novo token de verificação
        $tokenVerificacao = gerarToken();
        
        $queryToken = "UPDATE usuarios SET token_recuperacao = $1 WHERE id = $2";
        executarQueryPreparada($queryToken, [$tokenVerificacao, $usuarioId]);
        
        // Enviar email (simulado em desenvolvimento)
        error_log("=== EMAIL DE VERIFICAÇÃO ===");
        error_log("Novo email: $email");
        error_log("Link: http://" . $_SERVER['HTTP_HOST'] . "/auth/verificar-email.html?token=$tokenVerificacao");
        error_log("============================");
    }
    
    $mensagem = $emailMudou ? 
        'Dados atualizados! Enviamos um email de verificação para o novo endereço.' :
        'Dados atualizados com sucesso!';
    
    respostaSucesso($mensagem, [
        'email_mudou' => $emailMudou
    ]);
    
} catch (Exception $e) {
    // Log do erro
    error_log('Erro ao atualizar dados: ' . $e->getMessage());
    
    // Retornar erro genérico
    respostaErro('Erro ao atualizar dados. Tente novamente.', 500);
}
?>