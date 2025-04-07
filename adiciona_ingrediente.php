<?php
session_start();
include 'conexao.php';

$nome = $_POST['nome_ingrediente'];
$quantidade = $_POST['quantidade'];

$stmt = $conn->prepare("INSERT INTO estoque (nome_ingrediente, quantidade) VALUES (?, ?)");
$stmt->bind_param("si", $nome, $quantidade);
$stmt->execute();

header("Location: estoque.php");
exit();
?>
