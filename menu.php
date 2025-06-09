<header>
    <div class="logo-area">
        <img src="imagens/logo_cali_sem_fundo.png" alt="Logo Cali Burger">
        <h1>Cali Burger</h1>
    </div>
</header>

<nav>
    <a href="main.php">Início</a>
    <a href="pedidos.php">Pedidos</a>
    <a href="cardapio.php">Cardápio</a>
    <a href="estoque.php">Estoque</a>
    <?php
    if (isset($_SESSION['nome'])) {
        $first_name = explode(' ', trim($_SESSION['nome']))[0];
        echo '<a href="perfil_balconista_dono.php" style="display:flex; align-items:center; gap:8px; color:white; text-decoration:none;">';
        echo '<img src="imagens/user_icon.png" alt="User Icon" style="width:24px; height:24px;">';
        echo 'Olá, ' . htmlspecialchars($first_name);
        echo '</a>';
    }
    ?>
    <a href="sair.php" class="logout">Sair</a>
</nav>
