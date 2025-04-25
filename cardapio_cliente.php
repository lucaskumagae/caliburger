<?php
include 'conexao.php';
$query = "SELECT id, nome, descricao, preco FROM cardapio";
$result = mysqli_query($conn, $query);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Cardápio Cliente</title>
    <link rel="stylesheet" href="main.css" />
</head>
<body>
<?php include 'menu_cliente.php'; ?>
    <main class="cardapio">
        <form method="POST" action="carrinho.php">
        <?php
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<div class="lanche">';
                echo '<h2>' . htmlspecialchars($row['nome']) . '</h2>';
                echo '<p>' . htmlspecialchars($row['descricao']) . '</p>';
                echo '<p>R$' . htmlspecialchars($row['preco']) . '</p>';
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
// Fecha a conexão
mysqli_close($conn);
?>
