<?php
session_start();
require __DIR__ . "/../includes/dbQuery.php";

if(!isset($_SESSION["user"])){
    header("Location: ../../index.php"); exit;
}

$userId = (int)$_SESSION["user"]["id"];
$userRow = findItWhere("idusers", "id, name, surname, login, verified", "id", $userId);

if(empty($userRow)){
    session_destroy();
    header("Location: ../../index.php"); exit;
}

$user     = $userRow[0];
$fullName = trim(htmlspecialchars($user["name"] . " " . $user["surname"]));
$login    = htmlspecialchars($user["login"]);
$verified = (int)$user["verified"];
$initials = strtoupper(mb_substr($user["name"], 0, 1) . mb_substr($user["surname"], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $fullName ?> — MillyGram</title>
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
            --gold:    #f0c060;
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
            font-size: 32px; font-weight: 700; color: var(--text);
        }
        .page-subtitle { font-size: 13px; color: var(--muted); margin-top: 2px; }

        .stats-row {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;
        }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px; padding: 20px 22px;
            transition: border-color .2s;
        }
        .stat-card:hover { border-color: rgba(167,139,250,.25); }

        .stat-label { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .stat-value { font-family: 'Cormorant Garamond', serif; font-size: 28px; font-weight: 700; color: var(--text); }
        .stat-sub   { font-size: 12px; color: var(--muted); margin-top: 4px; }

        .section-title {
            font-size: 11px; color: var(--muted);
            text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 14px;
        }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

        .info-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px; padding: 18px 20px;
            display: flex; gap: 14px; align-items: flex-start;
            transition: border-color .2s;
        }
        .info-card:hover { border-color: rgba(167,139,250,.2); }
        .info-card.wide  { grid-column: span 2; }

        .info-icon-wrap {
            width: 38px; height: 38px; border-radius: 10px;
            background: rgba(124,58,237,.12);
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; flex-shrink: 0; color: var(--accent);
        }

        .info-label { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: .8px; margin-bottom: 4px; }
        .info-value { font-size: 15px; color: var(--text); font-weight: 400; word-break: break-all; }

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
            .stats-row { grid-template-columns: 1fr 1fr; }
            .info-grid { grid-template-columns: 1fr; }
            .info-card.wide { grid-column: span 1; }
        }

        @media (max-width: 480px) {
            .stats-row { grid-template-columns: 1fr; }
            .page-title { font-size: 24px; }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .main > * { animation: fadeIn .4s ease both; }
        .main > *:nth-child(2) { animation-delay: .07s; }
        .main > *:nth-child(3) { animation-delay: .14s; }
        .main > *:nth-child(4) { animation-delay: .21s; }
    </style>
</head>
<body>
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
            <a class="nav-item active" href="profile.php">
                <span class="nav-icon">◈</span> Profile
            </a>
            <a class="nav-item" href="posts.php">
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
                <h1 class="page-title">My Profile</h1>
                <p class="page-subtitle">Your personal information and account details</p>
            </div>
        </div>

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-label">User ID</div>
                <div class="stat-value">#<?= $userId ?></div>
                <div class="stat-sub">Account identifier</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Status</div>
                <div class="stat-value" style="font-size:20px; color: <?= $verified ? 'var(--success)' : 'var(--danger)' ?>">
                    <?= $verified ? 'Active' : 'Pending' ?>
                </div>
                <div class="stat-sub"><?= $verified ? 'Account verified' : 'Verification required' ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Member</div>
                <div class="stat-value" style="font-size:20px;">MillyGram</div>
                <div class="stat-sub">Standard plan</div>
            </div>
        </div>

        <div>
            <div class="section-title">Personal information</div>
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-icon-wrap">◈</div>
                    <div>
                        <div class="info-label">First Name</div>
                        <div class="info-value"><?= htmlspecialchars($user["name"]) ?></div>
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-icon-wrap">◈</div>
                    <div>
                        <div class="info-label">Last Name</div>
                        <div class="info-value"><?= htmlspecialchars($user["surname"]) ?></div>
                    </div>
                </div>
                <div class="info-card wide">
                    <div class="info-icon-wrap">✉</div>
                    <div>
                        <div class="info-label">Email / Login</div>
                        <div class="info-value"><?= $login ?></div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>