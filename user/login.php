<?php
require_once __DIR__ . '/../db.php'; // config.php sudah start session

$err = '';
$nik = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik = preg_replace('/\D+/', '', $_POST['nik'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nik === '' || $password === '') {
        $err = "NIK dan password wajib diisi.";
    } elseif (strlen($nik) !== 16) {
        $err = "NIK harus 16 digit.";
    } else {
        $pdo = pdo();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE nik = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$nik]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: dashboard.php");
            exit;
        } else {
            $err = "NIK atau password salah.";
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Login Warga — Portal RT 005</title>
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
      width:100%; max-width:480px; border:0; border-radius:20px;
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
    .divider{display:flex; align-items:center; gap:12px; color:#94a3b8}
    .divider::before,.divider::after{content:""; flex:1; height:1px; background:#e5e7eb}
    @media (max-width: 480px){ .card-auth{border-radius:16px} }
  </style>
</head>
<body>
  <div class="auth-grid">
    <div class="card card-auth">
      <div class="card-body p-4 p-md-5">
        <!-- Header -->
        <h1 class="title h3 mb-1">Masuk ke Portal RT 005</h1>
        <div class="subtle mb-4">Lanjutkan dengan akun warga Anda</div>

        <!-- Alerts -->
        <?php if ($err): ?>
          <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div><?= htmlspecialchars($err) ?></div>
          </div>
        <?php endif; ?>
        <?php if (!empty($_GET['registered'])): ?>
          <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="bi bi-check2-circle me-2"></i>
            <div>Pendaftaran berhasil. Silakan login.</div>
          </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="post" class="needs-validation" novalidate>
          <div class="mb-3">
            <label class="form-label">NIK</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="bi bi-person-vcard"></i></span>
              <input class="form-control border-start-0" name="nik" inputmode="numeric" autocomplete="username"
                     pattern="\d{16}" minlength="16" maxlength="16" placeholder="Masukkan NIK (16 digit)"
                     value="<?= htmlspecialchars($nik) ?>" required>
              <div class="invalid-feedback">NIK harus 16 digit.</div>
            </div>
          </div>

          <div class="mb-2">
            <label class="form-label">Password</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-lock"></i></span>
              <input class="form-control border-start-0" type="password" name="password" id="password" autocomplete="current-password" placeholder="••••••••" required>
              <button class="btn btn-outline-secondary" type="button" id="togglePw" title="Tampilkan">
                <i class="bi bi-eye"></i>
              </button>
              <div class="invalid-feedback">Password wajib diisi.</div>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="remember" disabled>
              <label class="form-check-label text-secondary" for="remember">Ingat saya</label>
            </div>
            <a class="small link-soft" href="#" onclick="alert('Lupa password? Hubungi pengurus RT.');return false;">Lupa password?</a>
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2">
            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
          </button>
        </form>

        <div class="divider my-4"><span class="small">atau</span></div>

        <div class="text-center">
          <span class="text-secondary small">Belum punya akun?</span>
          <a class="link-soft small ms-1" href="register.php"><i class="bi bi-person-plus"></i> Daftar</a>
        </div>
      </div>
    </div>

    <div class="mt-3 small text-secondary text-center">© <?= date('Y') ?> RT 005 Muara Telang</div>
  </div>

  <script>
    // Toggle show/hide password
    const btn = document.getElementById('togglePw');
    const pw  = document.getElementById('password');
    if (btn && pw) {
      btn.addEventListener('click', () => {
        const isText = pw.type === 'text';
        pw.type = isText ? 'password' : 'text';
        btn.innerHTML = isText ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
        btn.title = isText ? 'Tampilkan' : 'Sembunyikan';
      });
    }
    // Bootstrap client-side validation
    (() => {
      const forms = document.querySelectorAll('.needs-validation');
      Array.from(forms).forEach(form => {
        form.addEventListener('submit', (e) => {
          if (!form.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
          form.classList.add('was-validated');
        }, false);
      });
    })();
  </script>
  <script>
    const nikInput = document.querySelector('input[name="nik"]');
    if (nikInput) {
      nikInput.addEventListener('input', () => {
        nikInput.value = nikInput.value.replace(/\D+/g, '').slice(0, 16);
      });
    }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
