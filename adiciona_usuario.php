<?php
include 'conexao.php';

if (!isset($_POST['nome']) || !isset($_POST['email']) || !isset($_POST['senha'])) {
    header("Location: cadastro.php?error=missing_fields");
    exit();
}

$nome = $_POST['nome'];
$email = $_POST['email'];
$senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO cliente (nome, email, senha) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $nome, $email, $senha);
$stmt->execute();

header("Location: main.php");
exit();
?>
