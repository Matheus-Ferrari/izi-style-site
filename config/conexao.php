<?php
/**
 * =====================================================
 * ARQUIVO DE CONEXÃO - IZI STYLE E-COMMERCE
 * =====================================================
 * Conexão segura com PostgreSQL
 * Arquivo: config/conexao.php
 * =====================================================
 */

// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_PORT', '5433');
define('DB_NAME', 'izistyle_db');
define('DB_USER', 'postgres');
define('DB_PASS', 'admin');

// Variável global de conexão
$conexao = null;

/**
 * Estabelece conexão com PostgreSQL
 * @return resource|false Retorna a conexão ou false em caso de erro
 */
function conectarBanco() {
    global $conexao;
    
    // String de conexão
    $conn_string = sprintf(
        "host=%s port=%s dbname=%s user=%s password=%s",
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_USER,
        DB_PASS
    );
    
    // Tentar conexão
    $conexao = @pg_connect($conn_string);
    
    if (!$conexao) {
        error_log("Erro ao conectar no banco de dados: " . pg_last_error());
        return false;
    }
    
    // Definir encoding UTF-8
    pg_set_client_encoding($conexao, 'UTF8');
    
    return $conexao;
}

/**
 * Fecha a conexão com o banco
 */
function fecharBanco() {
    global $conexao;
    if ($conexao) {
        pg_close($conexao);
        $conexao = null;
    }
}

/**
 * Executa uma query e retorna o resultado
 * @param string $query SQL a ser executado
 * @return resource|false Resultado da query
 */
function executarQuery($query) {
    global $conexao;
    
    if (!$conexao) {
        conectarBanco();
    }
    
    $resultado = @pg_query($conexao, $query);
    
    if (!$resultado) {
        error_log("Erro na query: " . pg_last_error($conexao));
        return false;
    }
    
    return $resultado;
}

/**
 * Executa uma query preparada (previne SQL Injection)
 * @param string $query SQL com placeholders $1, $2, etc
 * @param array $params Array com os parâmetros
 * @return resource|false Resultado da query
 */
function executarQueryPreparada($query, $params = []) {
    global $conexao;
    
    if (!$conexao) {
        conectarBanco();
    }
    
    $resultado = @pg_query_params($conexao, $query, $params);
    
    if (!$resultado) {
        error_log("Erro na query preparada: " . pg_last_error($conexao));
        return false;
    }
    
    return $resultado;
}

/**
 * Escapa strings para prevenir SQL Injection
 * @param string $string String a ser escapada
 * @return string String escapada
 */
function escaparString($string) {
    global $conexao;
    
    if (!$conexao) {
        conectarBanco();
    }
    
    return pg_escape_string($conexao, $string);
}

/**
 * Retorna o último erro do banco
 * @return string Mensagem de erro
 */
function obterErro() {
    global $conexao;
    return pg_last_error($conexao);
}

/**
 * Inicia a conexão automaticamente ao incluir o arquivo
 */
conectarBanco();

// Registrar função para fechar conexão ao final do script
register_shutdown_function('fecharBanco');

?>