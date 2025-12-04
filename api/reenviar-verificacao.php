<?php
/**
 * =====================================================
 * API REENVIAR VERIFICAÇÃO - IZI STYLE E-COMMERCE
 * =====================================================
 * Reenvia email de verificação para o usuário
 * Arquivo: api/reenviar-verificacao.php
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
    
    // Buscar usuário
    $usuario = buscarUsuarioPorId($usuarioId);
    
    if (!$usuario) {
        respostaErro('Usuário não encontrado.', 404);
    }
    
    // Verificar se email já está verificado
    if ($usuario['email_verificado'] === 't' || $usuario['email_verificado'] === true) {
        respostaErro('Seu email já está verificado!');
    }
    
    // Gerar novo token de verificação
    $tokenVerificacao = gerarToken();
    
    // Salvar token no banco (usando campo token_recuperacao)
    $query = "UPDATE usuarios SET token_recuperacao = $1 WHERE id = $2";
    $resultado = executarQueryPreparada($query, [$tokenVerificacao, $usuarioId]);
    
    if (!$resultado) {
        respostaErro('Erro ao gerar token de verificação.', 500);
    }
    
    // Link de verificação
    $linkVerificacao = "http://" . $_SERVER['HTTP_HOST'] . "/auth/verificar-email.html?token=" . $tokenVerificacao;
    
    // =====================================================
    // SIMULAÇÃO DE ENVIO DE EMAIL
    // =====================================================
    
    // Log do link (em produção, remover!)
    error_log("=== EMAIL DE VERIFICAÇÃO ===");
    error_log("Usuário: " . $usuario['nome_completo']);
    error_log("Email: " . $usuario['email']);
    error_log("Link: " . $linkVerificacao);
    error_log("============================");
    
    // Simular email HTML
    $emailHTML = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #f70a31; color: white; padding: 30px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
            .button { display: inline-block; padding: 15px 30px; background: #f70a31; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { padding: 20px; text-align: center; font-size: 0.9em; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>IZI STYLE</h1>
                <p>Verificação de Email</p>
            </div>
            <div class='content'>
                <h2>Olá, {$usuario['nome_completo']}!</h2>
                <p>Clique no botão abaixo para verificar seu email:</p>
                <p style='text-align: center;'>
                    <a href='{$linkVerificacao}' class='button'>Verificar Email</a>
                </p>
                <p><strong>Ou copie e cole este link no seu navegador:</strong></p>
                <p style='word-break: break-all; background: white; padding: 10px; border-radius: 5px;'>{$linkVerificacao}</p>
                <p>Este link é válido e não expira.</p>
            </div>
            <div class='footer'>
                <p>IZI STYLE - Criação Personalizada</p>
                <p>contato@izistyle.com.br | (11) 9 7623-1926</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Salvar email em arquivo (para desenvolvimento)
    $emailFile = __DIR__ . '/../temp/email-verificacao-' . time() . '.html';
    @mkdir(__DIR__ . '/../temp', 0777, true);
    @file_put_contents($emailFile, $emailHTML);
    
    // =====================================================
    // FIM DA SIMULAÇÃO
    // =====================================================
    
    // Retornar sucesso
    respostaSucesso(
        'Email de verificação enviado com sucesso! Verifique sua caixa de entrada.',
        [
            'link_desenvolvimento' => $linkVerificacao // Remover em produção!
        ]
    );
    
} catch (Exception $e) {
    // Log do erro
    error_log('Erro ao reenviar verificação: ' . $e->getMessage());
    
    // Retornar erro genérico
    respostaErro('Erro ao enviar email de verificação. Tente novamente.', 500);
}
?>