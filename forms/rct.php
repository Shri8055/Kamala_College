<?php
include_once('../includes/header.php');
include '../includes/db.php';

// Calculate academic year dynamically
$year = date("Y");
$month = date("n");
if($month >= 6) {
    $acadYear = $year . "-" . ($year+1); // e.g. June 2025 → 2025-2026
} else {
    $acadYear = ($year-1) . "-" . $year; // e.g. Jan 2025 → 2024-2025
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['saveReceipt'])) {
    $stu_id     = intval($_POST['stu_id']);
    $cls_id     = intval($_POST['cls_id']);
    $prn        = $_POST['prn_no'];
    $name       = $_POST['r_stu_name'];
    $cls        = $_POST['r_stu_str'];
    $category   = $_POST['r_stu_cat'];
    $stu_type   = $_POST['type'];

    $receipt_no = $_POST['r_no'] ?? '';
    $receipt_date = $_POST['r_date'] ?? date('Y-m-d');
    $acadYear   = $_POST['r_acad_yr'] ?? '';
    $receiptAmt = floatval($_POST['receipt_amt'] ?? 0);
    $paymentType= $_POST['payment_type'] ?? 'Cash';
    $utrNo      = $_POST['utr_no'] ?? '';
    $concession = isset($_POST['concession_data']) ? json_decode($_POST['concession_data'], true) : [];
    $concession_by = $_POST['concession_by'] ?? '';
    $concession_amt = $_POST['concession_amt'] ?? 0;

if (floatval($_POST['receipt_amt']) <= 0) {
    die("Invalid receipt amount. Must be greater than zero.");
}

    // Fee JSON (build from JS before submit, or reconstruct here)
    $feeParticulars = $_POST['fee_data'] ?? '[]'; // send via hidden input
    $feeParticulars = json_decode($feeParticulars, true);

    // Calculate totals
    $totalFee = array_sum(array_column($feeParticulars, 'amount'));
    $pending  = $totalFee - $receiptAmt;

    // ✅ Insert into receipts
    $stmt = $conn->prepare("INSERT INTO receipts 
        (receipt_no, receipt_date, academic_year, stu_acad_year, student_prn, student_name, student_class, category, fee_type,
         fee_particulars, total_fee, receipt_amount, pending_fee, payment_type, utr_no, concession_by, concession_amt) 
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $jsonFee = json_encode($feeParticulars, JSON_UNESCAPED_UNICODE);
    $stuAcadYear = $acadYear; // or derive FY/SY/TY
    $stmt->bind_param("ssssssssssddsssss", 
        $receipt_no, $receipt_date, $acadYear, $stuAcadYear, $prn, $name, $cls, $category, $stu_type,
        $jsonFee, $totalFee, $receiptAmt, $pending, $paymentType, $utrNo, $concession_by, $concession_amt
    );
    $stmt->execute();

    // ======================
// 🎓 AUTO ADD TO ROLL CALL
// ======================

// Check if student already exists in roll_call for this class
$checkRoll = $conn->prepare("SELECT roll_id FROM roll_call WHERE prn = ? AND student_class = ?");
$checkRoll->bind_param("ss", $prn, $cls);
$checkRoll->execute();
$rollExists = $checkRoll->get_result();

if ($rollExists->num_rows == 0) {

    // 🔹 Fetch student details from admts table using PRN
    $stu_mob_no = null;
    $stu_email = null;
    $stu_abc_id = null;
    $stu_cat = $category; // default

    $fetchAdmt = $conn->prepare("SELECT r_stu_ph, r_stu_email, r_stu_castcat FROM admts WHERE prn_no = ? LIMIT 1");
    $fetchAdmt->bind_param("s", $prn);
    $fetchAdmt->execute();
    $resAdmt = $fetchAdmt->get_result();

    if ($resAdmt && $resAdmt->num_rows > 0) {
        $row = $resAdmt->fetch_assoc();
        $stu_mob_no = $row['r_stu_ph'] ?? null;
        $stu_email  = $row['r_stu_email'] ?? null;
        $stu_cat    = $row['r_stu_castcat'] ?? $category;
        $stu_abc_id = $row['abc_id'] ?? null;
    }

    // 🔹 Get next roll number for that class
    $getNext = $conn->prepare("SELECT COALESCE(MAX(roll_no), 0) + 1 AS next_roll FROM roll_call WHERE student_class = ?");
    $getNext->bind_param("s", $cls);
    $getNext->execute();
    $nextRoll = $getNext->get_result()->fetch_assoc()['next_roll'];

    // 🔹 Reformat name as "Surname First Middle"
    $fullName = trim($name);
    $nameParts = preg_split('/\s+/', $fullName);
    $formattedName = $fullName; // fallback if parsing fails

    if (count($nameParts) >= 3) {
        $first = $nameParts[0];
        $middle = $nameParts[1];
        $surname = $nameParts[count($nameParts) - 1];
        $formattedName = "$surname $first $middle";
    } elseif (count($nameParts) == 2) {
        $formattedName = "{$nameParts[1]} {$nameParts[0]}";
    }

    // Check if roll_call for this class is frozen
$cls = $_POST['r_stu_str'] ?? '';
$checkFreeze = $conn->prepare("SELECT is_frozen FROM roll_call_status WHERE student_class = ? LIMIT 1");
$checkFreeze->bind_param("s", $cls);
$checkFreeze->execute();
$freezeResult = $checkFreeze->get_result()->fetch_assoc();

if (!empty($freezeResult) && intval($freezeResult['is_frozen']) === 1) {
    echo "<script>
        alert('❌ Admission for this class ($cls) is currently frozen. You cannot make receipts.');
        window.location.href = 'rct.php';
    </script>";
    exit;
} else {
    // 🔹 Insert into roll_call
    $insertRoll = $conn->prepare("
    INSERT INTO roll_call 
        (roll_no, abc_id, prn, student_name, student_class, student_mob_no, student_category, student_email, created_at)
    VALUES (0, ?, ?, ?, ?, ?, ?, ?, NOW())
");
$insertRoll->bind_param(
    "issssss",
    $stu_id,
    $prn,
    $formattedName,
    $cls,
    $stu_mob_no,
    $stu_cat,
    $stu_email
);

    $insertRoll->execute();
    }
}
    // ✅ Update student_subjects (tot_fee, pen_fee)
    // Example: update student's subject fees
    $stmt = $conn->prepare("UPDATE student_subjects 
                            SET pen_fee=? 
                            WHERE stu_id=?");
    $stmt->bind_param("ds", $pending, $prn);
    $stmt->execute();

    echo "<script>
        alert('✅ Receipt saved and fees updated successfully!');
        window.location.href = 'rct.php';
    </script>";

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Kamala College | Fee Receipt</title>
<link rel="stylesheet" href="../assets/css/rct.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<h3 style="text-align: center; margin-top: 10px;">Fee Receipt</h3>
<!-- 🔍 Search Box -->
<div class="search-box" style="display: flex; align-items: center; gap: 10px;">
  <label for="stuSearch" style="padding-top: 5px;">Search Student:</label>
  <input type="text" id="stuSearch" placeholder="Search by PRN, R-ID, Phone, Name..." style="width: 500px; padding: 5px;">
</div>
<div id="searchResults" class="search-results"></div>
<form id="receiptForm" action="rct.php" method="POST">
  <input type="hidden" id="stu_id" name="stu_id">
  <input type="hidden" id="cls_id" name="cls_id">
    <table>
        <tr>
            <td style="width: 10%;"><label for="r_no">Receipt No.:</label></td>
            <td><input id="r_no" name="r_no" type="text" value="1001" style="text-align: center; background-color: #ffd3d1;" required readonly></td>
            <td><label for="r_date">Receipt Date:</label></td>
            <td><input id="r_date" name="r_date" type="date"></td>
            <td><label for="r_acad_yr">Academic Year:</label></td>
            <td><input type="text" id="r_acad_yr" name="r_acad_yr" value="<?php echo $acadYear; ?>" readonly></td>
            <td colspan="2">
              <button style="padding:8px 16px; background:#0056b3; color:#fff; border:none; border-radius:4px; cursor:pointer; margin-left: 45%;" type="button" id="calcBtn"><b>CALCULATOR</b></button>
              <!-- Calculator popup -->
              <div id="calculatorPopup" style="display:none; position:absolute; background:#fff; border:1px solid #ccc; padding:10px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.2); z-index:1000;">
                <input type="text" id="calcDisplay" readonly style="width:100%; padding:8px; margin-bottom:8px; font-size:1.2em; text-align:right; border:1px solid #ccc; border-radius:4px;">
                <!-- <img src="../assets/calclogo.png" style="display: flex; background-size: cover; background-position: center; margin: auto;"> -->      
                <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:5px; ">
                  <button class="calcBtn" type="button">7</button>
                  <button class="calcBtn" type="button">8</button>
                  <button class="calcBtn" type="button">9</button>
                  <button class="calcBtn" type="button">/</button>
                  <button class="calcBtn" type="button">4</button>
                  <button class="calcBtn" type="button">5</button>
                  <button class="calcBtn" type="button">6</button>
                  <button class="calcBtn" type="button">*</button>
                  <button class="calcBtn" type="button">1</button>
                  <button class="calcBtn" type="button">2</button>
                  <button class="calcBtn" type="button">3</button>
                  <button class="calcBtn" type="button">-</button>
                  <button class="calcBtn" type="button">0</button>
                  <button class="calcBtn" type="button">.</button>
                  <button class="calcBtn" type="button">C</button>
                  <button class="calcBtn" type="button">+</button>
                  <button class="calcBtn" type="button" style="grid-column: span 4; background:#28a745; color:#fff;">=</button>
                </div>
              </div>
            </td>
        </tr>
        <tr>
            <td><label for="r_stu_str">Class:</label></td>
            <td colspan="3"><input style="width: 96%;" type="text" id="r_stu_str" name="r_stu_str" readonly></td>
            <td><label for="type">Fee type:</label></td>
            <td><input type="text" id="type" name="type" readonly></td>
            <td><label for="std_fee">Standard Fee:</label></td>
            <td><input type="text" id="std_fee" name="std_fee" readonly></td>
        </tr>
        <tr>
            <td><label for="r_stu_name">Student Name:</label></td>
            <td colspan="3"><input style="width: 96%;" id="r_stu_name" name="r_stu_name" type="text" readonly></td>
            <td><label for="prn_no">PRN No:</label></td>
            <td><input type="text" id="prn_no" name="prn_no" readonly></td>
            <td><label for="r_stu_cat">Category:</label></td>
            <td><input type="text" id="r_stu_cat" name="r_stu_cat" readonly></td>
        </tr>
    </table><hr style="width: 80%; margin: 10px auto; border-radius: 60%;">
    <table>
        <tr>
            <td style="width: 10%;"><h4>Receipt Amount :</h4></td>
            <td style="width: 18%;"><input style="width: 90%;" name="receipt_amt" type="text" placeholder="Rct Amount"></td>
            <script>
document.getElementById("receiptForm").addEventListener("submit", function(e) {
  const stuId = document.getElementById("stu_id").value.trim();
  const receiptInput = document.querySelector("input[placeholder='Rct Amount']");
  const receiptAmt = parseFloat(receiptInput.value || 0);

  if (!stuId) {
    e.preventDefault();
    alert("⚠️ Please select a student first!");
    return false;
  }

  if (isNaN(receiptAmt) || receiptAmt <= 0) {
    e.preventDefault();
    alert("⚠️ Receipt amount must be greater than ₹0.00");
    receiptInput.focus();
    return false;
  }
});
</script>
            <td style="width: 8%;"><label for="payment_type">Payment Type :</label></td>
            <td style="width: 13%;"><select name="payment_type" id="payment_type">
                <option value="Cash">Cash</option>
                <option value="UPI">UPI</option>
                <option value="DD">DD</option>
                <option value="NEFT / RTGS">NEFT / RTGS</option>
            </select></td>
            <td style="width: 10%;"><label for="utr_no">UTR/DD/RTGS No.:</label></td>
            <td style="width: 25%;"><input type="text" name="utr_no" id="utr_no"></td>
            <td>
              <button type="submit" name="saveReceipt" 
                      style="cursor:pointer; background:#4fc2ffbc; border:1px solid black; color: black;width:60%">
                Save
              </button>
            </td>
         </tr>
    </table>
    <table>
  <tbody id="feeRows" style="width: 100%;"></tbody>
  <tbody id="receiptRows" style="width: 100%;"></tbody> <!-- 🔥 separate tbody for receipts -->
  <tr>
      <td><input style="background-color: #a0ff7a04; text-align: right;" 
                 type="hidden" id="fee_tot" name="fee_tot" readonly></td>
  </tr>
</table>
</form>
<script>
// 📌 Set today's date by default
const today = new Date().toISOString().split("T")[0];
const rDate = document.getElementById("r_date");
rDate.value = today;
// ❌ Prevent back-date or future-date
rDate.addEventListener("change", function() {
    if (this.value !== today) {
        alert("You cannot change the date. Resetting to today's date.");
        this.value = today;
    }
});
// 🔍 Live Search
document.getElementById("stuSearch").addEventListener("keyup", function(){
    let query = this.value.trim();
    if(query.length < 2) {
        document.getElementById("searchResults").innerHTML = "";
        return;
    }

    fetch("search_student.php?q=" + encodeURIComponent(query))
        .then(res => res.text())
        .then(data => {
            document.getElementById("searchResults").innerHTML = data;
        });
});
// ======================
// Show Previous Receipts
// ======================
function renderReceipts(receipts) {
  let receiptBody = document.getElementById("receiptRows");
  receiptBody.innerHTML = "";

  if (!receipts.length) {
    receiptBody.innerHTML = `
      <tr><td colspan="6" style="text-align:center;">No Receipts Found</td></tr>
    `;
    return;
  }
  let html = `
    <tr><td colspan="6">
      <h3>Previous Receipts</h3>
      <table border="1" style="width:100%; border-collapse:collapse;">
        <tr style="background:#0056b3;color:#fff;">
          <th>Receipt No</th>
          <th>Receipt Date</th>
          <th>Receipt Amount</th>
          <th>Pending Amount</th>
          <th></th>
        </tr>
        ${receipts.map(r => `
          <tr style="text-align:center;">
            <td>${r.receipt_no || r.receipt_id}</td>
            <td>${r.receipt_date}</td>
            <td>₹${parseFloat(r.receipt_amount).toFixed(2)}</td>
            <td>₹${parseFloat(r.pending_fee).toFixed(2)}</td>
            <td><a href="print_receipt.php?id=${r.receipt_id}" target="_blank">Print</a></td>
          </tr>
        `).join("")}
      </table>
    </td></tr>
  `;
  receiptBody.innerHTML = html;
}
// =====================
// Render Fee Tables
// =====================
function renderFees(data, concessionGiven = false, concessionAmount = 0) {
  const feeBody = document.getElementById("feeRows");
  feeBody.innerHTML = "";

  const universityFees = data.filter(row => row.fee_scope === "university");
  const collegeFees = data.filter(row => row.fee_scope === "college");

  const universityTotal = universityFees.reduce((s, f) => s + parseFloat(f.amount), 0);
  const collegeTotal = collegeFees.reduce((s, f) => s + parseFloat(f.amount), 0);
  const grandTotal = universityTotal + collegeTotal;

  window.feeCache = [...universityFees, ...collegeFees];

  // --- Build Table ---
  function makeTable(title, rows, total) {
    if (!rows.length) return "";
    return `
      <table border="1" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:#0056b3;color:#fff;"><th colspan="3">${title}</th></tr>
          <tr style="background:#ddd;"><th>Particular</th><th>Amount</th><th>Pay</th></tr>
        </thead>
        <tbody>
          ${rows.map(r => `
            <tr>
              <td>${r.fl_nm}</td>
              <td style="text-align:right;">${parseFloat(r.amount).toFixed(2)}</td>
              <td><input type="number" class="fee-input" data-max="${parseFloat(r.amount).toFixed(2)}"
                         value="0.00" step="0.01" min="0"></td>
            </tr>`).join("")}
        </tbody>
        <tfoot>
          <tr><td colspan="2" style="text-align:right;font-weight:bold;">${title} Total:</td>
          <td style="text-align:right;font-weight:bold;">₹${total.toFixed(2)}</td></tr>
        </tfoot>
      </table>`;
  }

  // --- Concession Block (only if not already given) ---
  const concessionBlock = !concessionGiven ? `
    <tr>
      <td colspan="0" style="padding:8px;font-weight:bold; width: 10%;">
        <label style="width: 100%;">Concession By:</label>
      </td>
      <td style="width: 15%;">
        <select id="concession_by" name="concession_by" style="margin-left:5px; width: 100%;">
          <option value="">Select</option>
          <option value="President">President</option>
          <option value="Secretary">Secretary</option>
          <option value="Principal">Principal</option>
        </select>
      </td>
      <td style="width: 15%;"><label style="margin-left:10px;">Concession Amount (₹):</label></td>
      <td><input type="number" id="concession_amt" value="0.00" name="concession_amt"
               min="0" step="0.01" style="width:150px; float: left;"></td>
    </tr>` : "";

  // --- Full Layout ---
  const html = `
    <tr>
      <td colspan="6">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
          ${makeTable("University Fees", universityFees, universityTotal)}
          ${makeTable("College Fees", collegeFees, collegeTotal)}
        </div>
      </td>
    </tr>
    ${concessionBlock}
    <tr class="fee-grand"><td colspan="6" id="std_fee_row">Standard Fee: ₹${grandTotal.toFixed(2)}</td></tr>
    <tr class="fee-grand"><td colspan="6" id="concession_row">Concession Applied: ₹${concessionAmount.toFixed(2)}</td></tr>
    <tr class="fee-grand"><td colspan="6" id="after_concession_row">Total Payable After Concession: ₹${(grandTotal - concessionAmount).toFixed(2)}</td></tr>
    <tr class="pen-amt"><td colspan="6" id="pending_row">Pending Fee: ₹${(grandTotal - concessionAmount).toFixed(2)}</td></tr>
    <tr style="background:#6cdefbc5;font-weight:bold;">
      <td colspan="6" style="padding:8px;color:#333;">
        Recommended Payment:<br>
        ➤ University Fees: ₹${universityTotal.toFixed(2)}<br>
        ➤ College Fees (without Tuition): ₹${collegeFees.filter(f => f.sh_nm !== "TUTI F").reduce((s, f) => s + parseFloat(f.amount), 0).toFixed(2)}<br>
        <h3>➤ Total Recommended: ₹${(universityTotal + collegeTotal).toFixed(2)}</h3>
      </td>
    </tr>`;
  feeBody.innerHTML = html;

  // --- Elements ---
  const receiptInput = document.querySelector("input[placeholder='Rct Amount']");
  const concessionAmt = document.getElementById("concession_amt"); // may be null
  const stdRow = document.getElementById("std_fee_row");
  const conRow = document.getElementById("concession_row");
  const afterConRow = document.getElementById("after_concession_row");
  const pendingRow = document.getElementById("pending_row");

  // --- Core Function: update + distribute ---
  function updateTotalsAndDistribute() {
    let receipt = parseFloat(receiptInput.value || 0);
    let concession = parseFloat((concessionAmt && concessionAmt.value) || concessionAmount || 0);

    // Limit receipt ≤ effective pending
    const effectiveTotal = Math.max(grandTotal - concession, 0);
    if (receipt > effectiveTotal) {
      receipt = effectiveTotal;
      receiptInput.value = receipt.toFixed(2);
    }

    // Update totals
    stdRow.textContent = `Standard Fee: ₹${grandTotal.toFixed(2)}`;
    conRow.textContent = `Concession Applied: ₹${concession.toFixed(2)}`;
    afterConRow.textContent = `Total Payable After Concession: ₹${effectiveTotal.toFixed(2)}`;
    pendingRow.textContent = `Pending Fee: ₹${(effectiveTotal - receipt).toFixed(2)}`;

    // --- Auto distribution ---
    let remaining = receipt;
    [...universityFees, ...collegeFees].forEach(f => (f.paid = 0));

    // 1️⃣ University first
    universityFees.forEach(f => {
      const amt = parseFloat(f.amount);
      const pay = Math.min(amt, remaining);
      f.paid = pay;
      remaining -= pay;
    });

    // 2️⃣ College (non-Tuition)
    collegeFees.filter(f => f.sh_nm !== "TUTI F").forEach(f => {
      const amt = parseFloat(f.amount);
      const pay = Math.min(amt, remaining);
      f.paid = pay;
      remaining -= pay;
    });

    // 3️⃣ Tuition last — adjust concession from it
    const tuition = collegeFees.find(f => f.sh_nm === "TUTI F");
    if (tuition) {
      const amt = Math.max(parseFloat(tuition.amount) - concession, 0);
      tuition.paid = Math.min(amt, remaining);
    }

    // Reflect on UI
    const inputs = document.querySelectorAll(".fee-input");
    const allFees = [...universityFees, ...collegeFees];
    inputs.forEach((inp, i) => {
      inp.value = (allFees[i]?.paid ?? 0).toFixed(2);
    });
  }

  // --- Listeners ---
  document.querySelectorAll(".fee-input").forEach(inp => {
    inp.addEventListener("input", function() {
      let max = parseFloat(this.dataset.max);
      let val = parseFloat(this.value) || 0;
      if (val < 0) val = 0;
      if (val > max) val = max;
      this.value = val.toFixed(2);
      updateReceiptAmount();
    });
  });

  receiptInput.addEventListener("input", updateTotalsAndDistribute);
  if (concessionAmt) concessionAmt.addEventListener("input", updateTotalsAndDistribute);

  // --- Initial run
  updateTotalsAndDistribute();
}


// 📌 When row clicked
function updateReceiptAmount() {
  let total = 0;
  document.querySelectorAll(".fee-input").forEach(inp => {
    total += parseFloat(inp.value) || 0;
  });
  document.querySelector("input[name='receipt_amt']").value = total.toFixed(2);
}

function selectStudent(stuId, prn, fullname, clsName, category, stuType) {
  document.getElementById("stu_id").value = stuId;
  document.getElementById("cls_id").value = clsName;
  document.getElementById("searchResults").innerHTML = "";
  document.getElementById("r_stu_name").value = fullname;
  document.getElementById("r_stu_str").value = clsName;
  document.getElementById("r_stu_cat").value = category;
  document.getElementById("prn_no").value = prn;
  document.getElementById("type").value = stuType;

  // 1️⃣ Load Fee Heads + Concession info
  fetch("load_fees.php?cls=" + encodeURIComponent(clsName) +
        "&type=" + encodeURIComponent(stuType) +
        "&prn=" + encodeURIComponent(prn))
    .then(res => res.json())
    .then(data => {
      const { fees, concession_given, concession_amount } = data;

      renderFees(fees, concession_given, concession_amount);

      let totalFee = fees.reduce((sum, f) => sum + parseFloat(f.orig_amount || f.amount), 0);
      document.getElementById("std_fee").value = totalFee.toFixed(2);
    });

  // 2️⃣ Load Receipts
  fetch("get_receipts.php?prn=" + encodeURIComponent(prn))
    .then(res => res.json())
    .then(receipts => renderReceipts(receipts));
}

</script>

<script>
document.getElementById("receiptForm").addEventListener("submit", function(e) {
    // collect all fee rows
    let rows = [...document.querySelectorAll("#feeRows table tbody tr")];
    let fees = [];
    rows.forEach(row => {
        let cells = row.querySelectorAll("td");
        if (cells.length >= 3) {
            let feeName = cells[0].innerText.trim();
            let amount  = parseFloat(cells[1].innerText.trim()) || 0;
            let paid    = parseFloat(cells[2].querySelector("input").value) || 0;
            // find matching JSON object from original fee_data
            let original = (window.feeCache || []).find(f => f.fl_nm === feeName && parseFloat(f.amount) === amount);
            fees.push({
                fee_id: original ? original.fee_id : null,
                fee_scope: original ? original.fee_scope : "college",
                sh_nm: original ? original.sh_nm : "",
                fl_nm: feeName,
                amount: amount,
                paid: paid
            });
        }
    });
    // update hidden field before submit
    let hidden = document.querySelector("input[name='fee_data']");
    if (!hidden) {
        hidden = document.createElement("input");
        hidden.type = "hidden";
        hidden.name = "fee_data";
        this.appendChild(hidden);
    }
    hidden.value = JSON.stringify(fees);
});
</script>
<!-- calculator -->
<script>
const calcBtn = document.getElementById("calcBtn");
const calculatorPopup = document.getElementById("calculatorPopup");
const calcDisplay = document.getElementById("calcDisplay");
let calcValue = "";
calcBtn.addEventListener("click", function (e) {
  e.stopPropagation();
  // Position popup near button
  const rect = calcBtn.getBoundingClientRect();
  calculatorPopup.style.top = rect.bottom + window.scrollY + "px";
  calculatorPopup.style.left = rect.left + window.scrollX + "px";

  if (!calculatorPopup.classList.contains("show")) {
    calculatorPopup.style.display = "block"; // Show before fading in
    setTimeout(() => {
      calculatorPopup.classList.add("show");
    }, 10); // slight delay to trigger transition
  }
});
// Close calculator if clicked outside
document.addEventListener("click", function (e) {
  if (!calculatorPopup.contains(e.target) && e.target !== calcBtn) {
    if (calculatorPopup.classList.contains("show")) {
      calculatorPopup.classList.remove("show");

      setTimeout(() => {
        calculatorPopup.style.display = "none"; // Hide after fade out
      }, 300); // match CSS transition time
    }
  }
});
calculatorPopup.querySelectorAll(".calcBtn").forEach(btn => {
  btn.addEventListener("click", function () {
    const val = this.innerText;

    if (val === "C") {
      calcValue = "";
    } else if (val === "=") {
      try {
        calcValue = eval(calcValue).toString();
      } catch {
        calcValue = "Error";
      }
    } else {
      calcValue += val;
    }

    calcDisplay.value = calcValue;
  });
});
// Keyboard support
document.addEventListener("keydown", function (e) {
  if (calculatorPopup.classList.contains("show")) {
    if ("0123456789+-*/.".includes(e.key)) {
      calcValue += e.key;
    } else if (e.key === "Enter") {
      try {
        calcValue = eval(calcValue).toString();
      } catch {
        calcValue = "Error";
      }
    } else if (e.key === "Backspace") {
      calcValue = calcValue.slice(0, -1);
    } else if (e.key.toLowerCase() === "c" || e.key === "Escape") {
      calcValue = "";
    }
    calcDisplay.value = calcValue;
  }
});
</script>
</body>
</html>