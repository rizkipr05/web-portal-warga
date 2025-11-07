<?php
require_once __DIR__ . '/auth.php';

$pdo = pdo();
$uid = (int)$warga['id'];
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  if ($title === '') {
    $err = 'Judul topik wajib diisi.';
  } else {
    $pdo->prepare("INSERT INTO forum_topics(user_id,title) VALUES (?,?)")->execute([$uid, $title]);
    header('Location: ' . url('/user/forum.php'));
    exit;
  }
}

$topics = $pdo->query("
  SELECT t.id,t.title,t.created_at,u.name
  FROM forum_topics t
  JOIN users u ON u.id=t.user_id
  ORDER BY t.created_at DESC
")->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Forum Diskusi — Portal RT 005</title>
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

    /* Navbar */
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
    .input-ico input{ padding-left:40px !important }
    .form-control{ border:1px solid var(--border); border-radius:12px }
    .form-control:focus{ border-color:#d6e2ff; box-shadow:0 0 0 .2rem rgba(37,99,235,.15) }

    .btn-brand{
      background:linear-gradient(135deg,var(--brand),var(--brand2));
      border:0; font-weight:700;
      box-shadow:0 10px 26px rgba(2,6,23,.18);
    }
    .btn-brand:hover{ filter:brightness(.96) }

    /* Topic list items */
    .topic{
      border:1px solid var(--border);
      border-radius:16px;
      padding:14px 14px;
      background:#fff;
      transition:.16s ease;
    }
    .topic:hover{ transform: translateY(-2px); box-shadow:0 14px 32px rgba(2,6,23,.08) }

    /* Toast */
    .toast-container{ position:fixed; top:20px; right:20px; z-index:1080 }
  </style>
</head>
<body>

<!-- NAV -->
<nav class="navbar">
  <div class="container-fluid edge py-2">
    <a class="navbar-brand fw-semibold d-flex align-items-center gap-2" href="<?= url('/user/dashboard.php') ?>">
      <span class="brand-pill"><i class="bi bi-chat-dots"></i> Forum Diskusi</span>
    </a>

    <div class="d-flex align-items-center gap-2">
      <!-- Dropdown profil: HANYA “Profil” & “Dashboard” -->
      <div class="dropdown">
        <button class="btn profile-btn d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
          <?php if(!empty($warga['avatar_url'])): ?>
            <img src="<?= htmlspecialchars($warga['avatar_url']) ?>" alt="Avatar"
                 style="width:34px;height:34px;border-radius:999px;object-fit:cover;box-shadow:0 6px 16px rgba(37,99,235,.25)">
          <?php else: ?>
            <span class="avatar"><?= htmlspecialchars(user_initials($warga['name'] ?? 'Kiki')) ?></span>
          <?php endif; ?>
          <span class="d-none d-sm-inline small">Hai, <b><?= htmlspecialchars($warga['name'] ?? 'Kiki') ?></b></span>
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
    <!-- Buat Topik -->
    <div class="col-12 col-lg-5">
      <div class="panel">
        <div class="panel-head d-flex align-items-center gap-2">
          <span class="badge text-bg-success rounded-pill"><i class="bi bi-plus-circle"></i></span>
          <div class="h6 m-0 fw-bold">Buat Topik Baru</div>
        </div>
        <div class="panel-body">
          <?php if($err): ?>
            <div class="alert alert-danger d-none d-md-block">
              <i class="bi bi-x-octagon me-2"></i><?= htmlspecialchars($err) ?>
            </div>
          <?php endif; ?>

          <form method="post" class="d-flex gap-2 flex-wrap" novalidate>
            <div class="input-ico flex-grow-1">
              <i class="bi bi-type"></i>
              <input class="form-control" name="title" placeholder="Judul topik..."
                     value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
            </div>
            <button class="btn btn-brand"><i class="bi bi-send me-1"></i> Posting</button>
          </form>
          <div class="form-text mt-2">Gunakan judul yang jelas dan singkat ya.</div>
        </div>
      </div>
    </div>

    <!-- Daftar Topik -->
    <div class="col-12 col-lg-7">
      <div class="panel">
        <div class="panel-head d-flex align-items-center gap-2">
          <span class="badge text-bg-light rounded-pill"><i class="bi bi-list-ul"></i></span>
          <div class="h6 m-0 fw-bold">Daftar Topik</div>
        </div>
        <div class="panel-body">
          <?php if(!$topics): ?>
            <div class="alert alert-info m-0"><i class="bi bi-info-circle me-2"></i>Belum ada topik.</div>
          <?php else: ?>
            <div class="d-grid gap-2">
              <?php foreach($topics as $t): ?>
                <div class="topic">
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="me-3">
                      <a class="fw-semibold text-decoration-none" href="<?= url('/user/forum_topic.php?id='.$t['id']) ?>">
                        <?= htmlspecialchars($t['title']) ?>
                      </a>
                      <div class="text-secondary small mt-1">
                        oleh <?= htmlspecialchars($t['name']) ?> • <?= htmlspecialchars($t['created_at']) ?>
                      </div>
                    </div>
                    <a class="btn btn-sm btn-outline-success" href="<?= url('/user/forum_topic.php?id='.$t['id']) ?>">
                      Buka
                    </a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-4">
    <a class="btn btn-outline-secondary" href="<?= url('/user/dashboard.php')?>">← Kembali</a>
  </div>
</div>

<!-- Toast error (modern) -->
<div class="toast-container">
  <?php if ($err): ?>
    <div id="toastErr" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body"><i class="bi bi-x-octagon me-2"></i><?= htmlspecialchars($err) ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function(){
    var tErr = document.getElementById('toastErr');
    if (tErr) new bootstrap.Toast(tErr, { delay: 4200 }).show();
  });
</script>
</body>
</html>
