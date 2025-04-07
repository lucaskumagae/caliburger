<?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit();
}
include 'conexao.php';
$result = $conn->query("SELECT * FROM cardapio");
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
    <table class="styled-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Descrição</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['nome'] ?></td>
                    <td><?= $row['descricao'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
