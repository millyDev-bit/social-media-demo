<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="card-container" style="height: 550px;">
        <div class="form-card verification-container">
            <div class="verification-body">
                <div class="mail-icon">✉️</div>
                <h3>Verification</h3>
                <p class="email-info" style="text-align:center; color: #888; font-size: 13px;">
                    The code has been sent to:<br> 
                    <strong><?php echo $_SESSION["email"] ?? 'your email'; ?></strong>
                </p>

                <div id="msg-box">
                    <span id="label-text">Please enter the 6-digit code sent to your email.</span>
                </div>

                <form action="../actions/broker.php" method="POST">
                    <div class="input-group">
                        <input type="text" name="verifCode" placeholder="Enter code" maxlength="6" style="text-align: center; font-size: 18px; letter-spacing: 2px;">
                    </div>
                    <button name="verification" id="action-btn">Confirm</button>
                </form>
            </div>

        </div>
    </div>
    <?php if(isset($_SESSION["raw_verif_code"])): ?>
        <p style="color:red;">Код: <?= $_SESSION["raw_verif_code"] ?></p>
    <?php endif; ?>
    <script>
        const codeSpan = document.getElementById("code-display");
        const labelText = document.getElementById("label-text");
        const actionBtn = document.getElementById("action-btn");
        const msgBox = document.getElementById("msg-box");

        setTimeout(() => {
            msgBox.innerHTML = "<span class='timeout-text'>Code expired! Request a new one.</span>";
            
            actionBtn.innerText = "Resend Code";
            actionBtn.name = "resend_code";
            actionBtn.style.background = "linear-gradient(135deg, #6c757d 0%, #495057 100%)";
        }, 5000);
    </script>
</body>
</html>