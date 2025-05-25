<?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit();
}

include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        $_SESSION['message'] = "ID do item do cardápio não fornecido.";
        header("Location: cardapio.php");
        exit();
    }

    $id = intval($_GET['id']);

    // Prepare statement to fetch cardapio item
    $stmt = $conn->prepare("SELECT * FROM cardapio WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $_SESSION['message'] = "Item do cardápio não encontrado.";
        header("Location: cardapio.php");
        exit();
    }
    $edit_row = $result->fetch_assoc();

    // Fetch ingredientes associated with this cardapio item
    $ingredientes_cardapio = [];
    $stmt_ing = $conn->prepare("SELECT id_ingrediente, quantidade_utilizada FROM cardapio_ingrediente WHERE id_cardapio = ?");
    $stmt_ing->bind_param("i", $id);
    $stmt_ing->execute();
    $result_ing = $stmt_ing->get_result();
    while ($row = $result_ing->fetch_assoc()) {
        $ingredientes_cardapio[$row['id_ingrediente']] = $row['quantidade_utilizada'];
    }

    // Fetch categorias for dropdown
    $categoria_result = $conn->query("SELECT id, nome FROM categoria ORDER BY nome");
    $categoria_list = [];
    while ($row = $categoria_result->fetch_assoc()) {
        $categoria_list[] = $row;
    }

    // Fetch ingredientes for form
    $ingredientes_result = $conn->query("SELECT id_ingrediente, nome_ingrediente FROM estoque ORDER BY nome_ingrediente");
    $ingredientes_list = [];
    while ($row = $ingredientes_result->fetch_assoc()) {
        $ingredientes_list[] = $row;
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cancelar'])) {
        header("Location: cardapio.php");
        exit();
    }

    if (!isset($_POST['id']) || empty($_POST['id']) || !isset($_POST['nome']) || !isset($_POST['preco']) || !isset($_POST['categoria'])) {
        $_SESSION['message'] = "Dados incompletos para atualização.";
        header("Location: cardapio.php");
        exit();
    }

    $id = intval($_POST['id']);
    $nome = trim($_POST['nome']);
    $descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
    $preco = floatval($_POST['preco']);
    $imagem = isset($_POST['imagem']) ? trim($_POST['imagem']) : '';
    $categoria_id = intval($_POST['categoria']);

    if ($nome === '' || $preco <= 0 || $categoria_id <= 0) {
        $_SESSION['message'] = "Nome, preço e categoria são obrigatórios e devem ser válidos.";
        header("Location: editar_cardapio.php?id=$id");
        exit();
    }

    // Update cardapio item
    $stmt = $conn->prepare("UPDATE cardapio SET nome = ?, descricao = ?, preco = ?, imagem = ?, categoria_id = ? WHERE id = ?");
    $stmt->bind_param("ssdssi", $nome, $descricao, $preco, $imagem, $categoria_id, $id);
    if (!$stmt->execute()) {
        $_SESSION['message'] = "Erro ao atualizar item do cardápio.";
        header("Location: editar_cardapio.php?id=$id");
        exit();
    }

    // Delete existing ingredientes for this item
    $stmt_del = $conn->prepare("DELETE FROM cardapio_ingrediente WHERE id_cardapio = ?");
    $stmt_del->bind_param("i", $id);
    $stmt_del->execute();

    // Insert new ingredientes with quantity > 0
    if (isset($_POST['ingredientes']) && is_array($_POST['ingredientes'])) {
        $stmt_ins = $conn->prepare("INSERT INTO cardapio_ingrediente (id_cardapio, id_ingrediente, quantidade_utilizada) VALUES (?, ?, ?)");
        foreach ($_POST['ingredientes'] as $id_ingrediente => $quantidade_utilizada) {
            $quantidade_utilizada = intval($quantidade_utilizada);
            $id_ingrediente = intval($id_ingrediente);
            if ($quantidade_utilizada > 0) {
                $stmt_ins->bind_param("iii", $id, $id_ingrediente, $quantidade_utilizada);
                $stmt_ins->execute();
            }
        }
        $stmt_ins->close();
    }

    $_SESSION['message'] = "Item atualizado com sucesso!";
    header("Location: cardapio.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Item do Cardápio - Cali Burger</title>
    <link rel="stylesheet" href="main.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
<style>
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            font-family: 'Poppins', sans-serif;
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
        input[type="text"], input[type="number"], select {
            width: 100%;
            padding: 6px;
            margin-top: 4px;
            box-sizing: border-box;
        }
        fieldset {
            margin-top: 15px;
            padding: 10px;
        }
        button {
            margin-top: 15px;
            padding: 8px 12px;
            font-size: 16px;
            cursor: pointer;
        }
        .message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        /* Modal styles to overlay the screen */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 300px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.25);
        }
    </style>
</head>
<body>

<?php include 'menu.php'; ?>

<div class="container">
    <h2>Editar Item do Cardápio</h2>

    <?php
    if (isset($_SESSION['message'])) {
        echo '<div class="message">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message']);
    }
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <form method="POST" action="editar_cardapio.php" id="edit-cardapio-form">
        <input type="hidden" name="id" value="<?= htmlspecialchars($edit_row['id']) ?>">
        <label>Nome:
            <input type="text" name="nome" value="<?= htmlspecialchars($edit_row['nome']) ?>" required>
        </label>
        <label>Descrição:
            <input type="text" name="descricao" value="<?= htmlspecialchars($edit_row['descricao']) ?>">
        </label>
        <label>Categoria:
            <select name="categoria" required>
                <option value="">Selecione a categoria</option>
                <?php foreach ($categoria_list as $categoria): ?>
                    <option value="<?= $categoria['id'] ?>" <?= ($edit_row['categoria_id'] == $categoria['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($categoria['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Preço:
            <input type="number" step="0.01" name="preco" value="<?= number_format($edit_row['preco'], 2, '.', '') ?>" required>
        </label>
        <label>Imagem (nome do arquivo):
            <input type="text" name="imagem" value="<?= htmlspecialchars($edit_row['imagem']) ?>">
        </label>
        <fieldset>
            <legend>Ingredientes</legend>
            <?php foreach ($ingredientes_list as $ingrediente): 
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
        <button type="submit" name="cancelar">Cancelar</button>
    </form>
</div>

<div id="confirmation-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <p id="modal-message">Confirma a ação?</p>
        <button id="confirm-btn">Confirmar</button>
        <button id="cancel-btn">Cancelar</button>
    </div>
</div>

<script>
    const modal = document.getElementById('confirmation-modal');
    const modalMessage = document.getElementById('modal-message');
    const confirmBtn = document.getElementById('confirm-btn');
    const cancelBtn = document.getElementById('cancel-btn');
    const form = document.getElementById('edit-cardapio-form');

    form.addEventListener('submit', (event) => {
        if (event.submitter && event.submitter.name === 'cancelar') {
            return;
        }
        event.preventDefault();
        modalMessage.textContent = 'Deseja salvar as alterações deste item do cardápio?';
        modal.style.display = 'block';
    });

    confirmBtn.addEventListener('click', () => {
        form.submit();
        modal.style.display = 'none';
    });

    cancelBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
</script>

</body>
</html>
