<?php
require_once 'functions.php';

session_start();

$email = $_GET['email'] ?? $_POST['unsubscribe_email'] ?? '';
$verificationCode = $_POST['verification_code'] ?? '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['unsubscribe_email']) && !isset($_POST['verification_code'])) {
        // Unsubscribe email submission
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $code = generateVerificationCode();
            if (sendUnsubscribeVerificationEmail($email, $code)) {
                $_SESSION['unsubscribe_code'] = $code;
                $_SESSION['unsubscribe_email'] = $email;
                $message = 'Unsubscribe verification code sent to your email.';
            } else {
                $message = 'Failed to send verification email. Please try again.';
            }
        } else {
            $message = 'Please enter a valid email address.';
        }
    } elseif (isset($_POST['verification_code'])) {
        // Verification code submission for unsubscribe
        $storedCode = $_SESSION['unsubscribe_code'] ?? '';
        $storedEmail = $_SESSION['unsubscribe_email'] ?? '';
        
        if ($storedCode === $verificationCode && $storedEmail === $email) {
            if (unsubscribeEmail($email)) {
                $message = 'You have been unsubscribed successfully.';
                unset($_SESSION['unsubscribe_code']);
                unset($_SESSION['unsubscribe_email']);
            } else {
                $message = 'Failed to unsubscribe. The email may not be registered.';
            }
        } else {
            $message = 'Invalid verification code. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unsubscribe from XKCD Comics</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="email"], input[type="text"] { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 15px; background-color: #f44336; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #d32f2f; }
        .message { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background-color: #dff0d8; color: #3c763d; }
        .error { background-color: #f2dede; color: #a94442; }
    </style>
</head>
<body>
    <h1>Unsubscribe from XKCD Comics</h1>
    
    <?php if ($message): ?>
        <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <form method="post">
        <div class="form-group">
            <label for="unsubscribe_email">Email Address</label>
            <input type="email" id="unsubscribe_email" name="unsubscribe_email" 
                   value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
        <button id="submit-unsubscribe" type="submit">Unsubscribe</button>
        
        <div class="form-group">
            <label for="verification_code">Verification Code</label>
            <input type="text" id="verification_code" name="verification_code" maxlength="6" required>
        </div>
        <button id="submit-verification" type="submit">Verify</button>
    </form>
</body>
</html>