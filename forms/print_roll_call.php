<?php
include '../includes/db.php';

if (empty($_GET['class'])) die("Class not specified.");
$cls = $_GET['class'];

$res = $conn->prepare("SELECT * FROM roll_call WHERE student_class=? ORDER BY roll_no ASC");
$res->bind_param("s", $cls);
$res->execute();
$data = $res->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<title>Roll Call - <?= htmlspecialchars($cls) ?></title>
<style>
body { font-family: Calibri, sans-serif; font-size: 13px; color: #000; margin: 20px; }
table { width: 100%; border-collapse: collapse; }
th, td { border: 1px solid #000; padding: 5px; text-align: center; }
th { background: #ddd; }
@media print {
  button { display: none; }
}
</style>
</head>
<body>
<h2 style="text-align:center;">KAMALA COLLEGE, KOLHAPUR</h2>
<h3 style="text-align:center;">Roll Call List - <?= htmlspecialchars($cls) ?></h3>
<table>
<tr>
  <th>Roll No</th><th>PRN</th><th>Student Name</th><th>Category</th><th>Mobile</th><th>Email</th>
</tr>
<?php while ($r = $data->fetch_assoc()): ?>
<tr>
  <td><?= $r['roll_no'] ?></td>
  <td><?= $r['prn'] ?></td>
  <td><?= $r['student_name'] ?></td>
  <td><?= $r['student_category'] ?></td>
  <td><?= $r['student_mob_no'] ?></td>
  <td><?= $r['student_email'] ?></td>
</tr>
<?php endwhile; ?>
</table>
<?php
// 🧾 Caste Summary
$summary = $conn->prepare("SELECT student_category, COUNT(*) AS total 
                            FROM roll_call WHERE student_class=? 
                            GROUP BY student_category ORDER BY student_category");
$summary->bind_param("s", $cls);
$summary->execute();
$summaryRes = $summary->get_result();

if ($summaryRes->num_rows > 0):
?>
<br>
<h3>Caste-wise Summary</h3>
<table style="width:50%; margin-top:5px;">
<tr><th>Category</th><th>Count</th></tr>
<?php 
$total = 0;
while ($s = $summaryRes->fetch_assoc()):
    $total += $s['total'];
?>
<tr><td><?= $s['student_category'] ?></td><td><?= $s['total'] ?></td></tr>
<?php endwhile; ?>
<tr style="font-weight:bold;"><td>Total Students</td><td><?= $total ?></td></tr>
</table>
<?php endif; ?>
<br><br>
<div style="display:flex;justify-content:space-between;">
  <span><b>Class Teacher Sign:</b> _____________________</span>
  <span><b>Principal Sign:</b> _____________________</span>
</div>
<script>
window.onload = function() {
    window.print(); // 🖨️ auto open print popup
};
</script>

</body>
</html>
