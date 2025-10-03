<?php
date_default_timezone_set('Asia/Manila'); // Manila timezone

require_once __DIR__ . '/../inc/auth.php';   // 🔹 Add this line
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Ensure only admin can generate these PDFs
require_role(1);


// ✅ correct: get medrep id from URL parameter
$medrepId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$medrepId) die("Invalid medrep.");

// Fetch Medrep name
$stmtUser = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
$stmtUser->execute([$medrepId]);
$medrep = $stmtUser->fetchColumn() ?: 'Unknown Medrep';

// Optional: filter date
$filterDate = isset($_GET['date']) && !empty($_GET['date']) ? $_GET['date'] : null;

// Fetch call logs
$sql = "
    SELECT id, date, client_name, precall_notes, postcall_notes, created_at
    FROM call_logs
    WHERE medrep_id = ?
";
$params = [$medrepId];

if ($filterDate) {
    $sql .= " AND date = ?";
    $params[] = $filterDate;
}

$sql .= " ORDER BY date DESC, id DESC LIMIT 30";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Determine if we should show Date column
$showDateColumn = !$filterDate;

// Start HTML
$html = '
<style>
body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
h1 { text-align: center; color: #b71c1c; font-size: 26px; margin: 0 0 10px 0; }

/* Header with logo on top-right */
.header {
    margin-bottom: 20px;
}
.header img {
    float: right;
    max-height: 50px;
    margin-left: 10px;
}
.header h2 {
    margin: 0;
    font-size: 20px;
    color: #b71c1c;
}
.header-info {
    font-weight: bold;
    color: #b71c1c;
    font-size: 14px;
}
table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: auto; word-wrap: break-word; }
th, td { border: 1px solid #000; padding: 6px; vertical-align: top; text-align: left; }
th { background-color: #f2f2f2; color: #333; }
.report-date { margin-top: 30px; font-style: italic; font-size: 11px; text-align: right; }
</style>

<div class="header">
    <img src="http://localhost/maconglomo_app/public/images/logo.png" alt="Logo">
    <h2>Pre/Post Call Logs</h2>
    <div class="header-info">Medrep: ' . htmlspecialchars($medrep) . '</div>
    <div class="header-info">' . ($filterDate ? 'Date Covered: ' . htmlspecialchars($filterDate) : 'Last 30 Days') . '</div>
    <div style="clear: both;"></div>
</div>

<table>
<thead>
<tr>';
if ($showDateColumn) $html .= '<th style="width: 12%;">Date</th>';
$html .= '
    <th style="width: 20%;">Client</th>
    <th style="width: 30%;">Pre-call Notes</th>
    <th style="width: 30%;">Post-call Notes</th>
    <th style="width: 8%;">Logged At</th>
</tr>
</thead>
<tbody>
';

if (!$logs) {
    $colspan = $showDateColumn ? 5 : 4;
    $html .= '<tr><td colspan="' . $colspan . '" class="text-center">No entries found.</td></tr>';
} else {
    foreach ($logs as $row) {
        $html .= '<tr>';
        if ($showDateColumn) $html .= '<td>' . date('M j, Y', strtotime($row['date'])) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['client_name']) . '</td>
                  <td style="white-space: normal;">' . nl2br(htmlspecialchars($row['precall_notes'])) . '</td>
                  <td style="white-space: normal;">' . nl2br(htmlspecialchars($row['postcall_notes'])) . '</td>
                  <td>' . date('g:i a', strtotime($row['created_at'])) . '</td>
                  </tr>';
    }
}

$html .= '</tbody></table>';

// Generated on bottom right
$html .= '<p class="report-date">Report generated on: ' . date('M j, Y, g:i a') . '</p>';

// Dompdf setup
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Sanitize medrep name for filename
$medrepSafe = preg_replace('/[^A-Za-z0-9_\-]/', '_', $medrep);

// Dynamic filename including medrep name
$filename = 'Pre_Post_Call_Logs_' . $medrepSafe . '_' . ($filterDate ?? date('Y-m-d')) . '.pdf';

// Output PDF inline
$dompdf->stream($filename, ['Attachment' => false]);
