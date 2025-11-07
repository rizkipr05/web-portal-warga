<?php require_once __DIR__ . '/auth.php'; ?>
<?php
$pdo = pdo();
$info = $err = '';

// Ambil data user terbaru
$st = $pdo->prepare("SELECT id,name,email,rt,rw,address,avatar_path FROM users WHERE id=? LIMIT 1");
$st->execute([ $_SESSION['user_id'] ]);
$me = $st->fetch() ?: [];

// Utility bikin URL avatar + cache buster
function avatar_url_or_null(?string $relPath): ?string {
  if (empty($relPath)) return null;
  $rel = '/' . ltrim($relPath, '/');
  $disk = realpath(__DIR__ . '/..' . $rel);
  $v = ($disk && file_exists($disk)) ? filemtime($disk) : time();
  return url($rel) . '?v=' . $v;
}
$me['avatar_url'] = avatar_url_or_null($me['avatar_path'] ?? null);

// Handle form submit (PRG pattern)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    // Data dasar
    $name = trim($_POST['name'] ?? ($me['name'] ?? ''));
    $email = trim($_POST['email'] ?? ($me['email'] ?? ''));
    $rw = (int)($_POST['rw'] ?? ($me['rw'] ?? 0));
    $addr = trim($_POST['address'] ?? ($me['address'] ?? ''));

    if ($name === '' || $email === '') {
      throw new Exception('Nama dan email wajib diisi.');
    }

    // Upload avatar (opsional)
    $newAvatarPath = null;
    if (!empty($_FILES['avatar']['name'])) {
      if (!is_dir(__DIR__ . '/../uploads/avatars')) {
        if (!@mkdir(__DIR__ . '/../uploads/avatars', 0775, true) && !is_dir(__DIR__ . '/../uploads/avatars')) {
          throw new Exception('Gagal membuat folder upload.');
        }
      }

      if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Gagal mengunggah file (err code '.$_FILES['avatar']['error'].')');
      }

      // Batas ukuran 2MB
      if ((int)$_FILES['avatar']['size'] > 2 * 1024 * 1024) {
        throw new Exception('Ukuran foto maksimal 2MB.');
      }

      // Validasi MIME (fallback ke ekstensi jika fileinfo tidak tersedia)
      $ext = null; $mime = null;
      if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['avatar']['tmp_name']);
      }
      if ($mime === 'image/jpeg') $ext = 'jpg';
      elseif ($mime === 'image/png') $ext = 'png';
      else {
        // fallback cek ekstensi aman
        $extGuess = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        if (in_array($extGuess, ['jpg','jpeg'])) $ext = 'jpg';
        elseif ($extGuess === 'png') $ext = 'png';
        else throw new Exception('Format foto harus JPG atau PNG.');
      }

      $fname = 'u_' . (int)$me['id'] . '_' . time() . '.' . $ext;
      $diskPath = __DIR__ . '/../uploads/avatars/' . $fname;
      $relPath  = 'uploads/avatars/' . $fname;

      if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $diskPath)) {
        throw new Exception('Tidak bisa menyimpan file ke server.');
      }

      // Hapus avatar lama bila ada
      if (!empty($me['avatar_path'])) {
        $old = __DIR__ . '/../' . ltrim($me['avatar_path'], '/');
        if (is_file($old)) @unlink($old);
      }

      $newAvatarPath = $relPath;
    }

    // Simpan ke DB
    if ($newAvatarPath) {
      $upd = $pdo->prepare("UPDATE users SET name=?, email=?, rw=?, address=?, avatar_path=? WHERE id=?");
      $upd->execute([$name,$email,$rw,$addr,$newAvatarPath,$me['id']]);
    } else {
      $upd = $pdo->prepare("UPDATE users SET name=?, email=?, rw=?, address=? WHERE id=?");
      $upd->execute([$name,$email,$rw,$addr,$me['id']]);
    }

    // Refresh session agar navbar ikut berubah
    $_SESSION['user_name'] = $name;

    // Redirect (PRG) untuk mencegah resubmit & munculkan toast
    header('Location: ' . url('/user/profile.php?updated=1'));
    exit;
  } catch (Throwable $e) {
    $err = $e->getMessage();
    // Jangan redirect kalau error: biar pesan tampil langsung
  }
}

