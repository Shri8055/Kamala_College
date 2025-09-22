<?php
ob_start();
session_start();
include_once('../includes/header.php');
include '../includes/db.php'; // DB connection

require '../vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ===== FETCH CLASS, RELIGION, CASTE =====
$classRows = [];
$result = $conn->query("SELECT cls_id, cls_ful_nm, term_title, term_label  FROM feecls ORDER BY cls_ful_nm DESC");
while ($row = $result->fetch_assoc()) $classRows[] = $row;

$ReliRows = [];
$result = $conn->query("SELECT rel_id, fl_nm FROM religion_m ORDER BY rel_id ASC");
while ($rrow = $result->fetch_assoc()) $ReliRows[] = $rrow;
 
$CasteRows = [];
$result = $conn->query("SELECT caste_id, c_ful_caste FROM caste_m ORDER BY caste_id ASC");
while ($crow = $result->fetch_assoc()) $CasteRows[] = $crow;

// ===== FORM SUBMISSION =====
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1) Generate unique 4-digit r_id
    do {
        $r_id = mt_rand(1000, 9999);
        $check = $conn->query("SELECT 1 FROM student_registration WHERE r_id = {$r_id} LIMIT 1");
    } while ($check && $check->num_rows > 0);

    // 2) Student upload folder
    $uploadDir = __DIR__ . "/uploads/students/" . $r_id . "/";
    $publicDir = "uploads/students/" . $r_id . "/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    function uploadFile($inputName, $destAbsDir, $destRelDir) {
        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) return null;
        $orig = basename($_FILES[$inputName]['name']);
        $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', $orig);
        $ext  = pathinfo($safe, PATHINFO_EXTENSION);
        $new  = $inputName . '_' . time() . '_' . mt_rand(1000, 9999) . ($ext ? ".".$ext : "");
        $abs  = $destAbsDir . $new;
        if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $abs)) {
            return $destRelDir . $new;
        }
        return null;
    }

    // File uploads
    $r_stu_id  = uploadFile("r_stu_id",  $uploadDir, $publicDir);
    $r_stu_sig = uploadFile("r_stu_sig", $uploadDir, $publicDir);
    $doc1 = uploadFile("doc1", $uploadDir, $publicDir);
    $doc2 = uploadFile("doc2", $uploadDir, $publicDir);
    $doc3 = uploadFile("doc3", $uploadDir, $publicDir);
    $doc4 = uploadFile("doc4", $uploadDir, $publicDir);
    $doc5 = uploadFile("doc5", $uploadDir, $publicDir);
    $doc6 = uploadFile("doc6", $uploadDir, $publicDir);
    $doc7 = uploadFile("doc7", $uploadDir, $publicDir);
    $doc8 = uploadFile("doc8", $uploadDir, $publicDir);

    $type = $_POST['type'] ?? 'paying';

    /* =========================
       NEW: capture subjects
       - Expect: compulsory[] and optional[] from get_subjects.php
       - Also expect: hidden acad_yr (e.g., 2024-25)
    ========================== */
    $compSel = isset($_POST['compulsory']) ? array_values(array_filter((array)$_POST['compulsory'])) : [];
    $optSel  = isset($_POST['optional'])   ? array_values(array_filter((array)$_POST['optional']))   : [];

    // (optional) normalize whitespace
    $compSel = array_map(function($v){ return trim((string)$v); }, $compSel);
    $optSel  = array_map(function($v){ return trim((string)$v); }, $optSel);

    // remove duplicates just in case
    $compSel = array_values(array_unique($compSel));
    $optSel  = array_values(array_unique($optSel));

    $compSel = $_POST['compulsory'] ?? [];
    $optSel  = $_POST['optional'] ?? [];
    $subjects_json = json_encode([
        'compulsory' => array_values(array_unique($compSel)),
        'optional'   => array_values(array_unique($optSel)),
    ], JSON_UNESCAPED_UNICODE);

    $comp_count = count($compSel);
    $opt_count  = count($optSel);
    $tot_count  = $comp_count + $opt_count;

    // acad_yr comes from a hidden input that get_subjects.php prints
    $acad_yr = date("Y") . "-" . (date("Y")+1);  // example: 2025-2026

    // Build one array that exactly matches your table columns (except created_at, which has default)
    $data = [
        'r_id' => $r_id,

        'r_stu_admi_cls' => $_POST['r_stu_admi_cls'] ?? null,
        'r_stu_tit'      => $_POST['r_stu_tit'] ?? null,
        'r_stu_mother'   => $_POST['r_stu_mother'] ?? null,
        'r_stu_gen'      => $_POST['r_stu_gen'] ?? null,
        'r_stu_id'       => $r_stu_id,
        'r_stu_sig'      => $r_stu_sig,
        'r_stu_name'     => $_POST['r_stu_name'] ?? null,
        'r_stu_father'   => $_POST['r_stu_father'] ?? null,
        'r_stu_sur'      => $_POST['r_stu_sur'] ?? null,

        'r_p_add'        => $_POST['r_p_add'] ?? null,
        'r_stu_vil'      => $_POST['r_stu_vil'] ?? null,
        'r_sub_dist'     => $_POST['r_sub_dist'] ?? null,
        'r_dist'         => $_POST['r_dist'] ?? null,
        'r_r_add'        => $_POST['r_r_add'] ?? null,
        'r_stu_ph'       => $_POST['r_stu_ph'] ?? null,
        'r_stu_G_ph'     => $_POST['r_stu_G_ph'] ?? null,

        'r_stu_B'            => $_POST['r_stu_B'] ?? null,
        'r_stu_B_sub_dist'   => $_POST['r_stu_B_sub_dist'] ?? null,
        'r_stu_B_dist'       => $_POST['r_stu_B_dist'] ?? null,
        'r_stu_B_city'       => $_POST['r_stu_B_city'] ?? null,
        'r_stu_B_sta'        => $_POST['r_stu_B_sta'] ?? null,
        'r_stu_B_date'       => $_POST['r_stu_B_date'] ?? null,
        'r_stu_B_dateW'      => $_POST['r_stu_B_dateW'] ?? null,
        'r_stu_age'          => $_POST['r_stu_age'] ?? null,

        'r_stu_disb'     => $_POST['r_stu_disb'] ?? null,
        'r_stu_mari'     => $_POST['r_stu_mari'] ?? null,
        'r_stu_reli'     => $_POST['r_stu_reli'] ?? null,
        'r_stu_cast'     => $_POST['r_stu_cast'] ?? null,
        'r_stu_castcat'  => $_POST['r_stu_castcat'] ?? null,

        'r_stu_aadr'     => $_POST['r_stu_aadr'] ?? null,
        'r_stu_pan'      => $_POST['r_stu_pan'] ?? null,
        'r_stu_bg'       => $_POST['r_stu_bg'] ?? null,
        'r_stu_email'    => $_POST['r_stu_email'] ?? null,
        'r_stu_p_email'  => $_POST['r_stu_p_email'] ?? null,
        'r_stu_mtoung'   => $_POST['r_stu_mtoung'] ?? null,
        'r_stu_nati'     => $_POST['r_stu_nati'] ?? null,
        'r_stu_jb'       => $_POST['r_stu_jb'] ?? null,
        'r_stu_vot'      => $_POST['r_stu_vot'] ?? null,
        'r_stu_vot_no'   => $_POST['r_stu_vot_no'] ?? null,
        'r_stu_org'      => $_POST['r_stu_org'] ?? null,
        'r_stu_sport'    => $_POST['r_stu_sport'] ?? null,
        'r_stu_intr_ncc' => $_POST['r_stu_intr_ncc'] ?? null,

        // NEW: subjects + counts + acad year
        'subjects_json' => $subjects_json,
        'comp_count'    => (string)$comp_count,
        'opt_count'     => (string)$opt_count,
        'tot_count'     => (string)$tot_count,
        'acad_yr'       => $acad_yr,

        'r_stu_bkn'      => $_POST['r_stu_bkn'] ?? null,
        'r_stu_ifsc'     => $_POST['r_stu_ifsc'] ?? null,
        'r_stu_bkacc'    => $_POST['r_stu_bkacc'] ?? null,
        'r_stu_adhr_lnk' => $_POST['r_stu_adhr_lnk'] ?? null,

        'r_stu_exam' => $_POST['r_stu_exam'] ?? null,
        'r_uni'      => $_POST['r_uni'] ?? null,
        'r_seat'     => $_POST['r_seat'] ?? null,
        'r_mrk_obt'  => $_POST['r_mrk_obt'] ?? null,
        'r_perc'     => $_POST['r_perc'] ?? null,
        'r_sch'      => $_POST['r_sch'] ?? null,

        'r_sub1' => $_POST['r_sub1'] ?? null, 'r_mrk1' => $_POST['r_mrk1'] ?? null,
        'r_sub2' => $_POST['r_sub2'] ?? null, 'r_mrk2' => $_POST['r_mrk2'] ?? null,
        'r_sub3' => $_POST['r_sub3'] ?? null, 'r_mrk3' => $_POST['r_mrk3'] ?? null,
        'r_sub4' => $_POST['r_sub4'] ?? null, 'r_mrk4' => $_POST['r_mrk4'] ?? null,
        'r_sub5' => $_POST['r_sub5'] ?? null, 'r_mrk5' => $_POST['r_mrk5'] ?? null,
        'r_sub6' => $_POST['r_sub6'] ?? null, 'r_mrk6' => $_POST['r_mrk6'] ?? null,
        'r_sub7' => $_POST['r_sub7'] ?? null, 'r_mrk7' => $_POST['r_mrk7'] ?? null,
        'r_sub8' => $_POST['r_sub8'] ?? null, 'r_mrk8' => $_POST['r_mrk8'] ?? null,

        'doc1' => $doc1, 'doc2' => $doc2, 'doc3' => $doc3, 'doc4' => $doc4,
        'doc5' => $doc5, 'doc6' => $doc6, 'doc7' => $doc7, 'doc8' => $doc8,

        'r_stu_mother_ph_no' => $_POST['r_stu_mother_ph_no'] ?? null,
        's_stu_mother_Occ'   => $_POST['s_stu_mother_Occ'] ?? null,
        's_stu_mother_Ophno' => $_POST['s_stu_mother_Ophno'] ?? null,
        's_stu_mother_Oadd'  => $_POST['s_stu_mother_Oadd'] ?? null,

        'r_stu_father_ph_no' => $_POST['r_stu_father_ph_no'] ?? null,
        'r_stu_father_Occ'   => $_POST['r_stu_father_Occ'] ?? null,
        'r_stu_father_Ophno' => $_POST['r_stu_father_Ophno'] ?? null,
        'r_stu_father_Oadd'  => $_POST['r_stu_father_Oadd'] ?? null,

        'r_stu_p_add' => $_POST['r_stu_p_add'] ?? null,
        'r_stu_inc'   => $_POST['r_stu_inc'] ?? null,
        'r_stu_rel'   => $_POST['r_stu_rel'] ?? null,

        'type' => $type // ENUM('paying','non-paying')
    ];

    // 6) Build INSERT dynamically so counts always match
    $cols = array_keys($data);
    $placeholders = array_fill(0, count($cols), '?');
    $sql = "INSERT INTO student_registration (" . implode(',', $cols) . ")
            VALUES (" . implode(',', $placeholders) . ")";
    $stmt = $conn->prepare($sql);

    // bind all as strings (OK for MySQL)
    $types = str_repeat('s', count($data));
    $values = array_values($data);
    $bindParams = [];
    $bindParams[] = & $types;
    foreach ($values as $k => $v) $bindParams[] = & $values[$k];
    call_user_func_array([$stmt, 'bind_param'], $bindParams);

    if ($stmt->execute()) {
        $stu_name    = $_POST['r_stu_name'];
        $father_nm   = $_POST['r_stu_father'];
        $last_nm     = $_POST['r_stu_sur'];
        $stu_class   = $_POST['r_stu_admi_cls'];
        $stu_email   = $_POST['r_stu_email'];
        $parent_email = $_POST['r_stu_p_email'];

        // Send Mail
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'lmnop012001@gmail.com';
            $mail->Password = 'axbl mhsh eubp zgyc'; // Gmail app password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('lmnop012001@gmail.com', 'Kamala College Admin');
            if ($stu_email) $mail->addAddress($stu_email);
            if ($parent_email) $mail->addAddress($parent_email);

            $mail->isHTML(true);
            $mail->Subject = "Registration Successful - $stu_name";
            $mail->Body = "
                <h3>Dear $stu_name $father_nm $last_nm,</h3>
                <p>You have been successfully registered.</p>
                <p><b>Class:</b> $stu_class</p>
                <p><b>Registration No:</b> $r_id</p>
                <br><p>Regards,<br>College Admin</p>
            ";
            $mail->send();
        } catch (Exception $e) {
            error_log("Mail error: {$mail->ErrorInfo}");
        }

        // Popup
        echo "
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let overlay = document.createElement('div');
                overlay.style.position = 'fixed';
                overlay.style.top = '0'; 
                overlay.style.left = '0'; 
                overlay.style.width = '100%';
                overlay.style.height = '100%';
                overlay.style.background = 'rgba(0,0,0,0.3)';
                overlay.style.display = 'flex';
                overlay.style.justifyContent = 'center';
                overlay.style.alignItems = 'center';
                overlay.style.zIndex = '9999';
                overlay.style.backdropFilter = 'blur(6px)';
                overlay.style.webkitBackdropFilter = 'blur(6px)';

                overlay.innerHTML = `
                    <div style='background:#fff;padding:20px 30px;border-radius:10px;
                        text-align:center;box-shadow:0 5px 20px rgba(0,0,0,0.3);'>
                        <h2>Student Registered Successfully!</h2><br>
                        <p><b>Name:</b> $stu_name $father_nm $last_nm </p><br>
                        <p><b>Class:</b> $stu_class</p><br>
                        <p><b>Registration No:</b> $r_id</p><br>
                        <p><b>We have also sent email to your email-id :</b> $stu_email, $parent_email</p><br>
                        <button id='okBtn' 
                            style='padding:10px 20px;margin-top:10px;
                            border:none;border-radius:8px;background:#007bff;
                            color:#fff;cursor:pointer;'>OK</button>
                    </div> `;

                document.body.appendChild(overlay);

                document.getElementById('okBtn').onclick = function() {
                    window.location.href = 'home.php';
                }
            });

            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        </script>";
    } else {
        echo "<script>alert('Registration failed. Try again.');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kamala College | Registration</title>
    <link rel="stylesheet" href="../assets/css/register.css">
</head>
<body>
    <form id="collegeForm" action="register.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="acad_yr" id="acad_yr" value="<?= htmlspecialchars($acad_yr) ?>">
        <input type="hidden" name="subjects_json" id="subjects_json" value="">
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
            <td><label for="r_stu_admi_cls"><span style="color: red;">*</span> Admission For Class :</label></td>
            <td colspan="3">
                <select id="r_stu_admi_cls" name="r_stu_admi_cls" required>
                    <option value="">Select Class</option>
                    <?php foreach ($classRows as $row): ?>
                        <option value="<?= htmlspecialchars($row['cls_ful_nm'] . ' - ' . $row['term_title']) ?>">
                            <?= htmlspecialchars($row['cls_ful_nm'] . ' - ' . $row['term_title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><label for="r_stu_tit">Title :</label></td>
            <td>
                <select name="r_stu_tit" id="r_stu_tit" required>
                    <option value="Miss.">Miss.</option>
                    <option value="Mrs.">Mrs.</option>
                </select>
            </td>
            <td></td>
            <td></td>
            <td style="margin: auto;"><img id="preview_r_stu_id" src="" alt="ID Preview" style="max-width: 100px; display: none; margin: auto; border:1px solid #ccc; border-radius:5px;"></td>
            <td style="margin: auto;"><img id="preview_r_stu_sig" src="" alt="Signature Preview" style="max-width: 100px; display: none; margin: auto; border:1px solid #ccc; border-radius:5px;"></td>
        </tr>
        <tr>
            <td><label for="r_stu_mother"><span style="color: red;">*</span> Mother's Name :</label></td>
            <td><input type="text" id="r_stu_mother" name="r_stu_mother" placeholder="Mother's Name" required></td>
            <td><label for="r_stu_gen" class="align"><span style="color: red;">*</span> Student Gender :</label></td>
            <td>
                <select name="r_stu_gen" id="r_stu_gen" required>
                    <option value="Female">Female</option>
                    <option value="Male">Male</option>
                    <option value="Other">Other</option>
                </select>
            </td>
            <td style="text-align: center;"><label for="r_stu_id">Upload Id Photo <br><span style="font-size: 14px; color: crimson;">Size < 2MB, Only .PNG</span></label> <input type="file" id="r_stu_id" name="r_stu_id"></td>
            <td style="text-align: center;"><label for="r_stu_sig">Upload Signature <br><span style="font-size: 14px; color: crimson;">Size < 2MB, Only .PNG</span></label> <input type="file" id="r_stu_sig" name="r_stu_sig"></td>
        </tr>
        <tr>
            <td><label for="r_stu_name"><span style="color: red;">*</span> Student / First Name :</label></td>
            <td><input type="text" id="r_stu_name" name="r_stu_name" placeholder="Student name" required></td>
            <td><label for="r_stu_father" class="align"><span style="color: red;">*</span> Father's / Middle Name :</label></td>
            <td><input type="text" id="r_stu_father" name="r_stu_father" placeholder="Father's name" required></td>
            <td><label for="r_stu_sur" class="align1"><span style="color: red;">*</span> Last Name / Surname :</label></td>
            <td><input type="text" id="r_stu_sur" name="r_stu_sur" placeholder="Last Name" required></td>
        </tr>
        <tr>
            <td><label for="r_p_add"><span style="color: red;">*</span> (पत्ता) Premanent Address :<br> <input style="width: 8%;" type="checkbox" id="same_address"><span style="font-size: 14px; color: red;"> Same Residential & Parents Address</span> </label></td>
            <td colspan="5"><input type="text" id="r_p_add" name="r_p_add" placeholder="कायमचा पत्ता / Permanent Address" required></td>
        </tr>
        <tr>
            <td><label for="r_stu_vil"><span style="color: red;">*</span> (गाव) Village :</label></td>
            <td><input type="text" id="r_stu_vil" name="r_stu_vil" placeholder="गाव / Village" required></td>
            <td><label for="r_sub_dist" class="align"><span style="color: red;">*</span> (तालुका) Sub-District :</label></td>
            <td><input type="text" id="r_sub_dist" name="r_sub_dist" placeholder="तालुका / Sub-District" required></td>
            <td><label for="r_dist" class="align"><span style="color: red;">*</span> (जिल्हा) District :</label></td>
            <td><input type="text" id="r_dist" name="r_dist" placeholder="जिल्हा / District" required></td>
        </tr>
        <tr>
            <td><label for="r_r_add">(पत्ता) Residential Address :</label></td>
            <td colspan="5"><input type="text" id="r_r_add" name="r_r_add" placeholder="स्थानिक पत्ता / Residential Address" required></td>
        </tr>
        <tr>
            <td><label for="r_stu_ph"><span style="color: red;">*</span> Personal Ph. No. 1 :</label></td>
            <td><input type="text" id="r_stu_ph" name="r_stu_ph" maxlength="11" placeholder="Personal Ph No." required></td>
            <td><label for="r_stu_G_ph" class="align"><span style="color: red;">*</span> Guardian Ph. No. 2 :</label></td>
            <td><input type="text" id="r_stu_G_ph" name="r_stu_G_ph" maxlength="11" placeholder="Guardian Ph. No." required></td>
        </tr>
    </table>
    <div class="btn-div">
        <button type="submit" class="next-btn" name="save">Next</button>
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
                    <td><label for="r_stu_B"><span style="color: red;">*</span> (गाव) / Birthplace Village :</label></td>
                    <td><input type="text" id="r_stu_B" name="r_stu_B" placeholder="गाव / Village" required></td>
                    <td><label for="r_stu_B_sub_dist"  class="align2"><span style="color: red;">*</span> (तालुका) / Birth Sub-District :</label></td>
                    <td><input type="text" id="r_stu_B_sub_dist" name="r_stu_B_sub_dist" placeholder="तालुका / Sub-District" required></td>
                    <td><label for="r_stu_B_dist" class="align"><span style="color: red;">*</span> (जिल्हा) / Birth District :</label></td>
                    <td><input type="text" id="r_stu_B_dist" name="r_stu_B_dist" placeholder="जिल्हा / District" required></td>
                </tr>
                <tr>
                    <td><label for="r_stu_B_city"><span style="color: red;">*</span> Birth City :</label></td>
                    <td><input type="text" id="r_stu_B_city" name="r_stu_B_city" placeholder="Birth City" required></td>
                    <td><label for="r_stu_B_sta" class="align"><span style="color: red;">*</span> Birth State :</label></td>
                    <td><input type="text" id="r_stu_B_sta" name="r_stu_B_sta" placeholder="Birth State" required></td>
                </tr>
                <tr> 
                    <td><label for="r_stu_B_date"><span style="color: red;">*</span> Birth Date :</label></td>
                    <td><input type="date" id="r_stu_B_date" name="r_stu_B_date" required></td>
                    <td><label for="r_stu_B_dateW" class="align"><span style="color: red;">*</span> Birth date in words :</label></td>
                    <td colspan="2"><input type="text" id="r_stu_B_dateW" name="r_stu_B_dateW" placeholder="Birth date in words" required></td>
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
                    <td><label for="r_stu_age"><span style="color: red;">*</span> Age :</label></td>
                    <td><input type="text" id="r_stu_age" name="r_stu_age" required></td>
                    <td><label for="r_stu_disb" class="align">Disabled :</label></td>
                    <td>
                        <select name="r_stu_disb" id="r_stu_disb" required>
                            <option value="No">No</option>
                            <option value="Yes">Yes</option>
                        </select>
                    </td>
                    <td><label for="r_stu_mari" class="align">Marital Status :</label></td>
                    <td>
                        <select name="r_stu_mari" id="r_stu_mari" required>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Other">Other</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="r_stu_reli"><span style="color: red;">*</span> Student Religion :</label></td>
                    <td>
                        <select id="r_stu_reli" name="r_stu_reli" required>
                            <option value="">Select Religion</option>
                            <?php foreach ($ReliRows as $rrow): ?>
                                <option value="<?= $rrow['fl_nm'] ?>"><?= htmlspecialchars($rrow['fl_nm']) ?></option>
                            <?php endforeach; ?>
                            <option value="Other">Other</option>
                            <option value="-">-</option>
                        </select>
                    </td>
                    <td><label for="r_stu_cast" class="align"><span style="color: red;">*</span> Student Caste :</label></td>
                    <td>
                        <select id="r_stu_cast" name="r_stu_cast" required>
                            <option value="">Select Caste</option>
                            <?php foreach ($CasteRows as $crow): ?>
                                <option value="<?= $crow['c_ful_caste'] ?>"><?= htmlspecialchars($crow['c_ful_caste']) ?></option>
                            <?php endforeach; ?>
                            <option value="-">-</option>
                        </select>
                    </td>
                    <td><label for="r_stu_castcat" class="align"><span style="color: red;">*</span> Student Caste Category :</label></td>
                    <td>
                        <select name="r_stu_castcat" id="r_stu_castcat" required>
                            <option value="">Select Caste Category</option>
                            <option value="DT/VJ(NT-A)">DT/VJ(NT-A)</option>
                            <option value="NA">NA</option>
                            <option value="NT">NT</option>
                            <option value="NT2(NT-C)">NT2(NT-C)</option>
                            <option value="NT-C">NT-C</option>
                            <option value="OPEN">OPEN</option>
                            <option value="OBC">OBC</option>
                            <option value="SC">SC</option>
                            <option value="SPECIAL BACKWARD CLASS">SPECIAL BACKWARD CLASS</option>
                            <option value="VIMUKTA JATI/DENOTIFIED TRIBES">VIMUKTA JATI/DENOTIFIED TRIBES</option>
                            <option value="VIMUKTA JATI & NOMADIC TRIBE B">VIMUKTA JATI & NOMADIC TRIBE B</option>
                            <option value="VIMUKTA JATI & NOMADIC TRIBE C">VIMUKTA JATI & NOMADIC TRIBE C</option>
                            <option value="VIMUKTA JATI & NOMADIC TRIBE D">VIMUKTA JATI & NOMADIC TRIBE D</option>
                            <option value="VJNT(A)">VJNT(A)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="r_stu_aadr"><span style="color: red;">*</span> Aadhar Card No. :</label></td>
                    <td><input type="text" id="r_stu_aadr" name="r_stu_aadr" maxlength="14" placeholder="XXXX XXXX XXXX" required></td>
                    <td><label for="r_stu_pan" class="align">PAN card No. :</label></td>
                    <td><input style="text-transform: uppercase;" type="text" id="r_stu_pan" name="r_stu_pan" placeholder="10 digits"></td>
                    <td><label for="r_stu_bg" class="align">Blood Group :</label></td>
                    <td>
                        <select name="r_stu_bg" id="r_stu_bg" required>
                            <option value="">Select Blood Group</option>
                            <option value="NA">NA</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="r_stu_email"><span style="color: red;">*</span> Student's E-mail :</label></td>
                    <td colspan="2"><input type="email" id="r_stu_email" name="r_stu_email" placeholder="Eg:student@gmail.com" required></td>
                    <td><label for="r_stu_p_email" class="align">Parent's E-mail :</label></td>
                    <td colspan="2"><input type="email" id="r_stu_p_email" name="r_stu_p_email" placeholder="Eg:parent@gmail.com"></td>
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
                            <option value="Marathi">Marathi</option>
                            <option value="Hindi">Hindi</option>
                            <option value="English">English</option>
                            <option value="Kannad">Kannad</option>
                            <option value="Bengali">Bengali</option>
                            <option value="Gujarati">Gujarati</option>
                            <option value="Urdu">Urdu</option>
                            <option value="Odia">Odia</option>
                            <option value="Punjabi">Punjabi</option>
                            <option value="Sindhi">Sindhi</option>
                        </select>
                    </td>
                    <td><label for="r_stu_nati" class="align">Nationality :</label></td>
                    <td>
                        <select name="r_stu_nati" id="r_stu_nati" required>
                            <option value="INDIA">INDIA</option>
                            <option value="CHINA">CHINA</option>
                            <option value="USA">USA</option>
                            <option value="UK">UK</option>
                        </select>
                    </td>
                    <td><label for="r_stu_jb">Student Job/Business :</label></td>
                    <td>
                        <select name="r_stu_jb" id="r_stu_jb" required>
                            <option value="Student">Student</option>
                            <option value="Employed">Employed</option>
                            <option value="Self Employed">Self Employed</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="r_stu_vot">Voting Card :</label></td>
                    <td>
                        <select name="r_stu_vot" id="r_stu_vot">
                            <option value="No">No</option>
                            <option value="Yes">Yes</option>
                        </select>
                    </td>
                    <td><label for="r_stu_vot_no" class="align">Voting Card No. :</label></td>
                    <td><input style="text-transform: uppercase;" type="text" id="r_stu_vot_no" name="r_stu_vot_no"></td>
                    <td><label for="r_stu_org">Willing to Donate Organs (on Death)?</label></td>
                    <td>
                        <select name="r_stu_org" id="r_stu_org">
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><label for="r_stu_sport">Contribution in District / State / National / International levels :</label></td>
                    <td>
                        <select name="r_stu_sport" id="r_stu_sport">
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </td>
                    <td colspan="2"><label for="r_stu_intr_ncc">Interested in Participating in N.C.C/N.S.S?</label></td>
                    <td>
                        <select name="r_stu_intr_ncc" id="r_stu_intr_ncc">
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
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
                            <option value="Bank of Maharashtra">Bank of Maharashtra</option>
                            <option value="Bank of Baroda">Bank of Baroda</option>
                            <option value="Bank of India">Bank of India</option>
                            <option value="State Bank of India">State Bank of India</option>
                            <option value="Punjab National Bank">Punjab National Bank</option>
                            <option value="Canara Bank">Canara Bank</option>
                            <option value="Central Bank of India">Central Bank of India</option>
                            <option value="Kotak Mahindra Bank">Kotak Mahindra Bank</option>
                            <option value="Union Bank of India">Union Bank of India</option>
                            <option value="Other">Other</option>
                        </select>
                    </td>
                    <td><label for="r_stu_ifsc" class="align"><span style="color: red;">*</span> IFSC Code :</label></td>
                    <td><input style="text-transform: uppercase;" type="text" id="r_stu_ifsc" name="r_stu_ifsc" required></td>
                    <td><label for="r_stu_bkacc" class="align"><span style="color: red;">*</span> Bank Account No. :</label></td>
                    <td><input style="text-transform: uppercase;" type="text" id="r_stu_bkacc" name="r_stu_bkacc" required></td>
                </tr>
                <tr>
                    <td><label for="r_stu_adhr_lnk">Aadhar Linked with Bank :</label></td>
                    <td>
                        <select name="r_stu_adhr_lnk" id="r_stu_adhr_lnk">
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
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
                    <th><span style="color: red;">*</span> Last Exam</th>
                    <th><span style="color: red;">*</span> University/Board</th>
                    <th><span style="color: red;">*</span> Seat No.</th>
                    <th><span style="color: red;">*</span> Total Marks Obtained</th>
                    <th><span style="color: red;">*</span> Percentage/Grade</th>
                    <th><span style="color: red;">*</span> School/College</th>
                </tr>
                <tr>
                    <td>
                        <select name="r_stu_exam" id="r_stu_exam">
                            <option value="">Select Board</option>
                            <option value="SSC">SSC</option>
                            <option value="CBSC">CBSC</option>
                            <option value="HSC-SCI">HSC-SCI</option>
                            <option value="HSC-COM">HSC-COM</option>
                            <option value="HSC-ART">HSC-ARTS</option>
                            <option value="DIPLOMA">DIPLOMA</option>
                        </select>
                    </td>
                    <td><input type="text" name="r_uni"></td>
                    <td><input type="text" name="r_seat"></td>
                    <td><input type="text" name="r_mrk_obt"></td>
                    <td><input type="text" name="r_perc" class="total-perc"></td>
                    <td><input type="text" name="r_sch"></td>
                </tr>
                <tr>
                    <td colspan="2"><h3>Exam Subjects and Marks :</h3><br></td>
                </tr>
                <tr>
                    <th colspan="2"><span style="color: red;">*</span> Subject</th>
                    <th><span style="color: red;">*</span> Marks</th>
                    <th colspan="2"><span style="color: red;">*</span> Subject</th>
                    <th><span style="color: red;">*</span> Marks</th>    
                </tr>
                <tr>
                    <td colspan="2"><input type="text" name="r_sub1" placeholder="1)"></td>
                    <td class="fstinp"><input type="number" name="r_mrk1" class="subject-marks"></td>
                    <td colspan="2"><input type="text" name="r_sub2" placeholder="2)"></td>
                    <td><input type="number" name="r_mrk2" class="subject-marks"></td>
                </tr>
                <tr>
                    <td colspan="2"><input type="text" name="r_sub3" placeholder="3)"></td>
                    <td class="fstinp"><input type="number" name="r_mrk3" class="subject-marks"></td>
                    <td colspan="2"><input type="text" name="r_sub4" placeholder="4)"></td>
                    <td><input type="number" name="r_mrk4" class="subject-marks"></td>
                </tr>
                <tr>
                    <td colspan="2"><input type="text" name="r_sub5" placeholder="5)"></td>
                    <td class="fstinp"><input type="number" name="r_mrk5" class="subject-marks"></td>
                    <td colspan="2"><input type="text" name="r_sub6" placeholder="6)"></td>
                    <td><input type="number" name="r_mrk6" class="subject-marks"></td>
                </tr>
                <tr>
                    <td colspan="2"><input type="text" name="r_sub7" placeholder="7)"></td>
                    <td class="fstinp"><input type="number" name="r_mrk7" class="subject-marks"></td>
                    <td colspan="2"><input type="text" name="r_sub8" placeholder="8)"></td>
                    <td><input type="number" name="r_mrk8" class="subject-marks"></td>
                </tr>
            </table>
            <div class="btn-div">
                <button type="button" class="next-btn">Next</button>
            </div>
        </div>



<!-- Subject Selection -->
<div id="subjects-container" class="tab-content">
    <table>
        <tr>
            <td colspan="6" class="student_summary" style="font-weight:bold; background:#f0f0f0;">
                Student Name | Class : 
            </td>
        </tr>
    </table>
    <p id="class-message">Select a class to see subjects</p>

    <!-- 🔹 Checkboxes will load here -->
    <div id="subject-list"></div>

    <!-- 🔹 Button stays outside -->
    <div class="btn-div">
        <button type="button" id="subjectsNextBtn" class="next-btn">Next</button>
    </div>
</div>

<script>
function loadSubjects() {
    const classSelect = document.getElementById("r_stu_admi_cls");
    const className = classSelect.value;
    if (!className) return;

    fetch("get_subjects.php?r_stu_admi_cls=" + encodeURIComponent(className))
        .then(res => res.text())
        .then(html => {
            document.getElementById("subject-list").innerHTML = html;
            document.getElementById("class-message").style.display = "none"; // hide message

            const maxComp = parseInt(document.querySelector("#maxComp")?.value || 0);
            const maxOpt  = parseInt(document.querySelector("#maxOpt")?.value || 0);
            const maxTot  = parseInt(document.querySelector("#maxTot")?.value || 0);

            enforceSubjectLimits(maxComp, maxOpt, maxTot);
        });
}


// attach to class dropdown
document.getElementById("r_stu_admi_cls").addEventListener("change", loadSubjects);

function enforceSubjectLimits(maxComp, maxOpt, maxTot) {
    const compBoxes = document.querySelectorAll(".comp-subject");
    const optBoxes  = document.querySelectorAll(".opt-subject");

    function validateSelection() {
        const compChecked = [...compBoxes].filter(cb => cb.checked).length;
        const optChecked  = [...optBoxes].filter(cb => cb.checked).length;
        const totalChecked = compChecked + optChecked;

        if (optChecked > maxOpt) {
            alert(`You can select only ${maxOpt} optional subjects.`);
            this.checked = false;
        }
        if (totalChecked > maxTot) {
            alert(`You can select only ${maxTot} subjects in total.`);
            this.checked = false;
        }
    }

    optBoxes.forEach(cb => cb.addEventListener("change", validateSelection));
}

// ✅ Save & Next validation
document.getElementById("subjectsNextBtn").addEventListener("click", function () {
    const maxComp = parseInt(document.querySelector("#maxComp")?.value || 0);
    const maxOpt  = parseInt(document.querySelector("#maxOpt")?.value || 0);
    const maxTot  = parseInt(document.querySelector("#maxTot")?.value || 0);

    const compBoxes = document.querySelectorAll(".comp-subject");
    const optBoxes  = document.querySelectorAll(".opt-subject");

    const compChecked = [...compBoxes].filter(cb => cb.checked).length;
    const optChecked  = [...optBoxes].filter(cb => cb.checked).length;
    const totalChecked = compChecked + optChecked;

    if (compChecked < maxComp) {
        alert(`You must select exactly ${maxComp} compulsory subjects.`);
        return;
    }
    if (optChecked > maxOpt) {
        alert(`You can select maximum ${maxOpt} optional subjects.`);
        return;
    }
    if (totalChecked !== maxTot) {
        alert(`You must select exactly ${maxTot} subjects in total.`);
        return;
    }

    // ✅ go to next tab
    const nextTab = document.querySelector(".nav-tabs .nav-link.active")
        .closest("li").nextElementSibling?.querySelector("[data-bs-toggle='tab']");
    if (nextTab) nextTab.click();
});
</script>





        <!-- Documents -->

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
                <tr>
                    <td><span style="color: red;">*</span> Leaving Certificate LC : </td>
                    <td style="text-align: center;"><input type="file" name="doc1" class="doc-required doc-input"></td>
                </tr>
                <tr>
                    <td><span style="color: red;">*</span> Marksheet : </td>
                    <td style="text-align: center;"><input type="file" name="doc2" class="doc-required doc-input"></td>
                </tr>
                <tr>
                    <td><span style="color: red;">*</span> Eligibility Form : </td>
                    <td style="text-align: center;"><input type="file" name="doc3" class="doc-required doc-input"></td>
                </tr>
                <tr>
                    <td><span style="color: red;">*</span> Aadhar Card Zerox : </td>
                    <td style="text-align: center;"><input type="file" name="doc4" class="doc-required doc-input"></td>
                </tr>
                <tr>
                    <td>EBC Form : </td>
                    <td style="text-align: center;"><input type="file" name="doc5" class="doc-input"></td>
                </tr>
                <tr>
                    <td>Caste Certificate : </td>
                    <td style="text-align: center;"><input type="file" name="doc6" class="doc-input"></td>
                </tr>
                <tr>
                    <td>Income Certificate : </td>
                    <td style="text-align: center;"><input type="file" name="doc7" class="doc-input"></td>
                </tr>
                <tr>
                    <td>Voting Card Zerox : </td>
                    <td style="text-align: center;"><input type="file" name="doc8" class="doc-input"></td>
                </tr>
            </table>
            <div class="btn-div" style="width: 400px; margin: auto; text-align: center;">
                <button type="button" class="next-btn" style="margin-top: 30px;">Upload Documents and Go Next</button>
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
                    <td><label for="r_stu_mother_ph_no"><span style="color: red;">*</span> Mother's Mobile No. :</label></td>
                    <td><input type="number" id="r_stu_mother_ph_no" name="r_stu_mother_ph_no" placeholder="Mother's Ph.No.:" required></td>
                    <td><label for="s_stu_mother_Occ" class="align1">Mother's Occupation :</label></td>
                    <td>
                        <select name="s_stu_mother_Occ" id="s_stu_mother_Occ" required>
                            <option value="House wife">House wife</option>
                            <option value="Job">Job</option>
                            <option value="Business">Business</option>
                        </select>
                    </td>
                    <td><label for="s_stu_mother_Ophno" class="align1">Mother's Office Ph No. :</label></td>
                    <td><input type="number" id="s_stu_mother_Ophno" name="s_stu_mother_Ophno" placeholder="Office Ph.No."></td>
                </tr>
                <tr>
                    <td><label for="s_stu_mother_Oadd">Mother's Office Address :</label></td>
                    <td colspan="3"><input type="text" id="s_stu_mother_Oadd" name="s_stu_mother_Oadd"></td>
                </tr>    
                <tr>
                    <td><label for="r_stu_father_ph_no"><span style="color: red;">*</span> Father's Mobile No. :</label></td>
                    <td><input type="number" id="r_stu_father_ph_no" name="r_stu_father_ph_no" placeholder="Father's Ph.No.:" required></td>
                    <td><label for="r_stu_father_Occ" class="align1">Father's Occupation :</label></td>
                    <td>
                        <select name="r_stu_father_Occ" id="r_stu_father_Occ" required>
                            <option value="Job">Job</option>
                            <option value="Business">Business</option>
                        </select>
                    </td>
                    <td><label for="r_stu_father_Ophno" class="align1">Father's Office Ph No. :</label></td>
                    <td><input type="number" id="r_stu_father_Ophno" name="r_stu_father_Ophno" placeholder="Office Ph.No."></td>
                </tr>
                <tr>
                    <td><label for="r_stu_father_Oadd">Father's Office Address :</label></td>
                    <td colspan="3"><input type="text" id="r_stu_father_Oadd" name="r_stu_father_Oadd"></td>
                </tr>
                <tr>
                    <td><label for="r_stu_p_add"><span style="color: red;">*</span> Parent's Address :</label></td>
                    <td colspan="3"><input type="text" id="r_stu_p_add" name="r_stu_p_add" required></td>
                </tr>
                <tr>
                    <td><label for="r_stu_inc"><span style="color: red;">*</span> Annual Income :</label></td>
                    <td colspan="2">
                        <select name="r_stu_inc" id="r_stu_inc" required>
                            <option value=">50,000 Rs"> > 50,000 Rs</option>
                            <option value="1,00,000 Rs-2,00,000 Rs">1,00,000 Rs - 2,00,000 Rs</option>
                            <option value="2,00,000 Rs-3,00,000 Rs">2,00,000 Rs - 3,00,000 Rs</option>
                            <option value="3,00,000 Rs-4,00,000 Rs">3,00,000 Rs - 4,00,000 Rs</option>
                            <option value="4,00,000 Rs-5,00,000 Rs">4,00,000 Rs - 5,00,000 Rs</option>
                            <option value="5,00,000 Rs-6,00,000 Rs">5,00,000 Rs - 6,00,000 Rs</option>
                            <option value="6,00,000 Rs-7,00,000 Rs">6,00,000 Rs - 7,00,000 Rs</option>
                            <option value="7,00,000 Rs-8,00,000 Rs">7,00,000 Rs - 8,00,000 Rs</option>
                            <option value="<8,00,000 Rs"> < 8,00,000 Rs</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="r_stu_rel"><span style="color: red;">*</span> Relation With Student :</label></td>
                    <td><input type="text" id="r_stu_rel" name="r_stu_rel" placeholder="Eg: Daughter" required></td>
                </tr>
            </table>
            <div class="btn-div">
                <button type="submit" class="submit-btn">Submit</button>
            </div>
        </div>
    </form>
    <!-- script 1 -->
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

        if (age < 14) {
            alert("Age must be at least 14");
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
    // Read allowed counts from hidden inputs (set from DB or PHP)
    const maxComp = parseInt(document.querySelector("#maxComp")?.value || 0);
    const maxOpt  = parseInt(document.querySelector("#maxOpt")?.value || 0);
    const maxTot  = parseInt(document.querySelector("#maxTot")?.value || 0);

    // Count selected checkboxes
    const compChecked  = document.querySelectorAll(".comp-subject:checked").length;
    const optChecked   = document.querySelectorAll(".opt-subject:checked").length;
    const totalChecked = compChecked + optChecked;

    // === VALIDATIONS ===

    // Compulsory subjects
    if (compChecked !== maxComp) {
        alert(`You must select exactly ${maxComp} compulsory subject(s).`);
        valid = false;
        return;
    }

    // Optional subjects
    if (optChecked > maxOpt) {
        alert(`You can select a maximum of ${maxOpt} optional subject(s).`);
        valid = false;
        return;
    }

    // Total subjects
    if (totalChecked !== maxTot) {
        alert(`You must select exactly ${maxTot} subject(s) in total.`);
        valid = false;
        return;
    }
}

            updateTabEmoji(currentTab, valid);

            if (valid) {
                if (currentTab < contents.length - 1) {
                    // Move to next tab
                    showTab(currentTab + 1);
                } else {
                    // Last tab: allow form submission
                    currentContent.closest("form").submit();
                }
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
        if (motherPh.length !== 10) { alert("Mother's mobile must be 10 digits"); return; }
        if (fatherPh.length !== 10) { alert("Father's mobile must be 10 digits"); return; }

        const motherOffice = document.getElementById("s_stu_mother_Ophno").value.trim();
        const fatherOffice = document.getElementById("r_stu_father_Ophno").value.trim();
        if (motherOffice && motherOffice.length !== 10) { alert("Mother's office number must be 10 digits"); return; }
        if (fatherOffice && fatherOffice.length !== 10) { alert("Father's office number must be 10 digits"); return; }

        parentTab.closest("form").submit();
    });

});
function updateSubjectsHiddenInput() {
    let selected = [];
    document.querySelectorAll('input[name="subjects[]"]:checked').forEach(cb => {
        selected.push(cb.value);
    });

    document.getElementById("subjects_json").value = JSON.stringify(selected);
}

// Call this whenever checkbox changes:
document.querySelectorAll('input[name="subjects[]"]').forEach(cb => {
    cb.addEventListener("change", updateSubjectsHiddenInput);
});

// Also call it before final form submit
document.querySelector("form").addEventListener("submit", updateSubjectsHiddenInput);

</script>
<!-- script 2 -->
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