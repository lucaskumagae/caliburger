<?php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cpf'])) {
        $cpf = $_POST['cpf'];

        $stmt = $conn->prepare("DELETE FROM cozinheiro WHERE cpf = ?");
        $stmt->bind_param("s", $cpf);

        if ($stmt->execute()) {
            header("Location: main.php");
            exit();
        } else {
            echo "Erro ao deletar cozinheiro: " . $stmt->error;
        }
    } else {
        echo "ID do cozinheiro não fornecido.";
    }
} else {
    echo "Método inválido.";
}
?>
