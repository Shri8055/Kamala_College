<?php
ob_start();
session_start();
include_once('../includes/header.php');
include '../includes/db.php'; // DB connection

// ===== HELPER: Insert or update total row =====
function updateTotalRow($conn, $term_id, $type) {
    $stmt = $conn->prepare("SELECT SUM(amount) as total 
                             FROM feestru 
                             WHERE term_id=? AND type=? AND sh_nm <> '-'");
    $stmt->bind_param("is", $term_id, $type);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $total = $res['total'] ?? 0;

    $stmt = $conn->prepare("SELECT fee_id 
                             FROM feestru 
                             WHERE term_id=? AND type=? AND sh_nm='-' AND fl_nm='Total' 
                             LIMIT 1");
    $stmt->bind_param("is", $term_id, $type);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt = $conn->prepare("UPDATE feestru SET amount=? WHERE fee_id=?");
        $stmt->bind_param("di", $total, $row['fee_id']);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO feestru (term_id, type, sh_nm, fl_nm, amount) 
                                VALUES (?, ?, '-', 'Total', ?)");
        $stmt->bind_param("isd", $term_id, $type, $total);
        $stmt->execute();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $term_id = intval($_POST['term_id']);

    // ==== UPDATE EXISTING FEES ====
    if (!empty($_POST['pay_fee_id']) || !empty($_POST['non_fee_id'])) {
        // Paying
        if (!empty($_POST['pay_fee_id'])) {
            foreach ($_POST['pay_fee_id'] as $i => $fee_id) {
                $sh_nm = strtoupper(trim($_POST['pay_sh_nm'][$i]));
                $fl_nm = trim($_POST['pay_fl_nm'][$i]);
                $amount = ($_POST['pay_amount'][$i] !== '') ? floatval($_POST['pay_amount'][$i]) : 0;
                $stmt = $conn->prepare("UPDATE feestru SET sh_nm=?, fl_nm=?, amount=? WHERE fee_id=? AND term_id=?");
                $stmt->bind_param("ssdii", $sh_nm, $fl_nm, $amount, $fee_id, $term_id);
                $stmt->execute();
            }
        }
        // Non-Paying
        if (!empty($_POST['non_fee_id'])) {
            foreach ($_POST['non_fee_id'] as $i => $fee_id) {
                $sh_nm = strtoupper(trim($_POST['non_sh_nm'][$i]));
                $fl_nm = trim($_POST['non_fl_nm'][$i]);
                $amount = ($_POST['non_amount'][$i] !== '') ? floatval($_POST['non_amount'][$i]) : 0;
                $stmt = $conn->prepare("UPDATE feestru SET sh_nm=?, fl_nm=?, amount=? WHERE fee_id=? AND term_id=?");
                $stmt->bind_param("ssdii", $sh_nm, $fl_nm, $amount, $fee_id, $term_id);
                $stmt->execute();
            }
        }

        // Update totals after updating items
        updateTotalRow($conn, $term_id, 'paying');
        updateTotalRow($conn, $term_id, 'non_paying');

        header("Location: feem.php?updated=1");
        exit;
    }

    // ==== INSERT NEW FEES ====
    if (!empty($_POST['pay_sh_nm'])) {
        foreach ($_POST['pay_sh_nm'] as $index => $sh_nm) {
            $sh_nm = strtoupper(trim($sh_nm));
            if ($sh_nm !== '') {
                $fl_nm = trim($_POST['pay_fl_nm'][$index]);
                $amount = ($_POST['pay_amount'][$index] !== '') ? floatval($_POST['pay_amount'][$index]) : 0;
                $stmt = $conn->prepare("INSERT INTO feestru (term_id, type, sh_nm, fl_nm, amount) VALUES (?, 'paying', ?, ?, ?)");
                $stmt->bind_param("issd", $term_id, $sh_nm, $fl_nm, $amount);
                $stmt->execute();
            }
        }
    }
    if (!empty($_POST['non_sh_nm'])) {
        foreach ($_POST['non_sh_nm'] as $index => $sh_nm) {
            $sh_nm = strtoupper(trim($sh_nm));
            if ($sh_nm !== '') {
                $fl_nm = trim($_POST['non_fl_nm'][$index]);
                $amount = ($_POST['non_amount'][$index] !== '') ? floatval($_POST['non_amount'][$index]) : 0;
                $stmt = $conn->prepare("INSERT INTO feestru (term_id, type, sh_nm, fl_nm, amount) VALUES (?, 'non_paying', ?, ?, ?)");
                $stmt->bind_param("issd", $term_id, $sh_nm, $fl_nm, $amount);
                $stmt->execute();
            }
        }
    }

    // Insert/update totals after inserting new fees
    updateTotalRow($conn, $term_id, 'paying');
    updateTotalRow($conn, $term_id, 'non_paying');

    header("Location: feem.php?inserted=1");
    exit;
}
ob_end_flush();

// ====== LOAD CLASS DATA ======
$classRows = [];
$result = $conn->query("SELECT term_id, cls_ful_nm, term_title FROM feecls ORDER BY term_id ASC");
while ($row = $result->fetch_assoc()) {
    $classRows[] = $row;
}

// ====== LOAD FEE DATA ======
$feeData = [];
foreach ($classRows as $c) {
    $feeData[$c['term_id']] = [
        'class_name' => $c['cls_ful_nm'],
        'term_title' => $c['term_title'],
        'paying' => [],
        'non_paying' => []
    ];
}
$res = $conn->query("SELECT fee_id, term_id, type, sh_nm, fl_nm, amount FROM feestru ORDER BY fee_id ASC");
while ($row = $res->fetch_assoc()) {
    $type = ($row['type'] == 'paying') ? 'paying' : 'non_paying';
    $feeData[$row['term_id']][$type][] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Kamala College | Fee Structure</title>
<link rel="stylesheet" href="../assets/css/feem.css">
<style>
table { border-collapse: collapse; width: 48%; float: left; margin: 1%;}
table, th, td { border: 1px solid #00000040; padding: 5px;}
.add-btn { cursor: pointer; color: blue; font-weight: bold; }
.class-list { display:flex; flex-wrap:wrap; gap:12px; justify-content:center; margin-top:20px; }
.class-card {
    background:#fff; border:2px solid #007bff; border-radius:8px; padding:10px 18px;
    font-weight:bold; font-size:14px; cursor:pointer; color:#007bff;
    box-shadow:0 2px 5px rgba(0,0,0,0.1); transition:all 0.3s ease; white-space:nowrap;
}
.class-card:hover { background:#007bff; color:#fff; transform:translateY(-2px); }
.class-card.active { background:#007bff; color:#fff; }
.card-details { border:1px solid #ccc; margin:15px auto; padding:10px; width:90%; background:#f9f9f9; }
</style>
</head>
<body>

<form method="POST">
<h3 style="text-align:center; padding-bottom: 5px;">Fee Structure Entry</h3><hr style="width:80%; margin:auto; border-radius: 50%;">
<label style="margin-left: 20px;">Select Class:</label>
<select style="margin-left: 20px; width: 45%;" name="term_id" required>
    <option value="">Select Class</option>
    <?php foreach ($classRows as $row): ?>
        <option value="<?= $row['term_id'] ?>"><?= htmlspecialchars($row['cls_ful_nm'] . ' - ' .$row['term_title']) ?></option>
    <?php endforeach; ?>
</select>
<div style="display: flex; justify-content: center; align-items: center; margin-top: 10px;">
  <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
    <input type="checkbox" id="copyData" style="width: 16px; height: 16px;">
    Copy Paying data to Non-Paying
  </label>
</div>

<div style="display:flex;">
    <!-- PAYING -->
    <table id="payingTable">
        <tr><th colspan="3">Paying</th></tr>
        <tr><th>Short Name</th><th>Full Name</th><th>Amount</th></tr>
        <tr>
            <td><input style="text-transform: uppercase;" type="text" name="pay_sh_nm[]"></td>
            <td><input type="text" name="pay_fl_nm[]"></td>
            <td><input type="number" step="0.01" name="pay_amount[]"></td>
        </tr>
    </table>
    <span class="add-btn" onclick="addRow('payingTable', 'pay')">+ Add Row</span>

    <!-- NON-PAYING -->
    <table id="nonPayingTable">
        <tr><th colspan="3">Non-Paying</th></tr>
        <tr><th>Short Name</th><th>Full Name</th><th>Amount</th></tr>
        <tr>
            <td><input style="text-transform: uppercase;" type="text" name="non_sh_nm[]"></td>
            <td><input type="text" name="non_fl_nm[]"></td>
            <td><input type="number" step="0.01" name="non_amount[]"></td>
        </tr>
    </table>
    <span class="add-btn" onclick="addRow('nonPayingTable', 'non')">+ Add Row</span>
</div>
<br>
<button type="submit" style="display: flex; margin: auto;">Save Fee Structure</button>
</form>

<hr style="padding: 20px; margin-top: 20px;">
<h3 style="text-align:center;">Fee Structure List</h3>

<div class="class-list" id="classList">
  <?php foreach ($classRows as $class): ?>
    <div
      class="class-card"
      data-target="card-<?= $class['term_id'] ?>"
      onclick="openClassCard(this)"
    >
      <?= strtoupper(htmlspecialchars($class['cls_ful_nm'] . ' - ' . $class['term_title'])) ?>
    </div>
  <?php endforeach; ?>
</div>

<?php foreach ($feeData as $cid => $data): ?>
  <div id="card-<?= $cid ?>" class="card-details" style="display:none;">
    <h3 style="margin-left: 10px; color: #007bff;"><?= strtoupper(htmlspecialchars($data['class_name'] . ' - ' .$data['term_title'])) ?></h3>
    <form method="POST" action="feem.php">
      <input type="hidden" name="term_id" value="<?= $cid ?>">

      <div style="display:flex; gap: 160px;">
        <!-- Paying -->
        <div style="flex:1;">
          <h4 style="text-align:center;">Paying</h4>
          <table border="1" cellpadding="4" cellspacing="0" style="width:100%;">
            <tr><th>Short Name</th><th>Full Name</th><th>Amount</th></tr>
            <?php foreach ($data['paying'] as $row): ?>
              <?php if ($row['sh_nm'] === '-' && $row['fl_nm'] === 'Total'): ?>
                <tr>
                  <td style="text-align:center;">-</td>
                  <td style="font-weight:bold;">Total</td>
                  <td style="text-align:right; font-weight:bold;"><?= number_format($row['amount'], 2) ?></td>
                </tr>
              <?php else: ?>
                <tr>
                  <td style="width: 20%;"><input type="text" name="pay_sh_nm[]" style="text-transform: uppercase" value="<?= htmlspecialchars($row['sh_nm']) ?>"></td>
                  <td style="width: 55%;"><input type="text" name="pay_fl_nm[]" value="<?= htmlspecialchars($row['fl_nm']) ?>"></td>
                  <td style="width: 25%;"><input style="text-align: right;" type="number" step="0.01" name="pay_amount[]" value="<?= $row['amount'] ?>"></td>
                  <input type="hidden" name="pay_fee_id[]" value="<?= htmlspecialchars($row['fee_id']) ?>">
                </tr>
              <?php endif; ?>
            <?php endforeach; ?>
          </table>
        </div>

        <!-- Non-Paying -->
        <div style="flex:1;">
          <h4 style="text-align:center;">Non-Paying</h4>
          <table border="1" cellpadding="4" cellspacing="0" style="width:100%;">
            <tr><th>Short Name</th><th>Full Name</th><th>Amount</th></tr>
            <?php foreach ($data['non_paying'] as $row): ?>
              <?php if ($row['sh_nm'] === '-' && $row['fl_nm'] === 'Total'): ?>
                <tr>
                  <td style="text-align:center;">-</td>
                  <td style="font-weight:bold;">Total</td>
                  <td style="text-align:right; font-weight:bold;"><?= number_format($row['amount'], 2) ?></td>
                </tr>
              <?php else: ?>
                <tr>
                  <td style="width: 20%;"><input type="text" name="non_sh_nm[]" style="text-transform: uppercase" value="<?= htmlspecialchars($row['sh_nm']) ?>"></td>
                  <td style="width: 55%;"><input type="text" name="non_fl_nm[]" value="<?= htmlspecialchars($row['fl_nm']) ?>"></td>
                  <td style="width: 25%;"><input style="text-align: right;" type="number" step="0.01" name="non_amount[]" value="<?= $row['amount'] ?>"></td>
                  <input type="hidden" name="non_fee_id[]" value="<?= htmlspecialchars($row['fee_id']) ?>">
                </tr>
              <?php endif; ?>
            <?php endforeach; ?>
          </table>
        </div>
      </div>

      <div style="margin-top:10px; text-align:center;">
        <button type="submit">Update</button>
      </div>
    </form>
  </div>
<?php endforeach; ?>

<script>
function addRow(tableId, prefix) {
    let table = document.getElementById(tableId);
    let row = table.insertRow(-1);
    row.insertCell(0).innerHTML = `<input style="text-transform: uppercase;" type="text" name="${prefix}_sh_nm[]">`;
    row.insertCell(1).innerHTML = `<input type="text" name="${prefix}_fl_nm[]">`;
    row.insertCell(2).innerHTML = `<input type="number" step="0.01" name="${prefix}_amount[]">`;
}

document.getElementById('copyData').addEventListener('change', function() {
    if (this.checked) {
        let payRows = document.querySelectorAll('#payingTable tr');
        let nonTable = document.getElementById('nonPayingTable');
        nonTable.innerHTML = `<tr><th colspan="3">Non-Paying</th></tr><tr><th>Short Name</th><th>Full Name</th><th>Amount</th></tr>`;
        payRows.forEach((row, i) => {
            if (i > 1) {
                let sh = row.querySelector(`input[name='pay_sh_nm[]']`).value;
                let fl = row.querySelector(`input[name='pay_fl_nm[]']`).value;
                let am = row.querySelector(`input[name='pay_amount[]']`).value;
                let newRow = nonTable.insertRow(-1);
                newRow.insertCell(0).innerHTML = `<input style="text-transform: uppercase;" type="text" name="non_sh_nm[]" value="${sh}">`;
                newRow.insertCell(1).innerHTML = `<input type="text" name="non_fl_nm[]" value="${fl}">`;
                newRow.insertCell(2).innerHTML = `<input type="number" step="0.01" name="non_amount[]" value="${am}">`;
            }
        });
    }
});

function openClassCard(btnEl){
    document.querySelectorAll('.class-card').forEach(el => el.classList.remove('active'));
    btnEl.classList.add('active');
    document.querySelectorAll('.card-details').forEach(p => p.style.display = 'none');
    const targetId = btnEl.getAttribute('data-target');
    const panel = document.getElementById(targetId);
    if(panel){
      panel.style.display = 'block';
      panel.scrollIntoView({behavior:'smooth', block:'start'});
    }
}
</script>

</body>
</html>
