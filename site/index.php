<?php
session_start();

$erro = '';
if (empty($_SESSION)) {
    $usuarioPadrao = '';
    $emailPadrao = '';
    $senhaPadrao = '';
} else {
    $usuarioPadrao = $_SESSION['usuario'];
    $emailPadrao = $_SESSION['email'];
    $senhaPadrao = $_SESSION['senha'];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entrada = trim($_POST['entrada'] ?? ''); // Pode ser email ou usuário
    $senha = $_POST['senha'] ?? '';


    if (($entrada === $usuarioPadrao || $entrada === $emailPadrao) && $senha === $senhaPadrao) {
        $_SESSION['logado'] = true;
        $_SESSION['usuario'] = $usuarioPadrao;
        $_SESSION['nome'] = $_SESSION['nome'];
        $_SESSION['foto'] = 'uploads/'.$_SESSION['foto'];
        header('Location: feed.php');
        exit;
    }
    if (isset($_SESSION['usuario'], $_SESSION['email'], $_SESSION['senha'])) {
        if (($usuarioPadrao === $_SESSION['usuario'] || $entrada === $_SESSION['email']) && $senha === $_SESSION['senha']) {
            $_SESSION['logado'] = true;
            header('Location: feed.php');
            exit;
        } else {
            $erro = "Usuário, e-mail ou senha inválidos.";
        }
    } else {
        $erro = "Nenhum usuário cadastrado. Cadastre-se primeiro.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Login - Minha Rede Social</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<div class="centralizador">
    <div class="container-login">
        <h1>Login</h1>
        <?php if (!empty($erro)): ?>
            <div class="erro"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label for="entrada">E-mail ou Usuário:</label>
                <input type="text" name="entrada" id="entrada" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" name="senha" id="senha" required minlength="6">
            </div>
            <button type="submit" class="botao-login">Entrar</button>
        </form>
        <div class="link-cadastro">
            <span>Não tem conta?</span>
            <a href="cadastro.php">Cadastre-se</a>
        </div>
    </div>
</div>
</body>
</html>
