<?php
include '../includes/db.php';

function esc($conn, $v) {
    return $conn->real_escape_string($v);
}

if (!isset($_POST['d_cls']) || empty($_POST['d_cls'])) {
    die("Error: Class is not selected.");
}
$class_id = (int) $_POST['d_cls'];

$edit_sem = isset($_GET['edit_sem']) ? (int) $_GET['edit_sem'] : 0;
$current_sem = $edit_sem ?: (int)($_POST['current_sem'] ?? 1);
$acad_yr = $_POST['acad_yr'] ?? '';

// fetch class info
$cls_name = '';
$int_cap_cls = 0;
$stmtC = $conn->prepare("SELECT tot_cap_cls, cls_ful_nm FROM classes WHERE cls_id = ?");
$stmtC->bind_param("i", $class_id);
$stmtC->execute();
$stmtC->bind_result($int_cap_cls, $cls_name);
$stmtC->fetch();
$stmtC->close();
$int_cap = (int)$int_cap_cls;

// posted arrays
$sub_codes   = $_POST['sub_code'] ?? [];
$sub_sh_nms  = $_POST['sub_sh_nm'] ?? [];
$sub_fl_nms  = $_POST['sub_fl_nm'] ?? [];
$sub_typs    = $_POST['sub_typ'] ?? [];
$credits     = $_POST['credit'] ?? [];
$int_mins    = $_POST['int_min_mrk'] ?? [];
$int_maxs    = $_POST['int_max_mrk'] ?? [];
$ext_mins    = $_POST['ext_min_mrk'] ?? [];
$ext_maxs    = $_POST['ext_max_mrk'] ?? [];
$totals      = $_POST['tot_sub'] ?? [];
$types       = $_POST['type'] ?? [];
$sub_ids_post= $_POST['sub_id'] ?? [];

// Fetch existing sub_ids from DB for this class & sem
$existing_ids = [];
$res = $conn->query("SELECT sub_id FROM subjects WHERE class_id={$class_id} AND sem={$current_sem}");
while ($row = $res->fetch_assoc()) {
    $existing_ids[] = (int)$row['sub_id'];
}
$res->free();

// IDs that were submitted in the form
$submitted_ids = array_filter(array_map('intval', $sub_ids_post));

// Find IDs to delete (in DB but not in submitted form)
$to_delete = array_diff($existing_ids, $submitted_ids);

// Delete missing subjects
if (!empty($to_delete)) {
    $del_ids = implode(',', $to_delete);
    $conn->query("DELETE FROM subjects WHERE sub_id IN ($del_ids)");
}

$sel_comp_sub = (int)($_POST['sel_comp_sub'] ?? 0);
$sel_op_sub   = (int)($_POST['sel_op_sub'] ?? 0);

$totalRows = count($sub_codes);
if ($totalRows === 0) die("Error: No subjects submitted.");

// --- get current max sort_order ---
$maxSort = 0;
$res = $conn->query("SELECT MAX(sort_order) AS max_sort FROM subjects WHERE class_id={$class_id} AND sem={$current_sem}");
if ($res) {
    $row = $res->fetch_assoc();
    $maxSort = (int)($row['max_sort'] ?? 0);
}

// counters
$comp_count = 0;
$opt_count  = 0;

