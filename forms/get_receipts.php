<?php
header('Content-Type: application/json');
include '../includes/db.php';

$prn = $_GET['prn'] ?? '';
if (!$prn) exit;

$stmt = $conn->prepare("SELECT receipt_id, receipt_no, receipt_date, receipt_amount, pending_fee 
                        FROM receipts 
                        WHERE student_prn=? 
                        ORDER BY created_at DESC");
$stmt->bind_param("s", $prn);
$stmt->execute();
$res = $stmt->get_result();

$receipts = [];
while ($row = $res->fetch_assoc()) {
    $receipts[] = $row;
}

echo json_encode($receipts, JSON_PRETTY_PRINT);
?>
