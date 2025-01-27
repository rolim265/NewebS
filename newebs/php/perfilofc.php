<?php
session_start(); // Iniciar a sessão

// Verificar se o usuário está logado (usando o ID de usuário na sessão)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redireciona para o login se não estiver logado
    exit();
}

include("../conect/serv.php");

// Buscar os dados do usuário logado
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Fechar a conexão com o banco
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Usuário</title>
    <link rel="stylesheet" href="../css/perfil.css">
</head>
<body>
    <div class="container">
        <div class="perfil">
            <h1>Perfil</h1>

            <!-- Foto do perfil -->
            <div class="foto-perfil">
                <img src="../uploads/<?php echo $user['profile_picture']; ?>" alt="Foto de Perfil" id="fotoPerfil">
                <form action="../api/upload_foto.php" method="POST" enctype="multipart/form-data">
                    <input type="file" name="foto" id="foto" />
                    <button type="submit">Alterar Foto</button>
                </form>
            </div>

            <!-- Dados do usuário -->
            <form action="../api/atualizar_perfil.php" method="POST">
                <label for="nome">Nome:</label>
                <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($user['NAME']); ?>" required>

                <label for="bio">Bio:</label>
                <textarea name="bio" id="bio" rows="4" required><?php echo htmlspecialchars($user['bio']); ?></textarea>

                <button type="submit">Atualizar Perfil</button>
            </form>

            <!-- Histórico de postagens -->
            <h2>Histórico de Postagens</h2>
            <ul id="historicoPostagens">
                <?php
                // Buscar as postagens do usuário
                include("../conect/serv.php");
                $sql_posts = "SELECT * FROM posts WHERE user_id = $user_id ORDER BY created_at DESC";
                $result_posts = $conn->query($sql_posts);
                while ($post = $result_posts->fetch_assoc()) {
                    echo "<li>" . htmlspecialchars($post['content']) . " - " . $post['created_at'] . "</li>";
                }
                ?>
            </ul>

            <!-- Botão de logout -->
            <a href="../api/logout.php" class="logout-btn">Sair</a>
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
