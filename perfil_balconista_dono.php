<?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit();
}

include 'conexao.php';

if (isset($_SESSION['cpf'])) {
    $cpf = $_SESSION['cpf'];
    $stmt = $conn->prepare("SELECT cpf, nome, email, data_nasc, sexo FROM balconista_dono WHERE cpf = ?");
    $stmt->bind_param("s", $cpf);
} else {
    $nome = $_SESSION['nome'];
    $stmt = $conn->prepare("SELECT cpf, nome, email, data_nasc, sexo FROM balconista_dono WHERE nome = ?");
    $stmt->bind_param("s", $nome);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = "Balconista não encontrado.";
    header("Location: main.php");
    exit();
}

$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Perfil do Balconista - Cali Burger</title>
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

<?php include 'menu.php'; ?>

<div class="container">
    <h2>Dados do Balconista</h2>

    <?php
    if (isset($_SESSION['message'])) {
        echo '<div class="message">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message']);
    }
    ?>

    <table>
        <tr><th>CPF</th><td><?= htmlspecialchars($user['cpf']) ?></td></tr>
        <tr><th>Nome</th><td><?= htmlspecialchars($user['nome']) ?></td></tr>
        <tr><th>Email</th><td><?= htmlspecialchars($user['email']) ?></td></tr>
        <tr><th>Data de Nascimento</th><td><?= htmlspecialchars($user['data_nasc']) ?></td></tr>
        <tr><th>Sexo</th><td><?= htmlspecialchars($user['sexo']) ?></td></tr>
    </table>

    <a class="edit-button" href="editar_balconista_dono.php?cpf=<?= urlencode($user['cpf']) ?>">Editar Dados</a>

    <form action="delete_balconista_dono.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.');">
        <input type="hidden" name="cpf" value="<?= htmlspecialchars($user['cpf']) ?>">
        <button class="edit-button" style="background-color: #c0392b;">Excluir Conta</button>
    </form>
</div>

</body>
</html>
