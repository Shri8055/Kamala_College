<?php
include '../includes/db.php';
$cls = $_GET['class'] ?? '';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Castewise_Report_" . str_replace(' ', '_', $cls) . ".xls");

echo "<table border='1'>
<tr style='background:#cfe2ff; font-weight:bold;'>
<th>Category</th><th>Student Count</th>
</tr>";

$query = $conn->prepare("
    SELECT student_category, COUNT(*) AS total_students
    FROM roll_call
    WHERE student_class = ?
    GROUP BY student_category
    ORDER BY student_category ASC
");
$query->bind_param("s", $cls);
$query->execute();
$res = $query->get_result();

$total = 0;
while ($r = $res->fetch_assoc()) {
    echo "<tr>
            <td>{$r['student_category']}</td>
            <td>{$r['total_students']}</td>
          </tr>";
    $total += $r['total_students'];
}

echo "<tr style='font-weight:bold;background:#f0f0f0;'>
        <td>Total Students</td>
        <td>{$total}</td>
      </tr>";
echo "</table>";
?>
