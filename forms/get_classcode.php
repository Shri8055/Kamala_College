<?php
include '../includes/db.php';

$cls_code = trim($_POST['cls_code']);
$cls_id = isset($_POST['cls_id']) ? intval($_POST['cls_id']) : 0;

if ($cls_id > 0) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM classes WHERE cls_code = ? AND cls_id != ?");
    $stmt->bind_param("si", $cls_code, $cls_id);
} else {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM classes WHERE cls_code = ?");
    $stmt->bind_param("s", $cls_code);
}

$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

echo ($count > 0) ? "exists" : "ok";
