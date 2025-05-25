<?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit();
}
include 'conexao.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required_fields = ['cpf', 'nome', 'data_nasc', 'email', 'sexo', 'senha'];
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
        $cpf = $_POST['cpf'];
        $nome = $_POST['nome'];
        $data_nasc = $_POST['data_nasc'];
        $email = $_POST['email'];
        $sexo = $_POST['sexo'];
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO balconista_dono (cpf, nome, data_nasc, email, sexo, senha) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $cpf, $nome, $data_nasc, $email, $sexo, $senha);

        if ($stmt->execute()) {
            $message = '<p style="color:green;">Usuário adicionado com sucesso!</p>';
        } else {
            $message = '<p style="color:red;">Erro ao adicionar usuário: ' . $stmt->error . '</p>';
        }
    }
}

$usuarios = $conn->query("SELECT * FROM balconista_dono");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Página Principal - Cali Burger</title>
    <link rel="stylesheet" href="main.css">
    <style>
        .container {
            max-width: 1200px !important;
        }
    </style>
</head>
<body>

<?php include 'menu.php'; ?> 

<div class="container">
    <h2>Bem-vindo <?php echo $_SESSION['nome']; ?>!</h2>
    <p>Gerencie os usuários abaixo:</p>

    <form action="" method="POST" class="form-inline">
        <input type="text" name="cpf" placeholder="CPF" required>
        <input type="text" name="nome" placeholder="Nome" required>
        <input type="date" name="data_nasc" placeholder="Data de Nascimento" required>
        <input type="email" name="email" placeholder="Email" required>
        <select name="sexo" required>
            <option value="" disabled selected>Sexo</option>
            <option value="M">Masculino</option>
            <option value="F">Feminino</option>
        </select>
        <input type="password" name="senha" placeholder="Senha" required>
        <button type="submit">Adicionar Usuário</button>
    </form>
    <table class="styled-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>CPF</th>
                <th>Nome</th>
                <th>Data de Nascimento</th>
                <th>Email</th>
                <th>Sexo</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $usuarios->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['cpf'] ?></td>
                    <td><?= $row['cpf'] ?></td>
                    <td><?= $row['nome'] ?></td>
                    <td><?= $row['data_nasc'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['sexo'] ?></td>
                    <td>
                        <form action="delete_balconista_dono.php" method="POST" onsubmit="return confirm('Deseja excluir este usuário?');">
                            <input type="hidden" name="cpf" value="<?= $row['cpf'] ?>">
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
