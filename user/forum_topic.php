<?php
require_once __DIR__ . '/auth.php';
$pdo = pdo(); $uid = $warga['id']; $id=(int)($_GET['id']??0);

$st=$pdo->prepare("SELECT t.id,t.title,t.created_at,u.name FROM forum_topics t JOIN users u ON u.id=t.user_id WHERE t.id=?");
$st->execute([$id]); $topic=$st->fetch();
if(!$topic){ http_response_code(404); echo "Topik tidak ditemukan."; exit; }

if($_SERVER['REQUEST_METHOD']==='POST'){
  $content=trim($_POST['content']??'');
  if($content!==''){ $pdo->prepare("INSERT INTO forum_posts(topic_id,user_id,content) VALUES (?,?,?)")->execute([$id,$uid,$content]); header('Location: '.url('/user/forum_topic.php?id='.$id)); exit; }
}
$posts=$pdo->prepare("SELECT p.content,p.created_at,u.name FROM forum_posts p JOIN users u ON u.id=p.user_id WHERE p.topic_id=? ORDER BY p.created_at ASC");
$posts->execute([$id]); $posts=$posts->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" /><title><?=htmlspecialchars($topic['title'])?> — Forum</title><meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>:root{--padx:clamp(16px,5vw,48px)}.edge{padding-inline:var(--padx)}body{background:#f5f7fb}</style>
</head>
<body>
<nav class="navbar bg-white border-bottom sticky-top">
  <div class="container-fluid edge py-2">
    <a class="navbar-brand fw-semibold" href="<?= url('/user/forum.php') ?>"><i class="bi bi-arrow-left me-2"></i><?=htmlspecialchars($topic['title'])?></a>
    <div class="d-flex align-items-center gap-3"><span class="text-secondary small"><?=htmlspecialchars($warga['name'])?></span><a class="btn btn-sm btn-outline-danger" href="<?= url('/user/logout.php')?>">Logout</a></div>
  </div>
</nav>

<div class="container-fluid edge my-4">
  <div class="card shadow-sm mb-3"><div class="card-body">
    <div class="text-secondary small">Dibuat oleh <b><?=htmlspecialchars($topic['name'])?></b> • <?=htmlspecialchars($topic['created_at'])?></div>
  </div></div>

  <div class="card shadow-sm mb-3"><div class="card-body">
    <h5 class="card-title mb-3">Balasan</h5>
    <?php if(!$posts):?><div class="alert alert-info m-0">Belum ada balasan.</div><?php endif;?>
    <?php foreach($posts as $p): ?>
      <div class="border rounded-3 p-3 mb-2 bg-white">
        <div><?=nl2br(htmlspecialchars($p['content']))?></div>
        <div class="text-secondary small mt-2">oleh <?=htmlspecialchars($p['name'])?> • <?=htmlspecialchars($p['created_at'])?></div>
      </div>
    <?php endforeach;?>
  </div></div>

  <div class="card shadow-sm"><div class="card-body">
    <h5 class="card-title mb-3">Tulis Balasan</h5>
    <form method="post">
      <textarea class="form-control" name="content" rows="4" placeholder="Ketik balasan..."></textarea>
      <div class="mt-2">
        <button class="btn btn-success"><i class="bi bi-send me-1"></i>Kirim</button>
        <a class="btn btn-outline-secondary ms-1" href="<?= url('/user/forum.php')?>">Kembali</a>
      </div>
    </form>
  </div></div>
</div>
</body></html>
