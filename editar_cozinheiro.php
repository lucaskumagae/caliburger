<?php
session_start();
if (!isset($_SESSION['cpf'])) {
    header("Location: login_cozinheiro.php");
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
    if (!isset($_GET['cpf']) || empty($_GET['cpf'])) {
        $_SESSION['message'] = "CPF do cozinheiro não fornecido.";
        header("Location: perfil_cozinheiro.php");
        exit();
    }

    $cpf = $_GET['cpf'];

    $stmt = $conn->prepare("SELECT cpf, nome, data_nasc, sexo, email, senha FROM cozinheiro WHERE cpf = ?");
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $_SESSION['message'] = "Cozinheiro não encontrado.";
        header("Location: perfil_cozinheiro.php");
        exit();
    }
    $edit_row = $result->fetch_assoc();

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cancelar'])) {
        header("Location: perfil_cozinheiro.php");
        exit();
    }

    if (!isset($_POST['cpf']) || empty($_POST['cpf']) || !isset($_POST['nome']) || !isset($_POST['data_nasc']) || !isset($_POST['sexo']) || !isset($_POST['email']) || !isset($_POST['senha'])) {
        $_SESSION['message'] = "Dados incompletos para atualização.";
        header("Location: editar_cozinheiro.php?cpf=" . urlencode($_POST['cpf']));
        exit();
    }

    $cpf = trim($_POST['cpf']);
    $nome = trim($_POST['nome']);
    $data_nasc = trim($_POST['data_nasc']);
    $sexo = trim($_POST['sexo']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    if ($nome === '' || $data_nasc === '' || $sexo === '' || $email === '' || $senha === '') {
        $_SESSION['message'] = "Todos os campos obrigatórios devem ser preenchidos.";
        header("Location: editar_cozinheiro.php?cpf=" . urlencode($cpf));
        exit();
    }

    if (!validaCPF($cpf)) {
        $_SESSION['message'] = "CPF inválido.";
        header("Location: editar_cozinheiro.php?cpf=" . urlencode($cpf));
        exit();
    }

    if (!validaDataNascimento($data_nasc)) {
        $_SESSION['message'] = "Data de nascimento inválida.";
        header("Location: editar_cozinheiro.php?cpf=" . urlencode($cpf));
        exit();
    }

    // Update cozinheiro data
    $stmt = $conn->prepare("UPDATE cozinheiro SET nome = ?, data_nasc = ?, sexo = ?, email = ?, senha = ? WHERE cpf = ?");
    $stmt->bind_param("ssssss", $nome, $data_nasc, $sexo, $email, $senha, $cpf);
    if (!$stmt->execute()) {
        $_SESSION['message'] = "Erro ao atualizar dados do cozinheiro.";
        header("Location: editar_cozinheiro.php?cpf=" . urlencode($cpf));
        exit();
    }

    header("Location: perfil_cozinheiro.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Cozinheiro - Cali Burger</title>
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
        input[type="text"], input[type="email"], input[type="date"], input[type="password"], select {
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
    </style>
</head>
<body>

<?php include 'menu_cozinheiro.php'; ?>

<div class="container">
    <h2>Editar Cozinheiro</h2>

    <?php
    if (isset($_SESSION['message'])) {
        echo '<div class="message">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message']);
    }
    ?>

    <form method="POST" action="editar_cozinheiro.php" id="edit-cozinheiro-form">
        <label>CPF:
            <input type="text" name="cpf" value="<?= htmlspecialchars($edit_row['cpf']) ?>" readonly>
        </label>
        <label>Nome:
            <input type="text" name="nome" value="<?= htmlspecialchars($edit_row['nome']) ?>" required maxlength="100">
        </label>
        <label>Data de Nascimento:
            <input type="date" name="data_nasc" value="<?= htmlspecialchars($edit_row['data_nasc']) ?>" required>
        </label>
        <label>Sexo:
            <select name="sexo" required>
                <option value="Masculino" <?= $edit_row['sexo'] == 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                <option value="Feminino" <?= $edit_row['sexo'] == 'Feminino' ? 'selected' : '' ?>>Feminino</option>
                <option value="Outro" <?= $edit_row['sexo'] == 'Outro' ? 'selected' : '' ?>>Outro</option>
            </select>
        </label>
        <label>Email:
            <input type="email" name="email" value="<?= htmlspecialchars($edit_row['email']) ?>" required maxlength="255">
        </label>
        <label>Senha:
            <input type="password" name="senha" value="<?= htmlspecialchars($edit_row['senha']) ?>" required>
        </label>
        <button type="submit">Atualizar</button>
        <button type="submit" name="cancelar">Cancelar</button>
    </form>
</div>

</body>
</html>
