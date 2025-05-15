<?php
session_start();
if (isset($_SESSION['nome'])) {
    header("Location: main.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Balconista Dono - Cali Burger</title>
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
      <div class="login-container">
        <h2>Login Balconista Dono</h2>
        <form action="valida_login_balconista.php" method="post">
          <label for="cpf">CPF</label>
          <input type="text" id="cpf" name="cpf" required />

          <label for="senha">Senha</label>
          <input type="password" id="senha" name="senha" required />

          <button type="submit">Entrar</button>

          <?php
          if (isset($_GET['erro'])) {
              if ($_GET['erro'] === 'cpf') {
                  echo "<p style='color:red; text-align:center; margin-top:10px;'>CPF inválido</p>";
              } elseif ($_GET['erro'] === 'senha') {
                  echo "<p style='color:red; text-align:center; margin-top:10px;'>Senha inválida</p>";
              } else {
                  echo "<p style='color:red; text-align:center; margin-top:10px;'>Erro de login</p>";
              }
          }
          ?>

          <p>Login para Cliente? <a href="login.php">Clique aqui</a></p>
        </form>
      </div>
    </div>

  </div>

</body>
</html>
