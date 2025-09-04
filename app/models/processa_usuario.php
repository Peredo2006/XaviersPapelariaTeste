<?php
session_start();
include_once "../controls/connection.php";

// Verificar se o usuário está logado e é gerente
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'gerente') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = mysqli_real_escape_string($conn, $_POST['id_usuario']);
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $cargo = mysqli_real_escape_string($conn, $_POST['cargo']);
    $senha = $_POST['senha'];
    
    // Verificar se é uma atualização ou inserção
    if (!empty($id)) {
        // Atualizar usuário existente
        if (!empty($senha)) {
            // Gerar hash da senha
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET nome='$nome', email='$email', tipo='$cargo', senha='$senha_hash' WHERE id_usuario='$id'";
        } else {
            $sql = "UPDATE usuarios SET nome='$nome', email='$email', tipo='$cargo' WHERE id_usuario='$id'";
        }
    } else {
        // Inserir novo usuário
        if (empty($senha)) {
            header('Location: usuarios.php?erro=senhavazia');
            exit();
        }
        // Gerar hash da senha
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nome, email, tipo, senha, data_cadastro) 
                VALUES ('$nome', '$email', '$cargo', '$senha_hash', NOW())";
    }
    
    if (mysqli_query($conn, $sql)) {
        header('Location: ../views/usuarios.php?sucesso=salvo');
        exit();
    } else {
        header('Location: ../views/usuarios.php?erro=bd');
        exit();
    }
}
?>