<?php
include_once('../includes/header.php');
include '../includes/db.php';

$updateMode = false;
// Handle Status Toggle
if (isset($_POST['toggle_id']) && isset($_POST['toggle_status'])) {
    $id = intval($_POST['toggle_id']);
    $newStatus = $_POST['toggle_status'] === 'inactive' ? 'inactive' : 'active';
    $conn->query("UPDATE classes SET status='$newStatus' WHERE cls_id=$id");
    echo "<script>alert('Class status updated successfully!'); window.location.href='addclass.php';</script>";
    exit;
}

$editRow = null;
$editTerms = [];

function romanNumeral($num) {
    $map = [
        'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
        'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
        'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1
    ];
    $returnValue = '';
    while ($num > 0) {
        foreach ($map as $roman => $int) {
            if($num >= $int) {
                $num -= $int;
                $returnValue .= $roman;
                break;
            }
        }
    }
    return $returnValue;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM classes WHERE cls_id = $id");
    $conn->query("DELETE FROM feecls WHERE cls_id = $id");
    echo "<script>alert('Class deleted successfully!'); window.location.href='addclass.php';</script>";
    exit;
}

// Handle Edit
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM classes WHERE cls_id = $id");
    if ($result && $result->num_rows > 0) {
        $editRow = $result->fetch_assoc();
        $updateMode = true;
        // fetch terms also
        $tRes = $conn->query("SELECT * FROM feecls WHERE cls_id = $id ORDER BY cls_id ASC");
        if ($tRes && $tRes->num_rows > 0) {
            while ($t = $tRes->fetch_assoc()) {
                $editTerms[] = $t;
            }
        }
    }
}

// Handle Add/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stream         = trim($_POST['d_strm']);
    $cls_code       = trim($_POST['d_cls_code']);
    $cls_shr_nm     = trim($_POST['d_cls']);
    $cls_ful_nm     = strtoupper(trim($_POST['d_ful_name']));
    $totdiv         = $_POST['tot_div'];
    $totintcap      = $_POST['tot_cap_cls'];
    $pattern        = strtolower($_POST['d_cls_patrn']);
    $fpattern       = strtolower($_POST['d_cls_fpatrn']);
    $edupattern     = strtoupper($_POST['edu_patt']);
    $acad_year      = strtoupper($_POST['acad_yr']);
    $status = isset($_POST['status']) ? $_POST['status'] : 'active';
    $duration_years = (int) $_POST['d_duration'];

    $total_terms = ($pattern === 'semester') ? ($duration_years * 2) : $duration_years;

    $term_titles = $_POST['term_title'] ?? [];

    if (isset($_POST['update_id']) && $_POST['update_id'] !== '') {
        $id = intval($_POST['update_id']);
        $sql = "UPDATE classes SET 
                    stream=?, cls_code=?, cls_shr_nm=?, cls_ful_nm=?,
                    tot_div=?, tot_cap_cls=?,
                    duration_years=?, total_terms=?, pattern=?, fpattern=?, edu_patt=?, status=?
                WHERE cls_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssiiiissssi", $stream, $cls_code, $cls_shr_nm, $cls_ful_nm,
                        $totdiv, $totintcap, $duration_years, $total_terms,
                        $pattern, $fpattern, $edupattern, $status, $id);

        $stmt->execute();
        $stmt->close();

        // refresh terms
        $conn->query("DELETE FROM feecls WHERE cls_id = $id");
        foreach ($term_titles as $i => $title) {
            if (trim($title) == '') continue;

            if ($pattern === 'semester') {
                // only odd semesters: 1, 3, 5...
                $semNumber = ($i * 2) + 1; 
                $label = "SEM " . romanNumeral($semNumber);
            } else {
                // yearly
                $label = "Year " . ($i + 1);
            }

            $stmt = $conn->prepare("INSERT INTO feecls (cls_id, cls_ful_nm, term_label, term_title) VALUES (?,?,?,?)");
            $clsIdForTerm = $id ?? $newId;
            $stmt->bind_param("isss", $clsIdForTerm, $cls_ful_nm, $label, $title);
            $stmt->execute();
            $stmt->close();
        }

        echo "<script>alert('Class & terms updated successfully!'); window.location.href='addclass.php';</script>";
    } else {
        $sql = "INSERT INTO classes (acad_yr, stream, cls_code, cls_shr_nm, cls_ful_nm,
            tot_div, tot_cap_cls, duration_years, total_terms, pattern, fpattern, edu_patt, status)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssiiiissss", $acad_year, $stream, $cls_code, $cls_shr_nm, $cls_ful_nm,
                            $totdiv, $totintcap, $duration_years, $total_terms,
                            $pattern, $fpattern, $edupattern, $status);

        $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();

        foreach ($term_titles as $i => $title) {
            if (trim($title) == '') continue;

            if ($pattern === 'semester') {
                $semNumber = ($i * 2) + 1; // Only odd
                $label = "SEM " . romanNumeral($semNumber);
            } else {
                $label = "Year " . ($i + 1);
            }

            $clsIdForTerm = $newId ?? $id;
            $stmt = $conn->prepare("INSERT INTO feecls (cls_id, cls_ful_nm, term_label, term_title) VALUES (?,?,?,?)");
            $stmt->bind_param("isss", $clsIdForTerm, $cls_ful_nm, $label, $title);
            $stmt->execute();
            $stmt->close();
        }


        echo "<script>alert('Class & terms added successfully!'); window.location.href='addclass.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Kamala College | Define Class</title>
