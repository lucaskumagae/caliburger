<?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add') {
            $nome = $conn->real_escape_string($_POST['nome']);
            $descricao = $conn->real_escape_string($_POST['descricao']);
            $preco = floatval($_POST['preco']);
            $imagem = $conn->real_escape_string($_POST['imagem']);
            $categoria_id = intval($_POST['categoria']);
            $conn->query("INSERT INTO cardapio (nome, descricao, preco, imagem, categoria_id) VALUES ('$nome', '$descricao', $preco, '$imagem', $categoria_id)");
            $new_id = $conn->insert_id;

            if (isset($_POST['ingredientes']) && is_array($_POST['ingredientes'])) {
                foreach ($_POST['ingredientes'] as $id_ingrediente => $quantidade_utilizada) {
                    $quantidade_utilizada = intval($quantidade_utilizada);
                    if ($quantidade_utilizada > 0) {
                        $conn->query("INSERT INTO cardapio_ingrediente (id_cardapio, id_ingrediente, quantidade_utilizada) VALUES ($new_id, $id_ingrediente, $quantidade_utilizada)");
                    }
                }
            }

            $_SESSION['message'] = "Item adicionado com sucesso!";
            header("Location: cardapio.php");
            exit();
        } elseif ($action === 'delete') {
            $id = intval($_POST['id']);
            $conn->query("DELETE FROM cardapio_ingrediente WHERE id_cardapio = $id");
            $conn->query("DELETE FROM cardapio WHERE id = $id");
            $_SESSION['message'] = "Item excluído com sucesso!";
            header("Location: cardapio.php");
            exit();
        }
    }
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
    <title>Cardápio - Cali Burger</title>
    <link rel="stylesheet" href="main.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="icon" href="imagens/logo_cali_ico.png" type="image/png">
    <link rel="icon" href="imagens/logo_cali_ico.ico" type="image/x-icon" />
    <style>
        .container {
            max-width: 1200px !important;
        }
    </style>
</head>
<body>

<?php include 'menu.php'; ?>

<div class="container">
    <h2>🍟 Itens do Cardápio</h2>
    <button type="submit" onclick="window.location.href='adiciona_item_cardapio.php'">Adicionar Novo Item</button>

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
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td>
                        <?= $row['nome'] ?>
                        <?php if ($row['min_estoque'] !== null && $row['min_estoque'] < 30): ?>
                            <br><span style="color: red; font-weight: bold;">Esgotado</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $row['descricao'] ?></td>
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
                    <td>
                        <form method="GET" action="editar_cardapio.php" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn-delete" style="margin-right: 5px;">Editar</button>
                        </form>
                        <form method="POST" action="cardapio.php" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php
if (isset($_SESSION['message'])) {
    echo '<div class="message" style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0; border-radius: 5px;">' . htmlspecialchars($_SESSION['message']) . '</div>';
    unset($_SESSION['message']);
}
?>
</body>
</html>
