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
    $feeQ = $conn->prepare("SELECT sh_nm, amount FROM feestru WHERE term_id=? AND type=?");
    $feeQ->bind_param("is", $term_id, $stuType);
    $feeQ->execute();
    $feeRes = $feeQ->get_result();
    while($f = $feeRes->fetch_assoc()){
        $fees[] = $f;
    }
    echo json_encode($fees);
}
