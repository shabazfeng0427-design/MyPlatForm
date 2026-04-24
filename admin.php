<?php
// ============================================================
//  admin.php — Simple Admin Dashboard
//  View messages, analytics, manage settings.
//  Default login: admin / Admin@1234  (CHANGE IMMEDIATELY!)
// ============================================================
require_once 'config.php';
session_name(ADMIN_SESSION_NAME);
session_start();

$action = $_GET['action'] ?? 'dashboard';

// ── AUTH ─────────────────────────────────────────────────────
function isLoggedIn(): bool {
    return !empty($_SESSION['admin_id'])
        && !empty($_SESSION['admin_expires'])
        && $_SESSION['admin_expires'] > time();
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: admin.php?action=login');
        exit;
    }
    // Extend session on activity
    $_SESSION['admin_expires'] = time() + ADMIN_SESSION_LIFE;
}

// ── HANDLE LOGIN POST ─────────────────────────────────────────
$loginError = '';
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $user     = DB::row('SELECT * FROM admin_users WHERE username = ?', [$username]);

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id']      = $user['id'];
        $_SESSION['admin_name']    = $user['username'];
        $_SESSION['admin_expires'] = time() + ADMIN_SESSION_LIFE;
        DB::exec('UPDATE admin_users SET last_login = NOW() WHERE id = ?', [$user['id']]);
        header('Location: admin.php');
        exit;
    }
    $loginError = 'Invalid username or password.';
}

// ── LOGOUT ───────────────────────────────────────────────────
if ($action === 'logout') {
    session_destroy();
    header('Location: admin.php?action=login');
    exit;
}

// ── MARK MESSAGE READ ─────────────────────────────────────────
if ($action === 'mark-read' && isLoggedIn()) {
    $id = (int)($_GET['id'] ?? 0);
    if ($id) DB::exec('UPDATE contact_messages SET is_read = 1 WHERE id = ?', [$id]);
    header('Location: admin.php?action=messages');
    exit;
}

// ── DELETE MESSAGE ────────────────────────────────────────────
if ($action === 'delete-message' && isLoggedIn()) {
    $id = (int)($_GET['id'] ?? 0);
    if ($id) DB::exec('DELETE FROM contact_messages WHERE id = ?', [$id]);
    header('Location: admin.php?action=messages');
    exit;
}

// ── SAVE SETTINGS ─────────────────────────────────────────────
if ($action === 'save-settings' && isLoggedIn() && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['site_name','site_tagline','site_email','site_location','site_available',
               'site_github','site_linkedin','site_twitter','site_dribbble',
               'hero_subtitle','about_bio_1','about_bio_2','about_bio_3'];
    foreach ($fields as $key) {
        $val = trim($_POST[$key] ?? '');
        DB::exec('INSERT INTO settings (`key`, value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=?', [$key,$val,$val]);
    }
    $savedMsg = 'Settings saved successfully.';
    $action = 'settings';
}

// ── DATA HELPERS ──────────────────────────────────────────────
function paginate(int $total, int $perPage, int $page): array {
    return [
        'total'   => $total,
        'pages'   => max(1, (int)ceil($total / $perPage)),
        'current' => max(1, $page),
        'offset'  => ($page - 1) * $perPage,
    ];
}

