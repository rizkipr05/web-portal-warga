<?php
// admin/view_complaint.php
require_once __DIR__ . '/auth.php';
$pdo = pdo();

// --- Ambil ID ---
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  header('Location: manage_complaints.php');
  exit;
}

// --- Flash helper ---
if (session_status() === PHP_SESSION_NONE) session_start();
function flash_take($key){
  if (!isset($_SESSION[$key])) return null;
  $v = $_SESSION[$key];
  unset($_SESSION[$key]);
  return $v;
}

// --- Aksi ubah status ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_status'])) {
  $status = $_POST['status'] ?? 'open';
  if (!in_array($status, ['open','in_progress','resolved'], true)) $status = 'open';
  $st = $pdo->prepare("UPDATE complaints SET status=? WHERE id=?");
  $st->execute([$status, $id]);
  $_SESSION['flash_info'] = "Status pengaduan #$id diubah ke <b>$status</b>.";
  header('Location: view_complaint.php?id='.(int)$id);
  exit;
}

// --- Ambil data pengaduan ---
$st = $pdo->prepare("SELECT c.id, c.title, c.content, c.status, c.created_at,
                            u.name, u.email, u.rt, u.rw, u.address
                     FROM complaints c
                     JOIN users u ON u.id = c.user_id
                     WHERE c.id=? LIMIT 1");
$st->execute([$id]);
$row = $st->fetch();

if (!$row) {
  $_SESSION['flash_err'] = 'Pengaduan tidak ditemukan.';
  header('Location: manage_complaints.php');
  exit;
}

$info = flash_take('flash_info');
$err  = flash_take('flash_err');

// --- util badge ---
function badge_class($s){
  return $s==='resolved' ? 'success' : ($s==='in_progress' ? 'warning' : 'secondary');
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Lihat Pengaduan #<?= (int)$row['id'] ?> — Admin</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{ --sidebar:270px; --edge:clamp(16px,3vw,36px); --brand:#2563eb; --brand2:#60a5fa; }
    body{ background:#f6f8fc; color:#0f172a }
    .layout{ display:grid; grid-template-columns:var(--sidebar) 1fr; min-height:100vh }

    /* Sidebar */
    .sidebar{ position:sticky; top:0; height:100vh; padding:16px 14px;
      background:#0f172a; color:#cbd5e1; box-shadow:6px 0 24px rgba(2,6,23,.08) }
    .brand{ color:#fff; text-decoration:none; display:flex; align-items:center; gap:10px; font-weight:800 }
    .brand .brand-pill{ height:30px; padding:0 10px; border-radius:999px; font-size:.8rem;
      display:inline-flex; align-items:center; gap:6px; background:linear-gradient(135deg,var(--brand),var(--brand2)); color:#fff }
    .nav-aside a{ color:#b6c2db; text-decoration:none; display:flex; align-items:center; gap:10px;
      padding:10px 12px; border-radius:10px; transition:.15s }
    .nav-aside a:hover{ background:#111c36; color:#fff }
    .nav-aside a.active{ background:linear-gradient(135deg,rgba(37,99,235,.18),rgba(96,165,250,.14));
      color:#fff; border:1px solid rgba(96,165,250,.25) }

    /* Topbar */
    .topbar-wrap{ padding:16px var(--edge); position:sticky; top:0; z-index:5 }
    .topbar{
      backdrop-filter:saturate(160%) blur(8px); -webkit-backdrop-filter:saturate(160%) blur(8px);
      background:rgba(255,255,255,.82); border:1px solid rgba(16,24,40,.06); border-radius:16px;
      box-shadow:0 10px 30px rgba(2,6,23,.08); padding:10px 14px;
    }
    .page-title{ font-weight:700; letter-spacing:.2px }

    .content{ padding:16px var(--edge) }

    /* Panel */
    .panel{ border:0; border-radius:18px; overflow:hidden; background:#fff; box-shadow:0 18px 40px rgba(2,6,23,.08) }
    .panel-head{ padding:18px 18px; border-bottom:1px solid #e8eef8;
      background-image:linear-gradient(135deg,rgba(37,99,235,.08),rgba(96,165,250,.06)) }
    .panel-body{ padding:18px }

    .toast-container{ position:fixed; top:20px; right:20px; z-index:1080 }

    @media(max-width:992px){
      .layout{ grid-template-columns:1fr }
      .sidebar{ position:fixed; inset:0 auto 0 0; width:var(--sidebar); transform:translateX(-100%); transition:.25s }
      .sidebar.show{ transform:translateX(0) }
    }
  </style>
</head>
<body>
<div class="layout">
  <!-- Sidebar -->
  <aside id="sb" class="sidebar">
    <a class="brand mb-3" href="dashboard.php">
      <span>Portal RT 005</span>
      <span class="brand-pill"><i class="bi bi-shield-lock"></i> Admin</span>
    </a>
    <nav class="nav-aside d-grid gap-1">
      <a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
      <a href="manage_users.php"><i class="bi bi-people"></i> Kelola Warga</a>
      <a class="active" href="manage_complaints.php"><i class="bi bi-exclamation-octagon"></i> Kelola Pengaduan</a>
      <a href="manage_letters.php"><i class="bi bi-file-earmark-text"></i> Kelola Surat</a>
      <a href="manage_forum.php"><i class="bi bi-chat-dots"></i> Kelola Forum</a>
      <hr class="border-secondary-subtle my-3">
      <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </nav>
  </aside>

  <!-- Main -->
  <main>
    <!-- Topbar -->
    <div class="topbar-wrap">
      <div class="topbar d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary d-lg-none me-1" onclick="document.getElementById('sb').classList.toggle('show')">
          <i class="bi bi-list"></i>
        </button>
        <a class="btn btn-outline-secondary" href="manage_complaints.php"><i class="bi bi-arrow-left"></i></a>
        <div class="page-title me-auto">Detail Pengaduan</div>
        <a class="btn btn-outline-primary" href="manage_complaints.php"><i class="bi bi-table me-1"></i>Daftar</a>
      </div>
    </div>

    <div class="content">
      <!-- Detail -->
      <div class="panel mb-3">
        <div class="panel-head d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div>
            <div class="fw-bold h5 mb-1 text-truncate">#<?= (int)$row['id'] ?> — <?= htmlspecialchars($row['title']) ?></div>
            <div class="text-secondary small">
              oleh <b><?= htmlspecialchars($row['name']) ?></b>
              • <?= htmlspecialchars($row['created_at'] ?? '') ?>
            </div>
          </div>
          <div>
            <span class="badge text-bg-<?= badge_class($row['status']) ?> px-3 py-2 text-capitalize">
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
              <div class="mb-2 fw-semibold">Info Warga</div>
              <div class="p-3 rounded border bg-white">
                <div class="mb-1"><i class="bi bi-person me-2"></i><?= htmlspecialchars($row['name']) ?></div>
                <div class="mb-1"><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($row['email']) ?></div>
                <div class="mb-1"><i class="bi bi-geo-alt me-2"></i>RT <?= (int)($row['rt'] ?? 5) ?> / RW <?= htmlspecialchars($row['rw'] ?? '-') ?></div>
                <div><i class="bi bi-house-door me-2"></i><?= htmlspecialchars($row['address'] ?? '-') ?></div>
              </div>

              <div class="mt-3 fw-semibold">Ubah Status</div>
              <div class="d-flex flex-wrap gap-2">
                <form method="post">
                  <input type="hidden" name="status" value="open">
                  <button name="set_status" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-bug"></i> Open
                  </button>
                </form>
                <form method="post">
                  <input type="hidden" name="status" value="in_progress">
                  <button name="set_status" class="btn btn-outline-warning btn-sm">
                    <i class="bi bi-hourglass-split"></i> Proses
                  </button>
                </form>
                <form method="post">
                  <input type="hidden" name="status" value="resolved">
                  <button name="set_status" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-check2-circle"></i> Selesai
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>

      <a class="btn btn-outline-secondary" href="manage_complaints.php">← Kembali</a>
    </div>
  </main>
</div>

<!-- Toast -->
<div class="toast-container">
  <?php if (!empty($info)): ?>
    <div id="toastInfo" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body"><i class="bi bi-check-circle me-2"></i><?= $info ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  <?php endif; ?>
  <?php if (!empty($err)): ?>
    <div id="toastErr" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body"><i class="bi bi-x-octagon me-2"></i><?= $err ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function(){
    var t1 = document.getElementById('toastInfo'); if (t1) new bootstrap.Toast(t1, { delay: 2800 }).show();
    var t2 = document.getElementById('toastErr'); if (t2) new bootstrap.Toast(t2, { delay: 3800 }).show();
  });
</script>
</body>
</html>
