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
<link rel="stylesheet" href="../assets/css/addclass.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.search-box { margin: 20px 0; width: 70%; margin-left: 40px; display: inline-block;}
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
    border-bottom: 1px solid #eee;
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
}

.fee-totals {
    background: #f9f9f9;
    font-weight: bold;
    text-align: center;
}

.fee-grand {
    background: #d1ffd1;
    font-weight: bold;
    font-size: 15px;
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

<form id="receiptForm" action="castem.php" method="POST">
    <table>
        <tr>
            <td><label for="r_no">Receipt No.:</label></td>
            <td><input id="r_no" name="r_no" type="text" value="1001" style="text-align: center;" required disabled></td>
            <td></td>
            <td></td>
            <td><label for="r_date">Receipt Date:</label></td>
            <td><input id="r_date" name="r_date" type="date"></td>
        </tr>
        <tr>
            <td><label for="r_stu_name">Student Name:</label></td>
            <td><input id="r_stu_name" name="r_stu_name" type="text" readonly></td=>
            <td><label for="r_acad_yr">Academic Year:</label></td>
            <td><input type="text" id="r_acad_yr" name="r_acad_yr" value="<?php echo $acadYear; ?>" readonly></td>
            <td><label for="r_stu_cat">Category:</label></td>
            <td><input type="text" id="r_stu_cat" name="r_stu_cat" readonly></td>
        </tr>
        <tr>
            <td><label for="r_stu_str">Stream:</label></td>
            <td colspan="3"><input style="width: 96%;" type="text" id="r_stu_str" name="r_stu_str" readonly></td>
        </tr>

        <!-- Fee Particulars will load here -->
        <tr>
            <td><h4>Fee Particulars</h4></td>
        </tr>
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
function selectStudent(rid, prn, fullname, cls, category, stuType) {
    document.getElementById("searchResults").innerHTML = "";
    document.getElementById("r_stu_name").value = fullname;
    document.getElementById("r_stu_str").value = cls;
    document.getElementById("r_stu_cat").value = category;

    // Fetch fee structure with type
    fetch("load_fees.php?cls=" + encodeURIComponent(cls) + "&type=" + encodeURIComponent(stuType))
        .then(res => res.json())
        .then(data => {
            let feeBody = document.getElementById("feeRows");
            feeBody.innerHTML = "";

            let tuitionFees = [];
            let universityFees = [];

            // Group fees manually
            data.forEach(row => {
                if (row.sh_nm === "TF" || row.fl_nm.toLowerCase().includes("tution")) {
                    tuitionFees.push(row); // Only tuition fee
                } else {
                    universityFees.push(row); // All others go here
                }
            });

            // Totals
            let tuitionTotal = tuitionFees.reduce((s, f) => s + parseFloat(f.amount), 0);
            let universityTotal = universityFees.reduce((s, f) => s + parseFloat(f.amount), 0);
            let grandTotal = tuitionTotal + universityTotal;

            // Helper: render two-column layout
            function renderColumns(arr, label) {
                let html = `<tr><td colspan="6"><h4>${label}</h4></td></tr>`;
                arr.forEach(row => {
                    html += `
                    <tr class="fee-item">
                        <td colspan="4" class="fee-label">${row.fl_nm}</td>
                        <td colspan="2" class="fee-amount">₹${parseFloat(row.amount).toFixed(2)}</td>
                    </tr>`;
                });
                return html;
            }

            // Render particulars
            feeBody.innerHTML += renderColumns(universityFees, "University Fees");
            feeBody.innerHTML += renderColumns(tuitionFees, "Tuition Fees");

            // Totals row (single row)
            feeBody.innerHTML += `
                <tr class="fee-totals">
                    <td colspan="2">University Fees Total: ₹${universityTotal.toFixed(2)}</td>
                    <td colspan="2">Tuition Fees Total: ₹${tuitionTotal.toFixed(2)}</td>
                    <td colspan="2">Grand Total: ₹${grandTotal.toFixed(2)}</td>
                </tr>
                <tr>
                    <td><label for="payable">Payable Amount:</label></td>
                    <td><input type="number" id="payable" name="payable" min="1" max="${grandTotal}" required></td>
                </tr>
                <tr>
                    <td colspan="6" id="pendingResult"></td>
                </tr>
            `;

            document.getElementById("fee_tot").value = grandTotal.toFixed(2);

            // Handle payable input logic
            document.getElementById("payable").addEventListener("input", function(){
                let val = parseFloat(this.value);
                if (isNaN(val) || val <= 0 || val > grandTotal) {
                    document.getElementById("pendingResult").innerHTML = "❌ Invalid amount!";
                    return;
                }

                let uniRemain = universityTotal;
                let tuitionRemain = tuitionTotal;

                if (val <= uniRemain) {
                    uniRemain -= val;
                } else {
                    val -= uniRemain;
                    uniRemain = 0;
                    tuitionRemain -= val;
                }

                document.getElementById("pendingResult").innerHTML = `
                    University Fees Pending: ₹${uniRemain.toFixed(2)} <br>
                    Tuition Fees Pending: ₹${tuitionRemain.toFixed(2)}
                `;
            });
        });
}


</script>
</body>
</html>
