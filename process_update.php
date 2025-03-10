<?php
// process_update.php
require 'config.php';
header("Content-Type: application/json");

if (!isset($_GET['code'])) {
    echo json_encode(['error' => 'No code provided.']);
    exit;
}

$short_code = $_GET['code'];
$stmt = $pdo->prepare("SELECT * FROM urls WHERE short_code = ?");
$stmt->execute([$short_code]);
$link = $stmt->fetch();

if (!$link) {
    echo json_encode(['error' => 'Invalid URL.']);
    exit;
}

// Decrypt stored email and original URL
$decrypted_email = decryptData($link['email']);
$decrypted_url = decryptData($link['original_url']);

// If the request is POST, process the update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $passcode = $_POST['passcode'];
    $new_url = filter_var($_POST['new_url'], FILTER_VALIDATE_URL);

    if (!$email || !$passcode || !$new_url) {
        echo json_encode(['error' => 'Please provide valid inputs.']);
        exit;
    } elseif ($email !== $decrypted_email || !password_verify($passcode, $link['passcode'])) {
        echo json_encode(['error' => 'Invalid email or passcode.']);
        exit;
    } else {
        // Encrypt the new URL before updating
        $encrypted_new_url = encryptData($new_url);
        $stmt = $pdo->prepare("UPDATE urls SET original_url = ? WHERE short_code = ?");
        $stmt->execute([$encrypted_new_url, $short_code]);

        // Send confirmation email to the user
        $subject = "URL Update Confirmation";
        $message = "Hello,\n\nYour URL has been updated successfully.\n\nNew URL: " . $new_url . "\n\nRegards,\nURL Shortener Service";
        $headers = "From: no-reply@shorturl.local";
        mail($email, $subject, $message, $headers);

        echo json_encode(['message' => 'URL updated successfully! Confirmation email sent.']);
        exit;
    }
}

// For GET requests, return the current URL details including visit count
echo json_encode([
    'original_url' => $decrypted_url,
    'visit_count' => $link['visit_count']
]);
?>