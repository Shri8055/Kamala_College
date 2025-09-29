<?php
ob_start();
include_once('../includes/header.php');
include '../includes/db.php'; // $conn

require '../vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$classRows = [];
$result = $conn->query("SELECT cls_id, cls_ful_nm, term_title, term_label FROM feecls ORDER BY cls_ful_nm DESC");
while ($row = $result->fetch_assoc()) $classRows[] = $row;

$ReliRows = [];
$result = $conn->query("SELECT rel_id, fl_nm FROM religion_m ORDER BY rel_id ASC");
while ($rrow = $result->fetch_assoc()) $ReliRows[] = $rrow;

$CasteRows = [];
$result = $conn->query("SELECT caste_id, c_ful_caste FROM caste_m ORDER BY caste_id ASC");
while ($crow = $result->fetch_assoc()) $CasteRows[] = $crow;

$student = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $sql = "SELECT * FROM student_registration WHERE r_id = $id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $student = $result->fetch_assoc();
    }
}

function generateUniquePRN($conn, $classCode = "000") {
    $year = date("Y");
    $prefix = $year . str_pad($classCode, 3, "0", STR_PAD_LEFT);

    $sql = "SELECT MAX(prn_no) AS max_prn FROM admts WHERE prn_no LIKE '{$prefix}%'";
    $res = $conn->query($sql);
    $row = $res ? $res->fetch_assoc() : null;

    $next = ($row && $row['max_prn']) ? intval(substr($row['max_prn'], -3)) + 1 : 1;
    return $prefix . str_pad($next, 3, "0", STR_PAD_LEFT);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['edit'])) {
    $r_id = intval($_GET['edit']);

    // ------------------------
    // Capture compulsory/optional subjects
    // ------------------------
    $compSel = isset($_POST['compulsory']) ? array_values(array_filter((array)$_POST['compulsory'])) : [];
    $optSel  = isset($_POST['optional'])   ? array_values(array_filter((array)$_POST['optional']))   : [];
    $compSel = array_values(array_unique(array_map('trim', $compSel)));
    $optSel  = array_values(array_unique(array_map('trim', $optSel)));

    $subjects_payload = [
        'compulsory' => $compSel,
        'optional'   => $optSel
    ];
    $subjects_json = json_encode($subjects_payload, JSON_UNESCAPED_UNICODE);

    $comp_count = count($compSel);
    $opt_count  = count($optSel);
    $tot_count  = $comp_count + $opt_count;
    $acad_yr = $_POST['acad_yr'] ?? null;

    // ------------------------------
    // UPDATE student_registration
    // ------------------------------
        // ------------------------------
    // UPDATE student_registration
    // ------------------------------
    $updateSql = "UPDATE student_registration SET
        r_stu_admi_cls=?, r_stu_tit=?, r_stu_mother=?, r_stu_gen=?, 
        r_stu_name=?, r_stu_father=?, r_stu_sur=?,
        r_p_add=?, r_stu_vil=?, r_sub_dist=?, r_dist=?, r_r_add=?, 
        r_stu_ph=?, r_stu_G_ph=?, r_stu_B=?, r_stu_B_sub_dist=?, 
        r_stu_B_dist=?, r_stu_B_city=?, r_stu_B_sta=?, r_stu_B_date=?, 
        r_stu_B_dateW=?, r_stu_age=?, r_stu_disb=?, r_stu_mari=?, 
        r_stu_reli=?, r_stu_cast=?, r_stu_castcat=?, 
        r_stu_aadr=?, r_stu_pan=?, r_stu_bg=?, r_stu_email=?, r_stu_p_email=?, 
        r_stu_mtoung=?, r_stu_nati=?, r_stu_jb=?, r_stu_vot=?, r_stu_vot_no=?, 
        r_stu_org=?, r_stu_sport=?, r_stu_intr_ncc=?, 
        subjects_json=?, comp_count=?, opt_count=?, tot_count=?, acad_yr=?, 
        r_stu_bkn=?, r_stu_ifsc=?, r_stu_bkacc=?, r_stu_adhr_lnk=?, 
        r_stu_exam=?, r_uni=?, r_seat=?, r_mrk_obt=?, r_perc=?, r_sch=?, 
        r_sub1=?, r_mrk1=?, r_sub2=?, r_mrk2=?, r_sub3=?, r_mrk3=?, 
        r_sub4=?, r_mrk4=?, r_sub5=?, r_mrk5=?, r_sub6=?, r_mrk6=?, 
        r_sub7=?, r_mrk7=?, r_sub8=?, r_mrk8=?, 
        r_stu_mother_ph_no=?, s_stu_mother_Occ=?, s_stu_mother_Ophno=?, s_stu_mother_Oadd=?, 
        r_stu_father_ph_no=?, r_stu_father_Occ=?, r_stu_father_Ophno=?, r_stu_father_Oadd=?, 
        r_stu_inc=?, r_stu_rel=?, type=?, 
        status='Admitted'
        WHERE r_id=?";



    // collect all variables into an array
    $vars = [
        $_POST['r_stu_admi_cls'], $_POST['r_stu_tit'], $_POST['r_stu_mother'], $_POST['r_stu_gen'],
        $_POST['r_stu_name'], $_POST['r_stu_father'], $_POST['r_stu_sur'],
        $_POST['r_p_add'], $_POST['r_stu_vil'], $_POST['r_sub_dist'], $_POST['r_dist'], $_POST['r_r_add'],
        $_POST['r_stu_ph'], $_POST['r_stu_G_ph'], $_POST['r_stu_B'], $_POST['r_stu_B_sub_dist'],
        $_POST['r_stu_B_dist'], $_POST['r_stu_B_city'], $_POST['r_stu_B_sta'], $_POST['r_stu_B_date'],
        $_POST['r_stu_B_dateW'], $_POST['r_stu_age'], $_POST['r_stu_disb'], $_POST['r_stu_mari'],
        $_POST['r_stu_reli'], $_POST['r_stu_cast'], $_POST['r_stu_castcat'],
        $_POST['r_stu_aadr'], $_POST['r_stu_pan'], $_POST['r_stu_bg'], $_POST['r_stu_email'], $_POST['r_stu_p_email'],
        $_POST['r_stu_mtoung'], $_POST['r_stu_nati'], $_POST['r_stu_jb'], $_POST['r_stu_vot'], $_POST['r_stu_vot_no'],
        $_POST['r_stu_org'], $_POST['r_stu_sport'], $_POST['r_stu_intr_ncc'],
        $subjects_json, $comp_count, $opt_count, $tot_count, $acad_yr,
        $_POST['r_stu_bkn'], $_POST['r_stu_ifsc'], $_POST['r_stu_bkacc'], $_POST['r_stu_adhr_lnk'],
        $_POST['r_stu_exam'], $_POST['r_uni'], $_POST['r_seat'], $_POST['r_mrk_obt'], $_POST['r_perc'], $_POST['r_sch'],
        $_POST['r_sub1'], $_POST['r_mrk1'], $_POST['r_sub2'], $_POST['r_mrk2'], $_POST['r_sub3'], $_POST['r_mrk3'],
        $_POST['r_sub4'], $_POST['r_mrk4'], $_POST['r_sub5'], $_POST['r_mrk5'], $_POST['r_sub6'], $_POST['r_mrk6'],
        $_POST['r_sub7'], $_POST['r_mrk7'], $_POST['r_sub8'], $_POST['r_mrk8'],
        $_POST['r_stu_mother_ph_no'], $_POST['s_stu_mother_Occ'], $_POST['s_stu_mother_Ophno'], $_POST['s_stu_mother_Oadd'],
        $_POST['r_stu_father_ph_no'], $_POST['r_stu_father_Occ'], $_POST['r_stu_father_Ophno'], $_POST['r_stu_father_Oadd'],
        $_POST['r_stu_inc'], $_POST['r_stu_rel'], $_POST['r_stu_type'],
        $r_id
    ];

    $stmt = $conn->prepare($updateSql);
    $updateAdmts = "UPDATE admts SET type=? WHERE r_id=?";
    $stmt2 = $conn->prepare($updateAdmts);
    $stmt2->bind_param("si", $_POST['r_stu_type'], $r_id);
    $stmt2->execute();


    // generate the type string dynamically
    $types = str_repeat("s", count($vars)-1) . "i"; // last param ($r_id) is int

    // bind dynamically
    $stmt->bind_param($types, ...$vars);

    // email automation
    // after student_registration UPDATE success
$stmt->execute() or die("student_registration UPDATE failed: " . $stmt->error);
    $stmt->close();

    // ------------------------
// Copy/Update into admts
// ------------------------
$admtsColsRes = $conn->query("SHOW COLUMNS FROM admts");
$admtsCols = [];
while ($c = $admtsColsRes->fetch_assoc()) $admtsCols[] = $c['Field'];

$stuColsRes = $conn->query("SHOW COLUMNS FROM student_registration");
$stuCols = [];
while ($c = $stuColsRes->fetch_assoc()) $stuCols[] = $c['Field'];

$insertCols = [];
$selectExprs = [];
foreach ($admtsCols as $col) {
    if ($col === 'created_at') continue;
    if ($col === 'status') {
        $insertCols[] = 'status';
        $selectExprs[] = "'Admitted'";
        continue;
    }
    if (in_array($col, $stuCols, true)) {
        $insertCols[] = $col;
        $selectExprs[] = 's.' . $col;
    } else {
        $insertCols[] = $col;
        $selectExprs[] = 'NULL';
    }
}

$existsRes = $conn->query("SELECT 1 FROM admts WHERE r_id = $r_id LIMIT 1");
if ($existsRes && $existsRes->num_rows > 0) {
    // UPDATE existing admts row
    $setPairs = [];
    foreach ($admtsCols as $col) {
        if ($col === 'created_at' || $col === 'r_id') continue;
        if ($col === 'status') {
            $setPairs[] = "admts.status = 'Admitted'";
            continue;
        }
        if (in_array($col, $stuCols, true)) {
            $setPairs[] = "admts.`$col` = s.`$col`";
        } else {
            $setPairs[] = "admts.`$col` = NULL";
        }
    }
    $updateAdmtsSql = "UPDATE admts AS admts
        JOIN student_registration AS s ON admts.r_id = s.r_id
        SET " . implode(", ", $setPairs) . "
        WHERE admts.r_id = $r_id";
    $conn->query($updateAdmtsSql) or die("Update admts failed: " . $conn->error);
} else {
    // INSERT new admts row
    $colsList = implode(", ", array_map(fn($c)=>"`$c`", $insertCols));
    $selectList = implode(", ", $selectExprs);
    $insertSql = "INSERT INTO admts ($colsList)
        SELECT $selectList
        FROM student_registration AS s
        WHERE s.r_id = $r_id";
    $conn->query($insertSql) or die("Insert into admts failed: " . $conn->error);
}


// ------------------------
// Class / PRN / student_subjects Logic
// ------------------------

// Normalize class name (fix different dash characters)
$normalized = str_replace(["–", "—"], "-", $_POST['r_stu_admi_cls']);
$parts = array_map('trim', explode('-', $normalized));
$clsLeft = $parts[0] ?? '';    // e.g. "BACHELOR OF COMPUTER APPLICATION"
$termRight = $parts[1] ?? '';  // e.g. "BCA Part 1"

$cls_id = 0;
$cls_nm = '';
$clsCode = "000";
$pattern = "semester";
$total_terms = 0;
$duration_years = 0;

// 🔍 Find class info
if ($clsLeft) {
    $clsRes = $conn->prepare("SELECT cls_id, cls_ful_nm, cls_code, pattern, total_terms, duration_years 
                              FROM classes 
                              WHERE cls_ful_nm = ?");
    $clsRes->bind_param("s", $clsLeft);
    $clsRes->execute();
    $clsData = $clsRes->get_result()->fetch_assoc();
    $clsRes->close();

    if ($clsData) {
        $cls_id = intval($clsData['cls_id']);
        $cls_nm = intval($clsData['cls_ful_nm']);
        $clsCode = $clsData['cls_code'] ?? "000";
        $pattern = $clsData['pattern'] ?? "semester";
        $total_terms = intval($clsData['total_terms']);
        $duration_years = intval($clsData['duration_years']);
    }
}

// 🔍 Find term label
$termLabel = '';
if ($clsLeft && $termRight) {
    $feeQ = $conn->prepare("SELECT term_label 
                            FROM feecls 
                            WHERE cls_ful_nm=? AND term_title=? 
                            LIMIT 1");
    $feeQ->bind_param("ss", $clsLeft, $termRight);
    $feeQ->execute();
    $feeRes = $feeQ->get_result()->fetch_assoc();
    $feeQ->close();
    $termLabel = $feeRes['term_label'] ?? '';
}

// ------------------------
// Decode Roman (SEM I → 1, SEM II → 2, Year 1 → 1, etc.)
// ------------------------
function decodeLabel($label) {
    $map = [
        "SEM I" => 1, "SEM II" => 2, "SEM III" => 3, "SEM IV" => 4,
        "SEM V" => 5, "SEM VI" => 6, "SEM VII" => 7, "SEM VIII" => 8,
        "YEAR 1" => 1, "YEAR 2" => 2, "YEAR 3" => 3, "YEAR 4" => 4
    ];
    $u = strtoupper(trim($label));
    return $map[$u] ?? 0;
}

$current_sem = decodeLabel($termLabel);
if ($current_sem === 0) $current_sem = 1;

// total semesters/years
$total_sem = ($pattern === "semester") ? $total_terms : $duration_years;

// ------------------------
// Generate PRN if missing
// ------------------------
$prn = '';
$prnCheck = $conn->query("SELECT prn_no FROM admts WHERE r_id = $r_id");
$prnRow = $prnCheck ? $prnCheck->fetch_assoc() : null;

if (!$prnRow || empty($prnRow['prn_no'])) {
    $newPRN = generateUniquePRN($conn, $clsCode);
    $upd = $conn->prepare("UPDATE admts SET prn_no=? WHERE r_id=?");
    $upd->bind_param("si", $newPRN, $r_id);
    $upd->execute();
    $upd->close();
    $prn = $newPRN;
} else {
    $prn = $prnRow['prn_no'];
}

// 🔍 Get student type
$stu_type = $_POST['r_stu_type'] ?? 'paying';

// 🔍 Get term_id
$term_id = 0;
$feeQ = $conn->prepare("SELECT term_id FROM feecls WHERE cls_ful_nm=? AND term_title=? LIMIT 1");
$feeQ->bind_param("ss", $clsLeft, $termRight);
$feeQ->execute();
$feeRes = $feeQ->get_result()->fetch_assoc();
$feeQ->close();
if ($feeRes) $term_id = intval($feeRes['term_id']);

// 🔍 Find total fee for this student type
$tot_fee = 0;
if ($term_id > 0) {
    $fQ = $conn->prepare("SELECT amount FROM feestru 
                          WHERE term_id=? AND type=? AND fl_nm='Total' LIMIT 1");
    $fQ->bind_param("is", $term_id, $stu_type);
    $fQ->execute();
    $fR = $fQ->get_result()->fetch_assoc();
    $fQ->close();
    if ($fR) $tot_fee = floatval($fR['amount']);
}
$pen_fee = $tot_fee;

// ------------------------
// Insert student_subjects if not exists
// ------------------------
if ($cls_id > 0) {
    // 🔍 Fetch subjects for this class and sem
    $subjects = [];
    $subQ = $conn->prepare("SELECT sub_id 
                            FROM subjects 
                            WHERE class_id=? AND sem=? AND status='active'");
    $subQ->bind_param("ii", $cls_id, $current_sem);
    $subQ->execute();
    $subRes = $subQ->get_result();
    while ($s = $subRes->fetch_assoc()) {
        $subjects[] = intval($s['sub_id']);
    }
    $subQ->close();

    // Build default structure
    $subjData = ["current_sem" => $current_sem, "status" => "in_progress"];

    if ($pattern === "semester") {
        for ($i = 1; $i <= $total_terms; $i++) {
            $subjData["sem$i"] = ["regular" => [], "backlog" => []];
        }
    } else {
        for ($i = 1; $i <= $duration_years; $i++) {
            $subjData["year$i"] = ["regular" => [], "backlog" => []];
        }
    }

    // Fill subjects for current sem
    $selectedSubjects = array_merge($compSel, $optSel);

    if ($pattern === "semester") {
        $subjData["sem{$current_sem}"]["regular"] = $selectedSubjects;
    } else {
        $subjData["year{$current_sem}"]["regular"] = $selectedSubjects;
    }

    $jsonData = json_encode($subjData, JSON_UNESCAPED_UNICODE);

    // Insert if not already
    $chk = $conn->prepare("SELECT ss_id FROM student_subjects WHERE stu_id=? LIMIT 1");
    $chk->bind_param("s", $prn);
    $chk->execute();
    $chkRes = $chk->get_result();

    if ($chkRes->num_rows === 0) {
        $ins = $conn->prepare("INSERT INTO student_subjects 
            (stu_id, cls_id, cls_ful_nm, subj_data, current_sem, tot_fee, pen_fee, stu_type, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'in_progress')");
        $ins->bind_param("sissidds", $prn, $cls_id, $clsLeft, $jsonData, $current_sem, $tot_fee, $pen_fee, $stu_type);
        $ins->execute() or die("Insert student_subjects failed: " . $ins->error);
        $ins->close();
    }
    $chk->close();
}


    // ------------------------
    // Send Email (existing code)
    // ------------------------
    $login_id = $prn . '@kckop.com';
    $pass     = 'Student@123';
    $stu_name    = $_POST['r_stu_name'];
    $father_nm   = $_POST['r_stu_father'];
    $last_nm     = $_POST['r_stu_sur'];
    $stu_class   = $_POST['r_stu_admi_cls'];
    $stu_email   = $_POST['r_stu_email'];
    $parent_email = $_POST['r_stu_p_email'];

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'lmnop012001@gmail.com';
        $mail->Password = 'axbl mhsh eubp zgyc';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('lmnop012001@gmail.com', 'Kamala College Admin');
        if ($stu_email) $mail->addAddress($stu_email);
        if ($parent_email) $mail->addAddress($parent_email);

        $mail->isHTML(true);
        $mail->Subject = "Admission Successful - $stu_name";
        $mail->Body = "
            <h3>Dear $stu_name $father_nm $last_nm,</h3>
            <p>You have been successfully Admitted.</p>
            <p><b>Class:</b> $stu_class</p>
            <p><b>Registration No:</b> $r_id</p>
            <p><b>PRN No :</b> $prn</p>
            <p><b>Now you can login to kckop.com</b></p>
            <p><b>Login id :</b> $login_id</p>
            <p><b>Password :</b> $pass</p>
            <p style='color: red';><i>You can update your password later.</i></p>
            <br><p>Regards,<br>College Admin</p>
        ";
        $mail->send();
    } catch (Exception $e) {
        error_log("Mail error: {$mail->ErrorInfo}");
    }

    echo "<script>
        alert('🎉 Student Admission Successful!\\n\\nStudent Name: $stu_name\\nStudent ID: $r_id');
        window.location.href = 'verify.php';
    </script>";
    exit;
}

ob_end_flush();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kamala College | Verify Registration</title>
    <link rel="stylesheet" href="../assets/css/verify.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <style>
        table { width:100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 6px; }
        th { background: #f2f2f2; }
        .search-bar input, .search-bar select { margin-right: 5px; padding: 4px; }/* Add stronger rule for your table cells */
        #registrationsTable td, 
        #registrationsTable th { padding: 5px !important;}
        tr:hover { cursor: pointer; }
    </style>
</head>
<body>
    <?php
        $edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
    ?>
    <h3 style="text-align: center; padding-top: 10px;">
        Student Verification<?php echo $edit_id ? " : R-ID : " . $edit_id : ""; ?>
    </h3>
    <form id="collegeForm" action="verify.php?edit=<?php echo $student['r_id']; ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="acad_yr" value="<?= htmlspecialchars($student['acad_yr'] ?? '') ?>">
        <input type="hidden" id="edit_id" value="<?= $edit_id ?>">
        <div class="tabs">
            <div class="tab active" data-tab="personal">Personal Details <span class="status-icon">❌</span></div>
            <div class="tab" data-tab="birthinfo">Birth Details <span class="status-icon">❌</span></div>
            <div class="tab" data-tab="addetails">Additional Personal Details <span class="status-icon">❌</span></div>
            <div class="tab" data-tab="bankdet">Bank Details <span class="status-icon">❌</span></div>
            <div class="tab" data-tab="academic">Academic Details <span class="status-icon">❌</span></div>
            <div class="tab" data-tab="subjects-container">Subject Selection <span class="status-icon">❌</span></div>
            <div class="tab" data-tab="docs">Documents <span class="status-icon">❌</span></div>
            <div class="tab" data-tab="parent">Parent Details <span class="status-icon">❌</span></div>
        </div>

        <!-- PERSONAL DETAILS -->
        <?php
            $r_id = $_GET['edit'] ?? null;
            $studentDocsPath = "uploads/students/$r_id/";

            $idFile = '';
            $sigFile = '';

            // find existing ID
            $idMatches = glob($studentDocsPath . "r_stu_id*.*");
            if (!empty($idMatches)) {
                $idFile = $idMatches[0];
            }

            // find existing Signature
            $sigMatches = glob($studentDocsPath . "r_stu_sig*.*");
            if (!empty($sigMatches)) {
                $sigFile = $sigMatches[0];
            }
        ?>
        <div class="tab-content active" id="personal">
            <table>
                <tr>
                    <td>
                        <h3>Academic Year :
                            <?php
                            $currentYear = date("Y");
                            $nextYear = $currentYear + 1;
                            echo $currentYear . "-" . substr($nextYear, 2);
                            ?>
                        </h3>
                    </td>
                </tr>
                <tr>
                    <td><label for="r_stu_admi_cls">Admission For Class :</label></td>
                    <td colspan="3">
                        <?php
                            $studentSelectedClass = trim($student['r_stu_admi_cls'] ?? '');
                        ?>
                        <select name="r_stu_admi_cls" id="r_stu_admi_cls">
                            <option value="">-- Select Class --</option>
                            <?php foreach ($classRows as $row): 
                                $optionValue = trim($row['cls_ful_nm'] . ' - ' . $row['term_title']);
                                $selected = ($optionValue === $studentSelectedClass) ? 'selected' : '';
                            ?>
                                <option value="<?= htmlspecialchars($optionValue) ?>" <?= $selected ?>>
                                    <?= htmlspecialchars($optionValue) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="r_stu_tit">Title :</label></td>
                    <td>
                        <select name="r_stu_tit" id="r_stu_tit" required>
                            <option value="Miss." <?= ($student && $student['r_stu_tit']=='Miss.')?'selected':'' ?>>Miss.</option>
                            <option value="Mrs." <?= ($student && $student['r_stu_tit']=='Mrs.')?'selected':'' ?>>Mrs.</option>
                        </select>
                    </td>
                    <td></td>
                    <td></td>
                    <td style="margin: auto; vertical-align: middle; text-align: center;">
                        <img id="preview_r_stu_id" 
                            src="<?= $idFile ? $idFile : '' ?>" 
                            alt="ID Preview" 
                            style="max-width: 100px; margin: auto; border:1px solid #ccc; border-radius:5px; <?= $idFile ? '' : 'display:none;' ?>">
                    </td>
                    <td style="margin: auto; vertical-align: middle; text-align: center;">
                        <img id="preview_r_stu_sig" 
                            src="<?= $sigFile ? $sigFile : '' ?>" 
                            alt="Signature Preview" 
                            style="max-width: 100px; margin: auto; border:1px solid #ccc; border-radius:5px; <?= $sigFile ? '' : 'display:none;' ?>">
                    </td>
                </tr>
                <tr>
                    <td><label for="r_stu_mother">Mother's Name :</label></td>
                    <td><input type="text" id="r_stu_mother" name="r_stu_mother" value="<?= $student['r_stu_mother'] ?? '' ?>" placeholder="Mother's Name" required></td>
                    <td><label for="r_stu_gen" class="align">Student Gender :</label></td>
                    <td>
                        <select id="r_stu_gen" name="r_stu_gen" required>
                            <option value="Female" <?= ($student && $student['r_stu_gen']=='Female')?'selected':'' ?>>Female</option>
                            <option value="Male" <?= ($student && $student['r_stu_gen']=='Male')?'selected':'' ?>>Male</option>
                            <option value="Other" <?= ($student && $student['r_stu_gen']=='Other')?'selected':'' ?>>Other</option>
                        </select>
                    </td>
                    <td style="text-align: center;"><label for="r_stu_id">Upload Id Photo <br><span style="font-size: 14px; color: crimson;">Size < 2MB, Only .PNG</span></label> <input type="file" id="r_stu_id" name="r_stu_id"></td>
                    <td style="text-align: center;"><label for="r_stu_sig">Upload Signature <br><span style="font-size: 14px; color: crimson;">Size < 2MB, Only .PNG</span></label> <input type="file" id="r_stu_sig" name="r_stu_sig"></td>
                </tr>
                <tr>
                    <td><label for="r_stu_name">Student Name :</label></td>
                    <td><input type="text" id="r_stu_name" name="r_stu_name" value="<?= $student['r_stu_name'] ?? '' ?>" placeholder="Student name" required></td>
                    <td><label for="r_stu_father" class="align">Father's Name :</label></td>
                    <td><input type="text" id="r_stu_father" name="r_stu_father" value="<?= $student['r_stu_father'] ?? '' ?>" placeholder="Father's name" required></td>
                    <td><label for="r_stu_sur" class="align1">Last Name / Surname :</label></td>
                    <td><input type="text" id="r_stu_sur" name="r_stu_sur" value="<?= $student['r_stu_sur'] ?? '' ?>" placeholder="Last Name" required></td>
                </tr>
                <tr>
                    <td><label for="r_p_add">(पत्ता) Premanent Address :<br> <input style="width: 8%;" type="checkbox" id="same_address"><span style="font-size: 14px; color: red;"> Same Residential & Parents Address</span> </label></td>
                    <td colspan="5"><input type="text" id="r_p_add" name="r_p_add" value="<?= $student['r_p_add'] ?? '' ?>" placeholder="कायमचा पत्ता / Permanent Address" required></td>
                </tr>
                <tr>
                    <td><label for="r_stu_vil">(गाव) Village :</label></td>
                    <td><input type="text" id="r_stu_vil" name="r_stu_vil" value="<?= $student['r_stu_vil'] ?? '' ?>" placeholder="गाव / Village" required></td>
                    <td><label for="r_sub_dist" class="align">(तालुका) Sub-District :</label></td>
                    <td><input type="text" id="r_sub_dist" name="r_sub_dist" value="<?= $student['r_sub_dist'] ?? '' ?>" placeholder="तालुका / Sub-District" required></td>
                    <td><label for="r_dist" class="align">(जिल्हा) District :</label></td>
                    <td><input type="text" id="r_dist" name="r_dist" value="<?= $student['r_dist'] ?? '' ?>" placeholder="जिल्हा / District" required></td>
                </tr>
                <tr>
                    <td><label for="r_r_add">(पत्ता) Residential Address :</label></td>
                    <td colspan="5"><input type="text" id="r_r_add" name="r_r_add" value="<?= $student['r_r_add'] ?? '' ?>" placeholder="स्थानिक पत्ता / Residential Address" required></td>
                </tr>
                <tr>
                    <td><label for="r_stu_ph">Personal Ph. No. 1 :</label></td>
                    <td><input type="text" id="r_stu_ph" name="r_stu_ph" maxlength="11" value="<?= $student['r_stu_ph'] ?? '' ?>" placeholder="Personal Ph No." required></td>
                    <td><label for="r_stu_G_ph" class="align">Guardian Ph. No. 2 :</label></td>
                    <td><input type="text" id="r_stu_G_ph" name="r_stu_G_ph" maxlength="11" value="<?= $student['r_stu_G_ph'] ?? '' ?>" placeholder="Guardian Ph. No." required></td>
                </tr>
            </table>
            <div class="btn-div">
                <button type="button" class="next-btn">Next</button>
            </div>
        </div>
        <script>
            function showPreview(input, previewId) {
                const file = input.files[0];
                const preview = document.getElementById(previewId);

                if (file) {
                    if (file.size > 2 * 1024 * 1024) {
                        alert("File size must be less than 2MB");
                        input.value = ""; // reset file input
                        preview.style.display = "none";
                        return;
                    }
                    if (file.type !== "image/png") {
                        alert("Only PNG images are allowed");
                        input.value = "";
                        preview.style.display = "none";
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        preview.src = e.target.result;
                        preview.style.display = "block";
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.style.display = "none";
                }
            }

            // Attach events
            document.getElementById("r_stu_id").addEventListener("change", function () {
                showPreview(this, "preview_r_stu_id");
            });
            document.getElementById("r_stu_sig").addEventListener("change", function () {
                showPreview(this, "preview_r_stu_sig");
            });
        </script>

        <!-- BIRTH DETAILS -->

        <div class="tab-content" id="birthinfo">
            <table>
                <tr>
                    <td colspan="6" class="student_summary" style="font-weight:bold; background:#f0f0f0;">
                        Student Name | Class : 
                    </td>
                </tr>
                <tr>
                    <td><label for="r_stu_B">(गाव) / Birthplace Village :</label></td>
                    <td><input type="text" id="r_stu_B" name="r_stu_B" value="<?= $student['r_stu_B'] ?? '' ?>" placeholder="गाव / Village" required></td>
                    <td><label for="r_stu_B_sub_dist"  class="align2">(तालुका) / Birth Sub-District :</label></td>
                    <td><input type="text" id="r_stu_B_sub_dist" name="r_stu_B_sub_dist" value="<?= $student['r_stu_B_sub_dist'] ?? '' ?>" placeholder="तालुका / Sub-District" required></td>
                    <td><label for="r_stu_B_dist" class="align">(जिल्हा) / Birth District :</label></td>
                    <td><input type="text" id="r_stu_B_dist" name="r_stu_B_dist" value="<?= $student['r_stu_B_dist'] ?? '' ?>" placeholder="जिल्हा / District" required></td>
                </tr>
                <tr>
                    <td><label for="r_stu_B_city">Birth City :</label></td>
                    <td><input type="text" id="r_stu_B_city" name="r_stu_B_city" value="<?= $student['r_stu_B_city'] ?? '' ?>" placeholder="Birth City" required></td>
                    <td><label for="r_stu_B_sta" class="align">Birth State :</label></td>
                    <td><input type="text" id="r_stu_B_sta" name="r_stu_B_sta" value="<?= $student['r_stu_B_sta'] ?? '' ?>" placeholder="Birth State" required></td>
                </tr>
                <tr>
                    <td><label for="r_stu_B_date">Birth Date :</label></td>
                    <td><input type="date" id="r_stu_B_date" name="r_stu_B_date" value="<?= $student['r_stu_B_date'] ?? '' ?>" required></td>
                    <td><label for="r_stu_B_dateW" class="align">Birth date in words :</label></td>
                    <td colspan="2"><input type="text" id="r_stu_B_dateW" name="r_stu_B_dateW" value="<?= $student['r_stu_B_dateW'] ?? '' ?>" placeholder="Birth date in words" required></td>
                </tr>
            </table>
            <div class="btn-div">
                <button type="button" class="next-btn">Next</button>
            </div>
        </div>

        <!-- ADDITIONAL DETAILS -->

        <div class="tab-content" id="addetails">
            <table>
                <tr>
                    <td colspan="6" class="student_summary" style="font-weight:bold; background:#f0f0f0;">
                        Student Name | Class : 
                    </td>
                </tr>
                <tr>
                    <td><label for="r_stu_age">Age :</label></td>
                    <td><input type="text" id="r_stu_age" name="r_stu_age" value="<?= $student['r_stu_age'] ?? '' ?>" required></td>
                    <td><label for="r_stu_disb" class="align">Disabled :</label></td>
                    <td>
                        <select name="r_stu_disb" id="r_stu_disb" required>
                            <option value="No" <?= ($student && $student['r_stu_disb']=='No')?'selected':'' ?>>No</option>
                            <option value="Yes" <?= ($student && $student['r_stu_disb']=='Yes')?'selected':'' ?>>Yes</option>
                        </select>
                    </td>
                    <td><label for="r_stu_mari" class="align">Marital Status :</label></td>
                    <td>
                        <select name="r_stu_mari" id="r_stu_mari" required>
                            <option value="Single" <?= ($student && $student['r_stu_mari']=='Single')?'selected':''?>>Single</option>
                            <option value="Married" <?= ($student && $student['r_stu_mari']=='Married')?'selected':''?>>Married</option>
                            <option value="Other" <?= ($student && $student['r_stu_mari']=='Other')?'selected':''?>>Other</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="r_stu_reli">Student Religion :</label></td>
                    <td>
                        <select id="r_stu_reli" name="r_stu_reli" class="form-select" required>
                            <option value="">-- Select Religion --</option>
                            <?php foreach ($ReliRows as $rrow): ?>
                                <option value="<?= $rrow['fl_nm'] ?>" 
                                    <?= (isset($student['r_stu_reli']) && $rrow['fl_nm'] == $student['r_stu_reli']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($rrow['fl_nm']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><label for="r_stu_cast" class="align">Student Caste :</label></td>
                    <td>
                        <select id="r_stu_cast" name="r_stu_cast" class="form-select" required>
                            <option value="">Select Caste</option>
                            <?php foreach ($CasteRows as $crow): ?>
                                <option value="<?= htmlspecialchars($crow['c_ful_caste']) ?>" 
                                    <?= (isset($student['r_stu_cast']) && $crow['c_ful_caste'] == $student['r_stu_cast']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($crow['c_ful_caste']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><label for="r_stu_castcat" class="align">Student Caste Category :</label></td>
                    <td>
                        <select name="r_stu_castcat" id="r_stu_castcat" required>
                            <option value="">Select Caste Category</option>
                            <option value="DT/VJ(NT-A)" <?= ($student && $student['r_stu_castcat']=='DT/VJ(NT-A)')?'selected':''?>>DT/VJ(NT-A)</option>
                            <option value="NA" <?= ($student && $student['r_stu_castcat']=='NA')?'selected':''?>>NA</option>
                            <option value="NT" <?= ($student && $student['r_stu_castcat']=='NT')?'selected':''?>>NT</option>
                            <option value="NT2(NT-C)" <?= ($student && $student['r_stu_castcat']=='NT2(NT-C)')?'selected':''?>>NT2(NT-C)</option>
                            <option value="NT-C" <?= ($student && $student['r_stu_castcat']=='NT-C')?'selected':''?>>NT-C</option>
                            <option value="OPEN" <?= ($student && $student['r_stu_castcat']=='OPEN')?'selected':''?>>OPEN</option>
                            <option value="OBC" <?= ($student && $student['r_stu_castcat']=='OBC')?'selected':''?>>OBC</option>
                            <option value="SC" <?= ($student && $student['r_stu_castcat']=='SC')?'selected':''?>>SC</option>
                            <option value="SPECIAL BACKWARD CLASS" <?= ($student && $student['r_stu_castcat']=='SPECIAL BACKWARD CLASS')?'selected':''?>>SPECIAL BACKWARD CLASS</option>
                            <option value="VIMUKTA JATI/DENOTIFIED TRIBES" <?= ($student && $student['r_stu_castcat']=='VIMUKTA JATI/DENOTIFIED TRIBES')?'selected':''?>>VIMUKTA JATI/DENOTIFIED TRIBES</option>
                            <option value="VIMUKTA JATI & NOMADIC TRIBE B" <?= ($student && $student['r_stu_castcat']=='VIMUKTA JATI & NOMADIC TRIBE B')?'selected':''?>>VIMUKTA JATI & NOMADIC TRIBE B</option>
                            <option value="VIMUKTA JATI & NOMADIC TRIBE C" <?= ($student && $student['r_stu_castcat']=='VIMUKTA JATI & NOMADIC TRIBE C')?'selected':''?>>VIMUKTA JATI & NOMADIC TRIBE C</option>
                            <option value="VIMUKTA JATI & NOMADIC TRIBE D" <?= ($student && $student['r_stu_castcat']=='VIMUKTA JATI & NOMADIC TRIBE D')?'selected':''?>>VIMUKTA JATI & NOMADIC TRIBE D</option>
                            <option value="VJNT(A)">VJNT(A)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="r_stu_aadr">Aadhar Card No. :</label></td>
                    <td><input type="text" id="r_stu_aadr" name="r_stu_aadr" value="<?= $student['r_stu_aadr'] ?? '' ?>" maxlength="14" placeholder="XXXX XXXX XXXX" required></td>
                    <td><label for="r_stu_pan" class="align">PAN card No. :</label></td>
                    <td><input style="text-transform: uppercase;" type="text" id="r_stu_pan" name="r_stu_pan" value="<?= $student['r_stu_pan'] ?? '' ?>" placeholder="10 digits"></td>
                    <td><label for="r_stu_bg" class="align">Blood Group :</label></td>
                    <td>
                        <select name="r_stu_bg" id="r_stu_bg" required>
                            <option value="">Select Blood Group</option>
                            <option value="A+" <?= ($student && $student['r_stu_bg']=='A+') ? 'selected' : '' ?>>A+</option>
                            <option value="A-" <?= ($student && $student['r_stu_bg']=='A-') ? 'selected' : '' ?>>A-</option>
                            <option value="B+" <?= ($student && $student['r_stu_bg']=='B+') ? 'selected' : '' ?>>B+</option>
                            <option value="B-" <?= ($student && $student['r_stu_bg']=='B-') ? 'selected' : '' ?>>B-</option>
                            <option value="AB+" <?= ($student && $student['r_stu_bg']=='AB+') ? 'selected' : '' ?>>AB+</option>
                            <option value="AB-" <?= ($student && $student['r_stu_bg']=='AB-') ? 'selected' : '' ?>>AB-</option>
                            <option value="O+" <?= ($student && $student['r_stu_bg']=='O+') ? 'selected' : '' ?>>O+</option>
                            <option value="O-" <?= ($student && $student['r_stu_bg']=='O-') ? 'selected' : '' ?>>O-</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="r_stu_email">Student's E-mail :</label></td>
                    <td colspan="2"><input type="email" id="r_stu_email" name="r_stu_email" value="<?= $student['r_stu_email'] ?? '' ?>" placeholder="Eg:student@gmail.com" required></td>
                    <td><label for="r_stu_p_email" class="align">Parent's E-mail :</label></td>
                    <td colspan="2"><input type="email" id="r_stu_p_email" name="r_stu_p_email" value="<?= $student['r_stu_p_email'] ?? '' ?>"  placeholder="Eg:parent@gmail.com"></td>
                </tr>
                <tr>
                    <td colspan="6" style="padding: 0; text-align: center;">
                        <i><p style="color: red; margin: 0; padding: 0;">
                            Please enter your email ID and your parent’s email ID correctly. 
                            Your Registration ID will be sent to these emails.
                        </p></i>
                    </td>
                </tr>
                <tr>
                    <td><label for="r_stu_mtoung">Mother Toungue :</label></td>
                    <td>
                        <select name="r_stu_mtoung" id="r_stu_mtoung" required>
                            <option value="Marathi" <?=($student && $student['r_stu_mtoung']=='Marathi') ? 'selected' : '' ?> >Marathi</option>
                            <option value="Hindi" <?=($student && $student['r_stu_mtoung']=='Hindi') ? 'selected' : '' ?> >Hindi</option>
                            <option value="English" <?=($student && $student['r_stu_mtoung']=='English') ? 'selected' : '' ?> >English</option>
                            <option value="Kannad" <?=($student && $student['r_stu_mtoung']=='Kannad') ? 'selected' : '' ?> >Kannad</option>
                            <option value="Bengali" <?=($student && $student['r_stu_mtoung']=='Bengali') ? 'selected' : '' ?> >Bengali</option>
                            <option value="Gujarati" <?=($student && $student['r_stu_mtoung']=='Gujarati') ? 'selected' : '' ?> >Gujarati</option>
                            <option value="Urdu" <?=($student && $student['r_stu_mtoung']=='Urdu') ? 'selected' : '' ?> >Urdu</option>
                            <option value="Odia" <?=($student && $student['r_stu_mtoung']=='Odia') ? 'selected' : '' ?> >Odia</option>
                            <option value="Punjabi" <?=($student && $student['r_stu_mtoung']=='Punjabi') ? 'selected' : '' ?> >Punjabi</option>
                            <option value="Punjabi" <?=($student && $student['r_stu_mtoung']=='Punjabi') ? 'selected' : '' ?> >Punjabi</option>
                            <option value="Sindhi" <?=($student && $student['r_stu_mtoung']=='Sindhi') ? 'selected' : '' ?> >Sindhi</option>
                        </select>
                    </td>
                    <td><label for="r_stu_nati" class="align">Nationality :</label></td>
                    <td>
                        <select name="r_stu_nati" id="r_stu_nati" required>
                            <option value="INDIA" <?= ($student && $student['r_stu_nati'] == 'INDIA') ? 'selected' : ''?> >INDIA</option>
                            <option value="CHINA" <?= ($student && $student['r_stu_nati'] == 'CHINA') ? 'selected' : ''?> >CHINA</option>
                            <option value="USA" <?= ($student && $student['r_stu_nati'] == 'USA') ? 'selected' : ''?> >USA</option>
                            <option value="UK" <?= ($student && $student['r_stu_nati'] == 'UK') ? 'selected' : ''?> >UK</option>
                        </select>
                    </td>
                    <td><label for="r_stu_jb">Student Job/Business :</label></td>
                    <td>
                        <select name="r_stu_jb" id="r_stu_jb" required>
                            <option value="Student" <?= ($student && $student['r_stu_jb'] == 'Student') ? 'selected' : '' ?> >Student</option>
                            <option value="Employed" <?= ($student && $student['r_stu_jb'] == 'Employed') ? 'selected' : '' ?> >Employed</option>
                            <option value="Self Employed" <?= ($student && $student['r_stu_jb'] == 'Self Employed') ? 'selected' : '' ?> >Self Employed</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="r_stu_vot">Voting Card :</label></td>
                    <td>
                        <select name="r_stu_vot" id="r_stu_vot">
                            <option value="No" <?= ($student && $student['r_stu_jb'] == 'No') ? 'selected' : '' ?>>No</option>
                            <option value="Yes" <?= ($student && $student['r_stu_jb'] == 'Yes') ? 'selected' : '' ?>>Yes</option>
                        </select>
                    </td>
                    <td><label for="r_stu_vot_no" class="align">Voting Card No. :</label></td>
                    <td><input style="text-transform: uppercase;" type="text" id="r_stu_vot_no" name="r_stu_vot_no" value="<?= $student['r_stu_vot_no'] ?? '' ?>" ></td>
                    <td><label for="r_stu_org">Willing to Donate Organs (on Death)?</label></td>
                    <td>
                        <select name="r_stu_org" id="r_stu_org">
                            <option value="Yes" <?= ($student && $student['r_stu_org'] == 'Yes') ? 'selected' : '' ?>>Yes</option>
                            <option value="No" <?= ($student && $student['r_stu_org'] == 'No') ? 'selected' : '' ?>>Yes</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><label for="r_stu_sport">Contribution in District / State / National / International levels :</label></td>
                    <td>
                        <select name="r_stu_sport" id="r_stu_sport">
                            <option value="Yes" <?= ($student && $student['r_stu_sport'] == 'Yes') ? 'selected' : '' ?>>Yes</option>
                            <option value="No" <?= ($student && $student['r_stu_sport'] == 'No') ? 'selected' : '' ?>>Yes</option>
                        </select>
                    </td>
                    <td colspan="2"><label for="r_stu_intr_ncc">Interested in Participating in N.C.C/N.S.S?</label></td>
                    <td>
                        <select name="r_stu_intr_ncc" id="r_stu_intr_ncc">
                            <option value="Yes" <?= ($student && $student['r_stu_intr_ncc'] == 'Yes') ? 'selected' : '' ?>>Yes</option>
                            <option value="No" <?= ($student && $student['r_stu_intr_ncc'] == 'No') ? 'selected' : '' ?>>Yes</option>
                        </select>
                    </td>
                </tr>
            </table>
            <div class="btn-div">
                <button type="button" class="next-btn">Next</button>
            </div>
        </div>
        
        <!-- BANK DETAILS -->

        <div class="tab-content" id="bankdet">
            <table>
                <tr>
                    <td colspan="6" class="student_summary" style="font-weight:bold; background:#f0f0f0;">
                        Student Name | Class : 
                    </td>
                </tr>
                <tr>
                    <td><label for="r_stu_bkn">Bank Name :</label></td>
                    <td>
                        <select name="r_stu_bkn" id="r_stu_bkn" required>
                            <option value="Bank of Maharashtra" <?= ($student && $student['r_stu_bkn'] == 'Bank of Maharashtra') ? 'selected' : '' ?> >Bank of Maharashtra</option>
                            <option value="Bank of Baroda" <?= ($student && $student['r_stu_bkn'] == 'Bank of Baroda') ? 'selected' : '' ?> >Bank of Baroda</option>
                            <option value="Bank of India" <?= ($student && $student['r_stu_bkn'] == 'Bank of India') ? 'selected' : '' ?> >Bank of India</option>
                            <option value="State Bank of India" <?= ($student && $student['r_stu_bkn'] == 'State Bank of India') ? 'selected' : '' ?> >State Bank of India</option>
                            <option value="Punjab National Bank" <?= ($student && $student['r_stu_bkn'] == 'Punjab National Bank') ? 'selected' : '' ?> >Punjab National Bank</option>
                            <option value="Canara Bank" <?= ($student && $student['r_stu_bkn'] == 'Canara Bank') ? 'selected' : '' ?> >Canara Bank</option>
                            <option value="Central Bank of India" <?= ($student && $student['r_stu_bkn'] == 'Central Bank of India') ? 'selected' : '' ?> >Central Bank of India</option>
                            <option value="Kotak Mahindra Bank" <?= ($student && $student['r_stu_bkn'] == 'Kotak Mahindra Bank') ? 'selected' : '' ?> >Kotak Mahindra Bank</option>
                            <option value="Union Bank of India" <?= ($student && $student['r_stu_bkn'] == 'Union Bank of India') ? 'selected' : '' ?> >Union Bank of India</option>
                            <option value="Other">Other</option>
                        </select>
                    </td>
                    <td><label for="r_stu_ifsc" class="align">IFSC Code :</label></td>
                    <td><input style="text-transform: uppercase;" type="text" id="r_stu_ifsc" name="r_stu_ifsc" value="<?= $student['r_stu_ifsc'] ?? '' ?>" required></td>
                    <td><label for="r_stu_bkacc" class="align">Bank Account No. :</label></td>
                    <td><input style="text-transform: uppercase;" type="text" id="r_stu_bkacc" name="r_stu_bkacc" value="<?= $student['r_stu_bkacc'] ?? '' ?>" required></td>
                </tr>
                <tr>
                    <td><label for="r_stu_adhr_lnk">Aadhar Linked with Bank :</label></td>
                    <td>
                        <select name="r_stu_adhr_lnk" id="r_stu_adhr_lnk">
                            <option value="Yes" <?= ($student && $student['r_stu_adhr_lnk'] == 'Yes') ? 'selected' : '' ?>>Yes</option>
                            <option value="No" <?= ($student && $student['r_stu_adhr_lnk'] == 'No') ? 'selected' : '' ?>>Yes</option>
                        </select>
                    </td>
                </tr>
            </table>
            <div class="btn-div">
                <button type="button" class="next-btn">Next</button>
            </div>
        </div>

        <!-- ACADEMIC -->

        <div class="tab-content" id="academic">
            <table>
                <tr>
                    <td colspan="6" class="student_summary" style="font-weight:bold; background:#f0f0f0;">
                        Student Name | Class : 
                    </td>
                </tr>
                <tr>
                    <td><h3>Last Exam Details :</h3></td></tr>
                <tr>
                    <th>Last Exam</th>
                    <th>University/Board</th>
                    <th>Seat No.</th>
                    <th>Total Marks Obtained</th>
                    <th>Percentage/Grade</th>
                    <th>School/College</th>
                </tr>
                <tr>
                    <td>
                        <select name="r_stu_exam" id="r_stu_exam">
                            <option value="">Select Board</option>
                            <option value="SSC" <?= ($student && $student['r_stu_exam']=='SSC') ? 'selected' : '' ?> >SSC</option>
                            <option value="CBSC" <?= ($student && $student['r_stu_exam']=='CBSC') ? 'selected' : '' ?> >CBSC</option>
                            <option value="HSC-SCI" <?= ($student && $student['r_stu_exam']=='HSC-SCI') ? 'selected' : '' ?> >HSC-SCI</option>
                            <option value="HSC-COM" <?= ($student && $student['r_stu_exam']=='HSC-COM') ? 'selected' : '' ?> >HSC-COM</option>
                            <option value="HSC-ART" <?= ($student && $student['r_stu_exam']=='HSC-ART') ? 'selected' : '' ?> >HSC-ART</option>
                            <option value="DIPLOMA" <?= ($student && $student['r_stu_exam']=='DIPLOMA') ? 'selected' : '' ?> >DIPLOMA</option>
                        </select>
                    </td>
                    <td><input type="text" name="r_uni" value="<?= $student['r_uni'] ?? '' ?>" ></td>
                    <td><input type="text" name="r_seat" value="<?= $student['r_seat'] ?? '' ?>" ></td>
                    <td><input type="text" name="r_mrk_obt" value="<?= $student['r_mrk_obt'] ?? '' ?>" ></td>
                    <td><input type="text" name="r_perc" value="<?= $student['r_perc'] ?? '' ?>"  class="total-perc"></td>
                    <td><input type="text" name="r_sch" value="<?= $student['r_sch'] ?? '' ?>" ></td>
                </tr>
                <tr>
                    <td colspan="2"><h3>Exam Subjects and Marks :</h3><br></td>
                </tr>
                <tr>
                    <th colspan="2">Subject</th>
                    <th>Marks</th>
                    <th colspan="2">Subject</th>
                    <th>Marks</th>    
                </tr>
                <tr>
                    <td colspan="2"><input type="text" name="r_sub1" value="<?= $student['r_sub1'] ?? '' ?>" placeholder="1)"></td>
                    <td class="fstinp"><input type="number" name="r_mrk1" value="<?= $student['r_mrk1'] ?? '' ?>" class="subject-marks"></td>
                    <td colspan="2"><input type="text" name="r_sub2" value="<?= $student['r_sub2'] ?? '' ?>" placeholder="2)"></td>
                    <td><input type="number" name="r_mrk2" value="<?= $student['r_mrk2'] ?? '' ?>" class="subject-marks"></td>
                </tr>
                <tr>
                    <td colspan="2"><input type="text" name="r_sub3" value="<?= $student['r_sub3'] ?? '' ?>" placeholder="3)"></td>
                    <td class="fstinp"><input type="number" name="r_mrk3" value="<?= $student['r_mrk3'] ?? '' ?>" class="subject-marks"></td>
                    <td colspan="2"><input type="text" name="r_sub4" value="<?= $student['r_sub4'] ?? '' ?>" placeholder="4)"></td>
                    <td><input type="number" name="r_mrk4" value="<?= $student['r_mrk4'] ?? '' ?>" class="subject-marks"></td>
                </tr>
                <tr>
                    <td colspan="2"><input type="text" name="r_sub5" value="<?= $student['r_sub5'] ?? '' ?>" placeholder="5)"></td>
                    <td class="fstinp"><input type="number" name="r_mrk5" value="<?= $student['r_mrk5'] ?? '' ?>" class="subject-marks"></td>
                    <td colspan="2"><input type="text" name="r_sub6" value="<?= $student['r_sub6'] ?? '' ?>" placeholder="6)"></td>
                    <td><input type="number" name="r_mrk6" value="<?= $student['r_mrk6'] ?? '' ?>" class="subject-marks"></td>
                </tr>
                <tr>
                    <td colspan="2"><input type="text" name="r_sub7" value="<?= $student['r_sub7'] ?? '' ?>" placeholder="7)"></td>
                    <td class="fstinp"><input type="number" name="r_mrk7" value="<?= $student['r_mrk7'] ?? '' ?>" class="subject-marks"></td>
                    <td colspan="2"><input type="text" name="r_sub8" value="<?= $student['r_sub8'] ?? '' ?>" placeholder="8)"></td>
                    <td><input type="number" name="r_mrk8" value="<?= $student['r_mrk8'] ?? '' ?>" class="subject-marks"></td>
                </tr>
            </table>
            <div class="btn-div">
                <button type="button" class="next-btn">Next</button>
            </div>
        </div>
 <!-- ************ -->
        <!-- subject selection -->

        <div id="subjects-container" class="tab-content">
            <table>
                <tr>
                    <td colspan="6" class="student_summary" style="font-weight:bold; background:#f0f0f0;">
                        Student Name | Class :
                    </td>
                </tr>
            </table>

            <div id="subject-list"></div>

            <div class="btn-div">
                <button type="button" id="subjectsNextBtn" class="next-btn">Next</button>
            </div>
        </div>
        <?php
            // decode subjects_json from DB
            $student_subjects = [];
            if (!empty($student['subjects_json'])) {
                $student_subjects = json_decode($student['subjects_json'], true);
                foreach (['compulsory', 'optional'] as $key) {
                    $student_subjects[$key] = array_map('intval', $student_subjects[$key] ?? []);
                }
            }
        ?>
        
        <script>
    const studentSubjects = <?= json_encode($student_subjects) ?>;
</script>

<script>
function loadSubjects() {
    const classSelect = document.getElementById("r_stu_admi_cls");
    const className = classSelect.value;
    const studentId = "<?= (int)($student['r_id'] ?? 0) ?>";
    if (!className) return;

    // Send empty POST body so server decides data source
    fetch("get_subjects.php?r_stu_admi_cls=" + encodeURIComponent(className) + "&r_id=" + studentId, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({})
    })
    .then(res => res.text())
    .then(html => {
        document.getElementById("subject-list").innerHTML = html;
        const maxComp = parseInt(document.querySelector("#maxComp")?.value || 0);
        const maxOpt  = parseInt(document.querySelector("#maxOpt")?.value || 0);
        const maxTot  = parseInt(document.querySelector("#maxTot")?.value || 0);
        function enforceSubjectLimits(maxComp, maxOpt, maxTot) {
    // Capture fresh lists (call each time after injecting subjects)
    const compBoxes = Array.from(document.querySelectorAll(".comp-subject"));
    const optBoxes  = Array.from(document.querySelectorAll(".opt-subject"));

    function validateSelection(e) {
        // recompute counts on every change
        const compChecked  = compBoxes.filter(cb => cb.checked).length;
        const optChecked   = optBoxes.filter(cb => cb.checked).length;
        const totalChecked = compChecked + optChecked;

        // If user toggled a compulsory box and now compChecked exceeds allowed
        if (this.classList.contains("comp-subject") && compChecked > maxComp) {
            alert(`You can select only ${maxComp} compulsory subject${maxComp === 1 ? "" : "s"}.`);
            // revert the change
            this.checked = false;
            return;
        }

        // If user toggled an optional box and now optChecked exceeds allowed
        if (this.classList.contains("opt-subject") && optChecked > maxOpt) {
            alert(`You can select only ${maxOpt} optional subject${maxOpt === 1 ? "" : "s"}.`);
            this.checked = false;
            return;
        }

        // Total limit guard
        if (totalChecked > maxTot) {
            alert(`You can select only ${maxTot} subjects in total.`);
            this.checked = false;
            return;
        }
    }

    // Attach handlers to both sets (safe even if empty arrays)
    [...compBoxes, ...optBoxes].forEach(cb => {
        cb.removeEventListener("change", validateSelection); // safe-guard duplicates
        cb.addEventListener("change", validateSelection);
    });
}
    });
}


document.getElementById("r_stu_admi_cls").addEventListener("change", loadSubjects);

document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("r_stu_admi_cls").value) {
        loadSubjects();
    }
});
</script>


        <!-- Documents -->
         
        <?php
            $r_id = $_GET['edit'] ?? null; 
            $studentDocsPath = "uploads/students/$r_id/";

            // Map doc numbers with labels
            $documents = [
                "doc1" => "* Leaving Certificate LC",
                "doc2" => "* Marksheet",
                "doc3" => "* Eligibility Form",
                "doc4" => "* Aadhar Card Zerox",
                "doc5" => "EBC Form",
                "doc6" => "Caste Certificate",
                "doc7" => "Income Certificate",
                "doc8" => "Voting Card Zerox"
            ];
        ?>
        <div class="tab-content" id="docs">
            <table style=" margin-bottom: 20px;">
                <tr>
                    <td colspan="6" class="student_summary" style="font-weight:bold; background:#f0f0f0;">
                        Student Name | Class : 
                    </td>
                </tr>
            </table>
            <div style="text-align: center; align-items: center;">
                <b><p><i style="border-bottom: 2px solid crimson; padding-bottom: 3px; background-color: #f7f70091; padding: 5px 15px; box-shadow: 0px 0px 15px 1px gray; border-radius: 10px;">
                NOTE: Upload <span style="color: red;">*</span> marked documents are MANDATORY. Other documents are optional but can be uploaded now if available.
                </i></p></b>
            </div><br>
            <div style="text-align: center;">
                <b><p>Uploaded file types should be .png / .pdf. Max size: 2 MB per file.</p></b>
            </div><br>
            <table style="width: 70%; margin: auto;" id="docsT">
                <?php foreach ($documents as $docKey => $docLabel): ?>
                    <tr>
                        <td>
                            <?php if (str_starts_with($docLabel, "*")): ?>
                                <span style="color: red;">*</span> <?= str_replace("*", "", $docLabel) ?> :
                            <?php else: ?>
                                <?= $docLabel ?> :
                            <?php endif; ?>
                        </td>

                        <td style="text-align: center;">
                            <?php
                            // Find the file (pdf or png) for this doc
                            $docFile = '';
                            foreach (['pdf', 'png', 'PDF', 'PNG'] as $ext) {
                                $file = $studentDocsPath . $docKey . "_*." . $ext;
                                $matches = glob($file);
                                if (!empty($matches)) {
                                    $docFile = $matches[0];
                                    break;
                                }
                            }
                            ?>

                            <?php if ($docFile): ?>
                                <a href="<?= $docFile ?>" target="_blank">
                                    <span style="border: 1px solid gray; padding: 10px; border-radius: 10px;">
                                        View Document <i class="fa-solid fa-eye"></i>
                                    </span>
                                </a>
                            <?php else: ?>
                                <span style="color: gray;">Not Uploaded</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <div class="btn-div" style="width: 400px; margin: auto; text-align: center;">
                <button type="button" class="next-btn" style="margin-top: 30px;">Next</button>
            </div>
        </div>

        <!-- PARENTS INFO -->

        <div class="tab-content" id="parent">
            <table>
                <tr>
                    <td colspan="6" class="student_summary" style="font-weight:bold; background:#f0f0f0;">
                        Student Name | Class : 
                    </td>
                </tr>
                <tr>
                    <td><label for="r_stu_mother_ph_no">Mother's Mobile No. :</label></td>
                    <td><input type="number" id="r_stu_mother_ph_no" name="r_stu_mother_ph_no" value="<?= $student['r_stu_mother_ph_no'] ?? '' ?>" placeholder="Mother's Ph.No.:" required></td>
                    <td><label for="s_stu_mother_Occ" class="align1">Mother's Occupation :</label></td>
                    <td>
                        <select name="s_stu_mother_Occ" id="s_stu_mother_Occ" required>
                            <option value="House wife" <?= ($student && $student['s_stu_mother_Occ']=='House wife') ? 'selected' : '' ?> >House wife</option>
                            <option value="Job" <?= ($student && $student['s_stu_mother_Occ']=='Job') ? 'selected' : '' ?> >Job</option>
                            <option value="Business" <?= ($student && $student['s_stu_mother_Occ']=='Business') ? 'selected' : '' ?> >Business</option>
                        </select>
                    </td>
                    <td><label for="s_stu_mother_Ophno" class="align1">Mother's Office Ph No. :</label></td>
                    <td><input type="number" id="s_stu_mother_Ophno" name="s_stu_mother_Ophno" value="<?= $student['s_stu_mother_Ophno'] ?? '' ?>" placeholder="Office Ph.No."></td>
                </tr>
                <tr>
                    <td><label for="s_stu_mother_Oadd">Mother's Office Address :</label></td>
                    <td colspan="3"><input type="text" id="s_stu_mother_Oadd" name="s_stu_mother_Oadd" value="<?= $student['s_stu_mother_Oadd'] ?? '' ?>" ></td>
                </tr>    
                <tr>
                    <td><label for="r_stu_father_ph_no">Father's Mobile No. :</label></td>
                    <td><input type="number" id="r_stu_father_ph_no" name="r_stu_father_ph_no" value="<?= $student['r_stu_father_ph_no'] ?? '' ?>" placeholder="Father's Ph.No.:" required></td>
                    <td><label for="r_stu_father_Occ" class="align1">Father's Occupation :</label></td>
                    <td>
                        <select name="r_stu_father_Occ" id="r_stu_father_Occ" required>
                            <option value="Job" <?= ($student && $student['r_stu_father_Occ']='Job') ? 'selected' : '' ?> >Job</option>
                            <option value="Business" <?= ($student && $student['r_stu_father_Occ']='Business') ? 'selected' : '' ?> >Business</option>
                        </select>
                    </td>
                    <td><label for="r_stu_father_Ophno" class="align1">Father's Office Ph No. :</label></td>
                    <td><input type="number" id="r_stu_father_Ophno" name="r_stu_father_Ophno" value="<?= $student['r_stu_father_Ophno'] ?? '' ?>" placeholder="Office Ph.No."></td>
                </tr>
                <tr>
                    <td><label for="r_stu_father_Oadd">Father's Office Address :</label></td>
                    <td colspan="3"><input type="text" id="r_stu_father_Oadd" name="r_stu_father_Oadd" value="<?= $student['r_stu_father_Oadd'] ?? '' ?>" ></td>
                    <td style="background-color: #ffdfe0;"><label for="r_stu_reli" class="align1">Student Religion :</label></td>
                    <td style="background-color: #ffdfe0;">
                        <select id="r_stu_reli" name="r_stu_reli" class="form-select" required disabled>
                            <option value="">-- Select Religion --</option>
                            <?php foreach ($ReliRows as $rrow): ?>
                                <option value="<?= $rrow['fl_nm'] ?>" 
                                    <?= (isset($student['r_stu_reli']) && $rrow['fl_nm'] == $student['r_stu_reli']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($rrow['fl_nm']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="r_stu_p_add">Parent's Address :</label></td>
                    <td colspan="3"><input type="text" id="r_stu_p_add" name="r_stu_p_add" value="<?= $student['r_stu_p_add'] ?? '' ?>" required></td>
                    <td style="background-color: #ffdfe0;"><label for="r_stu_cast" class="align1">Student Caste :</label></td>
                    <td style="background-color: #ffdfe0;">
                        <select id="r_stu_cast" name="r_stu_cast" class="form-select" required disabled>
                            <option value="">Select Caste</option>
                            <?php foreach ($CasteRows as $crow): ?>
                                <option value="<?= htmlspecialchars($crow['c_ful_caste']) ?>" 
                                    <?= (isset($student['r_stu_cast']) && $crow['c_ful_caste'] == $student['r_stu_cast']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($crow['c_ful_caste']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="r_stu_inc">Annual Income :</label></td>
                    <td colspan="2">
                        <select name="r_stu_inc" id="r_stu_inc" required>
                            <option value="> 50,000 Rs" <?= ($student && $student['r_stu_inc']=='> 50,000 Rs') ? 'selected' : '' ?> > > 50,000 Rs</option>
                            <option value="1,00,000 Rs - 2,00,000 Rs" <?= ($student && $student['r_stu_inc']=='1,00,000 Rs-2,00,000 Rs') ? 'selected' : '' ?> >1,00,000 Rs - 2,00,000 Rs</option>
                            <option value="2,00,000 Rs - 3,00,000 Rs" <?= ($student && $student['r_stu_inc']=='2,00,000 Rs-3,00,000 Rs') ? 'selected' : '' ?> >2,00,000 Rs - 3,00,000 Rs</option>
                            <option value="3,00,000 Rs - 4,00,000 Rs" <?= ($student && $student['r_stu_inc']=='3,00,000 Rs-4,00,000 Rs') ? 'selected' : '' ?> >3,00,000 Rs - 4,00,000 Rs</option>
                            <option value="4,00,000 Rs - 5,00,000 Rs" <?= ($student && $student['r_stu_inc']=='4,00,000 Rs-5,00,000 Rs') ? 'selected' : '' ?> >4,00,000 Rs - 5,00,000 Rs</option>
                            <option value="5,00,000 Rs - 6,00,000 Rs" <?= ($student && $student['r_stu_inc']=='5,00,000 Rs-6,00,000 Rs') ? 'selected' : '' ?> >5,00,000 Rs - 6,00,000 Rs</option>
                            <option value="6,00,000 Rs - 7,00,000 Rs" <?= ($student && $student['r_stu_inc']=='6,00,000 Rs-7,00,000 Rs') ? 'selected' : '' ?> >6,00,000 Rs - 7,00,000 Rs</option>
                            <option value="7,00,000 Rs - 8,00,000 Rs" <?= ($student && $student['r_stu_inc']=='7,00,000 Rs-8,00,000 Rs') ? 'selected' : '' ?> >7,00,000 Rs - 8,00,000 Rs</option>
                            <option value="< 8,00,000 Rs"  <?= ($student && $student['r_stu_inc']=='< 8,00,000 Rs') ? 'selected' : '' ?>  > < 8,00,000 Rs</option>
                        </select>
                    </td>
                    <td></td>
                    <td style="background-color: #ffdfe0;"><label for="r_stu_castcat" class="align1">Student Caste Category :</label></td>
                    <td style="background-color: #ffdfe0;">
                        <select name="r_stu_castcat" id="r_stu_castcat" required disabled>
                            <option value="">Select Caste Category</option>
                            <option value="DT/VJ(NT-A)" <?= ($student && $student['r_stu_castcat']=='DT/VJ(NT-A)')?'selected':''?>>DT/VJ(NT-A)</option>
                            <option value="NA" <?= ($student && $student['r_stu_castcat']=='NA')?'selected':''?>>NA</option>
                            <option value="NT" <?= ($student && $student['r_stu_castcat']=='NT')?'selected':''?>>NT</option>
                            <option value="NT2(NT-C)" <?= ($student && $student['r_stu_castcat']=='NT2(NT-C)')?'selected':''?>>NT2(NT-C)</option>
                            <option value="NT-C" <?= ($student && $student['r_stu_castcat']=='NT-C')?'selected':''?>>NT-C</option>
                            <option value="OPEN" <?= ($student && $student['r_stu_castcat']=='OPEN')?'selected':''?>>OPEN</option>
                            <option value="OBC" <?= ($student && $student['r_stu_castcat']=='OBC')?'selected':''?>>OBC</option>
                            <option value="SC" <?= ($student && $student['r_stu_castcat']=='SC')?'selected':''?>>SC</option>
                            <option value="SPECIAL BACKWARD CLASS" <?= ($student && $student['r_stu_castcat']=='SPECIAL BACKWARD CLASS')?'selected':''?>>SPECIAL BACKWARD CLASS</option>
                            <option value="VIMUKTA JATI/DENOTIFIED TRIBES" <?= ($student && $student['r_stu_castcat']=='VIMUKTA JATI/DENOTIFIED TRIBES')?'selected':''?>>VIMUKTA JATI/DENOTIFIED TRIBES</option>
                            <option value="VIMUKTA JATI & NOMADIC TRIBE B" <?= ($student && $student['r_stu_castcat']=='VIMUKTA JATI & NOMADIC TRIBE B')?'selected':''?>>VIMUKTA JATI & NOMADIC TRIBE B</option>
                            <option value="VIMUKTA JATI & NOMADIC TRIBE C" <?= ($student && $student['r_stu_castcat']=='VIMUKTA JATI & NOMADIC TRIBE C')?'selected':''?>>VIMUKTA JATI & NOMADIC TRIBE C</option>
                            <option value="VIMUKTA JATI & NOMADIC TRIBE D" <?= ($student && $student['r_stu_castcat']=='VIMUKTA JATI & NOMADIC TRIBE D')?'selected':''?>>VIMUKTA JATI & NOMADIC TRIBE D</option>
                            <option value="VJNT(A)">VJNT(A)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="r_stu_rel">Relation With Student :</label></td>
                    <td><input type="text" id="r_stu_rel" name="r_stu_rel" value="<?= $student['r_stu_rel'] ?? '' ?>" placeholder="Eg: Daughter" required></td>
                    <td></td>
                    <td></td>
                    <td class="cau"><label for="r_stu_type" class="align1">Fee Type :</label></td>
                    <td class="cau">
                        <select name="r_stu_type" id="r_stu_type" required>
                            <option value="">-- Select Type --</option>
                            <?php
                            $enumResult = $conn->query("SHOW COLUMNS FROM student_registration LIKE 'type'");
                            $enumRow = $enumResult->fetch_assoc();
                            $enumValues = str_replace(["enum(", ")", "'"], "", $enumRow['Type']);
                            $enumArray = explode(",", $enumValues);

                            foreach ($enumArray as $val) {
                                $val = trim($val);
                                echo "<option value='$val'>$val</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="cau"><label for="" class="align1">Fee Amount :</label></td>
                    <td class="cau" style="text-align: center;"><span id="fee_display" style="font-weight:bold; color:green; text-align: center;"></span></td>
                </tr>
                <script>
                    function updateFeeAmount() {
    let feeType = document.getElementById("r_stu_type").value;
    let studentId = document.getElementById("edit_id").value;
    let classFull = document.getElementById("r_stu_admi_cls").value;

    if (feeType && classFull && studentId) {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "get_fee.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            document.getElementById("fee_display").innerText =
                (this.responseText !== "N/A") ? " ₹" + this.responseText : "Fee not set";
        };
        xhr.send(
            "student_id=" + studentId +
            "&fee_type=" + encodeURIComponent(feeType) +
            "&class_full=" + encodeURIComponent(classFull)
        );
    } else {
        document.getElementById("fee_display").innerText = "";
    }
}

document.getElementById("r_stu_type").addEventListener("change", updateFeeAmount);
document.getElementById("r_stu_admi_cls").addEventListener("change", updateFeeAmount);

window.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById("r_stu_type").value && document.getElementById("r_stu_admi_cls").value) {
        updateFeeAmount();
    }
});

                </script>
            </table>
            <div class="btn-div">
                <button type="submit" class="submit-btn">Accept and ADMIT</button>
            </div>
        </div>
    </form><hr style="margin-top: 40px;">

    <div class="secBlock">
    <h3 style="text-align:center; margin-bottom: 10px;">Registrations</h3>
    <hr style="width: 80%; margin: auto; border-radius: 50%;">

    <div class="search-bar" style="margin-bottom:10px;">
        <table>
            <tr>
                <td><label>Search by Reg ID: <input type="text" id="searchRid"></label></td>
                <td><label>Search by Name / Class / Religion / Caste: <input type="text" id="searchKeyword"></label></td>
            </tr>
        </table>
    </div>

    <?php
        $result = $conn->query("SELECT * FROM student_registration WHERE status='Registered'");
        if ($result && $result->num_rows > 0) {
            echo "<table class='display-table' id='registrationsTable'>";
            echo "<thead><tr>
                    <th>R-ID</th>
                    <th>Student Name</th>
                    <th>Student DOB<br>YYYY-MM-DD</th>
                    <th>Registered Class</th>
                    <th>Fee Category</th>
                    <th>Student Religion</th>
                    <th>Student Caste</th>
                    <th>Student Caste<br>Category</th>
                  </tr></thead><tbody>";

            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td><a class='update-link' href='verify.php?edit={$row['r_id']}'>{$row['r_id']}</a></td>";
                echo "<td>{$row['r_stu_name']} {$row['r_stu_father']} {$row['r_stu_sur']}</td>";
                echo "<td>{$row['r_stu_B_date']}</td>";
                echo "<td>{$row['r_stu_admi_cls']}</td>";
                echo "<td>{$row['type']}</td>";
                echo "<td>{$row['r_stu_reli']}</td>";
                echo "<td>{$row['r_stu_cast']}</td>";
                echo "<td>{$row['r_stu_castcat']}</td>";
                echo "</tr>";
            }

            echo "</tbody></table>";
        } else {
            echo "<p style='text-align:center;'>No Registrations found.</p>";
        }
    ?>
</div>

<!-- DataTables -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#registrationsTable').DataTable({
        pageLength: 10,
        lengthChange: false,
        info: true
    });

    // Search by Reg ID
    $('#searchRid').on('keyup', function() {
        table.column(0).search(this.value).draw();
    });

    // Search by Name / Class / Religion / Caste
    $('#searchKeyword').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Filter by Date (From & To)
    $('#dateFrom, #dateTo').on('change', function() {
        var from = $('#dateFrom').val();
        var to = $('#dateTo').val();

        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                var dob = data[2] || ""; // Student DOB column
                if((!from && !to) || (dob >= from && dob <= to)) {
                    return true;
                }
                return false;
            }
        );
        table.draw();
        $.fn.dataTable.ext.search.pop();

        // Optional: reload form instead of just filtering
        // $('#fltForm').submit(); // uncomment if you have a form around search inputs
    });

    // Keep edit link clickable
    $('#registrationsTable tbody').on('click', 'tr', function(e) {
        if(!$(e.target).is('a')) {
            var link = $(this).find('a.update-link').attr('href');
            if(link) window.location.href = link;
        }
    });
});
</script>



    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const tabs = document.querySelectorAll(".tab");
        const contents = document.querySelectorAll(".tab-content");
        const nextButtons = document.querySelectorAll(".next-btn");
        let currentTab = 0;

        const tabValidStatus = Array(contents.length).fill(false);

        function updateTabEmoji(index, valid) {
            const tab = tabs[index];
            let span = tab.querySelector(".status-icon");
            if (!span) {
                span = document.createElement("span");
                span.className = "status-icon";
                tab.appendChild(span);
            }
            span.textContent = valid ? "✅" : "❌";
        }

        function showTab(index) {
            contents.forEach((content, i) => {
                content.classList.remove("active", "show");
                tabs[i].classList.remove("active");
                if (i === index) {
                    content.classList.add("active");
                    setTimeout(() => content.classList.add("show"), 50);
                    tabs[i].classList.add("active");
                }
            });
            currentTab = index;
        }

        tabs.forEach((tab, i) => tab.addEventListener("click", () => showTab(i)));
        showTab(0);

        // --- Phone formatting ---
        function formatPhone(input) {
            let val = input.value.replace(/\D/g, "").slice(0, 10);
            if (val.length > 5) val = val.substring(0, 5) + " " + val.substring(5);
            input.value = val;
        }
        ["r_stu_ph", "r_stu_G_ph"].forEach(id => {
            document.getElementById(id).addEventListener("input", function () {
                let cursorPos = this.selectionStart;
                let prevLength = this.value.length;
                formatPhone(this);
                let newLength = this.value.length;
                this.selectionStart = this.selectionEnd = cursorPos + (newLength - prevLength);
            });
        });

        // --- Aadhaar, PAN, Voter ---
        const aadhar = document.getElementById("r_stu_aadr");
        aadhar.addEventListener("input", () => {
            let raw = aadhar.value.replace(/\D/g, "").slice(0, 12);
            let parts = raw.match(/.{1,4}/g);
            aadhar.value = parts ? parts.join(" ") : raw;
        });

        const pan = document.getElementById("r_stu_pan");
        pan.addEventListener("input", () => {
            pan.value = pan.value.toUpperCase().replace(/[^A-Z0-9]/g, "").slice(0, 10);
        });

        const voter = document.getElementById("r_stu_vot_no");
        voter.addEventListener("input", () => {
            voter.value = voter.value.toUpperCase().replace(/[^A-Z0-9]/g, "").slice(0, 10);
        });

        // --- Address checkbox ---
        const permInput = document.getElementById("r_p_add");
        const resInput = document.getElementById("r_r_add");
        const parentInput = document.getElementById("r_stu_p_add");
        const sameCheckbox = document.getElementById("same_address");

        sameCheckbox.addEventListener("change", () => {
            if (sameCheckbox.checked) {
                resInput.value = permInput.value;
                parentInput.value = permInput.value;
            }
        });
        permInput.addEventListener("input", () => {
            if (sameCheckbox.checked) {
                resInput.value = permInput.value;
                parentInput.value = permInput.value;
            }
        });

        // --- Birth Date in words ---
        const ones = ["", "First", "Second", "Third", "Fourth", "Fifth", "Sixth", "Seventh", "Eighth", "Ninth", "Tenth",
            "Eleventh", "Twelfth", "Thirteenth", "Fourteenth", "Fifteenth", "Sixteenth", "Seventeenth",
            "Eighteenth", "Nineteenth"];
        const tens = ["", "", "Twentieth", "Thirtieth"];
        const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        const onesNum = ["", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine"];
        const teensNum = ["Ten", "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen", "Nineteen"];
        const tensNum = ["", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];

        function dayToWords(d) {
        const ones = ["", "First", "Second", "Third", "Fourth", "Fifth", "Sixth", "Seventh", "Eighth", "Ninth"];
        if (d <= 20) {
            const first20 = ["", "First", "Second", "Third", "Fourth", "Fifth", "Sixth", "Seventh", "Eighth", "Ninth",
                            "Tenth", "Eleventh", "Twelfth", "Thirteenth", "Fourteenth", "Fifteenth", "Sixteenth", 
                            "Seventeenth", "Eighteenth", "Nineteenth", "Twentieth"];
            return first20[d];
        } else if (d < 30) {
            return "Twenty " + ones[d - 20];
        } else if (d === 30) {
            return "Thirtieth";
        } else if (d === 31) {
            return "Thirty First";
        }
    }

        function yearToWords(y) {
            const thousands = Math.floor(y / 1000);
            const hundreds = Math.floor((y % 1000) / 100);
            const lastTwo = y % 100;
            let str = "";

            if (thousands) str += onesNum[thousands] + " Thousand ";
            if (hundreds) str += onesNum[hundreds] + " Hundred ";

            if (lastTwo) {
                if (lastTwo < 10) str += onesNum[lastTwo];
                else if (lastTwo >= 10 && lastTwo < 20) str += teensNum[lastTwo - 10];
                else {
                    const tensPlace = Math.floor(lastTwo / 10);
                    const onesPlace = lastTwo % 10;
                    str += tensNum[tensPlace];
                    if (onesPlace) str += " " + onesNum[onesPlace];
                }
            }

            return str.trim();
        }

        const dobInput = document.getElementById("r_stu_B_date");
        const dobWordsInput = document.getElementById("r_stu_B_dateW");
        const ageInput = document.getElementById("r_stu_age");

        dobInput.addEventListener("change", () => {
            if (!dobInput.value) {
                dobWordsInput.value = "";
                ageInput.value = "";
                return;
            }

            const dob = new Date(dobInput.value);
            const today = new Date();
            let age = today.getFullYear() - dob.getFullYear();
            const m = today.getMonth() - dob.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
            ageInput.value = age;

            if (age < 15) {
                alert("Age must be at least 15");
                dobInput.value = "";
                dobWordsInput.value = "";
                ageInput.value = "";
                return;
            }

            const dayWord = dayToWords(dob.getDate());
            const monthWord = months[dob.getMonth()];
            const yearWord = yearToWords(dob.getFullYear());
            dobWordsInput.value = `${dayWord} ${monthWord} ${yearWord}`;
        });

        // --- Next button validations ---
        nextButtons.forEach(btn => {
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                const currentContent = contents[currentTab];
                let valid = true;

                const selects = currentContent.querySelectorAll("select[required]");
                for (let sel of selects) {
                    if (!sel.value || sel.value === "") {
                        alert(`Please select ${sel.previousElementSibling ? sel.previousElementSibling.innerText : "an option"}`);
                        sel.focus();
                        valid = false;
                        return;
                    }
                }

                if (currentContent.id === "personal") {
                    let ph1 = document.getElementById("r_stu_ph").value.replace(/\s/g, "");
                    let ph2 = document.getElementById("r_stu_G_ph").value.replace(/\s/g, "");
                    if (ph1.length !== 10) { alert("Personal phone must be 10 digits."); valid = false; return; }
                    if (ph2.length !== 10) { alert("Guardian phone must be 10 digits."); valid = false; return; }
                }

                if (currentContent.id === "birthinfo") {
                    const dobInput = document.getElementById("r_stu_B_date");
                    const ageInput = document.getElementById("r_stu_age");
                    if (!dobInput.value) { alert("Please select Birth Date"); valid = false; return; }
                }

                if (currentContent.id === "addetails") {
                    const aadhaarNum = aadhar.value.replace(/\s/g, "");
                    if (aadhaarNum.length !== 12) { alert("Aadhar must be 12 digits"); valid = false; aadhar.focus(); return; }
                    if (pan.value && pan.value.length !== 10) { alert("PAN must be 10 characters"); valid = false; pan.focus(); return; }
                    if (voter.value && voter.value.length !== 10) { alert("Voter ID must be 10 characters"); valid = false; voter.focus(); return; }
                }

                if (currentContent.id === "bankdet") {
                    const ifsc = document.getElementById("r_stu_ifsc").value.trim();
                    const acc = document.getElementById("r_stu_bkacc").value.trim();
                    if (!/^[A-Z]{4}[A-Z0-9]{7}$/.test(ifsc)) { alert("Invalid IFSC"); valid = false; return; }
                    if (!/^\d{9,20}$/.test(acc)) { alert("Account must be 9-20 digits"); valid = false; return; }
                }

                if (currentContent.id === "academic") {
                    const exam = document.getElementById("r_stu_exam");
                    if (!exam || exam.value === "") { alert("Select Last Exam"); valid = false; return; }

                    const mainSubjects = currentContent.querySelectorAll('input.subject-marks');
                    for (let i = 0; i < 4; i++) {
                        const subj = mainSubjects[i];
                        if (!subj || subj.value.trim() === "") { alert(`Enter marks for subject ${i + 1}`); valid = false; return; }
                        const num = parseFloat(subj.value);
                        if (isNaN(num) || num < 0 || num > 100) { alert(`Marks for subject ${i+1} must be 0-100`); valid = false; return; }
                    }

                    const perc = parseFloat(currentContent.querySelector('input.total-perc').value);
                    if (isNaN(perc) || perc < 0 || perc > 100) { alert("Total % must be 0-100"); valid = false; return; }
                }

                if (currentContent.id === "docs") {
                    const MAX_SIZE = 2 * 1024 * 1024;
                    const allowedExtGeneral = ["png", "pdf"];
                    const allowedExtImages = ["png"];
                    const requiredDocs = currentContent.querySelectorAll(".doc-required");
                    const allFiles = currentContent.querySelectorAll("input[type='file']");

                    for (let input of requiredDocs) {
                        if (!input.files.length) {
                            alert("Upload all required documents");
                            valid = false;
                            return;
                        }
                    }

                    for (let input of allFiles) {
                        if (!input.files.length) continue;
                            const file = input.files[0];
                            const ext = file.name.split(".").pop().toLowerCase();

                            // Special case: ID & signature must be PNG only
                            if (["r_stu_id", "r_stu_sig"].includes(input.id)) {
                                if (!allowedExtImages.includes(ext)) {
                                    alert(`${file.name} must be a PNG file`);
                                    input.value = "";
                                    valid = false;
                                    return;
                                }
                            } else {
                                if (!allowedExtGeneral.includes(ext)) {
                                    alert(`${file.name} must be PNG or PDF`);
                                    input.value = "";
                                    valid = false;
                                    return;
                                }
                            }

                            if (file.size > MAX_SIZE) {
                                alert(`${file.name} exceeds 2MB`);
                                input.value = "";
                                valid = false;
                                return;
                            }
                        }
                    }
                if (currentContent.id === "subjects-container") {
    const maxComp = parseInt(document.querySelector("#maxComp")?.value || 0, 10);
    const maxOpt  = parseInt(document.querySelector("#maxOpt")?.value || 0, 10);
    const maxTot  = parseInt(document.querySelector("#maxTot")?.value || 0, 10);

    const compBoxes = Array.from(document.querySelectorAll(".comp-subject"));
    const optBoxes  = Array.from(document.querySelectorAll(".opt-subject"));

    const compChecked  = compBoxes.filter(cb => cb.checked).length;
    const optChecked   = optBoxes.filter(cb => cb.checked).length;
    const totalChecked = compChecked + optChecked;

    // MUST be exactly maxComp compulsory
    if (compChecked !== maxComp) {
        alert(`You must select exactly ${maxComp} compulsory subject${maxComp === 1 ? "" : "s"}.`);
        valid = false;
        return;
    }

    // Optional cannot exceed allowed number
    if (optChecked > maxOpt) {
        alert(`You can select maximum ${maxOpt} optional subject${maxOpt === 1 ? "" : "s"}.`);
        valid = false;
        return;
    }

    // Total must match required total
    if (totalChecked !== maxTot) {
        alert(`You must select exactly ${maxTot} subjects in total (compulsory + optional).`);
        valid = false;
        return;
    }
}

                updateTabEmoji(currentTab, valid);

                if (valid && currentTab < contents.length - 1) {
                    showTab(currentTab + 1);
                }
            });
    });
    // --- Parent submit button ---
    const parentTab = document.getElementById("parent");
    const submitBtn = parentTab.querySelector(".submit-btn");
        submitBtn.addEventListener("click", (e) => {
            e.preventDefault();
            const motherPh = document.getElementById("r_stu_mother_ph_no").value.trim();
            const fatherPh = document.getElementById("r_stu_father_ph_no").value.trim();
            const feeType = document.getElementById("r_stu_type").value;
            if (motherPh.length !== 10) { alert("Mother's mobile must be 10 digits"); return; }
            if (fatherPh.length !== 10) { alert("Father's mobile must be 10 digits"); return; }

            const motherOffice = document.getElementById("s_stu_mother_Ophno").value.trim();
            const fatherOffice = document.getElementById("r_stu_father_Ophno").value.trim();
            if (motherOffice && motherOffice.length !== 10) { alert("Mother's office number must be 10 digits"); return; }
            if (fatherOffice && fatherOffice.length !== 10) { alert("Father's office number must be 10 digits"); return; }
            if (!feeType) {
                alert("Please select a Fee Type (Paying / Non Paying) before submitting.");
                return;
            }
            parentTab.closest("form").submit();
        });

    });
</script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const nameInput = document.getElementById("r_stu_name");
    const fatherInput = document.getElementById("r_stu_father");
    const surnameInput = document.getElementById("r_stu_sur");
    const classSelect = document.getElementById("r_stu_admi_cls");

    function updateSummary() {
        const fullName = `${nameInput.value} ${fatherInput.value} ${surnameInput.value}`.trim();
        const className = classSelect.options[classSelect.selectedIndex]?.text || "";
        const text = fullName ? `Student Name : ${fullName} | Class : ${className}` : "Student Name | Class";
        
        // Update all summary elements currently in the DOM
        document.querySelectorAll(".student_summary").forEach(el => el.textContent = text);
    }

    // Update summary whenever inputs change
    [nameInput, fatherInput, surnameInput, classSelect].forEach(el => {
        el.addEventListener("input", updateSummary);
        el.addEventListener("change", updateSummary);
    });

    // Update when switching tabs (because some tabs may render hidden summaries)
    const tabs = document.querySelectorAll(".tab");
    tabs.forEach(tab => {
        tab.addEventListener("click", updateSummary);
    });

    // Initialize summary on page load
    updateSummary();
});

</script>
</body>
</html>