<?php
include "../includes/db.php";
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

if (empty($from) || empty($to)) {
    die("<p>No date range provided.</p>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Print Daily Collection Register</title>
<style>
  @page { size: A4 landscape; margin: 10mm; }
  body { font-family: "Calibri", "Inter", sans-serif; font-size: 10px; color: #000; margin: 0; padding: 10px; }
  .report-header { text-align: center; margin-bottom: 8px; }
  .report-header h1 { margin: 0; font-size: 14px; font-weight: bold; }
  .report-header h2 { margin: 0; font-size: 12px; text-decoration: underline; }
  .meta { text-align: center; font-size: 10px; margin-bottom: 6px; }
  table { width: 100%; border-collapse: collapse; font-size: 9px; table-layout: fixed; word-wrap: break-word; margin-bottom: 8px; }
  th, td { border: 1px solid #000; padding: 3px; vertical-align: middle; text-align: center; }
  thead th { background: #f2f2f2; font-weight: bold; }
  .student-info { text-align: left; background: #eaf6ff; font-weight: bold; padding: 4px; }
  .bank-line { text-align: left; padding: 4px; font-size: 9px; border: 1px solid #000; }
  .date-header { background: #f0f0f0; padding: 4px; font-weight: bold; border: 1px solid #000; margin-top: 10px; }
  .date-total { background: #e8f5e9; font-weight: bold; }
  .total-right { text-align: center; }
  thead { display: table-header-group; } /* Repeat header on new page */
  .page-break { page-break-after: always; }
</style>
</head>
<body onload="window.print()">

<div class="report-header">
  <h1>TARARANI VIDYAPEETH’S KAMALA COLLEGE, KOLHAPUR</h1>
  <h2>Detailed Daily Collection Register</h2>
</div>
<div class="meta">
  <strong>Session:</strong> 2025–2026 |
  <strong>Receipt Date From:</strong> <?= date('d/m/Y', strtotime($from)) ?> –
  <strong>To:</strong> <?= date('d/m/Y', strtotime($to)) ?>
</div>

<?php
// Fetch all receipts in range
$stmt = $conn->prepare("SELECT * FROM receipts WHERE receipt_date BETWEEN ? AND ? ORDER BY receipt_date ASC, receipt_id ASC");
$stmt->bind_param("ss", $from, $to);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "<p>No receipts found between these dates.</p>";
    exit;
}

$grouped = [];
$allHeads = [];
while ($r = $res->fetch_assoc()) {
    $date = $r['receipt_date'];
    $fees = json_decode($r['fee_particulars'], true);
    $map = [];
    if (is_array($fees)) {
        foreach ($fees as $f) {
            $h = strtoupper(trim($f['fl_nm']));
            $paid = floatval($f['paid'] ?? 0);
            $map[$h] = ($map[$h] ?? 0) + $paid;
            if (!in_array($h, $allHeads)) $allHeads[] = $h;
        }
    }
    $grouped[$date][] = [
        'student_name' => $r['student_name'],
        'student_prn' => $r['student_prn'],
        'student_class' => $r['student_class'],
        'receipt_no' => $r['receipt_no'],
        'fee_type' => strtoupper($r['fee_type']),
        'receipt_amount' => floatval($r['receipt_amount']),
        'pending_fee' => floatval($r['pending_fee']),
        'payment_type' => $r['payment_type'],
        'heads' => $map
    ];
}

sort($allHeads);
$totalHeads = count($allHeads);
$headsPerRow = 10;
$headerRows = ceil($totalHeads / $headsPerRow);
$colWidth = 100 / 12; // 12 even columns

foreach ($grouped as $date => $students) {
    echo "<div class='page-break'>";
    echo "<div class='date-header'>Date: " . date('d/m/Y', strtotime($date)) . "</div>";

    echo "<table>";
    echo "<colgroup>";
    echo "<col style='width:{$colWidth}%'>";
    for ($i = 0; $i < 10; $i++) echo "<col style='width:{$colWidth}%'>";
    echo "<col style='width:{$colWidth}%'>";
    echo "</colgroup>";

    // Fee head stack (multi-row header)
    echo "<thead>";
    for ($r = 0; $r < $headerRows; $r++) {
        echo "<tr>";
        if ($r == 0) echo "<th rowspan='{$headerRows}'>Student Data</th>";
        for ($i = 0; $i < $headsPerRow; $i++) {
            $idx = $r * $headsPerRow + $i;
            $head = ($idx < $totalHeads) ? htmlspecialchars($allHeads[$idx]) : '';
            echo "<th>$head</th>";
        }
        if ($r == 0) echo "<th rowspan='{$headerRows}'>Total & Pending</th>";
        echo "</tr>";
    }
    echo "</thead><tbody>";

    // Date totals
    $dateTotals = array_fill_keys($allHeads, 0.0);
    $dateGrand = 0.0;

    foreach ($students as $s) {
        // Student info (rowspan stack)
        echo "<tr>";
        echo "<td rowspan='{$headerRows}' class='student-info'>" .
             htmlspecialchars($s['student_name']) . "<br>PRN: " . htmlspecialchars($s['student_prn']) .
             "<br>Class: " . htmlspecialchars($s['student_class']) .
             "<br>Rcpt: " . htmlspecialchars($s['receipt_no']) . " | " . htmlspecialchars($s['fee_type']) . "</td>";

        // First row of fee amounts
        for ($i = 0; $i < $headsPerRow; $i++) {
            $h = $allHeads[$i] ?? '';
            $val = ($h && isset($s['heads'][$h])) ? $s['heads'][$h] : '';
            if ($h && $val) $dateTotals[$h] += $val;
            echo "<td>" . ($val !== '' ? number_format($val, 2) : '') . "</td>";
        }

        echo "<td rowspan='{$headerRows}' class='total-right'><b>" . number_format($s['receipt_amount'], 2) . "</b><br><small>Pending: " . number_format($s['pending_fee'], 2) . "</small></td>";
        echo "</tr>";

        // Remaining stacked rows
        for ($r = 1; $r < $headerRows; $r++) {
            echo "<tr>";
            for ($i = 0; $i < $headsPerRow; $i++) {
                $idx = $r * $headsPerRow + $i;
                $h = $allHeads[$idx] ?? '';
                $val = ($h && isset($s['heads'][$h])) ? $s['heads'][$h] : '';
                if ($h && $val) $dateTotals[$h] += $val;
                echo "<td>" . ($val !== '' ? number_format($val, 2) : '') . "</td>";
            }
            echo "</tr>";
        }

        // Bank summary row
        $colspan = 1 + 10 + 1;
        echo "<tr><td colspan='{$colspan}' class='bank-line'>";
        $utrDisplay = '-';
if (isset($s['utr_no']) && trim($s['utr_no']) !== '') {
    $utrDisplay = htmlspecialchars(trim($s['utr_no']));
}

// Build payment summary line
echo "<tr><td colspan='12' class='bank-line'>"
    . "Cash: " . ($s['payment_type'] == 'Cash' ? number_format($s['receipt_amount'], 2) : '0.00')
    . " | DD: " . ($s['payment_type'] == 'DD' ? number_format($s['receipt_amount'], 2) : '0.00')
    . " | UPI: " . ($s['payment_type'] == 'UPI' ? number_format($s['receipt_amount'], 2) : '0.00')
    . " | NEFT/RTGS: " . ($s['payment_type'] == 'NEFT / RTGS' ? number_format($s['receipt_amount'], 2) : '0.00')
    . " || Ref No: " . $utrDisplay
    . "</td></tr>";

        $dateGrand += $s['receipt_amount'];
    }

    // Date totals stack
    for ($r = 0; $r < $headerRows; $r++) {
        echo "<tr class='date-total'>";
        if ($r == 0) echo "<td>Date Total</td>"; else echo "<td></td>";
        for ($i = 0; $i < $headsPerRow; $i++) {
            $idx = $r * $headsPerRow + $i;
            $h = $allHeads[$idx] ?? '';
            $val = ($h) ? number_format($dateTotals[$h], 2) : '';
            echo "<td>$val</td>";
        }
        if ($r == 0) echo "<td><b>" . number_format($dateGrand, 2) . "</b></td>"; else echo "<td></td>";
        echo "</tr>";
    }

    echo "</tbody></table>";
    echo "</div>";
}
?>
</body>
</html>
