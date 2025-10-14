<?php
include_once('../includes/header.php');
include '../includes/db.php';
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Kamala College | Roll Call Management</title>
<style>
  body { font-family: Inter, sans-serif;}
  select, button { padding: 6px; font-size: 14px; }
  table { width: 100%; border-collapse: collapse; margin-top: 15px; }
  th, td { border: 1px solid #999; padding: 6px; text-align: center; }
  th { background: #0056b3; color: white; }
  .frozen { background: #e8f5e9; font-weight: bold; color: green; }
  .frozen {
  background: #e8f5e9;
  font-weight: bold;
  color: green;
  padding: 6px;
}
.unfrozen {
  background: #ffe4e4;
  font-weight: bold;
  color: red;
  padding: 6px;
}

</style>
</head>
<body>
    <div style="padding: 20px;">
<h2 style="Display: flex; justify-content: center; margin-top: 10px;">🎓 Roll Call Management</h2>

<form method="GET">
  <label for="class">Select Class:</label>
  <select name="class" id="class" required>
    <option value="">-- Select --</option>
    <?php
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

    // Check freeze status
    $status = $conn->prepare("SELECT is_frozen FROM roll_call_status WHERE student_class=? LIMIT 1");
    $status->bind_param("s", $cls);
    $status->execute();
    $isFrozen = $status->get_result()->fetch_assoc()['is_frozen'] ?? 0;

    echo "<h3 style='Display: flex; justify-content: center; margin-top: 10px;'><span style='padding-right: 10px;'>Class: </span>  <u> $cls</u></h3>";
    echo $isFrozen ? "<p class='frozen'>✅ Admission Frozen</p>" : "<p class='unfrozen'>⚠️ Admission Open</p>";

    $info = $conn->prepare("SELECT freeze_count, frozen_on FROM roll_call_status WHERE student_class=? LIMIT 1");
$info->bind_param("s", $cls);
$info->execute();
$statusRow = $info->get_result()->fetch_assoc();
if ($statusRow) {
    echo "<p><b>Last Frozen:</b> " . date('d-m-Y H:i', strtotime($statusRow['frozen_on'])) . " | 
          <b>Times Frozen:</b> " . intval($statusRow['freeze_count']) . "</p>";
}


    // Show roll call
    $result = $conn->prepare("SELECT * FROM roll_call WHERE student_class = ? ORDER BY roll_no ASC");
    $result->bind_param("s", $cls);
    $result->execute();
    $rolls = $result->get_result();

    if ($rolls->num_rows > 0) {
        echo "<table><thead>
              <tr><th>Roll No</th><th>ABC Id</th><th>PRN</th><th>Student Name</th><th>Category</th><th>Mobile</th><th>Email</th></tr></thead><tbody>";
        while ($r = $rolls->fetch_assoc()) {
            echo "<tr><td>{$r['roll_no']}</td>
                      <td>{$r['abc_id']}</td>
                      <td>{$r['prn']}</td>
                      <td>{$r['student_name']}</td>
                      <td>{$r['student_category']}</td>
                      <td>{$r['student_mob_no']}</td>
                      <td>{$r['student_email']}</td></tr>";
        }
        echo "</tbody></table>";

        // 🧾 Caste Summary
$summary = $conn->prepare("SELECT student_category, COUNT(*) AS total FROM roll_call WHERE student_class=? GROUP BY student_category ORDER BY student_category");
$summary->bind_param("s", $cls);
$summary->execute();
$summaryRes = $summary->get_result();

if ($summaryRes->num_rows > 0) {
    echo "<h4 style='margin-top:15px;'>Caste-wise Summary</h4>";
    echo "<table style='width:40%;'>
            <thead><tr><th>Category</th><th>Count</th></tr></thead><tbody>";
    $grandTotal = 0;
    while ($s = $summaryRes->fetch_assoc()) {
        echo "<tr><td>{$s['student_category']}</td><td>{$s['total']}</td></tr>";
        $grandTotal += $s['total'];
    }
    echo "<tr style='font-weight:bold;background:#f0f0f0;'><td>Total Students</td><td>{$grandTotal}</td></tr>";
    echo "</tbody></table>";
}


        echo "<div style='margin-top:10px; margin-bottom:15px;'>
        <button onclick=\"window.open('print_roll_call.php?class=" . urlencode($cls) . "', '_blank');\" 
                style='background:#007bff;color:#fff;padding:8px 16px;border:none;cursor:pointer;border-radius: 10px;'>
          🖨️ Print Roll Call
        </button>
        <button onclick=\"window.open('export_roll_call.php?class=" . urlencode($cls) . "', '_blank');\" 
                style='background:#28a745;color:#fff;padding:8px 16px;border:none;cursor:pointer; margin-left:10px;border-radius: 10px;'>
          📤 Export to Excel
        </button>
      </div>";



        if (!$isFrozen) {
    echo "<form method='POST'>
            <input type='hidden' name='class' value='$cls'>
            <button name='freeze' style='background:#28a745;color:#fff;padding:8px 16px;border:none;cursor:pointer; border-radius: 10px;'>❄️ Freeze Admission</button>
          </form>";
} else {
    echo "<form method='POST' onsubmit=\"return confirm('Are you sure you want to unfreeze this class?');\">
            <input type='hidden' name='class' value='$cls'>
            <button name='unfreeze' style='background:#ff5252;color:#fff;padding:8px 16px;border:none;cursor:pointer; border-radius: 10px;'>🔓 Unfreeze Admission</button>
          </form>";
}

    } else {
        echo "<p>No students found for this class.</p>";
    }
}

// 🧊 Handle Freeze Button
if (isset($_POST['freeze']) && !empty($_POST['class'])) {
    $cls = $_POST['class'];

    // 1️⃣ Get last assigned roll number for this class
    $lastRollQuery = $conn->prepare("SELECT COALESCE(MAX(roll_no), 0) AS last_roll FROM roll_call WHERE student_class=?");
    $lastRollQuery->bind_param("s", $cls);
    $lastRollQuery->execute();
    $lastRoll = $lastRollQuery->get_result()->fetch_assoc()['last_roll'];

    // 2️⃣ Fetch only newly added students (not yet rolled)
    $result = $conn->prepare("
        SELECT roll_id, student_name 
        FROM roll_call 
        WHERE student_class=? 
        AND (roll_no IS NULL OR roll_no=0)
        ORDER BY created_at ASC
    ");
    $result->bind_param("s", $cls);
    $result->execute();
    $students = $result->get_result()->fetch_all(MYSQLI_ASSOC);

    if (count($students) === 0) {
        echo "<script>alert('⚠️ No new students to sort or assign roll numbers.');</script>";
    } else {

        // 3️⃣ Sort alphabetically by *first word* (since stored as Surname First Middle)
        usort($students, function($a, $b) {
            $aParts = preg_split('/\s+/', trim($a['student_name']));
            $bParts = preg_split('/\s+/', trim($b['student_name']));
            $aSurname = strtolower($aParts[0] ?? '');
            $bSurname = strtolower($bParts[0] ?? '');

            $cmp = strcmp($aSurname, $bSurname);
            if ($cmp !== 0) return $cmp;

            // If surnames same, sort by first name (second word)
            $aFirst = strtolower($aParts[1] ?? '');
            $bFirst = strtolower($bParts[1] ?? '');
            $cmp = strcmp($aFirst, $bFirst);
            if ($cmp !== 0) return $cmp;

            // Finally, sort by middle name if needed
            $aMiddle = strtolower($aParts[2] ?? '');
            $bMiddle = strtolower($bParts[2] ?? '');
            return strcmp($aMiddle, $bMiddle);
        });

        // 4️⃣ Assign roll numbers continuing from last frozen student
        $rollNo = $lastRoll + 1;
        foreach ($students as $stu) {
            $update = $conn->prepare("UPDATE roll_call SET roll_no=? WHERE roll_id=?");
            $update->bind_param("ii", $rollNo, $stu['roll_id']);
            $update->execute();
            $rollNo++;
        }

        // 5️⃣ Mark class as frozen
        $conn->query("
            INSERT INTO roll_call_status (student_class, is_frozen, frozen_on, freeze_count)
            VALUES ('$cls', 1, NOW(), 1)
            ON DUPLICATE KEY UPDATE 
                is_frozen=1,
                frozen_on=NOW(),
                freeze_count=freeze_count+1
        ");

        echo "<script>
            alert('✅ Admission frozen — new students sorted correctly by surname (first word) and added!');
            window.location='roll_call.php?class=" . urlencode($cls) . "';
        </script>";
    }
}

// 🧊 Handle Unfreeze Button
if (isset($_POST['unfreeze']) && !empty($_POST['class'])) {
    $cls = $_POST['class'];

    // Set class back to open
    $conn->query("INSERT INTO roll_call_status (student_class, is_frozen, frozen_on)
                  VALUES ('$cls', 0, NULL)
                  ON DUPLICATE KEY UPDATE is_frozen=0, frozen_on=NULL");

    echo "<script>alert('🔓 Admission has been unfrozen for class: $cls');
          window.location='roll_call.php?class=" . urlencode($cls) . "';</script>";
}

?>
</div>
</body>
</html>
