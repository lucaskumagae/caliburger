<?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit();
}
include 'conexao.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'balconista_dono') {
        $required_fields = ['cpf', 'nome', 'data_nasc', 'email', 'sexo', 'senha', 'confirmar_senha'];
        $missing_fields = false;
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $missing_fields = true;
                break;
            }
        }

        if ($missing_fields) {
            $message = '<p style="color:red;">Por favor, preencha todos os campos obrigatórios.</p>';
        } else {
            if ($_POST['senha'] !== $_POST['confirmar_senha']) {
                $message = '<p style="color:red;">As senhas não coincidem.</p>';
            } else {
                $cpf = $_POST['cpf'];
                $nome = $_POST['nome'];
                $data_nasc = $_POST['data_nasc'];
                $email = $_POST['email'];
                $sexo = $_POST['sexo'];

                // Validate CPF format (basic check: 11 digits)
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
                if (!validaCPF($cpf)) {
                    $message = '<p style="color:red;">CPF inválido.</p>';
                }
                // Validate birth date is not in the future
                else if (strtotime($data_nasc) > time()) {
                    $message = '<p style="color:red;">Data de nascimento inválida. Não pode ser uma data futura.</p>';
                }
                else {
                    // Check if CPF already exists
                    $stmt_check = $conn->prepare("SELECT cpf FROM balconista_dono WHERE cpf = ?");
                    $stmt_check->bind_param("s", $cpf);
                    $stmt_check->execute();
                    $result_check = $stmt_check->get_result();
                    if ($result_check->num_rows > 0) {
                        $message = '<p style="color:red;">CPF já cadastrado.</p>';
                    } else {
                        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

                        $stmt = $conn->prepare("INSERT INTO balconista_dono (cpf, nome, data_nasc, email, sexo, senha) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("ssssss", $cpf, $nome, $data_nasc, $email, $sexo, $senha);

                        if ($stmt->execute()) {
                            $message = '<p style="color:green;">Usuário adicionado com sucesso!</p>';
                        } else {
                            $message = '<p style="color:red;">Erro ao adicionar usuário: ' . $stmt->error . '</p>';
                        }
                    }
                    $stmt_check->close();
                }
            }
        }
    } elseif (isset($_POST['form_type']) && $_POST['form_type'] === 'cozinheiro') {
        $required_fields = ['cpf', 'nome', 'data_nasc', 'email', 'sexo', 'senha', 'confirmar_senha'];
        $missing_fields = false;
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $missing_fields = true;
                break;
            }
        }

        if ($missing_fields) {
            $message = '<p style="color:red;">Por favor, preencha todos os campos obrigatórios.</p>';
        } else {
            if ($_POST['senha'] !== $_POST['confirmar_senha']) {
                $message = '<p style="color:red;">As senhas não coincidem.</p>';
            } else {
                $cpf = $_POST['cpf'];
                $nome = $_POST['nome'];
                $data_nasc = $_POST['data_nasc'];
                $email = $_POST['email'];
                $sexo = $_POST['sexo'];

                // Validate CPF format (basic check: 11 digits)
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
                if (!validaCPF($cpf)) {
                    $message = '<p style="color:red;">CPF inválido.</p>';
                }
                // Validate birth date is not in the future
                else if (strtotime($data_nasc) > time()) {
                    $message = '<p style="color:red;">Data de nascimento inválida. Não pode ser uma data futura.</p>';
                }
                else {
                    // Check if CPF already exists
                    $stmt_check = $conn->prepare("SELECT cpf FROM cozinheiro WHERE cpf = ?");
                    $stmt_check->bind_param("s", $cpf);
                    $stmt_check->execute();
                    $result_check = $stmt_check->get_result();
                    if ($result_check->num_rows > 0) {
                        $message = '<p style="color:red;">CPF já cadastrado.</p>';
                    } else {
                        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

                        $stmt = $conn->prepare("INSERT INTO cozinheiro (cpf, nome, data_nasc, email, sexo, senha) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("ssssss", $cpf, $nome, $data_nasc, $email, $sexo, $senha);

                        if ($stmt->execute()) {
                            $message = '<p style="color:green;">Cozinheiro adicionado com sucesso!</p>';
                        } else {
                            $message = '<p style="color:red;">Erro ao adicionar cozinheiro: ' . $stmt->error . '</p>';
                        }
                    }
                    $stmt_check->close();
                }
            }
        }
    }
}

