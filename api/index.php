<?php
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$api_key = getenv('API_KEY');
$base_url = getenv('BASE_URL');

$headers = [
    "Content-Type: application/json",
    "x-apikey: $api_key"
];
function createUser($userData) {
    global $base_url, $headers;

    $options = [
        "http" => [
            "header"  => implode("\r\n", $headers),
            "method"  => "POST",
            "content" => json_encode($userData),
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($base_url, false, $context);

    if ($result === FALSE) {
        return "Erro ao criar usuário.";
    }

    return json_decode($result);
}

function getUsers() {
    global $base_url, $headers;

    $options = [
        "http" => [
            "header" => implode("\r\n", $headers),
            "method" => "GET",
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($base_url, false, $context);

    if ($result === FALSE) {
        return "Erro ao obter usuários.";
    }

    return json_decode($result);
}


function updateUser($userId, $userData) {
    global $base_url, $headers;

    $options = [
        "http" => [
            "header"  => implode("\r\n", $headers),
            "method"  => "PUT",
            "content" => json_encode($userData),
        ]
    ];

    $context = stream_context_create($options);
    $url = $base_url . "/$userId";
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        return "Erro ao atualizar usuário.";
    }

    return json_decode($result);
}


function deleteUser($userId) {
    global $base_url, $headers;

    $options = [
        "http" => [
            "header" => implode("\r\n", $headers),
            "method" => "DELETE",
        ]
    ];

    $context = stream_context_create($options);
    $url = $base_url . "/$userId";
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        return "Erro ao deletar usuário.";
    }

    return "Usuário deletado com sucesso.";
}

?>