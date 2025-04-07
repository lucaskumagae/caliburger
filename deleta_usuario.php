<?php
include 'conexao.php';

$id = $_POST['id'];
$stmt = $conn->prepare("DELETE FROM cliente WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: main.php");
exit();
?>
