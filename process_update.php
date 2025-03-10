<?php
// process_update.php
require 'config.php';
header("Content-Type: application/json");

require 'vendor/autoload.php';  // Now includes endroid/qr-code
// Generate QR code using endroid/qr-code
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

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

// Decrypt the original URL (and email if needed for internal checks)
$decrypted_url = decryptData($link['original_url']);
// $decrypted_email = decryptData($link['email']); // Removed from GET response

// Handle POST request to update the URL
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $passcode = $_POST['passcode'];
    $new_url = filter_var($_POST['new_url'], FILTER_VALIDATE_URL);

    if (!$email || !$passcode || !$new_url) {
        echo json_encode(['error' => 'Please provide valid inputs.']);
        exit;
    }
    // We still validate email/passcode against stored values
    // (Assuming you still want to require the user to re-enter their email for updating)
    $decrypted_email = decryptData($link['email']); // For validation purposes only
    if ($email !== $decrypted_email || !password_verify($passcode, $link['passcode'])) {
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

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

$short_url = $base_url . "/" . $short_code;

// Generate the QR code for the short URL
$qrCode = new QrCode(urlencode($short_url));
$writer = new PngWriter();
$result = $writer->write($qrCode);
$qrImageData = $result->getString(); // Binary data for the QR image

// For GET requests, return the current URL details (without email) and visit count
echo json_encode([
    'original_url' => $decrypted_url,
    'visit_count' => $link['visit_count'],
    'qr_code' => $result->getDataUri()
]);
?>