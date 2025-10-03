<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../inc/auth.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Ensure only inventory can generate these PDFs
require_role(2);

// Get batch ID
$batch_id = isset($_GET['batch_id']) ? (int)$_GET['batch_id'] : 0;
if ($batch_id <= 0) {
    die("Batch ID is required.");
}

// fetch batch + medicine info
$stmt = $pdo->prepare("
    SELECT b.*, m.generic_name, m.brand_name, m.unit
    FROM medicine_batches b
    JOIN medicines m ON m.id = b.medicine_id
    WHERE b.id = ?
");
$stmt->execute([$batch_id]);
$batch = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$batch) {
    die("Batch not found.");
}

// detect transaction column name
$ttCol = (function ($pdo) {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `stock_transactions` LIKE 'transaction_type'");
    $stmt->execute();
    if ($stmt->fetch()) return 'transaction_type';
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `stock_transactions` LIKE 'type'");
    $stmt->execute();
    return $stmt->fetch() ? 'type' : 'transaction_type';
})($pdo);

// fetch transactions ascending
$stmt = $pdo->prepare("SELECT * FROM stock_transactions WHERE batch_id = ? ORDER BY date ASC, id ASC");
$stmt->execute([$batch_id]);
$txs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// compute running stock
$running = 0;
$rows = [];
foreach ($txs as $t) {
    $tt = strtoupper($t[$ttCol] ?? $t['type'] ?? $t['transaction_type'] ?? '');
    if ($tt === 'IN') {
        $running += (int)$t['quantity'];
    } else {
        $running -= (int)$t['quantity'];
    }
    $t['running'] = $running;
    $rows[] = $t;
}
$currentStock = $running;

// expiry classification
$expiryDate = new DateTime($batch['expiry_date']);
$today = new DateTime();
$diffDays = (int)$today->diff($expiryDate)->format('%r%a'); // negative if expired

$rowClass = '';
if ($diffDays < 0) {
    $rowClass = 'expired'; // expired = red
} elseif ($diffDays <= 60) {
    $rowClass = 'near-expiry'; // near expiry = orange
}

$html = '
<style>
body {
    font-family: Arial, sans-serif;
    margin: 40px;
    font-size: 12px;
}

/* Header */
.header {
    margin-bottom: 20px;
}
.header img {
    float: right;
    max-height: 40px;
    margin-left: 10px;
}
.header h2 {
    margin-top: 0;
    font-size: 24px;
    color: #b71c1c;
}

/* Section titles (including PRODUCT) */
h3 {
    margin-top: 25px;
    margin-bottom: 8px;
    color: #b71c1c;
    border-left: 5px solid #b71c1c; /* red bar on left */
    padding-left: 8px;
    font-size: 16px; /* bigger for PRODUCT line */
}

/* Info paragraph */
p.info {
    margin: 5px 0;
}

/* Table styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 10px 0;
}
th, td {
    border: 1px solid #000;
    padding: 6px;
    text-align: center;
}
th {
    background-color: #f2f2f2;
    color: #333;
}

/* Row highlights */
.expired { background-color: #fdecea; }     /* soft red */
.near-expiry { background-color: #fff7e6; } /* soft orange */

/* Report date */
.report-date {
    text-align: right;
    font-style: italic;
    margin-top: 20px;
    font-size: 11px;
}
</style>

<div class="header">
    <img src="http://localhost/maconglomo_app/public/images/logo.png" alt="Logo">
    <h2>Stock Card</h2>
    <div style="clear: both;"></div>
</div>

<h3>PRODUCT: ' . htmlspecialchars($batch['generic_name']) . ' (' . htmlspecialchars($batch['brand_name']) . ')</h3>

<p class="info">
Unit: ' . htmlspecialchars($batch['unit'] ?? '') . ' <span style="color:#b71c1c; padding: 0 5px;">|</span> 
Batch #: ' . htmlspecialchars($batch['batch_no'] ?? '') . ' <span style="color:#b71c1c; padding: 0 5px;">|</span> 
Expiry: <strong>' . htmlspecialchars($batch['expiry_date'] ?? '') . '</strong> <span style="color:#b71c1c; padding: 0 5px;">|</span> 
Current Stock: <strong>' . $currentStock . '</strong> <span style="color:#b71c1c; padding: 0 5px;">|</span> 
Cost/Unit: <strong>P' . number_format($batch['cost_per_unit'] ?? 0, 2) . '</strong>
</p>

<table>
<thead>
<tr>
    <th>Date</th>
    <th>Received (IN)</th>
    <th>Sale (OUT)</th>
    <th>Stock on Hand</th>
</tr>
</thead>
<tbody>
';



if (empty($rows)) {
    $html .= '<tr><td colspan="4" class="text-center">No transactions for this batch.</td></tr>';
} else {
    foreach ($rows as $t) {
        $tt = strtoupper($t[$ttCol] ?? $t['type'] ?? $t['transaction_type'] ?? '');
        $html .= '<tr class="' . $rowClass . '">
            <td>' . htmlspecialchars($t['date'] ?? $t['created_at'] ?? '') . '</td>
            <td>' . ($tt === 'IN' ? 'Qty: ' . (int)$t['quantity'] . (isset($t['cost']) ? '<br>Cost: ' . number_format($t['cost'], 2) : '') : '') . '</td>
            <td>' . ($tt === 'OUT' ? (isset($t['invoice_no']) ? 'Invoice: ' . htmlspecialchars($t['invoice_no']) . '<br>' : '') .
            (isset($t['customer']) ? 'Customer: ' . htmlspecialchars($t['customer']) . '<br>' : '') .
            'Qty: ' . (int)$t['quantity'] .
            (isset($t['price']) ? '<br>Price: ' . number_format($t['price'], 2) : '')
            : '') . '</td>
            <td>' . (int)$t['running'] . '</td>
        </tr>';
    }
}

// Set timezone to Manila
date_default_timezone_set('Asia/Manila');

$html .= '
</tbody>
</table>
<p class="report-date" style="text-align: right;">Report generated on: ' . date("M j, Y, g:i a") . '</p>
';

// Dompdf setup
$options = new Options();
$options->set("isHtml5ParserEnabled", true);
$options->set("isRemoteEnabled", true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper("A4", "portrait");
$dompdf->render();

// Ensure $batch is defined and has the necessary keys
$medicineName = isset($batch['generic_name']) ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $batch['generic_name']) : 'UnknownMedicine';
$batchNo     = isset($batch['batch_no']) ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $batch['batch_no']) : 'UnknownBatch';
$todayDate   = date('Y-m-d'); // current date in Manila timezone

// Dynamic filename
$filename = "StockCard_{$medicineName}_Batch{$batchNo}_{$todayDate}.pdf";

// Output PDF inline
$dompdf->stream($filename, ["Attachment" => false]);
