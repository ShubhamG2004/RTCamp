<?php
require_once __DIR__ . '/functions.php';

$email = '';
$verificationCode = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        // Email submission
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $code = generateVerificationCode();
            sendVerificationEmail($email, $code);
            $message = 'Verification code sent to your email.';
        } else {
            $message = 'Invalid email address.';
        }
    } elseif (isset($_POST['verification_code'])) {
        // Verification code submission
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $code = filter_var($_POST['verification_code'], FILTER_SANITIZE_STRING);
        
        if (verifyCode($email, $code)) {
            registerEmail($email);
            $message = 'Email verified and registered successfully!';
        } else {
            $message = 'Invalid verification code.';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>XKCD Comic Subscription</title>
</head>
<body>
    <h1>XKCD Comic Subscription</h1>
    
    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    
    <form method="post">
        <h2>Subscribe</h2>
        <input type="email" name="email" placeholder="Enter your email" required>
        <button id="submit-email">Submit</button>
        
        <h2>Verify Code</h2>
        <input type="text" name="verification_code" placeholder="Enter verification code" maxlength="6" required>
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
        <button id="submit-verification">Verify</button>
    </form>
</body>
</html>