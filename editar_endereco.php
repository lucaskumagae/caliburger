<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $end_estado = $_POST['end_estado'] ?? '';
    $end_cidade = $_POST['end_cidade'] ?? '';
    $end_bairro = $_POST['end_bairro'] ?? '';
    $end_logradouro = $_POST['end_logradouro'] ?? '';

    $stmt = $conn->prepare("UPDATE cliente SET end_estado = ?, end_cidade = ?, end_bairro = ?, end_logradouro = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $end_estado, $end_cidade, $end_bairro, $end_logradouro, $user_id);
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: pagamento.php");
        exit();
    } else {
        $error = "Erro ao atualizar endereço. Tente novamente.";
        $stmt->close();
    }
} else {
    // Load current address
    $stmt = $conn->prepare("SELECT end_estado, end_cidade, end_bairro, end_logradouro FROM cliente WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $end_estado = $row['end_estado'];
        $end_cidade = $row['end_cidade'];
        $end_bairro = $row['end_bairro'];
        $end_logradouro = $row['end_logradouro'];
    } else {
        $end_estado = $end_cidade = $end_bairro = $end_logradouro = '';
    }
    $stmt->close();
}
include 'menu_cliente.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Editar Endereço - Cali Burger</title>
    <link rel="stylesheet" href="main.css" />
    <style>
        .edit-endereco-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            font-family: 'Poppins', sans-serif;
            color: #333;
        }
        .edit-endereco-container h1 {
            text-align: center;
            color: #333333;
            margin-bottom: 20px;
            font-weight: 700;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 15px;
            font-weight: 600;
        }
        input[type="text"] {
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1em;
            margin-top: 5px;
        }
        button.save-button {
            margin-top: 25px;
            padding: 12px 30px;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.2em;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button.save-button:hover {
            background-color: #219150;
        }
        .error-message {
            color: red;
            margin-top: 15px;
            text-align: center;
        }
        a.button-voltar {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 25px;
            background-color: #333333;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            transition: background-color 0.3s ease;
        }
        a.button-voltar:hover {
            background-color: #555555;
        }
    </style>
</head>
<body>
    <main class="edit-endereco-container">
        <h1>Editar Endereço</h1>
        <?php if (isset($error)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="post" action="editar_endereco.php">
            <label for="end_logradouro">Logradouro</label>
            <input type="text" id="end_logradouro" name="end_logradouro" value="<?php echo htmlspecialchars($end_logradouro); ?>" required />

            <label for="end_bairro">Bairro</label>
            <input type="text" id="end_bairro" name="end_bairro" value="<?php echo htmlspecialchars($end_bairro); ?>" required />

            <label for="end_cidade">Cidade</label>
            <input type="text" id="end_cidade" name="end_cidade" value="<?php echo htmlspecialchars($end_cidade); ?>" required />

            <label for="end_estado">Estado</label>
            <input type="text" id="end_estado" name="end_estado" value="<?php echo htmlspecialchars($end_estado); ?>" required />

            <button type="submit" class="save-button">Salvar</button>
        </form>
        <a href="pagamento.php" class="button-voltar">Voltar ao Pagamento</a>
    </main>
</body>
</html>
