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

        foreach ($_SESSION['carrinho'] as $id => $quantidade) {
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

            $insert_item_stmt = $conn->prepare("INSERT INTO itens_pedido (numero_do_pedido, produto, quantidade, valor, observacao) VALUES (?, ?, ?, ?, ?)");
            $insert_item_stmt->bind_param("isids", $numero_do_pedido, $produto, $quantidade, $valor, $observacao);
            if (!$insert_item_stmt->execute()) {
                $insert_item_stmt->close();
                die("Erro ao inserir item do pedido no banco de dados.");
            }
            $insert_item_stmt->close();

            $ingredientes_stmt = $conn->prepare("SELECT id_ingrediente, quantidade_utilizada FROM cardapio_ingrediente WHERE id_cardapio = ?");
            $ingredientes_stmt->bind_param("i", $id);
            $ingredientes_stmt->execute();
            $ingredientes_result = $ingredientes_stmt->get_result();
            while ($ingrediente = $ingredientes_result->fetch_assoc()) {
                $id_ingrediente = $ingrediente['id_ingrediente'];
                $quantidade_utilizada = $ingrediente['quantidade_utilizada'] * $quantidade;

                $update_estoque_stmt = $conn->prepare("UPDATE estoque SET quantidade = quantidade - ? WHERE id_ingrediente = ? AND quantidade >= ?");
                $update_estoque_stmt->bind_param("iii", $quantidade_utilizada, $id_ingrediente, $quantidade_utilizada);
                $update_estoque_stmt->execute();
                if ($update_estoque_stmt->affected_rows === 0) {
                    $update_estoque_stmt->close();
                    die('Estoque insuficiente para o ingrediente ID: ' . $id_ingrediente);
                }
                $update_estoque_stmt->close();
            }
            $ingredientes_stmt->close();
        }

        unset($_SESSION['carrinho']);
        unset($_SESSION['observacoes']);
    } else {
        header("Location: carrinho.php");
        exit();
    }
} else {
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
