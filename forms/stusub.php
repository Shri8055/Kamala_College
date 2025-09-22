<?php
include '../includes/db.php';

// Student, class, sem, etc.
$stu_id     = intval($_POST['stu_id']);
$cls_id     = intval($_POST['cls_id']);
$current_sem= intval($_POST['current_sem']);
$status     = $_POST['status'] ?? 'in_progress';

// Selected subjects (from form checkboxes/multiple select)
$regular_subjects = isset($_POST['regular_subjects']) ? $_POST['regular_subjects'] : [];
$backlog_subjects = isset($_POST['backlog_subjects']) ? $_POST['backlog_subjects'] : [];

// Build JSON structure
$subj_data = [
    "current_sem" => $current_sem,
    "status"      => $status,
    "year{$current_sem}" => [
        "regular" => $regular_subjects,
        "backlog" => $backlog_subjects
    ]
];

// Encode as JSON (for MariaDB 10.4 use LONGTEXT)
$subj_data_json = json_encode($subj_data);

// Insert into DB
$stmt = $conn->prepare("INSERT INTO student_subjects (stu_id, cls_id, subj_data, current_sem, status) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisis", $stu_id, $cls_id, $subj_data_json, $current_sem, $status);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Student subjects saved successfully!";
} else {
    echo "Error saving data: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
