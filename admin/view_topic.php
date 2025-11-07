<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';
$pdo = pdo();

/* ---------- helper ---------- */
if (!function_exists('initials')) {
  function initials($name) {
    $name = trim($name ?: 'Warga');
    $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);
    if (count($parts) >= 2) {
      return mb_strtoupper(mb_substr($parts[0],0,1) . mb_substr(end($parts),0,1));
    }
    return mb_strtoupper(mb_substr($name,0,1));
  }
}

/* ---------- flash & local alerts ---------- */
$info = $err = '';
if (!empty($_SESSION['flash_info'])) { $info = $_SESSION['flash_info']; unset($_SESSION['flash_info']); }
if (!empty($_SESSION['flash_err']))  { $err  = $_SESSION['flash_err'];  unset($_SESSION['flash_err']); }

/* ---------- resolve admin fallback id ---------- */
$ADMIN_ID = defined('ADMIN_USER_ID_FALLBACK') ? (int)ADMIN_USER_ID_FALLBACK : 0;

/* ---------- topic id ---------- */
$topic_id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($topic_id <= 0) {
  http_response_code(400);
  exit('Topic ID tidak valid.');
}

/* ---------- handle reply (POST) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {
  $content = trim($_POST['content'] ?? '');
  if ($content === '') {
    $_SESSION['flash_err'] = 'Isi balasan tidak boleh kosong.';
  } else {
    try {
      if ($ADMIN_ID <= 0) {
        throw new Exception('ADMIN_USER_ID_FALLBACK belum diset di config.php.');
      }

      // pastikan topik ada
      $chk = $pdo->prepare("SELECT 1 FROM forum_topics WHERE id=? LIMIT 1");
      $chk->execute([$topic_id]);
      if (!$chk->fetchColumn()) {
        throw new Exception('Topik tidak ditemukan.');
      }

      // simpan balasan (user_id admin, bukan NULL)
      $ins = $pdo->prepare("INSERT INTO forum_posts (topic_id, user_id, content) VALUES (?,?,?)");
      $ins->execute([$topic_id, $ADMIN_ID, $content]);

      $_SESSION['flash_info'] = 'Balasan admin terkirim.';
    } catch (Throwable $e) {
      $_SESSION['flash_err'] = 'Gagal mengirim balasan: '.$e->getMessage();
    }
  }
  header('Location: view_topic.php?id=' . $topic_id); // PRG
  exit;
}

/* ---------- fetch topic & posts ---------- */
$tq = $pdo->prepare("SELECT t.id, t.title, t.created_at, u.name AS author_name
                     FROM forum_topics t
                     JOIN users u ON u.id = t.user_id
                     WHERE t.id = ?");
$tq->execute([$topic_id]);
$topic = $tq->fetch();

if (!$topic) {
  http_response_code(404);
  exit('Topik tidak ditemukan.');
}

$pq = $pdo->prepare("SELECT p.id, p.content, p.created_at, p.user_id,
                            u.name AS user_name, u.avatar_path AS avatar_path
                     FROM forum_posts p
                     LEFT JOIN users u ON u.id = p.user_id
                     WHERE p.topic_id = ?
                     ORDER BY p.created_at ASC");
$pq->execute([$topic_id]);
$posts = $pq->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Lihat Topik #<?= (int)$topic['id'] ?> — Admin Forum</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{ --edge:clamp(16px,3vw,36px); --brand:#2563eb; --brand2:#60a5fa; }
    body{ background:#f6f8fc; color:#0f172a; font-family:system-ui,Arial,sans-serif }
    .edge{ padding-inline:var(--edge) }
    .topbar-wrap{ padding:16px var(--edge); position:sticky; top:0; z-index:5 }
    .topbar{
      backdrop-filter:saturate(160%) blur(8px); -webkit-backdrop-filter:saturate(160%) blur(8px);
      background:rgba(255,255,255,.82); border:1px solid rgba(16,24,40,.06); border-radius:16px;
      box-shadow:0 10px 30px rgba(2,6,23,.08); padding:10px 14px;
    }
    .page-title{ font-weight:700; letter-spacing:.2px }
    .panel{ border:0; border-radius:18px; background:#fff; box-shadow:0 18px 40px rgba(2,6,23,.08) }
    .panel-head{ padding:18px 18px; border-bottom:1px solid #e8eef8; background-image:linear-gradient(135deg,rgba(37,99,235,.08),rgba(96,165,250,.06)) }
    .panel-body{ padding:18px }
    .bubble{ background:#fff; border-radius:14px; padding:12px 16px; box-shadow:0 4px 14px rgba(0,0,0,.05) }
    .bubble.me{ background:#dbeafe }
    .msg{ display:flex; gap:12px }
    .msg.me{ flex-direction: row-reverse }
    .avatar{ width:40px; height:40px; border-radius:999px; object-fit:cover; box-shadow:0 6px 16px rgba(37,99,235,.16) }
    .avatar-fallback{
      width:40px; height:40px; border-radius:999px; display:grid; place-items:center;
      background:linear-gradient(135deg,#60a5fa,#2563eb); color:#fff; font-weight:800;
      box-shadow:0 6px 16px rgba(37,99,235,.16)
    }
    .form-control{ border-radius:12px; border:1px solid #e5e7eb; padding:10px 12px }
    .form-control:focus{ border-color:#d6e2ff; box-shadow:0 0 0 .2rem rgba(37,99,235,.15) }
    .btn-primary{ background:linear-gradient(135deg,var(--brand),var(--brand2)); border:0; font-weight:700 }
    .btn-primary:hover{ filter:brightness(.96) }
    .toast-container{ position:fixed; top:20px; right:20px; z-index:1080 }
  </style>
</head>
<body>

<!-- Topbar -->
<div class="topbar-wrap">
  <div class="topbar d-flex align-items-center gap-2">
    <a class="btn btn-outline-secondary" href="manage_forum.php">
      <i class="bi bi-arrow-left"></i>
    </a>
    <div class="page-title me-auto">Lihat Topik</div>
    <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
  </div>
</div>

<div class="edge">
  <!-- Info Topik -->
  <div class="panel mb-3">
    <div class="panel-head">
      <div class="fw-bold h5 mb-0"><?= htmlspecialchars($topic['title']) ?></div>
      <div class="text-secondary small">oleh <b><?= htmlspecialchars($topic['author_name']) ?></b> • <?= htmlspecialchars($topic['created_at']) ?></div>
    </div>
    <div class="panel-body">
      <!-- Daftar Balasan -->
      <div class="d-flex flex-column gap-3">
        <?php if(!$posts): ?>
          <div class="text-secondary">Belum ada balasan pada topik ini.</div>
        <?php else: foreach($posts as $p):
          $isAdminReply = ($ADMIN_ID > 0 && (int)$p['user_id'] === $ADMIN_ID);
          $displayName  = $isAdminReply ? 'Admin RT005' : ($p['user_name'] ?? 'Warga');
          $avatarPath   = $isAdminReply ? null : ($p['avatar_path'] ?? null);
        ?>
          <div class="msg <?= $isAdminReply ? 'me' : '' ?>">
            <?php if ($avatarPath && is_file(__DIR__ . '/../' . ltrim($avatarPath,'/'))): ?>
              <img src="<?= url('/'.ltrim($avatarPath,'/')) ?>" class="avatar" alt="avatar">
            <?php else: ?>
              <div class="avatar-fallback"><?= htmlspecialchars(initials($displayName)) ?></div>
            <?php endif; ?>
            <div class="bubble <?= $isAdminReply ? 'me' : '' ?> flex-grow-1">
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="fw-semibold"><?= htmlspecialchars($displayName) ?></span>
                <?php if ($isAdminReply): ?><span class="badge text-bg-primary">Admin</span><?php endif; ?>
              </div>
              <div><?= nl2br(htmlspecialchars($p['content'])) ?></div>
              <small class="text-secondary"><?= htmlspecialchars($p['created_at']) ?></small>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <!-- Form Balasan Admin -->
  <div class="panel">
    <div class="panel-head d-flex align-items-center justify-content-between">
      <div class="fw-semibold">Balas sebagai Admin</div>
    </div>
    <div class="panel-body">
      <?php if($err): ?><div class="alert alert-danger"><i class="bi bi-x-octagon me-2"></i><?= htmlspecialchars($err) ?></div><?php endif; ?>
      <?php if($info): ?><div class="alert alert-success"><i class="bi bi-check2-circle me-2"></i><?= htmlspecialchars($info) ?></div><?php endif; ?>

      <form method="post" class="d-grid gap-2">
        <input type="hidden" name="id" value="<?= (int)$topic_id ?>">
        <textarea class="form-control" name="content" rows="3" placeholder="Ketik balasan untuk warga di sini..."></textarea>
        <button class="btn btn-primary" name="reply" value="1"><i class="bi bi-send me-1"></i>Kirim Balasan</button>
      </form>
      <?php if ($ADMIN_ID <= 0): ?>
        <div class="form-text mt-2">
          Set <code>ADMIN_USER_ID_FALLBACK</code> di <code>config.php</code> ke ID user Admin (tabel <code>users</code>), agar balasan tidak gagal karena constraint.
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Toast (opsional) -->
<div class="toast-container">
  <?php if ($info): ?>
    <div id="toastInfo" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($info) ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  <?php endif; ?>
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
    var t1 = document.getElementById('toastInfo'); if (t1) new bootstrap.Toast(t1, { delay: 2200 }).show();
    var t2 = document.getElementById('toastErr'); if (t2) new bootstrap.Toast(t2, { delay: 4200 }).show();
  });
</script>
</body>
</html>
