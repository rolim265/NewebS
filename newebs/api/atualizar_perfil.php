<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include("../conect/serv.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $bio = $_POST['bio'];
    $user_id = $_SESSION['user_id'];

    // Atualizar o perfil no banco de dados
    $sql = "UPDATE users SET NAME = ?, bio = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $nome, $bio, $user_id);
    $stmt->execute();

    header("Location: ../php/perfilofc.php"); // Redireciona de volta para o perfil após a atualização
    exit();
}
?>
