<?php
// process_update.php
require 'config.php';
require 'vendor/autoload.php'; // Make sure this loads your Endroid QR Code library
require 'qr_functions.php';


use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use SendGrid\Mail\Mail;


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

// Determine the base URL
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

$short_url = $base_url . "/" . $short_code;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $qrDataUri = generateQrCode($short_url);

    echo json_encode([
        'short_url' => $short_url,
        'original_url' => $decrypted_url,
        'visit_count'  => $link['visit_count'],
        'qr_code'  => $qrDataUri
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $passcode = $_POST['passcode'];
    $new_url = filter_var($_POST['new_url'], FILTER_VALIDATE_URL);

    if (!$email || !$passcode || !$new_url) {
        echo json_encode(['error' => 'Please provide valid inputs.']);
        exit;
    }
    if ($email !== $decrypted_email || !password_verify($passcode, $link['passcode'])) {
        echo json_encode(['error' => 'Invalid email or passcode.']);
        exit;
    }

    // Encrypt the new URL before updating the record
    $encrypted_new_url = encryptData($new_url);
    $stmt = $pdo->prepare("UPDATE urls SET original_url = ? WHERE short_code = ?");
    $stmt->execute([$encrypted_new_url, $short_code]);

    // Prepare confirmation email using SendGrid
    $subject = "URL Update Confirmation";
    $messageBody = "Hello,\n\nYour URL has been updated successfully.\n\nNew URL: " . $new_url . "\nShort Code: " . $short_code . "\n\nRegards,\nNiceLink Team";

    $emailObj = new Mail();
    $emailObj->setFrom("no-reply@nicelink.co.uk", "NiceLink Service");
    $emailObj->setSubject($subject);
    $emailObj->addTo($email);
    $emailObj->addContent("text/plain", $messageBody);

    $sendgrid = new \SendGrid($sendgrid_api_key);
    try {
        $response = $sendgrid->send($emailObj);
    } catch (Exception $e) {
        error_log('SendGrid Error: ' . $e->getMessage());
    }

    echo json_encode(['message' => 'URL updated successfully! Confirmation email sent.']);
    exit;
}

echo json_encode(['error' => 'Invalid request method.']);
exit;