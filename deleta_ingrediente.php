<?php
session_start();
include 'conexao.php';

$id = $_POST['id'];

// Delete related rows in cardapio_ingrediente first to avoid foreign key constraint error
$stmt = $conn->prepare("DELETE FROM cardapio_ingrediente WHERE id_ingrediente = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// Now delete the ingredient from estoque
$stmt = $conn->prepare("DELETE FROM estoque WHERE id_ingrediente = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: estoque.php");
exit();
?>
