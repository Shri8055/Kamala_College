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
.search-box { margin: 20px 0; }
.search-results { border: 1px solid #ccc; max-height: 200px; overflow-y: auto; }
.search-results table { width: 100%; border-collapse: collapse; }
.search-results td { padding: 6px; border-bottom: 1px solid #eee; cursor: pointer; }
.search-results tr:hover { background: #f2f2f2; }
</style>
</head>
<body>

<h3>Fee Receipt</h3>

<!-- 🔍 Search Box -->
<div class="search-box">
    <label>Search Student: </label>
    <input type="text" id="stuSearch" placeholder="Search by PRN, ID, Phone, Name..." style="width: 300px; padding: 5px;">
</div>
<div id="searchResults" class="search-results"></div>

<form id="receiptForm" action="castem.php" method="POST">
    <table>
        <tr>
            <td><label for="r_no">Receipt No.:</label></td>
            <td><input id="r_no" name="r_no" type="text" value="1001" style="text-align: center;" required disabled></td>
            <td></td><td></td>
            <td><label for="r_date">Receipt Date:</label></td>
            <td><input id="r_date" name="r_date" type="date"></td>
        </tr>
        <tr>
            <td><label for="r_stu_name">Student Name:</label></td>
            <td colspan="3"><input id="r_stu_name" name="r_stu_name" type="text" readonly></td>
            <td><label for="r_acad_yr">Academic Year:</label></td>
            <td><input type="text" id="r_acad_yr" name="r_acad_yr" value="<?php echo $acadYear; ?>" readonly></td>
        </tr>
        <tr>
            <td><label for="r_stu_str">Stream:</label></td>
            <td colspan="3"><input type="text" id="r_stu_str" name="r_stu_str" readonly></td>
            <td><label for="r_stu_cat">Category:</label></td>
            <td><input type="text" id="r_stu_cat" name="r_stu_cat" readonly></td>
        </tr>

        <!-- Fee Particulars will load here -->
        <tr><td colspan="6"><h4>Fee Particulars</h4></td></tr>
        <tbody id="feeRows"></tbody>

        <tr>
            <td><label for="fee_tot">Total:</label></td>
            <td><input type="text" id="fee_tot" name="fee_tot" readonly></td>
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
            let total = 0;
            data.forEach(row => {
                total += parseFloat(row.amount);
                feeBody.innerHTML += `
                <tr>
                    <td>${row.sh_nm}</td>
                    <td><input type="text" value="${row.amount}" readonly></td>
                </tr>`;
            });
            document.getElementById("fee_tot").value = total.toFixed(2);
        });
}
</script>
</body>
</html>
