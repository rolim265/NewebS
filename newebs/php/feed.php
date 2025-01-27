<?php
session_start();
include('../conect/serv.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../html/login.html');
    exit;
}

$user_id = $_SESSION['user_id'];

// Consulta corrigida para respeitar a privacidade corretamente
$sql = "SELECT posts.*, 
               CASE 
                   WHEN posts.privacy = 'anonymous_followers' THEN 'Anônimo'
                   ELSE users.name
               END AS display_name, 
               users.profile_picture 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        WHERE posts.privacy = 'public' 
           OR (posts.privacy = 'followers' AND posts.user_id IN (
                SELECT user_id FROM followers WHERE follower_id = ?
           ))
           OR (posts.privacy = 'anonymous_followers' AND posts.user_id IN (
                SELECT user_id FROM followers WHERE follower_id = ?
           ))
           OR posts.user_id = ?
        ORDER BY posts.created_at DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Erro na preparação da consulta: " . $conn->error);
}

$stmt->bind_param("iii", $user_id, $user_id, $user_id);

if (!$stmt->execute()) {
    die("Erro na execução da consulta: " . $stmt->error);
}

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed</title>
    <link rel="stylesheet" href="../css/feed.css">
</head>

<body>

    <div class="container">
        <form action="../api/postar.php" method="POST" enctype="multipart/form-data">
            <textarea name="content" placeholder="Escreva algo..." required></textarea>
            <input type="file" name="media" accept="image/*,video/*">

            <label for="privacy">Privacidade:</label>
            <select name="privacy" required>
                <option value="public">Mundial</option>
                <option value="followers">Apenas para seguidores</option>
                <option value="anonymous_followers">Anônima para seguidores</option>
            </select>

            <button type="submit">Postar</button>
        </form>

        <div class="feed">
            <?php
            if ($result->num_rows > 0) {
                while ($post = $result->fetch_assoc()) {
                    echo '<div class="post">';

                    if ($post['privacy'] !== 'anonymous_followers') {
                        echo '<div class="user-info"><img src="../uploads/' . $post['profile_picture'] . '" alt="User Avatar">';
                        echo '<h4>' . htmlspecialchars($post['display_name']) . '</h4></div>';
                    } else {
                        echo '<div class="anonymous-info"><h4>Postagem Anônima</h4></div>';
                    }

                    echo '<p>' . htmlspecialchars($post['content']) . '</p>';

                    if ($post['media']) {
                        echo '<img src="../posts/' . htmlspecialchars($post['media']) . '" alt="Post Media">';
                    }

                    echo '<form action="../api/comentar.php" method="POST">
                         <input type="hidden" name="post_id" value="' . $post['id'] . '">
                         <textarea name="comment" placeholder="Comente sobre essa postagem..." required></textarea>
                         <button class="comment" type="submit">Comentar</button>
                         </form>';

                    $post_id = $post['id'];

                    $comment_stmt = $conn->prepare("SELECT comments.comment, users.name 
                        FROM comments 
                        JOIN users ON comments.user_id = users.id 
                        WHERE comments.post_id = ? 
                        ORDER BY comments.created_at DESC");

                    $comment_stmt->bind_param("i", $post_id);
                    $comment_stmt->execute();
                    $comment_result = $comment_stmt->get_result();

                    if ($comment_result->num_rows > 0) {
                        while ($comment = $comment_result->fetch_assoc()) {
                            echo '<div class="comment">';
                            echo '<strong>' . htmlspecialchars($comment['name']) . ':</strong> ' . htmlspecialchars($comment['comment']);
                            echo '</div>';
                        }
                    } else {
                        echo '<p>Não há comentários.</p>';
                    }

                    $comment_stmt->close();

                    echo '</div>';
                }
            } else {
                echo "<p>Ainda não há postagens.</p>";
            }

            $stmt->close();
            ?>
        </div>
    </div>

    <div id="navbar"></div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            fetch('../html/navbar.html')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro ao carregar a navbar.');
                    }
                    return response.text();
                })
                .then(data => {
                    document.getElementById('navbar').innerHTML = data;
                })
                .catch(error => console.error(error));
        });
    </script>

</body>

</html>
