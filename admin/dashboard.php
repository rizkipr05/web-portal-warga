<?php require_once __DIR__ . '/auth.php'; ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Admin Dashboard — Portal RT 005</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{
      --sidebar: 270px; --edge: clamp(16px, 3vw, 36px);
      --ink:#0f172a; --brand:#2563eb; --brand2:#60a5fa;
    }
    body{ background:#f6f8fc; color:#0f172a }
    .layout{ display:grid; grid-template-columns: var(--sidebar) 1fr; min-height:100vh }

    /* Sidebar */
    .sidebar{
      position:sticky; top:0; height:100vh; padding:16px 14px;
      background:#0f172a; color:#cbd5e1; box-shadow: 6px 0 24px rgba(2,6,23,.08);
    }
    .brand{ color:#fff; text-decoration:none; display:flex; align-items:center; gap:10px; font-weight:800 }
    .brand .brand-pill{
      height:30px; padding:0 10px; border-radius:999px; font-size:.8rem; display:inline-flex; align-items:center; gap:6px;
      background:linear-gradient(135deg,var(--brand),var(--brand2)); color:#fff; letter-spacing:.3px;
    }
    .nav-aside a{
      color:#b6c2db; text-decoration:none; display:flex; align-items:center; gap:10px;
      padding:10px 12px; border-radius:10px; transition:.15s;
    }
    .nav-aside a:hover{ background:#111c36; color:#fff }
    .nav-aside a.active{
      background:linear-gradient(135deg,rgba(37,99,235,.18),rgba(96,165,250,.14));
      color:#fff; border:1px solid rgba(96,165,250,.25);
    }

    /* ===== Topbar (baru) ===== */
    .topbar-wrap{ padding:16px var(--edge); position:sticky; top:0; z-index:5 }
    .topbar{
      backdrop-filter: saturate(160%) blur(8px);
      -webkit-backdrop-filter: saturate(160%) blur(8px);
      background: rgba(255,255,255,.82);
      border: 1px solid rgba(16,24,40,.06);
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(2,6,23,.08);
      padding: 10px 14px;
    }
    .page-title{ font-weight:700; letter-spacing:.2px }
    .searchbox{
      border-radius: 12px; border:1px solid #e5e7eb; background:#fff;
    }
    .searchbox:focus{ box-shadow:0 0 0 .2rem rgba(37,99,235,.15); border-color:#d6e2ff }
    .icon-btn{
      display:inline-flex; align-items:center; justify-content:center;
      width:40px; height:40px; border-radius:12px; border:1px solid #e5e7eb; background:#fff;
    }
    .icon-btn:hover{ border-color:#dbe7ff; background:#f8fbff }
    .avatar{
      width:36px; height:36px; border-radius:999px; display:grid; place-items:center;
      background:linear-gradient(135deg,#dbeafe,#bfdbfe); color:#1e3a8a; font-weight:700;
      border:1px solid #dbe7ff;
    }

    .content{ padding:16px var(--edge) }

    /* Cards */
    .card-grad{
      border:0; border-radius:18px; overflow:hidden;
      background:linear-gradient(135deg,#ffffff,#f8fbff);
      box-shadow:0 18px 40px rgba(2,6,23,.08);
    }
    .stat-icon{
      width:44px; height:44px; border-radius:12px; display:grid; place-items:center;
      background:linear-gradient(135deg, rgba(96,165,250,.2), rgba(37,99,235,.18));
      border:1px solid rgba(37,99,235,.18); color:#2563eb;
    }
    .stat-k{ color:#6b7280; font-size:.86rem }
    .stat-v{ font-weight:800; font-size:1.6rem; letter-spacing:.2px }

    .quick{
      border:1px solid #eef2f7; border-radius:16px; background:#fff;
      box-shadow:0 10px 28px rgba(2,6,23,.06);
      transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
    }
    .quick:hover{ transform:translateY(-4px); box-shadow:0 18px 46px rgba(2,6,23,.12); border-color:#e5edf8 }

    /* Mobile sidebar */
    @media (max-width: 992px){
      .layout{ grid-template-columns: 1fr }
      .sidebar{ position:fixed; inset:0 auto 0 0; width:var(--sidebar); transform:translateX(-100%); transition:.25s }
      .sidebar.show{ transform:translateX(0) }
      .hide-sm{ display:none !important; }
    }
  </style>
</head>
<body>
<div class="layout">
  <!-- SIDEBAR -->
  <aside id="sb" class="sidebar">
    <a class="brand mb-3" href="dashboard.php">
      <span>Portal RT 005</span>
      <span class="brand-pill"><i class="bi bi-shield-lock"></i> Admin</span>
    </a>
    <div class="small text-secondary mb-2">Menu</div>
    <nav class="nav-aside d-grid gap-1">
      <a class="active" href="dashboard.php"><i class="bi bi-speedometer2"></i> <span>Dashboard</span></a>
      <a href="manage_users.php"><i class="bi bi-people"></i> <span>Kelola Warga</span></a>
      <a href="manage_complaints.php"><i class="bi bi-exclamation-octagon"></i> <span>Kelola Pengaduan</span></a>
      <a href="manage_letters.php"><i class="bi bi-file-earmark-text"></i> <span>Kelola Surat</span></a>
      <a href="manage_forum.php"><i class="bi bi-chat-dots"></i> <span>Kelola Forum</span></a>
    </nav>
    <hr class="border-secondary-subtle my-3">
    <a class="nav-aside" href="logout.php"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a>
  </aside>

  <!-- MAIN -->
  <main>
    <!-- Topbar baru -->
    <div class="topbar-wrap">
      <div class="topbar">
        <div class="d-flex align-items-center gap-2 gap-md-3">
          <button class="btn btn-outline-secondary d-lg-none me-1" onclick="document.getElementById('sb').classList.toggle('show')">
            <i class="bi bi-list"></i>
          </button>

          <div class="page-title me-auto">Dashboard</div>

          <form class="d-none d-md-flex align-items-center" role="search" onsubmit="return false;">
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
              <input class="form-control searchbox border-start-0" placeholder="Cari apa saja…" />
            </div>
          </form>

          <button class="icon-btn" title="Notifikasi"><i class="bi bi-bell"></i></button>

          <div class="dropdown">
            <?php
              $name = $_SESSION['admin_name'] ?? 'Admin';
              $initials = strtoupper(mb_substr($name,0,1));
            ?>
            <button class="btn p-0 border-0" data-bs-toggle="dropdown" aria-expanded="false" title="Profil">
              <div class="avatar"><?= htmlspecialchars($initials) ?></div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
              <li class="dropdown-header">
                <div class="fw-semibold"><?= htmlspecialchars($name) ?></div>
                <div class="small text-secondary">Admin RT005</div>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="manage_users.php"><i class="bi bi-people me-2"></i>Kelola Warga</a></li>
              <li><a class="dropdown-item" href="manage_letters.php"><i class="bi bi-file-earmark-text me-2"></i>Kelola Surat</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- CONTENT -->
    <div class="content">
      <!-- Kartu metrik -->
      <div class="row g-3">
        <?php
          $pdo = pdo();
          $u = $pdo->query("SELECT COUNT(*) n FROM users")->fetch()['n'] ?? 0;
          $c = $pdo->query("SELECT COUNT(*) n FROM complaints")->fetch()['n'] ?? 0;
          $l = $pdo->query("SELECT COUNT(*) n FROM letters")->fetch()['n'] ?? 0;
          $t = $pdo->query("SELECT COUNT(*) n FROM forum_topics")->fetch()['n'] ?? 0;
        ?>
        <div class="col-6 col-lg-3">
          <div class="card card-grad">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="stat-icon"><i class="bi bi-people"></i></div>
              <div><div class="stat-k">Total Warga</div><div class="stat-v"><?= (int)$u ?></div></div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="card card-grad">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="stat-icon"><i class="bi bi-exclamation-octagon"></i></div>
              <div><div class="stat-k">Pengaduan</div><div class="stat-v"><?= (int)$c ?></div></div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="card card-grad">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="stat-icon"><i class="bi bi-file-earmark-text"></i></div>
              <div><div class="stat-k">Surat</div><div class="stat-v"><?= (int)$l ?></div></div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="card card-grad">
            <div class="card-body d-flex align-items-center gap-3">
              <div class="stat-icon"><i class="bi bi-chat-dots"></i></div>
              <div><div class="stat-k">Topik Forum</div><div class="stat-v"><?= (int)$t ?></div></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick links -->
      <div class="row g-3 mt-1">
        <div class="col-12 col-lg-6">
          <div class="quick p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <div class="fw-semibold">Kelola Warga</div>
                <div class="text-secondary small">Aktif/nonaktif & hapus warga</div>
              </div>
              <a class="btn btn-primary" href="manage_users.php"><i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <div class="quick p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <div class="fw-semibold">Kelola Pengaduan</div>
                <div class="text-secondary small">Proses & selesaikan pengaduan</div>
              </div>
              <a class="btn btn-primary" href="manage_complaints.php"><i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3 mt-1">
        <div class="col-12 col-lg-6">
          <div class="quick p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <div class="fw-semibold">Kelola Surat</div>
                <div class="text-secondary small">Review, setujui, atau tolak</div>
              </div>
              <a class="btn btn-primary" href="manage_letters.php"><i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <div class="quick p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <div class="fw-semibold">Kelola Forum</div>
                <div class="text-secondary small">Moderasi topik & postingan</div>
              </div>
              <a class="btn btn-primary" href="manage_forum.php"><i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /content -->
  </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
