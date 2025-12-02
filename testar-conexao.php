<?php
/**
 * =====================================================
 * TESTE DE CONEXÃO - IZI STYLE E-COMMERCE
 * =====================================================
 * Este arquivo testa a conexão com o banco de dados
 * Arquivo: testar-conexao.php
 * =====================================================
 */

// Incluir arquivos necessários
require_once 'config/conexao.php';
require_once 'includes/funcoes.php';

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Conexão - IZI STYLE</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 40px;
        }
        
        h1 {
            color: #f70a31;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .teste-item {
            background: #f9f9f9;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            border-left: 4px solid #ddd;
        }
        
        .teste-item.sucesso {
            border-left-color: #28a745;
            background: #d4edda;
        }
        
        .teste-item.erro {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        
        .teste-titulo {
            font-weight: bold;
            font-size: 1.1em;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .teste-titulo::before {
            content: "✓";
            display: inline-block;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            color: white;
            font-weight: bold;
        }
        
        .sucesso .teste-titulo::before {
            background: #28a745;
        }
        
        .erro .teste-titulo::before {
            content: "✗";
            background: #dc3545;
        }
        
        .teste-detalhe {
            color: #666;
            font-size: 0.95em;
            margin-top: 5px;
        }
        
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .info-box h3 {
            color: #004085;
            margin-bottom: 15px;
        }
        
        .info-box ul {
            list-style: none;
            padding-left: 0;
        }
        
        .info-box li {
            padding: 8px 0;
            border-bottom: 1px solid #b3d9ff;
        }
        
        .info-box li:last-child {
            border-bottom: none;
        }
        
        .info-box strong {
            color: #004085;
            display: inline-block;
            width: 150px;
        }
        
        .btn-voltar {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 30px;
            background: #f70a31;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .btn-voltar:hover {
            background: #c40827;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Teste de Conexão - IZI STYLE</h1>
        
        <?php
        // TESTE 1: Conexão com banco
        $conexaoOk = false;
        if ($conexao) {
            echo '<div class="teste-item sucesso">';
            echo '<div class="teste-titulo">Conexão com PostgreSQL</div>';
            echo '<div class="teste-detalhe">✓ Conectado com sucesso ao banco de dados!</div>';
            echo '</div>';
            $conexaoOk = true;
        } else {
            echo '<div class="teste-item erro">';
            echo '<div class="teste-titulo">Conexão com PostgreSQL</div>';
            echo '<div class="teste-detalhe">✗ Erro ao conectar: ' . obterErro() . '</div>';
            echo '</div>';
        }
        
        // TESTE 2: Tabela usuários
        if ($conexaoOk) {
            $query = "SELECT COUNT(*) as total FROM usuarios";
            $resultado = executarQuery($query);
            
            if ($resultado) {
                $row = pg_fetch_assoc($resultado);
                echo '<div class="teste-item sucesso">';
                echo '<div class="teste-titulo">Tabela de Usuários</div>';
                echo '<div class="teste-detalhe">✓ Tabela "usuarios" encontrada com ' . $row['total'] . ' registro(s)</div>';
                echo '</div>';
            } else {
                echo '<div class="teste-item erro">';
                echo '<div class="teste-titulo">Tabela de Usuários</div>';
                echo '<div class="teste-detalhe">✗ Erro ao acessar tabela "usuarios"</div>';
                echo '</div>';
            }
        }
        
        // TESTE 3: Funções auxiliares
        echo '<div class="teste-item sucesso">';
        echo '<div class="teste-titulo">Funções Auxiliares</div>';
        echo '<div class="teste-detalhe">✓ Arquivo funcoes.php carregado com sucesso</div>';
        echo '</div>';
        
        // TESTE 4: Validações
        $emailValido = validarEmail('teste@izistyle.com.br');
        $cpfValido = validarCPF('123.456.789-09');
        
        if ($emailValido) {
            echo '<div class="teste-item sucesso">';
            echo '<div class="teste-titulo">Validação de Email</div>';
            echo '<div class="teste-detalhe">✓ Função validarEmail() funcionando</div>';
            echo '</div>';
        }
        
        // TESTE 5: Segurança
        $senhaHash = hashSenha('teste123');
        $senhaVerificada = verificarSenha('teste123', $senhaHash);
        
        if ($senhaVerificada) {
            echo '<div class="teste-item sucesso">';
            echo '<div class="teste-titulo">Hash de Senha</div>';
            echo '<div class="teste-detalhe">✓ Funções de criptografia funcionando corretamente</div>';
            echo '</div>';
        }
        
        // TESTE 6: Usuário de teste
        if ($conexaoOk) {
            $usuarioTeste = buscarUsuarioPorEmail('teste@izistyle.com.br');
            
            if ($usuarioTeste) {
                echo '<div class="teste-item sucesso">';
                echo '<div class="teste-titulo">Usuário de Teste</div>';
                echo '<div class="teste-detalhe">✓ Usuário de teste encontrado: ' . $usuarioTeste['nome_completo'] . '</div>';
                echo '</div>';
            }
        }
        ?>
        
        <div class="info-box">
            <h3>📊 Informações do Servidor</h3>
            <ul>
                <li><strong>PHP Versão:</strong> <?php echo phpversion(); ?></li>
                <li><strong>Host:</strong> <?php echo DB_HOST; ?></li>
                <li><strong>Porta:</strong> <?php echo DB_PORT; ?></li>
                <li><strong>Banco:</strong> <?php echo DB_NAME; ?></li>
                <li><strong>Usuário:</strong> <?php echo DB_USER; ?></li>
                <li><strong>Status:</strong> <?php echo $conexaoOk ? '🟢 Conectado' : '🔴 Desconectado'; ?></li>
            </ul>
        </div>
        
        <a href="index.html" class="btn-voltar">← Voltar para Home</a>
    </div>
</body>
</html>