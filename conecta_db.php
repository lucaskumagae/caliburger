<?php
	function conecta_db(){
		$db_name = "db_cali";
		$user    = "root";
		$pass    = "";
		$server  = "localhost:3306";
		$conexao = new mysqli($server, $user, $pass, $db_name);
		return $conexao;
	}
?>