<?php
include_once('../includes/header.php');
include '../includes/db.php';
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Kamala College | Caste-wise Report</title>
<style>
  body { font-family: Inter, sans-serif;}
  select, button { padding: 6px; font-size: 14px; }
  table { width: 60%; border-collapse: collapse; margin-top: 15px; }
  th, td { border: 1px solid #999; padding: 6px; text-align: center; }
  th { background: #0056b3; color: white; }
  .summary-box { background: #f0f0f0; padding: 8px; font-weight: bold; width: 45%; border: 1px solid #999; margin-top: 15px; }
</style>
</head>
<body>
  <div style="padding: 20px;">
<h2 style="Display: flex; justify-content: center; margin-top: 10px;">📊 Caste-wise Student Report</h2>

<form method="GET">
  <label for="class">Select Class:</label>
  <select name="class" id="class" required>
    <option value="">-- Select --</option>
    <?php
    // same as other reports — fetch distinct classes from roll_call
    $res = $conn->query("SELECT DISTINCT student_class FROM roll_call ORDER BY student_class");
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

    if ($res->num_rows > 0) {
        $grandTotal = 0;
        echo "<table>
                <thead>
                  <tr><th>Category</th><th>Student Count</th></tr>
                </thead>
                <tbody>";
        while ($r = $res->fetch_assoc()) {
            echo "<tr>
                    <td>{$r['student_category']}</td>
                    <td>{$r['total_students']}</td>
                  </tr>";
            $grandTotal += $r['total_students'];
        }
        echo "<tr style='font-weight:bold;background:#f0f0f0;'>
                <td>Total Students</td>
                <td>{$grandTotal}</td>
              </tr>";
        echo "</tbody></table>";

        echo "<div class='summary-box'>
                👥 <b>Total Students:</b> {$grandTotal}
              </div>";

        // Print & Export buttons
        echo "<div style='margin-top:15px;'>
                <button onclick=\"window.open('print_castewise.php?class=" . urlencode($cls) . "', '_blank');\" 
                        style='background:#007bff;color:#fff;padding:8px 16px;border:none;cursor:pointer;border-radius: 10px; border: 1px solid #5e5e5e94;'>🖨️ Print PDF</button>
                <button onclick=\"window.open('export_castewise.php?class=" . urlencode($cls) . "', '_blank');\" 
                        style='background:#28a745;color:#fff;padding:8px 16px;border:none;cursor:pointer;margin-left:10px;border-radius: 10px; border: 1px solid #5e5e5e94;'>📤 Export Excel</button>
              </div>";
    } else {
        echo "<p><b>No students found for this class.</b></p>";
    }
}
?></div>
</body>
</html>
