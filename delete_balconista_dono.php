<?php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'])) {
        $id = $_POST['id'];

        $stmt = $conn->prepare("DELETE FROM balconista_dono WHERE id = ?");
        $stmt->bind_param("i", $id);

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
