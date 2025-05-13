<?php

function generateVerificationCode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function registerEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    // Check if email already exists
    $emails = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    if (in_array($email, $emails)) {
        return false;
    }
    return file_put_contents($file, $email . PHP_EOL, FILE_APPEND) !== false;
}

function unsubscribeEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) {
        return false;
    }
    
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $emails = array_filter($emails, fn($e) => $e !== $email);
    
    return file_put_contents($file, implode(PHP_EOL, $emails) . PHP_EOL) !== false;
}

function sendVerificationEmail($email, $code) {
    $subject = 'Your Verification Code';
    $message = '<p>Your verification code is: <strong>' . $code . '</strong></p>';
    $headers = 'From: no-reply@example.com' . "\r\n" .
               'Content-Type: text/html; charset=UTF-8' . "\r\n";
    
    return mail($email, $subject, $message, $headers);
}

function verifyCode($email, $code) {
    session_start();
    $storedCode = $_SESSION['verification_code'] ?? '';
    $storedEmail = $_SESSION['verification_email'] ?? '';
    return ($storedCode === $code && $storedEmail === $email);
}

function fetchRandomXKCDComic() {
    // First get the latest comic number
    $latest = @json_decode(file_get_contents('https://xkcd.com/info.0.json'), true);
    $maxId = $latest['num'] ?? 2500; // Fallback if API fails
    
    // Try to get a random comic (retry up to 3 times if fails)
    $attempts = 0;
    while ($attempts < 3) {
        $randomId = random_int(1, $maxId);
        $comicData = @json_decode(file_get_contents("https://xkcd.com/{$randomId}/info.0.json"), true);
        if ($comicData) {
            return $comicData;
        }
        $attempts++;
    }
    return null;
}

function fetchAndFormatXKCDData() {
    $comic = fetchRandomXKCDComic();
    if (!$comic) {
        return '<p>Failed to fetch XKCD comic. Please try again later.</p>';
    }
    
    $html = '<h2>XKCD Comic: ' . htmlspecialchars($comic['safe_title']) . '</h2>';
    $html .= '<img src="' . htmlspecialchars($comic['img']) . '" alt="' . htmlspecialchars($comic['alt']) . '">';
    $html .= '<p>' . htmlspecialchars($comic['alt']) . '</p>';
    
    return $html;
}

function sendXKCDUpdatesToSubscribers() {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) {
        return;
    }
    
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (empty($emails)) {
        return;
    }
    
    $comicHtml = fetchAndFormatXKCDData();
    if (empty($comicHtml)) {
        return;
    }
    
    $subject = 'Your XKCD Comic';
    $headers = 'From: no-reply@example.com' . "\r\n" .
               'Content-Type: text/html; charset=UTF-8' . "\r\n";
    
    foreach ($emails as $email) {
        $unsubscribeLink = 'http://' . $_SERVER['HTTP_HOST'] . '/unsubscribe.php?email=' . urlencode($email);
        $message = $comicHtml . '<p><a href="' . $unsubscribeLink . '" id="unsubscribe-button">Unsubscribe</a></p>';
        mail($email, $subject, $message, $headers);
    }
}

function sendUnsubscribeVerificationEmail($email, $code) {
    $subject = 'Confirm Un-subscription';
    $message = '<p>To confirm un-subscription, use this code: <strong>' . $code . '</strong></p>';
    $headers = 'From: no-reply@example.com' . "\r\n" .
               'Content-Type: text/html; charset=UTF-8' . "\r\n";
    
    return mail($email, $subject, $message, $headers);
}

?>
