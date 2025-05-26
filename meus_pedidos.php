<?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit();
}

include 'conexao.php';

$cliente_nome = $_SESSION['nome'];

$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';

$where_clauses = ["p.nome_cliente = ?"];
$params = [$cliente_nome];

if ($status_filter !== '') {
    $where_clauses[] = "p.status = ?";
    $params[] = $status_filter;
}

if ($date_filter !== '') {
    $where_clauses[] = "DATE(p.data_pedido) >= ?";
    $params[] = $date_filter;
}

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
        p.observacao,
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
    GROUP BY p.numero_do_pedido, p.valor, p.observacao, p.status, p.data_pedido
    ORDER BY p.numero_do_pedido DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();

function mapStatus($status) {
    $map = [
        'Aguardando aceitação' => 'Aguardando aceitação',
        'Cancelado/recusado' => 'Cancelado/recusado',
        'Em preparação' => 'Em preparação',
        'Pedido a caminho' => 'Pedido a caminho',
        'Concluído' => 'Concluído',
    ];
    return $map[$status] ?? $status;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Meus Pedidos - Cali Burger</title>
    <link rel="stylesheet" href="main.css">
    <style>
        .container {
            max-width: 1000px;
            margin: 50px auto;
            background-color: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            text-align: center;
        }
        .styled-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .styled-table th,
        .styled-table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .styled-table th {
            background-color: #c0392b;
            color: white;
            font-weight: 600;
        }
        .styled-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .styled-table tr:hover {
            background-color: #f1f1f1;
        }
        form.filters {
            margin-bottom: 20px;
            display: flex;
            gap: 20px;
            align-items: center;
            justify-content: center;
        }
        /* Modal styles */
        .modal {
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
            border: 1px solid #888;
            width: 300px;
            border-radius: 8px;
            text-align: center;
        }
        .modal-content button {
            margin: 10px;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        #cancel-confirm-btn {
            background-color: #c0392b;
            color: white;
        }
        #cancel-cancel-btn {
            background-color: #bdc3c7;
            color: black;
        }
    </style>
</head>
<body>

<?php include 'menu_cliente.php'; ?>

<div class="container">
    <h2>Meus Pedidos</h2>
    <form method="GET" class="filters">
        <label for="status_filter">Status do Pedido:</label>
        <select name="status_filter" id="status_filter">
            <option value="" <?= (!isset($_GET['status_filter']) || $_GET['status_filter'] === '') ? 'selected' : '' ?>>Todos</option>
            <option value="Aguardando aceitação" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Aguardando aceitação') ? 'selected' : '' ?>>Aguardando aceitação</option>
            <option value="Cancelado/recusado" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Cancelado/recusado') ? 'selected' : '' ?>>Cancelado/recusado</option>
            <option value="Em preparação" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Em preparação') ? 'selected' : '' ?>>Em preparação</option>
            <option value="Pedido a caminho" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Pedido a caminho') ? 'selected' : '' ?>>Pedido a caminho</option>
            <option value="Concluído" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Concluído') ? 'selected' : '' ?>>Concluído</option>
        </select>

        <label for="date_filter">Data (a partir de):</label>
        <input type="date" name="date_filter" id="date_filter" value="<?= isset($_GET['date_filter']) ? htmlspecialchars($_GET['date_filter']) : '' ?>">

        <button type="submit">Filtrar</button>
    </form>
    <table class="styled-table">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Valor (R$)</th>
                <th>Observação</th>
                <th>Status</th>
                <th>Data e Hora</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                <td><?= $row['produtos'] ?></td>
                <td><?= number_format($row['valor'], 2, ',', '.') ?></td>
                <td><?= !empty($row['observacao']) ? $row['observacao'] : 'Ø' ?></td>
                <td><?= htmlspecialchars(mapStatus($row['status'])) ?></td>
                <td><?= date('d/m/Y H:i:s', strtotime($row['data_pedido'])) ?></td>
                <td>
                        <?php $mappedStatus = mapStatus($row['status']); ?>
                        <!-- DEBUG: Status is [<?= htmlspecialchars($mappedStatus) ?>] -->
                        <?php if ($mappedStatus === 'Cancelado/recusado'): ?>
                            <span style="color: red;">Pedido Cancelado</span>
                        <?php elseif ($mappedStatus === 'Aguardando aceitação' || $mappedStatus === 'Em preparação'): ?>
                            <form method="POST" action="atualizar_status_pedido.php" class="cancel-form">
                                <input type="hidden" name="numero_do_pedido" value="<?= $row['numero_do_pedido'] ?>">
                                <input type="hidden" name="acao" value="recusar">
                                <button type="submit" class="cancel-btn">Cancelar</button>
                            </form>
                        <?php elseif ($mappedStatus === 'Pedido a caminho'): ?>
                            <form method="POST" action="atualizar_status_pedido.php" class="received-form">
                                <input type="hidden" name="numero_do_pedido" value="<?= $row['numero_do_pedido'] ?>">
                                <input type="hidden" name="acao" value="concluir">
                                <button type="submit" class="received-btn">Recebi meu pedido</button>
                            </form>
                        <?php elseif ($mappedStatus === 'Concluído'): ?>
                            <span>Pedido recebido</span>
                        <?php else: ?>
                            <form method="POST" action="atualizar_status_pedido.php" class="received-form">
                                <input type="hidden" name="numero_do_pedido" value="<?= $row['numero_do_pedido'] ?>">
                                <input type="hidden" name="acao" value="concluir">
                                <button type="submit" class="received-btn">Recebi meu pedido</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div id="cancel-confirmation-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <p id="cancel-modal-message">Tem certeza que deseja cancelar este pedido?</p>
        <button id="cancel-confirm-btn">Confirmar</button>
        <button id="cancel-cancel-btn">Cancelar</button>
    </div>
