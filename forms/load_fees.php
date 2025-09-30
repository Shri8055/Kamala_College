<?php
include '../includes/db.php';

$clsFull = $_GET['cls'] ?? '';
$stuType = $_GET['type'] ?? ''; // paying or non_paying
if(!$clsFull || !$stuType) exit;

// Split class
$parts = array_map('trim', explode('-', $clsFull));
$clsLeft = $parts[0] ?? '';
$termRight = $parts[1] ?? '';

// Find term_id
$stmt = $conn->prepare("SELECT term_id FROM feecls WHERE cls_ful_nm=? AND term_title=? LIMIT 1");
$stmt->bind_param("ss", $clsLeft, $termRight);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$term_id = $res['term_id'] ?? 0;

if($term_id > 0){
    $fees = [];
    // include fee_id and fee_scope and order by fee_id so DB order is preserved
    $feeQ = $conn->prepare("SELECT fee_id, fee_scope, sh_nm, fl_nm, amount FROM feestru WHERE term_id=? AND type=? AND fl_nm <> 'Total' ORDER BY fee_id ASC");
    $feeQ->bind_param("is", $term_id, $stuType);
    $feeQ->execute();
    $feeRes = $feeQ->get_result();
    while($f = $feeRes->fetch_assoc()){
        $fees[] = $f;
    }
    echo json_encode($fees);
}
