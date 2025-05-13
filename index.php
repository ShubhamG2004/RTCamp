<?php
require_once 'functions.php';

$email = '';
$message = '';
$showVerification = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        // Email submission
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $code = generateVerificationCode();
            if (sendVerificationEmail($email, $code)) {
                $message = 'Verification code sent to your email.';
                $showVerification = true;
                // Store code in session for verification
                session_start();
                $_SESSION['verification_code'] = $code;
                $_SESSION['verification_email'] = $email;
            } else {
                $message = 'Failed to send verification email.';
            }
        } else {
            $message = 'Please enter a valid email address.';
        }
    } elseif (isset($_POST['verification_code'])) {
        // Verification code submission
        session_start();
        $email = $_SESSION['verification_email'] ?? '';
        $storedCode = $_SESSION['verification_code'] ?? '';
        
        if ($email && $storedCode && $storedCode === $_POST['verification_code']) {
            if (registerEmail($email)) {
                $message = 'Email verified and registered successfully!';
                unset($_SESSION['verification_code']);
                unset($_SESSION['verification_email']);
            } else {
                $message = 'Failed to register email.';
            }
        } else {
            $message = 'Invalid verification code.';
            $showVerification = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>XKCD Comic Subscription</title>
    <style>
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 15px; background: #0066cc; color: white; border: none; cursor: pointer; }
        button:hover { background: #0055aa; }
        .message { padding: 10px; margin: 10px 0; background: #f0f0f0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>XKCD Comic Subscription</h1>
        
        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
            </div>
            <button id="submit-email" type="submit">Subscribe</button>
            
            <?php if ($showVerification): ?>
                <div class="form-group" style="margin-top: 20px;">
                    <label for="verification_code">Verification Code</label>
                    <input type="text" id="verification_code" name="verification_code" maxlength="6" required>
                    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                </div>
                <button id="submit-verification" type="submit">Verify</button>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>