<?php
session_start();
$nome = $_POST['nome'];
$usuario = $_POST['usuario'];
$email = $_POST['email'];
$senha = $_POST['senha'];


$_SESSION['nome'] = $nome;
$_SESSION['usuario'] = $usuario;
$_SESSION['email'] = $email;
$_SESSION['senha'] = $senha;



?>