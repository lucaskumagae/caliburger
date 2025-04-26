<?php
include 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'];
    $senha = $_POST['senha'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $cpf = $_POST['cpf'];
    $data_nasc = $_POST['data_nascimento'];  // campo do form ainda se chama data_nascimento
    $estado = $_POST['estado'];
    $cidade = $_POST['cidade'];
    $bairro = $_POST['bairro'];
    $logradouro = $_POST['logradouro'];

    $sql = "INSERT INTO cliente (login, senha, nome, email, cpf, data_nasc, end_estado, end_cidade, end_bairro, end_logradouro)
            VALUES ('$login', '$senha', '$nome', '$email', '$cpf', '$data_nasc', '$estado', '$cidade', '$bairro', '$logradouro')";

    if ($conn->query($sql) === TRUE) {
        header("Location: login.php?cadastro=ok");
        exit();
    } else {
        echo "Erro ao cadastrar: " . $conn->error;
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
