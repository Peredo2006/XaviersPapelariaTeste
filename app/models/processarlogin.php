<?php
include_once "../controls/connection.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $senha = $_POST['senha'];
    
    $sql = "SELECT * FROM usuarios WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($senha, $user['senha'])) {
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['nome'];
            $_SESSION['user_type'] = $user['tipo'];
            
            header('Location: ../../index.php');
            exit();
        } else {
          // Senha incorreta - voltar para login com mensagem de erro
            header('Location: ../views/login.php?erro=senha_incorreta');
            exit();
        }
    } else {
        // Usuário não encontrado - voltar para login com mensagem de erro
        header('Location: ../views/login.php?erro=usuario_nao_encontrado');
        exit();
    }
}
mysqli_close($conn);
?>
