<?php
include 'conexao.php';

session_start();

if (!isset($_SESSION['id_cliente'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['id_cliente'];

$stmt = $conn->prepare("DELETE FROM cliente WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Destroy session after deleting user
    session_destroy();
    header("Location: login.php");
    exit();
} else {
    // Handle error, redirect back to profile with error message
    $_SESSION['message'] = "Erro ao excluir a conta.";
    header("Location: perfil_cliente.php");
    exit();
}
?>
