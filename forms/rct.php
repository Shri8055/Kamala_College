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

    // Fee JSON (build from JS before submit, or reconstruct here)
    $feeParticulars = $_POST['fee_data'] ?? '[]'; // send via hidden input
    $feeParticulars = json_decode($feeParticulars, true);

    // Calculate totals
    $totalFee = array_sum(array_column($feeParticulars, 'amount'));
    $pending  = $totalFee - $receiptAmt;

    // ✅ Insert into receipts
    $stmt = $conn->prepare("INSERT INTO receipts 
        (receipt_no, receipt_date, academic_year, stu_acad_year, student_prn, student_name, student_class, category, fee_type,
         fee_particulars, total_fee, receipt_amount, pending_fee, payment_type, utr_no) 
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $jsonFee = json_encode($feeParticulars, JSON_UNESCAPED_UNICODE);
    $stuAcadYear = $acadYear; // or derive FY/SY/TY
    $stmt->bind_param("ssssssssssddsss", 
        $receipt_no, $receipt_date, $acadYear, $stuAcadYear, $prn, $name, $cls, $category, $stu_type,
        $jsonFee, $totalFee, $receiptAmt, $pending, $paymentType, $utrNo
    );
    $stmt->execute();

    // ✅ Update student_subjects (tot_fee, pen_fee)
    // Example: update student's subject fees
    $stmt = $conn->prepare("UPDATE student_subjects 
                            SET tot_fee=?, pen_fee=? 
                            WHERE stu_id=?");
    $stmt->bind_param("dds", $totalFee, $pending, $prn);
    $stmt->execute();

    echo "<script>alert('✅ Receipt saved and fees updated successfully!')
                  Location('rct.php');      
          ;</script>";
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
              <button style="padding:8px 16px; background:#0056b3; color:#fff; border:none; border-radius:4px; cursor:pointer; margin-left: 40%;" type="button" id="calcBtn"><b>CALCULATOR</b></button>
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
            <td><input type="text" id="std_fee" name="std_fee"></td>
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
            <td style="width: 8%;"><label for="">Payment Type :</label></td>
            <td style="width: 13%;"><select name="" id="">
                <option value="Cash">Cash</option>
                <option value="UPI">UPI</option>
                <option value="DD">DD</option>
                <option value="NEFT / RTGS">NEFT / RTGS</option>
            </select></td>
            <td style="width: 10%;"><label for="">UTR/DD/RTGS No.:</label></td>
            <td style="width: 25%;"><input type="text"></td>
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
// =====================
// Show Previous Receipts
// =====================
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
function renderFees(data, receiptAmt = null) {
  let feeBody = document.getElementById("feeRows");
  feeBody.innerHTML = "";

  let universityFees = data.filter(row => row.fee_scope === "university");
  let collegeFees    = data.filter(row => row.fee_scope === "college");

  let universityTotal = universityFees.reduce((s, f) => s + parseFloat(f.amount), 0);
  let collegeTotal    = collegeFees.reduce((s, f) => s + parseFloat(f.amount), 0);
  let grandTotal      = universityTotal + collegeTotal;

  if (receiptAmt !== null && receiptAmt > grandTotal) {
    alert("❌ Receipt Amount cannot be greater than Grand Total (" + grandTotal.toFixed(2) + ")");
    receiptAmt = grandTotal;
    let receiptInput = document.querySelector("input[placeholder='Rct Amount']");
    if (receiptInput) receiptInput.value = grandTotal.toFixed(2);
  }

  if (receiptAmt !== null) {
    let remaining = receiptAmt;
    universityFees.forEach(f => {
      let amt = parseFloat(f.amount);
      let pay = Math.min(amt, remaining);
      f.paid = pay;
      remaining -= pay;
    });
    collegeFees.filter(f => f.sh_nm !== "TF").forEach(f => {
      let amt = parseFloat(f.amount);
      let pay = Math.min(amt, remaining);
      f.paid = pay;
      remaining -= pay;
    });
    let tuitionFee = collegeFees.find(f => f.sh_nm === "TF");
    if (tuitionFee) {
      let amt = parseFloat(tuitionFee.amount);
      let pay = Math.min(amt, remaining);
      tuitionFee.paid = pay;
      remaining -= pay;
    }
  } else {
    [...universityFees, ...collegeFees].forEach(f => f.paid = 0);
  }

  window.feeCache = [...universityFees, ...collegeFees];

  function makeTable(title, rows, total) {
    if (!rows.length) return "";
    return `
      <table border="1" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:#0056b3;color:#fff;">
            <th colspan="3">${title}</th>
          </tr>
          <tr style="background:#ddd;">
            <th>Particular</th>
            <th>Amount</th>
            <th>Pay</th>
          </tr>
        </thead>
        <tbody>
          ${rows.map(r => `
            <tr>
              <td>${r.fl_nm}</td>
              <td style="text-align:right;">${parseFloat(r.amount).toFixed(2)}</td>
              <td><input type="number" 
                         class="fee-input" 
                         data-max="${parseFloat(r.amount).toFixed(2)}"
                         value="${r.paid.toFixed(2)}"></td>
            </tr>
          `).join("")}
        </tbody>
        <tfoot>
          <tr>
            <td colspan="2" style="text-align:right;">${title} Total:</td>
            <td>₹${total.toFixed(2)}</td>
          </tr>
        </tfoot>
      </table>
    `;
  }

  let recommendedUni  = universityFees.reduce((s, f) => s + parseFloat(f.amount), 0);
  let recommendedColl = collegeFees.filter(f => f.sh_nm !== "TF").reduce((s, f) => s + parseFloat(f.amount), 0);
  let recommendedTotal = recommendedUni + recommendedColl;

  // ✅ Build HTML first
  let html = `
    <tr>
      <td colspan="6">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start;">
          ${makeTable("University Fees", universityFees, universityTotal)}
          ${makeTable("College Fees", collegeFees, collegeTotal)}
        </div>
      </td>
    </tr>
    <tr class="fee-grand">
      <td colspan="6">Grand Total Pending : ₹${grandTotal.toFixed(2)}</td>
    </tr>
    <tr class="pen-amt">
      <td colspan="6">Pending Fee: ₹${(grandTotal - (receiptAmt || 0)).toFixed(2)}</td>
    </tr>
    <tr style="background:#6cdefbc5;font-weight:bold;">
      <td colspan="6" style="padding:8px; color:#333;">
        Recommended Payment:<br>
        ➤ University Fees: ₹${recommendedUni.toFixed(2)}<br>
        ➤ College Fees (without Tuition): ₹${recommendedColl.toFixed(2)}<br>
        <h3>➤ Total Recommended: ₹${recommendedTotal.toFixed(2)}</h3>
      </td>
    </tr>
  `;

  // ✅ Then insert it
  feeBody.innerHTML = html;

  // ✅ Then bind validation
  document.querySelectorAll(".fee-input").forEach(inp => {
    inp.addEventListener("input", function() {
      let max = parseFloat(this.dataset.max);
      let val = parseFloat(this.value) || 0;
      if (val < 0) this.value = "0.00";
      if (val > max) this.value = max.toFixed(2);
      updateReceiptAmount();
    });
  });

  document.getElementById("fee_tot").value = grandTotal.toFixed(2);
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
  // 1️⃣ Load Fee Heads
  fetch("load_fees.php?cls=" + encodeURIComponent(clsName) +
        "&type=" + encodeURIComponent(stuType) +
        "&prn=" + encodeURIComponent(prn))
    .then(res => res.json())
    .then(data => {
      console.log("✅ Fees response:", data); // <-- debug
      renderFees(data);
      let totalFee = data.reduce((sum, f) => sum + parseFloat(f.orig_amount || f.amount), 0);
document.getElementById("std_fee").value = totalFee.toFixed(2);

      document.querySelector("input[placeholder='Rct Amount']")
        .addEventListener("input", function(){
          let val = parseFloat(this.value || 0);
          renderFees(data, val);
        });
    });
  // 2️⃣ Load Receipts
  fetch("get_receipts.php?prn=" + encodeURIComponent(prn))
    .then(res => res.json())
    .then(receipts => renderReceipts(receipts));
}
</script>
<script>
  document.getElementById("receiptForm").addEventListener("submit", function(e){
  if(!document.getElementById("stu_id").value){
    e.preventDefault();
    alert("⚠️ Please select a student first!");
    return false;
  }
  if(!document.querySelector("input[placeholder='Rct Amount']").value){
    e.preventDefault();
    alert("⚠️ Please enter Receipt Amount!");
    return false;
  }
});
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