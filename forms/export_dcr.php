<?php
include "../includes/db.php";

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

if (empty($from) || empty($to)) {
    die("Please select a date range first.");
}

// Fetch data
$stmt = $conn->prepare("SELECT * FROM receipts WHERE receipt_date BETWEEN ? AND ? ORDER BY receipt_date ASC, receipt_id ASC");
$stmt->bind_param("ss", $from, $to);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    die("No records found in this range.");
}

// Build arrays
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
        'utr_no' => $r['utr_no'],
        'concession_by' => $r['concession_by'] ?? '',
        'concession_amt' => isset($r['concession_amt']) ? floatval($r['concession_amt']) : 0,
        'heads' => $map
    ];
}

sort($allHeads);

// Prepare Excel file headers
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Daily_Collection_Register_" . date('Ymd_His') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Start Excel output (HTML table compatible with Excel)
echo "<table border='1'>";
echo "<tr><th colspan='" . (count($allHeads) + 6) . "' style='font-size:16px;'>Daily Collection Register (BCA)</th></tr>";
echo "<tr><th colspan='" . (count($allHeads) + 6) . "'>Session: 2025-2026 | From: " . date('d/m/Y', strtotime($from)) . " To: " . date('d/m/Y', strtotime($to)) . "</th></tr>";

foreach ($grouped as $date => $students) {
    echo "<tr><th colspan='" . (count($allHeads) + 6) . "' style='background:#d9ead3;'>Date: " . date('d/m/Y', strtotime($date)) . "</th></tr>";

    // Header row
    echo "<tr>
            <th>Student Name</th>
            <th>PRN</th>
            <th>Class</th>
            <th>Receipt No</th>";

    foreach ($allHeads as $head) {
        echo "<th>" . htmlspecialchars($head) . "</th>";
    }

    echo "<th>Total</th><th>Pending</th><th>Payment Type</th><th>Concession</th><th>Ref No</th></tr>";

    // Data rows
    $dateTotals = array_fill_keys($allHeads, 0.0);
    $dateGrand = 0.0;

    foreach ($students as $s) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($s['student_name']) . "</td>";
        echo "<td>" . htmlspecialchars($s['student_prn']) . "</td>";
        echo "<td>" . htmlspecialchars($s['student_class']) . "</td>";
        echo "<td>" . htmlspecialchars($s['receipt_no']) . "</td>";

        foreach ($allHeads as $h) {
            $val = isset($s['heads'][$h]) ? $s['heads'][$h] : '';
            if ($val) $dateTotals[$h] += $val;
            echo "<td>" . ($val !== '' ? number_format($val, 2) : '') . "</td>";
        }

        $utrDisplay = '-';
        if (!empty(trim($s['utr_no']))) $utrDisplay = htmlspecialchars(trim($s['utr_no']));

        echo "<td>" . number_format($s['receipt_amount'], 2) . "</td>";
        echo "<td>" . number_format($s['pending_fee'], 2) . "</td>";
        echo "<td>" . htmlspecialchars($s['payment_type']) . "</td>";
        echo "<td>" . htmlspecialchars($s['concession_by']) ." - ". htmlspecialchars($s['concession_amt']) ."</td>";
        echo "<td>" . $utrDisplay . "</td>";
        echo "</tr>";

        $dateGrand += $s['receipt_amount'];
    }

    // Date totals
    echo "<tr style='background:#f9cb9c; font-weight:bold;'><td colspan='4'>Date Total</td>";
    foreach ($allHeads as $h) {
        echo "<td>" . number_format($dateTotals[$h], 2) . "</td>";
    }
    echo "<td>" . number_format($dateGrand, 2) . "</td><td colspan='3'></td></tr>";
}

echo "</table>";
exit;
?>
