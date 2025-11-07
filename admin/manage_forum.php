<?php require_once __DIR__ . '/auth.php'; ?>
<?php
$pdo = pdo();
$info = $err = '';

// Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_topic'])) {
  $id = (int)$_POST['id'];
  try {
    // Hapus posts dahulu (kalau FK CASCADE sudah di DB, baris pertama opsional)
    $pdo->prepare("DELETE FROM forum_posts WHERE topic_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM forum_topics WHERE id=?")->execute([$id]);
    $info = "Topik #$id dihapus beserta semua posting.";
  } catch (Throwable $e) {
    $err = "Gagal menghapus topik: " . $e->getMessage();
  }
}

// Query
$q = trim($_GET['q'] ?? '');
$sql = "SELECT t.id,t.title,t.created_at,u.name,
        (SELECT COUNT(*) FROM forum_posts p WHERE p.topic_id=t.id) posts
        FROM forum_topics t
        JOIN users u ON u.id=t.user_id
        WHERE 1";
$params = [];
if ($q !== '') {
  $sql .= " AND (t.title LIKE ? OR u.name LIKE ?)";
  $params = ["%$q%","%$q%"];
}
$sql .= " ORDER BY t.created_at DESC LIMIT 300";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Kelola Forum — Admin</title>
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
    .icon-btn{ display:inline-flex; align-items:center; justify-content:center; width:40px; height:40px;
      border-radius:12px; border:1px solid #e5e7eb; background:#fff }
    .icon-btn:hover{ border-color:#dbe7ff; background:#f8fbff }
    .avatar{ width:36px; height:36px; border-radius:999px; display:grid; place-items:center;
      background:linear-gradient(135deg,#dbeafe,#bfdbfe); color:#1e3a8a; font-weight:700; border:1px solid #dbe7ff }

    .content{ padding:16px var(--edge) }

    /* Card/Table */
    .card-grad{ border:0; border-radius:18px; background:#fff; box-shadow:0 18px 40px rgba(2,6,23,.08) }
    .table > :not(caption) > * > *{ padding:12px 14px }
    .table thead th{ font-weight:700; color:#334155; border-bottom:1px solid #e8eef8 }
    .table tbody td{ border-top:1px solid #eef2f7 }

    /* Toast */
    .toast-container{ position:fixed; top:20px; right:20px; z-index:1080 }

    @media (max-width:992px){
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
      <a href="manage_complaints.php"><i class="bi bi-exclamation-octagon"></i> Kelola Pengaduan</a>
      <a href="manage_letters.php"><i class="bi bi-file-earmark-text"></i> Kelola Surat</a>
      <a class="active" href="manage_forum.php"><i class="bi bi-chat-dots"></i> Kelola Forum</a>
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

          <div class="page-title me-auto">Kelola Forum</div>

          <form class="d-none d-md-flex align-items-center" method="get">
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
              <input class="form-control searchbox border-start-0" name="q" placeholder="Cari judul topik/nama warga…" value="<?= htmlspecialchars($q) ?>"/>
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
      <!-- Search mobile -->
      <form class="d-flex d-md-none mb-3" method="get">
        <input class="form-control me-2" name="q" placeholder="Cari topik/nama…" value="<?= htmlspecialchars($q) ?>">
        <button class="btn btn-primary"><i class="bi bi-search"></i></button>
      </form>

      <div class="card card-grad">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr>
                  <th>ID</th><th>Judul</th><th>Pembuat</th><th>Balasan</th><th>Waktu</th><th style="width:180px">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($rows as $r): ?>
                <tr>
                  <td><?= (int)$r['id'] ?></td>
                  <td class="text-truncate" style="max-width:460px"><?= htmlspecialchars($r['title']) ?></td>
                  <td><?= htmlspecialchars($r['name']) ?></td>
                  <td><span class="badge text-bg-light"><?= (int)$r['posts'] ?></span></td>
                  <td><?= htmlspecialchars($r['created_at'] ?? '') ?></td>
                  <td>
                    <a class="btn btn-sm btn-outline-primary" href="view_topic.php?id=<?= (int)$r['id'] ?>">
                      <i class="bi bi-eye"></i> Lihat
                    </a>
                    <form class="d-inline" method="post" onsubmit="return confirm('Hapus topik ini beserta semua balasan?')">
                      <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                      <button name="delete_topic" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash"></i> Hapus
                      </button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; if(!$rows): ?>
                <tr><td colspan="6" class="text-center text-secondary">Tidak ada data.</td></tr>
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
  document.addEventListener('DOMContentLoaded', function(){
    var t1 = document.getElementById('toastInfo');
    if (t1) new bootstrap.Toast(t1, { delay: 3000 }).show();
    var t2 = document.getElementById('toastErr');
    if (t2) new bootstrap.Toast(t2, { delay: 4000 }).show();
  });
</script>
</body>
</html>
