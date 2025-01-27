<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../html/login.html");
    exit();
}

include("../conect/serv.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['foto'])) {
    $user_id = $_SESSION['user_id'];
    $foto = $_FILES['foto'];

    // Verificar se o arquivo foi enviado corretamente
    if ($foto['error'] == 0) {
        $ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
        $novo_nome = uniqid() . "." . $ext;
        $upload_dir = "../uploads/" . $novo_nome;

        // Mover o arquivo para o diretório de uploads
        move_uploaded_file($foto['tmp_name'], $upload_dir);

        // Atualizar o nome da foto no banco de dados
        $sql = "UPDATE users SET profile_picture = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $novo_nome, $user_id);
        $stmt->execute();
    }

    header("Location: ../php/perfilofc.php");
    exit();
}
?>
