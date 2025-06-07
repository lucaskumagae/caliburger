<?php
session_start();
include 'conexao.php'; // <- Isso cria a variável $conexao

// Verifica se o usuário está logado
if (!isset($_SESSION['cpf'])) {
    header("Location: login_cozinheiro.php");
    exit();
}

// Obtém dados do cozinheiro logado
$cpf = $_SESSION['cpf'];
$sql = "SELECT * FROM cozinheiro WHERE cpf = '$cpf'";
$resultado = mysqli_query($conn, $sql);

$cozinheiro = mysqli_fetch_assoc($resultado);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Perfil do Cozinheiro</title>
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
            background-color: #a83227;
        }
    </style>
</head>
<body>

<?php include 'menu_cozinheiro.php'; ?>

<div class="container">
    <h2>Dados do Cozinheiro</h2>

    <table>
        <tr><th>CPF</th><td><?= htmlspecialchars($cozinheiro['cpf']) ?></td></tr>
        <tr><th>Nome</th><td><?= htmlspecialchars($cozinheiro['nome']) ?></td></tr>
        <tr><th>Data de Nascimento</th><td><?= htmlspecialchars($cozinheiro['data_nasc']) ?></td></tr>
        <tr><th>Sexo</th><td><?= htmlspecialchars($cozinheiro['sexo']) ?></td></tr>
        <tr><th>Email</th><td><?= htmlspecialchars($cozinheiro['email']) ?></td></tr>
    </table>

    <a class="edit-button" href="editar_cozinheiro.php?cpf=<?= urlencode($cozinheiro['cpf']) ?>">Editar Dados</a>
</div>

</body>
</html>
