<?php
session_start();
include 'conexao.php';

function validaCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11) {
        return false;
    }
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    for ($t = 9; $t < 11; $t++) {
        $d = 0;
        for ($c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    return true;
}

if (!isset($_POST['cpf']) || !isset($_POST['senha'])) {
    header("Location: login_cozinheiro.php?erro=1");
    exit();
}

$cpf = $_POST['cpf'];
$senha = $_POST['senha'];

if (!validaCPF($cpf)) {
    // CPF nao validado
    header("Location: login_cozinheiro.php?erro=cpf");
    exit();
}

$sql = "SELECT * FROM cozinheiro WHERE cpf = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $cpf);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $cozinheiro = $result->fetch_assoc();
    if ($cozinheiro['senha'] === $senha) {
        $_SESSION['cpf'] = $cozinheiro['cpf'];
        $_SESSION['nome'] = $cozinheiro['nome'];
        header("Location: pedidos_cozinheiro.php");

        exit();
    } else {
        header("Location: login_cozinheiro.php?erro=senha");
        exit();
    }
} else {
    //CPF nao encontrado
    header("Location: login_cozinheiro.php?erro=cpf");
    exit();
}

$conn->close();
?>
