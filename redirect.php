<meta name="robots" content="noindex, nofollow">
<?php
// redirect.php
require 'config.php';

if (!isset($_GET['code'])) {
    die("No code provided.");
}

$short_code = $_GET['code'];

// Retrieve the record
$stmt = $pdo->prepare("SELECT original_url FROM urls WHERE short_code = ?");
$stmt->execute([$short_code]);
$row = $stmt->fetch();

if ($row) {
    // Decrypt the original URL
    $original_url = decryptData($row['original_url']);

    // Increment the visit count (assuming 'visit_count' exists in your table)
    $stmt = $pdo->prepare("UPDATE urls SET visit_count = visit_count + 1 WHERE short_code = ?");
    $stmt->execute([$short_code]);

    // Redirect the visitor
    header("Location: " . $original_url);
    exit;
} else {
    die("Invalid URL.");
}
?>