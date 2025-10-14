<?php
include_once('../includes/header.php');
include '../includes/db.php';

$updateMode = false;
$editRow = null;

// Handle Edit
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM edu_pattm WHERE edu_id = $id");
    if ($result && $result->num_rows > 0) {
        $editRow = $result->fetch_assoc();
        $updateMode = true;
    }
}

// Handle Add/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shortName = strtoupper(trim($_POST['edu_sh_nm']));
    $fullName  = ucwords(trim($_POST['edu_fl_nm']));

    if (isset($_POST['update_id']) && $_POST['update_id'] !== '') {
        // UPDATE
        $id = intval($_POST['update_id']);
        $stmt = $conn->prepare("UPDATE edu_pattm SET edu_sh_nm=?, edu_fl_nm=? WHERE edu_id=?");
        $stmt->bind_param("ssi", $shortName, $fullName, $id);
        if ($stmt->execute()) {
            echo "<script>alert('Education Pattern updated successfully!'); window.location.href='edu_pattm.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        // INSERT
        $stmt = $conn->prepare("INSERT INTO edu_pattm (edu_sh_nm, edu_fl_nm) VALUES (?, ?)");
        $stmt->bind_param("ss", $shortName, $fullName);
        if ($stmt->execute()) {
            echo "<script>alert('Education Pattern added successfully!'); window.location.href='edu_pattm.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Kamala College | Education Pattern Master</title>
<link rel="stylesheet" href="../assets/css/addclass.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.display-table th, .display-table td { text-align:center; padding:8px; }
.current-pattern { background: #d4edda; font-weight:bold; }
</style>
</head>
<body>

<form action="edu_pattm.php" method="POST">
    <h3 style="text-align: center; padding-bottom: 10px;">
        <?= $updateMode ? 'Update Education Pattern' : 'Add Education Pattern'; ?>
    </h3>
    <hr style="width: 80%; margin: auto; border-radius: 50%;">

    <input type="hidden" name="update_id" value="<?= $updateMode ? $editRow['edu_id'] : ''; ?>">

    <table>
        <tr>
            <td style="width: 20%;"><label for="edu_sh_nm">Short Name:</label></td>
            <td colspan="2">
                <input style="text-transform: uppercase;" type="text" id="edu_sh_nm" name="edu_sh_nm" 
                       value="<?= $updateMode ? $editRow['edu_sh_nm'] : ''; ?>" 
                       placeholder="e.g., NEP" required>
            </td>
        </tr>
        <tr>
            <td><label for="edu_fl_nm">Full Name:</label></td>
            <td colspan="2">
                <input style="text-transform: capitalize;" type="text" id="edu_fl_nm" name="edu_fl_nm" 
                       value="<?= $updateMode ? $editRow['edu_fl_nm'] : ''; ?>" 
                       placeholder="e.g., National Education Policy" required>
            </td>
        </tr>
    </table>

    <div class="btn-div">
        <input class="btn" type="submit" 
               value="<?= $updateMode ? 'Update Pattern' : 'Add Pattern'; ?>" 
               style="background-color: #0277b6; color: white; border: none; cursor: pointer;">
    </div>
</form>

<hr style="margin: 30px auto; width: 90%;">
<h3 style="text-align:center;">Existing Education Patterns</h3>

<?php
$result = $conn->query("SELECT * FROM edu_pattm ORDER BY edu_id DESC");
if ($result && $result->num_rows > 0) {
    $latestId = $conn->query("SELECT MAX(edu_id) AS max_id FROM edu_pattm")->fetch_assoc()['max_id'];

    echo "<table class='display-table'>";
    echo "<tr>
            <th>ID</th>
            <th>Short Name</th>
            <th>Full Name</th>
            <th>Created At</th>
            <th>Edit</th>
        </tr>";

    while ($row = $result->fetch_assoc()) {
        $isCurrent = ($row['edu_id'] == $latestId);
        echo "<tr class='".($isCurrent ? "current-pattern" : "")."'>";
        echo "<td>{$row['edu_id']}</td>";
        echo "<td>{$row['edu_sh_nm']}</td>";
        echo "<td>{$row['edu_fl_nm']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "<td><a href='edu_pattm.php?edit={$row['edu_id']}'><i class='fa-solid fa-pen-to-square'></i></a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='text-align:center;'>No education patterns found.</p>";
}
?>
</body>
</html>
