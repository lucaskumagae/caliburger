<?php
session_start();
include 'conexao.php';

$nome = $_POST['nome_ingrediente'];
$quantidade = $_POST['quantidade'];

// verifica se ingrediente existe
$check_stmt = $conn->prepare("SELECT quantidade FROM estoque WHERE nome_ingrediente = ?");
$check_stmt->bind_param("s", $nome);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    // ingrediente existe, é atualizado
    $check_stmt->bind_result($existing_quantidade);
    $check_stmt->fetch();
    $new_quantidade = $existing_quantidade + $quantidade;

    $update_stmt = $conn->prepare("UPDATE estoque SET quantidade = ? WHERE nome_ingrediente = ?");
    $update_stmt->bind_param("is", $new_quantidade, $nome);

    if ($update_stmt->execute()) {
        $_SESSION['msg_success'] = "Quantidade do ingrediente atualizada com sucesso!";
    } else {
        $_SESSION['msg_error'] = "Erro ao atualizar quantidade: " . $update_stmt->error;
    }
} else {
    // ingrediente não existe, é adicionado
    $insert_stmt = $conn->prepare("INSERT INTO estoque (nome_ingrediente, quantidade) VALUES (?, ?)");
    $insert_stmt->bind_param("si", $nome, $quantidade);

    if ($insert_stmt->execute()) {
        $_SESSION['msg_success'] = "Ingrediente adicionado com sucesso!";
    } else {
        $_SESSION['msg_error'] = "Erro ao adicionar ingrediente: " . $insert_stmt->error;
    }
}

header("Location: estoque.php");
exit();
?>
