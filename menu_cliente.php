<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<header>
    <div class="logo-area">
        <img src="imagens/logo_cali_sem_fundo.png" alt="Logo Cali Burger">
        <h1>Cali Burger</h1>
    </div>
</header>

<nav>
    <a href="cardapio_cliente.php">Cardápio</a>
    <a href="carrinho.php">Carrinho</a>
    <?php if (!isset($_SESSION['nome'])): ?>
        <a href="login.php">Log-in</a>
    <?php else: ?>
        <a href="sair.php" class="logout">Sair</a>
    <?php endif; ?>
</nav>
