<?php
include '../includes/db.php';

$cls   = $_GET['cls'] ?? '';
$type  = $_GET['type'] ?? '';
$stuId = intval($_GET['stu_id'] ?? 0);

// 1. Load full fee structure
$fees = [];
$res = $conn->query("SELECT fee_id, fee_scope, sh_nm, fl_nm, amount 
                     FROM fees 
                     WHERE cls_name='$cls' AND fee_type='$type'");
while($row = $res->fetch_assoc()) {
    $row['amount'] = floatval($row['amount']);
    $row['paid']   = 0;
    $row['pending']= $row['amount'];
    $fees[$row['fee_id']] = $row;
}

// 2. Subtract receipts already made
$res2 = $conn->query("SELECT fee_particulars FROM receipts WHERE stu_id=$stuId");
while($row2 = $res2->fetch_assoc()) {
    $feeData = json_decode($row2['fee_particulars'], true);
    foreach($feeData as $f) {
        if(isset($fees[$f['fee_id']])) {
            $fees[$f['fee_id']]['paid']    += floatval($f['paid']);
            $fees[$f['fee_id']]['pending']  = max(0, $fees[$f['fee_id']]['amount'] - $fees[$f['fee_id']]['paid']);
        }
    }
}

echo json_encode(array_values($fees));
?>
