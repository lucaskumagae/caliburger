<?php
session_start();
include 'conexao.php';

$login = $_POST['login'];
$senha = $_POST['senha'];

$sql = "SELECT * FROM cliente WHERE login = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $login);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $cliente = $result->fetch_assoc();
    $stored_password = $cliente['senha'];

    // Check if stored password is hashed (starts with $2y$, $2a$, or $2b$ for bcrypt)
    $hash_prefix = substr($stored_password, 0, 4);
    if ($hash_prefix === '$2y$' || $hash_prefix === '$2a$' || $hash_prefix === '$2b$') {
        $password_valid = password_verify($senha, $stored_password);
    } else {
        // Legacy plain text password comparison
        $password_valid = ($senha === $stored_password);
        // Optionally rehash and update password to hashed version
        if ($password_valid) {
            $new_hash = password_hash($senha, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE cliente SET senha = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_hash, $cliente['id']);
            $update_stmt->execute();
            $update_stmt->close();
        }
    }

    // DEBUGGING - Remove or comment out after fixing
    // echo "Password: $senha\n";
    // echo "Stored hash: $stored_password\n";
    // echo "Password verify result: " . (password_verify($senha, $stored_password) ? 'true' : 'false') . "\n";

    if ($password_valid) {
        $_SESSION['id_cliente'] = $cliente['id'];
        $_SESSION['nome'] = $cliente['nome'];
        header("Location: cardapio_cliente.php");
        exit();
    } else {
        header("Location: login.php?erro=1");
        exit();
    }
} else {
    header("Location: login.php?erro=1");
    exit();
}

$conn->close();
?>