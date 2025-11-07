<?php
require_once __DIR__ . '/auth.php';
$pdo = pdo(); $uid = $warga['id']; $err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $title=trim($_POST['title']??'');
  if($title==='') $err='Judul topik wajib diisi.';
  else{ $pdo->prepare("INSERT INTO forum_topics(user_id,title)VALUES(?,?)")->execute([$uid,$title]); header('Location: '.url('/user/forum.php')); exit; }
}
$topics=$pdo->query("SELECT t.id,t.title,t.created_at,u.name FROM forum_topics t JOIN users u ON u.id=t.user_id ORDER BY t.created_at DESC")->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" /><title>Forum Diskusi</title><meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>:root{--padx:clamp(16px,5vw,48px)}.edge{padding-inline:var(--padx)}body{background:#f5f7fb}</style>
</head>
<body>
<nav class="navbar bg-white border-bottom sticky-top">
  <div class="container-fluid edge py-2">
    <a class="navbar-brand fw-semibold" href="<?= url('/user/dashboard.php') ?>"><i class="bi bi-chat-dots me-2 text-success"></i>Forum Diskusi</a>
    <div class="d-flex align-items-center gap-3"><span class="text-secondary small"><?=htmlspecialchars($warga['name'])?></span><a class="btn btn-sm btn-outline-danger" href="<?= url('/user/logout.php')?>">Logout</a></div>
  </div>
</nav>

<div class="container-fluid edge my-4">
  <div class="row g-4">
    <div class="col-12 col-lg-5">
      <div class="card shadow-sm"><div class="card-body">
        <h5 class="card-title mb-3">Buat Topik Baru</h5>
        <?php if($err):?><div class="alert alert-danger"><?=$err?></div><?php endif;?>
        <form method="post" class="d-flex gap-2 flex-wrap">
          <input class="form-control flex-grow-1" name="title" placeholder="Judul topik..." value="<?=htmlspecialchars($_POST['title']??'')?>">
          <button class="btn btn-success"><i class="bi bi-send me-1"></i>Posting</button>
        </form>
      </div></div>
    </div>

    <div class="col-12 col-lg-7">
      <div class="card shadow-sm"><div class="card-body">
        <h5 class="card-title mb-3">Daftar Topik</h5>
        <?php if(!$topics):?><div class="alert alert-info m-0">Belum ada topik.</div>
        <?php else: foreach($topics as $t): ?>
          <div class="border rounded-3 p-3 mb-2 bg-white">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <a class="fw-semibold text-decoration-none" href="<?= url('/user/forum_topic.php?id='.$t['id']) ?>"><?=htmlspecialchars($t['title'])?></a>
                <div class="text-secondary small">oleh <?=htmlspecialchars($t['name'])?> • <?=htmlspecialchars($t['created_at'])?></div>
              </div>
              <a class="btn btn-sm btn-outline-success" href="<?= url('/user/forum_topic.php?id='.$t['id']) ?>">Buka</a>
            </div>
          </div>
        <?php endforeach; endif;?>
      </div></div>
    </div>
  </div>
  <div class="mt-4"><a class="btn btn-outline-secondary" href="<?= url('/user/dashboard.php')?>">← Kembali</a></div>
</div>
</body></html>
