<?php
/**
 * TESTE SIMPLES - API OBTER DADOS
 * Versão ultra-simplificada para debug
 */

// Iniciar sessão
session_start();

// Headers
header('Content-Type: application/json; charset=utf-8');

// Log de tudo
error_log("=== TESTE SIMPLES ===");
error_log("SESSION: " . print_r($_SESSION, true));

// Verificar se está logado
if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    error_log("NÃO ESTÁ LOGADO!");
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Não está logado',
        'session' => $_SESSION
    ]);
    exit;
}

error_log("ESTÁ LOGADO!");

// Pegar ID
$usuarioId = $_SESSION['usuario_id'] ?? null;
error_log("ID: " . $usuarioId);

if (!$usuarioId) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'ID não encontrado'
    ]);
    exit;
}

// Conectar banco
require_once __DIR__ . '/../config/conexao.php';

// Query simples
$query = "SELECT * FROM usuarios WHERE id = $1 LIMIT 1";
$resultado = pg_query_params($conexao, $query, [$usuarioId]);

if (!$resultado) {
    $erro = pg_last_error($conexao);
    error_log("ERRO QUERY: " . $erro);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro na query',
        'erro' => $erro
    ]);
    exit;
}

if (pg_num_rows($resultado) === 0) {
    error_log("USUÁRIO NÃO ENCONTRADO!");
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Usuário não existe'
    ]);
    exit;
}

$usuario = pg_fetch_assoc($resultado);
error_log("USUÁRIO ENCONTRADO: " . $usuario['nome_completo']);

// Retornar
echo json_encode([
    'sucesso' => true,
    'mensagem' => 'OK',
    'usuario' => $usuario
]);

error_log("=== FIM TESTE ===");
?>