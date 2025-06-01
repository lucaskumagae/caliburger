<header>
    <div class="logo-area">
        <img src="imagens/logo_cali_sem_fundo.png" alt="Logo Cali Burger">
        <h1>Cali Burger</h1>
    </div>
</header>

<nav>
    <a href="cardapio_cliente.php">Cardápio</a>
    <a href="carrinho.php">Carrinho</a>
    <a href="meus_pedidos.php">Meus pedidos</a>
    <?php if (!isset($_SESSION['nome'])): ?>
        <a href="login.php">Log-in</a>
    <?php else: 
        $first_name = explode(' ', trim($_SESSION['nome']))[0];
    ?>
        <a href="perfil_cliente.php" style="display:flex; align-items:center; gap:8px; color:white; text-decoration:none;">
            <img src="imagens/user_icon.png" alt="User Icon" style="width:24px; height:24px;">
            Olá, <?= htmlspecialchars($first_name) ?>
        </a>
        <a href="sair.php" class="logout">Sair</a>
    <?php endif; ?>
</nav>
</create_file>
