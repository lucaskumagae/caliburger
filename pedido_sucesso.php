<?php
session_start();
include 'conexao.php';
include 'menu_cliente.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_pagamento'])) {
    if (!empty($_SESSION['carrinho'])) {
        $nome_cliente = isset($_SESSION['nome']) ? $_SESSION['nome'] : 'Cliente Desconhecido';

        $observacao_pedido = '';
        if (isset($_SESSION['observacao_pedido'])) {
            $observacao_pedido = $_SESSION['observacao_pedido'];
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

        // Insert order record in pedido table with observation, total value, and status
        $insert_order_stmt = $conn->prepare("INSERT INTO pedido (nome_cliente, aceito, observacao, valor, status) VALUES (?, ?, ?, ?, ?)");
        $aceito = 1;
        $status = "Concluído";
        $insert_order_stmt->bind_param("sisds", $nome_cliente, $aceito, $observacao_pedido, $total_pedido, $status);
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
    } else {
        // No cart found, redirect to cart page
        header("Location: carrinho.php");
        exit();
    }
} else {
    // Accessed without payment confirmation, redirect to cart
    header("Location: carrinho.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Pedido Realizado</title>
    <link rel="stylesheet" href="main.css" />
    <style>
        .success-message {
            max-width: 400px;
            margin: 100px auto;
            padding: 40px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            text-align: center;
            font-family: 'Poppins', sans-serif;
            color: #27ae60;
            font-size: 1.5em;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <main class="success-message">
        Pedido realizado com sucesso!
    </main>
</body>
</html>
