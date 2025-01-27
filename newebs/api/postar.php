<?php
session_start();
include('../conect/serv.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../html/login.html');  // Redireciona para o login se não estiver logado
    exit;
}

$user_id = $_SESSION['user_id'];  // Pega o ID do usuário logado
$content = isset($_POST['content']) ? trim($_POST['content']) : '';  // Conteúdo da postagem
$privacy = isset($_POST['privacy']) ? $_POST['privacy'] : '';  // Privacidade da postagem
$media = NULL;  // Inicializa a variável para mídia

// Lista de opções de privacidade válidas
$privacy_options = ['public', 'followers', 'anonymous_followers'];
if (!in_array($privacy, $privacy_options)) {
    die("Privacidade inválida!");
}

// Verifica se o conteúdo não está vazio
if (empty($content)) {
    die("O conteúdo da postagem não pode estar vazio.");
}

// Verifica se existe um arquivo enviado
if (isset($_FILES['media']) && $_FILES['media']['error'] == 0) {
    $file_name = $_FILES['media']['name']; // Nome original do arquivo
    $file_tmp = $_FILES['media']['tmp_name']; // Local temporário do arquivo no servidor
    $file_error = $_FILES['media']['error']; // Erro no envio do arquivo, se houver

    // Verifica se houve erro no envio do arquivo
    if ($file_error !== 0) {
        die("Erro ao enviar o arquivo: " . $file_error);
    }

    // Define o diretório onde os arquivos serão armazenados
    $upload_dir = '../posts/';

    // Verifica se a pasta existe, senão cria
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);  // Cria a pasta caso não exista
    }

    // Garante que o nome do arquivo é único
    $unique_name = uniqid() . '_' . basename($file_name);
    $file_path = $upload_dir . $unique_name;

    // Move o arquivo para a pasta de uploads
    if (move_uploaded_file($file_tmp, $file_path)) {
        $media = $unique_name;  // Armazena o nome do arquivo no banco de dados
    } else {
        die("Falha ao mover o arquivo para o diretório.");
    }
}

// Insere os dados da postagem no banco de dados usando prepared statement
$sql = "INSERT INTO posts (user_id, content, media, privacy) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("isss", $user_id, $content, $media, $privacy);
    if ($stmt->execute()) {
        header('Location: ../php/feed.php');  // Redireciona para o feed após postar
    } else {
        echo "Erro ao postar: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Erro na preparação da consulta.";
}

$conn->close();
?>
