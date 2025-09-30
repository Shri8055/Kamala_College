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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Kamala College | Fee Receipt</title>
<link rel="stylesheet" href="../assets/css/rct.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.search-box { margin: 0px 0; width: 70%; margin-left: 40px; display: inline-block;}
.search-results { border: 1px solid #ccc; max-height: 200px; overflow-y: auto; padding: 0px 60px; }
.search-results table { width: 100%; border-collapse: collapse; }
.search-results td { padding: 6px; border-bottom: 1px solid #5b5b5b84; cursor: pointer; }
.search-results tr:hover { background: #f2f2f2; }
</style>
<style>
/* === Fee Section Styling === */
#feeRows h4 {
    margin: 5px 0 8px;
    padding: 6px 10px;
    background: #0056b3;
    color: #fff;
    border-radius: 4px;
    font-size: 16px;
} 

.fee-item {
    display: flex;
    justify-content: space-between;
    padding: 4px 8px;
    border-bottom: 1px solid #36363621;
}

.fee-item:last-child {
    border-bottom: none;
}

.fee-label {
    font-weight: 500;
}

.fee-amount {
    font-weight: bold;
    color: #333;
    position: relative;
    float: right;
}

.fee-totals {
    background: #f9f9f9;
    font-weight: bold;
}

.fee-grand {
    background: #d1ffd1;
    font-weight: bold;
    font-size: 20px;
    text-align: center;
}

#pendingResult {
    margin-top: 10px;
    padding: 8px;
    border-radius: 5px;
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeeba;
}
#feeRows table {
  border: 1px solid #ccc;
}

#feeRows th, #feeRows td {
  padding: 0px 10px;
  text-align: left;
}

#feeRows input {
  width: 130px;
  text-align: right;
  float: right;
  padding: 2px;
  border-radius: 0px;
}

</style>

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
    <table>
        <tr>
            <td style="width: 10%;"><label for="r_no">Receipt No.:</label></td>
            <td><input id="r_no" name="r_no" type="text" value="1001" style="text-align: center;" required disabled></td>
            <td><label for="r_date">Receipt Date:</label></td>
            <td><input id="r_date" name="r_date" type="date"></td>
            <td><label for="r_acad_yr">Academic Year:</label></td>
            <td><input type="text" id="r_acad_yr" name="r_acad_yr" value="<?php echo $acadYear; ?>" readonly></td>
            <td colspan="2"><button>CALCULATOR</button></td>
        </tr>
        <tr>
            <td><label for="r_stu_str">Class:</label></td>
            <td colspan="3"><input style="width: 96%;" type="text" id="r_stu_str" name="r_stu_str" readonly></td>
            <td><label for="prn_no">PRN No:</label></td>
            <td><input type="text" id="prn_no" name="prn_no" readonly></td>
            <td><label for="type">Fee type:</label></td>
            <td><input type="text" id="type" name="type" readonly></td>
        </tr>
        <tr>
            <td><label for="r_stu_name">Student Name:</label></td>
            <td colspan="3"><input style="width: 96%;" id="r_stu_name" name="r_stu_name" type="text" readonly></td>
            <td></td>
            <td></td>
            <td><label for="r_stu_cat">Category:</label></td>
            <td><input type="text" id="r_stu_cat" name="r_stu_cat" readonly></td>
        </tr>
    </table><hr style="width: 80%; margin: 10px auto; border-radius: 60%;">
    <table>
        <tr>
            <td style="width: 10%;"><h4>Receipt Amount :</h4></td>
            <td style="width: 18%;"><input style="width: 90%;" type="text" placeholder="Rct Amount"></td>
            <td style="width: 8%;"><label for="">Payment Type :</label></td>
            <td style="width: 13%;"><select name="" id="">
                <option value="Cash">Cash</option>
                <option value="UPI">UPI</option>
                <option value="DD">DD</option>
                <option value="NEFT / RTGS">NEFT / RTGS</option>
            </select></td>
            <td style="width: 10%;"><label for="">UTR/DD/RTGS No.:</label></td>
            <td style="width: 25%;"><input type="text"></td>
            <td><input style="cursor: pointer; background-color: #98ff6f9f; border: 1px solid black; width: 60%" type="button" value="Print"></td>
         </tr>
    </table>
    <table>
        <!-- Fee Particulars will load here -->
         
        <tbody id="feeRows"></tbody>

        <tr>
            <td><input style="background-color: #a0ff7a04; text-align: right;" type="hidden" id="fee_tot" name="fee_tot" readonly></td>
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

