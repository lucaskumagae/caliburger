<?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login_cozinheiro.php");
    exit();
}
include 'conexao.php';

$categoria_result = $conn->query("SELECT id, nome FROM categoria ORDER BY nome");
$categoria_list = [];
while ($row = $categoria_result->fetch_assoc()) {
    $categoria_list[] = $row;
}

$ingredientes_result = $conn->query("SELECT id_ingrediente, nome_ingrediente FROM estoque ORDER BY nome_ingrediente");
$ingredientes_list = [];
while ($row = $ingredientes_result->fetch_assoc()) {
    $ingredientes_list[] = $row;
}

$zero_ingredients = [];
$zero_result = $conn->query("SELECT nome_ingrediente FROM estoque WHERE quantidade = 0");
while ($row = $zero_result->fetch_assoc()) {
    $zero_ingredients[] = $row['nome_ingrediente'];
}

if (count($zero_ingredients) > 0) {
    $conditions = [];
    foreach ($zero_ingredients as $ingredient) {
        $escaped_ingredient = $conn->real_escape_string($ingredient);
        $conditions[] = "c.descricao NOT LIKE '%$escaped_ingredient%'";
    }
    $where_clause = implode(' AND ', $conditions);
    $sql = "SELECT c.*, cat.nome AS categoria_nome, GROUP_CONCAT(e.nome_ingrediente ORDER BY e.nome_ingrediente SEPARATOR ', ') AS ingredientes,
            MIN(e.quantidade) AS min_estoque
                FROM cardapio c
                LEFT JOIN categoria cat ON c.categoria_id = cat.id
                LEFT JOIN cardapio_ingrediente ci ON c.id = ci.id_cardapio
                LEFT JOIN estoque e ON ci.id_ingrediente = e.id_ingrediente
                WHERE $where_clause
                GROUP BY c.id";
} else {
    $sql = "SELECT c.*, cat.nome AS categoria_nome, GROUP_CONCAT(e.nome_ingrediente ORDER BY e.nome_ingrediente SEPARATOR ', ') AS ingredientes,
            MIN(e.quantidade) AS min_estoque
                FROM cardapio c
                LEFT JOIN categoria cat ON c.categoria_id = cat.id
                LEFT JOIN cardapio_ingrediente ci ON c.id = ci.id_cardapio
                LEFT JOIN estoque e ON ci.id_ingrediente = e.id_ingrediente
                GROUP BY c.id";
}

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cardápio Cozinheiro - Cali Burger</title>
    <link rel="stylesheet" href="main.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="icon" href="imagens/logo_cali_ico.png" type="image/png">
    <link rel="icon" href="imagens/logo_cali_ico.ico" type="image/x-icon" />
    <style>
        .container {
            max-width: 1200px !important;
        }
        .btn-action, .btn-delete, .btn-edit, button[type="submit"] {
            display: none !important;
        }
    </style>
</head>
<body>

<?php include 'menu_cozinheiro.php'; ?>

<div class="container">
    <h2>🍟 Itens do Cardápio</h2>

    <table class="styled-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Categoria</th>
                <th>Ingredientes</th>
                <th>Preço</th>
                <th>Imagem</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td>
                        <?= htmlspecialchars($row['nome']) ?>
                        <?php if ($row['min_estoque'] !== null && $row['min_estoque'] < 30): ?>
                            <br><span style="color: red; font-weight: bold;">Esgotado</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['descricao']) ?></td>
                    <td><?= htmlspecialchars($row['categoria_nome']) ?></td>
                    <td><?= htmlspecialchars($row['ingredientes']) ?></td>
                    <td><?= number_format($row['preco'], 2, ',', '.') ?></td>
                    <td>
                        <?php if (!empty($row['imagem'])): ?>
                            <img src="imagens/lanches/<?= htmlspecialchars($row['imagem']) ?>" alt="<?= htmlspecialchars($row['nome']) ?>" style="max-width: 100px; max-height: 100px;">
                        <?php else: ?>
                            Sem imagem
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
