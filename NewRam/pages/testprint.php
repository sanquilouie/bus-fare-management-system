<?php
require_once '../libraries/vendor/autoload.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

// Replace this with your printer share name
$connector = new WindowsPrintConnector("POS58");

$printer = new Printer($connector);

$printer->text("Hello, printer!\n");
$printer->cut();
$printer->close();
?>