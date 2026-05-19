<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MillyGram - Welcome</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:      #080b10;
            --panel:   #0f1318;
            --border:  rgba(255,255,255,.06);
            --accent:  #a78bfa;
            --accent2: #7c3aed;
            --text:    #e8eaf0;
            --muted:   #5a6070;
            --success: #34d399;
            --danger:  #f87171;
        }

        html, body { height: 100%; }

        body{
            min-height: 100vh;
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .deco-panel{
            background: var(--panel);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px;
            position: relative;
            overflow: hidden;
            min-height: 100vh;
        }

        .deco-panel::before{
            content: '';
            position: absolute; top: -100px; left: -100px;
            width: 450px; height: 450px;
            background: radial-gradient(circle, rgba(124,58,237,.2) 0%, transparent 65%);
            pointer-events: none;
        }

        .deco-panel::after{
            content: '';
            position: absolute; bottom: -80px; right: -80px;
            width: 320px; height: 320px;
            background: radial-gradient(circle, rgba(167,139,250,.1) 0%, transparent 65%);
            pointer-events: none;
        }

        .deco-brand{
            font-family: 'Cormorant Garamond', serif;
            font-size: 42px; font-weight: 700;
            letter-spacing: 4px; text-transform: uppercase;
            color: var(--accent); margin-bottom: 16px;
            position: relative; z-index: 1;
        }
        .deco-tagline {
            font-size: 14px; color: var(--muted);
            text-align: center; line-height: 1.7;
            max-width: 280px; position: relative; z-index: 1;
            margin-bottom: 56px;
        }
        .deco-circles {
            position: relative; z-index: 1;
            display: flex; gap: 16px; align-items: center;
        }
        .deco-circle {
            border-radius: 50%;
            border: 1px solid rgba(167,139,250,.2);
        }
        .deco-circle:nth-child(1) { width: 80px; height: 80px; background: rgba(124,58,237,.08); }
        .deco-circle:nth-child(2) { width: 56px; height: 56px; background: rgba(124,58,237,.12); }
        .deco-circle:nth-child(3) { width: 36px; height: 36px; background: rgba(124,58,237,.18); }

        .form-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 48px 56px;
            overflow-y: auto;
            min-height: 100vh;
        }

        .flip-scene {
            width: 100%;
            max-width: 400px;
            perspective: 1200px;
        }

        .flip-inner {
            position: relative;
            width: 100%;
            transition: transform .65s cubic-bezier(.4,0,.2,1);
            transform-style: preserve-3d;
        }
        .flip-inner.flipped { transform: rotateY(180deg); }

        .flip-front,
        .flip-back {
            width: 100%;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }

        .flip-front { position: relative; }
        .flip-back  {
            position: absolute;
            top: 0; left: 0;
            transform: rotateY(180deg);
        }

        .form-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 34px; font-weight: 700;
            margin-bottom: 6px;
        }
        .form-sub {
            font-size: 13px; color: var(--muted);
            margin-bottom: 32px;
        }
        .field { margin-bottom: 16px; }
        .field label {
            display: block; font-size: 11px; color: var(--muted);
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 7px;
        }
        .field input {
            width: 100%;
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px; color: var(--text);
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color .2s;
        }
        .field input:focus { border-color: rgba(167,139,250,.5); }
        .field input::placeholder { color: var(--muted); }
        .field-hint { display: block; font-size: 11px; color: var(--muted); margin-top: 5px; }

        .btn-submit {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, var(--accent2), var(--accent));
            border: none; border-radius: 10px;
            color: #fff; font-family: 'Inter', sans-serif;
            font-size: 14px; font-weight: 500;
            cursor: pointer; margin-top: 8px;
            transition: opacity .2s, transform .15s;
        }
        .btn-submit:hover  { opacity: .88; }
        .btn-submit:active { transform: scale(.98); }

        .toggle-line {
            margin-top: 20px; font-size: 13px; color: var(--muted); text-align: center;
        }
        .toggle-line span {
            color: var(--accent); cursor: pointer; font-weight: 500;
        }
        .toggle-line span:hover { text-decoration: underline; }

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

        @media (max-width: 768px) {
            body { grid-template-columns: 1fr; }
            .deco-panel {
                min-height: auto; padding: 24px;
                flex-direction: row; justify-content: flex-start;
                border-right: none; border-bottom: 1px solid var(--border);
            }
            .deco-panel::before, .deco-panel::after { display: none; }
            .deco-brand { font-size: 24px; margin-bottom: 0; }
            .deco-tagline, .deco-circles { display: none; }
            .form-panel { padding: 32px 24px; min-height: auto; }
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION["message"])): ?>
        <?php
            $type = $_SESSION["message"]["type"] ?? "error";
            $msgClass = ($type === "success") ? "success-bg" : "error-bg";
        ?>
        <div id="notification" class="notification <?= $msgClass ?>">
            <?= htmlspecialchars($_SESSION["message"]["message"] ?? "An error occurred") ?>
        </div>
        <?php unset($_SESSION["message"]); ?>
        <script>
            const n = document.getElementById("notification");
            setTimeout(() => n.classList.add("show"), 100);
            setTimeout(() => n.classList.remove("show"), 5000);
        </script>
    <?php endif; ?>

    <div class="deco-panel">
        <div class="deco-brand">MillyGram</div>
        <p class="deco-tagline">Connect, share and discover people around you.</p>
        <div class="deco-circles">
            <div class="deco-circle"></div>
            <div class="deco-circle"></div>
            <div class="deco-circle"></div>
        </div>
    </div>

    <div class="form-panel">
        <div class="flip-scene">
            <div class="flip-inner" id="flipInner">
                <div class="flip-front">
                    <h1 class="form-title">Welcome back</h1>
                    <p class="form-sub">Sign in to your account</p>
                    <form action="actions/broker.php" method="POST" novalidate>
                        <div class="field">
                            <label>Email or login</label>
                            <input type="text" name="login" placeholder="you@example.com" required>
                        </div>
                        <div class="field">
                            <label>Password</label>
                            <input type="password" name="password" placeholder="••••••••" minlength="8" required>
                        </div>
                        <button class="btn-submit" name="signin">Sign In</button>
                    </form>
                    <p class="toggle-line">New here? <span onclick="flipCard()">Create an account</span></p>
                </div>
                <div class="flip-back">
                    <h1 class="form-title">Create account</h1>
                    <p class="form-sub">Join MillyGram today</p>
                    <form action="../actions/broker.php" method="POST" novalidate id="signupForm">
                        <div class="field">
                            <label>Name</label>
                            <input type="text" name="name" placeholder="Anna"
                                pattern="^[a-zA-Zа-яА-ЯёЁ\s\-]{2,50}$"
                                title="Letters only, 2–50 characters">
                        </div>
                        <div class="field">
                            <label>Surname</label>
                            <input type="text" name="surname" placeholder="Ivanova"
                                pattern="^[a-zA-Zа-яА-ЯёЁ\s\-]{2,50}$"
                                title="Letters only, 2–50 characters">
                        </div>
                        <div class="field">
                            <label>Email / Login *</label>
                            <input type="text" name="login" placeholder="you@example.com" required>
                        </div>
                        <div class="field">
                            <label>Password *</label>
                            <input type="password" name="password" id="regPass" placeholder="••••••••"
                                pattern="^(?=.*[A-Z])(?=.*[0-9]).{8,}$"
                                title="Min 8 chars, 1 uppercase letter, 1 digit"
                                required>
                            <span class="field-hint">Min 8 chars, 1 uppercase, 1 digit</span>
                        </div>
                        <div class="field">
                            <label>Confirm Password *</label>
                            <input type="password" name="conf_password" id="confPass" placeholder="••••••••" required>
                        </div>
                        <button class="btn-submit" name="signup">Sign Up</button>
                    </form>
                    <p class="toggle-line">Already a member? <span onclick="flipCard()">Sign in</span></p>
                </div>

            </div>
        </div>
    </div>

    <script>
        const flipInner = document.getElementById("flipInner");
        const front = flipInner.querySelector(".flip-front");
        const back  = flipInner.querySelector(".flip-back");

        function syncHeight() {
            const isFlipped = flipInner.classList.contains("flipped");
            back.style.position = "relative";
            back.style.visibility = "hidden";
            const backH  = back.scrollHeight;
            const frontH = front.scrollHeight;
            back.style.position = "";
            back.style.visibility = "";
            flipInner.style.height = (isFlipped ? backH : frontH) + "px";
        }

        function flipCard() {
            flipInner.classList.toggle("flipped");
            setTimeout(syncHeight, 10);
        }

        syncHeight();
        window.addEventListener("resize", syncHeight);

        document.getElementById("confPass")?.addEventListener("input", function () {
            const pass = document.getElementById("regPass").value;
            this.setCustomValidity(this.value !== pass ? "Passwords do not match" : "");
        });
    </script>
</body>
</html>