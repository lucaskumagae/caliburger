<?php
session_start();
if (empty($_SESSION['carrinho'])) {
    header("Location: carrinho.php");
    exit();
}
include 'menu_cliente.php';

include 'conexao.php';

// Get logged-in user id from session
$user_id = isset($_SESSION['id_cliente']) ? $_SESSION['id_cliente'] : null;

$endereco = 'Endereço não cadastrado';
if ($user_id) {
    $stmt = $conn->prepare("SELECT end_estado, end_cidade, end_bairro, end_logradouro FROM cliente WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $endereco = $row['end_logradouro'] . ', ' . $row['end_bairro'] . ', ' . $row['end_cidade'] . ' - ' . $row['end_estado'];
    }
    $stmt->close();
}

// Calculate order details
$total = 0;
$itens_pedido = [];
foreach ($_SESSION['carrinho'] as $id => $quantidade) {
    $stmt = $conn->prepare("SELECT nome, preco FROM cardapio WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nome = $row['nome'];
        $preco = $row['preco'];
        $subtotal = $preco * $quantidade;
        $total += $subtotal;
        $itens_pedido[] = ['nome' => $nome, 'quantidade' => $quantidade, 'subtotal' => $subtotal];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Pagamento - Cali Burger</title>
    <link rel="stylesheet" href="main.css" />
    <style>
        .pagamento-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            font-family: 'Poppins', sans-serif;
            color: #333;
        }
        .pagamento-container h1 {
            text-align: center;
            color: #333333;
            margin-bottom: 20px;
            font-weight: 700;
        }
        .section {
            margin-bottom: 25px;
        }
        .section h2 {
            font-size: 1.2em;
            margin-bottom: 10px;
            color: #333333;
            border-bottom: 2px solid #333333;
            padding-bottom: 5px;
        }
        .address, .delivery-options, .order-details {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .order-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-details th, .order-details td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        .order-details th {
            background-color: #eafaf1;
        }
        .total {
            text-align: right;
            font-weight: 700;
            font-size: 1.1em;
            margin-top: 10px;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        button.confirm-payment {
            background-color: #27ae60;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 1.2em;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }
        button.confirm-payment:hover {
            background-color: #219150;
        }
        label {
            margin-right: 15px;
            font-weight: 600;
        }
        a.button-voltar, a.button-editar {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 25px;
            background-color: #333333;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            transition: background-color 0.3s ease;
        }
        a.button-voltar:hover, a.button-editar:hover {
            background-color: #555555;
        }
        .qr-code {
            display: block;
            margin: 15px auto;
            max-width: 200px;
        }
    </style>
</head>
<body>
    <main class="pagamento-container">
        <h1>Pagamento via PIX</h1>

        <div class="section address">
            <h2>Endereço de Entrega</h2>
            <p><?php echo htmlspecialchars($endereco); ?></p>
            <a href="editar_endereco.php" class="button-editar">Editar Endereço</a>
        </div>

        <form method="post" action="pedido_sucesso.php">

            <div class="section payment-options">
                <h2>PIX</h2>
                <img src="imagens/qrcode.png" alt="QR Code PIX" class="qr-code" />
            </div>

            <div class="section order-details">
                <h2>Detalhes do Pedido</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($itens_pedido as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['nome']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantidade']); ?></td>
                            <td>R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="total">Total: R$ <?php echo number_format($total, 2, ',', '.'); ?></p>
            </div>

            <button type="submit" name="confirmar_pagamento" class="confirm-payment">Confirmar Pagamento</button>
        </form>

        <a href="carrinho.php" class="button-voltar">Voltar ao carrinho</a>
    </main>
</body>
</html>
