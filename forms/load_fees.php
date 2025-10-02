<?php
header('Content-Type: application/json');
include '../includes/db.php';

$clsFull = $_GET['cls'] ?? '';
$stuType = $_GET['type'] ?? '';
$prn     = $_GET['prn'] ?? ''; // must be passed from frontend

if(!$clsFull || !$stuType) exit;

// --- STEP 1: If PRN exists in receipts, return only pending fees ---
if($prn){
    $stmt = $conn->prepare("SELECT fee_particulars 
                            FROM receipts 
                            WHERE student_prn=? 
                            ORDER BY created_at DESC 
                            LIMIT 1");
    $stmt->bind_param("s", $prn);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if($res){ 
    $feeData = json_decode($res['fee_particulars'], true);
    $pendingFees = [];

    foreach($feeData as $fee){
        $pending = $fee['amount'] - $fee['paid'];
        if($pending > 0){
            $pendingFees[] = [
                "fee_id"   => $fee['fee_id'],
                "fee_scope"=> $fee['fee_scope'],
                "sh_nm"    => $fee['sh_nm'],
                "fl_nm"    => $fee['fl_nm'],
                "amount"   => number_format((float)$pending, 2, '.', ''),   // ✅ pending is shown as amount
                "paid"     => number_format((float)$fee['paid'], 2, '.', ''),
                "pending"  => number_format((float)$pending, 2, '.', '')
            ];
        }
    }

    echo json_encode($pendingFees, JSON_PRETTY_PRINT);
    exit;
}

}

// --- STEP 2: If no receipts, load full fee structure ---
$clsFull = preg_replace('/\s+/', ' ', $clsFull); 
$parts = array_map('trim', explode('-', $clsFull));
$clsLeft = $parts[0] ?? '';
$termRight = $parts[1] ?? '';

$stmt = $conn->prepare("SELECT cls_id, term_id 
                        FROM feecls 
                        WHERE cls_ful_nm=? AND term_title=? 
                        LIMIT 1");
$stmt->bind_param("ss", $clsLeft, $termRight);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$term_id = $res['term_id'] ?? 0;

if($term_id > 0){
    $fees = [];
    $feeQ = $conn->prepare("SELECT fee_id, fee_scope, sh_nm, fl_nm, amount 
                            FROM feestru 
                            WHERE term_id=? AND type=? AND fl_nm <> 'Total' 
                            ORDER BY fee_id ASC");
    $feeQ->bind_param("is", $term_id, $stuType);
    $feeQ->execute();
    $feeRes = $feeQ->get_result();
    while($f = $feeRes->fetch_assoc()){
        $f['paid'] = "0.00";
        $f['pending'] = number_format((float)$f['amount'], 2, '.', '');
        $fees[] = $f;
    }
    echo json_encode($fees, JSON_PRETTY_PRINT);
}
?>
