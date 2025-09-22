<?php
include '../includes/db.php';

// Initialize selected subjects
$selectedSubjects = ['compulsory' => [], 'optional' => []];

// Step 1: Get class full name + term title + student ID from GET
$classFullTerm = $_GET['r_stu_admi_cls'] ?? '';
$studentId     = $_GET['r_id'] ?? 0;

$parts       = array_map('trim', explode(' - ', $classFullTerm));
$cls_ful_nm  = $parts[0] ?? '';
$term_title  = $parts[1] ?? '';

// Step 1.5: Get POSTed selected subjects
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents("php://input");
    $selectedSubjects = json_decode($json, true) ?? ['compulsory' => [], 'optional' => []];
}

$selectedComp = array_map('intval', $selectedSubjects['compulsory'] ?? []);
$selectedOpt  = array_map('intval', $selectedSubjects['optional'] ?? []);

// Step 1.6: Fallback to DB only if POSTed data is empty
if ($studentId && empty($selectedComp) && empty($selectedOpt)) {
    $stmtSel = $conn->prepare("SELECT subjects_json FROM student_registration WHERE r_id = ?");
    $stmtSel->bind_param("i", $studentId);
    $stmtSel->execute();
    $resSel = $stmtSel->get_result();
    
    if ($resSel && $resSel->num_rows > 0) {
        $rowSel = $resSel->fetch_assoc();
        $subjects_json = json_decode($rowSel['subjects_json'], true);
        $selectedComp = array_map('intval', $subjects_json['compulsory'] ?? []);
        $selectedOpt  = array_map('intval', $subjects_json['optional'] ?? []);
    }
}

if (empty($cls_ful_nm) || empty($term_title)) {
    echo "<p>Error: Invalid class selection provided.</p>";
    exit;
}

$compulsory = [];
$optional   = [];
$maxComp    = 0;
$maxOpt     = 0;
$maxTot     = 0;

// Step 2: Get termLabel and cls_id from feecls
$stmt1 = $conn->prepare("SELECT term_label, cls_id FROM feecls WHERE cls_ful_nm = ? AND term_title = ?");
$stmt1->bind_param("ss", $cls_ful_nm, $term_title);
$stmt1->execute();
$res1 = $stmt1->get_result();

if ($res1->num_rows === 0) {
    echo "<p>No subjects found for this class.</p>";
    exit;
}

$feecls    = $res1->fetch_assoc();
$termLabel = $feecls['term_label'];
$cls_id    = (int)$feecls['cls_id'];

// Step 3: Determine semNum based on pattern
$stmt0 = $conn->prepare("SELECT pattern FROM classes WHERE cls_ful_nm = ?");
$stmt0->bind_param("s", $cls_ful_nm);
$stmt0->execute();
$res0 = $stmt0->get_result();
$classInfo = $res0->fetch_assoc();
$pattern = $classInfo['pattern'] ?? '';

function romanToInt($roman) {
    $map = [
        'XIII' => 13, 'XII' => 12, 'XI' => 11, 'X' => 10,
        'IX' => 9, 'VIII' => 8, 'VII' => 7, 'VI' => 6,
        'V' => 5, 'IV' => 4, 'III' => 3, 'II' => 2, 'I' => 1,
    ];
    $roman = strtoupper($roman);
    foreach ($map as $key => $num) {
        if ($roman === $key) return $num;
    }
    return 0;
}

$semNum = 0;
if ($pattern === 'semester') {
    preg_match('/SEM\s+([IVX]+)/i', $termLabel, $matches);
    $semNum = romanToInt($matches[1] ?? '');
} else if ($pattern === 'yearly') {
    preg_match('/Year\s+(\d+)/i', $termLabel, $matches);
    $semNum = (int)($matches[1] ?? 0);
} else {
    echo "<p>Error: Unknown pattern type.</p>";
    exit;
}

// Step 4: Get selection limits
$stmt2 = $conn->prepare("SELECT DISTINCT sel_comp_sub, sel_op_sub FROM subjects WHERE class_name = ? AND sem = ? LIMIT 1");
$stmt2->bind_param("si", $cls_ful_nm, $semNum);
$stmt2->execute();
$res2 = $stmt2->get_result();
$summary = $res2->fetch_assoc();
$maxComp = (int)($summary['sel_comp_sub'] ?? 0);
$maxOpt  = (int)($summary['sel_op_sub'] ?? 0);
$maxTot  = $maxComp + $maxOpt;

