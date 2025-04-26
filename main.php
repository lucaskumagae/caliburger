<?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit();
}
include 'conexao.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required_fields = ['login', 'email', 'cpf', 'data_nasc', 'end_estado', 'end_cidade', 'end_bairro', 'end_logradouro', 'senha'];
    $missing_fields = false;
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields = true;
            break;
        }
    }

    if ($missing_fields) {
        $message = '<p style="color:red;">Por favor, preencha todos os campos obrigatórios.</p>';
    } else {
        $login = $_POST['login'];
        $email = $_POST['email'];
        $cpf = $_POST['cpf'];
        $data_nasc = $_POST['data_nasc'];
        $end_estado = $_POST['end_estado'];
        $end_cidade = $_POST['end_cidade'];
        $end_bairro = $_POST['end_bairro'];
        $end_logradouro = $_POST['end_logradouro'];
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO cliente (login, email, cpf, data_nasc, end_estado, end_cidade, end_bairro, end_logradouro, senha) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $login, $email, $cpf, $data_nasc, $end_estado, $end_cidade, $end_bairro, $end_logradouro, $senha);

        if ($stmt->execute()) {
            $message = '<p style="color:green;">Usuário adicionado com sucesso!</p>';
        } else {
            $message = '<p style="color:red;">Erro ao adicionar usuário: ' . $stmt->error . '</p>';
        }
    }
}

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

    <form action="" method="POST" class="form-inline">
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
    <?php echo $message; ?>
</div>

</body>
</html>