$usuarios = $conn->query("SELECT * FROM balconista_dono");
$cozinheiros = $conn->query("SELECT * FROM cozinheiro");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Página Principal - Cali Burger</title>
    <link rel="stylesheet" href="main.css">
    <style>
        .container {
            max-width: 1200px !important;
        }
        h3 {
            margin-top: 40px;
        }
    </style>
</head>
<body>

<?php include 'menu.php'; ?> 

<div class="container">
    <h2>Bem-vindo <?php echo $_SESSION['nome']; ?>!</h2>
    <p>Gerencie os usuários abaixo:</p>

    <form action="" method="POST" class="form-inline">
        <input type="hidden" name="form_type" value="balconista_dono">
        <input type="text" name="cpf" placeholder="CPF" required>
        <input type="text" name="nome" placeholder="Nome" required>
        <input type="date" name="data_nasc" placeholder="Data de Nascimento" required>
        <input type="email" name="email" placeholder="Email" required>
        <select name="sexo" required>
            <option value="" disabled selected>Sexo</option>
            <option value="Masculino">Masculino</option>
            <option value="Feminino">Feminino</option>
        </select>
        <input type="password" name="senha" placeholder="Senha" required>
        <input type="password" name="confirmar_senha" placeholder="Confirmar Senha" required>
        <button type="submit">Adicionar Usuário</button>
    </form>
    <h3>Balconistas</h3>
    <table class="styled-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>CPF</th>
                <th>Nome</th>
                <th>Data de Nascimento</th>
                <th>Email</th>
                <th>Sexo</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $usuarios->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['cpf'] ?></td>
                    <td><?= $row['cpf'] ?></td>
                    <td><?= $row['nome'] ?></td>
                    <td><?= $row['data_nasc'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['sexo'] ?></td>
                    <td>
                        <form action="delete_balconista_dono.php" method="POST" onsubmit="return confirm('Deseja excluir este usuário?');">
                            <input type="hidden" name="cpf" value="<?= $row['cpf'] ?>">
                            <button type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h3>Cadastro de Cozinheiro</h3>
    <form action="" method="POST" class="form-inline">
        <input type="hidden" name="form_type" value="cozinheiro">
        <input type="text" name="cpf" placeholder="CPF" required>
        <input type="text" name="nome" placeholder="Nome" required>
        <input type="date" name="data_nasc" placeholder="Data de Nascimento" required>
        <input type="email" name="email" placeholder="Email" required>
        <select name="sexo" required>
            <option value="" disabled selected>Sexo</option>
            <option value="Masculino">Masculino</option>
            <option value="Feminino">Feminino</option>
        </select>
        <input type="password" name="senha" placeholder="Senha" required>
        <input type="password" name="confirmar_senha" placeholder="Confirmar Senha" required>
        <button type="submit">Adicionar Cozinheiro</button>
    </form>

    <h3>Cozinheiros</h3>
    <table class="styled-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>CPF</th>
                <th>Nome</th>
                <th>Data de Nascimento</th>
                <th>Email</th>
                <th>Sexo</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $cozinheiros->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['cpf'] ?></td>
                    <td><?= $row['cpf'] ?></td>
                    <td><?= $row['nome'] ?></td>
                    <td><?= $row['data_nasc'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['sexo'] ?></td>
                    <td>
                        <form action="delete_cozinheiro.php" method="POST" onsubmit="return confirm('Deseja excluir este cozinheiro?');">
                            <input type="hidden" name="cpf" value="<?= $row['cpf'] ?>">
                            <button type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <?php echo $message; ?>
</div>

</body>
</html>
