<?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit();
}

include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['numero_do_pedido']) && isset($_POST['acao'])) {
        $numero_do_pedido = $_POST['numero_do_pedido'];
        $acao = $_POST['acao'];

        if ($acao === 'aceitar') {
            $novo_status = 'Em preparação';
        } elseif ($acao === 'recusar') {
            $novo_status = 'Cancelado/recusado';
        } elseif ($acao === 'confirmar') {
            $novo_status = 'A caminho';
        } elseif ($acao === 'concluir') {
            $novo_status = 'Concluído';
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Ação inválida']);
            exit();
        }

        $sql = "UPDATE pedido SET status = ? WHERE numero_do_pedido = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $novo_status, $numero_do_pedido);
        if ($stmt->execute()) {
            header("Location: meus_pedidos.php");
            exit();
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao atualizar o status']);
        }
        exit();
    }
}

http_response_code(400);
echo json_encode(['error' => 'Parâmetros inválidos']);
exit();
?>
