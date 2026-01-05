<?php
require_once __DIR__ . '/auth.php';

$pdo = pdo();

// ambil semua pengaduan untuk warga
$all = $pdo->query(
  "SELECT c.id, c.title, c.status, c.created_at, u.name
   FROM complaints c
   JOIN users u ON u.id = c.user_id
   ORDER BY c.created_at DESC"
)->fetchAll();

function status_class($s) {
  return $s === 'resolved' ? 'badge-ok'
       : ($s === 'in_progress' ? 'badge-proses' : 'badge-open');
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Daftar Pengaduan Warga — Portal RT 005</title>
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

    .card-lite{
      border:1px solid rgba(16,24,40,.06); background:#fff; border-radius:18px;
      box-shadow:0 18px 40px rgba(2,6,23,.08);
    }
    .panel-head{
      padding:18px 18px; border-bottom:1px solid var(--border);
      background-image:linear-gradient(135deg, rgba(37,99,235,.10), rgba(96,165,250,.06));
    }
    .panel-body{ padding:18px }

    .table > :not(caption) > * > *{ padding:12px 14px }
    .table thead th{ color:#334155; font-weight:700; border-bottom:1px solid #e8eef8 }
    .table tbody td{ border-top:1px solid #eef2f7 }

    .badge-open{ background:#e2e8f0; color:#0f172a; border-radius:999px; }
    .badge-proses{ background:#fff7ed; color:#9a3412; border-radius:999px; }
    .badge-ok{ background:#ecfdf5; color:#065f46; border-radius:999px; }
  </style>
</head>
<body>

<nav class="navbar">
  <div class="container-fluid edge py-2">
    <a class="navbar-brand fw-semibold d-flex align-items-center gap-2" href="<?= url('/user/complaints.php') ?>">
      <span class="brand-pill"><i class="bi bi-people"></i> Pengaduan Warga</span>
    </a>
    <a class="btn btn-outline-secondary" href="<?= url('/user/complaints.php') ?>">
      <i class="bi bi-arrow-left"></i> Kembali
    </a>
  </div>
</nav>

<div class="container-fluid edge my-4">
  <div class="card-lite">
    <div class="panel-head d-flex align-items-center justify-content-between">
      <div class="h6 m-0 fw-bold d-flex align-items-center gap-2">
        <span class="badge text-bg-primary rounded-pill"><i class="bi bi-people"></i></span>
        Daftar Pengaduan Warga
      </div>
      <div class="text-secondary small">Total: <?= count($all) ?></div>
    </div>
    <div class="panel-body">
      <?php if(!$all): ?>
        <div class="alert alert-info m-0"><i class="bi bi-info-circle me-2"></i>Belum ada pengaduan warga.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th style="width:160px">Waktu</th>
                <th>Judul</th>
                <th style="width:200px">Pelapor</th>
                <th style="width:140px">Status</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach($all as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['created_at']) ?></td>
                <td class="text-truncate" style="max-width:520px">
                  <a class="text-decoration-none" href="<?= url('/user/complaint_detail.php?id='.(int)$r['id']) ?>">
                    <?= htmlspecialchars($r['title']) ?>
                  </a>
                </td>
                <td><?= htmlspecialchars($r['name']) ?></td>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
