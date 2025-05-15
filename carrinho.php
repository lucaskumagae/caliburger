<?php
session_start();
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'conexao.php';

    // Save observations per item in session if provided
    if (isset($_POST['observacao'])) {
        $_SESSION['observacoes'] = $_POST['observacao'];
    }

    // Handle remove item action - decrement quantity by 1
    if (isset($_POST['remove_id'])) {
        $remove_id = $_POST['remove_id'];
        if (isset($_SESSION['carrinho'][$remove_id])) {
            $_SESSION['carrinho'][$remove_id]--;
            if ($_SESSION['carrinho'][$remove_id] <= 0) {
                unset($_SESSION['carrinho'][$remove_id]);
                if (isset($_SESSION['observacoes'][$remove_id])) {
                    unset($_SESSION['observacoes'][$remove_id]);
                }
            }
        }
    }

    // Finalize order
    if (isset($_POST['finalizar_pedido'])) {
        if (!empty($_SESSION['carrinho'])) {
            // Redirect to payment page instead of inserting order directly
            header("Location: pagamento.php");
            exit();
        }
    }

    // Save quantities in session if provided
    if (isset($_POST['quantidade'])) {
        $_SESSION['carrinho'] = array_filter($_POST['quantidade'], function($qty) {
            return $qty > 0;
        });
    }

    // Redirect to refresh the page and show updated cart
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
            echo '<form method="POST" action="carrinho.php">';
            echo '<ul>';
            $total = 0;
            foreach ($_SESSION['carrinho'] as $id => $quantidade) {
                // Query lanche name and price by id
                $stmt = $conn->prepare("SELECT nome, preco FROM cardapio WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $nome = $row['nome'];
                    $preco = $row['preco'];
                } else {
                    $nome = "Lanche desconhecido";
                    $preco = 0;
                }

                // Accumulate total price
                $total += $preco * $quantidade;

                // Get existing observation if any
                $observacao = '';
                if (isset($_SESSION['observacoes'][$id])) {
                    $observacao = $_SESSION['observacoes'][$id];
                }

                echo '<li>';
                echo htmlspecialchars($nome) . ' - Quantidade: ' . htmlspecialchars($quantidade);
                echo '<br />';
                echo '<label for="observacao_' . $id . '">Observação:</label> ';
                echo '<input type="text" id="observacao_' . $id . '" name="observacao[' . $id . ']" value="' . htmlspecialchars($observacao) . '" />';
echo ' <button type="submit" name="remove_id" value="' . $id . '">Remover</button>';
                echo '</li>';

                $stmt->close();
            }
            echo '</ul>';
            echo '<p class="total-pedido"><strong>Total do pedido: R$ ' . number_format($total, 2, ',', '.') . '</strong></p>';
            echo '<a href="cardapio_cliente.php" class="button-voltar">Voltar ao cardápio</a>';
            echo '<button type="submit" name="finalizar_pedido" value="1">Finalizar Pedido</button>';
            echo '</form>';
        } else {
            echo '<p>Seu carrinho está vazio.</p>';
        }
        ?>
        <!-- Removed duplicate Voltar ao cardápio button here -->
    </main>
</body>
</html>