// ── LOGIN PAGE ────────────────────────────────────────────────
if ($action === 'login'):
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark" data-color="violet">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Admin Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600&family=JetBrains+Mono&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="style.css"/>
  <style>
    .login-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--bg);}
    .login-box{background:var(--bg2);border:1px solid var(--border2);border-radius:var(--radius-lg);padding:2.5rem;width:100%;max-width:400px;}
    .login-title{font-family:'Outfit',sans-serif;font-size:24px;font-weight:600;color:var(--text);margin-bottom:0.5rem;}
    .login-sub{font-size:13px;color:var(--text3);margin-bottom:2rem;font-family:'JetBrains Mono',monospace;}
    .login-err{background:rgba(244,63,94,0.1);border:1px solid rgba(244,63,94,0.3);border-radius:6px;padding:10px 14px;font-size:13px;color:#F43F5E;margin-bottom:1rem;}
    .login-btn{width:100%;background:var(--accent);color:#fff;border:none;border-radius:24px;padding:13px;font-size:15px;font-family:'Outfit',sans-serif;cursor:pointer;margin-top:0.5rem;transition:background .2s;}
    .login-btn:hover{background:var(--accent2);}
    [data-theme="dark"]{--bg:#0A0A0F;--bg2:#111118;--text:#F0EFF8;--text3:#6B6880;--border2:rgba(255,255,255,0.13);--accent:#8B5CF6;--accent2:#6D28D9;--radius-lg:20px;}
  </style>
</head>
<body>
<div class="login-wrap">
  <div class="login-box">
    <div class="login-title">Admin Panel</div>
    <div class="login-sub">portfolio_db management</div>
    <?php if ($loginError): ?><div class="login-err"><?= e($loginError) ?></div><?php endif; ?>
    <form method="POST" action="admin.php?action=login">
      <div class="fg"><label for="username">Username</label><input type="text" id="username" name="username" required autocomplete="username"/></div>
      <div class="fg"><label for="password">Password</label><input type="password" id="password" name="password" required autocomplete="current-password"/></div>
      <button type="submit" class="login-btn">Sign In →</button>
    </form>
    <p style="margin-top:1.5rem;font-size:12px;color:var(--text3);font-family:'JetBrains Mono',monospace;">Default: admin / Admin@1234 — change immediately!</p>
  </div>
</div>
</body>
</html>
<?php exit; endif;

// ── All admin pages require login ─────────────────────────────
requireLogin();

// ── Load page data ────────────────────────────────────────────
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$unreadCount = DB::row('SELECT COUNT(*) AS n FROM contact_messages WHERE is_read = 0')['n'] ?? 0;
$totalMsgs   = DB::row('SELECT COUNT(*) AS n FROM contact_messages')['n'] ?? 0;
$totalViews  = DB::row('SELECT COUNT(*) AS n FROM page_views')['n'] ?? 0;
$todayViews  = DB::row('SELECT COUNT(*) AS n FROM page_views WHERE DATE(created_at) = CURDATE()')['n'] ?? 0;

// Messages page
if ($action === 'messages') {
    $pg   = paginate($totalMsgs, $perPage, $page);
    $msgs = DB::query(
        'SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT ? OFFSET ?',
        [$perPage, $pg['offset']]
    );
}

// Settings page
if ($action === 'settings') {
    $settings = DB::query('SELECT `key`, value FROM settings ORDER BY `key`');
    $cfg = [];
    foreach ($settings as $s) $cfg[$s['key']] = $s['value'];
}

// Analytics
if ($action === 'analytics') {
    $viewsByDay = DB::query(
        'SELECT DATE(created_at) AS day, COUNT(*) AS views
         FROM page_views
         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
         GROUP BY DATE(created_at) ORDER BY day DESC'
    );
}

// Header helper
function adminHeader(string $title, int $unread): void { ?>
<!DOCTYPE html>
<html lang="en" data-theme="dark" data-color="violet">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Admin — <?= e($title) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=JetBrains+Mono&display=swap" rel="stylesheet"/>
  <style>
    :root{--bg:#0A0A0F;--bg2:#111118;--bg3:#16161F;--bg4:#1C1C28;--text:#F0EFF8;--text2:#A09DB8;--text3:#6B6880;--border:rgba(255,255,255,0.07);--border2:rgba(255,255,255,0.13);--accent:#8B5CF6;--accent2:#6D28D9;--green:#22C55E;--red:#F43F5E;}
    *{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;}
    a{text-decoration:none;color:inherit;}
    /* Sidebar */
    .sidebar{width:240px;flex-shrink:0;background:var(--bg2);border-right:1px solid var(--border);padding:1.5rem;display:flex;flex-direction:column;gap:2rem;}
    .sb-logo{font-size:22px;font-weight:700;color:var(--accent);}
    .sb-nav{display:flex;flex-direction:column;gap:4px;}
    .sb-link{padding:9px 14px;border-radius:8px;font-size:14px;color:var(--text2);display:flex;align-items:center;gap:10px;transition:all .2s;}
    .sb-link:hover,.sb-link.active{background:rgba(139,92,246,.12);color:var(--accent);}
    .sb-badge{background:var(--red);color:#fff;border-radius:10px;padding:2px 7px;font-size:11px;font-family:'JetBrains Mono',monospace;}
    .sb-footer{margin-top:auto;}
    .sb-user{font-size:12px;color:var(--text3);margin-bottom:8px;}
    .sb-logout{font-size:13px;color:var(--red);display:block;padding:8px 14px;border-radius:8px;transition:background .2s;}
    .sb-logout:hover{background:rgba(244,63,94,.1);}
    /* Main */
    .main{flex:1;padding:2rem;overflow-x:auto;}
    .main-title{font-size:24px;font-weight:700;margin-bottom:0.5rem;}
    .main-sub{font-size:13px;color:var(--text3);font-family:'JetBrains Mono',monospace;margin-bottom:2rem;}
    /* Stats row */
    .stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:2rem;}
    .stat-box{background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:1.25rem;}
    .stat-num{font-size:32px;font-weight:700;color:var(--accent);}
    .stat-lbl{font-size:12px;color:var(--text3);font-family:'JetBrains Mono',monospace;margin-top:4px;}
    /* Table */
    .tbl{width:100%;border-collapse:collapse;font-size:14px;}
    .tbl th{text-align:left;padding:10px 14px;border-bottom:1px solid var(--border);font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:var(--text3);font-family:'JetBrains Mono',monospace;}
    .tbl td{padding:12px 14px;border-bottom:1px solid var(--border);color:var(--text2);}
    .tbl tr:hover td{background:var(--bg3);}
    .badge-unread{background:rgba(139,92,246,.15);color:var(--accent);border:1px solid rgba(139,92,246,.3);border-radius:4px;padding:2px 8px;font-size:11px;}
    .badge-read{background:var(--bg3);color:var(--text3);border:1px solid var(--border);border-radius:4px;padding:2px 8px;font-size:11px;}
    /* Msg detail */
    .msg-quote{background:var(--bg3);border-left:3px solid var(--accent);padding:12px 16px;border-radius:0 8px 8px 0;font-size:14px;color:var(--text2);line-height:1.7;max-width:600px;}
    /* Actions */
    .act-btn{padding:5px 12px;border-radius:6px;font-size:12px;border:none;cursor:pointer;font-family:'Outfit',sans-serif;transition:all .2s;}
    .act-primary{background:var(--accent);color:#fff;}.act-primary:hover{background:var(--accent2);}
    .act-danger{background:rgba(244,63,94,.12);color:var(--red);border:1px solid rgba(244,63,94,.3);}.act-danger:hover{background:rgba(244,63,94,.2);}
    .act-ghost{background:var(--bg4);color:var(--text2);border:1px solid var(--border2);}.act-ghost:hover{background:var(--bg3);}
    /* Pagination */
    .pagination{display:flex;gap:8px;margin-top:1.5rem;}
    .pg-btn{padding:7px 14px;border-radius:6px;background:var(--bg3);border:1px solid var(--border2);color:var(--text2);font-size:13px;cursor:pointer;text-decoration:none;}
    .pg-btn.active{background:var(--accent);border-color:var(--accent);color:#fff;}
    /* Form fields */
    .adm-fg{display:flex;flex-direction:column;gap:6px;margin-bottom:1.25rem;}
    .adm-fg label{font-size:12px;font-family:'JetBrains Mono',monospace;text-transform:uppercase;letter-spacing:1.5px;color:var(--text3);}
    .adm-fg input,.adm-fg textarea,.adm-fg select{background:var(--bg3);border:1px solid var(--border2);border-radius:8px;padding:10px 14px;color:var(--text);font-family:'Outfit',sans-serif;font-size:14px;outline:none;width:100%;transition:border-color .2s;}
    .adm-fg input:focus,.adm-fg textarea:focus{border-color:var(--accent);}
    .save-btn{background:var(--accent);color:#fff;border:none;border-radius:24px;padding:12px 28px;font-size:14px;font-family:'Outfit',sans-serif;cursor:pointer;transition:background .2s;}
    .save-btn:hover{background:var(--accent2);}
    .alert-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);border-radius:8px;padding:12px 16px;font-size:14px;color:var(--green);margin-bottom:1.5rem;}
    .section-divider{height:1px;background:var(--border);margin:2rem 0;}
    .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;}
    @media(max-width:768px){body{flex-direction:column;}.sidebar{width:100%;flex-direction:row;flex-wrap:wrap;}.main{padding:1rem;}.grid-2{grid-template-columns:1fr;}}
  </style>
</head>
<body>
<aside class="sidebar">
  <div class="sb-logo">⚡ Admin</div>
  <nav class="sb-nav">
    <a href="admin.php" class="sb-link <?= ($title==='Dashboard')?'active':'' ?>">📊 Dashboard</a>
    <a href="admin.php?action=messages" class="sb-link <?= ($title==='Messages')?'active':'' ?>">
      ✉ Messages
      <?php if ($unread > 0): ?><span class="sb-badge"><?= $unread ?></span><?php endif; ?>
    </a>
    <a href="admin.php?action=analytics" class="sb-link <?= ($title==='Analytics')?'active':'' ?>">📈 Analytics</a>
    <a href="admin.php?action=settings"  class="sb-link <?= ($title==='Settings')?'active':'' ?>">⚙ Settings</a>
    <a href="index.php" class="sb-link" target="_blank">🌐 View Site ↗</a>
  </nav>
  <div class="sb-footer">
    <div class="sb-user">Logged in as <?= e($_SESSION['admin_name'] ?? 'admin') ?></div>
    <a href="admin.php?action=logout" class="sb-logout">→ Logout</a>
  </div>
</aside>
<main class="main">
<?php }

function adminFooter(): void { echo '</main></body></html>'; }
?>

<?php adminHeader(match($action) {
    'messages'  => 'Messages',
    'analytics' => 'Analytics',
    'settings'  => 'Settings',
    default     => 'Dashboard',
}, (int)$unreadCount); ?>

<?php if ($action === 'dashboard'): ?>
<!-- ── DASHBOARD ───────────────────────────────── -->
<div class="main-title">Dashboard</div>
<div class="main-sub">portfolio_db overview</div>
<div class="stats-row">
  <div class="stat-box"><div class="stat-num"><?= $unreadCount ?></div><div class="stat-lbl">Unread Messages</div></div>
  <div class="stat-box"><div class="stat-num"><?= $totalMsgs ?></div><div class="stat-lbl">Total Messages</div></div>
  <div class="stat-box"><div class="stat-num"><?= $totalViews ?></div><div class="stat-lbl">Total Page Views</div></div>
  <div class="stat-box"><div class="stat-num"><?= $todayViews ?></div><div class="stat-lbl">Views Today</div></div>
  <div class="stat-box"><div class="stat-num"><?= DB::row('SELECT COUNT(*) AS n FROM projects WHERE active=1')['n'] ?></div><div class="stat-lbl">Active Projects</div></div>
  <div class="stat-box"><div class="stat-num"><?= DB::row('SELECT COUNT(*) AS n FROM testimonials WHERE active=1')['n'] ?></div><div class="stat-lbl">Testimonials</div></div>
</div>

<h3 style="margin-bottom:1rem;font-size:16px;">Recent Messages</h3>
<?php $recent = DB::query('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5'); ?>
<table class="tbl">
  <thead><tr><th>From</th><th>Email</th><th>Message</th><th>Date</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($recent as $m): ?>
  <tr>
    <td><?= e($m['name']) ?></td>
    <td><a href="mailto:<?= e($m['email']) ?>" style="color:var(--accent)"><?= e($m['email']) ?></a></td>
    <td><?= e(mb_substr($m['message'], 0, 60)) ?>…</td>
    <td style="font-family:'JetBrains Mono',monospace;font-size:12px"><?= date('M j, Y', strtotime($m['created_at'])) ?></td>
    <td><?php if (!$m['is_read']): ?><span class="badge-unread">New</span><?php else: ?><span class="badge-read">Read</span><?php endif; ?></td>
    <td><a href="admin.php?action=mark-read&id=<?= $m['id'] ?>" class="act-btn act-primary">Mark Read</a></td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$recent): ?><tr><td colspan="6" style="text-align:center;color:var(--text3);padding:2rem">No messages yet.</td></tr><?php endif; ?>
  </tbody>
</table>
<div style="margin-top:1rem"><a href="admin.php?action=messages" class="act-btn act-ghost">View all messages →</a></div>

<?php elseif ($action === 'messages'): ?>
<!-- ── MESSAGES ────────────────────────────────── -->
<div class="main-title">Messages</div>
<div class="main-sub"><?= $totalMsgs ?> total · <?= $unreadCount ?> unread</div>
<table class="tbl">
  <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Budget</th><th>Types</th><th>Message</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($msgs as $m): ?>
  <tr>
    <td style="font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--text3)"><?= $m['id'] ?></td>
    <td style="font-weight:600"><?= e($m['name']) ?></td>
    <td><a href="mailto:<?= e($m['email']) ?>" style="color:var(--accent)"><?= e($m['email']) ?></a></td>
    <td style="font-size:13px;color:var(--text3)"><?= e($m['budget'] ?: '—') ?></td>
    <td style="font-size:12px;color:var(--text3)"><?= e($m['project_types'] ?: '—') ?></td>
    <td>
      <div class="msg-quote"><?= e(mb_substr($m['message'], 0, 100)) ?><?= mb_strlen($m['message']) > 100 ? '…' : '' ?></div>
    </td>
    <td style="font-family:'JetBrains Mono',monospace;font-size:12px;white-space:nowrap"><?= date('M j, Y H:i', strtotime($m['created_at'])) ?></td>
    <td><?php if (!$m['is_read']): ?><span class="badge-unread">New</span><?php else: ?><span class="badge-read">Read</span><?php endif; ?></td>
    <td style="white-space:nowrap;display:flex;gap:6px;flex-wrap:wrap">
      <?php if (!$m['is_read']): ?>
      <a href="admin.php?action=mark-read&id=<?= $m['id'] ?>" class="act-btn act-primary">Read</a>
      <?php endif; ?>
      <a href="mailto:<?= e($m['email']) ?>?subject=Re: Your message&body=Hi <?= urlencode($m['name']) ?>," class="act-btn act-ghost">Reply</a>
      <a href="admin.php?action=delete-message&id=<?= $m['id'] ?>" class="act-btn act-danger" onclick="return confirm('Delete this message?')">Del</a>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$msgs): ?><tr><td colspan="9" style="text-align:center;color:var(--text3);padding:3rem">No messages found.</td></tr><?php endif; ?>
  </tbody>
</table>
<!-- Pagination -->
<div class="pagination">
  <?php for ($i = 1; $i <= $pg['pages']; $i++): ?>
  <a href="admin.php?action=messages&page=<?= $i ?>" class="pg-btn <?= $i === $pg['current'] ? 'active' : '' ?>"><?= $i ?></a>
  <?php endfor; ?>
</div>

<?php elseif ($action === 'analytics'): ?>
<!-- ── ANALYTICS ──────────────────────────────── -->
<div class="main-title">Analytics</div>
<div class="main-sub">Page views — last 30 days</div>
<div class="stats-row" style="grid-template-columns:repeat(3,1fr)">
  <div class="stat-box"><div class="stat-num"><?= $totalViews ?></div><div class="stat-lbl">All-Time Views</div></div>
  <div class="stat-box"><div class="stat-num"><?= $todayViews ?></div><div class="stat-lbl">Today</div></div>
  <div class="stat-box"><div class="stat-num"><?= DB::row('SELECT COUNT(*) AS n FROM page_views WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)')['n'] ?></div><div class="stat-lbl">Last 7 Days</div></div>
</div>
<table class="tbl">
  <thead><tr><th>Date</th><th>Views</th><th>Bar</th></tr></thead>
  <tbody>
  <?php $maxViews = max(1, max(array_column($viewsByDay, 'views'))); ?>
  <?php foreach ($viewsByDay as $v): ?>
  <tr>
    <td style="font-family:'JetBrains Mono',monospace"><?= $v['day'] ?></td>
    <td style="font-weight:600;color:var(--accent)"><?= $v['views'] ?></td>
    <td>
      <div style="height:8px;border-radius:4px;background:var(--bg3);width:300px;overflow:hidden">
        <div style="height:100%;border-radius:4px;background:var(--accent);width:<?= round(($v['views'] / $maxViews) * 100) ?>%"></div>
      </div>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$viewsByDay): ?><tr><td colspan="3" style="text-align:center;color:var(--text3);padding:3rem">No data yet.</td></tr><?php endif; ?>
  </tbody>
</table>

<?php elseif ($action === 'settings'): ?>
<!-- ── SETTINGS ──────────────────────────────── -->
<div class="main-title">Settings</div>
<div class="main-sub">Manage site content stored in the database</div>
<?php if (!empty($savedMsg)): ?><div class="alert-success"><?= e($savedMsg) ?></div><?php endif; ?>
<form method="POST" action="admin.php?action=save-settings">
  <div class="grid-2">
    <div class="adm-fg"><label>Site Name</label><input type="text" name="site_name" value="<?= e($cfg['site_name'] ?? '') ?>" /></div>
    <div class="adm-fg"><label>Tagline</label><input type="text" name="site_tagline" value="<?= e($cfg['site_tagline'] ?? '') ?>" /></div>
    <div class="adm-fg"><label>Email</label><input type="email" name="site_email" value="<?= e($cfg['site_email'] ?? '') ?>" /></div>
    <div class="adm-fg"><label>Location</label><input type="text" name="site_location" value="<?= e($cfg['site_location'] ?? '') ?>" /></div>
    <div class="adm-fg"><label>Available for work?</label>
      <select name="site_available">
        <option value="1" <?= ($cfg['site_available']??'1')==='1'?'selected':'' ?>>Yes — show "Available" badge</option>
        <option value="0" <?= ($cfg['site_available']??'1')==='0'?'selected':'' ?>>No — hide badge</option>
      </select>
    </div>
  </div>
  <div class="section-divider"></div>
  <div class="grid-2">
    <div class="adm-fg"><label>GitHub URL</label><input type="url" name="site_github" value="<?= e($cfg['site_github'] ?? '') ?>" /></div>
    <div class="adm-fg"><label>LinkedIn URL</label><input type="url" name="site_linkedin" value="<?= e($cfg['site_linkedin'] ?? '') ?>" /></div>
    <div class="adm-fg"><label>Twitter URL</label><input type="url" name="site_twitter" value="<?= e($cfg['site_twitter'] ?? '') ?>" /></div>
    <div class="adm-fg"><label>Dribbble URL</label><input type="url" name="site_dribbble" value="<?= e($cfg['site_dribbble'] ?? '') ?>" /></div>
  </div>
  <div class="section-divider"></div>
  <div class="adm-fg"><label>Hero Subtitle</label><textarea name="hero_subtitle" rows="2"><?= e($cfg['hero_subtitle'] ?? '') ?></textarea></div>
  <div class="adm-fg"><label>About Bio — Paragraph 1</label><textarea name="about_bio_1" rows="3"><?= e($cfg['about_bio_1'] ?? '') ?></textarea></div>
  <div class="adm-fg"><label>About Bio — Paragraph 2</label><textarea name="about_bio_2" rows="3"><?= e($cfg['about_bio_2'] ?? '') ?></textarea></div>
  <div class="adm-fg"><label>About Bio — Paragraph 3</label><textarea name="about_bio_3" rows="3"><?= e($cfg['about_bio_3'] ?? '') ?></textarea></div>
  <button type="submit" class="save-btn">Save Settings →</button>
</form>

<?php endif; ?>
<?php adminFooter(); ?>
