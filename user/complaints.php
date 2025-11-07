<?php
require_once __DIR__ . '/auth.php';
$pdo = pdo(); $uid = $warga['id'];
$err=$ok='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $title=trim($_POST['title']??''); $content=trim($_POST['content']??'');
  if($title===''||$content==='') $err='Judul dan isi pengaduan wajib diisi.';
  else{ $pdo->prepare("INSERT INTO complaints(user_id,title,content) VALUES (?,?,?)")->execute([$uid,$title,$content]); $ok='Pengaduan terkirim.'; $_POST=[]; }
}
$list=$pdo->prepare("SELECT id,title,status,created_at FROM complaints WHERE user_id=? ORDER BY created_at DESC");
$list->execute([$uid]); $list=$list->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" /><title>Pengaduan</title><meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>:root{--padx:clamp(16px,5vw,48px)}.edge{padding-inline:var(--padx)}body{background:#f5f7fb}</style>
</head>
<body>
<nav class="navbar bg-white border-bottom sticky-top">
  <div class="container-fluid edge py-2">
    <a class="navbar-brand fw-semibold" href="<?= url('/user/dashboard.php') ?>"><i class="bi bi-exclamation-octagon me-2 text-danger"></i>Pengaduan</a>
    <div class="d-flex align-items-center gap-3"><span class="text-secondary small"><?=htmlspecialchars($warga['name'])?></span><a class="btn btn-sm btn-outline-danger" href="<?= url('/user/logout.php')?>">Logout</a></div>
  </div>
</nav>

<div class="container-fluid edge my-4">
  <div class="row g-4">
    <div class="col-12 col-lg-5">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title mb-3">Buat Pengaduan</h5>
          <?php if($err):?><div class="alert alert-danger"><?=$err?></div><?php endif;?>
          <?php if($ok):?><div class="alert alert-success"><?=$ok?></div><?php endif;?>
          <form method="post">
            <div class="mb-3"><label class="form-label">Judul</label>
              <input class="form-control" name="title" value="<?=htmlspecialchars($_POST['title']??'')?>"></div>
            <div class="mb-3"><label class="form-label">Isi Pengaduan</label>
              <textarea class="form-control" name="content" rows="5"><?=htmlspecialchars($_POST['content']??'')?></textarea></div>
            <button class="btn btn-danger"><i class="bi bi-send me-1"></i>Kirim</button>
            <a class="btn btn-outline-secondary ms-1" href="<?= url('/user/dashboard.php')?>">Batal</a>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-7">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title mb-3">Riwayat Pengaduan</h5>
          <?php if(!$list):?><div class="alert alert-info m-0">Belum ada pengaduan.</div>
          <?php else:?>
            <div class="table-responsive">
              <table class="table align-middle">
                <thead><tr><th>Waktu</th><th>Judul</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach($list as $r): $s=$r['status']; $cls=$s==='resolved'?'success':($s==='in_progress'?'warning':'secondary'); ?>
                  <tr>
                    <td><?=htmlspecialchars($r['created_at'])?></td>
                    <td><?=htmlspecialchars($r['title'])?></td>
                    <td><span class="badge text-bg-<?=$cls?>"><?=htmlspecialchars($s)?></span></td>
                  </tr>
                <?php endforeach;?>
                </tbody>
              </table>
            </div>
          <?php endif;?>
        </div>
      </div>
    </div>
  </div>
  <div class="mt-4"><a class="btn btn-outline-secondary" href="<?= url('/user/dashboard.php')?>">← Kembali</a></div>
</div>
</body></html>
