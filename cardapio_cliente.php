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
        .lanche {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .lanche img {
            max-width: 120px;
            height: auto;
            border-radius: 5px;
        }
        .lanche-details {
            flex-grow: 1;
        }
        .lanche-details h2 {
            margin: 0 0 5px 0;
        }
        .lanche-details p {
            margin: 3px 0;
        }
        .lanche input[type="number"] {
            width: 60px;
            padding: 5px;
        }
    </style>
</head>
<body>
<?php include 'menu_cliente.php'; ?>
    <main class="cardapio">
        <form method="POST" action="carrinho.php">
        <?php
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<div class="lanche">';
                
                echo '<img src="imagens/lanches/' . htmlspecialchars($row['imagem']) . '" alt="' . htmlspecialchars($row['nome']) . '">';
                
                echo '<div class="lanche-details">';
                echo '<h2>' . htmlspecialchars($row['nome']) . '</h2>';
                echo '<p>' . htmlspecialchars($row['descricao']) . '</p>';
                echo '<p>R$' . htmlspecialchars(number_format($row['preco'], 2, ',', '.')) . '</p>';
                echo '</div>';
                
                echo '<input type="number" name="quantidade[' . $row['id'] . ']" min="0" value="0" />';
                echo '</div>';
            }
        } else {
            echo '<p>Nenhum lanche encontrado.</p>';
        }
        ?>

        <button type="submit">Adicionar</button>
        </form>
    </main>
</body>
</html>
<?php
mysqli_close($conn);
?>