<link rel="stylesheet" href="../assets/css/addclass.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<style>
    #edu_patt[disabled] {
  background: #f9f9f9ff;
  color: #2e2e2eff;
  cursor: not-allowed;
}
input[readonly] {
  background-color: #f5f5f5;
  color: #333;
  font-weight: 500;
  cursor: not-allowed;
}
</style>
<body>

<form action="addclass.php" method="POST" id="classForm">
    <h3 style="text-align: center; padding-bottom: 10px;">Add Classes</h3>
    <hr style="width: 80%; margin: auto; border-radius: 50%;">
    <input type="hidden" name="update_id" value="<?php echo $updateMode ? $editRow['cls_id'] : ''; ?>">

    <table>
        <tr>
            <?php
                // Fetch all patterns
                $patterns = $conn->query("SELECT * FROM edu_pattm ORDER BY edu_id DESC");

                // Get latest pattern (for new class default)
                $latest = $conn->query("SELECT edu_fl_nm FROM edu_pattm ORDER BY edu_id DESC LIMIT 1")->fetch_assoc()['edu_fl_nm'];

                // Determine currently selected pattern
                $selectedPattern = $updateMode 
                    ? ($editRow['edu_patt'] ?? '')   // For Edit Mode → use saved value
                    : $latest;                       // For New Class → use latest pattern
            ?>
            <td><label>Education Pattern:</label></td>
            <td>
                <select name="edu_patt" id="edu_patt" <?= $updateMode ? 'disabled' : ''; ?> required>
                    <?php
                    if ($patterns && $patterns->num_rows > 0) {
                        while ($p = $patterns->fetch_assoc()) {
                            // Compare case-insensitively to avoid mismatch
                            $isSelected = (strcasecmp($p['edu_fl_nm'], $selectedPattern) === 0) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($p['edu_fl_nm'], ENT_QUOTES) . "' $isSelected>"
                                . htmlspecialchars($p['edu_fl_nm']) .
                                "</option>";
                        }
                    } else {
                        echo "<option value='' disabled>No patterns found</option>";
                    }
                    ?>
                </select>

                <?php if ($updateMode): ?>
                    <!-- Hidden field ensures the value is still submitted even though dropdown is disabled -->
                    <input type="hidden" name="edu_patt" value="<?= htmlspecialchars($editRow['edu_patt'], ENT_QUOTES); ?>">
                <?php endif; ?>
            </td>
            <td></td>
            <td></td>
            <td><label style="margin-left: 15%;">Academic Year:</label></td>
                <?php
                    // --- Determine Current Academic Year ---
                    $current_year = (int)date('Y');
                    $current_month = (int)date('m');

                    // Academic year logic (e.g. 2025-2026 starts from June 2025)
                    if ($current_month >= 6) {
                        $default_acad_yr = $current_year . '-' . ($current_year + 1);
                    } else {
                        $default_acad_yr = ($current_year - 1) . '-' . $current_year;
                    }

                    // --- Use stored value if in update mode ---
                    $acad_year_value = $updateMode 
                        ? htmlspecialchars($editRow['acad_yr'] ?? $default_acad_yr, ENT_QUOTES)
                        : $default_acad_yr;
                ?>
            <td>
                <input 
                    style="width: 50%;" 
                    type="text" 
                    name="acad_yr" 
                    value="<?= $acad_year_value ?>" 
                    readonly
                >
            </td>
        </tr>

        <tr>
            <td><label for="d_strm">Stream :</label></td>
            <td colspan="2">
                <input type="text" id="d_strm" name="d_strm" value="<?php echo $updateMode ? htmlspecialchars($editRow['stream'], ENT_QUOTES) : ''; ?>" placeholder="Stream Name" style="width: 97%;" required>
            </td>
            <td></td>
            <td><label for="d_cls_code" style="margin-left: 15%;">Class Code (3-digit) :</label></td>
            <td>
                <input type="text" maxlength="3" id="d_cls_code" name="d_cls_code"
                       value="<?php echo $updateMode ? htmlspecialchars($editRow['cls_code'], ENT_QUOTES) : ''; ?>"
                       placeholder="e.g., 101" <?php echo $updateMode ? 'readonly' : ''; ?> required>
            </td>
        </tr>

        <tr>
            <td><label for="d_cls">Class Short Name :</label></td>
            <td class="td"><input type="text" id="d_cls" name="d_cls" value="<?php echo $updateMode ? htmlspecialchars($editRow['cls_shr_nm'], ENT_QUOTES) : ''; ?>" placeholder="Short form" required></td>
            <td><label for="d_ful_name" style="margin-left: 30%;">Class Full Name :</label></td>
            <td colspan="3"><input type="text" id="d_ful_name" style="text-transform: uppercase; width: 97%;" name="d_ful_name" value="<?php echo $updateMode ? htmlspecialchars($editRow['cls_ful_nm'], ENT_QUOTES) : ''; ?>" placeholder="Full Form" required></td>
        </tr>

        <tr>
            <td><label for="tot_div">Total Divisions :</label></td>
            <td><input type="number" id="tot_div" name="tot_div" placeholder="Total Divisions per year" value="<?php echo $updateMode ? htmlspecialchars($editRow['tot_div'], ENT_QUOTES) : ''; ?>"></td>
            <td><label for="tot_cap_cls" style="margin-left: 15%;">Total Intake Capacity :</label></td>
            <td><input type="number" id="tot_cap_cls" name="tot_cap_cls" placeholder="Total Strength" value="<?php echo $updateMode ? htmlspecialchars($editRow['tot_cap_cls'], ENT_QUOTES) : ''; ?>"></td>
        </tr>

        <tr>
            <td><label for="d_cls_patrn">Course Pattern :</label></td>
            <td>
                <select name="d_cls_patrn" id="d_cls_patrn" required>
                    <option value="semester" <?php echo ($updateMode && strtolower($editRow['pattern']) == 'semester') ? 'selected' : ''; ?>>Semester</option>
                    <option value="yearly" <?php echo ($updateMode && strtolower($editRow['pattern']) == 'yearly') ? 'selected' : ''; ?>>Yearly</option>
                </select>
            </td>

            <td><label for="d_cls_fpatrn" style="margin-left: 45%;">Fee Pattern :</label></td>
            <td>
                <select name="d_cls_fpatrn" id="d_cls_fpatrn" required>
                    <option value="semester" <?php echo ($updateMode && strtolower($editRow['fpattern']) == 'semester') ? 'selected' : ''; ?>>Semester</option>
                    <option value="yearly" <?php echo ($updateMode && strtolower($editRow['fpattern']) == 'yearly') ? 'selected' : ''; ?>>Yearly</option>
                </select>
            </td>
            <td><label for="d_duration" style="margin-left: 30%;">Duration (Years) :</label></td>
            <td><input type="number" name="d_duration" id="d_duration" value="<?php echo $updateMode ? (int)$editRow['duration_years'] : ''; ?>" placeholder="No. of Years" required></td>
        </tr>
        <tr>
            <td colspan="6">
                <div id="termsContainer" style="width: 70%; margin: auto;">
                    
                    <?php if ($updateMode && !empty($editTerms)): ?>
                        <h4>Define Terms</h4>
                        <table border="1px" style="margin-top:10px; width:100%; text-align:center;">
                            <tr>
                                <th style="padding: 10px;">Term Title</th=>
                                <th style="padding: 10px;">Label</th>
                            </tr>
                            <?php foreach ($editTerms as $t): ?>
                                <tr>
                                    <td>
                                        <input type="text" style="width: 100%;" name="term_title[]" 
                                            value="<?php echo htmlspecialchars($t['term_title'], ENT_QUOTES); ?>">
                                    </td>
                                    <td style="text-align:center;"><?php echo htmlspecialchars($t['term_label'], ENT_QUOTES); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php endif; ?>
                </div>
            </td>
        </tr>

        
    </table>

    <div class="btn-div" style="margin-top:12px;">
        <input class="btn" type="submit" value="<?php echo $updateMode ? 'Update Class' : 'Add Class'; ?>" style="background-color: #0277b6; color: white; border: none; cursor: pointer;">
    </div>
