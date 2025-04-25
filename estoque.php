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
                        <form action="deleta_ingrediente.php" method="POST" onsubmit="return confirm('Deseja excluir este ingrediente?');">
                            <input type="hidden" name="id" value="<?= $row['id_ingrediente'] ?>">
                            <button type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>