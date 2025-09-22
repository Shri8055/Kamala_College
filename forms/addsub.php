<?php
ob_start();
include '../includes/db.php';
include_once('../includes/header.php');

// Fetch classes for dropdown
$classes = [];
$res = $conn->query("SELECT cls_id, cls_ful_nm, total_terms FROM classes");
while ($row = $res->fetch_assoc()) {
    $classes[] = $row;
}

$class_id    = (int)($_POST['d_cls'] ?? ($_GET['d_cls'] ?? 0));
$total_terms = 0;
$current_sem = 1;
$class_name  = '';
$edit_mode = false;
$edit_sem = (int)($_GET['edit_sem'] ?? 0);
$edit_compulsory_subjects = [];
$edit_optional_subjects = [];

if ($class_id) {
    // Get class info
    $q = $conn->prepare("SELECT cls_ful_nm, total_terms FROM classes WHERE cls_id=?");
    $q->bind_param("i", $class_id);
    $q->execute();
    $q->bind_result($class_name, $total_terms);
    $q->fetch();
    $q->close();

    if ($edit_sem) {
        $edit_mode = true;
        $stmt = $conn->prepare("SELECT * FROM subjects WHERE class_id=? AND sem=?");
        $stmt->bind_param("ii", $class_id, $edit_sem);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            if ($row['type'] === 'compulsory') {
                $edit_compulsory_subjects[] = $row;
            } else {
                $edit_optional_subjects[] = $row;
            }
        }
        $stmt->close();
    } else {
        // Normal add mode: determine next semester
        $q2 = $conn->prepare("SELECT MAX(sem) FROM subjects WHERE class_id=?");
        $q2->bind_param("i", $class_id);
        $q2->execute();
        $q2->bind_result($last_sem);
        $q2->fetch();
        $q2->close();

        $current_sem = $last_sem ? $last_sem + 1 : 1;
    }
}
$pattern = '';  // 'semester' or 'yearly'
$q3 = $conn->prepare("SELECT pattern FROM classes WHERE cls_id=?");
$q3->bind_param("i", $class_id);
$q3->execute();
$q3->bind_result($pattern);
$q3->fetch();
$q3->close();

// Fetch all existing subjects to display in table
$existing_subjects = [];
$res = $conn->query("SELECT COUNT(DISTINCT sem) AS total_added FROM subjects WHERE class_id={$class_id}");
$total_added = $res->fetch_assoc()['total_added'];

