<?php
session_start();
include 'conexao.php';

$cat_query = "SELECT id, nome FROM categoria";
$cat_result = mysqli_query($conn, $cat_query);

$selected_cat_id = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;

if ($selected_cat_id > 0) {
    $query = "SELECT c.id, c.nome, c.descricao, c.preco, c.imagem,
        MIN(e.quantidade) AS min_estoque
        FROM cardapio c
        LEFT JOIN cardapio_ingrediente ci ON c.id = ci.id_cardapio
        LEFT JOIN estoque e ON ci.id_ingrediente = e.id_ingrediente
        WHERE c.categoria_id = $selected_cat_id
        GROUP BY c.id";
} else {
    $query = "SELECT c.id, c.nome, c.descricao, c.preco, c.imagem,
        MIN(e.quantidade) AS min_estoque
        FROM cardapio c
        LEFT JOIN cardapio_ingrediente ci ON c.id = ci.id_cardapio
        LEFT JOIN estoque e ON ci.id_ingrediente = e.id_ingrediente
        GROUP BY c.id";
}
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8" />
        <title>Cardápio Cliente</title>
        <link rel="stylesheet" href="main.css" />
        <style>
            .item-img {
                height: 60px !important;
                width: auto !important;
                object-fit: cover;
                border-radius: 10px;
                margin-right: 20px;
            }
            .cardapio-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 20px;
                padding: 20px;
            }
            .item-cardapio {
                border: 1px solid #ccc;
                padding: 25px 30px;
                margin: 10px 0;
                border-radius: 10px;
                max-width: 850px;
                width: 100%;
                background-color: #fff;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            .item-info {
                flex: 1;
                margin-left: 10px;
            }
            .item-quantidade input[type="number"] {
                width: 60px;
                padding: 5px;
                font-size: 1em;
                border: 1px solid #ccc;
                border-radius: 6px;
            }
            nav.category-nav {
                background-color: #b22222;
                color: white;
                margin: 0;
                padding: 10px 0;
                text-align: center;
            }
            nav.category-nav a {
                margin: 0 10px;
                text-decoration: none;
                color: white;
                font-weight: normal;
            }
            nav.category-nav a.selected {
                color: black;
                font-weight: bold;
            }
        </style>
    </head>
<body>

<?php include 'menu_cliente.php'; ?>

<nav class="category-nav">
    <?php if ($cat_result && mysqli_num_rows($cat_result) > 0): ?>
        <?php while ($cat = mysqli_fetch_assoc($cat_result)): ?>
            <a href="cardapio_cliente.php?categoria=<?= $cat['id'] ?>" class="<?= ($selected_cat_id == $cat['id']) ? 'selected' : '' ?>">
                <?= htmlspecialchars($cat['nome']) ?>
            </a>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Nenhuma categoria encontrada.</p>
    <?php endif; ?>
</nav>

    <main class="cardapio-container">
<form method="POST" action="carrinho.php" id="cardapioForm">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="item-cardapio">
                        <img src="imagens/lanches/<?= htmlspecialchars($row['imagem']) ?>" alt="<?= htmlspecialchars($row['nome']) ?>" class="item-img">
                        <div class="item-info">
                            <h3><?= htmlspecialchars($row['nome']) ?></h3>
                            <p><?= htmlspecialchars($row['descricao']) ?></p>
                            <strong>R$<?= number_format($row['preco'], 2, ',', '.') ?></strong>
                        </div>
                        <div class="item-quantidade">
                            <?php if ($row['min_estoque'] !== null && $row['min_estoque'] < 30): ?>
                                <span style="color: red; font-weight: bold;">Esgotado</span>
                            <?php else: ?>
<input type="number" name="quantidade[<?= $row['id'] ?>]" value="0" min="0" data-item-id="<?= $row['id'] ?>">
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Nenhum item no cardápio.</p>
            <?php endif; ?>

            <div class="botao-adicionar">
                <button type="submit">Adicionar</button>
            </div>
        </form>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('cardapioForm');
    const inputs = form.querySelectorAll('input[type="number"][data-item-id]');

    inputs.forEach(input => {
        const itemId = input.getAttribute('data-item-id');
        const savedValue = localStorage.getItem('quantidade_' + itemId);
        if (savedValue !== null) {
            input.value = savedValue;
        }
    });

    inputs.forEach(input => {
        input.addEventListener('input', () => {
            const itemId = input.getAttribute('data-item-id');
            localStorage.setItem('quantidade_' + itemId, input.value);
        });
    });

    form.addEventListener('submit', () => {
        const existingHiddenInputs = form.querySelectorAll('input[type="hidden"][name^="quantidade["]');
        existingHiddenInputs.forEach(input => input.remove());

        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key.startsWith('quantidade_')) {
                const itemId = key.replace('quantidade_', '');
                const value = localStorage.getItem(key);
                if (value && value !== '0') {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = `quantidade[${itemId}]`;
                    hiddenInput.value = value;
                    form.appendChild(hiddenInput);
                }
            }
        }

        for (let i = localStorage.length - 1; i >= 0; i--) {
            const key = localStorage.key(i);
            if (key.startsWith('quantidade_')) {
                localStorage.removeItem(key);
            }
        }
    });
});
</script>

</body>
</html>
