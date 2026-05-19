<?php
session_start();
require __DIR__ . "/../includes/dbQuery.php";

if(!isset($_SESSION["user"])){
    header("Location: index.php");
    exit;
}

$userId = (int)$_SESSION["user"]["id"];
$userRow = findItWhere("idusers", "id, name, surname, login, verified", "id", $userId);

if(empty($userRow)){
    session_destroy();
    header("Location: index.php");
    exit;
}

$user = $userRow[0];
$fullName = trim(htmlspecialchars($user["name"] . " " . $user["surname"]));
$login = htmlspecialchars($user["login"]);
$verified = (int)$user["verified"];
$initials = strtoupper(mb_substr($user["name"], 0, 1) . mb_substr($user["surname"], 0, 1));
$posts = getAllPosts();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts — MillyGram</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:      #080b10;
            --panel:   #0f1318;
            --card:    #141920;
            --border:  rgba(255,255,255,.06);
            --accent:  #a78bfa;
            --accent2: #7c3aed;
            --text:    #e8eaf0;
            --muted:   #5a6070;
            --success: #34d399;
            --danger:  #f87171;
        }

        html, body { height: 100%; }

        body {
            min-height: 100vh;
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            display: grid;
            grid-template-columns: 280px 1fr;
            grid-template-rows: 100vh;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            background: var(--panel);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 48px 24px 32px;
            position: relative;
            overflow: hidden;
        }
        .sidebar::before {
            content: '';
            position: absolute;
            top: -80px; left: -80px;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(124,58,237,.18) 0%, transparent 70%);
            pointer-events: none;
        }
        .brand-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 22px; font-weight: 700;
            letter-spacing: 3px; text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 52px;
            align-self: flex-start;
        }
        .avatar {
            width: 100px; height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent2), var(--accent));
            display: flex; align-items: center; justify-content: center;
            font-family: 'Cormorant Garamond', serif;
            font-size: 34px; font-weight: 700; color: #fff;
            margin-bottom: 20px;
            box-shadow: 0 0 40px rgba(124,58,237,.35);
            flex-shrink: 0;
        }
        .sidebar-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 22px; font-weight: 600;
            text-align: center; margin-bottom: 8px;
        }
        .sidebar-login {
            font-size: 12px; color: var(--muted);
            letter-spacing: .5px; margin-bottom: 20px;
            text-align: center; word-break: break-all;
        }
        .badge {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 11px; font-weight: 500;
            padding: 4px 12px; border-radius: 20px; margin-bottom: 40px;
        }
        .badge.ok { color: var(--success); background: rgba(52,211,153,.1); border: 1px solid rgba(52,211,153,.2); }
        .badge.no { color: var(--danger);  background: rgba(248,113,113,.1);  border: 1px solid rgba(248,113,113,.2); }

        .sidebar-nav {
            width: 100%; display: flex; flex-direction: column; gap: 4px; flex: 1;
        }
        .nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 16px; border-radius: 10px;
            font-size: 14px; color: var(--muted);
            cursor: pointer; transition: background .15s, color .15s;
            text-decoration: none;
        }
        .nav-item:hover  { background: rgba(255,255,255,.04); color: var(--text); }
        .nav-item.active { background: rgba(124,58,237,.15);  color: var(--accent); }
        .nav-icon { font-size: 16px; width: 20px; text-align: center; }

        .logout-btn {
            display: flex; align-items: center; gap: 10px;
            width: 100%; padding: 11px 16px; border-radius: 10px;
            font-size: 14px; color: var(--danger);
            background: none; border: none; cursor: pointer;
            transition: background .15s; text-decoration: none; margin-top: auto;
        }
        .logout-btn:hover { background: rgba(248,113,113,.08); }

        /* ── MAIN ── */
        .main {
            overflow-y: auto;
            padding: 48px;
            display: flex; flex-direction: column; gap: 28px;
        }
        .page-header {
            display: flex; align-items: center; justify-content: space-between;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border);
        }
        .page-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 32px; font-weight: 700;
        }
        .page-subtitle { font-size: 13px; color: var(--muted); margin-top: 2px; }

        /* ── CREATE POST ── */
        .create-box {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 22px 24px;
            transition: border-color .2s;
        }
        .create-box:focus-within { border-color: rgba(167,139,250,.3); }

        .create-header {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 14px;
        }
        .create-avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: linear-gradient(135deg, var(--accent2), var(--accent));
            display: flex; align-items: center; justify-content: center;
            font-family: 'Cormorant Garamond', serif;
            font-size: 14px; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .create-label { font-size: 13px; color: var(--muted); }

        textarea {
            width: 100%;
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px; color: var(--text);
            font-family: 'Inter', sans-serif;
            resize: none; outline: none;
            transition: border-color .2s;
            line-height: 1.6;
        }
        textarea:focus { border-color: rgba(167,139,250,.4); }
        textarea::placeholder { color: var(--muted); }

        .create-footer {
            display: flex; align-items: center; justify-content: space-between;
            margin-top: 12px;
        }
        .char-count { font-size: 12px; color: var(--muted); }
        .char-count.warn { color: var(--danger); }

        .btn-post {
            padding: 9px 22px;
            background: linear-gradient(135deg, var(--accent2), var(--accent));
            border: none; border-radius: 8px;
            color: #fff; font-family: 'Inter', sans-serif;
            font-size: 13px; font-weight: 500;
            cursor: pointer; transition: opacity .2s, transform .15s;
        }
        .btn-post:hover  { opacity: .88; }
        .btn-post:active { transform: scale(.97); }

        /* ── FEED ── */
        .section-title {
            font-size: 11px; color: var(--muted);
            text-transform: uppercase; letter-spacing: 1.5px;
            margin-bottom: 14px;
        }

        .feed { display: flex; flex-direction: column; gap: 14px; }

        .post-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px 22px;
            transition: border-color .2s;
            animation: fadeIn .35s ease both;
        }
        .post-card:hover { border-color: rgba(167,139,250,.2); }

        .post-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 12px;
        }
        .post-author-wrap { display: flex; align-items: center; gap: 10px; }
        .post-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: linear-gradient(135deg, var(--accent2), var(--accent));
            display: flex; align-items: center; justify-content: center;
            font-family: 'Cormorant Garamond', serif;
            font-size: 13px; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .post-author { font-size: 14px; font-weight: 500; color: var(--text); }
        .post-date   { font-size: 11px; color: var(--muted); margin-top: 2px; }

        .post-content {
            font-size: 14px; color: var(--text);
            line-height: 1.7; word-break: break-word;
        }

        .btn-delete {
            background: none; border: none;
            color: var(--muted); font-size: 13px;
            cursor: pointer; padding: 4px 8px;
            border-radius: 6px;
            transition: background .15s, color .15s;
        }
        .btn-delete:hover { background: rgba(248,113,113,.1); color: var(--danger); }

        .empty-state {
            text-align: center; padding: 60px 20px;
            color: var(--muted); font-size: 14px;
        }
        .empty-icon { font-size: 40px; margin-bottom: 12px; opacity: .4; }

        /* ── NOTIFICATION ── */
        .notification {
            position: fixed; top: 24px; right: 24px; z-index: 999;
            padding: 14px 20px; border-radius: 12px;
            font-size: 14px; max-width: 340px;
            opacity: 0; transform: translateY(-10px);
            transition: opacity .3s, transform .3s;
            pointer-events: none;
        }
        .notification.show { opacity: 1; transform: translateY(0); pointer-events: auto; }
        .notification.error-bg   { background: rgba(248,113,113,.12); border: 1px solid rgba(248,113,113,.3); color: var(--danger); }
        .notification.success-bg { background: rgba(52,211,153,.12);  border: 1px solid rgba(52,211,153,.3);  color: var(--success); }

        /* ── RESPONSIVE ── */
        @media (max-width: 900px) {
            body { grid-template-columns: 1fr; grid-template-rows: auto 1fr; }
            .sidebar {
                flex-direction: row; flex-wrap: wrap;
                padding: 20px 24px; gap: 16px;
                align-items: center;
                border-right: none; border-bottom: 1px solid var(--border);
            }
            .sidebar::before { display: none; }
            .brand-name { margin-bottom: 0; font-size: 18px; flex: 1; }
            .avatar { width: 52px; height: 52px; font-size: 18px; margin-bottom: 0; box-shadow: none; }
            .sidebar-name { font-size: 16px; margin-bottom: 0; }
            .sidebar-login, .badge, .sidebar-nav { display: none; }
            .logout-btn { margin-top: 0; width: auto; padding: 8px 14px; }
            .main { padding: 24px 20px; }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .main > * { animation: fadeIn .4s ease both; }
        .main > *:nth-child(2) { animation-delay: .07s; }
        .main > *:nth-child(3) { animation-delay: .14s; }
    </style>
</head>
<body>

<?php if(isset($_SESSION["message"])): ?>
    <?php
        $type = $_SESSION["message"]["type"] ?? "error";
        $msgClass = ($type == "success") ? "success-bg" : "error-bg";
    ?>
    <div id="notification" class="notification <?= $msgClass ?>">
        <?= htmlspecialchars($_SESSION["message"]["message"] ?? "") ?>
    </div>
    <?php unset($_SESSION["message"]); ?>
    <script>
        const n = document.getElementById("notification");
        setTimeout(() => n.classList.add("show"), 100);
        setTimeout(() => n.classList.remove("show"), 4000);
    </script>
<?php endif; ?>

<aside class="sidebar">
    <div class="brand-name">MillyGram</div>
    <div class="avatar"><?= $initials ?></div>
    <div class="sidebar-name"><?= $fullName ?></div>
    <div class="sidebar-login"><?= $login ?></div>

    <?php if($verified): ?>
        <span class="badge ok">✔ Verified</span>
    <?php else: ?>
        <span class="badge no">✖ Not verified</span>
    <?php endif; ?>

    <nav class="sidebar-nav">
        <a class="nav-item" href="profile.php">
            <span class="nav-icon">◈</span> Profile
        </a>
        <a class="nav-item active" href="posts.php">
            <span class="nav-icon">◎</span> Posts
        </a>
        <a class="nav-item" href="#">
            <span class="nav-icon">◉</span> Activity
        </a>
    </nav>
    <a href="../actions/logout.php" class="logout-btn">
        <span>→</span> Log out
    </a>
</aside>

<main class="main">
    <div class="page-header">
        <div>
            <h1 class="page-title">Posts</h1>
            <p class="page-subtitle">Share your thoughts with the community</p>
        </div>
    </div>

    <div class="create-box">
        <div class="create-header">
            <div class="create-avatar"><?= $initials ?></div>
            <div class="create-label">What's on your mind?</div>
        </div>
        <form action="../actions/broker.php" method="POST">
            <textarea
                name="content"
                id="postContent"
                rows="3"
                maxlength="255"
                placeholder="Write something..."
                oninput="updateCount(this)"
            ></textarea>
            <div class="create-footer">
                <span class="char-count" id="charCount">0 / 255</span>
                <button class="btn-post" name="create_post">Publish</button>
            </div>
        </form>
    </div>

    <div>
        <div class="section-title">Recent posts</div>
        <div class="feed">
            <?php if(empty($posts)): ?>
                <div class="empty-state">
                    <div class="empty-icon">◎</div>
                    <p>No posts yet. Be the first to share something!</p>
                </div>
            <?php else: ?>
                <?php foreach($posts as $post):
                    $authorInitials = strtoupper(
                        mb_substr($post["name"], 0, 1) .
                        mb_substr($post["surname"], 0, 1)
                    );
                    $authorName = htmlspecialchars($post["name"] . " " . $post["surname"]);
                    $isOwner    = (int)$post["userid"] === $userId;
                ?>
                <div class="post-card">
                    <div class="post-header">
                        <div class="post-author-wrap">
                            <div class="post-avatar"><?= $authorInitials ?></div>
                            <div>
                                <div class="post-author"><?= $authorName ?></div>
                                <div class="post-date"><?= htmlspecialchars($post["date"]) ?></div>
                            </div>
                        </div>
                        <?php if($isOwner): ?>
                        <form action="../actions/broker.php" method="POST" style="margin:0;">
                            <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                            <button class="btn-delete" name="delete_post" title="Delete post">✕</button>
                        </form>
                        <?php endif; ?>
                    </div>
                    <div class="post-content"><?= nl2br(htmlspecialchars($post["content"])) ?></div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
    function updateCount(el) {
        const len = el.value.length;
        const label = document.getElementById("charCount");
        label.textContent = len + " / 255";
        label.classList.toggle("warn", len > 220);
    }
</script>

</body>
</html>