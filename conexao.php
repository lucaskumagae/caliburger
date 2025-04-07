<?php
$servername = "localhost:3306";
$username = "root";
$password = "";
$dbname = "db_caliburger";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>
