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
            $nome_cliente = isset($_SESSION['nome']) ? $_SESSION['nome'] : 'Cliente Desconhecido';

            $observacao_pedido = '';
            if (isset($_POST['observacao_pedido'])) {
                $observacao_pedido = $_POST['observacao_pedido'];
            }

            // Calculate total order value
            $total_pedido = 0;
            foreach ($_SESSION['carrinho'] as $id => $quantidade) {
                $stmt = $conn->prepare("SELECT preco FROM cardapio WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $preco = $result->fetch_assoc()['preco'];
                    $total_pedido += $preco * $quantidade;
                }
                $stmt->close();
            }

            // Insert order record in pedido table with observation and total value
            $insert_order_stmt = $conn->prepare("INSERT INTO pedido (nome_cliente, aceito, observacao, valor) VALUES (?, ?, ?, ?)");
            $aceito = 1;
            $insert_order_stmt->bind_param("sisd", $nome_cliente, $aceito, $observacao_pedido, $total_pedido);
            if (!$insert_order_stmt->execute()) {
                $insert_order_stmt->close();
                die("Erro ao inserir pedido no banco de dados.");
            }
            $numero_do_pedido = $conn->insert_id;
            $insert_order_stmt->close();

            // Insert each item in itens_pedido table
            foreach ($_SESSION['carrinho'] as $id => $quantidade) {
                // Get product name and price
                $stmt = $conn->prepare("SELECT nome, preco FROM cardapio WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $produto = $row['nome'];
                    $valor = $row['preco'] * $quantidade;
                } else {
                    $produto = "Produto desconhecido";
                    $valor = 0;
                }
                $stmt->close();

                $observacao = '';
                if (isset($_SESSION['observacoes'][$id])) {
                    $observacao = $_SESSION['observacoes'][$id];
                }

                // Insert item into itens_pedido table
                $insert_item_stmt = $conn->prepare("INSERT INTO itens_pedido (numero_do_pedido, produto, quantidade, valor, observacao) VALUES (?, ?, ?, ?, ?)");
                $insert_item_stmt->bind_param("isids", $numero_do_pedido, $produto, $quantidade, $valor, $observacao);
                if (!$insert_item_stmt->execute()) {
                    $insert_item_stmt->close();
                    die("Erro ao inserir item do pedido no banco de dados.");
                }
                $insert_item_stmt->close();
            }

            // Clear cart and observations
            unset($_SESSION['carrinho']);
            unset($_SESSION['observacoes']);

            // Redirect to success page
            header("Location: pedido_sucesso.php");
            exit;
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
