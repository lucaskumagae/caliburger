<?php
session_start();
if (!isset($_SESSION['nome']) || !isset($_SESSION['id_cliente'])) {
    header("Location: login.php");
    exit();
}

include 'conexao.php';

$id = intval($_SESSION['id_cliente']);

$stmt = $conn->prepare("SELECT id, login, nome, email, cpf, data_nasc, end_estado, end_cidade, end_bairro, end_logradouro FROM cliente WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = "Cliente não encontrado.";
    header("Location: menu_cliente.php");
    exit();
}

$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Perfil do Cliente - Cali Burger</title>
    <link rel="stylesheet" href="main.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            font-family: 'Poppins', sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .edit-button {
            margin-top: 15px;
            padding: 8px 12px;
            font-size: 16px;
            background-color: #c0392b;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .edit-button:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>

<?php include 'menu_cliente.php'; ?>

<div class="container">
    <h2>Dados do Usuário</h2>

    <?php
    if (isset($_SESSION['message'])) {
        echo '<div class="message">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message']);
    }
    ?>

    <table>
        <tr><th>Login</th><td><?= htmlspecialchars($user['login']) ?></td></tr>
        <tr><th>Nome</th><td><?= htmlspecialchars($user['nome']) ?></td></tr>
        <tr><th>Email</th><td><?= htmlspecialchars($user['email']) ?></td></tr>
        <tr><th>CPF</th><td><?= htmlspecialchars($user['cpf']) ?></td></tr>
        <tr><th>Data de Nascimento</th><td><?= htmlspecialchars($user['data_nasc']) ?></td></tr>
        <tr><th>Estado</th><td><?= htmlspecialchars($user['end_estado']) ?></td></tr>
        <tr><th>Cidade</th><td><?= htmlspecialchars($user['end_cidade']) ?></td></tr>
        <tr><th>Bairro</th><td><?= htmlspecialchars($user['end_bairro']) ?></td></tr>
        <tr><th>Logradouro</th><td><?= htmlspecialchars($user['end_logradouro']) ?></td></tr>
    </table>

    <a class="edit-button" href="editar_cliente.php?id=<?= $user['id'] ?>">Editar Dados</a>

    <form action="deleta_usuario.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.');">
        <button class="edit-button" style="background-color: #c0392b;">Excluir Conta</button>
    </form>
</div>

</body>
</html>
