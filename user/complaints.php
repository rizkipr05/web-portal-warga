<?php
require_once __DIR__ . '/auth.php';

$pdo = pdo();
$uid = (int)$warga['id'];
$err = $ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title   = trim($_POST['title'] ?? '');
  $content = trim($_POST['content'] ?? '');
  if ($title === '' || $content === '') {
    $err = 'Judul dan isi pengaduan wajib diisi.';
  } else {
    $stmt = $pdo->prepare("INSERT INTO complaints(user_id,title,content) VALUES (?,?,?)");
    $stmt->execute([$uid, $title, $content]);
    $ok = 'Pengaduan terkirim!';
    $_POST = []; // reset form
  }
}

// ambil riwayat
$list = $pdo->prepare("SELECT id,title,status,created_at FROM complaints WHERE user_id=? ORDER BY created_at DESC");
$list->execute([$uid]);
$list = $list->fetchAll();


// helper status → badge class
function status_class($s) {
  return $s === 'resolved' ? 'badge-ok'
       : ($s === 'in_progress' ? 'badge-proses' : 'badge-open');
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Pengaduan — Portal RT 005</title>
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

    /* Navbar + profile */
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

    /* Left form card */
    .panel{
      border:1px solid rgba(16,24,40,.06); background:var(--card);
      border-radius:18px; box-shadow:0 20px 48px rgba(2,6,23,.08);
      overflow:hidden;
    }
    .panel-head{
      padding:18px 18px; border-bottom:1px solid var(--border);
      background-image:linear-gradient(135deg, rgba(37,99,235,.10), rgba(96,165,250,.06));
    }
    .panel-body{ padding:18px }

    .input-ico{ position:relative }
    .input-ico .bi{ position:absolute; left:12px; top:50%; transform:translateY(-50%); opacity:.75 }
    .input-ico input, .input-ico textarea{ padding-left:40px !important }
    .form-control{ border:1px solid var(--border); border-radius:12px }
    .form-control:focus{ border-color:#d6e2ff; box-shadow:0 0 0 .2rem rgba(37,99,235,.15) }

    .btn-brand{
      background:linear-gradient(135deg,var(--brand),var(--brand2));
      border:0; font-weight:700;
      box-shadow:0 10px 26px rgba(2,6,23,.18);
    }
    .btn-brand:hover{ filter:brightness(.96) }

    /* Right table card */
    .card-lite{
      border:1px solid rgba(16,24,40,.06); background:#fff; border-radius:18px;
      box-shadow:0 18px 40px rgba(2,6,23,.08);
    }
    .table > :not(caption) > * > *{ padding:12px 14px }
    .table thead th{ color:#334155; font-weight:700; border-bottom:1px solid #e8eef8 }
    .table tbody td{ border-top:1px solid #eef2f7 }

    /* Status chips */
    .badge-open{ background:#e2e8f0; color:#0f172a; border-radius:999px; }
    .badge-proses{ background:#fff7ed; color:#9a3412; border-radius:999px; }
    .badge-ok{ background:#ecfdf5; color:#065f46; border-radius:999px; }

    /* Toast */
    .toast-container{ position:fixed; top:20px; right:20px; z-index:1080 }
  </style>
</head>
<body>

<!-- NAV -->
<nav class="navbar">
  <div class="container-fluid edge py-2">
    <a class="navbar-brand fw-semibold d-flex align-items-center gap-2" href="<?= url('/user/dashboard.php') ?>">
      <span class="brand-pill"><i class="bi bi-exclamation-octagon"></i> Pengaduan</span>
    </a>
    <div class="d-flex align-items-center gap-2">
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
          <li><a class="dropdown-item" href="<?= url('/user/dashboard.php') ?>"><i class="bi bi-file-earmark-text me-2"></i> Dashboard</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="<?= url('/user/logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i> Keluar</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<!-- CONTENT -->
<div class="container-fluid edge my-4">
  <div class="row g-4">
    <!-- Form Pengaduan -->
    <div class="col-12 col-lg-5">
      <div class="panel">
        <div class="panel-head d-flex align-items-center justify-content-between">
          <div class="h6 m-0 fw-bold d-flex align-items-center gap-2">
            <span class="badge text-bg-danger rounded-pill"><i class="bi bi-pencil-square"></i></span>
            Buat Pengaduan
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
              <i class="bi bi-check2-circle me-2"></i><?= htmlspecialchars($ok) ?>
            </div>
          <?php endif; ?>

          <form method="post" novalidate>
            <div class="mb-3">
              <label class="form-label">Judul</label>
              <div class="input-ico">
                <i class="bi bi-type"></i>
                <input class="form-control" name="title" placeholder="Contoh: Lampu jalan mati di RT 005"
                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Isi Pengaduan</label>
              <div class="input-ico">
                <i class="bi bi-card-text"></i>
                <textarea class="form-control" name="content" rows="6"
                          placeholder="Jelaskan lokasi, waktu kejadian, dan dampaknya..."><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
              </div>
              <div class="form-text">Gunakan bahasa yang jelas dan sopan. Sertakan detail seperlunya.</div>
            </div>

            <div class="d-flex gap-2">
              <button class="btn btn-brand"><i class="bi bi-send me-1"></i> Kirim</button>
    <a class="btn btn-outline-secondary" href="<?= url('/user/dashboard.php') ?>">Batal</a>
    <a class="btn btn-outline-primary" href="<?= url('/user/complaints_all.php') ?>">
      <i class="bi bi-people me-1"></i> Lihat Pengaduan Warga
    </a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Riwayat Pengaduan -->
    <div class="col-12 col-lg-7">
      <div class="card-lite">
        <div class="panel-head d-flex align-items-center justify-content-between">
          <div class="h6 m-0 fw-bold d-flex align-items-center gap-2">
            <span class="badge text-bg-light rounded-pill"><i class="bi bi-clock-history"></i></span>
            Riwayat Pengaduan
          </div>
        </div>
        <div class="panel-body">
          <?php if(!$list): ?>
            <div class="alert alert-info m-0"><i class="bi bi-info-circle me-2"></i>Belum ada pengaduan.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr>
                    <th style="width:160px">Waktu</th>
                    <th>Judul</th>
                    <th style="width:140px">Status</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach($list as $r): ?>
                  <tr>
                    <td><?= htmlspecialchars($r['created_at']) ?></td>
                    <td class="text-truncate" style="max-width:460px">
                      <a class="text-decoration-none" href="<?= url('/user/complaint_detail.php?id='.(int)$r['id']) ?>">
                        <?= htmlspecialchars($r['title']) ?>
                      </a>
                    </td>
                    <td>
                      <?php $cls = status_class($r['status']); ?>
                      <span class="badge <?= $cls ?> text-uppercase" style="font-size:.78rem;letter-spacing:.3px">
                        <?= htmlspecialchars($r['status']) ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-4">
    <a class="btn btn-outline-secondary" href="<?= url('/user/dashboard.php') ?>">← Kembali</a>
  </div>
</div>

<!-- Toasts (modern) -->
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
    // Auto show toast
    var tErr = document.getElementById('toastErr');
    if (tErr) new bootstrap.Toast(tErr, { delay: 4200 }).show();
    var tOk = document.getElementById('toastOk');
    if (tOk) new bootstrap.Toast(tOk, { delay: 2200 }).show();

    // Auto-resize textarea ringan
    const ta = document.querySelector('textarea[name="content"]');
    if (ta) {
      const fit = () => { ta.style.height = 'auto'; ta.style.height = (ta.scrollHeight + 2) + 'px'; };
      ta.addEventListener('input', fit); fit();
    }
  });
</script>
</body>
</html>
