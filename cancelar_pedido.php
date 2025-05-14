<?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit();
}

include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['numero_do_pedido'])) {
        $numero_do_pedido = $_POST['numero_do_pedido'];
        $cliente_nome = $_SESSION['nome'];

        // Update the order status to "Cancelado/recusado" only if it belongs to the logged-in user
        $sql = "UPDATE pedido SET status = 'Cancelado/recusado' WHERE numero_do_pedido = ? AND nome_cliente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('is', $numero_do_pedido, $cliente_nome);
        $stmt->execute();
    }
}

header("Location: meus_pedidos.php");
exit();
?>
