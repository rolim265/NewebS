<?php
include('../conect/serv.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Verificar se os dados foram enviados corretamente
    var_dump($_POST); // Adicione esta linha para depurar

    // Verificar a conexão com o banco de dados
    if ($conn->connect_error) {
        die("Conexão falhou: " . $conn->connect_error);
    }

    // Buscar o usuário no banco de dados
    $sql = "SELECT id, name, password FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Debug: Verificar o que foi retornado
        var_dump($user);  // Isso vai mostrar o conteúdo da variável $user

        // Verificar se a senha corresponde com password_verify
        if (isset($user['password']) && password_verify($password, $user['password'])) {
            // Iniciar sessão e redirecionar para o feed ou página principal
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            header("Location: ../php/feed.php");
        } else {
            echo "Senha incorreta!";
        }
    } else {
        echo "Usuário não encontrado!";
    }

    $conn->close();
}

?>
