<?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit();
}
include 'conexao.php';
$result = $conn->query("SELECT * FROM pedido");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pedidos - Cali Burger</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
<header>
    <h1>🍔 Cali Burger - Pedidos</h1>
</header>

<nav>
    <a href="main.php">Início</a>
    <a href="pedidos.php">Pedidos</a>
    <a href="cardapio.php">Cardápio</a>
    <a href="estoque.php">Estoque</a>
    <a href="sair.php" class="logout">Sair</a>
</nav>

<div class="container">
    <h2>Pedidos Realizados</h2>
    <table>
        <tr>
            <th>Nº Pedido</th>
            <th>Produto</th>
            <th>Valor (R$)</th>
            <th>Cliente</th>
            <th>Aceito</th>
            <th>Observação</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['numero_do_pedido'] ?></td>
                <td><?= $row['produto'] ?></td>
                <td><?= number_format($row['valor'], 2, ',', '.') ?></td>
                <td><?= $row['nome_cliente'] ?></td>
                <td><?= $row['aceito'] ? 'Sim' : 'Não' ?></td>
                <td><?= $row['observacao'] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
