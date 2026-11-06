<?php
ini_set('display_errors', '0');
error_reporting(0);

/**
 * API RESTful de gerenciamento de usuários.
 *
 * Ponto de entrada único (Vercel roteia tudo para este arquivo).
 * Expõe endpoints CRUD para a tabela `usuarios` (SQLite) e a documentação
 * OpenAPI/Swagger em /openapi.json e /docs.
 *
 * @package ApiRestfulPhp
 */
$db_file = '/tmp/database.db';

if (!file_exists($db_file)) {
    $db = new SQLite3($db_file);
    $db->exec("CREATE TABLE usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE
    )");
} else {
    $db = new SQLite3($db_file);
}

$table_check = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='usuarios'");
if (!$table_check->fetchArray()) {
    $db->exec("CREATE TABLE usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE
    )");
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$request_uri = $request_uri === '/' ? '/' : rtrim($request_uri, '/');

// Endpoints de documentação da API
if ($request_uri === '/openapi.json' || $request_uri === '/api/openapi.json') {
    header('Content-Type: application/json; charset=utf-8');
    readfile(__DIR__ . '/openapi.json');
    exit;
}

if ($request_uri === '/docs' || $request_uri === '/api/docs') {
    header('Content-Type: text/html; charset=utf-8');
    echo renderDocsPage();
    exit;
}

// Rotas válidas para o recurso de usuários (mantém compatibilidade com '/')
$allowed_paths = ['/', '/usuarios', '/api/usuarios'];
if (!in_array($request_uri, $allowed_paths, true)) {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Rota não encontrada']);
    exit;
}

switch ($request_method) {
    case 'GET':
        getUsuarios();
        break;
    case 'POST':
        createUsuario();
        break;
    case 'PUT':
        updateUsuario();
        break;
    case 'DELETE':
        deleteUsuario();
        break;
    default:
        echo json_encode(['error' => 'Método não suportado']);
        break;
}

/**
 * Cria um novo usuário a partir de um payload JSON.
 *
 * Espera no corpo da requisição (application/json) os campos:
 * nome (string) e email (string). Retorna JSON com
 * sucesso ou erro.
 *
 * @return void
 */
function createUsuario()
{
    global $db;
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['nome'], $input['email'])) {
        $check = $db->prepare('SELECT 1 FROM usuarios WHERE email = :email');
        if ($check === false) {
            echo json_encode(['error' => 'Erro ao preparar a declaração SQL']);
            return;
        }
        $check->bindValue(':email', $input['email'], SQLITE3_TEXT);
        $check_result = $check->execute();
        if ($check_result && $check_result->fetchArray()) {
            echo json_encode(['error' => 'Email já cadastrado']);
            return;
        }

        $stmt = $db->prepare('INSERT INTO usuarios (nome, email) VALUES (:nome, :email)');
        if ($stmt === false) {
            echo json_encode(['error' => 'Erro ao preparar a declaração SQL']);
            return;
        }
        $stmt->bindValue(':nome', $input['nome'], SQLITE3_TEXT);
        $stmt->bindValue(':email', $input['email'], SQLITE3_TEXT);
        $result = @$stmt->execute();
        if ($result) {
            echo json_encode(['success' => 'Usuário criado com sucesso']);
        } else {
            echo json_encode(['error' => 'Erro ao criar usuário']);
        }
    } else {
        echo json_encode(['error' => 'Dados incompletos']);
    }
}

/**
 * Retorna a lista de todos os usuários cadastrados em formato JSON.
 *
 * @return void
 */
function getUsuarios()
{
    global $db;
    $result = $db->query('SELECT * FROM usuarios');
    $usuarios = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $usuarios[] = $row;
    }
    echo json_encode($usuarios);
}

/**
 * Atualiza um usuário existente.
 *
 * Espera no corpo da requisição (application/x-www-form-urlencoded)
 * os campos: id (int), nome (string) e email (string).
 *
 * @return void
 */
function updateUsuario()
{
    global $db;
    parse_str(file_get_contents('php://input'), $input);
    if (isset($input['id'], $input['nome'], $input['email'])) {
        $check = $db->prepare('SELECT 1 FROM usuarios WHERE email = :email AND id != :id');
        if ($check === false) {
            echo json_encode(['error' => 'Erro ao preparar a declaração SQL']);
            return;
        }
        $check->bindValue(':email', $input['email'], SQLITE3_TEXT);
        $check->bindValue(':id', $input['id'], SQLITE3_INTEGER);
        $check_result = $check->execute();
        if ($check_result && $check_result->fetchArray()) {
            echo json_encode(['error' => 'Email já cadastrado']);
            return;
        }

        $stmt = $db->prepare('UPDATE usuarios SET nome = :nome, email = :email WHERE id = :id');
        if ($stmt === false) {
            echo json_encode(['error' => 'Erro ao preparar a declaração SQL']);
            return;
        }
        $stmt->bindValue(':nome', $input['nome'], SQLITE3_TEXT);
        $stmt->bindValue(':email', $input['email'], SQLITE3_TEXT);
        $stmt->bindValue(':id', $input['id'], SQLITE3_INTEGER);
        $result = @$stmt->execute();
        if ($result) {
            echo json_encode(['success' => 'Usuário atualizado com sucesso']);
        } else {
            echo json_encode(['error' => 'Erro ao atualizar usuário']);
        }
    } else {
        echo json_encode(['error' => 'Dados incompletos']);
    }
}

/**
 * Remove um usuário pelo id.
 *
 * Espera no corpo da requisição (application/x-www-form-urlencoded)
 * o campo id (int).
 *
 * @return void
 */
function deleteUsuario()
{
    global $db;
    parse_str(file_get_contents('php://input'), $input);
    if (isset($input['id'])) {
        $stmt = $db->prepare('DELETE FROM usuarios WHERE id = :id');
        if ($stmt === false) {
            echo json_encode(['error' => 'Erro ao preparar a declaração SQL']);
            return;
        }
        $stmt->bindValue(':id', $input['id'], SQLITE3_INTEGER);
        $result = $stmt->execute();
        if ($result) {
            echo json_encode(['success' => 'Usuário deletado com sucesso']);
        } else {
            echo json_encode(['error' => 'Erro ao deletar usuário']);
        }
    } else {
        echo json_encode(['error' => 'ID não fornecido']);
    }
}

/**
 * Renderiza a página HTML com a interface Swagger UI apontando para /openapi.json.
 *
 * @return string
 */
function renderDocsPage(): string
{
    return <<<'HTML'
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Documentação da API - Swagger UI</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script>
        window.onload = function () {
            SwaggerUIBundle({
                url: '/openapi.json',
                dom_id: '#swagger-ui',
                presets: [SwaggerUIBundle.presets.apis, SwaggerUIBundle.SwaggerUIStandalonePreset],
                layout: 'BaseLayout'
            });
        };
    </script>
</body>
</html>
HTML;
}
