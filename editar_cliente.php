<?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit();
}

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

function validaDataNascimento($data) {
    $timestamp = strtotime($data);
    if (!$timestamp) {
        return false;
    }
    $hoje = strtotime(date('Y-m-d'));
    if ($timestamp > $hoje) {
        return false;
    }
    $year = (int)date('Y', $timestamp);
    $currentYear = (int)date('Y');
    if ($year > $currentYear) {
        return false;
    }
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        $_SESSION['message'] = "ID do cliente não fornecido.";
        header("Location: perfil_cliente.php");
        exit();
    }

    $id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT id, login, nome, email, cpf, data_nasc, end_estado, end_cidade, end_bairro, end_logradouro FROM cliente WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $_SESSION['message'] = "Cliente não encontrado.";
        header("Location: perfil_cliente.php");
        exit();
    }
    $edit_row = $result->fetch_assoc();

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cancelar'])) {
        header("Location: perfil_cliente.php");
        exit();
    }

    if (!isset($_POST['id']) || empty($_POST['id']) || !isset($_POST['login']) || !isset($_POST['nome']) || !isset($_POST['email']) || !isset($_POST['cpf']) || !isset($_POST['data_nasc']) || !isset($_POST['end_estado']) || !isset($_POST['end_cidade']) || !isset($_POST['end_bairro']) || !isset($_POST['end_logradouro'])) {
        $_SESSION['message'] = "Dados incompletos para atualização.";
        header("Location: editar_cliente.php?id=" . intval($_POST['id']));
        exit();
    }

    $id = intval($_POST['id']);
    $login = trim($_POST['login']);
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $cpf = trim($_POST['cpf']);
    $data_nasc = trim($_POST['data_nasc']);
    $end_estado = trim($_POST['end_estado']);
    $end_cidade = trim($_POST['end_cidade']);
    $end_bairro = trim($_POST['end_bairro']);
    $end_logradouro = trim($_POST['end_logradouro']);

    if ($login === '' || $nome === '' || $email === '' || $data_nasc === '' || $end_estado === '' || $end_cidade === '' || $end_bairro === '' || $end_logradouro === '') {
        $_SESSION['message'] = "Todos os campos obrigatórios devem ser preenchidos.";
        header("Location: editar_cliente.php?id=$id");
        exit();
    }

    if (!validaCPF($cpf)) {
        $_SESSION['message'] = "CPF inválido.";
        header("Location: editar_cliente.php?id=$id");
        exit();
    }

    if (!validaDataNascimento($data_nasc)) {
        $_SESSION['message'] = "Data de nascimento inválida.";
        header("Location: editar_cliente.php?id=$id");
        exit();
    }

    // Update cliente data
    $stmt = $conn->prepare("UPDATE cliente SET login = ?, nome = ?, email = ?, cpf = ?, data_nasc = ?, end_estado = ?, end_cidade = ?, end_bairro = ?, end_logradouro = ? WHERE id = ?");
    $stmt->bind_param("sssssssssi", $login, $nome, $email, $cpf, $data_nasc, $end_estado, $end_cidade, $end_bairro, $end_logradouro, $id);
    if (!$stmt->execute()) {
        $_SESSION['message'] = "Erro ao atualizar dados do cliente.";
        header("Location: editar_cliente.php?id=$id");
        exit();
    }

    header("Location: perfil_cliente.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente - Cali Burger</title>
    <link rel="stylesheet" href="main.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <style>
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            font-family: 'Poppins', sans-serif;
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
        input[type="text"], input[type="email"], input[type="date"] {
            width: 100%;
            padding: 6px;
            margin-top: 4px;
            box-sizing: border-box;
        }
        button {
            margin-top: 15px;
            padding: 8px 12px;
            font-size: 16px;
            cursor: pointer;
        }
        .message {
            background-color: #a93226;
            color:rgb(255, 255, 255);
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
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
            border: 1px solid #888;
            width: 300px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.25);
        }
    </style>
</head>
<body>

<?php include 'menu_cliente.php'; ?>

<div class="container">
    <h2>Editar Cliente</h2>

    <?php
    if (isset($_SESSION['message'])) {
        echo '<div class="message">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message']);
    }
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <form method="POST" action="editar_cliente.php" id="edit-cliente-form">
        <input type="hidden" name="id" value="<?= htmlspecialchars($edit_row['id']) ?>">
        <label>Login:
            <input type="text" name="login" value="<?= htmlspecialchars($edit_row['login']) ?>" required maxlength="30">
        </label>
        <label>Nome:
            <input type="text" name="nome" value="<?= htmlspecialchars($edit_row['nome']) ?>" required maxlength="100">
        </label>
        <label>Email:
            <input type="email" name="email" value="<?= htmlspecialchars($edit_row['email']) ?>" required maxlength="255">
        </label>
        <label>CPF:
            <input type="text" name="cpf" value="<?= htmlspecialchars($edit_row['cpf']) ?>" required pattern="\d{11}" title="CPF deve conter 11 dígitos numéricos">
        </label>
        <label>Data de Nascimento:
            <input type="date" name="data_nasc" value="<?= htmlspecialchars($edit_row['data_nasc']) ?>" required>
        </label>
        <label>Estado:
            <input type="text" name="end_estado" value="<?= htmlspecialchars($edit_row['end_estado']) ?>" required maxlength="255">
        </label>
        <label>Cidade:
            <input type="text" name="end_cidade" value="<?= htmlspecialchars($edit_row['end_cidade']) ?>" required maxlength="255">
        </label>
        <label>Bairro:
            <input type="text" name="end_bairro" value="<?= htmlspecialchars($edit_row['end_bairro']) ?>" required maxlength="255">
        </label>
        <label>Logradouro:
            <input type="text" name="end_logradouro" value="<?= htmlspecialchars($edit_row['end_logradouro']) ?>" required maxlength="255">
        </label>
        <button type="submit">Atualizar</button>
        <button type="submit" name="cancelar">Cancelar</button>
    </form>
</div>

<div id="confirmation-modal" class="modal" style="display:none;">
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
    const form = document.getElementById('edit-cliente-form');

    form.addEventListener('submit', (event) => {
        if (event.submitter && event.submitter.name === 'cancelar') {
            return;
        }
        event.preventDefault();
        modalMessage.textContent = 'Deseja salvar as alterações deste cliente?';
        modal.style.display = 'block';
    });

    confirmBtn.addEventListener('click', () => {
        form.submit();
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
</script>

</body>
</html>
