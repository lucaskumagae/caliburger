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
    <h2>Bem-vindo <?php echo $_SESSION['nome']; ?>!</h2>
    <p>Gerencie os usuários abaixo:</p>

    <form action="adiciona_usuario.php" method="POST" class="form-inline">
        <input type="text" name="login" placeholder="Login" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="cpf" placeholder="CPF" required>
        <input type="date" name="data_nasc" placeholder="Data de Nascimento" required>
        <input type="text" name="end_estado" placeholder="Estado" required>
        <input type="text" name="end_cidade" placeholder="Cidade" required>
        <input type="text" name="end_bairro" placeholder="Bairro" required>
        <input type="text" name="end_logradouro" placeholder="Logradouro" required>
        <input type="text" name="senha" placeholder="Senha" required>
        <button type="submit">Adicionar Usuário</button>
    </form>

    <table class="styled-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Login</th>
                <th>Email</th>
                <th>Endereço</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $usuarios->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['login'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['end_logradouro'] ?></td>
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
