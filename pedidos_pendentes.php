<?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

include 'conexao.php';

$sql_pendentes = "
    SELECT 
        p.numero_do_pedido,
        GROUP_CONCAT(
            CASE 
                WHEN ip.quantidade >= 2 THEN CONCAT(ip.produto, ' - X', ip.quantidade)
                ELSE ip.produto
            END
            SEPARATOR '<br>'
        ) AS produtos,
        p.valor,
        p.nome_cliente,
        p.data_pedido,
        GROUP_CONCAT(
            CASE 
                WHEN ip.observacao IS NOT NULL AND ip.observacao != '' 
                THEN CONCAT(ip.produto, ': ', ip.observacao) 
                ELSE NULL 
            END
            SEPARATOR '<br>'
        ) AS observacao
    FROM pedido p
    LEFT JOIN itens_pedido ip ON p.numero_do_pedido = ip.numero_do_pedido
    WHERE p.status = 'Aguardando aceitação'
    GROUP BY p.numero_do_pedido, p.valor, p.nome_cliente, p.data_pedido
";

$result_pendentes = $conn->query($sql_pendentes);

$pedidos = [];

while ($row = $result_pendentes->fetch_assoc()) {
    $pedidos[] = [
        'numero_do_pedido' => $row['numero_do_pedido'],
        'produtos' => $row['produtos'],
        'valor' => number_format($row['valor'], 2, ',', '.'),
        'nome_cliente' => htmlspecialchars($row['nome_cliente']),
        'data_pedido' => date('d/m/Y H:i:s', strtotime($row['data_pedido'])),
        'observacao' => !empty($row['observacao']) ? $row['observacao'] : 'Ø',
    ];
}

header('Content-Type: application/json');
echo json_encode($pedidos);
exit();
?>
