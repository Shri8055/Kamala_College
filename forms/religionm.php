<?php
include_once('../includes/header.php');
include '../includes/db.php'; // contains $conn

$updateMode = false;
$editRow = null;

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM religion_m WHERE rel_id = $id");
    echo "<script>alert('Religion deleted successfully!'); window.location.href='religionm.php';</script>";
    exit;
}

// Handle Edit (load data into form)
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM religion_m WHERE rel_id = $id");
    if ($result && $result->num_rows > 0) {
        $editRow = $result->fetch_assoc();
        $updateMode = true;
    }
}

// Handle Add/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shortName = strtoupper(trim($_POST['sh_nm']));
    $fullName  = ucwords(trim($_POST['fl_nm']));

    if (isset($_POST['update_id']) && $_POST['update_id'] !== '') {
        // UPDATE
        $id = intval($_POST['update_id']);
        $stmt = $conn->prepare("UPDATE religion_m SET sh_nm=?, fl_nm=? WHERE rel_id=?");
        $stmt->bind_param("ssi", $shortName, $fullName, $id);
        if ($stmt->execute()) {
            echo "<script>alert('Religion updated successfully!'); window.location.href='religionm.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        // INSERT
        $stmt = $conn->prepare("INSERT INTO religion_m (sh_nm, fl_nm) VALUES (?, ?)");
        $stmt->bind_param("ss", $shortName, $fullName);
        if ($stmt->execute()) {
            echo "<script>alert('Religion added successfully!'); window.location.href='religionm.php';</script>";
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
<title>Kamala College | Religion Master</title>
<link rel="stylesheet" href="../assets/css/addclass.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<form action="religionm.php" method="POST">
    <h3 style="text-align: center; padding-bottom: 10px;">
        <?php echo $updateMode ? 'Update Religion' : 'Add Religion'; ?>
    </h3>
    <hr style="width: 80%; margin: auto; border-radius: 50%;">

    <input type="hidden" name="update_id" value="<?php echo $updateMode ? $editRow['rel_id'] : ''; ?>">

    <table>
        <tr>
            <td style="width: 20%;"><label for="sh_nm">Short Form of Religion :</label></td>
            <td colspan="2">
                <input style="text-transform: uppercase;" type="text" id="sh_nm" name="sh_nm" 
                       value="<?php echo $updateMode ? $editRow['sh_nm'] : ''; ?>" 
                       placeholder="Short Form" style="width: 97%;" required>
            </td>
        </tr>
        <tr>
            <td style="width: 20%;"><label for="fl_nm">Full Form of Religion :</label></td>
            <td colspan="2">
                <input style="text-transform: capitalize;" type="text" id="fl_nm" name="fl_nm" 
                       value="<?php echo $updateMode ? $editRow['fl_nm'] : ''; ?>" 
                       placeholder="Full Form" style="width: 97%;" required>
            </td>
        </tr>
    </table>

    <div class="btn-div">
        <input class="btn" type="submit" value="<?php echo $updateMode ? 'Update Religion' : 'Add Religion'; ?>" 
               style="background-color: #0277b6; color: white; border: none; cursor: pointer;">
    </div>
</form>

<hr style="margin: 30px auto; width: 90%;">

<h3 style="text-align:center;">Existing Religions</h3>
<?php
$result = $conn->query("SELECT * FROM religion_m");
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
        echo "<td><a class='update-link' href='religionm.php?edit={$row['rel_id']}'>{$row['rel_id']}</a></td>";
        echo "<td>{$row['sh_nm']}</td>";
        echo "<td>{$row['fl_nm']}</td>";
        echo "<td>
                <a href='religionm.php?delete={$row['rel_id']}' onclick='return confirm(\"Are you sure you want to delete this religion?\");'>
                    <i class='fa-solid fa-circle-xmark delete-icon'></i>
                </a>
              </td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='text-align:center;'>No religions found.</p>";
}
?>

</body>
</html>