// Info toast dari query
if (isset($_GET['updated']) && $_GET['updated'] == '1') {
  $info = 'Profil berhasil diperbarui.';
}

// helper initials + firstname
function initials($name) {
  $name = trim($name ?: 'Kiki');
  $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);
  if (count($parts) >= 2) return mb_strtoupper(mb_substr($parts[0],0,1) . mb_substr(end($parts),0,1));
  return mb_strtoupper(mb_substr($name,0,1));
}
$firstName = explode(' ', trim($me['name'] ?? 'Kiki'))[0] ?? 'Kiki';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Profil — Portal RT 005</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{
      --brand:#2563eb; --brand2:#60a5fa; --ink:#0f172a; --border:#e5e7eb;
      --padx: clamp(16px, 5vw, 48px);
    }
    body{ background:#f5f7fb; color:var(--ink) }
    .edge{ padding-left:var(--padx); padding-right:var(--padx) }

    .navbar{
      background:#fff;
      border-bottom:1px solid #e9eef7;
      position:sticky; top:0; z-index:1000;
      background-image: linear-gradient(180deg, rgba(96,165,250,.06), transparent);
    }
    .brand-pill{
      display:inline-flex; align-items:center; gap:.5rem;
      padding:.35rem .7rem; border-radius:999px;
      font-weight:700; letter-spacing:.2px;
      background:linear-gradient(135deg,rgba(37,99,235,.12),rgba(96,165,250,.10));
      border:1px solid rgba(96,165,250,.30);
      color:#1e293b;
    }
    .profile-btn{
      background:#fff; border:1px solid #e6eaf3; border-radius:999px;
      padding:.25rem .5rem .25rem .25rem;
    }
    .profile-btn:hover{ border-color:#d5def0; background:#f8fbff }
    .avatar, .avatar-lg{
      border-radius:999px; object-fit:cover;
      box-shadow: 0 6px 16px rgba(37,99,235,.25);
    }
    .avatar{ width:34px;height:34px }
    .avatar-lg{ width:96px;height:96px }

    .panel{
      border:1px solid rgba(16,24,40,.06);
      background:#fff; border-radius:20px;
      box-shadow:0 24px 60px rgba(2,6,23,.08);
      overflow:hidden;
    }
    .panel-head{
      padding:18px 18px;
      background-image: linear-gradient(135deg, rgba(37,99,235,.10), rgba(96,165,250,.06));
      border-bottom:1px solid var(--border);
    }
    .badge-pill{
      display:inline-flex; align-items:center; gap:8px;
      padding:8px 12px; border-radius:999px;
      background:linear-gradient(135deg,var(--brand),var(--brand2)); color:#fff; font-weight:700;
    }
    .panel-body{ padding:22px 18px }

    .form-control{ border-radius:12px; border:1px solid var(--border); padding:10px 12px; }
    .form-control:focus{ border-color:#d6e2ff; box-shadow:0 0 0 .2rem rgba(37,99,235,.15) }
    .btn-primary{ background:linear-gradient(135deg,var(--brand),var(--brand2)); border:0; font-weight:700 }
    .btn-primary:hover{ filter:brightness(.95) }

    .toast-container{ position:fixed; top:20px; right:20px; z-index:1080 }
    .avatar-fallback{
      width:96px;height:96px;border-radius:999px;display:grid;place-items:center;
      background:linear-gradient(135deg,var(--brand),var(--brand2)); color:#fff;font-weight:800;font-size:32px;
      box-shadow: 0 12px 28px rgba(37,99,235,.25);
    }
  </style>
</head>
<body>
  <!-- NAV -->
  <nav class="navbar">
    <div class="container-fluid edge py-2">
      <a class="navbar-brand fw-semibold d-flex align-items-center gap-2" href="<?= url('/user/dashboard.php') ?>">
        <span class="brand-pill"><i class="bi bi-people-fill"></i> Portal Warga RT 005</span>
      </a>

      <div class="d-flex align-items-center gap-2">
        <div class="dropdown">
          <button class="btn profile-btn d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
            <?php if(!empty($me['avatar_url'])): ?>
              <img src="<?= htmlspecialchars($me['avatar_url']) ?>" class="avatar" alt="Avatar">
            <?php else: ?>
              <span class="avatar-fallback" style="width:34px;height:34px;font-size:12px"><?= htmlspecialchars(initials($me['name'] ?? 'Kiki')) ?></span>
            <?php endif; ?>
            <span class="d-none d-sm-inline small">Hai, <b><?= htmlspecialchars($firstName) ?></b></span>
            <i class="bi bi-chevron-down small"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow">
            <li class="dropdown-header">
              <div class="fw-semibold"><?= htmlspecialchars($me['name'] ?? 'Kiki') ?></div>
              <div class="text-secondary small">RT <?= (int)($me['rt'] ?? 5) ?> • Muara Telang</div>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= url('/user/profile.php') ?>"><i class="bi bi-person me-2"></i> Profil</a></li>
            <li><a class="dropdown-item" href="<?= url('/user/letters.php') ?>"><i class="bi bi-file-earmark-text me-2"></i> Surat Saya</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= url('/user/logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i> Keluar</a></li>
          </ul>
        </div>
      </div>
    </div>
  </nav>

  <!-- BODY -->
  <div class="container edge my-4">
    <div class="panel">
      <div class="panel-head d-flex align-items-center justify-content-between">
        <div class="badge-pill"><i class="bi bi-person-gear"></i> Profil</div>
        <small class="text-secondary">Kelola informasi akun & foto</small>
      </div>
      <div class="panel-body">
        <!-- Form -->
        <form method="post" enctype="multipart/form-data" class="row g-3">
          <div class="col-12">
            <label class="form-label d-block">Foto Profil</label>
            <div class="d-flex align-items-center gap-3">
              <?php if(!empty($me['avatar_url'])): ?>
                <img id="avatarPreview" src="<?= htmlspecialchars($me['avatar_url']) ?>" class="avatar-lg" alt="Avatar">
              <?php else: ?>
                <div id="avatarPreviewFallback" class="avatar-fallback"><?= htmlspecialchars(initials($me['name'] ?? 'Kiki')) ?></div>
              <?php endif; ?>
              <div class="flex-grow-1">
                <input class="form-control" type="file" name="avatar" accept="image/jpeg,image/png" id="avatarInput">
                <div class="form-text">JPG/PNG, maks 2MB.</div>
              </div>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Nama *</label>
            <input class="form-control" name="name" value="<?= htmlspecialchars($me['name'] ?? '') ?>" required>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Email *</label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($me['email'] ?? '') ?>" required>
          </div>

          <div class="col-12 col-md-3">
            <label class="form-label">RT</label>
            <input class="form-control" value="<?= htmlspecialchars($me['rt'] ?? 5) ?>" readonly>
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label">RW</label>
            <input class="form-control" name="rw" inputmode="numeric" pattern="\d{1,3}" value="<?= htmlspecialchars($me['rw'] ?? '') ?>">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Alamat</label>
            <input class="form-control" name="address" value="<?= htmlspecialchars($me['address'] ?? '') ?>">
          </div>

          <div class="col-12">
            <button class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
          </div>
        </form>

        <?php if($err): ?>
          <div class="alert alert-danger mt-3"><i class="bi bi-x-octagon me-2"></i><?= htmlspecialchars($err) ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Toast -->
  <div class="toast-container">
    <?php if ($info): ?>
      <div id="toastOk" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body"><i class="bi bi-check2-circle me-2"></i><?= htmlspecialchars($info) ?></div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      var tOk = document.getElementById('toastOk'); if (tOk) new bootstrap.Toast(tOk, { delay: 2200 }).show();

      // Preview instan saat pilih file
      const input = document.getElementById('avatarInput');
      if (input) {
        input.addEventListener('change', (e) => {
          const file = e.target.files?.[0];
          if (!file) return;
          if (!['image/jpeg','image/png'].includes(file.type)) { alert('Hanya JPG/PNG'); input.value=''; return; }
          if (file.size > 2*1024*1024) { alert('Maksimal 2MB'); input.value=''; return; }
          const url = URL.createObjectURL(file);
          let img = document.getElementById('avatarPreview');
          if (!img) {
            const fallback = document.getElementById('avatarPreviewFallback');
            if (fallback) fallback.outerHTML = '<img id="avatarPreview" class="avatar-lg" alt="Avatar">';
            img = document.getElementById('avatarPreview');
          }
          img.src = url;
        });
      }
    });
  </script>
</body>
</html>
