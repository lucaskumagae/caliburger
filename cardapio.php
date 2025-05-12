<?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit();
}
include 'conexao.php';

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
            $conn->query("INSERT INTO cardapio (nome, descricao, preco, imagem) VALUES ('$nome', '$descricao', $preco, '$imagem')");
            $new_id = $conn->insert_id;

            // Insert ingredients
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
        } elseif ($action === 'edit_form') {
            $id = intval($_POST['id']);
            $edit_result = $conn->query("SELECT * FROM cardapio WHERE id = $id");
            $edit_row = $edit_result->fetch_assoc();

            // Fetch ingredients for this cardapio item
            $ingredientes_cardapio = [];
            $ingredientes_result = $conn->query("SELECT id_ingrediente, quantidade_utilizada FROM cardapio_ingrediente WHERE id_cardapio = $id");
            while ($row = $ingredientes_result->fetch_assoc()) {
                $ingredientes_cardapio[$row['id_ingrediente']] = $row['quantidade_utilizada'];
            }
        } elseif ($action ===    'edit') {
            $id = intval($_POST['id']);
            $nome = $conn->real_escape_string($_POST['nome']);
            $descricao = $conn->real_escape_string($_POST['descricao']);
            $preco = floatval($_POST['preco']);
            $imagem = $conn->real_escape_string($_POST['imagem']);
            $conn->query("UPDATE cardapio SET nome='$nome', descricao='$descricao', preco=$preco, imagem='$imagem' WHERE id=$id");

            // Update ingredients
            $conn->query("DELETE FROM cardapio_ingrediente WHERE id_cardapio = $id");
            if (isset($_POST['ingredientes']) && is_array($_POST['ingredientes'])) {
                foreach ($_POST['ingredientes'] as $id_ingrediente => $quantidade_utilizada) {
                    $quantidade_utilizada = intval($quantidade_utilizada);
                    if ($quantidade_utilizada > 0) {
                        $conn->query("INSERT INTO cardapio_ingrediente (id_cardapio, id_ingrediente, quantidade_utilizada) VALUES ($id, $id_ingrediente, $quantidade_utilizada)");
                    }
                }
            }

            $_SESSION['message'] = "Item atualizado com sucesso!";
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
    $sql = "SELECT c.*, GROUP_CONCAT(e.nome_ingrediente ORDER BY e.nome_ingrediente SEPARATOR ', ') AS ingredientes
            FROM cardapio c
            LEFT JOIN cardapio_ingrediente ci ON c.id = ci.id_cardapio
            LEFT JOIN estoque e ON ci.id_ingrediente = e.id_ingrediente
            WHERE $where_clause
            GROUP BY c.id";
} else {
    $sql = "SELECT c.*, GROUP_CONCAT(e.nome_ingrediente ORDER BY e.nome_ingrediente SEPARATOR ', ') AS ingredientes
            FROM cardapio c
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
</head>
<body>

<?php include 'menu.php'; ?>

<div class="container">
    <h2>🍟 Itens do Cardápio</h2>
    <button onclick="document.getElementById('addForm').style.display='block'">Adicionar Novo Item</button>

    <div id="addForm" style="display:none; margin-bottom: 20px;">
            <form method="POST" action="cardapio.php">
                <input type="hidden" name="action" value="add">
                <label>Nome: <input type="text" name="nome" required></label>
                <label>Descrição: <input type="text" name="descricao"></label>
                <label>Preço: <input type="number" step="0.01" name="preco" required></label>
                <label>Imagem (nome do arquivo): <input type="text" name="imagem"></label>
                <fieldset>
                    <legend>Ingredientes</legend>
                    <?php foreach ($ingredientes_list as $ingrediente): ?>
                        <label>
                            <?= htmlspecialchars($ingrediente['nome_ingrediente']) ?>:
                            <input type="number" name="ingredientes[<?= $ingrediente['id_ingrediente'] ?>]" min="0" value="0" style="width: 60px;">
                        </label><br>
                    <?php endforeach; ?>
                </fieldset>
                <button type="submit">Salvar</button>
                <button type="button" onclick="document.getElementById('addForm').style.display='none'">Cancelar</button>
            </form>
    </div>

    <table class="styled-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Descrição</th>
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
                    <td><?= $row['nome'] ?></td>
                    <td><?= $row['descricao'] ?></td>
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
                        <form method="POST" action="cardapio.php" style="display:inline;">
                            <input type="hidden" name="action" value="edit_form">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit">Editar</button>
                        </form>
                        <form method="POST" action="cardapio.php" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este item?');">
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

<?php if (isset($edit_row)): ?>
    <div id="editForm" style="margin: 20px auto; width: 300px; border: 1px solid #ccc; padding: 15px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <form method="POST" action="cardapio.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $edit_row['id'] ?>">
            <label>Nome: <input type="text" name="nome" value="<?= htmlspecialchars($edit_row['nome']) ?>" required></label>
            <label>Descrição: <input type="text" name="descricao" value="<?= htmlspecialchars($edit_row['descricao']) ?>"></label>
            <label>Preço: <input type="number" step="0.01" name="preco" value="<?= number_format($edit_row['preco'], 2, '.', '') ?>" required></label>
            <label>Imagem (nome do arquivo): <input type="text" name="imagem" value="<?= htmlspecialchars($edit_row['imagem']) ?>"></label>
            <fieldset>
                <legend>Ingredientes</legend>
                <?php foreach ($ingredientes_list as $ingrediente): ?>
                    <?php
                        $id_ing = $ingrediente['id_ingrediente'];
                        $quant = isset($ingredientes_cardapio[$id_ing]) ? $ingredientes_cardapio[$id_ing] : 0;
                    ?>
                    <label>
                        <?= htmlspecialchars($ingrediente['nome_ingrediente']) ?>:
                        <input type="number" name="ingredientes[<?= $id_ing ?>]" min="0" value="<?= $quant ?>" style="width: 60px;">
                    </label><br>
                <?php endforeach; ?>
            </fieldset>
            <button type="submit">Atualizar</button>
            <button type="button" onclick="window.location.href='cardapio.php'">Cancelar</button>
        </form>
    </div>
<?php endif; ?>

<?php
if (isset($_SESSION['message'])) {
    echo "<script>alert('" . addslashes($_SESSION['message']) . "');</script>";
    unset($_SESSION['message']);
}
?>
</body>
</html>
