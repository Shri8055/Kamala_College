<?php
include_once('../includes/header.php');
include '../includes/db.php';
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Kamala College | Fee Dues Report</title>
<style>
  body { font-family: Inter, sans-serif; }
  select, button, input { padding: 6px; font-size: 14px; }
  table { width: 100%; border-collapse: collapse; margin-top: 15px; }
  th, td { border: 1px solid #999; padding: 6px; text-align: center; }
  th { background: #0056b3; color: white; }
  .no-print { margin-bottom: 15px; }
  .highlight { background: #fff7cc; }
  .summary-box { background: #f0f0f0; padding: 8px; font-weight: bold; width: 60%; border: 1px solid #999; }
  #searchBox { margin-left: 20px; width: 250px; }
</style>
<script>
function searchTable() {
  const input = document.getElementById('searchBox').value.toLowerCase();
  const rows = document.querySelectorAll('#duesTable tbody tr');
  let visibleCount = 0;
  rows.forEach(row => {
    const text = row.innerText.toLowerCase();
    if (text.includes(input)) {
      row.style.display = '';
      visibleCount++;
    } else {
      row.style.display = 'none';
    }
  });
  document.getElementById('countDisplay').innerText = "Total Students with Dues: " + visibleCount;
}
</script>
</head>
<body>
  <div style="padding: 20px;">
<h2 style="display:flex;justify-content:center;margin-top:10px;">💰 Fee Dues Report</h2>

<form method="GET" class="no-print">
  <label for="class">Select Class:</label>
  <select name="class" id="class" required>
    <option value="">-- Select --</option>
    <?php
    $res = $conn->query("SELECT DISTINCT student_class FROM receipts ORDER BY student_class");
    while ($row = $res->fetch_assoc()) {
        $selected = (isset($_GET['class']) && $_GET['class'] == $row['student_class']) ? 'selected' : '';
        echo "<option value='{$row['student_class']}' $selected>{$row['student_class']}</option>";
    }
    ?>
  </select>
  <button type="submit">Show</button>
</form>

<?php
if (!empty($_GET['class'])) {
    $cls = $_GET['class'];
    echo "<h3 style='display:flex;justify-content:center;margin-top:10px;'>
            <span style='padding-right:10px;'>Class:</span> <u>$cls</u></h3>";

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
    $dues = $query->get_result();

    $students = [];
    while ($row = $dues->fetch_assoc()) {
        if (floatval($row['pending_fee']) > 0.00) {
            // 🔹 Extract tuition fee info from JSON
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

            // 🔹 Fetch Roll Number
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
        $studentCount = count($students);
        echo "<div class='no-print' style='display:flex;align-items:center;'>
                <span id='countDisplay' style='font-weight:bold;'>Total Students with Dues: $studentCount</span>
                <input type='text' id='searchBox' onkeyup='searchTable()' placeholder='🔍 Search by Roll No, Name, or PRN'>
              </div>";

        echo "<table id='duesTable'>
                <thead>
                  <tr>
                    <th>Roll No</th>
                    <th>PRN</th>
                    <th>Student Name</th>
                    <th>Category</th>
                    <th>Total Fee</th>
                    <th>Tuition Fee</th>
                    <th>Pending Tuition Fee</th>
                    <th>Paid (Last)</th>
                    <th>Total Pending Fee</th>
                    <th>Concession</th>
                    <th>Concession By</th>
                    <th>Last Payment Date</th>
                  </tr>
                </thead><tbody>";

        $total_due = 0; 
        $total_paid = 0; 
        $total_concession = 0; 
        $total_tuition_due = 0;

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

        echo "<div class='summary-box'>
              👥 <b>Total Students:</b> $studentCount<br>
              💰 <b>Total Tuition Pending:</b> " . number_format($total_tuition_due, 2) . "<br>
              💰 <b>Total Fees Pending:</b> " . number_format($total_due, 2) . "<br>
              ✅ <b>Total of Last Payments:</b> " . number_format($total_paid, 2) . "<br>
              🎓 <b>Total Concession:</b> " . number_format($total_concession, 2) . "
              </div>";

        echo "<div style='margin-top:15px;'>
                <button onclick=\"window.open('print_fee_dues.php?class=" . urlencode($cls) . "', '_blank');\" 
                        style='background:#007bff;color:#fff;padding:8px 16px;border:none;cursor:pointer;border-radius: 10px; border: 1px solid #5e5e5e94;'>🖨️ Print PDF</button>
                <button onclick=\"window.open('export_fee_dues.php?class=" . urlencode($cls) . "', '_blank');\" 
                        style='background:#28a745;color:#fff;padding:8px 16px;border:none;cursor:pointer;margin-left:10px;border-radius: 10px; border: 1px solid #5e5e5e94;'>📤 Export Excel</button>
              </div>";
    } else {
        echo "<p><b>No pending dues found for this class 🎉</b></p>";
    }
}
?></div>
</body>
</html>