// Step 5: Get subjects
$stmt3 = $conn->prepare("SELECT DISTINCT sub_id, sub_code, sub_fl_nm, type, sub_typ FROM subjects WHERE class_name = ? AND sem = ? AND status='active'");
$stmt3->bind_param("si", $cls_ful_nm, $semNum);
$stmt3->execute();
$res3 = $stmt3->get_result();

while ($row = $res3->fetch_assoc()) {
    if ($row['type'] === 'compulsory') {
        $compulsory[] = $row;
    } else {
        $optional[] = $row;
    }
}
?>

<!-- HTML Output -->
<style>
.subject-col { width: 65%; float: left; margin: 10px; padding: 0px 0px 0px 0px;border: 1px solid gray; border-radius: 10px;}
.subject-checkbox { transform: scale(1.4); margin: 6px; }
.subject-label { font-size: 15px; display: block; padding: 8px; }
.clearfix { clear: both; }
.subject { display: flex; justify-content: center; margin: auto; }
</style>
<div class="subject">
    <div class="subject-col">
        <h3 style="text-align: center; padding: 10px 0px; margin: 0; background-color: #f3878780; border-radius: 8px 8px 0 0;">
            Select "<?php echo $maxComp; ?>" Compulsory Subjects
        </h3>
        <hr style="margin-top: 0px;">
        <?php foreach ($compulsory as $index => $sub): 
            $sub_id = (int)$sub['sub_id'];
            $isChecked = in_array($sub_id, $selectedComp, true) ? 'checked' : '';
        ?>
            <label class="subject-label" style="display: flex; align-items: center;">
                <!-- Sr. No -->
                <span style="width: 30px; text-align: right; margin-right: 10px;">
                    <?= $index + 1 ?>.
                </span>
                
                <!-- Checkbox -->
                <input style="width: 2%;" type="checkbox"
                    name="compulsory[]"
                    value="<?= $sub_id ?>"
                    class="subject-checkbox comp-subject"
                    <?= $isChecked ?>>

                <!-- Subject Info -->
                <div style="display: inline-block; width: 95%;">
                    <b style="margin-right: 20px;"><?= htmlspecialchars($sub['sub_code']); ?></b>
                    <p style="width: 60%;display: inline-block;"><?= htmlspecialchars($sub['sub_fl_nm']); ?></p> - 
                    <b><?= htmlspecialchars($sub['sub_typ']); ?></b>
                </div>
            </label>
        <?php endforeach; ?>
    </div>

    <div class="subject-col">
        <h3 style="text-align: center; padding: 10px 0px; background-color: #b2f387b3; border-radius: 8px 8px 0 0;">
            Select up to "<?php echo $maxOpt; ?>" Optional Subjects
        </h3>
        <hr style="margin-top: 0px;">
        <?php foreach ($optional as $index => $sub): 
            $sub_id = (int)$sub['sub_id'];
            $isChecked = in_array($sub_id, $selectedOpt, true) ? 'checked' : '';
        ?>
            <label class="subject-label" style="display: flex; align-items: center;">
                <!-- Sr. No -->
                <span style="width: 30px; text-align: right; margin-right: 10px;">
                    <?= $index + 1 ?>.
                </span>

                <!-- Checkbox -->
                <input style="width: 2%;" type="checkbox"
                    name="optional[]"
                    value="<?= $sub_id ?>"
                    class="subject-checkbox opt-subject"
                    <?= $isChecked ?>>

                <!-- Subject Info -->
                <div style="display: inline-block; width: 95%;">
                    <b style="margin-right: 20px;"><?= htmlspecialchars($sub['sub_code']); ?></b>
                    <p style="width: 60%;display: inline-block;"><?= htmlspecialchars($sub['sub_fl_nm']); ?></p> - 
                    <b><?= htmlspecialchars($sub['sub_typ']); ?></b>
                </div>
            </label>
        <?php endforeach; ?>
    </div>
</div>

<div class="clearfix"></div>

<input type="hidden" id="maxComp" value="<?= $maxComp ?>">
<input type="hidden" id="maxOpt" value="<?= $maxOpt ?>">
<input type="hidden" id="maxTot" value="<?= $maxTot ?>">
