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
  font-size: 11px;
  margin: 20px;
}
table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
}
th, td {
  border: 1px solid #000;
  padding: 4px;
  text-align: center;
}
th {
  background: #dbe7ff;
}
h2 {
  text-align: center;
  font-size: 14px;
  margin-bottom: 8px;
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
        r1.student_prn,
        r1.student_name,
        r1.category,
        (SELECT MAX(total_fee) FROM receipts r2 WHERE r2.student_prn = r1.student_prn) AS total_fee,
        r1.receipt_amount,
        r1.pending_fee,
        r1.concession_amt,
        r1.concession_by,
        r1.receipt_date,
        r1.fee_particulars
    FROM receipts r1
    INNER JOIN (
        SELECT student_prn, MAX(receipt_id) AS latest_receipt
        FROM receipts
        WHERE student_class = ?
        GROUP BY student_prn
    ) AS latest ON r1.student_prn = latest.student_prn AND r1.receipt_id = latest.latest_receipt
    WHERE r1.student_class = ?
    ORDER BY r1.student_name ASC
");
$query->bind_param("ss", $cls, $cls);
$query->execute();
$res = $query->get_result();

$total_due = $total_paid = $total_concession = $total_tuition_due = 0;
$students = [];

while ($row = $res->fetch_assoc()) {
    if (floatval($row['pending_fee']) > 0.00) {
        // 🔹 Extract Tuition Fee and Pending Tuition Fee
        $tuitionFee = 0;
        $tuitionPaid = 0;
        $json = json_decode($row['fee_particulars'], true);
        if (is_array($json)) {
            foreach ($json as $fee) {
                $short = strtolower($fee['sh_nm'] ?? '');
                $full  = strtolower($fee['fl_nm'] ?? '');
                if (strpos($short, 'tuti') !== false || strpos($full, 'tution') !== false || strpos($full, 'tuition') !== false) {
                    $tuitionFee += floatval($fee['amount']);
                    $tuitionPaid += floatval($fee['paid']);
                }
            }
        }
        $pendingTuition = $tuitionFee - $tuitionPaid;

        // 🔹 Fetch Roll No
        $roll = $conn->prepare("SELECT roll_no FROM roll_call WHERE prn = ? AND student_class = ? LIMIT 1");
        $roll->bind_param("ss", $row['student_prn'], $cls);
        $roll->execute();
        $rollRes = $roll->get_result()->fetch_assoc();
        $rollNo = $rollRes['roll_no'] ?? '-';

        $row['roll_no'] = $rollNo;
        $row['tution_fee'] = $tuitionFee;
        $row['pending_tution'] = $pendingTuition;
        $students[] = $row;
    }
}

if (count($students) > 0) {
    echo "<table><thead><tr>
          <th>Roll No</th><th>PRN</th><th>Student Name</th><th>Category</th>
          <th>Total Fee</th><th>Tuition Fee</th><th>Pending Tuition Fee</th>
          <th>Paid (Last)</th><th>Total Pending Fee</th>
          <th>Concession</th><th>Concession By</th><th>Last Payment Date</th>
          </tr></thead><tbody>";

    foreach ($students as $r) {
        $total_due += $r['pending_fee'];
        $total_paid += $r['receipt_amount'];
        $total_concession += $r['concession_amt'];
        $total_tuition_due += $r['pending_tution'];

        echo "<tr" . ($r['concession_amt'] > 0 ? " class='highlight'" : "") . ">
              <td>{$r['roll_no']}</td>
              <td>{$r['student_prn']}</td>
              <td>{$r['student_name']}</td>
              <td>{$r['category']}</td>
              <td>" . number_format($r['total_fee'], 2) . "</td>
              <td>" . number_format($r['tution_fee'], 2) . "</td>
              <td>" . number_format($r['pending_tution'], 2) . "</td>
              <td>" . number_format($r['receipt_amount'], 2) . "</td>
              <td><b>" . number_format($r['pending_fee'], 2) . "</b></td>
              <td>" . number_format($r['concession_amt'], 2) . "</td>
              <td>{$r['concession_by']}</td>
              <td>" . date('d-m-Y', strtotime($r['receipt_date'])) . "</td>
              </tr>";
    }

    echo "</tbody></table>";

    echo "<p style='margin-top:10px;font-weight:bold;text-align:center;'>
          👥 Total Students: " . count($students) . "<br>
          💰 Total Tuition Pending: " . number_format($total_tuition_due, 2) . "<br>
          💰 Total Fees Pending: " . number_format($total_due, 2) . "<br>
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
