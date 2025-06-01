<?php
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

$error = '';
$login = $senha = $nome = $email = $cpf = $data_nasc = $estado = $cidade = $bairro = $logradouro = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'];
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $cpfInput = $_POST['cpf'];
    $dataNascInput = $_POST['data_nascimento'];
    $estado = $_POST['estado'];
    $cidade = $_POST['cidade'];
    $bairro = $_POST['bairro'];
    $logradouro = $_POST['logradouro'];

    $cpfValido = validaCPF($cpfInput);
    $dataValida = validaDataNascimento($dataNascInput);

    if (!$cpfValido) {
        $error = "CPF inválido.";
        $cpf = '';
    } else {
        $cpf = $cpfInput;
    }
    if (!$dataValida) {
        $error = $error ? $error . " Data de nascimento inválida." : "Data de nascimento inválida.";
        $data_nasc = '';
    } else {
        $data_nasc = $dataNascInput;
    }

    if ($senha !== $confirmar_senha) {
        $error = "As senhas não coincidem.";
    }

    if ($cpfValido && $dataValida && $senha === $confirmar_senha) {
        // Check if login or CPF already exists
        $stmt_check = $conn->prepare("SELECT * FROM cliente WHERE login = ? OR cpf = ?");
        $stmt_check->bind_param("ss", $login, $cpf);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows > 0) {
            $error = "Login ou CPF já cadastrado.";
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "INSERT INTO cliente (login, senha, nome, email, cpf, data_nasc, end_estado, end_cidade, end_bairro, end_logradouro)
                    VALUES ('$login', '$senha_hash', '$nome', '$email', '$cpf', '$data_nasc', '$estado', '$cidade', '$bairro', '$logradouro')";

            if ($conn->query($sql) === TRUE) {
                header("Location: login.php?cadastro=ok");
                exit();
            } else {
                $error = "Erro ao cadastrar: " . $conn->error;
            }
        }
        $stmt_check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>Cadastro - Cali Burger</title>
  <link rel="stylesheet" href="login.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
  <link rel="icon" href="imagens/logo_cali_ico.ico" type="image/x-icon" />
</head>
<body>

<div class="container">

  <div class="left-side">
    <img src="imagens/logo_cali_sem_fundo.png" alt="Logo" class="logo" />
  </div>

  <div class="right-side">
    <div class="login-container cadastro-container">
      <h2>Cadastro</h2>
      <?php if ($error): ?>
        <p style="color:red; text-align:center; margin-bottom: 10px;"><?php echo $error; ?></p>
      <?php endif; ?>
      <form method="post" action="cadastro.php" class="form-grid">

        <div class="form-group">
          <label for="login">Usuário</label>
          <input type="text" name="login" id="login" required />
        </div>

        <div class="form-group">
          <label for="senha">Senha</label>
          <input type="password" name="senha" id="senha" required />
        </div>

        <div class="form-group">
          <label for="confirmar_senha">Confirmar Senha</label>
          <input type="password" name="confirmar_senha" id="confirmar_senha" required />
        </div>

        <div class="form-group">
          <label for="nome">Nome</label>
          <input type="text" name="nome" id="nome" required />
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" name="email" id="email" required />
        </div>

        <div class="form-group">
          <label for="cpf">CPF</label>
          <input type="text" name="cpf" id="cpf" required />
        </div>

        <div class="form-group">
          <label for="data_nascimento">Nascimento</label>
          <input type="date" name="data_nascimento" id="data_nascimento" required />
        </div>

        <div class="form-group">
          <label for="estado">Estado</label>
          <input type="text" name="estado" id="estado" required />
        </div>

        <div class="form-group">
          <label for="cidade">Cidade</label>
          <input type="text" name="cidade" id="cidade" required />
        </div>

        <div class="form-group">
          <label for="bairro">Bairro</label>
          <input type="text" name="bairro" id="bairro" required />
        </div>

        <div class="form-group">
          <label for="logradouro">Logradouro</label>
          <input type="text" name="logradouro" id="logradouro" required />
        </div>

        <div class="form-buttons">
          <button type="submit">Cadastrar</button>
          <p>Já tem uma conta? <a href="login.php">Voltar para o login</a></p>
        </div>

      </form>
    </div>
  </div>

</div>

</body>
</html>
