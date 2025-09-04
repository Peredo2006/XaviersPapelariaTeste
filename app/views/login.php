<?php
session_start();
// Verificar se há parâmetros de erro na URL
$erro = isset($_GET['erro']) ? $_GET['erro'] : '';
$mensagem_erro = '';

if ($erro === 'senha_incorreta') {
    $mensagem_erro = 'Senha incorreta!';
} elseif ($erro === 'usuario_nao_encontrado') {
    $mensagem_erro = 'Usuário não encontrado!';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xavier's - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=National+Park&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
    <div class="container-fluid container-principal pagina-login">
        <div class="row h-100">
            <!-- págiina de login -->
            <div class="col-12 h-100">
                <div class="row h-100 align-items-center">
                    <!-- formulário -->
                    <div class="col-lg-6 d-flex justify-content-center align-items-center form-col">
                        <div class="quadrado-container">
                            <div class="quadrado-preto"></div>
                            <div class="quadrado-verde">
                                <h1 class="titulo-rosa">Faça o seu login!</h1>
                                <div class="campo-formulario">
                                <form action="../models/processarlogin.php" method="POST">
                                
                                    <input type="email" id="login-email" placeholder="E-mail" name="email">
                                </div>
                                
                                <div class="campo-formulario">
                                    <input type="password" id="login-senha" placeholder="Senha" name="senha">
                                </div>

                                <!-- Mensagem de erro  -->
                                <?php if (!empty($mensagem_erro)): ?>
                                    <div class="mensagem-erro">
                                        <?php echo $mensagem_erro; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <button type="submit" class="btn-cadastrar" id="btn-entrar">Entrar</button>
                                </form>
                                
                                <!--<p class="texto-login">Não possui login? <br> <a href="cadastro.html" class="link-roxo">Faça o seu cadastro!</a></p>-->
                            </div>
                        </div>
                    </div>
                    
                    <!-- logo -->
                    <div class="col-lg-6 logo-container">
                        <img src="../../public/assets/images/logo.png" alt="Xavier's Logo" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>


