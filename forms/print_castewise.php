<?php
include '../includes/db.php';
$cls = $_GET['class'] ?? '';

header("Content-Type: text/html");
?>
<html>
<head>
<title>Caste-wise Report - <?= htmlspecialchars($cls) ?></title>
<style>
body {
  font-family: Calibri, sans-serif;
  font-size: 11px;
  margin: 20px;
}
table {
  width: 60%;
  border-collapse: collapse;
  margin: 0 auto;
  font-size: 13px;
}
th, td {
  border: 1px solid #000;
  padding: 3px;
  text-align: center;
}
th {
  background: #dbe7ff;
}
h2 {
  text-align: center;
  font-size: 14px;
}
@media print {
  button { display: none; }
}
</style>
</head>
<body onload="window.print();" oncontextmenu="return false" onkeydown="return false" onmousedown="return false">

<h2>Kamala College<br>Caste-wise Student Report<br><small><?= htmlspecialchars($cls) ?></small></h2>

<?php
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
    $total = 0;
    echo "<table><thead><tr><th>Category</th><th>Student Count</th></tr></thead><tbody>";
    while ($r = $res->fetch_assoc()) {
        echo "<tr><td>{$r['student_category']}</td><td>{$r['total_students']}</td></tr>";
        $total += $r['total_students'];
    }
    echo "<tr style='font-weight:bold;background:#f0f0f0;'>
            <td>Total Students</td><td>{$total}</td>
          </tr></tbody></table>";
    echo "<p style='text-align:center;margin-top:10px;font-weight:bold;font-size:11px;'>👥 Total Students: {$total}</p>";
} else {
    echo "<p style='text-align:center;'>No students found.</p>";
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
