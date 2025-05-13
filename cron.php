<?php
require_once 'functions.php';

// Send XKCD updates to all subscribers
sendXKCDUpdatesToSubscribers();

// Log the execution (optional)
file_put_contents(__DIR__ . '/cron.log', date('Y-m-d H:i:s') . " - Sent XKCD updates\n", FILE_APPEND);
?>