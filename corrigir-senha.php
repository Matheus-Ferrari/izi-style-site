<?php
/**
 * CORRIGIR SENHA DO USUÁRIO DE TESTE
 * Gera hash correto e atualiza no banco
 */

require_once 'config/conexao.php';
require_once 'includes/funcoes.php';

echo "<h1>🔧 Corrigir Senha do Usuário de Teste</h1>";
echo "<hr>";

// Senha que queremos
$senhaDesejada = 'teste123';
$emailUsuario = 'teste@izistyle.com.br';

echo "<h2>📋 Informações</h2>";
echo "Email: <strong>$emailUsuario</strong><br>";
echo "Senha desejada: <strong>$senhaDesejada</strong><br>";
echo "<hr>";

// Gerar hash correto
echo "<h2>🔐 Gerando Hash Correto</h2>";
$hashCorreto = password_hash($senhaDesejada, PASSWORD_DEFAULT);
echo "Hash gerado: <code>$hashCorreto</code><br><br>";

// Testar se o hash funciona
if (password_verify($senhaDesejada, $hashCorreto)) {
    echo "✅ Hash testado e validado!<br>";
} else {
    echo "❌ Erro ao validar hash!<br>";
    die();
}

echo "<hr>";

// Atualizar no banco
echo "<h2>💾 Atualizando no Banco de Dados</h2>";

$query = "UPDATE usuarios SET senha = $1 WHERE email = $2";
$resultado = executarQueryPreparada($query, [$hashCorreto, $emailUsuario]);

if ($resultado) {
    echo "✅ <strong>Senha atualizada com sucesso!</strong><br><br>";
    
    // Verificar se funcionou
    echo "<h2>🧪 Testando Login Atualizado</h2>";
    
    $usuario = buscarUsuarioPorEmail($emailUsuario);
    
    if ($usuario && verificarSenha($senhaDesejada, $usuario['senha'])) {
        echo "✅ <strong>LOGIN FUNCIONANDO PERFEITAMENTE!</strong><br><br>";
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745;'>";
        echo "<h3>🎉 Tudo Pronto!</h3>";
        echo "<p><strong>Credenciais atualizadas:</strong></p>";
        echo "<p>Email: <strong>$emailUsuario</strong></p>";
        echo "<p>Senha: <strong>$senhaDesejada</strong></p>";
        echo "<p><a href='auth/login.html' style='color: #f70a31; font-weight: bold;'>→ Ir para página de login</a></p>";
        echo "</div>";
    } else {
        echo "❌ Ainda há problema na verificação<br>";
    }
} else {
    echo "❌ Erro ao atualizar senha no banco<br>";
    echo "Erro: " . obterErro() . "<br>";
}

echo "<hr>";
echo "<h2>📊 SQL para Executar Manualmente (se necessário)</h2>";
echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
echo "UPDATE usuarios \n";
echo "SET senha = '$hashCorreto' \n";
echo "WHERE email = '$emailUsuario';";
echo "</pre>";

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
code {
    background: #fff;
    padding: 5px 10px;
    border-radius: 4px;
    border: 1px solid #ddd;
    font-size: 0.9em;
    display: inline-block;
    word-break: break-all;
}
pre {
    background: #fff;
    padding: 15px;
    border-left: 4px solid #f70a31;
    overflow-x: auto;
    border-radius: 5px;
}
</style>