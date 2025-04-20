<?php
date_default_timezone_set('Asia/Manila');
require_once '../libraries/vendor/autoload.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

try {
    // Connect to the printer
    $connector = new WindowsPrintConnector("POS60");
    $printer = new Printer($connector);

    // Test print content
    $printer->text("=== TEST PRINT ===\n");
    $printer->text("Printerrrrrr\n");
    $printer->text(date("Y-m-d H:i:s") . "\n");
    $printer->feed(3);
    $printer->cut();  

    // Close printer connection
    $printer->close();
} catch (Exception $e) {
    echo "Couldn't print to this printer: " . $e->getMessage() . "\n";
}
