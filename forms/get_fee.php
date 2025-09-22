<?php
include '../includes/db.php';

if (isset($_POST['student_id']) && isset($_POST['fee_type']) && isset($_POST['class_full'])) {
    $student_id = (int)$_POST['student_id'];
    $fee_type   = $_POST['fee_type'];
    $class_full = trim($_POST['class_full']); // e.g., "BACHELOR OF COMPUTER APPLICATIONS - BCA Part 3"

    // Step 1: Split into parts
    $parts = array_map('trim', explode(' - ', $class_full));
    $cls_ful_nm = $parts[0] ?? '';
    $term_title = $parts[1] ?? '';

    if (!$cls_ful_nm || !$term_title) {
        echo "N/A";
        exit;
    }

    // Step 2: Get term_id from feecls
    $stmt = $conn->prepare("SELECT term_id FROM feecls WHERE cls_ful_nm = ? AND term_title = ?");
    $stmt->bind_param("ss", $cls_ful_nm, $term_title);
    $stmt->execute();
    $stmt->bind_result($term_id);
    $stmt->fetch();
    $stmt->close();

    if (!$term_id) {
        echo "N/A";
        exit;
    }

    // Step 3: Get the fee amount
    $stmt = $conn->prepare("SELECT amount FROM feestru WHERE term_id = ? AND type = ? AND fl_nm = 'Total' LIMIT 1");
    $stmt->bind_param("is", $term_id, $fee_type);
    $stmt->execute();
    $stmt->bind_result($amount);

    if ($stmt->fetch()) {
        echo $amount;
    } else {
        echo "N/A";
    }

    $stmt->close();
} else {
    echo "N/A";
}
?>
