<?php
// qr_functions.php
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

/**
 * Generates a QR code data URI for a given URL.
 *
 * @param string $url   The URL to encode.
 * @param int    $size  The size of the QR code (width and height in pixels).
 * @param int    $margin The margin around the QR code.
 *
 * @return string       The data URI of the generated QR code.
 */
function generateQrCode($url) {
    $result = Builder::create()
        ->writer(new PngWriter())
        ->data($url)
        ->size(1000)
        ->margin(50)
        ->build();
    return $result->getDataUri();
}