// 📌 When row clicked
// 📌 When row clicked
function selectStudent(rid, prn, fullname, cls, category, stuType) {

    document.getElementById("searchResults").innerHTML = "";
    document.getElementById("r_stu_name").value = fullname;
    document.getElementById("r_stu_str").value = cls;
    document.getElementById("r_stu_cat").value = category;
    document.getElementById("prn_no").value = prn;
    document.getElementById("type").value = stuType;

    fetch("load_fees.php?cls=" + encodeURIComponent(cls) + "&type=" + encodeURIComponent(stuType))
  .then(res => res.json())
  .then(data => {
    let feeBody = document.getElementById("feeRows");
    feeBody.innerHTML = "";

    let universityFees = data.filter(row => row.fee_scope === "university");
    let collegeFees    = data.filter(row => row.fee_scope === "college");

    let universityTotal = universityFees.reduce((s, f) => s + parseFloat(f.amount), 0);
    let collegeTotal    = collegeFees.reduce((s, f) => s + parseFloat(f.amount), 0);
    let grandTotal      = universityTotal + collegeTotal;

    let html = `
  <tr>
    <td colspan="6">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start;">
        
        <!-- University Fees -->
        <table style="width:100%; border-collapse: collapse;" border="1">
          <thead>
            <tr style="background:#0056b3;color:#fff;">
              <th colspan="3">University / Other Fees</th>
            </tr>
            <tr>
              <th>Particular</th>
              <th>Amount</th>
              <th>Pay</th>
            </tr>
          </thead>
          <tbody>
            ${universityFees.map(uni => `
              <tr>
                <td>${uni.fl_nm}</td>
                <td style="text-align:right;">${parseFloat(uni.amount).toFixed(2)}</td>
                <td><input type="number" name="pay_fee[${uni.fee_id}]" value="${parseFloat(uni.amount).toFixed(2)}"></td>
              </tr>
            `).join("")}
          </tbody>
          <tfoot>
            <tr class="fee-totals">
              <td colspan="5" >University Fees Total: ₹${universityTotal.toFixed(2)}</td>
            </tr>
          </tfoot>
        </table>

        <!-- College Fees -->
        <table style="width:100%; border-collapse: collapse;" border="1">
          <thead>
            <tr style="background:#0056b3;color:#fff;">
              <th colspan="3">College Fees</th>
            </tr>
            <tr>
              <th>Particular</th>
              <th>Amount</th>
              <th>Pay</th>
            </tr>
          </thead>
          <tbody>
            ${collegeFees.map(col => `
              <tr>
                <td>${col.fl_nm}</td>
                <td style="text-align:right;">${parseFloat(col.amount).toFixed(2)}</td>
                <td><input type="number" name="pay_fee[${col.fee_id}]" value="${parseFloat(col.amount).toFixed(2)}"></td>
              </tr>
            `).join("")}
          </tbody>
          <tfoot>
            <tr class="fee-totals">
              <td colspan="5" >College Fees Total: ₹${collegeTotal.toFixed(2)}</td>
            </tr>
          </tfoot>
        </table>

      </div>
    </td>
  </tr>

  <!-- Grand Total -->
  <tr class="fee-grand">
    <td colspan="6">Grand Total: ₹${grandTotal.toFixed(2)}</td>
  </tr>
`;

    feeBody.innerHTML = html;
    document.getElementById("fee_tot").value = grandTotal.toFixed(2);
  });
}

</script>
</body>
</html>
