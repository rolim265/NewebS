<?php
include ('../conect/serv.php');
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recebendo dados do formulário
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $bio = $_POST['bio'];
    $profile_picture = 'default.jpg'; // Caso não envie uma foto

    // Verificar se uma foto foi enviada
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $profile_picture = $_FILES['profile_picture']['name'];
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], "../uploads/" . $profile_picture); // Pasta onde as fotos serão salvas
    }

    // Criptografar a senha com password_hash
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);


    // Inserir os dados no banco
    $sql = "INSERT INTO users (name, email, password, bio, profile_picture) 
            VALUES ('$name', '$email', '$hashed_password', '$bio', '$profile_picture')";

    if ($conn->query($sql) === TRUE) {
        header("location: ../html/login.html");
        echo "Cadastro realizado com sucesso!";
    } else {
        echo "Erro: " . $conn->error;
    }

    $conn->close();
}
?>
