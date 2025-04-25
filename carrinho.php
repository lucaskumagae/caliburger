<?php
session_start();
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantidade'])) {
    // Save quantities in session
    $_SESSION['carrinho'] = array_filter($_POST['quantidade'], function($qty) {
        return $qty > 0;
    });

    // Redirect to a cart display page or back to menu
    header('Location: carrinho.php');
    exit;
}

// Display cart contents
?>
<?php
include 'menu_cliente.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Carrinho</title>
    <link rel="stylesheet" href="main.css" />
</head>
<body>
    <main class="carrinho-container">
        <h1>Seu Carrinho</h1>
        <?php
        if (!empty($_SESSION['carrinho'])) {
            echo '<ul>';
            foreach ($_SESSION['carrinho'] as $id => $quantidade) {
                // Query lanche name by id
                $stmt = $conn->prepare("SELECT nome FROM cardapio WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $nome = $result->num_rows > 0 ? $result->fetch_assoc()['nome'] : "Lanche desconhecido";

                echo '<li>' . htmlspecialchars($nome) . ' - Quantidade: ' . htmlspecialchars($quantidade) . '</li>';

                $stmt->close();
            }
            echo '</ul>';
        } else {
            echo '<p>Seu carrinho está vazio.</p>';
        }
        ?>
        <a href="cardapio_cliente.php">Voltar ao cardápio</a>
    </main>
</body>
</html>
