<?php
$db_file = '/tmp/database.db';

if (!file_exists($db_file)) {
    $db = new SQLite3($db_file);
    $db->exec("CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        age INTEGER NOT NULL
    )");
} else {
    $db = new SQLite3($db_file);
}

$table_check = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
if (!$table_check->fetchArray()) {
    $db->exec("CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        age INTEGER NOT NULL
    )");
}

header('Content-Type: application/json');
$request_method = $_SERVER['REQUEST_METHOD'];

switch ($request_method) {
    case 'GET':
        getUsers();
        break;
    case 'POST':
        createUser();
        break;
    case 'PUT':
        updateUser();
        break;
    case 'DELETE':
        deleteUser();
        break;
    default:
        echo json_encode(['error' => 'Método não suportado']);
        break;
}

function createUser() {
    global $db;
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['name'], $input['email'], $input['age'])) {
        $stmt = $db->prepare('INSERT INTO users (name, email, age) VALUES (:name, :email, :age)');
        if ($stmt === false) {
            echo json_encode(['error' => 'Erro ao preparar a declaração SQL']);
            return;
        }
        $stmt->bindValue(':name', $input['name'], SQLITE3_TEXT);
        $stmt->bindValue(':email', $input['email'], SQLITE3_TEXT);
        $stmt->bindValue(':age', $input['age'], SQLITE3_INTEGER);
        $result = $stmt->execute();
        if ($result) {
            echo json_encode(['success' => 'Usuário criado com sucesso']);
        } else {
            echo json_encode(['error' => 'Erro ao criar usuário']);
        }
    } else {
        echo json_encode(['error' => 'Dados incompletos']);
    }
}

function getUsers() {
    global $db;
    $result = $db->query('SELECT * FROM users');
    $users = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $users[] = $row;
    }
    echo json_encode($users);
}

function updateUser() {
    global $db;
    parse_str(file_get_contents('php://input'), $input);
    if (isset($input['id'], $input['name'], $input['email'], $input['age'])) {
        $stmt = $db->prepare('UPDATE users SET name = :name, email = :email, age = :age WHERE id = :id');
        if ($stmt === false) {
            echo json_encode(['error' => 'Erro ao preparar a declaração SQL']);
            return;
        }
        $stmt->bindValue(':name', $input['name'], SQLITE3_TEXT);
        $stmt->bindValue(':email', $input['email'], SQLITE3_TEXT);
        $stmt->bindValue(':age', $input['age'], SQLITE3_INTEGER);
        $stmt->bindValue(':id', $input['id'], SQLITE3_INTEGER);
        $result = $stmt->execute();
        if ($result) {
            echo json_encode(['success' => 'Usuário atualizado com sucesso']);
        } else {
            echo json_encode(['error' => 'Erro ao atualizar usuário']);
        }
    } else {
        echo json_encode(['error' => 'Dados incompletos']);
    }
}

function deleteUser() {
    global $db;
    parse_str(file_get_contents('php://input'), $input);
    if (isset($input['id'])) {
        $stmt = $db->prepare('DELETE FROM users WHERE id = :id');
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
?>