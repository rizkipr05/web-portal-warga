<?php
require_once __DIR__ . '/auth.php';

$pdo = pdo();
$uid = (int)$warga['id'];

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  header('Location: ' . url('/user/complaints.php'));
  exit;
}

// ambil detail pengaduan
$st = $pdo->prepare(
  "SELECT c.id, c.title, c.content, c.status, c.created_at,
          u.name, u.rt, u.rw, u.address
   FROM complaints c
   JOIN users u ON u.id = c.user_id
   WHERE c.id = ? LIMIT 1"
);
$st->execute([$id]);
$row = $st->fetch();

if (!$row) {
  header('Location: ' . url('/user/complaints.php'));
  exit;
}

// catat view (hindari duplikasi)
$pdo->prepare("INSERT IGNORE INTO complaint_views (complaint_id, user_id) VALUES (?, ?)")
    ->execute([$id, $uid]);

// ambil daftar warga yang sudah melihat
$viewers = $pdo->prepare(
  "SELECT u.name, u.rt, u.rw, cv.viewed_at
   FROM complaint_views cv
   JOIN users u ON u.id = cv.user_id
   WHERE cv.complaint_id = ?
   ORDER BY cv.viewed_at DESC"
);
$viewers->execute([$id]);
$viewers = $viewers->fetchAll();

function status_class($s) {
  return $s === 'resolved' ? 'badge-ok'
       : ($s === 'in_progress' ? 'badge-proses' : 'badge-open');
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Detail Pengaduan — Portal RT 005</title>
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

    .badge-open{ background:#e2e8f0; color:#0f172a; border-radius:999px; }
    .badge-proses{ background:#fff7ed; color:#9a3412; border-radius:999px; }
    .badge-ok{ background:#ecfdf5; color:#065f46; border-radius:999px; }
  </style>
</head>
<body>

<nav class="navbar">
  <div class="container-fluid edge py-2">
    <a class="navbar-brand fw-semibold d-flex align-items-center gap-2" href="<?= url('/user/complaints.php') ?>">
      <span class="brand-pill"><i class="bi bi-exclamation-octagon"></i> Detail Pengaduan</span>
    </a>
  </div>
</nav>

<div class="container-fluid edge my-4">
  <div class="panel mb-4">
    <div class="panel-head d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div>
        <div class="h5 fw-bold mb-1">#<?= (int)$row['id'] ?> — <?= htmlspecialchars($row['title']) ?></div>
        <div class="text-secondary small">
          oleh <b><?= htmlspecialchars($row['name']) ?></b>
          • <?= htmlspecialchars($row['created_at'] ?? '') ?>
        </div>
      </div>
      <div>
        <?php $cls = status_class($row['status']); ?>
        <span class="badge <?= $cls ?> text-uppercase px-3 py-2" style="font-size:.78rem;letter-spacing:.3px">
          <?= htmlspecialchars($row['status']) ?>
        </span>
      </div>
    </div>
    <div class="panel-body">
      <div class="row g-3">
        <div class="col-12 col-lg-8">
          <div class="mb-2 fw-semibold">Isi Pengaduan</div>
          <div class="p-3 rounded border bg-light-subtle">
            <?= nl2br(htmlspecialchars($row['content'] ?? '')) ?>
          </div>
        </div>
        <div class="col-12 col-lg-4">
          <div class="mb-2 fw-semibold">Info Pelapor</div>
          <div class="p-3 rounded border bg-white">
            <div class="mb-1"><i class="bi bi-person me-2"></i><?= htmlspecialchars($row['name']) ?></div>
            <div class="mb-1"><i class="bi bi-geo-alt me-2"></i>RT <?= (int)($row['rt'] ?? 5) ?> / RW <?= htmlspecialchars($row['rw'] ?? '-') ?></div>
            <div><i class="bi bi-house-door me-2"></i><?= htmlspecialchars($row['address'] ?? '-') ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head d-flex align-items-center justify-content-between">
      <div class="h6 m-0 fw-bold d-flex align-items-center gap-2">
        <span class="badge text-bg-primary rounded-pill"><i class="bi bi-eye"></i></span>
        Warga Yang Sudah Melihat
      </div>
      <div class="text-secondary small">Total: <?= count($viewers) ?></div>
    </div>
    <div class="panel-body">
      <?php if (!$viewers): ?>
        <div class="alert alert-info m-0"><i class="bi bi-info-circle me-2"></i>Belum ada yang melihat.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Nama</th>
                <th style="width:140px">RT/RW</th>
                <th style="width:200px">Waktu Lihat</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($viewers as $v): ?>
              <tr>
                <td><?= htmlspecialchars($v['name']) ?></td>
                <td>RT <?= (int)($v['rt'] ?? 5) ?> / RW <?= htmlspecialchars($v['rw'] ?? '-') ?></td>
                <td><?= htmlspecialchars($v['viewed_at']) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="mt-4">
    <a class="btn btn-outline-secondary" href="<?= url('/user/complaints.php') ?>">← Kembali</a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
