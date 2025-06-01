<?php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
if (isset($_POST['cpf'])) {
    $cpf = $_POST['cpf'];

    $stmt = $conn->prepare("DELETE FROM balconista_dono WHERE cpf = ?");
    $stmt->bind_param("s", $cpf);

        if ($stmt->execute()) {
            header("Location: main.php");
            exit();
        } else {
            echo "Erro ao deletar usuário: " . $stmt->error;
        }
    } else {
        echo "ID do usuário não fornecido.";
    }
} else {
    echo "Método inválido.";
}
?>
