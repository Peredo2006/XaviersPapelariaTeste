<?php
session_start();
include_once "app/controls/connection.php";

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: app/views/login.php');
    exit();
}

// Verificar se é gerente
$is_gerente = ($_SESSION['user_type'] === 'Gerente');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo - Xavier's</title>
    <link href="https://fonts.googleapis.com/css2?family=National+Park&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/assets/css/style.css">
</head>
<body class="pagina-inicial">
    <h1>👋 Bem-vindo, <?php echo $_SESSION['user_name']; ?>!</h1>
    
    <div class="logo-central">
        <img src="public/assets/images/logo.png" alt="Xavier's Logo">
    </div>

    <div class="menu-container">
        <div class="menu-coluna esquerda">
            <a href="app/views/estoque.html" class="opcao-menu">Gerenciar estoque</a>
            <?php if ($is_gerente): ?>
                <a href="app/views/usuarios.php" class="opcao-menu">Gerenciar funcionários</a>
            <?php else: ?>
                <p style="color: #777; font-style: italic;">Acesso restrito a gerentes</p>
            <?php endif; ?>
        </div>
        <div class="menu-coluna direita">
            <a href="app/views/clientes.html" class="opcao-menu">Gerenciar clientes</a>
            <a href="app/views/notificacoes.html" class="opcao-menu">Notificações</a>
        </div>
    </div>
    
    <div class="user-info">
        <a href="app/controls/logout.php" class="btn-sair">Sair</a>
    </div>
</body>
</html>
