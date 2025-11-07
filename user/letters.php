<?php
require_once __DIR__ . '/auth.php';
$pdo = pdo(); $uid = $warga['id'];
$err=$ok='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $type=$_POST['type']??'surat_pengantar';
  $subject=trim($_POST['subject']??''); $details=trim($_POST['details']??'');
  $file_path=null;

  if(!empty($_FILES['attachment']['name'])){
    $allowed=['pdf','jpg','jpeg','png'];
    $ext=strtolower(pathinfo($_FILES['attachment']['name'],PATHINFO_EXTENSION));
    if(!in_array($ext,$allowed)) $err='Lampiran harus pdf/jpg/png.';
    elseif($_FILES['attachment']['error']===UPLOAD_ERR_OK){
      $name='L_'.$uid.'_'.time().'.'.$ext;
      $dest=__DIR__.'/../uploads/'.$name;
      if(move_uploaded_file($_FILES['attachment']['tmp_name'],$dest)) $file_path='uploads/'.$name;
      else $err='Gagal menyimpan file.';
    } else $err='Upload gagal.';
  }
  if(!$err && $subject==='') $err='Perihal wajib diisi.';
  if(!$err){
    $pdo->prepare("INSERT INTO letters(user_id,type,subject,details,file_path) VALUES (?,?,?,?,?)")
        ->execute([$uid,$type,$subject,$details,$file_path]);
    $ok='Pengajuan surat terkirim.'; $_POST=[];
  }
}
$list=$pdo->prepare("SELECT id,type,subject,status,created_at,file_path FROM letters WHERE user_id=? ORDER BY created_at DESC");
$list->execute([$uid]); $list=$list->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" /><title>Surat Online</title><meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>:root{--padx:clamp(16px,5vw,48px)}.edge{padding-inline:var(--padx)}body{background:#f5f7fb}</style>
</head>
<body>
<nav class="navbar bg-white border-bottom sticky-top">
  <div class="container-fluid edge py-2">
    <a class="navbar-brand fw-semibold" href="<?= url('/user/dashboard.php') ?>"><i class="bi bi-file-earmark-text me-2 text-warning"></i>Surat Online</a>
    <div class="d-flex align-items-center gap-3"><span class="text-secondary small"><?=htmlspecialchars($warga['name'])?></span><a class="btn btn-sm btn-outline-danger" href="<?= url('/user/logout.php')?>">Logout</a></div>
  </div>
</nav>

<div class="container-fluid edge my-4">
  <div class="row g-4">
    <div class="col-12 col-lg-5">
      <div class="card shadow-sm"><div class="card-body">
        <h5 class="card-title mb-3">Ajukan Surat</h5>
        <?php if($err):?><div class="alert alert-danger"><?=$err?></div><?php endif;?>
        <?php if($ok):?><div class="alert alert-success"><?=$ok?></div><?php endif;?>
        <form method="post" enctype="multipart/form-data">
          <div class="mb-3"><label class="form-label">Jenis Surat</label>
            <select class="form-select" name="type">
              <option value="surat_pengantar">Surat Pengantar</option>
              <option value="surat_domisili">Surat Keterangan Domisili</option>
              <option value="surat_usaha">Surat Keterangan Usaha</option>
              <option value="lainnya">Lainnya</option>
            </select>
          </div>
          <div class="mb-3"><label class="form-label">Perihal</label>
            <input class="form-control" name="subject" value="<?=htmlspecialchars($_POST['subject']??'')?>" placeholder="Contoh: Pengantar pembuatan KTP">
          </div>
          <div class="mb-3"><label class="form-label">Detail / Keterangan</label>
            <textarea class="form-control" name="details" rows="5"><?=htmlspecialchars($_POST['details']??'')?></textarea>
          </div>
          <div class="mb-3"><label class="form-label">Lampiran (opsional: pdf/jpg/png)</label>
            <input class="form-control" type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png">
          </div>
          <button class="btn btn-warning text-dark"><i class="bi bi-send me-1"></i>Kirim</button>
          <a class="btn btn-outline-secondary ms-1" href="<?= url('/user/dashboard.php')?>">Batal</a>
        </form>
      </div></div>
    </div>

    <div class="col-12 col-lg-7">
      <div class="card shadow-sm"><div class="card-body">
        <h5 class="card-title mb-3">Riwayat Pengajuan</h5>
        <?php if(!$list):?><div class="alert alert-info m-0">Belum ada pengajuan.</div>
        <?php else:?>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead><tr><th>Waktu</th><th>Jenis</th><th>Perihal</th><th>Status</th><th>Lampiran</th></tr></thead>
              <tbody>
              <?php foreach($list as $r): $s=$r['status']; $cls=$s==='approved'?'success':($s==='review'?'warning':($s==='rejected'?'danger':'secondary')); ?>
                <tr>
                  <td><?=htmlspecialchars($r['created_at'])?></td>
                  <td><?=htmlspecialchars($r['type'])?></td>
                  <td><?=htmlspecialchars($r['subject'])?></td>
                  <td><span class="badge text-bg-<?=$cls?>"><?=htmlspecialchars($s)?></span></td>
                  <td>
                    <?php if($r['file_path']): ?>
                      <a class="btn btn-sm btn-outline-primary" target="_blank" href="<?= url('/'.$r['file_path']) ?>"><i class="bi bi-paperclip me-1"></i>Lihat</a>
                    <?php else: ?>-<?php endif; ?>
                  </td>
                </tr>
              <?php endforeach;?>
              </tbody>
            </table>
          </div>
        <?php endif;?>
      </div></div>
    </div>
  </div>
  <div class="mt-4"><a class="btn btn-outline-secondary" href="<?= url('/user/dashboard.php')?>">← Kembali</a></div>
</div>
</body></html>
