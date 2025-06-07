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

<style>
    nav a {
        color: white;
        text-decoration: none;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 8px;
        transition: background-color 0.3s;
        margin-right: 30px;
    }
    nav a:hover {
        background-color: #c0392b;
    }
    nav .logout {
        background-color: #ffffff22;
        border: 1px solid white;
    }
    header, nav {
        display: flex;
        align-items: center;
        padding: 20px;
        background-color: #c0392b;
        color: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .logo-area {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .logo-area img {
        height: 60px;
        width: auto;
    }
    .logo-area h1 {
        font-size: 1.8em;
    }
</style>

<nav>
    <a href="pedidos_cozinheiro.php">Pedidos</a>
    <a href="cardapio_cozinheiro.php">Cardápio</a>
    <a href="perfil_cozinheiro.php">Olá, <?= isset($_SESSION['nome']) ? htmlspecialchars(explode(' ', trim($_SESSION['nome']))[0]) : 'Usuário' ?></a>
    <a href="sair.php" class="logout">Sair</a>
</nav>
