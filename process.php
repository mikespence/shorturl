<?php
// process.php
require 'config.php';
require 'filter.php';
require 'vendor/autoload.php';  // Now includes endroid/qr-code
require 'qr_functions.php';

// Generate QR code using endroid/qr-code
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

header("Content-Type: application/json");

// Function to generate a random string
function generateRandomString($length = 6) {
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}

// Example banned words array (if not defined in filter.php)
if (!isset($bannedWords)) {
    $bannedWords = ['admin', 'root', 'login', 'support', 'update', 'help'];
}

$original_url = filter_var($_POST['original_url'], FILTER_VALIDATE_URL);
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
$custom_code = isset($_POST['custom_code']) ? trim($_POST['custom_code']) : '';

if (!$original_url || !$email) {
    echo json_encode(['error' => 'Invalid input.']);
    exit;
}

$short_code = '';
if (!empty($custom_code)) {
    // Perform a case-insensitive banned word check
    if (in_array(strtolower($custom_code), array_map('strtolower', $bannedWords))) {
        echo json_encode(['error' => 'The custom code you provided is not allowed. Please choose another.']);
        exit;
    }
    
    // Check if the custom code already exists
    $stmt = $pdo->prepare("SELECT id FROM urls WHERE short_code = ?");
    $stmt->execute([$custom_code]);
    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Custom code already in use.']);
        exit;
    }
    
    $short_code = $custom_code;
} else {
    // Generate a random unique code if no custom code was provided.
    do {
        $short_code = generateRandomString();
        $stmt = $pdo->prepare("SELECT id FROM urls WHERE short_code = ?");
        $stmt->execute([$short_code]);
    } while ($stmt->fetch());
}

// Auto-generate a 4-digit numeric passcode
$passcode_plain = random_int(1000, 9999);
// Hash the passcode for secure storage
$hashed_passcode = password_hash($passcode_plain, PASSWORD_DEFAULT);

// Encrypt the original URL and email before storing
$encrypted_url = encryptData($original_url);
$encrypted_email = encryptData($email);

$stmt = $pdo->prepare("INSERT INTO urls (short_code, original_url, email, passcode) VALUES (?, ?, ?, ?)");
$stmt->execute([$short_code, $encrypted_url, $encrypted_email, $hashed_passcode]);

// Get the base URL (ensure it matches your environment)
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

$short_url = $base_url . "/" . $short_code;
// Create a nicer update URL using the /u/ route (make sure your .htaccess routes /u/SHORTCODE to update.php)
$update_url = $base_url . "/u/" . $short_code;

// Send email via SendGrid
$emailObj = new \SendGrid\Mail\Mail();
$emailObj->setFrom("no-reply@shortqr.app", "ShortQR");
$emailObj->setSubject("Your Short URL Information");
$emailObj->addTo($email);  // The recipient email address

$emailContent = "Hello,\n\n"
    . "Your short URL has been created successfully.\n\n"
    . "Short URL: $short_url\n"
    // . "Update URL: $update_url\n"
    // . "Your update passcode: $passcode_plain\n\n"
    // . "Keep this information safe. You will need the email and passcode to update your URL later.\n\n";
$emailObj->addContent("text/plain", $emailContent);

$sendgrid = new \SendGrid($sendgrid_api_key);
try {
    $response = $sendgrid->send($emailObj);
} catch (Exception $e) {
    error_log('SendGrid Error: ' . $e->getMessage());
}

// Return the response as JSON, including the locally generated QR code data URI
$response = [
    'short_url' => $short_url,
    'update_url' => $update_url,
    'qr_code' => generateQrCode($short_url)
];

echo json_encode($response);
?>