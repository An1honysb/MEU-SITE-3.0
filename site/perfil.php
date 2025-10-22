<?php
session_start();

// Verifica se usuário está logado
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Location: index.php');
    exit;
}

$erro = '';
$sucesso = '';

$nome = $_SESSION['nome'] ?? '';
$usuario = $_SESSION['usuario'] ?? '';
$fotoAtual = $_SESSION['foto'] ?? 'uploads/avatar-padrao.png';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novoNome = trim($_POST['nome'] ?? '');
    $novoUsuario = trim($_POST['usuario'] ?? '');
    $novaSenha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    if (!$novoNome || !$novoUsuario) {
        $erro = 'Nome e usuário são obrigatórios.';
    } elseif ($novaSenha !== '' && strlen($novaSenha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($novaSenha !== $confirmarSenha) {
        $erro = 'As senhas não coincidem.';
    } else {
        $_SESSION['nome'] = $novoNome;
        $_SESSION['usuario'] = $novoUsuario;

        if ($novaSenha !== '') {
            $_SESSION['senha'] = $novaSenha;
        }

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
            $arquivoTmp = $_FILES['foto']['tmp_name'];
            $nomeOriginal = $_FILES['foto']['name'];
            $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

            if (in_array($extensao, $extensoesPermitidas)) {
                if (!is_dir('uploads')) {
                    mkdir('uploads', 0755, true);
                }
                $novoNomeFoto = uniqid('foto_') . '.' . $extensao;
                $destino = 'uploads/' . $novoNomeFoto;
                if (move_uploaded_file($arquivoTmp, $destino)) {
                    $_SESSION['foto'] = $destino;
                    $fotoAtual = $destino;
                } else {
                    $erro = 'Erro ao salvar a foto.';
                }
            } else {
                $erro = 'Formato de foto não permitido.';
            }
        }

        if (!$erro) {
            $sucesso = 'Perfil atualizado com sucesso!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<nav class="barra-navegacao">
  <ul>
    <li><a href="feed.php">🏠 Início</a></li>
    <li><a href="#">🔍 Pesquisa</a></li>
    <li><a href="#">➕ Nova Postagem</a></li>
    <li><a href="perfil.php">👤 Perfil</a></li>
    <li><a href="logout.php" style="color:#74d69d;">Sair</a></li>
  </ul>
</nav>

<div class="centralizador">
    <div class="container-editar-perfil">
        <h1>Editar Perfil</h1>

        <?php if ($erro): ?>
            <div class="erro"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <div class="sucesso"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nome:</label>
                <input type="text" name="nome" required value="<?= htmlspecialchars($nome) ?>" />
            </div>

            <div class="form-group">
                <label>Usuário:</label>
                <input type="text" name="usuario" required value="<?= htmlspecialchars($usuario) ?>" />
            </div>

            <div class="form-group">
                <label>Nova Senha (deixe vazio para não alterar):</label>
                <input type="password" name="senha" minlength="6" />
            </div>

            <div class="form-group">
                <label>Confirmar Nova Senha:</label>
                <input type="password" name="confirmar_senha" minlength="6" />
            </div>

            <div class="form-group">
                <label>Foto Atual:</label><br />
                <img src="<?= htmlspecialchars($fotoAtual) ?>" alt="Foto de Perfil" style="width:100px; height:100px; border-radius:50%;" />
            </div>

            <div class="form-group">
                <label>Alterar Foto:</label>
                <input type="file" name="foto" accept="image/*" />
            </div>

            <button type="submit" class="botao-editar">Salvar Alterações</button>
        </form>
    </div>
</div>
</body>
</html>
