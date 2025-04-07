<?php
session_start();
if (!isset($_SESSION['nome'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Página Principal - Cali Burger</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <header>
        <h1>🍔 Cali Burger - Sistema</h1>
    </header>

    <nav>
        <a href="main.php">Início</a>
        <a href="pedidos.php">Pedidos</a>
        <a href="cardapio.php">Cardápio</a>
        <a href="estoque.php">Estoque</a>
        <a href="sair.php" class="logout">Sair</a>
    </nav>

    <div class="container">
        <h2>Bem-vindo, <?php echo $_SESSION['nome']; ?>!</h2>
    </div>
</body>
</html>
