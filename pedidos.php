<?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit();
}
include 'conexao.php';

$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';

$where_clauses = [];
$params = [];

if ($status_filter !== '') {
    $where_clauses[] = "p.status = ?";
    $params[] = $status_filter;
}

if ($date_filter !== '') {
    $where_clauses[] = "p.data_pedido >= ?";
    $params[] = $date_filter;
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

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

if (count($params) > 0) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pedidos - Cali Burger</title>
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
    </style>
</head>
<body>

<?php include 'menu.php'; ?>

<div class="container">
    <?php
    // Get count of pending orders
    $sql_count_pendentes = "SELECT COUNT(*) as count FROM pedido WHERE status = 'Aguardando aceitação'";
    $result_count = $conn->query($sql_count_pendentes);
    $count_pendentes = 0;
    if ($result_count) {
        $row_count = $result_count->fetch_assoc();
        $count_pendentes = $row_count['count'];
    }
    ?>
    <h2 id="pageTitle">🍔 Pedidos Realizados 
        <button id="togglePendingBtn" title="Mostrar Pedidos Aguardando Aceitação" style="position: relative; margin-left: 10px; cursor: pointer; font-size: 18px; padding: 5px 10px; border-radius: 5px; border: 1px solid #ccc; background-color: #f0f0f0; display: inline-block;">
            📥
            <?php if ($count_pendentes > 0): ?>
                <span id="pendingCountBadge" style="position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; padding: 2px 6px; font-size: 12px; font-weight: bold;"><?= $count_pendentes ?></span>
            <?php endif; ?>
        </button>
    </h2>  
    <form id="filterForm" method="GET" style="margin-bottom: 20px; display: flex; gap: 20px; align-items: center; justify-content: center;">
        <label for="status_filter">Status do Pedido:</label>
        <select name="status_filter" id="status_filter">
            <option value="" <?= (!isset($_GET['status_filter']) || $_GET['status_filter'] === '') ? 'selected' : '' ?>>Todos</option>
            <option value="Recusado" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Cancelado/recusado') ? 'selected' : '' ?>>Cancelado/Recusado</option>
            <option value="Em preparação" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Em preparação') ? 'selected' : '' ?>>Em preparação</option>
            <option value="A caminho" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] === 'A caminho') ? 'selected' : '' ?>>A caminho</option>
            <option value="Concluído" <?= (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Concluído') ? 'selected' : '' ?>>Concluído</option>
        </select>

        <label for="date_filter">Data e Hora (a partir de):</label>
        <input type="datetime-local" name="date_filter" id="date_filter" value="<?= isset($_GET['date_filter']) ? htmlspecialchars($_GET['date_filter']) : '' ?>">

        <button type="submit">Filtrar</button>
        <a id="reportButton" href="relatorio.php" style="padding: 8px 16px; background-color: #c0392b; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">Relatório de Vendas</a>
    </form>
    <div id="realizadosTable">
        <table class="styled-table">
            <thead>
                <tr>
                <th>Nº Pedido</th>
                <th>Produto</th>
                <th>Valor (R$)</th>
                <th>Cliente</th>
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
                    <td><?= htmlspecialchars($row['status'] === 'Cancelado/Recusado' ? 'Cancelado/recusado' : $row['status']) ?></td>
                    <td><?= date('d/m/Y H:i:s', strtotime($row['data_pedido'])) ?></td>
                    <td><?= !empty($row['observacao']) ? $row['observacao'] : 'Ø' ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div id="pendentesTable" style="display:none;">
        <table class="styled-table" id="pendentesTableContent">
            <thead>
                <tr>
                <th>Nº Pedido</th>
                <th>Produto</th>
                <th>Valor (R$)</th>
                <th>Cliente</th>
                <th>Data e Hora</th>
                <th>Observação</th>
                <th>Aceitar</th>
                </tr>
            </thead>
            <tbody>
                <!-- Pending orders will be loaded here dynamically -->
            </tbody>
        </table>
    </div>

    <style>
        /* Modal styles */
        #confirmation-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0;
            width: 100%; height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        #confirmation-modal .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 300px;
            border-radius: 8px;
            text-align: center;
            font-family: 'Poppins', sans-serif;
        }
        #confirmation-modal button {
            margin: 10px;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
        }
        #confirm-btn {
            background-color: #27ae60;
            color: white;
        }
        #cancel-btn {
            background-color: #c0392b;
            color: white;
        }
    </style>

    <div id="confirmation-modal">
        <div class="modal-content">
            <p id="modal-message">Confirma a ação?</p>
            <button id="confirm-btn">Confirmar</button>
            <button id="cancel-btn">Cancelar</button>
        </div>
    </div>

    <script>
        const modal = document.getElementById('confirmation-modal');
        const modalMessage = document.getElementById('modal-message');
        const confirmBtn = document.getElementById('confirm-btn');
        const cancelBtn = document.getElementById('cancel-btn');

        let currentAction = null;
        let currentPedido = null;

        function showConfirmation(message, pedido, action) {
            modalMessage.textContent = message;
            currentPedido = pedido;
            currentAction = action;
            modal.style.display = 'block';
        }

        confirmBtn.addEventListener('click', () => {
            if (currentPedido && currentAction) {
                sendStatusUpdate(currentPedido, currentAction);
            }
            modal.style.display = 'none';
        });

        cancelBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        window.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });

        document.getElementById('togglePendingBtn').addEventListener('click', function() {
            var realizados = document.getElementById('realizadosTable');
            var pendentes = document.getElementById('pendentesTable');
            if (pendentes.style.display === 'none') {
                // Fetch pending orders via AJAX
                fetch('pedidos_pendentes.php')
                .then(response => response.json())
                .then(data => {
                    var tbody = document.querySelector('#pendentesTableContent tbody');
                    tbody.innerHTML = '';
                    data.forEach(function(pedido) {
                        var tr = document.createElement('tr');

                        tr.innerHTML = `
                            <td>${pedido.numero_do_pedido}</td>
                            <td>${pedido.produtos}</td>
                            <td>${pedido.valor}</td>
                            <td>${pedido.nome_cliente}</td>
                            <td>${pedido.data_pedido}</td>
                            <td>${pedido.observacao}</td>
                            <td>
                                <button class="acceptBtn" data-pedido="${pedido.numero_do_pedido}" title="Aceitar" style="color: green; font-weight: bold;">✔️</button>
                                <button class="rejectBtn" data-pedido="${pedido.numero_do_pedido}" title="Recusar" style="color: red; font-weight: bold;">❌</button>
                            </td>
                        `;

                        tbody.appendChild(tr);
                    });

                    realizados.style.display = 'none';
                    pendentes.style.display = 'block';
                    this.title = 'Mostrar Pedidos Realizados';

                    // Attach event listeners to new buttons
                    document.querySelectorAll('.acceptBtn').forEach(function(button) {
                        button.addEventListener('click', function() {
                            var pedido = this.getAttribute('data-pedido');
                            showConfirmation('Tem certeza que deseja aceitar o pedido ' + pedido + '?', pedido, 'aceitar');
                        });
                    });

                    document.querySelectorAll('.rejectBtn').forEach(function(button) {
                        button.addEventListener('click', function() {
                            var pedido = this.getAttribute('data-pedido');
                            showConfirmation('Tem certeza que deseja recusar o pedido ' + pedido + '?', pedido, 'recusar');
                        });
                    });
                })
                .catch(error => {
                    alert('Erro ao carregar pedidos pendentes: ' + error);
                });
            } else {
                pendentes.style.display = 'none';
                realizados.style.display = 'block';
                this.title = 'Mostrar Pedidos Aguardando Aceitação';
            }
        });

        function sendStatusUpdate(numero_do_pedido, acao) {
            fetch('atualizar_status_pedido.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'numero_do_pedido=' + encodeURIComponent(numero_do_pedido) + '&acao=' + encodeURIComponent(acao)
            })
            .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload the page and keep showing pending orders
                        localStorage.setItem('showPendingOrders', 'true');
                        location.reload();
                    } else {
                        alert('Erro: ' + (data.error || 'Erro desconhecido'));
                    }
                })
            .catch(error => {
                alert('Erro na requisição: ' + error);
            });
        }
    </script>

    <script>
        // On page load, check if we should show pending orders or completed orders
        document.addEventListener('DOMContentLoaded', () => {
            const showPending = localStorage.getItem('showPendingOrders') === 'true';
            const toggleBtn = document.getElementById('togglePendingBtn');
            const realizados = document.getElementById('realizadosTable');
            const pendentes = document.getElementById('pendentesTable');
            const pageTitle = document.getElementById('pageTitle');
            const filterForm = document.getElementById('filterForm');
            const reportButton = document.getElementById('reportButton');

            function showPendingOrders() {
                // Fetch pending orders via AJAX
                fetch('pedidos_pendentes.php')
                .then(response => response.json())
                .then(data => {
                    var tbody = document.querySelector('#pendentesTableContent tbody');
                    tbody.innerHTML = '';

                    if (data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">Não há pedidos pendentes de aceitação.</td></tr>';
                    } else {
                        data.forEach(function(pedido) {
                            var tr = document.createElement('tr');

                            tr.innerHTML = `
                                <td>${pedido.numero_do_pedido}</td>
                                <td>${pedido.produtos}</td>
                                <td>${pedido.valor}</td>
                                <td>${pedido.nome_cliente}</td>
                                <td>${pedido.data_pedido}</td>
                                <td>${pedido.observacao}</td>
                                <td>
                                    <button class="acceptBtn" data-pedido="${pedido.numero_do_pedido}" title="Aceitar" style="color: green; font-weight: bold;">✔️</button>
                                    <button class="rejectBtn" data-pedido="${pedido.numero_do_pedido}" title="Recusar" style="color: red; font-weight: bold;">❌</button>
                                </td>
                            `;

                            tbody.appendChild(tr);
                        });
                    }

        realizados.style.display = 'none';
        pendentes.style.display = 'block';
        toggleBtn.title = 'Mostrar Pedidos Realizados';
        pageTitle.textContent = 'Pedidos pendentes de aceitação';
        filterForm.style.display = 'none';
        reportButton.style.display = 'none';

        // Add hamburger emoji in front of title
        pageTitle.textContent = '🍔 ' + pageTitle.textContent;

        // Add a button to go back to pedidos realizados
        let backButton = document.createElement('button');
        backButton.textContent = 'Voltar';
        backButton.style.cssText = 'float: right; margin-left: 10px; padding: 5px 10px; font-size: 14px; cursor: pointer; border-radius: 5px; border: none; background-color: #c0392b; color: white; font-weight: bold;';
        backButton.title = 'Voltar para Pedidos Realizados';
        backButton.addEventListener('click', () => {
            // Reload the page to ensure toggle button appears correctly
            location.reload();
        });
        // Remove existing back button if any
        let existingBackButton = document.getElementById('backToPedidosBtn');
        if (existingBackButton) {
            existingBackButton.remove();
        }
        backButton.id = 'backToPedidosBtn';
        pageTitle.appendChild(backButton);

        // Attach event listeners to new buttons
        document.querySelectorAll('.acceptBtn').forEach(function(button) {
            button.addEventListener('click', function() {
                var pedido = this.getAttribute('data-pedido');
                showConfirmation('Tem certeza que deseja aceitar o pedido ' + pedido + '?', pedido, 'aceitar');
            });
        });

        document.querySelectorAll('.rejectBtn').forEach(function(button) {
            button.addEventListener('click', function() {
                var pedido = this.getAttribute('data-pedido');
                showConfirmation('Tem certeza que deseja recusar o pedido ' + pedido + '?', pedido, 'recusar');
            });
        });

        localStorage.setItem('showPendingOrders', 'true');
    })
    .catch(error => {
        alert('Erro ao carregar pedidos pendentes: ' + error);
    });
}

function showCompletedOrders() {
    realizados.style.display = 'block';
    pendentes.style.display = 'none';
    toggleBtn.title = 'Mostrar Pedidos Aguardando Aceitação';
    pageTitle.textContent = '🍔 Pedidos Realizados';
    filterForm.style.display = 'flex';
    reportButton.style.display = 'inline-block';

    // Remove back button if present
    let existingBackButton = document.getElementById('backToPedidosBtn');
    if (existingBackButton) {
        existingBackButton.remove();
    }

    localStorage.setItem('showPendingOrders', 'false');
}

toggleBtn.addEventListener('click', function() {
        if (pendentes.style.display === 'none') {
            showPendingOrders();
            // Hide toggle button immediately when showing pending orders
            document.getElementById('togglePendingBtn').style.display = 'none';
        } else {
            showCompletedOrders();
        }
});

// On page load, show the correct table
        });
    </script>
</div>

</body>
</html>
