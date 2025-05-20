 <?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit();
}
include 'conexao.php';

$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';

$where_clauses = [];
$params = [];

if ($status_filter !== '') {
    $where_clauses[] = "p.status = ?";
    $params[] = $status_filter;
}

if ($date_filter !== '') {
    $where_clauses[] = "p.data_pedido >= ?";
    $params[] = $date_filter;
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

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

if (count($params) > 0) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pedidos - Cali Burger</title>
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
    </style>
</head>
<body>

<?php include 'menu.php'; ?>

<div class="container">
    <h2>🍔 Pedidos Realizados</h2>  
    <form method="GET" style="margin-bottom: 20px; display: flex; gap: 20px; align-items: center; justify-content: center;">
        <label for="status_filter">Status do Pedido:</label>
        <select name="status_filter" id="status_filter">
            <option value="" <?= (!isset($_GET['status_filter']) || $_GET['status_filter'] === '') ? 'selected' : '' ?>>Todos</option>
            <option value="Recusado" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Cancelado/recusado') ? 'selected' : '' ?>>Cancelado/Recusado</option>
            <option value="Em preparação" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Em preparação') ? 'selected' : '' ?>>Em preparação</option>
            <option value="A caminho" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] === 'A caminho') ? 'selected' : '' ?>>A caminho</option>
            <option value="Concluído" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Concluído') ? 'selected' : '' ?>>Concluído</option>
        </select>

        <label for="date_filter">Data e Hora (a partir de):</label>
        <input type="datetime-local" name="date_filter" id="date_filter" value="<?= isset($_GET['date_filter']) ? htmlspecialchars($_GET['date_filter']) : '' ?>">

        <button type="submit">Filtrar</button>
        <a href="relatorio.php" style="padding: 8px 16px; background-color: #c0392b; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">Relatório de Vendas</a>
    </form>
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
