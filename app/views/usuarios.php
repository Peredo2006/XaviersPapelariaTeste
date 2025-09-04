<?php
session_start();
include_once "../controls/connection.php";

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar se é gerente
$is_gerente = ($_SESSION['user_type'] === 'Gerente');

// Processar exclusão de usuário (apenas gerentes)
if ($is_gerente && isset($_GET['excluir'])) {
    $id_excluir = mysqli_real_escape_string($conn, $_GET['excluir']);
    
    // Não permitir que o usuário exclua a si mesmo
    if ($id_excluir != $_SESSION['user_id']) {
        $sql_excluir = "DELETE FROM usuarios WHERE id_usuario = '$id_excluir'";
        mysqli_query($conn, $sql_excluir);
        header('Location: usuarios.php?sucesso=excluido');
        exit();
    } else {
        header('Location: usuarios.php?erro=autoexclusao');
        exit();
    }
}

// Buscar usuários do banco de dados
$sql = "SELECT id_usuario, nome, email, tipo, data_cadastro FROM usuarios ORDER BY data_cadastro ASC";
$result = mysqli_query($conn, $sql);
$usuarios = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xavier's - Gerenciamento de Funcionários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=National+Park&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
    <!-- header -->
    <header class="header">
        <div class="logo">
            <img src="../../public/assets/images/logo.png" alt="Logo Xavier's">
        </div>
        <div>
            <span class="me-3 text-light"><?php echo $_SESSION['user_name']; ?> (<?php echo $_SESSION['user_type']; ?>)</span>
            <button class="btn-sair">Sair</button>
        </div>
    </header>

    <!-- principal -->
    <div class="container-principal">
        <h1 class="titulo-pagina">Gerenciamento de Funcionários</h1>
        
        <!-- Mensagens de feedback -->
        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 'excluido'): ?>
            <div class="alert alert-success">Funcionário excluído com sucesso!</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['erro']) && $_GET['erro'] == 'autoexclusao'): ?>
            <div class="alert alert-danger">Você não pode excluir a si mesmo!</div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <span>Lista de Funcionários</span>
                <?php if ($is_gerente): ?>
                    <button class="btn-adicionar" data-bs-toggle="modal" data-bs-target="#modalUsuario">
                        <i class="fas fa-plus"></i> Adicionar Funcionário
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Cargo</th>
                                <th>Data de Cadastro</th>
                                <?php if ($is_gerente): ?>
                                    <th>Ações</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><?php echo $usuario['id_usuario']; ?></td>
                                    <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['tipo']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($usuario['data_cadastro'])); ?></td>
                                    <?php if ($is_gerente): ?>
                                        <td>
                                            <button class="btn-acao btn-editar" data-bs-toggle="modal" data-bs-target="#modalUsuario" 
                                                data-id="<?php echo $usuario['id_usuario']; ?>"
                                                data-nome="<?php echo htmlspecialchars($usuario['nome']); ?>"
                                                data-email="<?php echo htmlspecialchars($usuario['email']); ?>"
                                                data-cargo="<?php echo $usuario['tipo']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-acao btn-excluir" 
                                                onclick="confirmarExclusao(<?php echo $usuario['id_usuario']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para adicionar/editar usuário (apenas para gerentes) -->
    <?php if ($is_gerente): ?>
    <div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUsuarioLabel">Adicionar Funcionário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formUsuario" action="../models/processa_usuario.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="id_usuario" name="id_usuario">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="nome" name="nome" placeholder="Digite o nome completo" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Digite o e-mail" required>
                        </div>
                        <div class="mb-3">
                            <label for="cargo" class="form-label">Cargo</label>
                            <select class="form-control" id="cargo" name="cargo" required>
                                <option value="">Selecione um cargo</option>
                                <option value="Gerente">Gerente</option>
                                <option value="Vendedor">Vendedor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="senha" name="senha" placeholder="Digite a senha">
                            <small class="form-text text-muted">Deixe em branco para manter a senha atual (ao editar)</small>
                        </div>
                        <div class="mb-3">
                            <label for="confirmarSenha" class="form-label">Confirmar Senha</label>
                            <input type="password" class="form-control" id="confirmarSenha" name="confirmarSenha" placeholder="Confirme a senha">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-modal">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Scripts Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Função para confirmar exclusão
        function confirmarExclusao(id) {
            if (confirm('Tem certeza que deseja excluir este funcionário?')) {
                window.location.href = 'usuarios.php?excluir=' + id;
            }
        }
        
        // Função para o botão Sair
        document.querySelector('.btn-sair').addEventListener('click', function() {
            if (confirm('Deseja realmente sair do sistema?')) {
                window.location.href = '../models/logout.php';
            }
        });

        // Preencher modal de edição
        var modalUsuario = document.getElementById('modalUsuario');
        if (modalUsuario) {
            modalUsuario.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var modalTitle = modalUsuario.querySelector('.modal-title');
                var modalBodyInputId = modalUsuario.querySelector('#id_usuario');
                var modalBodyInputNome = modalUsuario.querySelector('#nome');
                var modalBodyInputEmail = modalUsuario.querySelector('#email');
                var modalBodyInputCargo = modalUsuario.querySelector('#cargo');
                
                if (button.getAttribute('data-id')) {
                    // Modo edição
                    modalTitle.textContent = 'Editar Usuário';
                    modalBodyInputId.value = button.getAttribute('data-id');
                    modalBodyInputNome.value = button.getAttribute('data-nome');
                    modalBodyInputEmail.value = button.getAttribute('data-email');
                    modalBodyInputCargo.value = button.getAttribute('data-cargo');
                } else {
                    // Modo adição
                    modalTitle.textContent = 'Adicionar Funcionário';
                    modalBodyInputId.value = '';
                    modalBodyInputNome.value = '';
                    modalBodyInputEmail.value = '';
                    modalBodyInputCargo.value = '';
                }
            });
        }

        // Validação de senha
        var formUsuario = document.getElementById('formUsuario');
        if (formUsuario) {
            formUsuario.addEventListener('submit', function(e) {
                var senha = document.getElementById('senha').value;
                var confirmarSenha = document.getElementById('confirmarSenha').value;
                
                if (senha !== confirmarSenha) {
                    e.preventDefault();
                    alert('As senhas não coincidem!');
                }
            });
        }
    </script>
</body>
</html>


