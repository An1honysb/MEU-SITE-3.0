<?php
session_start();

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Location: index.php');
    exit;
}

if (empty($_SESSION)) {
    $nome = '';
    $usuario = '';
    $foto = '';
} else {
    $nome = $_SESSION['nome'] ?? 'Anthony Santos Batista';
    $usuario = $_SESSION['usuario'] ?? 'anthony_s.b';
    $foto = $_SESSION['foto'] ?? 'uploads/avatar-padrao.png';
}


$arquivoPosts = 'posts.json';
$posts = file_exists($arquivoPosts) ? json_decode(file_get_contents($arquivoPosts), true) : [];

if (!isset($_SESSION['comments'])) {
    $_SESSION['comments'] = [];
}

// Criar nova postagem e salvar no arquivo JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensagem']) && trim($_POST['mensagem']) !== '' && !isset($_POST['comment'])) {
    $nova_postagem = [
        'nome' => $nome,
        'usuario' => $usuario,
        'foto' => $foto,
        'mensagem' => htmlspecialchars(trim($_POST['mensagem'])),
        'data' => date('d/m/Y H:i'),
        'likes' => 0
    ];

    array_unshift($posts, $nova_postagem);
    file_put_contents($arquivoPosts, json_encode($posts, JSON_PRETTY_PRINT));
    header("Location: feed.php");
    exit;
}

// Incrementar likes
if (isset($_GET['like'])) {
    $idx = (int)$_GET['like'];
    if (isset($posts[$idx])) {
        $posts[$idx]['likes']++;
        file_put_contents($arquivoPosts, json_encode($posts, JSON_PRETTY_PRINT));
    }
    header("Location: feed.php");
    exit;
}

// Processar comentários e salvar na sessão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isset($_POST['post_idx'])) {
    $idx = (int)$_POST['post_idx'];
    $comentario = trim($_POST['comment']);
    if ($comentario !== '') {
        if (!isset($_SESSION['comments'][$idx])) {
            $_SESSION['comments'][$idx] = [];
        }
        $_SESSION['comments'][$idx][] = [
            'usuario' => $usuario,
            'mensagem' => htmlspecialchars($comentario),
            'data' => date('d/m/Y H:i'),
        ];
    }
    header("Location: feed.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>Feed - Minha Rede Social</title>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<nav class="barra-navegacao">
  <ul>
    <li><a href="#">🏠 Início</a></li>
    <li><a href="#">🔍 Pesquisa</a></li>
    <li><a href="#">➕ Nova Postagem</a></li>
    <li><a href="#">👤 Perfil</a></li>
    <li><a href="logout.php" style="color:#74d69d;">Sair</a></li>
  </ul>
</nav>
<main class="conteudo-principal">
  <header class="cabecalho-pagina">
    <div class="info-perfil">
      <img src="<?= htmlspecialchars($foto) ?>" alt="Foto de Perfil" class="avatar" />
      <div class="info-texto-perfil">
        <span class="nome-usuario"><?= htmlspecialchars($nome) ?></span>
        <span class="nickname-usuario">@<?= htmlspecialchars($usuario) ?></span>
      </div>
      <a href="cadastro.php" class="btn btn-editar-perfil">Editar Perfil</a>
    </div>
    <form class="form-nova-postagem" method="post" action="">
      <textarea name="mensagem" placeholder="No que está pensando?" required></textarea>
      <div class="form-actions">
        <button type="submit" class="btn btn-postar">Postar</button>
      </div>
    </form>
  </header>

  <section class="feed">
    <?php foreach ($posts as $idx => $post): ?>
      <article class="post">
        <div class="post-header">
          <img src="<?= htmlspecialchars($post['foto']) ?>" alt="Foto de Perfil" class="avatar-post" />
          <div class="post-info-autor">
            <span class="nome-autor"><?= htmlspecialchars($post['nome']) ?></span>
            <span class="nickname-autor">@<?= htmlspecialchars($post['usuario']) ?></span>
            <span style="font-size:12px;color:#abb2f9;"><?= $post['data'] ?></span>
          </div>
        </div>
        <div class="post-content">
          <p><?= nl2br(htmlspecialchars($post['mensagem'])) ?></p>
        </div>
        <div class="post-footer">
          <div class="post-actions">
            <form style="display:inline;" method="get" action="">
              <input type="hidden" name="like" value="<?= $idx ?>" />
              <button type="submit" class="like-btn" title="Curtir">❤</button>
            </form>
            <label><?= $post['likes'] ?> likes</label>
          </div>
        </div>
        <div class="comment-list">
          <?php if (!empty($_SESSION['comments'][$idx])): ?>
            <?php foreach ($_SESSION['comments'][$idx] as $coment): ?>
              <div class="comment-item">
                <span class="comment-user"><?= htmlspecialchars($coment['usuario']) ?></span>:
                <?= nl2br(htmlspecialchars($coment['mensagem'])) ?>
                <span class="comment-date">(<?= $coment['data'] ?>)</span>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <form class="comment-form" method="post" action="">
          <textarea name="comment" placeholder="Escreva um comentário..." rows="2" required></textarea>
          <input type="hidden" name="post_idx" value="<?= $idx ?>" />
          <button type="submit">Comentar</button>
        </form>
      </article>
    <?php endforeach; ?>
  </section>
</main>
</body>
</html>
