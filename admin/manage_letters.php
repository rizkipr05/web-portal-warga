<?php require_once __DIR__ . '/auth.php'; ?>
<?php
$pdo = pdo();
$info = $err = '';

function ensure_letter_attachments_table(PDO $pdo) {
  $pdo->exec(
    "CREATE TABLE IF NOT EXISTS letter_attachments (
      id INT NOT NULL AUTO_INCREMENT,
      letter_id INT NOT NULL,
      label VARCHAR(20) NOT NULL,
      file_path VARCHAR(255) NOT NULL,
      created_at DATETIME NOT NULL,
      PRIMARY KEY (id),
      INDEX (letter_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
  );
}

function ensure_letter_notifications_table(PDO $pdo) {
  $pdo->exec(
    "CREATE TABLE IF NOT EXISTS letter_notifications (
      id INT NOT NULL AUTO_INCREMENT,
      letter_id INT NOT NULL,
      message TEXT NOT NULL,
      created_at DATETIME NOT NULL,
      created_by VARCHAR(100) NOT NULL,
      PRIMARY KEY (id),
      INDEX (letter_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
  );
}

ensure_letter_attachments_table($pdo);
ensure_letter_notifications_table($pdo);

/* ===== Actions ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_letter'])) {
  $id = (int)$_POST['id'];
  try {
    $pdo->beginTransaction();
    $pdo->prepare("DELETE FROM letter_attachments WHERE letter_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM letter_notifications WHERE letter_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM letters WHERE id=?")->execute([$id]);
    $pdo->commit();
    $info = "Pengajuan surat #$id dihapus.";
  } catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $err = "Gagal menghapus surat: " . $e->getMessage();
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notification'])) {
  $nid = (int)$_POST['notification_id'];
  $pdo->prepare("DELETE FROM letter_notifications WHERE id=?")->execute([$nid]);
  $info = "Notifikasi surat dihapus.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_status'])) {
  $id = (int)$_POST['id'];
  $status = $_POST['status'] ?? 'review';
  if (!in_array($status, ['review','approved','rejected','submitted'], true)) $status = 'review';
  $pdo->prepare("UPDATE letters SET status=? WHERE id=?")->execute([$status,$id]);
  $info = "Status surat #$id diubah ke $status.";

  if ($status === 'approved') {
    $by = $_SESSION['admin_name'] ?? $_SESSION['admin_username'] ?? 'Admin';
    $msg = trim($_POST['note'] ?? '');
    if ($msg === '') $msg = 'Silakan ambil surat di rumah RT.';
    $st = $pdo->prepare(
      "INSERT INTO letter_notifications (letter_id,message,created_at,created_by)
       VALUES (?,?,NOW(),?)"
    );
    $st->execute([$id, $msg, $by]);
  }
}

/* ===== Query + (opsional) filter status ===== */
$q = trim($_GET['q'] ?? '');
$f = trim($_GET['status'] ?? ''); // submitted/review/approved/rejected

$sql = "SELECT l.id,l.type,l.subject,l.status,l.created_at,l.file_path,u.name
        FROM letters l
        JOIN users u ON u.id=l.user_id
        WHERE 1";
$params = [];
if ($q !== '') {
  $sql .= " AND (l.subject LIKE ? OR u.name LIKE ?)";
  $params[] = "%$q%";
  $params[] = "%$q%";
}
if ($f !== '' && in_array($f, ['submitted','review','approved','rejected'], true)) {
  $sql .= " AND l.status = ?";
  $params[] = $f;
}
$sql .= " ORDER BY l.created_at DESC LIMIT 300";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$attachments_by_letter = [];
if ($rows) {
  $ids = array_map(fn($r) => (int)$r['id'], $rows);
  $in  = implode(',', array_fill(0, count($ids), '?'));
  $st = $pdo->prepare("SELECT letter_id,label,file_path FROM letter_attachments WHERE letter_id IN ($in)");
  $st->execute($ids);
  foreach ($st->fetchAll() as $a) {
    $attachments_by_letter[(int)$a['letter_id']][] = $a;
  }
}

$notifications_by_letter = [];
if ($rows) {
  $ids = array_map(fn($r) => (int)$r['id'], $rows);
  $in  = implode(',', array_fill(0, count($ids), '?'));
  $st = $pdo->prepare(
    "SELECT id, letter_id, message, created_at
     FROM letter_notifications
     WHERE letter_id IN ($in)
     ORDER BY created_at DESC, id DESC"
  );
  $st->execute($ids);
  foreach ($st->fetchAll() as $n) {
    $notifications_by_letter[(int)$n['letter_id']][] = $n;
  }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Kelola Pengajuan Surat — Admin</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{ --sidebar: 270px; --edge: clamp(16px, 3vw, 36px); --brand:#2563eb; --brand2:#60a5fa; }
    body{ background:#f6f8fc; color:#0f172a }
    .layout{ display:grid; grid-template-columns: var(--sidebar) 1fr; min-height:100vh }

    /* Sidebar */
    .sidebar{ position:sticky; top:0; height:100vh; padding:16px 14px;
      background:#0f172a; color:#cbd5e1; box-shadow:6px 0 24px rgba(2,6,23,.08) }
    .brand{ color:#fff; text-decoration:none; display:flex; align-items:center; gap:10px; font-weight:800 }
    .brand .brand-pill{ height:30px; padding:0 10px; border-radius:999px; font-size:.8rem;
      display:inline-flex; align-items:center; gap:6px; background:linear-gradient(135deg,var(--brand),var(--brand2)); color:#fff }
    .nav-aside a{ color:#b6c2db; text-decoration:none; display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:10px; transition:.15s }
    .nav-aside a:hover{ background:#111c36; color:#fff }
    .nav-aside a.active{ background:linear-gradient(135deg,rgba(37,99,235,.18),rgba(96,165,250,.14)); color:#fff; border:1px solid rgba(96,165,250,.25) }

    /* Topbar glass */
    .topbar-wrap{ padding:16px var(--edge); position:sticky; top:0; z-index:5 }
    .topbar{
      backdrop-filter:saturate(160%) blur(8px); -webkit-backdrop-filter:saturate(160%) blur(8px);
      background:rgba(255,255,255,.82); border:1px solid rgba(16,24,40,.06); border-radius:16px;
      box-shadow:0 10px 30px rgba(2,6,23,.08); padding:10px 14px;
    }
    .page-title{ font-weight:700; letter-spacing:.2px }
    .searchbox{ border-radius:12px; border:1px solid #e5e7eb; background:#fff }
    .searchbox:focus{ box-shadow:0 0 0 .2rem rgba(37,99,235,.15); border-color:#d6e2ff }
    .icon-btn{ display:inline-flex; align-items:center; justify-content:center; width:40px; height:40px; border-radius:12px; border:1px solid #e5e7eb; background:#fff }
    .icon-btn:hover{ border-color:#dbe7ff; background:#f8fbff }
    .avatar{ width:36px; height:36px; border-radius:999px; display:grid; place-items:center; background:linear-gradient(135deg,#dbeafe,#bfdbfe); color:#1e3a8a; font-weight:700; border:1px solid #dbe7ff }

    .content{ padding:16px var(--edge) }

    /* Chips filter */
    .chips{ display:flex; flex-wrap:wrap; gap:10px }
    .chip{ border-radius:999px; padding:6px 12px; font-size:.9rem; border:1px solid #e5e7eb; background:#fff; color:#334155; text-decoration:none; display:inline-flex; align-items:center; gap:6px }
    .chip:hover{ border-color:#dbe7ff }
    .chip.active{ background:linear-gradient(135deg, rgba(96,165,250,.16), rgba(37,99,235,.12)); border-color:rgba(96,165,250,.45); color:#0f172a }

    /* Status badges */
    .badge-review{background:#eef2ff;color:#3730a3}
    .badge-approved{background:#ecfdf5;color:#065f46}
    .badge-rejected{background:#fef2f2;color:#b91c1c}
    .badge-submitted{background:#e2e8f0;color:#111827}

    /* Card/Table */
    .card-grad{ border:0; border-radius:18px; background:#fff; box-shadow:0 18px 40px rgba(2,6,23,.08) }
    .table > :not(caption) > * > *{ padding:12px 14px }
    .table thead th{ font-weight:700; color:#334155; border-bottom:1px solid #e8eef8 }
    .table tbody td{ border-top:1px solid #eef2f7 }
    .action-stack{ display:flex; flex-direction:column; gap:10px }
    .action-row{ display:flex; flex-wrap:wrap; gap:6px }
    .action-note{ width:220px }
    .notif-list{ display:flex; flex-direction:column; gap:6px }
    .notif-item{ display:flex; align-items:center; gap:6px; padding:6px 8px; border:1px dashed #e5e7eb; border-radius:10px; background:#fff }
    .notif-text{ font-size:.85rem; color:#475569 }

    /* Toasts */
    .toast-container{ position:fixed; top:20px; right:20px; z-index:1080 }

    /* Mobile */
    @media (max-width:992px){
      .layout{ grid-template-columns:1fr }
      .sidebar{ position:fixed; inset:0 auto 0 0; width:var(--sidebar); transform:translateX(-100%); transition:.25s }
      .sidebar.show{ transform:translateX(0) }
      .hide-sm{ display:none !important }
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
      <a href="manage_complaints.php"><i class="bi bi-exclamation-octagon"></i> Kelola Pengaduan</a>
      <a class="active" href="manage_letters.php"><i class="bi bi-file-earmark-text"></i> Kelola Surat</a>
      <a href="manage_forum.php"><i class="bi bi-chat-dots"></i> Kelola Forum</a>
      <hr class="border-secondary-subtle my-3">
      <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </nav>
  </aside>

  <!-- Main -->
  <main>
    <!-- Topbar -->
    <div class="topbar-wrap">
      <div class="topbar">
        <div class="d-flex align-items-center gap-2 gap-md-3">
          <button class="btn btn-outline-secondary d-lg-none me-1" onclick="document.getElementById('sb').classList.toggle('show')">
            <i class="bi bi-list"></i>
          </button>

          <div class="page-title me-auto">Kelola Pengajuan Surat</div>

          <form class="d-none d-md-flex align-items-center" method="get">
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
              <input class="form-control searchbox border-start-0" name="q" placeholder="Cari perihal/nama warga…" value="<?= htmlspecialchars($q) ?>"/>
              <?php if ($f): ?><input type="hidden" name="status" value="<?= htmlspecialchars($f) ?>"><?php endif; ?>
            </div>
          </form>

          <button class="icon-btn" title="Notifikasi"><i class="bi bi-bell"></i></button>

          <div class="dropdown">
            <?php $name = $_SESSION['admin_name'] ?? 'Admin'; $initials = strtoupper(mb_substr($name,0,1)); ?>
            <button class="btn p-0 border-0" data-bs-toggle="dropdown" aria-expanded="false" title="Profil">
              <div class="avatar"><?= htmlspecialchars($initials) ?></div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
              <li class="dropdown-header">
                <div class="fw-semibold"><?= htmlspecialchars($name) ?></div>
                <div class="small text-secondary">Admin RT005</div>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
              <li><a class="dropdown-item" href="manage_users.php"><i class="bi bi-people me-2"></i>Kelola Warga</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="content">

      <!-- Status chips + search (mobile) -->
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div class="chips">
          <?php
            $base = 'manage_letters.php';
            $chip = function($val,$label,$icon) use ($base,$q,$f){
              $p=[]; if($q!=='') $p['q']=$q; if($val!=='') $p['status']=$val;
              $href=$base.($p?('?'.http_build_query($p)):'');
              $active = ($f===$val)||($val==='' && $f==='');
              return '<a class="chip '.($active?'active':'').'" href="'.htmlspecialchars($href).'"><i class="bi bi-'.$icon.'"></i> '.$label.'</a>';
            };
            echo $chip('','Semua','ui-checks-grid');
            echo $chip('submitted','Diajukan','inbox');
            echo $chip('review','Review','search');
            echo $chip('approved','Disetujui','check2-circle');
            echo $chip('rejected','Ditolak','x-octagon');
          ?>
        </div>

        <form class="d-flex d-md-none" method="get">
          <input class="form-control me-2" name="q" placeholder="Cari perihal/nama…" value="<?= htmlspecialchars($q) ?>">
          <?php if ($f): ?><input type="hidden" name="status" value="<?= htmlspecialchars($f) ?>"><?php endif; ?>
          <button class="btn btn-primary"><i class="bi bi-search"></i></button>
        </form>
      </div>

      <div class="card card-grad">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr>
                  <th>ID</th><th>Jenis</th><th>Perihal</th><th>Warga</th><th>Status</th><th>Waktu</th><th style="width:340px">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($rows as $r): ?>
                <tr>
                  <td><?= (int)$r['id'] ?></td>
                  <td><?= htmlspecialchars($r['type']) ?></td>
                  <td class="text-truncate" style="max-width:420px"><?= htmlspecialchars($r['subject']) ?></td>
                  <td><?= htmlspecialchars($r['name']) ?></td>
                  <td><span class="badge badge-<?= htmlspecialchars($r['status'] ?: 'submitted') ?>"><?= htmlspecialchars($r['status'] ?: 'submitted') ?></span></td>
                  <td><?= htmlspecialchars($r['created_at'] ?? '') ?></td>
                  <td>
                    <?php $atts = $attachments_by_letter[(int)$r['id']] ?? []; ?>
                    <?php $notes = $notifications_by_letter[(int)$r['id']] ?? []; ?>
                    <?php if($atts): ?>
                      <div class="action-row mb-1">
                      <?php foreach($atts as $a): ?>
                        <a class="btn btn-sm btn-outline-primary" target="_blank" href="../<?= htmlspecialchars($a['file_path']) ?>">
                          <i class="bi bi-paperclip"></i> <?= htmlspecialchars(strtoupper($a['label'])) ?>
                        </a>
                      <?php endforeach; ?>
                      </div>
                    <?php elseif(!empty($r['file_path'])): ?>
                      <a class="btn btn-sm btn-outline-primary mb-1" target="_blank" href="../<?= htmlspecialchars($r['file_path']) ?>">
                        <i class="bi bi-paperclip"></i> Lampiran
                      </a>
                    <?php endif; ?>
                    <div class="action-stack">
                      <?php if ($notes): ?>
                        <div class="notif-list">
                          <?php foreach ($notes as $n): ?>
                            <div class="notif-item">
                              <div class="notif-text text-truncate" style="max-width:220px">
                                <?= htmlspecialchars($n['message']) ?>
                              </div>
                              <form method="post" onsubmit="return confirm('Hapus notifikasi ini?');">
                                <input type="hidden" name="notification_id" value="<?= (int)$n['id'] ?>">
                                <button name="delete_notification" class="btn btn-sm btn-outline-dark">
                                  <i class="bi bi-trash"></i>
                                </button>
                              </form>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>
                      <div class="action-row">
                        <form class="d-inline" method="post">
                          <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                          <input type="hidden" name="status" value="review">
                          <button name="set_status" class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i> Review</button>
                        </form>
                        <form class="d-inline" method="post">
                          <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                          <input type="hidden" name="status" value="rejected">
                          <button name="set_status" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-octagon"></i> Tolak</button>
                        </form>
                        <form class="d-inline" method="post" onsubmit="return confirm('Hapus pengajuan surat ini?');">
                          <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                          <button name="delete_letter" class="btn btn-sm btn-outline-dark"><i class="bi bi-trash"></i> Hapus</button>
                        </form>
                      </div>
                      <form class="d-inline" method="post">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <input type="hidden" name="status" value="approved">
                        <div class="action-row">
                          <input class="form-control form-control-sm action-note" name="note" placeholder="Pesan: surat siap diambil…">
                          <button name="set_status" class="btn btn-sm btn-outline-success"><i class="bi bi-check2-circle"></i> Setujui</button>
                        </div>
                      </form>
                    </div>
                  </td>
                </tr>
                <?php endforeach; if(!$rows): ?>
                <tr><td colspan="7" class="text-center text-secondary">Tidak ada data.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div><!-- /content -->
  </main>
</div>

<!-- Toast (alert modern) -->
<div class="toast-container">
  <?php if ($info): ?>
    <div id="toastInfo" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($info) ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  <?php endif; ?>
  <?php if ($err): ?>
    <div id="toastErr" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body"><i class="bi bi-x-octagon me-2"></i><?= htmlspecialchars($err) ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // tampilkan toast otomatis bila ada pesan
  document.addEventListener('DOMContentLoaded', function(){
    var t1 = document.getElementById('toastInfo');
    if (t1) new bootstrap.Toast(t1, { delay: 3000 }).show();
    var t2 = document.getElementById('toastErr');
    if (t2) new bootstrap.Toast(t2, { delay: 4000 }).show();
  });
</script>
</body>
</html>
