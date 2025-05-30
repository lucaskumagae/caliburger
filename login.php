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
  <title>Tela de Login - Cali Burger</title>
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
        <h2>Login</h2>
        <form action="valida_login.php" method="post">
          <label for="login">Usuário</label>
          <input type="text" id="login" name="login" required />

          <label for="senha">Senha</label>
          <input type="password" id="senha" name="senha" required />

          <button type="submit">Entrar</button>

          <?php
          if (isset($_GET['erro'])) {
              echo "<p style='color:red; text-align:center; margin-top:10px;'>Login inválido</p>";
          }
          if (isset($_GET['cadastro']) && $_GET['cadastro'] === 'ok') {
              echo "<p style='color:green; text-align:center; margin-top:10px;'>Cadastro realizado com sucesso! Faça login para continuar.</p>";
          }
          ?>

          <p>Não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>
          <p>Login para Balconista Dono? <a href="login_balconista.php">Clique aqui</a></p>
          <p>Login para Cozinheiro? <a href="login_cozinheiro.php">Clique aqui</a></p>
        </form>
      </div>
    </div>

  </div>

</body>
</html>
