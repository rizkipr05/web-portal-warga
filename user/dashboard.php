<?php require_once __DIR__ . '/auth.php'; ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Dashboard Warga (Full)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{ --padx: clamp(16px, 5vw, 48px); }
    .edge { padding-left: var(--padx); padding-right: var(--padx); }
    .hero{ background: linear-gradient(135deg,#2563eb 0%,#60a5fa 100%); color:#fff; border-radius: 24px; }
    .hero-btn{ background:#fff; border:0; }
    .tile{ border:0; border-radius:20px; transition:.2s }
    .tile:hover{ transform: translateY(-4px); box-shadow:0 1rem 2rem rgba(0,0,0,.08) }
    .chip{ background: rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.25) }
    body{ background:#f5f7fb; }
  </style>
</head>
<body>
  <nav class="navbar bg-white border-bottom sticky-top">
    <div class="container-fluid edge py-2">
      <a class="navbar-brand fw-semibold" href="<?= url('/user/dashboard.php') ?>">
        <i class="bi bi-people-fill me-2 text-primary"></i>Portal Warga RT 005
      </a>
      <div class="d-flex align-items-center gap-3">
        <span class="text-secondary small">Hai, <b><?= htmlspecialchars($warga['name']) ?></b></span>
        <a class="btn btn-sm btn-outline-danger" href="<?= url('/user/logout.php') ?>">
          <i class="bi bi-box-arrow-right me-1"></i>Logout
        </a>
      </div>
    </div>
  </nav>

  <section class="container-fluid edge my-4">
    <div class="hero p-4 p-md-5">
      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <h1 class="display-6 fw-bold mb-2">Selamat datang, <?= htmlspecialchars($warga['name']) ?> 👋</h1>
          <p class="mb-3">Kelola administrasi RT secara online: pengaduan, forum, dan pengajuan surat.</p>
          <div class="d-flex flex-wrap gap-2">
            <span class="badge rounded-pill chip text-white">Status akun: <b>Aktif</b></span>
            <span class="badge rounded-pill chip text-white">RT 005 • Muara Telang</span>
          </div>
        </div>
        <div class="col-lg-4 text-lg-end">
          <a href="<?= url('/user/letters.php') ?>" class="btn btn-lg hero-btn shadow-sm">
            <i class="bi bi-file-earmark-text me-2"></i>Ajukan Surat
          </a>
        </div>
      </div>
    </div>
  </section>

  <section class="container-fluid edge mb-5">
    <div class="row g-3">
      <div class="col-12 col-sm-6 col-lg-4">
        <a href="<?= url('/user/complaints.php') ?>" class="text-decoration-none">
          <div class="card tile h-100">
            <div class="card-body">
              <div class="d-flex align-items-center mb-2">
                <div class="rounded-3 p-2 bg-danger-subtle text-danger me-2"><i class="bi bi-exclamation-octagon"></i></div>
                <h5 class="m-0 text-dark">Pengaduan</h5>
              </div>
              <p class="text-secondary small mb-0">Laporkan masalah lingkungan/administrasi.</p>
            </div>
          </div>
        </a>
      </div>
      <div class="col-12 col-sm-6 col-lg-4">
        <a href="<?= url('/user/forum.php') ?>" class="text-decoration-none">
          <div class="card tile h-100">
            <div class="card-body">
              <div class="d-flex align-items-center mb-2">
                <div class="rounded-3 p-2 bg-success-subtle text-success me-2"><i class="bi bi-chat-dots"></i></div>
                <h5 class="m-0 text-dark">Forum Diskusi</h5>
              </div>
              <p class="text-secondary small mb-0">Diskusikan topik seputar RT 005.</p>
            </div>
          </div>
        </a>
      </div>
      <div class="col-12 col-sm-6 col-lg-4">
        <a href="<?= url('/user/letters.php') ?>" class="text-decoration-none">
          <div class="card tile h-100">
            <div class="card-body">
              <div class="d-flex align-items-center mb-2">
                <div class="rounded-3 p-2 bg-warning-subtle text-warning me-2"><i class="bi bi-file-earmark-text"></i></div>
                <h5 class="m-0 text-dark">Surat Online</h5>
              </div>
              <p class="text-secondary small mb-0">Pengajuan & status surat.</p>
            </div>
          </div>
        </a>
      </div>
    </div>
  </section>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
