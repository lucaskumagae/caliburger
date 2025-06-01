<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['id_cliente'])) {
    header("Location: login.php");
    exit();
}

$id_cliente = $_SESSION['id_cliente'];

$stmt = $conn->prepare("SELECT id, login, nome, email, cpf, data_nasc, end_estado, end_cidade, end_bairro, end_logradouro FROM cliente WHERE id = ?");
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Cliente não encontrado.";
    exit();
}

$cliente = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Perfil do Cliente</title>
    <link rel="stylesheet" href="main.css" />
</head>
<body>
    <?php include 'menu_cliente.php'; ?>
    <main class="carrinho-container">
        <h1>Perfil do Cliente</h1>
        <?php
        echo '<form method="GET" action="editar_cliente.php">';
        echo '<ul>';
        echo '<li><strong>Login:</strong> ' . htmlspecialchars($cliente['login']) . '</li>';
        echo '<li><strong>Nome:</strong> ' . htmlspecialchars($cliente['nome']) . '</li>';
        echo '<li><strong>Email:</strong> ' . htmlspecialchars($cliente['email']) . '</li>';
        echo '<li><strong>CPF:</strong> ' . htmlspecialchars($cliente['cpf']) . '</li>';
        echo '<li><strong>Data de Nascimento:</strong> ' . htmlspecialchars($cliente['data_nasc']) . '</li>';
        echo '<li><strong>Estado:</strong> ' . htmlspecialchars($cliente['end_estado']) . '</li>';
        echo '<li><strong>Cidade:</strong> ' . htmlspecialchars($cliente['end_cidade']) . '</li>';
        echo '<li><strong>Bairro:</strong> ' . htmlspecialchars($cliente['end_bairro']) . '</li>';
        echo '<li><strong>Logradouro:</strong> ' . htmlspecialchars($cliente['end_logradouro']) . '</li>';
        echo '</ul>';
        echo '<input type="hidden" name="id" value="' . htmlspecialchars($cliente['id']) . '" />';
        echo '<button type="submit">Editar</button>';
        echo '</form>';
        ?>
    </main>
</body>
</html>