for ($i = 0; $i < $totalRows; $i++) {
    $code    = trim($sub_codes[$i] ?? '');
    $sh      = trim($sub_sh_nms[$i] ?? '');
    $fl      = trim($sub_fl_nms[$i] ?? '');
    $typ     = trim($sub_typs[$i] ?? '');
    $credit  = (int)($credits[$i] ?? 0);
    $intMin  = (int)($int_mins[$i] ?? 0);
    $intMax  = (int)($int_maxs[$i] ?? 0);
    $extMin  = (int)($ext_mins[$i] ?? 0);
    $extMax  = (int)($ext_maxs[$i] ?? 0);
    $rowType = trim($types[$i] ?? 'compulsory');

    // 👇 New: status field
    $status  = isset($_POST['status'][$i]) && $_POST['status'][$i] === 'inactive' ? 'inactive' : 'active';

    if ($code === '' && $fl === '') continue;

    if ($rowType === 'compulsory') $comp_count++; else $opt_count++;

    $total = $intMax + $extMax;
    if ($total <= 0) $total = !empty($totals[$i]) ? (int)$totals[$i] : 0;

    $code_e = esc($conn, $code);
    $sh_e   = esc($conn, $sh);
    $fl_e   = esc($conn, $fl);
    $typ_e  = esc($conn, $typ);
    $type_e = esc($conn, $rowType);
    $acad_e = esc($conn, $acad_yr);
    $clsname_e = esc($conn, $cls_name);
    $status_e  = esc($conn, $status);

    $posted_sid = isset($sub_ids_post[$i]) && $sub_ids_post[$i] !== '' ? (int)$sub_ids_post[$i] : 0;

    if ($posted_sid > 0) {
        // ✅ Update existing subject
        $sql = "UPDATE subjects SET
                    sub_code='{$code_e}',
                    sub_sh_nm='{$sh_e}',
                    sub_fl_nm='{$fl_e}',
                    sub_typ='{$typ_e}',
                    credit={$credit},
                    int_min_mrk={$intMin},
                    int_max_mrk={$intMax},
                    ext_min_mrk={$extMin},
                    ext_max_mrk={$extMax},
                    total={$total},
                    type='{$type_e}',
                    acad_yr='{$acad_e}',
                    status='{$status_e}'
                WHERE sub_id={$posted_sid} LIMIT 1";
        $conn->query($sql);
    } else {
        // ✅ Insert new subject (always active by default)
        $maxSort++;
        $sql = "INSERT INTO subjects
            (class_id, sem, acad_yr, int_cap, class_name, sub_code, sub_sh_nm, sub_fl_nm, sub_typ, credit, int_min_mrk, int_max_mrk, ext_min_mrk, ext_max_mrk, total, type, sort_order, status)
            VALUES
            ({$class_id}, {$current_sem}, '{$acad_e}', {$int_cap}, '{$clsname_e}', '{$code_e}', '{$sh_e}', '{$fl_e}', '{$typ_e}', {$credit}, {$intMin}, {$intMax}, {$extMin}, {$extMax}, {$total}, '{$type_e}', {$maxSort}, 'active')";
        $conn->query($sql);
    }
}


// --- update subject_summary ---
$tot_sub = $comp_count + $opt_count;

$check = $conn->prepare("SELECT COUNT(*) FROM subject_summary WHERE class_id=? AND sem=?");
$check->bind_param("ii", $class_id, $current_sem);
$check->execute();
$check->bind_result($exists);
$check->fetch();
$check->close();

if ($exists > 0) {
    $upd = $conn->prepare("UPDATE subject_summary SET comp_sub=?, sel_comp_sub=?, op_sub=?, sel_op_sub=?, tot_sub=? WHERE class_id=? AND sem=?");
    $upd->bind_param("iiiiiii", $comp_count, $sel_comp_sub, $opt_count, $sel_op_sub, $tot_sub, $class_id, $current_sem);
    $upd->execute();
    $upd->close();
} else {
    $ins = $conn->prepare("INSERT INTO subject_summary (class_id, sem, comp_sub, sel_comp_sub, op_sub, sel_op_sub, tot_sub) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $ins->bind_param("iiiiiii", $class_id, $current_sem, $comp_count, $sel_comp_sub, $opt_count, $sel_op_sub, $tot_sub);
    $ins->execute();
    $ins->close();
}

// propagate summary counts
$updSubjects = $conn->prepare("UPDATE subjects SET comp_sub=?, sel_comp_sub=?, op_sub=?, sel_op_sub=?, tot_sub=? WHERE class_id=? AND sem=?");
$updSubjects->bind_param("iiiiiii", $comp_count, $sel_comp_sub, $opt_count, $sel_op_sub, $tot_sub, $class_id, $current_sem);
$updSubjects->execute();
$updSubjects->close();

header("Location: addsub.php?d_cls={$class_id}");
exit;