$res_subjects = $conn->query("
    SELECT s.*, ss.comp_sub, ss.op_sub, ss.tot_sub
    FROM subjects s
    LEFT JOIN subject_summary ss ON s.class_id = ss.class_id AND s.sem = ss.sem
    WHERE s.class_id = {$class_id}
    ORDER BY s.sem
");

while ($row = $res_subjects->fetch_assoc()) {
    $existing_subjects[] = $row;
}


$can_add = ($total_added < $total_terms) || $edit_mode;

$comp_sub = 0;
$op_sub   = 0;
$tot_sub  = 0;

// Set defaults for dynamic dropdown
$sel_comp_sub = $comp_sub;
$sel_op_sub   = $op_sub;

if ($edit_mode) {
    $res = $conn->query("SELECT * FROM subject_summary WHERE class_id = {$class_id} AND sem = {$edit_sem}");
    if ($res->num_rows) {
        $summary = $res->fetch_assoc();
        $comp_sub     = (int)$summary['comp_sub'];
        $op_sub       = (int)$summary['op_sub'];
        $tot_sub      = (int)$summary['tot_sub'];
        $sel_comp_sub = (int)$summary['sel_comp_sub'];
        $sel_op_sub   = (int)$summary['sel_op_sub'];
    }
}


ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kamala College | Define Subjects</title>
    <link rel="stylesheet" href="../assets/css/addsub.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Add/Edit Subjects</title>
    <style>
        td input {
            width: 100%;
        }

        .icon-btn {
            cursor: pointer;
            font-size: 18px;
        }

        .icon-plus {
            color: green;
        }

        .icon-minus {
            color: red;
        }
    </style>
</head>

<body>
    <form method="POST" action="save_subjects.php<?= $edit_mode ? '?edit_sem=' . $edit_sem : '' ?>">
        <input type="hidden" name="current_sem" value="<?= $edit_sem ?: $current_sem ?>">
        <label style="font-weight: bold;">Select Class:</label>
        <select name="d_cls" id="class-select" required>
            <option value="">-- Select Class --</option>
            <?php foreach ($classes as $c): ?>
                <option value="<?= $c['cls_id'] ?>" <?= ($class_id == $c['cls_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['cls_ful_nm']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <script>
            document.getElementById('class-select').addEventListener('change', function() {
                const clsId = this.value;
                if (clsId) {
                    // Redirect to the same page with the class id in URL
                    let url = new URL(window.location.href);
                    url.searchParams.set('d_cls', clsId);
                    // Remove edit_sem when switching class
                    url.searchParams.delete('edit_sem');
                    window.location.href = url.toString();
                }
            });
        </script>
        <?php if ($class_id && $can_add): ?>
            <?php
            $comp_sub = $op_sub = $tot_sub = 0;
            if ($edit_mode) {
                $res = $conn->query("SELECT * FROM subject_summary WHERE class_id = {$class_id} AND sem = {$edit_sem}");
                if ($res->num_rows) {
                    $summary = $res->fetch_assoc();
                    $comp_sub = $summary['comp_sub'];
                    $op_sub = $summary['op_sub'];
                    $tot_sub = $summary['tot_sub'];
                }
            }
            ?>
            <h3 style="display: flex; justify-content: center; align-items: center; text-align: center; font-weight: bold; margin-top: 30px;">
                <?= $pattern === 'yearly' ? 'Year' : 'Semester' ?> <?= $edit_mode ? $edit_sem : $current_sem ?> Subjects <?= $can_add ? '' : '(Already Added)' ?>
            </h3><hr style="width: 70%; margin: auto; margin-bottom: 20px; margin-top: 10px; border-radius: 1000%;">
            <table>
                <tr>
                    <td><label>Academic Year:</label></td>
                    <?php
                        $current_year = (int)date('Y');
                        $current_month = (int)date('m');

                        // If month >= June (6), academic year is current year to next year
                        if ($current_month >= 6) {
                            $acad_year = $current_year . '-' . ($current_year + 1);
                        } else {
                            // Otherwise, academic year is previous year to current year
                            $acad_year = ($current_year - 1) . '-' . $current_year;
                        }
                    ?>
                    <td><input style="width: 50%;" type="text" name="acad_yr" value="<?= $acad_year ?>" readonly></td>
                </tr>
                <tr>    
                    <td><label>Compulsory Subjects:</label></td>
                    <td><input style="width: 50%;" type="number" id="comp_sub" name="comp_sub" value="<?= $comp_sub ?>" readonly></td>
                    <td><label>Optional Subjects:</label></td>
                    <td><input style="width: 50%;" type="number" id="op_sub" name="op_sub" value="<?= $op_sub ?>" readonly></td>
                    <td><label>Total Subjects:</label></td>
                    <td><input style="width: 50%;" type="number" id="tot_sub" name="tot_sub" value="<?= $tot_sub ?>" readonly></td>
                </tr>
                <tr>
                    <td><label>Selectable Compulsory Subjects:</label></td>
                    <td>
                        <select style="width: 50%;" name="sel_comp_sub">
                            <?php for ($i = 1; $i <= $comp_sub; $i++): ?>
                                <option value="<?= $i ?>" <?= ($i == $sel_comp_sub) ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </td>

                    <td><label>Selectable Optional Subjects:</label></td>
                    <td>
                        <select style="width: 50%;" name="sel_op_sub">
                            <?php for ($i = 0; $i <= $op_sub; $i++): ?>
                                <option value="<?= $i ?>" <?= ($i == $sel_op_sub) ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </td>
                </tr>
            </table>
            <script>
                function updateSelectableSubjectDropdowns() {
                    const compCount = document.querySelectorAll('#compulsory-subjects tr').length;
                    const optCount = document.querySelectorAll('#optional-subjects tr').length;

                    const selCompSub = document.querySelector('select[name="sel_comp_sub"]');
                    const selOptSub = document.querySelector('select[name="sel_op_sub"]');

                    // Clear current options
                    selCompSub.innerHTML = '';
                    selOptSub.innerHTML = '';

                    // Populate Compulsory Select
                    for (let i = 1; i <= compCount; i++) {
                        const option = document.createElement('option');
                        option.value = i;
                        option.textContent = i;
                        if (i === compCount) option.selected = true;  // Default to max
                        selCompSub.appendChild(option);
                    }

                    // Populate Optional Select
                    for (let i = 0; i <= optCount; i++) {
                        const option = document.createElement('option');
                        option.value = i;
                        option.textContent = i;
                        if (i === optCount) option.selected = true;  // Default to max
                        selOptSub.appendChild(option);
                    }

                    // Update displayed subject counts
                    document.getElementById('comp_sub').value = compCount;
                    document.getElementById('op_sub').value = optCount;
                    document.getElementById('tot_sub').value = compCount + optCount;
                }
            </script>

            <h3 style="display: flex; justify-content: center; align-items: center; text-align: center; font-weight: bold; margin-top: 30px;">Compulsory Subjects</h3><hr style="width: 50%; margin: auto; margin-bottom: 20px; margin-top: 10px; border-radius: 100%;">
            <table>
                <thead>
                    <tr>
                        <th style="width: 10%;" >Code</th>
                        <th style="width: 7%;" >Short Name</th>
                        <th style="width: 38%;" >Full Name</th>
                        <th style="width: 9%;" >Type</th>
                        <th style="width: 2%;" >Credit</th>
                        <th style="width: 5%;" >Int Min</th>
                        <th style="width: 5%;" >Int Max</th>
                        <th style="width: 5%;" >Ext Min</th>
                        <th style="width: 5%;" >Ext Max</th>
                        <th style="width: 4%;" >Total</th>
                        <th style="width: 7%;">Status</th>
                        <th colspan="2"></th>
                    </tr>
                </thead>
                <tbody id="compulsory-subjects">
                    <?php
                    $comp_rows = $edit_mode ? $edit_compulsory_subjects : [['sub_code' => '', 'sub_sh_nm' => '', 'sub_fl_nm' => '', 'sub_typ' => 'Theory', 'credit' => '', 'int_min_mrk' => '', 'int_max_mrk' => '', 'ext_min_mrk' => '', 'ext_max_mrk' => '', 'total' => 0]];
                    foreach ($comp_rows as $sub): ?>
                        <tr>
                            <input type="hidden" name="sub_id[]" value="<?= $sub['sub_id'] ?? '' ?>">
                            <td><input name="sub_code[]" value="<?= htmlspecialchars($sub['sub_code']) ?>" required></td>
                            <td><input name="sub_sh_nm[]" value="<?= htmlspecialchars($sub['sub_sh_nm']) ?>" required></td>
                            <td><input name="sub_fl_nm[]" value="<?= htmlspecialchars($sub['sub_fl_nm']) ?>" required></td>
                            <td>
                                <select name="sub_typ[]" required>
                                    <option <?= $sub['sub_typ'] == 'Theory' ? 'selected' : '' ?> value="Theory">Theory</option>
                                    <option <?= $sub['sub_typ'] == 'Theory+Lab' ? 'selected' : '' ?> value="Theory+Lab">Theory+Lab</option>
                                    <option <?= $sub['sub_typ'] == 'Lab' ? 'selected' : '' ?> value="Lab">Lab</option>
                                    <option <?= $sub['sub_typ'] == 'Project' ? 'selected' : '' ?> value="Project">Project</option>
                                </select>
                            </td>
                            <td><input type="number" name="credit[]" min="0" value="<?= $sub['credit'] ?>" required></td>
                            <td><input type="number" name="int_min_mrk[]" min="0" value="<?= $sub['int_min_mrk'] ?>" required></td>
                            <td><input type="number" name="int_max_mrk[]" min="0" value="<?= $sub['int_max_mrk'] ?>" required></td>
                            <td><input type="number" name="ext_min_mrk[]" min="0" value="<?= $sub['ext_min_mrk'] ?>" required></td>
                            <td><input type="number" name="ext_max_mrk[]" min="0" value="<?= $sub['ext_max_mrk'] ?>" required></td>
                            <td><input type="number" name="total[]" value="<?= $sub['total'] ?>" readonly></td>
                            <td>
                                <select name="status[]">
                                    <option value="active" <?= (!isset($sub['status']) || $sub['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= (isset($sub['status']) && $sub['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </td>
                            <td><span class="icon-btn icon-plus" style="position: relative;" onclick="addRow('compulsory-subjects', 'compulsory')"><i class="fa-solid fa-circle-plus"></i></span></td>
                            <td><span class="icon-btn icon-minus" style="position: relative;" onclick="removeRow(this)"><i class="fa-solid fa-circle-minus"></i></span><input type="hidden" name="type[]" value="compulsory"></td>
                        </tr> 
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h3 style="display: flex; justify-content: center; align-items: center; text-align: center; font-weight: bold; margin-top: 30px;">Optional Subjects</h3><hr style="width: 50%; margin: auto; margin-bottom: 20px; margin-top: 10px; border-radius: 100%;">
            <table>
                <thead>
                    <tr>
                        <th style="width: 10%;" >Code</th>
                        <th style="width: 7%;" >Short Name</th>
                        <th style="width: 38%;" >Full Name</th>
                        <th style="width: 9%;" >Type</th>
                        <th style="width: 2%;" >Credit</th>
                        <th style="width: 5%;" >Int Min</th>
                        <th style="width: 5%;" >Int Max</th>
                        <th style="width: 5%;" >Ext Min</th>
                        <th style="width: 5%;" >Ext Max</th>
                        <th style="width: 4%;" >Total</th>
                        <th style="width: 7%;">Status</th>
                        <th colspan="2"></th>
                    </tr>
                </thead>
                <tbody id="optional-subjects">
                    <?php
                    $opt_rows = $edit_mode ? $edit_optional_subjects : [['sub_code' => '', 'sub_sh_nm' => '', 'sub_fl_nm' => '', 'sub_typ' => 'Theory', 'credit' => '', 'int_min_mrk' => '', 'int_max_mrk' => '', 'ext_min_mrk' => '', 'ext_max_mrk' => '', 'total' => 0]];
                    foreach ($opt_rows as $sub): ?>
                        <tr>
                            <input type="hidden" name="sub_id[]" value="<?= $sub['sub_id'] ?? '' ?>">
                            <td><input name="sub_code[]" value="<?= htmlspecialchars($sub['sub_code']) ?>" required></td>
                            <td><input name="sub_sh_nm[]" value="<?= htmlspecialchars($sub['sub_sh_nm']) ?>" required></td>
                            <td><input name="sub_fl_nm[]" value="<?= htmlspecialchars($sub['sub_fl_nm']) ?>" required></td>
                            <td>
                                <select name="sub_typ[]" required>
                                    <option <?= $sub['sub_typ'] == 'Theory' ? 'selected' : '' ?> value="Theory">Theory</option>
                                    <option <?= $sub['sub_typ'] == 'Theory+Lab' ? 'selected' : '' ?> value="Theory+Lab">Theory+Lab</option>
                                    <option <?= $sub['sub_typ'] == 'Lab' ? 'selected' : '' ?> value="Lab">Lab</option>
                                    <option <?= $sub['sub_typ'] == 'Project' ? 'selected' : '' ?> value="Project">Project</option>
                                </select>
                            </td>
                            <td><input type="number" name="credit[]" min="0" value="<?= $sub['credit'] ?>" required></td>
                            <td><input type="number" name="int_min_mrk[]" min="0" value="<?= $sub['int_min_mrk'] ?>" required></td>
                            <td><input type="number" name="int_max_mrk[]" min="0" value="<?= $sub['int_max_mrk'] ?>" required></td>
                            <td><input type="number" name="ext_min_mrk[]" min="0" value="<?= $sub['ext_min_mrk'] ?>" required></td>
                            <td><input type="number" name="ext_max_mrk[]" min="0" value="<?= $sub['ext_max_mrk'] ?>" required></td>
                            <td><input type="number" name="total[]" value="<?= $sub['total'] ?>" readonly></td>
                            <td>
                                <select name="status[]">
                                    <option value="active" <?= (!isset($sub['status']) || $sub['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= (isset($sub['status']) && $sub['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </td>
                            <td><span class="icon-btn icon-plus" style="position: relative;" onclick="addRow('optional-subjects', 'optional')"><i class="fa-solid fa-circle-plus"></i></span></td>
                            <td><span class="icon-btn icon-minus delete-icon" style="position: relative;" onclick="removeRow(this)"><i class="fa-solid fa-circle-minus"></i></span><input type="hidden" name="type[]" value="optional"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div style="text-align: center; margin-top: 20px;">
                <button type="submit" name="save_semester"><?= $edit_mode ? "Update Semester {$edit_sem}" : "Save Semester {$current_sem}" ?></button>
            </div>
        <?php elseif ($class_id): ?>
            <p>All terms for this class are already added. To update subjects, use Filter to find subjects and then click on the Edit link in the list below.</p>
        <?php endif; ?>
    </form><hr style="margin-top: 40px;">


    <?php
include '../includes/db.php';

// Fetch all classes for dropdown
$classes = [];
$res = $conn->query("SELECT cls_id, cls_ful_nm FROM classes");
while ($row = $res->fetch_assoc()) {
    $classes[] = $row;
}

$selected_class = (int)($_GET['selected_class'] ?? 0);
$selected_sem   = (int)($_GET['selected_sem'] ?? 0);
$existing_subjects = [];

// Populate subjects when class and sem are selected
if ($selected_class && $selected_sem) {
    $stmt = $conn->prepare("SELECT * FROM subjects WHERE class_id=? AND sem=?");
    $stmt->bind_param("ii", $selected_class, $selected_sem);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($sub = $result->fetch_assoc()) {
        $existing_subjects[] = $sub;
    }
    $stmt->close();
}

// Fetch available semesters for selected class
$semesters = [];
if ($selected_class) {
    $stmt = $conn->prepare("SELECT DISTINCT sem FROM subjects WHERE class_id=? ORDER BY sem ASC");
    $stmt->bind_param("i", $selected_class);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $semesters[] = $row['sem'];
    }
    $stmt->close();
}
?>

<!-- Filter Form -->
<form method="GET" action="">
    <table>
        <tr>
            <td style="width: 50%;">
                <select name="selected_class" id="class-select" onchange="this.form.submit()">
                    <option value="">Select Class</option>
                    <?php foreach ($classes as $cls): ?>
                        <option value="<?= $cls['cls_id'] ?>" <?= ($selected_class == $cls['cls_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cls['cls_ful_nm']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <select name="selected_sem" id="sem-select">
                    <option value="">Select Semester</option>
                    <?php foreach ($semesters as $sem): ?>
                        <option value="<?= $sem ?>" <?= ($selected_sem == $sem) ? 'selected' : '' ?>>
                            <?= $sem ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <input type="text" id="search-input" placeholder="Search subjects..." onkeyup="filterSubjects()">
            </td>
        </tr>
    </table>

    <button type="submit">Apply filter</button>
</form>


<div class="form2">
    <table class="display-table" id="subjects-table">
        <thead>
            <tr>
                <th>Sem</th>
                <th>Acad Year</th>
                <th>Sub ID (Edit)</th>
                <th>Code</th>
                <th>Short Name</th>
                <th>Full Name</th>
                <th>Type</th>
                <th>Total Subjects</th>
                <th>Credit</th>
                <th>Total Marks</th>
                <th>Total Intake<br>of Class</th>
                <th>Compulsory<br>/Optional</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($existing_subjects as $sub): ?>
                <tr>
                    <td><?= $sub['sem'] ?></td>
                    <td><?= htmlspecialchars($sub['acad_yr']) ?></td>
                    <td><a href="?d_cls=<?= $selected_class ?>&edit_sem=<?= $sub['sem'] ?>">Edit (Sem <?= $sub['sem'] ?>)</a></td>
                    <td><?= htmlspecialchars($sub['sub_code']) ?></td>
                    <td><?= htmlspecialchars($sub['sub_sh_nm']) ?></td>
                    <td><?= htmlspecialchars($sub['sub_fl_nm']) ?></td>
                    <td><?= htmlspecialchars($sub['sub_typ']) ?></td>
                    <td><?= $sub['tot_sub'] ?></td>
                    <td><?= $sub['credit'] ?></td>
                    <td><?= $sub['total'] ?></td>
                    <td><?= $sub['int_cap'] ?></td>
                    <td><?= htmlspecialchars($sub['type']) ?></td>
                    <td><?= htmlspecialchars($sub['status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
// Simple search filter
function filterSubjects() {
    const input = document.getElementById('search-input').value.toLowerCase();
    const rows = document.querySelectorAll('#subjects-table tbody tr');
    rows.forEach(row => {
        row.style.display = Array.from(row.cells)
            .some(cell => cell.textContent.toLowerCase().includes(input)) ? '' : 'none';
    });
}
</script>


    <script>
        // Update the summary counts and selectable dropdowns
function updateSubjectSummaryAndDropdowns() {
    // --- Compulsory ---
    const compRows = document.querySelectorAll('#compulsory-subjects tr').length;
    const selCompSub = document.querySelector('select[name="sel_comp_sub"]');
    const prevCompValue = parseInt(selCompSub.value) || compRows;

    selCompSub.innerHTML = '';
    for (let i = 1; i <= compRows; i++) {
        const opt = document.createElement('option');
        opt.value = i;
        opt.textContent = i;
        if (i === prevCompValue) opt.selected = true;
        selCompSub.appendChild(opt);
    }

    // --- Optional ---
    const optRows = document.querySelectorAll('#optional-subjects tr').length;
    const selOptSub = document.querySelector('select[name="sel_op_sub"]');
    const prevOptValue = parseInt(selOptSub.value) || optRows;

    selOptSub.innerHTML = '';
    for (let i = 0; i <= optRows; i++) {
        const opt = document.createElement('option');
        opt.value = i;
        opt.textContent = i;
        if (i === prevOptValue) opt.selected = true;
        selOptSub.appendChild(opt);
    }

    // --- Update displayed counts ---
    document.getElementById('comp_sub').value = compRows;
    document.getElementById('op_sub').value = optRows;
    document.getElementById('tot_sub').value = compRows + optRows;
}


// Add a new row dynamically
function addRow(tbodyId, type) {
    const tbody = document.getElementById(tbodyId);

    const newRow = document.createElement('tr');
    newRow.innerHTML = `
    <tr class="subject-row new-subject" data-new="1">
        <input type="hidden" name="sub_id[]" value="">
        <td><input name="sub_code[]" required></td>
        <td><input name="sub_sh_nm[]" required></td>
        <td><input name="sub_fl_nm[]" required></td>
        <td>
            <select name="sub_typ[]" required>
                <option value="Theory">Theory</option>
                <option value="Theory+Lab">Theory+Lab</option>
                <option value="Lab">Lab</option>
                <option value="Project">Project</option>
            </select>
        </td>
        <td><input type="number" name="credit[]" min="0" required></td>
        <td><input type="number" name="int_min_mrk[]" min="0" required></td>
        <td><input type="number" name="int_max_mrk[]" min="0" required></td>
        <td><input type="number" name="ext_min_mrk[]" min="0" required></td>
        <td><input type="number" name="ext_max_mrk[]" min="0" required></td>
        <td><input type="number" name="total[]" readonly></td>
        <td></td>
        <td>
            <span class="icon-btn icon-plus" onclick="addRow('${tbodyId}', '${type}')">
                <i class="fa-solid fa-circle-plus"></i>
            </span>
        </td>
        <td>
            <span class="icon-btn icon-minus" onclick="removeRow(this)">
                <i class="fa-solid fa-circle-minus"></i>
            </span>
            <input type="hidden" name="type[]" value="${type}">
        </td>
    </tr>
    `;
    tbody.appendChild(newRow);

    updateSubjectSummaryAndDropdowns();
}

// Remove a row dynamically
function removeRow(el) {
    const row = el.closest('tr');
    row.remove();
    updateSubjectSummaryAndDropdowns();
}

// Recalculate row counts when page loads (edit mode or fresh)
window.addEventListener('DOMContentLoaded', () => {
    updateSubjectSummaryAndDropdowns();
});

    </script>

</body>

</html>