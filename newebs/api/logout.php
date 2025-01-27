<?php
session_start(); 
if (isset($_SESSION['id_usuario'])) {
    session_unset();

    session_destroy();
    
    header('Location: ../html/home.html');
    echo json_encode(["message" => "Logout realizado com sucesso"]);
    
} else {
    echo json_encode(["message" => "Nenhum usuário logado"]);
}
?>