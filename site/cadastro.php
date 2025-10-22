<?php
session_start();

$erro = '';
$dados = [
    'nome' => '',
    'usuario' => '',
    'email' => '',
    'senha' => '',
    'nascimento' => '',
    'genero' => '',
    'foto' => '',
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dados['nome']        = trim($_POST['nome'] ?? '');
    $dados['usuario']     = trim($_POST['usuario'] ?? '');
    $dados['email']       = trim($_POST['email'] ?? '');
    $dados['senha']       = $_POST['senha'] ?? '';
    $confirmar            = $_POST['confirmar'] ?? '';
    $dados['nascimento']  = $_POST['nascimento'] ?? '';
    $dados['genero']      = $_POST['genero'] ?? '';

    // Validação dos campos obrigatórios
    if (!$dados['nome'] || !$dados['usuario'] || !$dados['email'] || !$dados['senha'] || !$confirmar || !$dados['nascimento'] || !$dados['genero']) {
        $erro = 'Preencha todos os campos obrigatórios.';
    } 
    // Validação de email
    elseif (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        $erro = 'Email inválido.';
    } 
    // Validação de senha - 6+ caracteres, 1 maiúscula e 1 número
    elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d).{6,}$/', $dados['senha'])) {
        $erro = 'A senha deve ter pelo menos 6 caracteres, incluir 1 letra maiúscula e 1 número.';
    } 
    // Confirmação de senha
    elseif ($dados['senha'] !== $confirmar) {
        $erro = 'As senhas não coincidem.';
    } 
    // Validação da data de nascimento
    elseif (strtotime($dados['nascimento']) === false || strtotime($dados['nascimento']) > time()) {
        $erro = 'Data de nascimento inválida.';
    } 
    // Validação de gênero
    elseif (!in_array($dados['genero'], ['Feminino', 'Masculino', 'Outro'])) {
        $erro = 'Selecione um gênero válido.';
    }

    // Upload da foto (opcional)
    if (!$erro && isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        $arquivoTemporario = $_FILES['foto']['tmp_name'];
        $nomeArquivoOriginal = $_FILES['foto']['name'];
        $extensao = strtolower(pathinfo($nomeArquivoOriginal, PATHINFO_EXTENSION));

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $arquivoTemporario);
        finfo_close($finfo);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];

        if (!in_array($extensao, $extensoesPermitidas) || !in_array($mimeType, $allowedMimes)) {
            $erro = "Tipo de arquivo da foto não permitido. Use JPG, PNG ou GIF.";
        } else {
            if (!is_dir('uploads')) { 
                mkdir('uploads', 0755, true); 
            }
            $novoNome = uniqid("foto_") . "." . $extensao;
            $destino = 'uploads/' . $novoNome;
            if (move_uploaded_file($arquivoTemporario, $destino)) {
                $dados['foto'] = 'uploads/' . $novoNome;
            } else {
                $erro = "Erro ao salvar a foto.";
            }
        }
    } else {
        // Foto padrão se não enviar nenhuma
        $dados['foto'] = 'uploads/avatar-padrao.png';
    }

    // Caso tudo esteja OK, salvar na sessão
    if (!$erro) {
        $_SESSION['nome']       = $dados['nome'];
        $_SESSION['usuario']    = $dados['usuario'];
        $_SESSION['email']      = $dados['email'];
        $_SESSION['senha']      = $dados['senha']; // ⚠️ Em produção, use password_hash()
        $_SESSION['nascimento'] = $dados['nascimento'];
        $_SESSION['genero']     = $dados['genero'];
        $_SESSION['foto']       = $dados['foto'];
        $_SESSION['logado']     = true;

        header('Location: feed.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Cadastro - Minha Rede Social</title>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<div class="centralizador">
  <div class="container-cadastro">
    <h1>Cadastro</h1>
    <?php if (!empty($erro)): ?>
      <div class="erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>
    <form action="" method="post" enctype="multipart/form-data">
      <div class="form-group">
        <label for="nome">Nome:</label>
        <input type="text" name="nome" id="nome" required value="<?= htmlspecialchars($dados['nome']) ?>">
      </div>
      <div class="form-group">
        <label for="usuario">Usuário:</label>
        <input type="text" name="usuario" id="usuario" required value="<?= htmlspecialchars($dados['usuario']) ?>">
      </div>
      <div class="form-group">
        <label for="email">E-mail:</label>
        <input type="email" name="email" id="email" required value="<?= htmlspecialchars($dados['email']) ?>">
      </div>
      <div class="form-group">
        <label for="senha">Senha:</label>
        <input type="password" name="senha" id="senha" required minlength="6">
      </div>
      <div class="form-group">
        <label for="confirmar">Confirmar Senha:</label>
        <input type="password" name="confirmar" id="confirmar" required minlength="6">
      </div>
      <div class="form-group">
        <label for="nascimento">Data de Nascimento:</label>
        <input type="date" name="nascimento" id="nascimento" required value="<?= htmlspecialchars($dados['nascimento']) ?>">
      </div>
      <div class="form-group">
        <label for="genero">Gênero:</label>
        <select name="genero" id="genero" required>
          <option value="">Selecione</option>
          <option value="Feminino" <?= ($dados['genero']=='Feminino') ? 'selected' : '' ?>>Feminino</option>
          <option value="Masculino" <?= ($dados['genero']=='Masculino') ? 'selected' : '' ?>>Masculino</option>
          <option value="Outro" <?= ($dados['genero']=='Outro') ? 'selected' : '' ?>>Outro</option>
        </select>
      </div>
      <div class="form-group">
        <label for="foto">Foto de perfil (opcional):</label>
        <input type="file" name="foto" id="foto" accept="image/*">
      </div>
      <button type="submit" class="botao-cadastro">Cadastrar</button>
    </form>
    <div class="link-login">
      <span>Já tem conta?</span>
      <a href="index.php">Faça login</a>
    </div>
  </div>
</div>
</body>
</html>
