<?php
session_start();
include 'conexao.php';

$id = $_POST['id'];

$stmt = $conn->prepare("DELETE FROM estoque WHERE id_ingrediente = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: estoque.php");
exit();
?>
