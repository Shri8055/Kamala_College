<?php
include '../includes/db.php';

$prn = $_GET['prn'] ?? '';
if (!$prn) die("No PRN given");

// 1. Get student_subjects
$ssQ = $conn->prepare("SELECT * FROM student_subjects WHERE stu_id=? LIMIT 1");
$ssQ->bind_param("s", $prn);
$ssQ->execute();
$stu = $ssQ->get_result()->fetch_assoc();
$ssQ->close();

if (!$stu) die("Student not found");

$current_sem = intval($stu['current_sem']);
$cls_id      = intval($stu['cls_id']);
$cls_ful_nm  = $stu['cls_ful_nm'];
$subjData    = json_decode($stu['subj_data'], true);

// 2. Promote (increment sem)
$new_sem = $current_sem + 1;

// get total terms/years
$clsQ = $conn->prepare("SELECT pattern, total_terms, duration_years FROM classes WHERE cls_id=?");
$clsQ->bind_param("i", $cls_id);
$clsQ->execute();
$clsData = $clsQ->get_result()->fetch_assoc();
$clsQ->close();

$total_sem = ($clsData['pattern'] === "semester") ? $clsData['total_terms'] : $clsData['duration_years'];
if ($new_sem > $total_sem) die("Already in final sem");

// 3. Find term_id for new sem
$termQ = $conn->prepare("SELECT term_id, term_title FROM feecls WHERE cls_ful_nm=? LIMIT 1 OFFSET ?");
$termQ->bind_param("si", $cls_ful_nm, $new_sem-1); // offset by sem index
$termQ->execute();
$termRes = $termQ->get_result()->fetch_assoc();
$termQ->close();

$term_id = $termRes['term_id'] ?? 0;
$term_title = $termRes['term_title'] ?? '';

// 4. Get fee
$new_fee = 0;
if ($term_id > 0) {
    $fQ = $conn->prepare("SELECT amount FROM feestru WHERE term_id=? AND type=? AND fl_nm='Total' LIMIT 1");
    $fQ->bind_param("is", $term_id, $stu['stu_type']);
    $fQ->execute();
    $fR = $fQ->get_result()->fetch_assoc();
    $fQ->close();
    if ($fR) $new_fee = floatval($fR['amount']);
}

// 5. Update JSON: put subjects for new sem
$newSubjects = [];
$subQ = $conn->prepare("SELECT sub_id FROM subjects WHERE class_id=? AND sem=? AND status='active'");
$subQ->bind_param("ii", $cls_id, $new_sem);
$subQ->execute();
$res = $subQ->get_result();
while ($s = $res->fetch_assoc()) $newSubjects[] = intval($s['sub_id']);
$subQ->close();

if ($clsData['pattern'] === "semester") {
    $subjData["sem{$new_sem}"]["regular"] = $newSubjects;
} else {
    $subjData["year{$new_sem}"]["regular"] = $newSubjects;
}
$subjJson = json_encode($subjData, JSON_UNESCAPED_UNICODE);

// 6. Update DB
$upd = $conn->prepare("UPDATE student_subjects 
    SET current_sem=?, subj_data=?, 
        tot_fee = tot_fee + ?, 
        pen_fee = pen_fee + ? 
    WHERE stu_id=?");
$upd->bind_param("isdds", $new_sem, $subjJson, $new_fee, $new_fee, $prn);
$upd->execute() or die("Promotion update failed: " . $upd->error);
$upd->close();

echo "✅ Promoted $prn to SEM $new_sem (added fee $new_fee)";
