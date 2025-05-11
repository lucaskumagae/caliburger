<?php
session_start();
include 'menu_cliente.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Pedido Realizado</title>
    <link rel="stylesheet" href="main.css" />
    <style>
        .success-message {
            max-width: 400px;
            margin: 100px auto;
            padding: 40px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            text-align: center;
            font-family: 'Poppins', sans-serif;
            color: #27ae60;
            font-size: 1.5em;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <main class="success-message">
        Pedido realizado com sucesso!
    </main>
</body>
</html>
