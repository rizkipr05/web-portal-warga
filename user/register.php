<?php
require_once __DIR__ . '/../db.php'; // config.php sudah start session

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $nik      = preg_replace('/\D+/', '', $_POST['nik'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    // Validasi
    if ($name === '' || $nik === '' || $email === '' || $password === '' || $confirm === '') {
        $err = 'Semua field wajib diisi.';
    } elseif (strlen($nik) !== 16) {
        $err = 'NIK harus 16 digit.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $err = 'Password minimal 6 karakter.';
    } elseif ($password !== $confirm) {
        $err = 'Konfirmasi password tidak cocok.';
    } else {
        $pdo = pdo();

        // Cek unik NIK / email
        $cek = $pdo->prepare("SELECT 1 FROM users WHERE nik = ? OR email = ? LIMIT 1");
        $cek->execute([$nik, $email]);
        if ($cek->fetch()) {
            $err = 'NIK atau Email sudah terdaftar.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO users (name, nik, email, password, status) VALUES (?, ?, ?, ?, 'active')");
            $ins->execute([$name, $nik, $email, $hash]);
            header("Location: login.php?registered=1");
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Daftar Warga — Portal RT 005</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{
      --edge: clamp(16px, 5vw, 56px);
      --brand:#2563eb; --brand2:#60a5fa;
    }
    html,body{height:100%;}
    body{
      background:
        radial-gradient(900px 420px at -10% -10%, rgba(96,165,250,.25), transparent 60%),
        radial-gradient(900px 420px at 110% 120%, rgba(37,99,235,.18), transparent 60%),
        #f6f8fc;
      color:#111827;
    }
    .auth-grid{min-height:100%; display:grid; place-items:center; padding:24px}
    .card-auth{
      width:100%; max-width:560px; border:0; border-radius:20px;
      background:#ffffff; box-shadow:0 24px 50px rgba(16,24,40,.12);
    }
    .title{
      font-weight:800; letter-spacing:.2px;
      background:linear-gradient(135deg,#0f172a,#334155);
      -webkit-background-clip:text; background-clip:text; color:transparent;
    }
    .subtle{color:#6b7280}
    .form-control, .input-group-text, .btn{border-radius:12px}
    .form-control:focus{border-color:var(--brand); box-shadow:0 0 0 .2rem rgba(37,99,235,.15)}
    .btn-primary{background:linear-gradient(135deg,var(--brand),var(--brand2)); border:0}
    .btn-outline-secondary{border-color:#e5e7eb}
    .link-soft{ color:#4b5563; text-decoration:none }
    .link-soft:hover{ color:#1f2937; text-decoration:underline }
    @media (max-width: 480px){ .card-auth{border-radius:16px} }
  </style>
</head>
<body>
  <div class="auth-grid">
    <div class="card card-auth">
      <div class="card-body p-4 p-md-5">
        <h1 class="title h3 mb-1">Buat Akun Warga</h1>
        <div class="subtle mb-4">Daftar menggunakan data yang valid</div>

        <?php if ($err): ?>
          <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="bi bi-x-circle me-2"></i>
            <div><?= htmlspecialchars($err) ?></div>
          </div>
        <?php endif; ?>

        <form method="post" class="needs-validation" novalidate>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Nama Lengkap</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                <input class="form-control border-start-0" name="name" placeholder="Nama sesuai KTP"
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                <div class="invalid-feedback">Nama wajib diisi.</div>
              </div>
            </div>

            <div class="col-12">
              <label class="form-label">NIK</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person-vcard"></i></span>
                <input class="form-control border-start-0" name="nik" inputmode="numeric" maxlength="16"
                       placeholder="16 digit NIK" value="<?= htmlspecialchars($_POST['nik'] ?? '') ?>" required>
                <div class="invalid-feedback">NIK wajib 16 digit.</div>
              </div>
              <div class="form-text">Gunakan NIK yang terdaftar pada wilayah RT 005.</div>
            </div>

            <div class="col-12">
              <label class="form-label">Email</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                <input class="form-control border-start-0" type="email" name="email" placeholder="email@contoh.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                <div class="invalid-feedback">Email tidak valid.</div>
              </div>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">Password</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-lock"></i></span>
                <input class="form-control border-start-0" type="password" name="password" id="password" placeholder="Minimal 6 karakter" required>
                <button class="btn btn-outline-secondary" type="button" id="togglePw"><i class="bi bi-eye"></i></button>
                <div class="invalid-feedback">Password minimal 6 karakter.</div>
              </div>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">Konfirmasi Password</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-check"></i></span>
                <input class="form-control border-start-0" type="password" name="confirm" id="confirm" placeholder="Ulangi password" required>
                <button class="btn btn-outline-secondary" type="button" id="togglePw2"><i class="bi bi-eye"></i></button>
                <div class="invalid-feedback">Konfirmasi password wajib diisi.</div>
              </div>
            </div>
          </div>

          <div class="d-grid d-sm-flex justify-content-between align-items-center gap-2 mt-3">
            <a class="btn btn-outline-secondary flex-sm-grow-1" href="login.php">
              <i class="bi bi-arrow-left me-1"></i> Kembali ke Login
            </a>
            <button type="submit" class="btn btn-primary flex-sm-grow-1">
              <i class="bi bi-person-plus me-1"></i> Daftar
            </button>
          </div>
        </form>

        <div class="text-center mt-3">
          <span class="text-secondary small">Sudah punya akun?</span>
          <a class="link-soft small ms-1" href="login.php">Masuk</a>
        </div>
      </div>
    </div>

    <div class="mt-3 small text-secondary text-center">© <?= date('Y') ?> RT 005 Muara Telang</div>
  </div>

  <script>
    // Toggle password
    const btn1=document.getElementById('togglePw'), pw=document.getElementById('password');
    const btn2=document.getElementById('togglePw2'), cf=document.getElementById('confirm');
    function toggle(btn, input){
      if(!btn || !input) return;
      btn.addEventListener('click', () => {
        const isText = input.type === 'text';
        input.type = isText ? 'password' : 'text';
        btn.innerHTML = isText ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
      });
    }
    toggle(btn1, pw); toggle(btn2, cf);

    // Validasi NIK & password (client-side tambahan)
    (() => {
      const form = document.querySelector('.needs-validation');
      if (!form) return;
      form.addEventListener('submit', (e) => {
        const nik = form.querySelector('input[name="nik"]');
        const pass = form.querySelector('input[name="password"]');
        const conf = form.querySelector('input[name="confirm"]');
        if (nik && nik.value.replace(/\D/g,'').length !== 16) { e.preventDefault(); e.stopPropagation(); nik.setCustomValidity('NIK harus 16 digit'); }
        else if (nik) nik.setCustomValidity('');
        if (pass && pass.value.length < 6) { e.preventDefault(); e.stopPropagation(); pass.setCustomValidity('Min 6'); }
        else if (pass) pass.setCustomValidity('');
        if (conf && conf.value !== pass.value) { e.preventDefault(); e.stopPropagation(); conf.setCustomValidity('Tidak sama'); }
        else if (conf) conf.setCustomValidity('');
        form.classList.add('was-validated');
      }, false);
    })();
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
