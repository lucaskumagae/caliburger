<?php
include 'conexao.php';

$query = "SELECT id, nome, descricao, preco, imagem FROM cardapio";
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
        </style>
    </head>
<body>
<header>
    <div class="logo-area">
        <img src="imagens/logo_cali_sem_fundo.png" alt="Logo Cali Burger">
        <h1>Cali Burger</h1>
    </div>
</header>

<nav>
    <a href="cardapio_cliente.php">Cardápio</a>
    <a href="carrinho.php">Carrinho</a>
    <a href="meus_pedidos.php">Meus pedidos</a>
    <a href="sair.php" class="logout">Sair</a>
</nav>

    <main class="cardapio-container">
        <form method="POST" action="carrinho.php">
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
                            <input type="number" name="quantidade[<?= $row['id'] ?>]" value="0" min="0">
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
</body>
</html>
