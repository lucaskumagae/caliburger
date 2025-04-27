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

        <div style="text-align: right; margin-top: 10px;">
            <button type="submit">Adicionar</button>
        </div>
        </form>
    </main>
    <script>
        document.querySelector('form').addEventListener('submit', function(event) {
            const inputs = document.querySelectorAll('input[type="number"]');
            let allZero = true;
            inputs.forEach(input => {
                if (parseInt(input.value) > 0) {
                    allZero = false;
                }
            });
            if (allZero) {
                alert("Nenhum lanche selecionado");
                event.preventDefault();
            }
        });
    </script>
</body>
</html>
<?php
mysqli_close($conn);
?>
