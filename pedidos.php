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
    <link rel="stylesheet" href="main.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="icon" href="imagens/logo_cali_ico.png" type="image/png">
    <link rel="icon" href="imagens/logo_cali_ico.ico" type="image/x-icon" />
</head>
<body>

<?php include 'menu.php'; ?>

<div class="container">
    <h2>🍔 Pedidos Realizados</h2>
    <table class="styled-table">
        <thead>
            <tr>
                <th>Nº Pedido</th>
                <th>Produto</th>
                <th>Valor (R$)</th>
                <th>Cliente</th>
                <th>Aceito</th>
                <th>Observação</th>
            </tr>
        </thead>
        <tbody>
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
        </tbody>
    </table>
</div>

</body>
</html>
