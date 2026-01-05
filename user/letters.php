<?php
require_once __DIR__ . '/auth.php';
$pdo = pdo(); 
$uid = (int)$warga['id'];

function ensure_letter_attachments_table(PDO $pdo) {
  $pdo->exec(
    "CREATE TABLE IF NOT EXISTS letter_attachments (
      id INT NOT NULL AUTO_INCREMENT,
      letter_id INT NOT NULL,
      label VARCHAR(20) NOT NULL,
      file_path VARCHAR(255) NOT NULL,
      created_at DATETIME NOT NULL,
      PRIMARY KEY (id),
      INDEX (letter_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
  );
}

function ensure_letter_notifications_table(PDO $pdo) {
  $pdo->exec(
    "CREATE TABLE IF NOT EXISTS letter_notifications (
      id INT NOT NULL AUTO_INCREMENT,
      letter_id INT NOT NULL,
      message TEXT NOT NULL,
      created_at DATETIME NOT NULL,
      created_by VARCHAR(100) NOT NULL,
      PRIMARY KEY (id),
      INDEX (letter_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
  );
}

ensure_letter_attachments_table($pdo);
ensure_letter_notifications_table($pdo);

$err = $ok = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $type    = $_POST['type']    ?? 'surat_pengantar';
  $subject = trim($_POST['subject'] ?? '');
  $details = trim($_POST['details'] ?? '');
  $file_path = null;
  $attachments = [];

  $allowed = ['pdf','jpg','jpeg','png'];
  $upload_one = function($file, $label) use ($uid, $allowed, &$err) {
    if ($err) return null;
    if (empty($file['name'])) return null;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
      $err = 'Lampiran harus bertipe PDF/JPG/PNG.';
      return null;
    }
    if ($file['error'] === UPLOAD_ERR_OK) {
      $name = 'L_'.$uid.'_'.time().'_'.$label.'.'.$ext;
      $dest = __DIR__.'/../uploads/'.$name;
      if (move_uploaded_file($file['tmp_name'], $dest)) {
        return 'uploads/'.$name;
      }
      $err = 'Gagal menyimpan file.';
      return null;
    }
    $err = 'Upload gagal.';
    return null;
  };

  if ($type === 'surat_domisili') {
    $kk  = $upload_one($_FILES['kk_file'] ?? [], 'kk');
    $ktp = $upload_one($_FILES['ktp_file'] ?? [], 'ktp');
    if (!$err && (!$kk || !$ktp)) {
      $err = 'KTP dan KK wajib diunggah untuk surat domisili.';
    }
    if ($kk)  $attachments[] = ['label' => 'kk', 'path' => $kk];
    if ($ktp) $attachments[] = ['label' => 'ktp', 'path' => $ktp];
  } else {
    $lampiran = $upload_one($_FILES['attachment'] ?? [], 'lampiran');
    if ($lampiran) {
      $file_path = $lampiran;
      $attachments[] = ['label' => 'lampiran', 'path' => $lampiran];
    }
  }

  if (!$err && $subject === '') $err = 'Perihal wajib diisi.';

  if (!$err) {
    $pdo->prepare("INSERT INTO letters(user_id,type,subject,details,file_path) VALUES (?,?,?,?,?)")
        ->execute([$uid,$type,$subject,$details,$file_path]);
    $letter_id = (int)$pdo->lastInsertId();
    if ($letter_id && $attachments) {
      $st = $pdo->prepare(
        "INSERT INTO letter_attachments (letter_id,label,file_path,created_at)
         VALUES (?,?,?,NOW())"
      );
      foreach ($attachments as $a) {
        $st->execute([$letter_id, $a['label'], $a['path']]);
      }
    }
    $ok = 'Pengajuan surat terkirim.';
    $_POST = [];
  }
}

$list = $pdo->prepare("SELECT id,type,subject,status,created_at,file_path FROM letters WHERE user_id=? ORDER BY created_at DESC");
$list->execute([$uid]);
$list = $list->fetchAll();

$attachments_by_letter = [];
if ($list) {
  $ids = array_map(fn($r) => (int)$r['id'], $list);
  $in  = implode(',', array_fill(0, count($ids), '?'));
  $st = $pdo->prepare("SELECT letter_id,label,file_path FROM letter_attachments WHERE letter_id IN ($in)");
  $st->execute($ids);
  foreach ($st->fetchAll() as $a) {
    $attachments_by_letter[(int)$a['letter_id']][] = $a;
  }
}

