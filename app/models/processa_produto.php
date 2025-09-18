<?php
// app/models/processa_produto.php
require_once('../controls/connection.php');

// diretório onde salvamos fotos (relativo a este arquivo)
$uploadDir = __DIR__ . '/../../public/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$acao = $_GET['acao'] ?? '';

if ($acao === 'novo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome_produto'] ?? '');
    $quantidade = intval($_POST['quantidade_produto'] ?? 0);
    $preco = floatval($_POST['preco_produto'] ?? 0);
    $descricao = trim($_POST['descricao_produto'] ?? '');
    $fotoNome = '';

    if (!empty($_FILES['foto_produto']['name'])) {
        $ext = pathinfo($_FILES['foto_produto']['name'], PATHINFO_EXTENSION);
        $fotoNome = uniqid('p_') . '.' . $ext;
        move_uploaded_file($_FILES['foto_produto']['tmp_name'], $uploadDir . $fotoNome);
    }

    $stmt = $conn->prepare("INSERT INTO produtos (nome_produto, foto_produto, quantidade_produto, preco_produto, descricao_produto) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('ssids', $nome, $fotoNome, $quantidade, $preco, $descricao);
    $stmt->execute();
    $stmt->close();

    header('Location: ../views/produtos.php');
    exit;
}

if ($acao === 'editar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id_produto'] ?? 0);
    $nome = trim($_POST['nome_produto'] ?? '');
    $quantidade = intval($_POST['quantidade_produto'] ?? 0);
    $preco = floatval($_POST['preco_produto'] ?? 0);
    $descricao = trim($_POST['descricao_produto'] ?? '');
    $fotoAtual = $_POST['foto_atual'] ?? '';
    $fotoNome = $fotoAtual;

    if (!empty($_FILES['foto_produto']['name'])) {
        // salva nova foto
        $ext = pathinfo($_FILES['foto_produto']['name'], PATHINFO_EXTENSION);
        $fotoNome = uniqid('p_') . '.' . $ext;
        move_uploaded_file($_FILES['foto_produto']['tmp_name'], $uploadDir . $fotoNome);
        // remove antiga
        if ($fotoAtual && file_exists($uploadDir . $fotoAtual)) {
            @unlink($uploadDir . $fotoAtual);
        }
    }

    $stmt = $conn->prepare("UPDATE produtos SET nome_produto = ?, foto_produto = ?, quantidade_produto = ?, preco_produto = ?, descricao_produto = ? WHERE id_produto = ?");
    $stmt->bind_param('ssidsi', $nome, $fotoNome, $quantidade, $preco, $descricao, $id);
    $stmt->execute();
    $stmt->close();

    header('Location: ../views/produtos.php');
    exit;
}

if ($acao === 'excluir' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // remover foto (se houver)
    $st = $conn->prepare("SELECT foto_produto FROM produtos WHERE id_produto = ?");
    $st->bind_param('i', $id);
    $st->execute();
    $res = $st->get_result();
    if ($row = $res->fetch_assoc()) {
        if (!empty($row['foto_produto']) && file_exists($uploadDir . $row['foto_produto'])) {
            @unlink($uploadDir . $row['foto_produto']);
        }
    }
    $st->close();

    $stmt = $conn->prepare("DELETE FROM produtos WHERE id_produto = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    header('Location: ../views/produtos.php');
    exit;
}

// se chegar aqui sem ação conhecida, redireciona
header('Location: ../views/produtos.php');
exit;



