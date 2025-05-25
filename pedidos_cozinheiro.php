<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['nome'])) {
    header("Location: login_cozinheiro.php");
    exit();
}
include 'conexao.php';

$where_clauses = ["p.aceito = 1"]; // Only accepted orders
$params = [];

$where_sql = 'WHERE ' . implode(' AND ', $where_clauses);

$sql = "
    SELECT 
        p.numero_do_pedido,
        GROUP_CONCAT(
            CASE 
                WHEN ip.quantidade >= 2 THEN CONCAT(ip.produto, ' - X', ip.quantidade)
                ELSE ip.produto
            END
            SEPARATOR '<br>'
        ) AS produtos,
        p.valor,
        p.nome_cliente,
        p.aceito,
        p.status,
        p.data_pedido,
        GROUP_CONCAT(
            CASE 
                WHEN ip.observacao IS NOT NULL AND ip.observacao != '' 
                THEN CONCAT(ip.produto, ': ', ip.observacao) 
                ELSE NULL 
            END
            SEPARATOR '<br>'
        ) AS observacao
    FROM pedido p
    LEFT JOIN itens_pedido ip ON p.numero_do_pedido = ip.numero_do_pedido
    $where_sql
    GROUP BY p.numero_do_pedido, p.valor, p.nome_cliente, p.aceito, p.status, p.data_pedido
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pedidos Cozinheiro - Cali Burger</title>
    <link rel="stylesheet" href="main.css">
    <style>
        .container {
            max-width: 1200px !important;
        }
        input[type="datetime-local"]:not(:placeholder-shown) {
            color: black;
        }
        input[type="datetime-local"]:hover,
        input[type="datetime-local"]:focus {
            border-color: initial;
            outline-color: initial;
        }
        nav a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
            transition: background-color 0.3s;
            margin-right: 30px;
        }
        nav a:hover {
            background-color: #c0392b;
        }
        nav .logout {
            background-color: #ffffff22;
            border: 1px solid white;
        }
        header, nav {
            display: flex;
            align-items: center;
            padding: 20px;
            background-color: #c0392b;
            color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .logo-area {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logo-area img {
            height: 60px;
            width: auto;
        }
        .logo-area h1 {
            font-size: 1.8em;
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
    <a href="pedidos_cozinheiro.php">Pedidos</a>
    <a href="cardapio_cozinheiro.php">Cardápio</a>
    <a href="sair.php" class="logout">Sair</a>
</nav>

<div class="container">
    <h2>🍔 Pedidos Aceitos</h2>  
    <table class="styled-table">
        <thead>
            <tr>
            <th>Nº Pedido</th>
            <th>Produto</th>
            <th>Valor (R$)</th>
            <th>Cliente</th>
            <th>Aceito</th>
            <th>Status</th>
            <th>Data e Hora</th>
            <th>Observação</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                <td><?= $row['numero_do_pedido'] ?></td>
                <td><?= $row['produtos'] ?></td>
                <td><?= number_format($row['valor'], 2, ',', '.') ?></td>
                <td><?= htmlspecialchars($row['nome_cliente']) ?></td>
                <td><?= $row['aceito'] ? 'Sim' : 'Não' ?></td>
                <td><?= htmlspecialchars($row['status'] === 'Cancelado/Recusado' ? 'Cancelado/recusado' : $row['status']) ?></td>
                <td><?= date('d/m/Y H:i:s', strtotime($row['data_pedido'])) ?></td>
                <td><?= !empty($row['observacao']) ? $row['observacao'] : 'Ø' ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
