<?php
include '../includes/db.php';
if (empty($_GET['class'])) die("Class not specified.");
$cls = $_GET['class'];

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Roll_Call_".str_replace(" ","_",$cls).".xls");

echo "<h2>Kamala College, Kolhapur</h2>";
echo "<h3>Roll Call List - ".htmlspecialchars($cls)."</h3>";


$res = $conn->prepare("SELECT * FROM roll_call WHERE student_class=? ORDER BY roll_no ASC");
$res->bind_param("s", $cls);
$res->execute();
$data = $res->get_result();

echo "<table border='1'>";
echo "<tr><th>Roll No</th><th>PRN</th><th>Student Name</th><th>Category</th><th>Mobile</th><th>Email</th></tr>";
while ($r = $data->fetch_assoc()) {
  echo "<tr>
        <td>{$r['roll_no']}</td>
        <td>{$r['prn']}</td>
        <td>{$r['student_name']}</td>
        <td>{$r['student_category']}</td>
        <td>{$r['student_mob_no']}</td>
        <td>{$r['student_email']}</td>
        </tr>";
}
echo "</table>";

// 🧮 Caste Summary
$summary = $conn->prepare("SELECT student_category, COUNT(*) AS total 
                            FROM roll_call WHERE student_class=? 
                            GROUP BY student_category ORDER BY student_category");
$summary->bind_param("s", $cls);
$summary->execute();
$summaryRes = $summary->get_result();

if ($summaryRes->num_rows > 0) {
    echo "<br><br><table border='1'>";
    echo "<tr><th colspan='2'>Caste-wise Summary</th></tr>";
    echo "<tr><th>Category</th><th>Count</th></tr>";
    $total = 0;
    while ($s = $summaryRes->fetch_assoc()) {
        $total += $s['total'];
        echo "<tr><td>{$s['student_category']}</td><td>{$s['total']}</td></tr>";
    }
    echo "<tr style='font-weight:bold;'><td>Total Students</td><td>{$total}</td></tr>";
    echo "</table>";
}
?>
