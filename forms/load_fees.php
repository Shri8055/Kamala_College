<?php
header('Content-Type: application/json');
include '../includes/db.php';

$clsFull = $_GET['cls'] ?? '';
$stuType = $_GET['type'] ?? '';
$prn     = $_GET['prn'] ?? '';

if (!$clsFull || !$stuType) {
    echo json_encode([
        "fees" => [],
        "concession_given" => false,
        "concession_amount" => 0,
        "error" => "Missing class or type."
    ]);
    exit;
}

// ✅ STEP 1: If PRN exists in receipts, get pending fees
if ($prn) {
    $stmt = $conn->prepare("SELECT fee_particulars 
                            FROM receipts 
                            WHERE student_prn = ? 
                            ORDER BY created_at DESC 
                            LIMIT 1");
    $stmt->bind_param("s", $prn);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res) {
        $feeData = json_decode($res['fee_particulars'], true) ?? [];
        $pendingFees = [];
$tuitionIndex = null;

// ✅ Check for previous concession
$concessionGiven = false;
$concessionAmount = 0;
$checkCons = $conn->prepare("
  SELECT concession_amt 
  FROM receipts 
  WHERE student_prn = ? 
    AND student_class = ? 
    AND concession_amt > 0 
  ORDER BY created_at DESC 
  LIMIT 1
");
$checkCons->bind_param("ss", $prn, $clsFull);
$checkCons->execute();
$consRes = $checkCons->get_result()->fetch_assoc();

if ($consRes && floatval($consRes['concession_amt']) > 0) {
    $concessionGiven = true;
    $concessionAmount = floatval($consRes['concession_amt']);
}

// ✅ Find tuition fee first (for adjustment)
foreach ($feeData as $index => $fee) {
    if (isset($fee['sh_nm']) && trim($fee['sh_nm']) === 'TUTI F') {
        $tuitionIndex = $index;
        break;
    }
}

// ✅ Apply pending + concession adjustments
foreach ($feeData as $index => $fee) {
    $pending = $fee['amount'] - $fee['paid'];

    // Apply concession only to Tuition Fee
    if ($concessionGiven && $tuitionIndex === $index) {
        $pending = max($pending - $concessionAmount, 0);
    }

    if ($pending > 0) {
        $pendingFees[] = [
            "fee_id"   => $fee['fee_id'] ?? null,
            "fee_scope"=> $fee['fee_scope'] ?? 'college',
            "sh_nm"    => $fee['sh_nm'] ?? '',
            "fl_nm"    => $fee['fl_nm'] ?? '',
            "amount"   => number_format((float)$pending, 2, '.', ''),
            "paid"     => number_format((float)($fee['paid'] ?? 0), 2, '.', ''),
            "pending"  => number_format((float)$pending, 2, '.', ''),
        ];
    }
}

// ✅ Return result as JSON
echo json_encode([
    "fees" => $pendingFees,
    "concession_given" => $concessionGiven,
    "concession_amount" => $concessionAmount
], JSON_PRETTY_PRINT);
exit;

    }
}

// ✅ STEP 2: Load full fee structure if no receipts exist
$clsFull = preg_replace('/\s+/', ' ', $clsFull);
$parts = array_map('trim', explode('-', $clsFull));
$clsLeft = $parts[0] ?? '';
$termRight = $parts[1] ?? '';

$stmt = $conn->prepare("SELECT cls_id, term_id 
                        FROM feecls 
                        WHERE cls_ful_nm = ? AND term_title = ? 
                        LIMIT 1");
$stmt->bind_param("ss", $clsLeft, $termRight);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$term_id = $res['term_id'] ?? 0;

$fees = [];

if ($term_id > 0) {
    $feeQ = $conn->prepare("SELECT fee_id, fee_scope, sh_nm, fl_nm, amount 
                            FROM feestru 
                            WHERE term_id = ? AND type = ? AND fl_nm <> 'Total' 
                            ORDER BY fee_id ASC");
    $feeQ->bind_param("is", $term_id, $stuType);
    $feeQ->execute();
    $feeRes = $feeQ->get_result();

    while ($f = $feeRes->fetch_assoc()) {
        $f['paid'] = "0.00";
        $f['pending'] = number_format((float)$f['amount'], 2, '.', '');
        $f['orig_amount'] = $f['amount'];
        $fees[] = $f;
    }
}

// ✅ Always send a consistent JSON structure
echo json_encode([
    "fees" => $fees,
    "concession_given" => false,
    "concession_amount" => 0
], JSON_PRETTY_PRINT);
exit;
?>
