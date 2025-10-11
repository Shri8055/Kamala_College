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
  body { font-family: Inter, sans-serif; margin: 20px; }
  select, button, input { padding: 6px; font-size: 14px; }
  table { width: 100%; border-collapse: collapse; margin-top: 15px; }
  th, td { border: 1px solid #999; padding: 6px; text-align: center; }
  th { background: #0056b3; color: white; }
  .no-print { margin-bottom: 15px; }
  .highlight { background: #fff7cc; }
  .summary-box { background: #f0f0f0; padding: 8px; font-weight: bold; width: 45%; border: 1px solid #999; }
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
<h2 style="Display: flex; justify-content: center; margin-top: 10px;">💰 Fee Dues Report</h2>

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
    echo "<h3 style='Display: flex; justify-content: center; margin-top: 10px;'><span style='padding-right: 10px;'>Class: </span>  <u> $cls</u></h3>";

    // ✅ Fetch latest receipt per student + true total_fee (MAX)
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
            r1.receipt_date
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
            $students[] = $row;
        }
    }

    if (count($students) > 0) {
        $studentCount = count($students);
        echo "<div class='no-print' style='display:flex;align-items:center;'>
                <span id='countDisplay' style='font-weight:bold;'>Total Students with Dues: $studentCount</span>
                <input type='text' id='searchBox' onkeyup='searchTable()' placeholder='🔍 Search by Name or PRN'>
              </div>";

        echo "<table id='duesTable'>
                <thead>
                  <tr>
                    <th>PRN</th>
                    <th>Student Name</th>
                    <th>Category</th>
                    <th>Total Fee</th>
                    <th>Paid (Last)</th>
                    <th>Pending Fee</th>
                    <th>Concession</th>
                    <th>Concession By</th>
                    <th>Last Payment Date</th>
                  </tr>
                </thead><tbody>";

        $total_due = 0; $total_paid = 0; $total_concession = 0;
        foreach ($students as $r) {
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
                    <td>" . date('d-m-Y', strtotime($r['receipt_date'])) . "</td>
                  </tr>";
        }

        echo "</tbody></table>";

        echo "<div class='summary-box' style='margin-top:15px;'>
              👥 <b>Total Students:</b> $studentCount<br>
              💰 <b>Total Pending Fees:</b> " . number_format($total_due, 2) . "<br>
              ✅ <b>Total of Last Payments:</b> " . number_format($total_paid, 2) . "<br>
              🎓 <b>Total Concession:</b> " . number_format($total_concession, 2) . "
              </div>";

        echo "<div style='margin-top:15px;'>
                <button onclick=\"window.open('print_fee_dues.php?class=" . urlencode($cls) . "', '_blank');\" 
                        style='background:#007bff;color:#fff;padding:8px 16px;border:none;cursor:pointer;'>🖨️ Print PDF</button>
                <button onclick=\"window.open('export_fee_dues.php?class=" . urlencode($cls) . "', '_blank');\" 
                        style='background:#28a745;color:#fff;padding:8px 16px;border:none;cursor:pointer;margin-left:10px;'>📤 Export Excel</button>
              </div>";
    } else {
        echo "<p><b>No pending dues found for this class 🎉</b></p>";
    }
}
?>
</body>
</html>
