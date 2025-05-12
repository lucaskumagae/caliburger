<?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit();
}

include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        $_SESSION['msg_error'] = "ID do ingrediente não fornecido.";
        header("Location: estoque.php");
        exit();
    }

    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM estoque WHERE id_ingrediente = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $_SESSION['msg_error'] = "Ingrediente não encontrado.";
        header("Location: estoque.php");
        exit();
    }
    $ingrediente = $result->fetch_assoc();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cancelar'])) {
        header("Location: estoque.php");
        exit();
    }

    if (!isset($_POST['id']) || empty($_POST['id']) || !isset($_POST['nome_ingrediente']) || !isset($_POST['quantidade'])) {
        $_SESSION['msg_error'] = "Dados incompletos para atualização.";
        header("Location: estoque.php");
        exit();
    }

    $id = intval($_POST['id']);
    $nome = trim($_POST['nome_ingrediente']);
    $quantidade = intval($_POST['quantidade']);

    if ($nome === '' || $quantidade < 1) {
        $_SESSION['msg_error'] = "Nome do ingrediente inválido ou quantidade menor que 1.";
        header("Location: edita_ingrediente.php?id=$id");
        exit();
    }

    $stmt = $conn->prepare("UPDATE estoque SET nome_ingrediente = ?, quantidade = ? WHERE id_ingrediente = ?");
    $stmt->bind_param("sii", $nome, $quantidade, $id);
    if ($stmt->execute()) {
        $_SESSION['msg_success'] = "Ingrediente atualizado com sucesso.";
        header("Location: estoque.php");
        exit();
    } else {
        $_SESSION['msg_error'] = "Erro ao atualizar ingrediente.";
        header("Location: edita_ingrediente.php?id=$id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Ingrediente - Cali Burger</title>
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="botoes_estoque.css">
</head>
<body>

<?php include 'menu.php'; ?>

<div class="container">
    <h2>Editar Ingrediente</h2>

    <?php
    if (isset($_SESSION['msg_error'])) {
        echo '<p class="error-message">' . $_SESSION['msg_error'] . '</p>';
        unset($_SESSION['msg_error']);
    }
    ?>

    <form action="edita_ingrediente.php" method="POST" class="form-inline" id="edit-ingredient-form">
        <input type="hidden" name="id" value="<?= htmlspecialchars($ingrediente['id_ingrediente']) ?>">
        <label for="nome_ingrediente">Ingrediente:</label>
        <input type="text" id="nome_ingrediente" name="nome_ingrediente" value="<?= htmlspecialchars($ingrediente['nome_ingrediente']) ?>" required>
        <label for="quantidade">Quantidade:</label>
        <input type="number" id="quantidade" name="quantidade" value="<?= htmlspecialchars($ingrediente['quantidade']) ?>" required min="1">
        <button type="submit" id="save-btn">Salvar</button>
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
    const saveBtn = document.getElementById('save-btn');
    const form = document.getElementById('edit-ingredient-form');

    form.addEventListener('submit', (event) => {
        if (event.submitter && event.submitter.name === 'cancelar') {
            // Allow form to submit immediately if "Cancelar" button was clicked
            return;
        }
        event.preventDefault();
        modalMessage.textContent = 'Deseja salvar as alterações deste ingrediente?';
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
