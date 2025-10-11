<?php
include '../includes/db.php';
$cls = $_GET['class'] ?? '';
header("Content-Type: text/html");
?>
<html>
<head>
<title>Print Fee Dues - <?= htmlspecialchars($cls) ?></title>
<style>
body {
  font-family: Calibri, sans-serif;
  font-size: 11px; /* 👈 smaller font size */
  margin: 20px;
}
table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
th, td {
  border: 1px solid #000;
  padding: 3px; /* 👈 reduced padding */
  text-align: center;
  font-size: 13px;
}
th {
  background: #dbe7ff;
  font-size: 13px;
}
h2 {
  text-align: center;
  margin-bottom: 8px;
  font-size: 14px; /* slightly smaller heading */
}
.highlight {
  background: #fff9d6;
}
@media print {
  button { display: none; }
}
</style>
</head>
<body onload="window.print();" oncontextmenu="return false" onkeydown="return false" onmousedown="return false">

<h2>Kamala College - Fee Dues Report<br><small><?= htmlspecialchars($cls) ?></small></h2>

<?php
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

if ($count > 0) {
    echo "<table><thead><tr>
          <th>PRN</th><th>Student Name</th><th>Category</th>
          <th>Total Fee</th><th>Paid (Last)</th><th>Pending</th>
          <th>Concession</th><th>By</th><th>Date</th>
          </tr></thead><tbody>";

    while ($r = $res->fetch_assoc()) {
        $total_due += $r['pending_fee'];
        $total_paid += $r['receipt_amount'];
        $total_concession += $r['concession_amt'];
        echo "<tr" . ($r['concession_amt'] > 0 ? " class='highlight'" : "") . ">
        <td>{$r['student_prn']}</td>
        <td>{$r['student_name']}</td>
        <td>{$r['category']}</td>
        <td>" . number_format($r['total_fee'], 2) . "</td>
        <td>" . number_format($r['receipt_amount'], 2) . "</td>
        <td><b>" . number_format($r['pending_fee'], 2) . "</b></td>
        <td>" . number_format($r['concession_amt'], 2) . "</td>
        <td>{$r['concession_by']}</td>
        <td>" . date('d-m-Y', strtotime($r['receipt_date'])) . "</td></tr>";
    }
    echo "</tbody></table>";

    echo "<p style='margin-top:10px;font-weight:bold;font-size:11px;'>
          👥 Total Students: $count<br>
          💰 Total Pending Fees: " . number_format($total_due, 2) . "<br>
          ✅ Total of Last Payments: " . number_format($total_paid, 2) . "<br>
          🎓 Total Concession: " . number_format($total_concession, 2) . "
          </p>";
} else {
    echo "<p>No pending dues found 🎉</p>";
}
?>
<script>
// === Disable Right-Click Context Menu ===
document.addEventListener('contextmenu', event => event.preventDefault());

// === Disable common DevTools shortcuts ===
document.addEventListener('keydown', function(e) {
    // F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C, Ctrl+U
    if (
        e.keyCode === 123 || 
        (e.ctrlKey && e.shiftKey && ['I','J','C'].includes(e.key.toUpperCase())) ||
        (e.ctrlKey && e.key.toUpperCase() === 'U')
    ) {
        e.preventDefault();
        return false;
    }
});

// === Detect DevTools open (interval check) ===
(function() {
    const element = new Image();
    Object.defineProperty(element, 'id', {
        get: function() {
            alert('⚠️ Developer tools are disabled on this page!');
            window.close();
        }
    });
    console.log(element);
})();

// === Disable text selection & copying ===
document.addEventListener('selectstart', e => e.preventDefault());
document.addEventListener('copy', e => e.preventDefault());
document.addEventListener('cut', e => e.preventDefault());
document.addEventListener('paste', e => e.preventDefault());

// === Disable drag/drop ===
document.addEventListener('dragstart', e => e.preventDefault());
document.addEventListener('drop', e => e.preventDefault());

// === Make the whole page non-editable ===
document.body.contentEditable = false;
document.designMode = "off";
</script>
</body>
</html>
