<?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit();
}
include 'conexao.php';

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$where_clauses = [];
$params = [];

if ($start_date !== '') {
    $where_clauses[] = "p.data_pedido >= ?";
    $params[] = $start_date;
}

if ($end_date !== '') {
    $where_clauses[] = "p.data_pedido <= ?";
    $params[] = $end_date;
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

$sql = "
    SELECT 
        ip.produto,
        SUM(ip.quantidade) AS quantidade_vendida,
        SUM(ip.valor) AS valor_total,
        DATE(p.data_pedido) AS data_venda
    FROM itens_pedido ip
    INNER JOIN pedido p ON ip.numero_do_pedido = p.numero_do_pedido
    $where_sql
    GROUP BY ip.produto, DATE(p.data_pedido)
    ORDER BY DATE(p.data_pedido) DESC, ip.produto
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
    <title>Relatório de Vendas - Cali Burger</title>
    <link rel="stylesheet" href="main.css">
    <style>
        .container {
            max-width: 1200px !important;
            margin-top: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        form {
            margin-bottom: 20px;
            display: flex;
            gap: 20px;
            justify-content: center;
            align-items: center;
        }
        label {
            font-weight: bold;
        }
        input[type="date"] {
            padding: 6px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        button {
            padding: 8px 16px;
            background-color: #28a745; /* Changed to match pedidos.php buttons */
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        table.styled-table {
            border-collapse: collapse;
            margin: 0 auto;
            font-size: 0.9em;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-width: 700px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
        }
        table.styled-table thead tr {
            background-color: #009879;
            color: #ffffff;
            text-align: center;
        }
        table.styled-table th,
        table.styled-table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: center;
        }
        table.styled-table tbody tr {
            border-bottom: 1px solid #ddd;
        }
        table.styled-table tbody tr:nth-of-type(even) {
            background-color: #f3f3f3;
        }
        table.styled-table tbody tr:last-of-type {
            border-bottom: 2px solid #c0392b;
        }
    </style>
</head>
<body>

<?php include 'menu.php'; ?>

<div class="container">
    <h2>Relatório de Vendas</h2>
    <form method="GET">
        <label for="start_date">Data Início:</label>
        <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">

        <label for="end_date">Data Fim:</label>
        <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">

        <button type="submit">Filtrar</button>
    </form>

    <table class="styled-table">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Quantidade Vendida</th>
                <th>Valor Unitário (R$)</th>
                <th>Valor Total (R$)</th>
                <th>Data da Venda</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['produto']) ?></td>
                        <td><?= $row['quantidade_vendida'] ?></td>
                        <td><?= number_format($row['valor_total'] / $row['quantidade_vendida'], 2, ',', '.') ?></td>
                        <td><?= number_format($row['valor_total'], 2, ',', '.') ?></td>
                        <td><?= date('d/m/Y', strtotime($row['data_venda'])) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Nenhum registro encontrado para o período selecionado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// Query for ingredients used in sales within date range
$ingredients_used_sql = "
    SELECT ci.id_ingrediente, e.nome_ingrediente AS nome, SUM(ci.quantidade_utilizada * ip.quantidade) AS total_usado
    FROM itens_pedido ip
    INNER JOIN pedido p ON ip.numero_do_pedido = p.numero_do_pedido
    INNER JOIN cardapio c ON c.nome = ip.produto
    INNER JOIN cardapio_ingrediente ci ON ci.id_cardapio = c.id
    INNER JOIN estoque e ON e.id_ingrediente = ci.id_ingrediente
    WHERE 1=1
";

$ingredients_params = [];
if ($start_date !== '') {
    $ingredients_used_sql .= " AND p.data_pedido >= ?";
    $ingredients_params[] = $start_date;
}
if ($end_date !== '') {
    $ingredients_used_sql .= " AND p.data_pedido <= ?";
    $ingredients_params[] = $end_date;
}

$ingredients_used_sql .= "
    GROUP BY ci.id_ingrediente, e.nome_ingrediente
    ORDER BY e.nome_ingrediente
";

if (count($ingredients_params) > 0) {
    $stmt_ing = $conn->prepare($ingredients_used_sql);
    $stmt_ing->bind_param(str_repeat('s', count($ingredients_params)), ...$ingredients_params);
    $stmt_ing->execute();
    $ingredients_used_result = $stmt_ing->get_result();
} else {
    $ingredients_used_result = $conn->query($ingredients_used_sql);
}

// Query for ingredients to buy (low stock)
$low_stock_threshold = 10; // example threshold
$ingredients_to_buy_sql = "
    SELECT e.id_ingrediente, e.nome_ingrediente AS nome, e.quantidade
    FROM estoque e
    WHERE e.quantidade <= ?
    ORDER BY e.quantidade ASC
";

$stmt_buy = $conn->prepare($ingredients_to_buy_sql);
$stmt_buy->bind_param("i", $low_stock_threshold);
$stmt_buy->execute();
$ingredients_to_buy_result = $stmt_buy->get_result();
?>

<div class="container" style="margin-top: 40px;">
    <h2>Ingredientes Usados</h2>
    <table class="styled-table">
        <thead>
            <tr>
                <th>Ingrediente</th>
                <th>Quantidade Usada</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($ingredients_used_result && $ingredients_used_result->num_rows > 0): ?>
                <?php while ($row = $ingredients_used_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nome']) ?></td>
                        <td><?= $row['total_usado'] ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2">Nenhum ingrediente usado encontrado para o período selecionado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="container" style="margin-top: 40px;">
    <h2>Ingredientes a Comprar (Estoque Baixo)</h2>
    <table class="styled-table">
        <thead>
            <tr>
                <th>Ingrediente</th>
                <th>Quantidade em Estoque</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($ingredients_to_buy_result && $ingredients_to_buy_result->num_rows > 0): ?>
                <?php while ($row = $ingredients_to_buy_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nome']) ?></td>
                        <td><?= $row['quantidade'] ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2">Nenhum ingrediente com estoque baixo.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
