<?php
session_start();
include('../conect/serv.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../html/login.html');  // Redireciona para o login se não estiver logado
    exit;
}

$user_id = $_SESSION['user_id'];  // Pega o ID do usuário logado
$post_id = $_POST['post_id'];     // Pega o ID da postagem
$comment = $_POST['comment'];     // Pega o conteúdo do comentário

// Insere o comentário no banco de dados
$sql = "INSERT INTO comments (post_id, user_id, comment) VALUES ('$post_id', '$user_id', '$comment')";

if ($conn->query($sql) === TRUE) {
    header("Location: ../php/feed.php");  // Redireciona de volta para o feed
} else {
    echo "Erro ao comentar: " . $conn->error;
}

$conn->close();
?>
