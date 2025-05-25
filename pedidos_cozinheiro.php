<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['nome'])) {
    header("Location: login_cozinheiro.php");
    exit();
}
include 'conexao.php';

$where_clauses = ["p.aceito = 1", "p.status = 'Em preparação'"];
$params = [];

$where_sql = 'WHERE ' . implode(' AND ', $where_clauses);

$sql = "
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
        p.aceito,
        p.status,
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
    $where_sql
    GROUP BY p.numero_do_pedido, p.valor, p.nome_cliente, p.aceito, p.status, p.data_pedido
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pedidos Cozinheiro - Cali Burger</title>
    <link rel="stylesheet" href="main.css">
<style>
        .container {
            max-width: 1200px !important;
        }
        input[type="datetime-local"]:not(:placeholder-shown) {
            color: black;
        }
        input[type="datetime-local"]:hover,
        input[type="datetime-local"]:focus {
            border-color: initial;
            outline-color: initial;
        }
        .btn-confirmar {
            background-color: #c0392b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn-confirmar:hover {
            background-color: #a93226;
        }
    </style>
</head>
<body>

<?php include 'menu_cozinheiro.php'; ?>

<div class="container">
    <h2>🍔 Pedidos</h2>  
    <table class="styled-table">
        <thead>
            <tr>
            <th>Nº Pedido</th>
            <th>Produto</th>
            <th>Valor (R$)</th>
            <th>Cliente</th>
            <th>Aceito</th>
            <th>Status</th>
            <th>Data e Hora</th>
            <th>Observação</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                <td><?= $row['numero_do_pedido'] ?></td>
                <td><?= $row['produtos'] ?></td>
                <td><?= number_format($row['valor'], 2, ',', '.') ?></td>
                <td><?= htmlspecialchars($row['nome_cliente']) ?></td>
                <td><?= $row['aceito'] ? 'Sim' : 'Não' ?></td>
                <td><?= htmlspecialchars($row['status'] === 'Cancelado/Recusado' ? 'Cancelado/recusado' : $row['status']) ?></td>
                <td><?= date('d/m/Y H:i:s', strtotime($row['data_pedido'])) ?></td>
                <td><?= !empty($row['observacao']) ? $row['observacao'] : 'Ø' ?></td>
                <td>
                    <button class="confirmar-btn btn-confirmar" data-numero-pedido="<?= $row['numero_do_pedido'] ?>">Confirmar</button>
                </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div id="confirmModal" class="modal">
  <div class="modal-content">
    <p id="modalText">Deseja confirmar o pedido para entrega?</p>
    <div class="modal-buttons">
      <button id="modalConfirm" class="btn-confirm">Confirmar</button>
      <button id="modalCancel" class="btn-cancel">Cancelar</button>
    </div>
  </div>
</div>

<style>
.modal {
  display: none; 
  position: fixed; 
  z-index: 1000; 
  left: 0;
  top: 0;
  width: 100%; 
  height: 100%; 
  overflow: auto; 
  background-color: rgba(0,0,0,0.4); 
}

.modal-content {
  background-color: #fefefe;
  margin: 15% auto; 
  padding: 20px;
  border-radius: 8px;
  width: 300px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.3);
  text-align: center;
  font-family: Arial, sans-serif;
}

.modal-buttons {
  margin-top: 20px;
  display: flex;
  justify-content: center;
  gap: 15px;
}

.btn-confirm {
  background-color: #28a745;
  border: none;
  color: white;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
}

.btn-cancel {
  background-color: #dc3545;
  border: none;
  color: white;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.confirmar-btn');
    const modal = document.getElementById('confirmModal');
    const modalConfirm = document.getElementById('modalConfirm');
    const modalCancel = document.getElementById('modalCancel');
    let currentPedido = null;

    buttons.forEach(button => {
        button.addEventListener('click', function() {
            currentPedido = this.dataset.numeroPedido;
            modal.style.display = 'block';
        });
    });

    modalConfirm.addEventListener('click', function() {
        if (!currentPedido) return;
        fetch('atualizar_status_pedido.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                'numero_do_pedido': currentPedido,
                'acao': 'confirmar'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {

                location.reload();
            } else {
                alert('Erro: ' + (data.error || 'Falha ao atualizar o status.'));
            }
        })
        .catch(() => {
            alert('Erro na requisição.');
        })
        .finally(() => {
            modal.style.display = 'none';
            currentPedido = null;
        });
    });

    modalCancel.addEventListener('click', function() {
        modal.style.display = 'none';
        currentPedido = null;
    });

    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
            currentPedido = null;
        }
    });
});
</script>
</body>
</html>
