<?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit();
}
include 'conexao.php';

// Pede todos os campos da tabela cliente
$usuarios = $conn->query("SELECT * FROM cliente");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Página Principal - Cali Burger</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>

<?php include 'menu.php'; ?>

<div class="container">
    <h2>Bem-vindo(a), <?php echo $_SESSION['nome']; ?>!</h2>
    <p>Gerencie os usuários abaixo:</p>

    <form action="adiciona_usuario.php" method="POST" class="form-inline">
        <input type="text" name="nome" placeholder="Nome" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="senha" placeholder="Senha" required>
        <button type="submit">Adicionar Usuário</button>
    </form>

    <table class="styled-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Senha (Texto)</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $usuarios->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['nome'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['senha'] ?></td>
                    <td>
                        <form action="deleta_usuario.php" method="POST" onsubmit="return confirm('Deseja excluir este usuário?');">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
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