</div>

<div id="received-confirmation-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <p id="received-modal-message">Confirma que recebeu seu pedido?</p>
        <button id="received-confirm-btn">Confirmar</button>
        <button id="received-cancel-btn">Cancelar</button>
    </div>
</div>

    <script>
        const cancelModal = document.getElementById('cancel-confirmation-modal');
        const cancelConfirmBtn = document.getElementById('cancel-confirm-btn');
        const cancelCancelBtn = document.getElementById('cancel-cancel-btn');
        let formToSubmit = null;

        document.querySelectorAll('.cancel-form').forEach(form => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                formToSubmit = form;
                cancelModal.style.display = 'block';
            });
        });

        cancelConfirmBtn.addEventListener('click', () => {
            if (formToSubmit) {
                // Submit form via AJAX
                const formData = new FormData(formToSubmit);
                fetch(formToSubmit.action, {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload the page to reflect changes
                        location.reload();
                    } else {
                        alert('Erro ao cancelar pedido: ' + (data.error || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    alert('Erro na requisição: ' + error);
                });
            }
            cancelModal.style.display = 'none';
        });

        cancelCancelBtn.addEventListener('click', () => {
            cancelModal.style.display = 'none';
            formToSubmit = null;
        });

        window.addEventListener('click', (event) => {
            if (event.target === cancelModal) {
                cancelModal.style.display = 'none';
                formToSubmit = null;
            }
        });

        // Confirmation modal for "Recebi meu pedido"
        const receivedModal = document.getElementById('received-confirmation-modal');
        const receivedConfirmBtn = document.getElementById('received-confirm-btn');
        const receivedCancelBtn = document.getElementById('received-cancel-btn');
        let receivedFormToSubmit = null;

        document.querySelectorAll('.received-form').forEach(form => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                receivedFormToSubmit = form;
                receivedModal.style.display = 'block';
            });
        });

        receivedConfirmBtn.addEventListener('click', () => {
            if (receivedFormToSubmit) {
                // Submit form via AJAX
                const formData = new FormData(receivedFormToSubmit);
                fetch(receivedFormToSubmit.action, {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload the page to reflect changes
                        location.reload();
                    } else {
                        alert('Erro ao confirmar recebimento: ' + (data.error || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    alert('Erro na requisição: ' + error);
                });
            }
            receivedModal.style.display = 'none';
        });

        receivedCancelBtn.addEventListener('click', () => {
            receivedModal.style.display = 'none';
            receivedFormToSubmit = null;
        });

        window.addEventListener('click', (event) => {
            if (event.target === receivedModal) {
                receivedModal.style.display = 'none';
                receivedFormToSubmit = null;
            }
        });
    </script>

</body>
</html>
