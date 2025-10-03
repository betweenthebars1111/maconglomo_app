<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../inc/auth.php';


use Dompdf\Dompdf;
use Dompdf\Options;

// Ensure only admin can generate these PDFs
require_role(1);

/**
 * Fetch inventory data
 */
$query = "
    SELECT 
        m.generic_name, 
        m.brand_name, 
        m.unit, 
        b.batch_no, 
        b.expiry_date, 
        b.quantity
    FROM medicine_batches b
    JOIN medicines m ON b.medicine_id = m.id
    ORDER BY m.generic_name ASC, b.expiry_date ASC
";
$stmt = $pdo->query($query);
$allBatches = $stmt->fetchAll(PDO::FETCH_ASSOC);

/**
 * Categorize batches by expiry
 */
$today      = strtotime('today');
$oneYearOut = strtotime('+1 year', $today);

$expired    = [];
$nearExpiry = [];
$valid      = [];

foreach ($allBatches as $row) {
    $expiryDate = strtotime($row['expiry_date']);
    if ($expiryDate < $today) {
        $expired[] = $row;
    } elseif ($expiryDate <= $oneYearOut) {
        $nearExpiry[] = $row;
    } else {
        $valid[] = $row;
    }
}

/**
 * CSS and header
 */
$html = '
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 40px;
        font-size: 12px;
    }

    /* Header with logo on top-right and title below */
    .header {
        margin-bottom: 20px;
    }

    .header img {
        float: right;
        max-height: 40px; /* smaller logo */
        margin-left: 10px;
    }

    .header h2 {
        margin-top: 0;
        font-size: 24px;
        color: #b71c1c;
    }

    /* Section titles */
    h3 {
        margin-top: 25px;
        margin-bottom: 8px;
        color: #b71c1c;
        border-left: 5px solid #b71c1c;
        padding-left: 8px;
        font-size: 14px;
    }

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

    .report-date {
        text-align: left;
        font-style: italic;
        margin-top: 20px;
        font-size: 11px;
    }
</style>

<div class="header">
    <img src="http://localhost/maconglomo_app/public/images/logo.png" alt="Logo">
    <h2>Inventory Report</h2>
    <div style="clear: both;"></div>
</div>
';

/**
 * Helper to render a table
 */
function renderTable(array $rows, string $title): string
{
    if (empty($rows)) {
        return "<h3>{$title}</h3><p>No records found.</p>";
    }

    $html = "<h3>{$title}</h3>
    <table>
        <thead>
            <tr>
                <th>Generic Name</th>
                <th>Brand Name</th>
                <th>Unit</th>
                <th>Batch No.</th>
                <th>Expiry Date</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>";

    foreach ($rows as $row) {
        $expiryDate   = strtotime($row['expiry_date']);
        $formattedDate = date("M j, Y", $expiryDate);

        $rowClass = '';
        if ($expiryDate < time()) {
            $rowClass = 'expired';
        } elseif ($expiryDate <= strtotime('+1 year')) {
            $rowClass = 'near-expiry';
        }

        $html .= sprintf(
            '<tr class="%s">
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
            </tr>',
            $rowClass,
            htmlspecialchars($row['generic_name']),
            htmlspecialchars($row['brand_name']),
            htmlspecialchars($row['unit']),
            htmlspecialchars($row['batch_no']),
            $formattedDate,
            htmlspecialchars($row['quantity'])
        );
    }

    $html .= '</tbody></table>';
    return $html;
}

/**
 * Render sections
 */
$html .= renderTable($valid, "Valid Inventory");
$html .= renderTable($nearExpiry, "Near Expiry (within 1 year)");
$html .= renderTable($expired, "Expired");

/**
 * Report date (right-aligned and italic)
 */

// Set timezone to Manila
date_default_timezone_set('Asia/Manila');

$html .= '<p class="report-date" style="text-align: right; font-style: italic; font-size: 11px;">Report generated on: ' . date("M j, Y, g:i a") . '</p>';

/**
 * Setup Dompdf
 */
$options = new Options();
$options->set("isHtml5ParserEnabled", true);
$options->set("isRemoteEnabled", true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper("A4", "portrait");
$dompdf->render();

/**
 * Dynamic filename
 */
$date = date('Y-m-d'); // current date
$filename = "Inventory_Report_{$date}.pdf";

/**
 * Output PDF inline (preview)
 */
$dompdf->stream($filename, ["Attachment" => false]);
