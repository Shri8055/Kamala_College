<?php
include_once('../includes/header.php');
include '../includes/db.php'; // contains $conn

$updateMode = false;
$editRow = null;

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM caste_m WHERE caste_id = $id");
    echo "<script>alert('Caste deleted successfully!'); window.location.href='castem.php';</script>";
    exit;
}

// Handle Edit (load data into form)
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM caste_m WHERE caste_id = $id");
    if ($result && $result->num_rows > 0) {
        $editRow = $result->fetch_assoc();
        $updateMode = true;
    }
}

// Handle Add/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shortName = strtoupper(trim($_POST['c_sh_caste']));
    $fullName  = ucwords(trim($_POST['c_ful_caste']));

    if (isset($_POST['update_id']) && $_POST['update_id'] !== '') {
        // UPDATE
        $id = intval($_POST['update_id']);
        $stmt = $conn->prepare("UPDATE caste_m SET c_sh_caste=?, c_ful_caste=? WHERE caste_id=?");
        $stmt->bind_param("ssi", $shortName, $fullName, $id);
        if ($stmt->execute()) {
            echo "<script>alert('Caste updated successfully!'); window.location.href='castem.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        // INSERT
        $stmt = $conn->prepare("INSERT INTO caste_m (c_sh_caste, c_ful_caste) VALUES (?, ?)");
        $stmt->bind_param("ss", $shortName, $fullName);
        if ($stmt->execute()) {
            echo "<script>alert('Caste added successfully!'); window.location.href='castem.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Kamala College | Caste Master</title>
<link rel="stylesheet" href="../assets/css/addclass.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<form action="castem.php" method="POST">
    <h3 style="text-align: center; padding-bottom: 10px;">
        <?php echo $updateMode ? 'Update Caste' : 'Add Caste'; ?>
    </h3>
    <hr style="width: 80%; margin: auto; border-radius: 50%;">

    <input type="hidden" name="update_id" value="<?php echo $updateMode ? $editRow['caste_id'] : ''; ?>">

    <table>
        <tr>
            <td style="width: 20%;"><label for="c_sh_caste">Short Form of Caste :</label></td>
            <td colspan="2">
                <input style="text-transform: uppercase;" type="text" id="c_sh_caste" name="c_sh_caste" 
                       value="<?php echo $updateMode ? $editRow['c_sh_caste'] : ''; ?>" 
                       placeholder="Short Form" style="width: 97%;" required>
            </td>
        </tr>
        <tr>
            <td style="width: 20%;"><label for="c_ful_caste">Full Form of Caste :</label></td>
            <td colspan="2">
                <input style="text-transform: capitalize;" type="text" id="c_ful_caste" name="c_ful_caste" 
                       value="<?php echo $updateMode ? $editRow['c_ful_caste'] : ''; ?>" 
                       placeholder="Full Form" style="width: 97%;" required>
            </td>
        </tr>
    </table>

    <div class="btn-div">
        <input class="btn" type="submit" value="<?php echo $updateMode ? 'Update Caste' : 'Add Caste'; ?>" 
               style="background-color: #0277b6; color: white; border: none; cursor: pointer;">
    </div>
</form>

<hr style="margin: 30px auto; width: 90%;">

<h3 style="text-align:center;">Existing Castes</h3>
<?php
$result = $conn->query("SELECT * FROM caste_m");
if ($result && $result->num_rows > 0) {
    echo "<table class='display-table'>";
    echo "<tr>
            <th>ID</th>
            <th>Short Name</th>
            <th>Full Name</th>
            <th>Actions</th>
        </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td><a class='update-link' href='castem.php?edit={$row['caste_id']}'>{$row['caste_id']}</a></td>";
        echo "<td>{$row['c_sh_caste']}</td>";
        echo "<td>{$row['c_ful_caste']}</td>";
        echo "<td>
                <a href='castem.php?delete={$row['caste_id']}' onclick='return confirm(\"Are you sure you want to delete this caste?\");'>
                    <i class='fa-solid fa-circle-xmark delete-icon'></i>
                </a>
              </td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='text-align:center;'>No castes found.</p>";
}
?>

</body>
</html>