$notif_by_letter = [];
if ($list) {
  $ids = array_map(fn($r) => (int)$r['id'], $list);
  $in  = implode(',', array_fill(0, count($ids), '?'));
  $st = $pdo->prepare(
    "SELECT ln.letter_id, ln.message
     FROM letter_notifications ln
     WHERE ln.letter_id IN ($in)
     ORDER BY ln.created_at DESC"
  );
  $st->execute($ids);
  foreach ($st->fetchAll() as $n) {
    if (!isset($notif_by_letter[(int)$n['letter_id']])) {
      $notif_by_letter[(int)$n['letter_id']] = $n['message'];
    }
  }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Surat Online — Portal RT 005</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --padx: clamp(16px, 5vw, 48px);
      --ink:#0f172a; --brand:#2563eb; --brand2:#60a5fa;
      --card:#ffffff; --border:#e6eaf3;
    }
    body{ background:#f5f7fb; color:var(--ink) }
    .edge{ padding-inline: var(--padx) }

    /* Navbar (selaras halaman lain) */
    .navbar{
      background:#fff; border-bottom:1px solid #e9eef7;
      position:sticky; top:0; z-index:1000;
      background-image: linear-gradient(180deg, rgba(96,165,250,.06), transparent);
    }
    .brand-pill{
      display:inline-flex; align-items:center; gap:.5rem;
      padding:.35rem .7rem; border-radius:999px; font-weight:700;
      background:linear-gradient(135deg,rgba(37,99,235,.12),rgba(96,165,250,.10));
      border:1px solid rgba(96,165,250,.30); color:#1e293b;
    }
    .profile-btn{
      background:#fff; border:1px solid var(--border);
      border-radius:999px; padding:.25rem .5rem .25rem .25rem;
    }
    .profile-btn:hover{ background:#f8fbff; border-color:#d5def0 }
    .avatar{
      width:34px;height:34px;border-radius:999px;display:inline-grid;place-items:center;
      font-weight:700; color:#fff;
      background:linear-gradient(135deg,var(--brand),var(--brand2));
      box-shadow:0 6px 16px rgba(37,99,235,.25);
    }

    /* Panels */
    .panel{
      border:1px solid rgba(16,24,40,.06); background:var(--card);
      border-radius:18px; box-shadow:0 20px 48px rgba(2,6,23,.08);
      overflow:hidden;
    }
    .panel-head{
      padding:18px; border-bottom:1px solid var(--border);
      background-image:linear-gradient(135deg, rgba(37,99,235,.10), rgba(96,165,250,.06));
    }
    .panel-body{ padding:18px }

    .input-ico{ position:relative }
    .input-ico .bi{ position:absolute; left:12px; top:50%; transform:translateY(-50%); opacity:.75 }
    .input-ico input, .input-ico textarea, .input-ico select{ padding-left:40px !important }
    .form-control, .form-select{ border:1px solid var(--border); border-radius:12px }
    .form-control:focus, .form-select:focus{ border-color:#d6e2ff; box-shadow:0 0 0 .2rem rgba(37,99,235,.15) }

    .btn-brand{
      background:linear-gradient(135deg,var(--brand),var(--brand2));
      border:0; font-weight:700;
      box-shadow:0 10px 26px rgba(2,6,23,.18);
    }
    .btn-brand:hover{ filter:brightness(.96) }

    /* Table */
    .table > :not(caption) > * > *{ padding:12px 14px }
    .table thead th{ font-weight:700; color:#334155; border-bottom:1px solid #e8eef8 }
    .table tbody td{ border-top:1px solid #eef2f7 }

    /* Toast */
    .toast-container{ position:fixed; top:20px; right:20px; z-index:1080 }
  </style>
</head>
<body>

<!-- NAV -->
<nav class="navbar">
  <div class="container-fluid edge py-2">
    <a class="navbar-brand fw-semibold d-flex align-items-center gap-2" href="<?= url('/user/dashboard.php') ?>">
      <span class="brand-pill"><i class="bi bi-file-earmark-text"></i> Surat Online</span>
    </a>

    <div class="d-flex align-items-center gap-2">
      <!-- Dropdown profil (Profil & Dashboard) -->
      <div class="dropdown">
        <button class="btn profile-btn d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
          <?php if(!empty($warga['avatar_url'])): ?>
            <img src="<?= htmlspecialchars($warga['avatar_url']) ?>" alt="Avatar"
                 style="width:34px;height:34px;border-radius:999px;object-fit:cover;box-shadow:0 6px 16px rgba(37,99,235,.25)">
          <?php else: ?>
            <span class="avatar"><?= htmlspecialchars(user_initials($warga['name'] ?? 'Kiki')) ?></span>
          <?php endif; ?>
          <span class="d-none d-sm-inline small"><b><?= htmlspecialchars($warga['name'] ?? 'Kiki') ?></b></span>
          <i class="bi bi-chevron-down small"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow">
          <li class="dropdown-header">
            <div class="fw-semibold"><?= htmlspecialchars($warga['name'] ?? '') ?></div>
            <div class="text-secondary small">RT 005 • Muara Telang</div>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="<?= url('/user/profile.php') ?>"><i class="bi bi-person me-2"></i> Profil</a></li>
          <li><a class="dropdown-item" href="<?= url('/user/dashboard.php') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
          <li><a class="dropdown-item" href="<?= url('/user/logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<!-- CONTENT -->
<div class="container-fluid edge my-4">
  <div class="row g-4">

    <!-- Panel Ajukan Surat -->
    <div class="col-12 col-lg-5">
      <div class="panel">
        <div class="panel-head d-flex align-items-center gap-2">
          <span class="badge text-bg-warning rounded-pill text-dark"><i class="bi bi-pencil-square"></i></span>
          <div class="h6 m-0 fw-bold">Ajukan Surat</div>
        </div>
        <div class="panel-body">
          <!-- Inline alert fallback (desktop) -->
          <?php if($err):?><div class="alert alert-danger d-none d-md-block"><i class="bi bi-x-octagon me-2"></i><?= htmlspecialchars($err) ?></div><?php endif;?>
          <?php if($ok):?><div class="alert alert-success d-none d-md-block"><i class="bi bi-check2-circle me-2"></i><?= htmlspecialchars($ok) ?></div><?php endif;?>

          <form method="post" enctype="multipart/form-data" novalidate>
            <div class="mb-3">
              <label class="form-label">Jenis Surat</label>
              <div class="input-ico">
                <i class="bi bi-ui-checks-grid"></i>
                <select class="form-select" name="type" id="letterType">
                  <option value="surat_pengantar" <?= (($_POST['type']??'')==='surat_pengantar'?'selected':'') ?>>Surat Pengantar</option>
                  <option value="surat_domisili"  <?= (($_POST['type']??'')==='surat_domisili' ?'selected':'') ?>>Surat Keterangan Domisili</option>
                  <option value="surat_usaha"     <?= (($_POST['type']??'')==='surat_usaha'    ?'selected':'') ?>>Surat Keterangan Usaha</option>
                  <option value="lainnya"         <?= (($_POST['type']??'')==='lainnya'        ?'selected':'') ?>>Lainnya</option>
                </select>
              </div>
            </div>

            <div class="mb-3" id="reqBox">
              <label class="form-label">Syarat</label>
              <div class="p-3 rounded border bg-light-subtle">
                <ul class="m-0 ps-3" id="reqList"></ul>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Perihal *</label>
              <div class="input-ico">
                <i class="bi bi-card-text"></i>
                <input class="form-control" name="subject" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" placeholder="Contoh: Pengantar pembuatan KTP" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Detail / Keterangan</label>
              <div class="input-ico">
                <i class="bi bi-chat-left-text"></i>
                <textarea class="form-control" name="details" rows="5" placeholder="Tambahkan keterangan pendukung (opsional)"><?= htmlspecialchars($_POST['details'] ?? '') ?></textarea>
              </div>
            </div>

            <div class="mb-3" id="attSingle">
              <label class="form-label">Lampiran (PDF/JPG/PNG)</label>
              <div class="input-ico">
                <i class="bi bi-paperclip"></i>
                <input class="form-control" type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" id="att">
              </div>
              <div id="attName" class="form-text"></div>
            </div>

            <div class="mb-3 d-none" id="attDomisili">
              <label class="form-label">Lampiran KTP (PDF/JPG/PNG)</label>
              <div class="input-ico mb-2">
                <i class="bi bi-person-vcard"></i>
                <input class="form-control" type="file" name="ktp_file" accept=".pdf,.jpg,.jpeg,.png" id="ktpFile">
              </div>
              <label class="form-label">Lampiran KK (PDF/JPG/PNG)</label>
              <div class="input-ico">
                <i class="bi bi-people"></i>
                <input class="form-control" type="file" name="kk_file" accept=".pdf,.jpg,.jpeg,.png" id="kkFile">
              </div>
            </div>

            <div class="d-flex gap-2">
              <button class="btn btn-brand"><i class="bi bi-send me-1"></i> Kirim</button>
              <a class="btn btn-outline-secondary" href="<?= url('/user/dashboard.php')?>">Batal</a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Panel Riwayat -->
    <div class="col-12 col-lg-7">
      <div class="panel">
        <div class="panel-head d-flex align-items-center gap-2">
          <span class="badge text-bg-light rounded-pill"><i class="bi bi-clock-history"></i></span>
          <div class="h6 m-0 fw-bold">Riwayat Pengajuan</div>
        </div>
        <div class="panel-body">
          <?php if(!$list):?>
            <div class="alert alert-info m-0"><i class="bi bi-info-circle me-2"></i>Belum ada pengajuan.</div>
          <?php else:?>
            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr>
                    <th>Waktu</th>
                    <th>Jenis</th>
                    <th>Perihal</th>
                    <th>Status</th>
                    <th>Pesan RT</th>
                    <th>Lampiran</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach($list as $r): 
                  $s = $r['status'] ?: 'submitted';
                  $cls = ($s==='approved' ? 'success' : ($s==='review' ? 'warning' : ($s==='rejected' ? 'danger' : 'secondary')));
                  $atts = $attachments_by_letter[(int)$r['id']] ?? [];
                  $notif = $notif_by_letter[(int)$r['id']] ?? '';
                ?>
                  <tr>
                    <td><?= htmlspecialchars($r['created_at']) ?></td>
                    <td><?= htmlspecialchars($r['type']) ?></td>
                    <td class="text-truncate" style="max-width:320px"><?= htmlspecialchars($r['subject']) ?></td>
                    <td><span class="badge text-bg-<?= $cls ?>"><?= htmlspecialchars($s) ?></span></td>
                    <td class="text-truncate" style="max-width:260px"><?= htmlspecialchars($notif ?: '-') ?></td>
                    <td>
                      <?php if($atts): ?>
                        <?php foreach($atts as $a): ?>
                          <a class="btn btn-sm btn-outline-primary me-1 mb-1" target="_blank" href="<?= url('/'.$a['file_path']) ?>">
                            <i class="bi bi-paperclip me-1"></i><?= htmlspecialchars(strtoupper($a['label'])) ?>
                          </a>
                        <?php endforeach; ?>
                      <?php elseif(!empty($r['file_path'])): ?>
                        <a class="btn btn-sm btn-outline-primary" target="_blank" href="<?= url('/'.$r['file_path']) ?>">
                          <i class="bi bi-paperclip me-1"></i>Lihat
                        </a>
                      <?php else: ?>-<?php endif; ?>
                    </td>
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

  <div class="mt-4">
    <a class="btn btn-outline-secondary" href="<?= url('/user/dashboard.php')?>">← Kembali</a>
  </div>
</div>

<!-- Toast (modern) -->
<div class="toast-container">
  <?php if ($err): ?>
    <div id="toastErr" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body"><i class="bi bi-x-octagon me-2"></i><?= htmlspecialchars($err) ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  <?php endif; ?>
  <?php if ($ok): ?>
    <div id="toastOk" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body"><i class="bi bi-check2-circle me-2"></i><?= htmlspecialchars($ok) ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function(){
    // Tampilkan toast bila ada
    var tErr = document.getElementById('toastErr');
    if (tErr) new bootstrap.Toast(tErr, { delay: 4200 }).show();
    var tOk = document.getElementById('toastOk');
    if (tOk) new bootstrap.Toast(tOk, { delay: 2500 }).show();

    // Tampilkan nama file lampiran
    var att = document.getElementById('att');
    var nameBox = document.getElementById('attName');
    if (att && nameBox) {
      att.addEventListener('change', function(){
        nameBox.textContent = att.files?.[0]?.name ? ('File: ' + att.files[0].name) : '';
      });
    }

    var typeSel = document.getElementById('letterType');
    var reqList = document.getElementById('reqList');
    var attSingle = document.getElementById('attSingle');
    var attDomisili = document.getElementById('attDomisili');
    var ktpFile = document.getElementById('ktpFile');
    var kkFile = document.getElementById('kkFile');
    var reqs = {
      surat_pengantar: ['KTP (jika diminta)', 'Tujuan pembuatan surat'],
      surat_domisili: ['KTP', 'KK', 'Alamat lengkap domisili'],
      surat_usaha: ['KTP', 'Foto usaha (opsional)', 'Alamat usaha'],
      lainnya: ['Jelaskan kebutuhan surat pada kolom perihal']
    };
    function renderReq() {
      var val = typeSel ? typeSel.value : 'surat_pengantar';
      var items = reqs[val] || [];
      if (reqList) {
        reqList.innerHTML = items.map(function(t){ return '<li>'+t+'</li>'; }).join('');
      }
      var isDom = val === 'surat_domisili';
      if (attSingle) attSingle.classList.toggle('d-none', isDom);
      if (attDomisili) attDomisili.classList.toggle('d-none', !isDom);
      if (ktpFile) ktpFile.required = isDom;
      if (kkFile) kkFile.required = isDom;
    }
    if (typeSel) {
      typeSel.addEventListener('change', renderReq);
      renderReq();
    }
  });
</script>
</body>
</html>
