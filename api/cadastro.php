<?php
/**
 * =====================================================
 * API DE CADASTRO - IZI STYLE E-COMMERCE
 * =====================================================
 * Processa registro de novos usuários
 * Arquivo: api/cadastro.php
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
    if (empty($dados['nome_completo']) || empty($dados['email']) || 
        empty($dados['telefone']) || empty($dados['senha'])) {
        respostaErro('Todos os campos obrigatórios devem ser preenchidos.');
    }
    
    // Sanitizar dados
    $nomeCompleto = limparString($dados['nome_completo']);
    $email = limparString($dados['email']);
    $telefone = preg_replace('/[^0-9]/', '', $dados['telefone']);
    $cpf = !empty($dados['cpf']) ? preg_replace('/[^0-9]/', '', $dados['cpf']) : null;
    $senha = $dados['senha'];
    $aceitaTermos = isset($dados['aceita_termos']) ? $dados['aceita_termos'] : false;
    $aceitaNewsletter = isset($dados['aceita_newsletter']) ? $dados['aceita_newsletter'] : false;
    
    // Validar nome
    if (strlen($nomeCompleto) < 3) {
        respostaErro('Nome deve ter pelo menos 3 caracteres.');
    }
    
    // Validar email
    if (!validarEmail($email)) {
        respostaErro('E-mail inválido.');
    }
    
    // Validar telefone
    if (!validarTelefone($telefone)) {
        respostaErro('Telefone inválido.');
    }
    
    // Validar CPF se fornecido
    if ($cpf && !validarCPF($cpf)) {
        respostaErro('CPF inválido.');
    }
    
    // Validar senha
    if (!validarSenha($senha)) {
        respostaErro('Senha deve ter no mínimo 6 caracteres.');
    }
    
    // Validar aceite de termos
    if (!$aceitaTermos) {
        respostaErro('Você deve aceitar os termos de uso.');
    }
    
    // Verificar se email já está cadastrado
    if (emailJaCadastrado($email)) {
        respostaErro('Este e-mail já está cadastrado.');
    }
    
    // Verificar se CPF já está cadastrado (se fornecido)
    if ($cpf && cpfJaCadastrado($cpf)) {
        respostaErro('Este CPF já está cadastrado.');
    }
    
    // Gerar hash da senha
    $senhaHash = hashSenha($senha);
    
    // Obter IP do cliente
    $ipCadastro = obterIPCliente();
    
    // Preparar query de inserção
    $query = "
        INSERT INTO usuarios (
            nome_completo,
            email,
            telefone,
            cpf,
            senha,
            status,
            email_verificado,
            aceita_newsletter,
            aceita_termos,
            ip_cadastro,
            data_cadastro
        ) VALUES (
            $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, CURRENT_TIMESTAMP
        )
        RETURNING id, nome_completo, email
    ";
    
    // Converter booleanos para formato PostgreSQL
    $statusAtivo = 't'; // ou 'true'
    $emailVerificado = 'f'; // ou 'false'
    $newsletterBool = $aceitaNewsletter ? 't' : 'f';
    $termosBool = $aceitaTermos ? 't' : 'f';
    
    $params = [
        $nomeCompleto,
        $email,
        $telefone,
        $cpf,
        $senhaHash,
        $statusAtivo, // status ativo
        $emailVerificado, // email ainda não verificado
        $newsletterBool,
        $termosBool,
        $ipCadastro
    ];
    
    // Executar inserção
    $resultado = executarQueryPreparada($query, $params);
    
    if (!$resultado) {
        respostaErro('Erro ao criar conta. Tente novamente mais tarde.', 500);
    }
    
    // Pegar dados do usuário criado
    $usuarioCriado = pg_fetch_assoc($resultado);
    
    if (!$usuarioCriado) {
        respostaErro('Erro ao recuperar dados do usuário criado.', 500);
    }
    
    // Fazer login automaticamente após cadastro
    $usuarioCompleto = buscarUsuarioPorId($usuarioCriado['id']);
    if ($usuarioCompleto) {
        fazerLogin($usuarioCompleto);
    }
    
    // Retornar sucesso
    respostaSucesso('Cadastro realizado com sucesso!', [
        'usuario_id' => $usuarioCriado['id'],
        'nome' => $usuarioCriado['nome_completo'],
        'email' => $usuarioCriado['email'],
        'mensagem_adicional' => 'Bem-vindo(a) à IZI STYLE! Use o cupom IZISTYLE10 para 10% de desconto na primeira compra.'
    ]);
    
} catch (Exception $e) {
    // Log do erro
    error_log('Erro no cadastro: ' . $e->getMessage());
    
    // Retornar erro genérico
    respostaErro('Erro ao processar cadastro. Tente novamente mais tarde.', 500);
}
?>