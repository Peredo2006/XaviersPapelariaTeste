
<?php
// app/views/produtos.php
session_start();
include_once "../controls/connection.php";

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
// Endpoint JSON (usado pelo modal de edição)
if (isset($_GET['json'])) {
    $id = intval($_GET['json']);
    $st = $conn->prepare("SELECT * FROM produtos WHERE id_produto = ?");
    $st->bind_param('i', $id);
    $st->execute();
    $res = $st->get_result();
    $row = $res->fetch_assoc();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($row ?? []);
    exit;
}

// buscar produtos
$res = $conn->query("SELECT * FROM produtos ORDER BY id_produto DESC");
$produtos = [];
if ($res) {
    while ($r = $res->fetch_assoc()) $produtos[] = $r;
}

// helper de escape
function e($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Gerenciamento de Produtos</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <style>
        /* --- Escopo exclusivo para não conflitar com style.css --- */
        .produtos-page .topbar { display:flex; align-items:center; justify-content:space-between; padding:18px 24px; }
        .produtos-page .logo img { height:54px; display:block; }
        .produtos-page .titulo-pagina { text-align:center; font-size:28px; color:#E03A7F; margin-top:6px; margin-bottom:12px; }

        .painel { background: #CFF8DB; border-radius:12px; padding:14px; box-shadow:0 6px 8px rgba(0,0,0,0.03); }
        .painel .cabecalho { display:flex; justify-content:space-between; align-items:center; padding:6px 10px; }
        .gestao-label { background:#BDEECF; padding:10px 14px; border-radius:8px; font-weight:600; color:#2b2b2b; }
        .btn-adicionar {
            background: linear-gradient(180deg,#8a3ce6,#6f18c5);
            color:white; padding:10px 14px; border-radius:18px; text-decoration:none; font-weight:700;
            box-shadow: 0 3px 0 rgba(0,0,0,0.08); border:0;
        }

        /* grid de cards (3 colunas como na referência) */
        .produtos-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:26px; margin-top:18px; }
        .produto-card {
            background: #bff6d9; border-radius:22px; padding:18px; text-align:center;
            display:flex; flex-direction:column; align-items:center; gap:12px;
        }

        .produto-foto {
            width:100%; height:220px; background:#ff2b2b; border-radius:28px; display:flex;
            align-items:center; justify-content:center; color:#111; font-weight:700; overflow:hidden;
        }
        .produto-foto img { width:100%; height:100%; object-fit:cover; border-radius:22px; }

        .produto-nome {
            color:#FF7A00; 
            font-weight:800;
            text-transform:uppercase;
            font-size:16px;
        }

        .produto-valor {
            color:#E03A7F; 
            font-weight:700;
            font-size:15px;
        }

        .acoes { display:flex; gap:10px; justify-content:center; margin-top:6px; }
        .acao-btn { width:40px; height:40px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; border:0; cursor:pointer; color:#fff; font-size:18px; }
        .acao-editar { background:#7A06C7; }
        .acao-excluir { background:#E03A7F; }

        /* modal */
        .modal { position:fixed; inset:0; background:rgba(0,0,0,0.45); display:none; align-items:center; justify-content:center; z-index:999; }
        .modal.open { display:flex; }
        .modal-box { width:95%; max-width:540px; background:#fff; padding:18px; border-radius:12px; box-shadow:0 12px 30px rgba(0,0,0,0.15); }
        .modal-box h3 { margin:0 0 8px 0; color:#333; }
        .form-row { margin-top:10px; }
        .form-row input[type="text"], .form-row input[type="number"], .form-row textarea, .form-row input[type="file"] {
            width:100%; padding:8px; border-radius:8px; border:1px solid #ddd; box-sizing:border-box;
        }
        .modal-actions { margin-top:14px; text-align:right; }
        .btn-cancel { background:#ddd; border:0; padding:8px 12px; border-radius:10px; margin-right:8px; cursor:pointer; }
        .btn-save { background:#7A06C7; color:#fff; border:0; padding:8px 12px; border-radius:10px; cursor:pointer; }

        /* responsivo */
        @media (max-width:1000px) { .produtos-grid{ grid-template-columns:repeat(2,1fr); } }
        @media (max-width:640px) { .produtos-grid{ grid-template-columns:1fr; } .produto-foto{height:180px;} }
    </style>
</head>
<body class="produtos-page">

    <header class="topbar">
        <div class="logo">
            <a href="../../index.php"><img src="../../public/assets/images/logo.png" alt="Logo"></a>
        </div>
        <div style="display:flex; align-items:center; gap:14px;">
                        <span class="me-3 text-light"><?php echo $_SESSION['user_name']; ?> (<?php echo $_SESSION['user_type']; ?>)</span>
            <button class="btn-sair">Sair</button>
        </div>
    </header>

    <main style="max-width:1100px;margin:18px auto;padding:0 16px;">
        <h2 class="titulo-pagina">Gerenciamento de Produtos</h2>

        <div class="painel">
            <div class="cabecalho">
                <div class="gestao-label">Gestão dos produtos</div>
                <button id="btnAdd" class="btn-adicionar">+ Adicionar Produto</button>
            </div>

            <div class="produtos-grid" style="padding:18px;">
                <?php if (empty($produtos)): ?>
                    <div style="grid-column:1/-1; text-align:center; color:#666;">Nenhum produto cadastrado.</div>
                <?php else: ?>
                    <?php foreach ($produtos as $p): ?>
                        <div class="produto-card">
                            <div class="produto-foto">
                                <?php if (!empty($p['imagem']) && file_exists(__DIR__ . '/../../public/uploads/' . $p['imagem'])): ?>
                                    <img src="../../public/uploads/<?php echo e($p['imagem']); ?>" alt="">
                                <?php else: ?>
                                    <div style="font-weight:700;color:#222;">FOTO DO PRODUTO</div>
                                <?php endif; ?>
                            </div>

                            <div class="produto-nome"><?php echo e($p['nome']); ?></div>
                            <div class="produto-valor">R$ <?php echo number_format((float)$p['preco'], 2, ',', '.'); ?></div>

                            <div class="acoes">
                                <button class="acao-btn acao-editar" onclick="openEdit(<?php echo (int)$p['id_produto']; ?>)" title="Editar">✎</button>
                                <a class="acao-btn acao-excluir" href="../models/processa_produto.php?acao=excluir&id=<?php echo (int)$p['id_produto']; ?>" onclick="return confirm('Excluir este produto?')" title="Excluir">🗑️</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal (add / edit) -->
    <div id="modal" class="modal" aria-hidden="true">
        <div class="modal-box" role="dialog" aria-modal="true">
            <h3 id="modalTitle">Adicionar produto</h3>
            <form id="frmProduto" method="post" enctype="multipart/form-data" action="../models/processa_produto.php?acao=novo">
                <input type="hidden" name="id_produto" id="id_produto" value="">
                <input type="hidden" name="foto_atual" id="foto_atual" value="">
                <div class="form-row">
                    <label>Nome</label>
                    <input type="text" name="nome" id="nome" required>
                </div>
                <div class="form-row">
                    <label>Quantidade</label>
                    <input type="number" name="quantidade" id="quantidade" min="0" value="0" required>
                </div>
                <div class="form-row">
                    <label>Preço</label>
                    <input type="number" step="0.01" name="preco" id="preco" min="0" value="0.00" required>
                </div>
                <div class="form-row">
                    <label>Descrição</label>
                    <textarea name="descricao" id="descricao" rows="3"></textarea>
                </div>
                <div class="form-row">
                    <label>Foto (opcional)</label>
                    <input type="file" name="imagem" id="imagem" accept="image/*">
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn-save">Salvar</button>
                </div>
            </form>
        </div>
    </div>

<script>
    document.getElementById('btnAdd').addEventListener('click', function(){
        openAdd();
    });

    function openAdd(){
        document.getElementById('modalTitle').innerText = 'Adicionar produto';
        document.getElementById('frmProduto').action = '../models/processa_produto.php?acao=novo';
        document.getElementById('id_produto').value = '';
        document.getElementById('foto_atual').value = '';
        document.getElementById('nome').value = '';
        document.getElementById('quantidade').value = 0;
        document.getElementById('preco').value = '0.00';
        document.getElementById('descricao').value = '';
        document.getElementById('image').value = '';
        document.getElementById('modal').classList.add('open');
    }

    function closeModal(){
        document.getElementById('modal').classList.remove('open');
    }

    function openEdit(id){
        fetch('produtos.php?json=' + id)
            .then(r => r.json())
            .then(data => {
                if (!data || !data.id_produto) { alert('Produto não encontrado'); return; }
                document.getElementById('modalTitle').innerText = 'Editar produto';
                document.getElementById('frmProduto').action = '../models/processa_produto.php?acao=editar';
                document.getElementById('id_produto').value = data.id_produto;
                document.getElementById('foto_atual').value = data.imagem ?? '';
                document.getElementById('nome').value = data.nome ?? '';
                document.getElementById('quantidade').value = data.quantidade ?? 0;
                document.getElementById('preco').value = parseFloat(data.preco ?? 0).toFixed(2);
                document.getElementById('descricao').value = data.descricao ?? '';
                document.getElementById('imagem').value = data.imagem ?? '';
                document.getElementById('modal').classList.add('open');
            })
            .catch(e => { console.error(e); alert('Erro ao buscar produto'); });
    }

    // fechar modal com ESC
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeModal(); });
</script>

</body>
</html>



