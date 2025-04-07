<?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit();
}
include 'conexao.php';
$result = $conn->query("SELECT * FROM estoque");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Estoque - Cali Burger</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
<header>
    <h1>🍔 Cali Burger - Estoque</h1>
</header>

<nav>
    <a href="main.php">Início</a>
    <a href="pedidos.php">Pedidos</a>
    <a href="cardapio.php">Cardápio</a>
    <a href="estoque.php">Estoque</a>
    <a href="sair.php" class="logout">Sair</a>
</nav>

<div class="container">
    <h2>Ingredientes em Estoque</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Ingrediente</th>
            <th>Quantidade</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id_ingrediente'] ?></td>
                <td><?= $row['nome_ingrediente'] ?></td>
                <td><?= $row['quantidade'] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
