<?php
require_once __DIR__ . '/../db.php';

$err = $ok = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $nik  = preg_replace('/\D+/', '', $_POST['nik'] ?? '');
  $email= trim($_POST['email'] ?? '');
  $rt   = (int)($_POST['rt'] ?? 0);
  $rw   = (int)($_POST['rw'] ?? 0);
  $addr = trim($_POST['address'] ?? '');
  $pass = $_POST['password'] ?? '';
  $pass2= $_POST['password2'] ?? '';

  if ($name==='' || $nik==='' || $email==='' || $pass==='') {
    $err = 'Semua field bertanda * wajib diisi.';
  } elseif (strlen($nik) !== 16) {
    $err = 'NIK harus 16 digit.';
  } elseif ($pass !== $pass2) {
    $err = 'Konfirmasi password tidak cocok.';
  } elseif ($rt !== 5) {
    $err = 'Hanya warga RT 005 yang dapat mendaftar.';
  } else {
    try {
      $pdo = pdo();
      $du = $pdo->prepare("SELECT 1 FROM users WHERE nik=? OR email=? LIMIT 1");
      $du->execute([$nik,$email]);
      if ($du->fetch()) {
        $err = 'NIK atau Email sudah terdaftar.';
      } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $st = $pdo->prepare("INSERT INTO users (name, nik, email, password, status, rt, rw, address, created_at)
                             VALUES (?,?,?,?, 'active', ?, ?, ?, NOW())");
        $st->execute([$name,$nik,$email,$hash,$rt,$rw,$addr]);
        $ok = 'Registrasi berhasil! Mengarahkan ke halaman login…';
      }
    } catch (Throwable $e) {
      $err = 'Gagal mendaftar: '.$e->getMessage();
    }
  }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Daftar Warga RT 005</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{ --brand:#2563eb; --brand2:#60a5fa; --ink:#0f172a; --border:#e5e7eb; --radius:18px; }
    body{
      margin:0; color:var(--ink); background:
        radial-gradient(1200px 600px at 10% -10%, rgba(37,99,235,.08), transparent 60%),
        radial-gradient(900px 500px at 110% 0%, rgba(96,165,250,.10), transparent 55%),
        #f6f8fc;
      min-height:100vh; display:flex; align-items:center;
    }
    .wrap{ width:100%; padding:24px }
    .panel{
      max-width:640px; margin-inline:auto; border-radius:var(--radius); background:#fff;
      border:1px solid rgba(16,24,40,.06); box-shadow:0 24px 60px rgba(2,6,23,.10); overflow:hidden;
    }
    .panel-head{ padding:22px; background-image:linear-gradient(135deg, rgba(37,99,235,.10), rgba(96,165,250,.06)); border-bottom:1px solid var(--border) }
    .badge-pill{
      display:inline-flex; gap:8px; align-items:center; padding:8px 12px; border-radius:999px;
      background:linear-gradient(135deg,var(--brand),var(--brand2)); color:#fff; font-weight:600; letter-spacing:.2px;
      box-shadow:0 8px 22px rgba(37,99,235,.25);
    }
    .panel-body{ padding:24px 22px 26px }
    .sub{ color:#64748b; font-size:.95rem }

    .input-ico{ position:relative }
    .input-ico .bi{ position:absolute; left:12px; top:50%; transform:translateY(-50%); opacity:.7 }
    .input-ico input{ padding-left:40px !important }
    .form-control{ border-radius:12px; border:1px solid var(--border); padding:10px 12px; background:#fff }
    .form-control:focus{ border-color:#d6e2ff; box-shadow:0 0 0 .2rem rgba(37,99,235,.15) }
    .row.g-2 > [class^="col"]{ margin-top:.25rem }

    .btn-primary{ background:linear-gradient(135deg,var(--brand),var(--brand2)); border:0; font-weight:600 }
    .btn-primary:hover{ filter:brightness(.95) }
    .muted-link{ color:#2563eb; text-decoration:none } .muted-link:hover{ text-decoration:underline }

    .toggle-pass{ position:absolute; right:10px; top:50%; transform:translateY(-50%); border:0; background:transparent; color:#64748b }

    .toast-container{ position:fixed; top:20px; right:20px; z-index:1080 }
  </style>
</head>
<body>
<div class="wrap">
  <div class="panel">
    <div class="panel-head">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <div class="badge-pill"><i class="bi bi-house-heart"></i> Portal Warga</div>
          <h1 class="h4 fw-bold mt-3 mb-1">Daftar Warga RT 005</h1>
          <div class="sub">Isi data di bawah ini untuk membuat akun warga.</div>
        </div>
      </div>
    </div>

    <div class="panel-body">
      <?php if($err): ?>
        <div class="alert alert-danger d-none d-md-block">
          <i class="bi bi-x-octagon me-2"></i><?= htmlspecialchars($err) ?>
        </div>
      <?php endif; ?>
      <?php if($ok): ?>
        <div class="alert alert-success d-none d-md-block">
          <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($ok) ?>
        </div>
      <?php endif; ?>

      <form method="post" novalidate>
        <div class="row g-2">
          <div class="col-12">
            <label class="form-label">Nama *</label>
            <div class="input-ico">
              <i class="bi bi-person"></i>
              <input class="form-control" name="name" autocomplete="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">NIK *</label>
            <div class="input-ico">
              <i class="bi bi-credit-card-2-front"></i>
              <input class="form-control" name="nik" inputmode="numeric" pattern="\d{16}" minlength="16" maxlength="16" placeholder="Hanya angka"
                     value="<?= htmlspecialchars($_POST['nik'] ?? '') ?>" required>
            </div>
            <div class="form-text">Wajib 16 digit, hanya angka.</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Email *</label>
            <div class="input-ico">
              <i class="bi bi-envelope"></i>
              <input type="email" class="form-control" name="email" autocomplete="email"
                     value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
          </div>

          <div class="col-6">
            <label class="form-label">RT *</label>
            <div class="input-ico">
              <i class="bi bi-sign-stop"></i>
              <input class="form-control" name="rt" inputmode="numeric" pattern="\d{1,3}"
                     value="<?= htmlspecialchars($_POST['rt'] ?? '5') ?>" required>
            </div>
          </div>

          <div class="col-6">
            <label class="form-label">RW</label>
            <div class="input-ico">
              <i class="bi bi-signpost-split"></i>
              <input class="form-control" name="rw" inputmode="numeric" pattern="\d{1,3}"
                     value="<?= htmlspecialchars($_POST['rw'] ?? '') ?>">
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">Alamat</label>
            <div class="input-ico">
              <i class="bi bi-geo-alt"></i>
              <input class="form-control" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
            </div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Password *</label>
            <div class="position-relative input-ico">
              <i class="bi bi-lock"></i>
              <input type="password" class="form-control" name="password" id="pw1" required>
              <button class="toggle-pass" type="button" data-target="#pw1" aria-label="Tampilkan password">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Ulangi Password *</label>
            <div class="position-relative input-ico">
              <i class="bi bi-lock-fill"></i>
              <input type="password" class="form-control" name="password2" id="pw2" required>
              <button class="toggle-pass" type="button" data-target="#pw2" aria-label="Tampilkan password">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>
        </div>

        <button class="btn btn-primary w-100 mt-3 py-2">
          <i class="bi bi-person-plus me-1"></i> Daftar
        </button>
      </form>

      <div class="text-center mt-3">
        <a class="muted-link" href="login.php">Sudah punya akun? Login</a>
      </div>
    </div>
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
    var tErr = document.getElementById('toastErr');
    if (tErr) new bootstrap.Toast(tErr, { delay: 4500 }).show();

    var tOk = document.getElementById('toastOk');
    if (tOk) {
      new bootstrap.Toast(tOk, { delay: 2200 }).show();
      setTimeout(()=>{ window.location.href = 'login.php?registered=1'; }, 2200);
    }

    document.querySelectorAll('.toggle-pass').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const inp = document.querySelector(btn.dataset.target);
        if (!inp) return;
        const eye = btn.querySelector('i');
        if (inp.type === 'password') { inp.type='text'; eye.classList.replace('bi-eye','bi-eye-slash'); }
        else { inp.type='password'; eye.classList.replace('bi-eye-slash','bi-eye'); }
      });
    });

    const nik = document.querySelector('input[name="nik"]');
    if (nik) nik.addEventListener('input', ()=>{ nik.value = nik.value.replace(/\D+/g,'').slice(0,16); });
    const rt = document.querySelector('input[name="rt"]');
    if (rt) rt.addEventListener('input', ()=>{ rt.value = rt.value.replace(/\D+/g,'').slice(0,3); });
    const rw = document.querySelector('input[name="rw"]');
    if (rw) rw.addEventListener('input', ()=>{ rw.value = rw.value.replace(/\D+/g,'').slice(0,3); });
  });
</script>
</body>
</html>
