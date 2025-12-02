<?php
/**
 * DEBUG LOGIN - IZI STYLE
 * Teste direto da função de login
 */

require_once 'config/conexao.php';
require_once 'includes/funcoes.php';

echo "<h1>🔍 DEBUG - Sistema de Login</h1>";
echo "<hr>";

// Teste 1: Conexão
echo "<h2>1. Teste de Conexão</h2>";
if ($conexao) {
    echo "✅ Conectado ao banco!<br>";
} else {
    echo "❌ Erro na conexão!<br>";
    die();
}

// Teste 2: Buscar usuário
echo "<h2>2. Buscar Usuário de Teste</h2>";
$email = 'teste@izistyle.com.br';
echo "Buscando: $email<br>";

$usuario = buscarUsuarioPorEmail($email);

if ($usuario) {
    echo "✅ Usuário encontrado!<br>";
    echo "<pre>";
    echo "ID: " . $usuario['id'] . "\n";
    echo "Nome: " . $usuario['nome_completo'] . "\n";
    echo "Email: " . $usuario['email'] . "\n";
    echo "Status: " . var_export($usuario['status'], true) . "\n";
    echo "Status (type): " . gettype($usuario['status']) . "\n";
    echo "Senha (hash): " . substr($usuario['senha'], 0, 20) . "...\n";
    echo "</pre>";
} else {
    echo "❌ Usuário NÃO encontrado!<br>";
    die();
}

// Teste 3: Verificar status
echo "<h2>3. Verificar Status</h2>";
echo "Status do banco: " . var_export($usuario['status'], true) . "<br>";

// Testar diferentes comparações
$statusComparacoes = [
    'status === true' => ($usuario['status'] === true),
    'status === "t"' => ($usuario['status'] === 't'),
    'status === "1"' => ($usuario['status'] === '1'),
    'status == true' => ($usuario['status'] == true),
    'status !== false' => ($usuario['status'] !== false),
];

echo "<pre>";
foreach ($statusComparacoes as $teste => $resultado) {
    echo "$teste: " . ($resultado ? '✅ TRUE' : '❌ FALSE') . "\n";
}
echo "</pre>";

// Teste 4: Verificar senha
echo "<h2>4. Teste de Senha</h2>";
$senhaTeste = 'teste123';
echo "Senha testada: $senhaTeste<br>";
echo "Hash no banco: " . substr($usuario['senha'], 0, 30) . "...<br>";

if (verificarSenha($senhaTeste, $usuario['senha'])) {
    echo "✅ Senha CORRETA!<br>";
} else {
    echo "❌ Senha INCORRETA!<br>";
    
    // Testar se o hash está correto
    echo "<br><strong>Testando hash correto:</strong><br>";
    $hashCorreto = password_hash($senhaTeste, PASSWORD_DEFAULT);
    echo "Hash gerado agora: " . substr($hashCorreto, 0, 30) . "...<br>";
    
    if (password_verify($senhaTeste, $hashCorreto)) {
        echo "✅ Função password_verify() está funcionando<br>";
    } else {
        echo "❌ Problema com password_verify()<br>";
    }
}

// Teste 5: Simulação completa de login
echo "<h2>5. Simulação de Login</h2>";

$loginDados = [
    'email' => 'teste@izistyle.com.br',
    'senha' => 'teste123'
];

echo "Dados de entrada:<br>";
echo "Email: " . $loginDados['email'] . "<br>";
echo "Senha: " . $loginDados['senha'] . "<br><br>";

// Buscar
$usuarioLogin = buscarUsuarioPorEmail($loginDados['email']);
if (!$usuarioLogin) {
    echo "❌ FALHA: Usuário não encontrado<br>";
} else {
    echo "✅ Usuário encontrado<br>";
    
    // Verificar status
    if ($usuarioLogin['status'] !== 't' && $usuarioLogin['status'] !== true && $usuarioLogin['status'] !== '1') {
        echo "❌ FALHA: Conta inativa (status = " . var_export($usuarioLogin['status'], true) . ")<br>";
    } else {
        echo "✅ Conta ativa<br>";
        
        // Verificar senha
        if (!verificarSenha($loginDados['senha'], $usuarioLogin['senha'])) {
            echo "❌ FALHA: Senha incorreta<br>";
        } else {
            echo "✅ Senha correta<br>";
            echo "<br><strong>🎉 LOGIN DEVERIA FUNCIONAR!</strong><br>";
        }
    }
}

echo "<hr>";
echo "<p><a href='auth/login.html'>← Voltar para Login</a></p>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
    background: #f5f5f5;
}
h1 {
    color: #f70a31;
}
h2 {
    color: #333;
    border-bottom: 2px solid #f70a31;
    padding-bottom: 10px;
    margin-top: 30px;
}
pre {
    background: #fff;
    padding: 15px;
    border-left: 4px solid #f70a31;
    overflow-x: auto;
}
</style>