</form>

<hr style="margin: 30px auto; width: 90%;">

<h3 style="text-align:center;">Existing Classes</h3>
<?php
$result = $conn->query("SELECT cls_id, stream, cls_code, cls_shr_nm, cls_ful_nm, tot_div, tot_cap_cls, pattern, fpattern, duration_years, total_terms, edu_patt, status FROM classes ORDER BY cls_id DESC");
if ($result && $result->num_rows > 0) {
    echo "<table class='display-table'>";
    echo "<tr>
            <th>Class ID</th>
            <th>Stream</th>
            <th>Class Code</th>
            <th>Short Name</th>
            <th>Full Name</th>
            <th>Division</th>
            <th>Intake</th>
            <th style='border-right: 1px solid #cccccc;'>Course<br>Pattern</th>
            <th style='border-right: 1px solid #cccccc;'>Fee<br>Pattern</th>
            <th style='border-right: 1px solid #cccccc;'>Education Pattern</th>
            <th style='width: 5%; border-right: 1px solid #cccccc;'>Duration (Years)</th>
            <th style='width: 5%; border-right: 1px solid #cccccc;'>Total Terms</th>
            <th>Actions</th>
        </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td><a class='update-link' href='addclass.php?edit={$row['cls_id']}'>{$row['cls_id']}</a></td>";
        echo "<td>" . htmlspecialchars($row['stream'], ENT_QUOTES) . "</td>";
        echo "<td>" . htmlspecialchars($row['cls_code'], ENT_QUOTES) . "</td>";
        echo "<td>" . htmlspecialchars($row['cls_shr_nm'], ENT_QUOTES) . "</td>";
        echo "<td>" . htmlspecialchars($row['cls_ful_nm'], ENT_QUOTES) . "</td>";
        echo "<td>" . htmlspecialchars($row['tot_div'], ENT_QUOTES) . "</td>";
        echo "<td>" . htmlspecialchars($row['tot_cap_cls'], ENT_QUOTES) . "</td>";
        echo "<td style='border-right: 1px solid #cccccc;'>" . htmlspecialchars($row['pattern'], ENT_QUOTES) . "</td>";
        echo "<td style='border-right: 1px solid #cccccc;'>" . htmlspecialchars($row['fpattern'], ENT_QUOTES) . "</td>";
        echo "<td style='border-right: 1px solid #cccccc;'>" . htmlspecialchars($row['edu_patt'], ENT_QUOTES) . "</td>";
        echo "<td style='border-right: 1px solid #cccccc;'>" . (int)$row['duration_years'] . "</td>";
        echo "<td style='border-right: 1px solid #cccccc;'>" . (int)$row['total_terms'] . "</td>";
        echo "<td>
                    <form method='POST' action='' style='padding: 0px; margin: 0px;'>
                        <input type='hidden' name='toggle_id' value='{$row['cls_id']}'>
                        <select name='toggle_status' onchange='this.form.submit()'>
                            <option value='active' " . ($row['status'] === 'active' ? 'selected' : '') . ">Active</option>
                            <option value='inactive' " . ($row['status'] === 'inactive' ? 'selected' : '') . ">Inactive</option>
                        </select>
                    </form>
                </td>
                ";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='text-align:center;'>No classes found.</p>";
}
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){

    function generateTerms() {
    let years = parseInt($("#d_duration").val());
    let pattern = $("#d_cls_patrn").val();
    if (!years || !pattern) return;

    let termsContainer = $("#termsContainer");
    termsContainer.empty();

    let table = $("<table border='1px' style='margin-top:10px; width:100%; text-align:center; '><tr><th style='padding:10px'>Term Title</th><th style='padding:10px'>Label</th></tr></table>");

    if (pattern === "semester") {
        for (let y=1; y<=years; y++) {
            let semNumber = (y*2) - 1; // odd sem only
            let roman = toRoman(semNumber);
            let row = `<tr>
                          <td><input type="text" style="width: 100%;" name="term_title[]" placeholder="Enter title for SEM ${roman}"></td>
                          <td style="text-align:center;">SEM ${roman}</td>
                       </tr>`;
            table.append(row);
        }
    } else {
        for (let y=1; y<=years; y++) {
            let row = `<tr>
                          <td><input type="text" style="width: 100%;" name="term_title[]" placeholder="Enter title for Year ${y}"></td>
                          <td style="text-align:center;">Year ${y}</td>
                       </tr>`;
            table.append(row);
        }
    }

    termsContainer.append("<h4>Define Terms</h4>");
    termsContainer.append(table);
}

function toRoman(num) {
    const map = [
        ["M",1000],["CM",900],["D",500],["CD",400],
        ["C",100],["XC",90],["L",50],["XL",40],
        ["X",10],["IX",9],["V",5],["IV",4],["I",1]
    ];
    let result = '';
    for (let [roman, value] of map) {
        while (num >= value) {
            result += roman;
            num -= value;
        }
    }
    return result;
}

    $("#d_duration, #d_cls_patrn").on("change blur", generateTerms);

});
</script>

</body>
</html>
