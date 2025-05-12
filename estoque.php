<?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit();
}
include 'conexao.php';
$result = $conn->query("SELECT * FROM estoque");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Estoque - Cali Burger</title>
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="botoes_estoque.css">
</head>
<body>

<?php include 'menu.php'; ?>

<?php
if (isset($_SESSION['msg_success'])) {
    echo '<p class="success-message">' . $_SESSION['msg_success'] . '</p>';
    unset($_SESSION['msg_success']);
}
if (isset($_SESSION['msg_error'])) {
    echo '<p class="error-message">' . $_SESSION['msg_error'] . '</p>';
    unset($_SESSION['msg_error']);
}
?>

<div class="container">
    <h2>🧾 Ingredientes em Estoque</h2>

    <form action="adiciona_ingrediente.php" method="POST" class="form-inline">
        <input type="text" name="nome_ingrediente" placeholder="Ingrediente" required>
        <input type="number" name="quantidade" placeholder="Quantidade" required min="1">
        <button type="submit">Adicionar Ingrediente</button>
    </form>

    <table class="styled-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Ingrediente</th>
                <th>Quantidade</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id_ingrediente'] ?></td>
                    <td><?= $row['nome_ingrediente'] ?></td>
                    <td><?= $row['quantidade'] ?></td>
                    <td>
                        <form id="delete-form-<?= $row['id_ingrediente'] ?>" action="deleta_ingrediente.php" method="POST" style="display:inline-block; margin-right: 5px;">
                            <input type="hidden" name="id" value="<?= $row['id_ingrediente'] ?>">
                            <button type="button" class="btn-delete" data-id="<?= $row['id_ingrediente'] ?>">Excluir</button>
                        </form>
                        <form id="edit-form-<?= $row['id_ingrediente'] ?>" action="edita_ingrediente.php" method="GET" style="display:inline-block;">
                            <input type="hidden" name="id" value="<?= $row['id_ingrediente'] ?>">
                            <button type="submit" class="btn-edit" data-id="<?= $row['id_ingrediente'] ?>">Editar</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
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

    let currentForm = null;

    document.querySelectorAll('.btn-delete').forEach(button => {
        if (button.closest('form').id.startsWith('delete-form-')) {
            button.addEventListener('click', () => {
                const id = button.getAttribute('data-id');
                currentForm = document.getElementById('delete-form-' + id);
                modalMessage.textContent = 'Deseja excluir este ingrediente?';
                modal.style.display = 'block';
            });
        }
    });

    confirmBtn.addEventListener('click', () => {
        if (currentForm) {
            currentForm.submit();
        }
        modal.style.display = 'none';
    });

    cancelBtn.addEventListener('click', () => {
        modal.style.display = 'none';
        currentForm = null;
    });

    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
            currentForm = null;
        }
    });
</script>

</body>
</html>
