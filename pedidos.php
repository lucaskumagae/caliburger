 <?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit();
}
include 'conexao.php';
$result = $conn->query("
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
    GROUP BY p.numero_do_pedido, p.valor, p.nome_cliente, p.aceito
");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pedidos - Cali Burger</title>
    <link rel="stylesheet" href="main.css">
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
                <td><?= $row['produtos'] ?></td>
                <td><?= number_format($row['valor'], 2, ',', '.') ?></td>
                <td><?= htmlspecialchars($row['nome_cliente']) ?></td>
                <td><?= $row['aceito'] ? 'Sim' : 'Não' ?></td>
                <td><?= $row['observacao'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
