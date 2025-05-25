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

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Item - Cardápio - Cali Burger</title>
    <link rel="stylesheet" href="main.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="icon" href="imagens/logo_cali_ico.png" type="image/png">
    <link rel="icon" href="imagens/logo_cali_ico.ico" type="image/x-icon" />
    </style>
</head>
<body>

<?php include 'menu.php'; ?>

<div class="container" style="max-width: 800px; margin: 50px auto; background-color: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center;">
    <h2 style="font-size: 2em; color: #c0392b; margin-bottom: 10px;">Adicionar Novo Item ao Cardápio</h2>

    <form method="POST" action="adiciona_item_cardapio.php" class="form-inline" id="add-item-form">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required>
        <label for="descricao">Descrição:</label>
        <input type="text" id="descricao" name="descricao">
        <label for="categoria">Categoria:</label>
        <select id="categoria" name="categoria" required>
            <option value="">Selecione a categoria</option>
            <?php foreach ($categoria_list as $categoria): ?>
                <option value="<?= $categoria['id'] ?>"><?= htmlspecialchars($categoria['nome']) ?></option>
            <?php endforeach; ?>
        </select>
        <label for="preco">Preço:</label>
        <input type="number" step="0.01" id="preco" name="preco" required>
        <label for="imagem">Imagem (nome do arquivo):</label>
        <input type="text" id="imagem" name="imagem" style="width: 100%;">
        <fieldset id="ingredientes-fieldset" style="display: none; flex-direction: column; gap: 10px; margin-top: 10px;">
            <legend>Ingredientes</legend>
            <div style="display: flex; flex-direction: column; gap: 10px; flex-grow: 1;">
                <?php foreach ($ingredientes_list as $ingrediente): ?>
                    <label style="display: flex; justify-content: space-between; width: 200px;">
                        <?= htmlspecialchars($ingrediente['nome_ingrediente']) ?>:
                        <input type="number" name="ingredientes[<?= $ingrediente['id_ingrediente'] ?>]" min="0" value="0" style="width: 60px;">
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>
    </form>
    <div style="display: flex; justify-content: center; gap: 20px; margin-top: 20px;">
        <button type="submit" form="add-item-form" class="btn btn-danger" style="width: 120px;">Salvar</button>
        <button type="button" class="btn btn-secondary" style="width: 120px;" onclick="window.location.href='cardapio.php'">Cancelar</button>
    </div>
</div>

<script>
    const categoriaSelect = document.getElementById('categoria');
    const ingredientesFieldset = document.getElementById('ingredientes-fieldset');

    function toggleIngredientes() {
        const selectedOption = categoriaSelect.options[categoriaSelect.selectedIndex];
        const selectedText = selectedOption.text.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, "");
        if (selectedText === 'lanche' || selectedText === 'lanches') {
            ingredientesFieldset.style.display = 'flex';
        } else {
            ingredientesFieldset.style.display = 'none';
        }
    }

    categoriaSelect.addEventListener('change', toggleIngredientes);

    window.addEventListener('DOMContentLoaded', () => {
        toggleIngredientes();
    });
</script>

<div id="confirmation-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <p id="modal-message">Confirma a ação?</p>
        <button id="confirm-btn">Confirmar</button>
        <button id="cancel-btn">Cancelar</button>
    </div>
</div>


</body>
</html>
