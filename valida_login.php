<?php
session_start();
include 'conexao.php';

$login = $_POST['login'];
$senha = $_POST['senha'];

$sql = "SELECT * FROM cliente WHERE login = ? AND senha = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $login, $senha);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $cliente = $result->fetch_assoc();
    $_SESSION['id'] = $cliente['id'];
    $_SESSION['nome'] = $cliente['nome'];
    header("Location: menu_cliente.php");
} else {
    header("Location: login.php?erro=1");
}

$conn->close();
?>