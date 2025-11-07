<?php require_once __DIR__ . '/auth.php'; ?>
<?php
// --- Helper nama & inisial
$fullName = trim($warga['name'] ?? 'Kiki');
$words    = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY);
$firstName= $words[0] ?? 'Kiki';

if (!function_exists('user_initials')) {
  function user_initials($name) {
    $name  = trim($name ?: 'Kiki');
    $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);
    if (count($parts) >= 2) {
      return mb_strtoupper(mb_substr($parts[0],0,1) . mb_substr(end($parts),0,1));
    }
    return mb_strtoupper(mb_substr($name,0,1));
  }
}

// --- Pastikan avatar_url tersedia jika auth.php belum set
if (empty($warga['avatar_url']) && !empty($warga['avatar_path'])) {
  $warga['avatar_url'] = url('/' . ltrim($warga['avatar_path'], '/'));
}
$initials = user_initials($fullName);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Dashboard Warga — Portal RT 005</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{
      --padx: clamp(16px, 5vw, 48px);
      --brand:#2563eb; --brand2:#60a5fa; --ink:#0f172a;
    }
    body{ background:#f5f7fb; color:var(--ink) }
    .edge { padding-left: var(--padx); padding-right: var(--padx); }

    /* NAV */
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
    .avatar{
      width:34px; height:34px; border-radius:999px; display:inline-grid; place-items:center;
      font-weight:700; background:linear-gradient(135deg,var(--brand),var(--brand2)); color:#fff;
      box-shadow: 0 6px 16px rgba(37,99,235,.25);
    }
    .profile-btn{
      background:#fff; border:1px solid #e6eaf3; border-radius:999px;
      padding:.25rem .5rem .25rem .25rem;
    }
    .profile-btn:hover{ border-color:#d5def0; background:#f8fbff }

    /* HERO */
    .hero{
      background: linear-gradient(135deg,#2563eb 0%,#60a5fa 100%);
      color:#fff; border-radius: 24px; overflow:hidden;
      box-shadow: 0 24px 60px rgba(37,99,235,.25);
    }
    .hero .chip{
      background: rgba(255,255,255,.16); border:1px solid rgba(255,255,255,.28);
    }
    .hero-btn{
      background:#fff; border:0; font-weight:700;
      box-shadow: 0 10px 26px rgba(2,6,23,.18);
    }

    /* TILES */
    .tile{ border:0; border-radius:20px; transition:.18s; background:#fff }
    .tile:hover{ transform: translateY(-4px); box-shadow:0 22px 46px rgba(2,6,23,.10) }
    .tile .icon{
      display:grid; place-items:center; width:40px; height:40px; border-radius:12px;
      border:1px solid #e6eaf3;
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
        <!-- Dropdown Profil (gabungan avatar/inisial + menu) -->

<div class="dropdown">
  <button class="btn profile-btn d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
    <?php if(!empty($warga['avatar_url'])): ?>
      <img src="<?= htmlspecialchars($warga['avatar_url']) ?>"
           alt="Avatar"
           style="width:34px;height:34px;border-radius:999px;object-fit:cover;box-shadow:0 6px 16px rgba(37,99,235,.25)">
    <?php else: ?>
      <span class="avatar"><?= htmlspecialchars(user_initials($warga['name'] ?? 'Kiki')) ?></span>
    <?php endif; ?>
    <span class="d-none d-sm-inline small"><b><?= htmlspecialchars($warga['name'] ?? 'Kiki') ?></b></span>
    <i class="bi bi-chevron-down small"></i>
  </button>
  <ul class="dropdown-menu dropdown-menu-end shadow">
    <li class="dropdown-header">
      <div class="fw-semibold"><?= htmlspecialchars($warga['name'] ?? 'Kiki') ?></div>
      <div class="text-secondary small">RT 005 • Muara Telang</div>
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

  <!-- HERO -->
  <section class="container-fluid edge my-4">
    <div class="hero p-4 p-md-5">
      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <h1 class="display-6 fw-bold mb-2">Selamat datang, <?= htmlspecialchars($firstName ?: 'Kiki') ?> 👋</h1>
          <p class="mb-3 opacity-95">Kelola administrasi RT secara online: pengaduan, forum, dan pengajuan surat.</p>
          <div class="d-flex flex-wrap gap-2">
            <span class="badge rounded-pill chip text-white">Status akun: <b>Aktif</b></span>
            <span class="badge rounded-pill chip text-white">RT 005 • Muara Telang</span>
          </div>
        </div>
        <div class="col-lg-4 text-lg-end">
          <a href="<?= url('/user/letters.php') ?>" class="btn btn-lg hero-btn">
            <i class="bi bi-file-earmark-text me-2"></i> Ajukan Surat
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- QUICK TILES -->
  <section class="container-fluid edge mb-5">
    <div class="row g-3">
      <div class="col-12 col-sm-6 col-lg-4">
        <a href="<?= url('/user/complaints.php') ?>" class="text-decoration-none">
          <div class="card tile h-100 p-2">
            <div class="card-body">
              <div class="d-flex align-items-center mb-2">
                <div class="icon bg-danger-subtle text-danger me-2"><i class="bi bi-exclamation-octagon"></i></div>
                <h5 class="m-0 text-dark">Pengaduan</h5>
              </div>
              <p class="text-secondary small mb-0">Laporkan masalah lingkungan atau administrasi.</p>
            </div>
          </div>
        </a>
      </div>

      <div class="col-12 col-sm-6 col-lg-4">
        <a href="<?= url('/user/forum.php') ?>" class="text-decoration-none">
          <div class="card tile h-100 p-2">
            <div class="card-body">
              <div class="d-flex align-items-center mb-2">
                <div class="icon bg-success-subtle text-success me-2"><i class="bi bi-chat-dots"></i></div>
                <h5 class="m-0 text-dark">Forum Diskusi</h5>
              </div>
              <p class="text-secondary small mb-0">Diskusikan topik seputar kegiatan RT 005.</p>
            </div>
          </div>
        </a>
      </div>

      <div class="col-12 col-sm-6 col-lg-4">
        <a href="<?= url('/user/letters.php') ?>" class="text-decoration-none">
          <div class="card tile h-100 p-2">
            <div class="card-body">
              <div class="d-flex align-items-center mb-2">
                <div class="icon bg-warning-subtle text-warning me-2"><i class="bi bi-file-earmark-text"></i></div>
                <h5 class="m-0 text-dark">Surat Online</h5>
              </div>
              <p class="text-secondary small mb-0">Ajukan dan pantau status surat Anda.</p>
            </div>
          </div>
        </a>
      </div>
    </div>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
