<?php
session_start();
include('../conect/serv.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../html/login.html');  // Redireciona para o login se não estiver logado
    exit;
}

// Aqui você pode colocar o código do feed (exibição das postagens)
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed</title>
    <link rel="stylesheet" href="../css/feed.css"> <!-- Inclui o CSS -->
</head>

<body>

    <div class="container">
        <!-- Formulário de postagem -->
        <form action="../api/postar.php" method="POST" enctype="multipart/form-data">
            <textarea name="content" placeholder="Escreva algo..." required></textarea>
            <input type="file" name="media" accept="image/*,video/*">

            <label for="privacy">Privacidade:</label>
            <select name="privacy" required>
                <option value="public">Mundial</option>
                <option value="followers">Apenas para seguidores</option>
                <option value="anonymous">Anônima</option>
            </select>

            <button type="submit">Postar</button>
        </form>


        <!-- Exibição do feed de postagens -->
        <div class="feed">
            <?php
            // Consulta as postagens no banco de dados para exibir no feed
            $user_id = $_SESSION['user_id'];  // Pega o ID do usuário logado

            // Buscar as postagens para exibir no feed (exemplo de postagens públicas ou privadas)
            $sql = "SELECT posts.*, users.name, users.profile_picture FROM posts 
                JOIN users ON posts.user_id = users.id 
                WHERE posts.privacy = 'public' OR posts.user_id = '$user_id' OR posts.privacy = 'followers'
                ORDER BY posts.created_at DESC"; // Ordena as postagens pela data de criação em ordem decrescente

            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($post = $result->fetch_assoc()) {
                    echo '<div class="post">';
                    echo '<div class="user-info"><img src="../uploads/' . $post['profile_picture'] . '" alt="User Avatar">';
                    echo '<h4>' . $post['name'] . '</h4></div>';

                    if ($post['privacy'] == 'anonymous') {
                        echo '<div class="anonymous-info"><h4>Postagem Anônima</h4></div>';
                    }

                    echo '<p>' . $post['content'] . '</p>';

                    if ($post['media']) {
                        echo '<img src="../posts/' . $post['media'] . '" alt="Post Media">';
                    }

                    echo '<form action="../api/comentar.php" method="POST">
                         <input type="hidden" name="post_id" value="' . $post['id'] . '">
                         <textarea name="comment" placeholder="Comente sobre essa postagem..." required></textarea>
                         <button class="comment" type="submit">Comentar</button>
                         </form>';

                    // Exibir comentários
                    $post_id = $post['id'];

                    // Consulta preparada para evitar SQL Injection
                    $stmt = $conn->prepare("SELECT comments.comment, users.name 
                        FROM comments 
                        JOIN users ON comments.user_id = users.id 
                        WHERE comments.post_id = ? 
                        ORDER BY comments.created_at DESC");

                    if ($stmt) {
                        $stmt->bind_param("i", $post_id);
                        $stmt->execute();
                        $comment_result = $stmt->get_result();

                        if ($comment_result->num_rows > 0) {
                            while ($comment = $comment_result->fetch_assoc()) {
                                $user_name = htmlspecialchars($comment['name']);
                                $user_comment = htmlspecialchars($comment['comment']);

                                echo '<div class="comment">';
                                echo '<strong>' . $user_name . ':</strong> ' . $user_comment;
                                echo '</div>';
                            }
                        } else {
                            echo '<p>Não há comentários.</p>';
                        }

                        $stmt->close();
                    } else {
                        echo '<p>Erro ao buscar comentários.</p>';
                    }


                }
            } else {
                echo "<p>Ainda não há postagens.</p>";
            }
            ?>
        </div>
    </div>

</body>

</html>