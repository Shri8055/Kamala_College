<?php
include '../includes/db.php';
$cls = $_GET['class'] ?? '';
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Fee_Dues_" . str_replace(' ', '_', $cls) . ".xls");

echo "<table border='1'>
<tr style='background:#cfe2ff; font-weight:bold;'>
<th>PRN</th><th>Student Name</th><th>Category</th>
<th>Total Fee</th><th>Paid (Last)</th><th>Pending Fee</th><th>Concession</th><th>Concession By</th><th>Last Payment Date</th>
</tr>";

$query = $conn->prepare("
    SELECT 
        r1.student_prn, r1.student_name, r1.category,
        (SELECT MAX(total_fee) FROM receipts r2 WHERE r2.student_prn = r1.student_prn) AS total_fee,
        r1.receipt_amount, r1.pending_fee, r1.concession_amt, r1.concession_by, r1.receipt_date
    FROM receipts r1
    INNER JOIN (
        SELECT student_prn, MAX(receipt_id) AS latest_receipt
        FROM receipts WHERE student_class = ? GROUP BY student_prn
    ) AS latest ON r1.student_prn = latest.student_prn AND r1.receipt_id = latest.latest_receipt
    WHERE r1.student_class = ? AND r1.pending_fee > 0
    ORDER BY r1.student_name ASC
");
$query->bind_param("ss", $cls, $cls);
$query->execute();
$res = $query->get_result();

$total_due = $total_paid = $total_concession = 0;
$count = $res->num_rows;

while ($r = $res->fetch_assoc()) {
    $total_due += $r['pending_fee'];
    $total_paid += $r['receipt_amount'];
    $total_concession += $r['concession_amt'];
    echo "<tr>
          <td>{$r['student_prn']}</td>
          <td>{$r['student_name']}</td>
          <td>{$r['category']}</td>
          <td>" . number_format($r['total_fee'], 2) . "</td>
          <td>" . number_format($r['receipt_amount'], 2) . "</td>
          <td>" . number_format($r['pending_fee'], 2) . "</td>
          <td>" . number_format($r['concession_amt'], 2) . "</td>
          <td>{$r['concession_by']}</td>
          <td>" . date('d-m-Y', strtotime($r['receipt_date'])) . "</td>
          </tr>";
}

echo "<tr style='font-weight:bold;background:#f0f0f0;'>
<td colspan='9'>
👥 Total Students: $count |
💰 Total Pending Fees: " . number_format($total_due, 2) . " |
✅ Total of Last Payments: " . number_format($total_paid, 2) . " |
🎓 Total Concession: " . number_format($total_concession, 2) . "
</td></tr>";
echo "</table>";
?>
