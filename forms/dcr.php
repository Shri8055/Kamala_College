<?php
include_once('../includes/header.php');
include "../includes/db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Daily Collection Register</title>
<style>
  body { font-family: "Calibri", "Inter", sans-serif; font-size: 12px; color: #000; margin: 20px; }
  input, button { padding: 6px; font-size: 14px; margin-right: 6px; }
  table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 10px; table-layout: fixed; word-wrap: break-word; }
  th, td { border: 1px solid #000; text-align: center; padding: 3px 5px; vertical-align: middle; }
  thead th { background: #f3f3f3; font-weight: bold; }
  .student-info { text-align: left; background: #eaf6ff; font-weight: bold; padding: 5px; }
  .date-header { background: #f5f5f5; font-weight: bold; padding: 5px; margin-top: 10px; border: 1px solid #000; }
  .bank-line { text-align: left; padding: 5px; font-size: 10px; }
  .date-total { background: #e8f5e9; font-weight: bold; }
  .total-right { text-align: center; }
  .no-print { margin-bottom: 10px; }
  @media print { .no-print { display: none; } }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/dcr.css">
</head>
<body>

<h2 style="text-align: center; margin-top: 10px;">📅 Daily Collection Register</h2>

<form method="GET" class="no-print">
  <label for="from">From Date:</label>
  <input type="date" name="from" id="from" required value="<?= isset($_GET['from']) ? $_GET['from'] : date('Y-m-01'); ?>">
  <label for="to">To Date:</label>
  <input type="date" name="to" id="to" required value="<?= isset($_GET['to']) ? $_GET['to'] : date('Y-m-d'); ?>">
  <button type="submit" style="background-color: #ffd861ff; border: 1px solid #00000042; cursor: pointer; border-radius: 5px;">Show</button>
  <?php if (!empty($_GET['from']) && !empty($_GET['to'])): ?>
    <button type="button" style="background-color: #a4ff9aff; border: 1px solid #00000042; cursor: pointer; border-radius: 5px;" onclick="window.open('print_dcr.php?from=<?= urlencode($_GET['from']) ?>&to=<?= urlencode($_GET['to']) ?>','_blank')">🖨️ Print DCR</button>
    <button type="button" 
            style="background-color: #9ab4ff; border: 1px solid #00000042; cursor: pointer; border-radius: 5px;" 
            onclick="window.open('export_dcr.php?from=<?= urlencode($_GET['from']) ?>&to=<?= urlencode($_GET['to']) ?>','_blank')">
            📤 Export to Excel
    </button>

  <?php endif; ?>
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
</form>

<hr>

<?php
if (!empty($_GET['from']) && !empty($_GET['to'])) {
    $from = $_GET['from'];
    $to = $_GET['to'];

    $stmt = $conn->prepare("SELECT * FROM receipts WHERE receipt_date BETWEEN ? AND ? ORDER BY receipt_date ASC, receipt_id ASC");

    $stmt->bind_param("ss", $from, $to);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 0) {
        echo "<p>No receipts found in this range.</p>";
        exit;
    }

    $grouped = [];
    $allHeads = [];
    while ($r = $res->fetch_assoc()) {
        $date = $r['receipt_date'];
        $fees = json_decode($r['fee_particulars'], true);
        $map = [];
        if (is_array($fees)) {
            foreach ($fees as $f) {
                $h = strtoupper(trim($f['fl_nm']));
                $paid = floatval($f['paid'] ?? 0);
                $map[$h] = ($map[$h] ?? 0) + $paid;
                if (!in_array($h, $allHeads)) $allHeads[] = $h;
            }
        }
        $grouped[$date][] = [
    'student_name' => $r['student_name'],
    'student_prn' => $r['student_prn'],
    'student_class' => $r['student_class'],
    'receipt_no' => $r['receipt_no'],
    'fee_type' => strtoupper($r['fee_type']),
    'receipt_amount' => floatval($r['receipt_amount']),
    'pending_fee' => floatval($r['pending_fee']),
    'payment_type' => $r['payment_type'],
    'utr_no' => $r['utr_no'] ?? '',
    'concession_by' => $r['concession_by'] ?? '',
    'concession_amt' => isset($r['concession_amt']) ? floatval($r['concession_amt']) : 0,
    'heads' => $map
];


    }

    sort($allHeads);
    $totalHeads = count($allHeads);
    $headsPerRow = 10;
    $headerRows = ceil($totalHeads / $headsPerRow);
    $colWidth = 100 / 12; // equal column widths

    echo "<div style='text-align:center;margin-bottom:10px;'>
            <strong>Session :</strong> 2025-2026 |
            <strong>Receipt Date From :</strong> " . date('d/m/Y', strtotime($from)) . " To " . date('d/m/Y', strtotime($to)) . "
          </div>";

    foreach ($grouped as $date => $students) {
        echo "<div class='date-header'>Date : " . date('d/m/Y', strtotime($date)) . "</div>";

        echo "<table style='width:100%;'>";
        echo "<colgroup>";
        echo "<col style='width:{$colWidth}%'>";
        for ($i = 0; $i < 10; $i++) echo "<col style='width:{$colWidth}%'>";
        echo "<col style='width:{$colWidth}%'>";
        echo "</colgroup>";

        // Header section
        echo "<thead>";
        for ($r = 0; $r < $headerRows; $r++) {
            echo "<tr>";
            if ($r == 0) echo "<th rowspan='{$headerRows}'>Student Data</th>";
            for ($i = 0; $i < $headsPerRow; $i++) {
                $idx = $r * $headsPerRow + $i;
                $head = ($idx < $totalHeads) ? htmlspecialchars($allHeads[$idx]) : '';
                echo "<th>$head</th>";
            }
            if ($r == 0) echo "<th rowspan='{$headerRows}'>Paid & Pending</th>";
            echo "</tr>";
        }
        echo "</thead><tbody>";

        $dateTotals = array_fill_keys($allHeads, 0.0);
        $dateGrand = 0.0;

        foreach ($students as $s) {
            echo "<tr>";
            echo "<td rowspan='{$headerRows}' class='student-info'>" .
                htmlspecialchars($s['student_name']) . "<br>PRN: " . htmlspecialchars($s['student_prn']) .
                "<br>Class: " . htmlspecialchars($s['student_class']) .
                "<br>Rcpt: " . htmlspecialchars($s['receipt_no']) . " | " . htmlspecialchars($s['fee_type']) . "</td>";

            for ($i = 0; $i < $headsPerRow; $i++) {
                $h = $allHeads[$i] ?? '';
                $val = ($h && isset($s['heads'][$h])) ? $s['heads'][$h] : '';
                if ($h && $val) $dateTotals[$h] += $val;
                echo "<td>" . ($val !== '' ? number_format($val, 2) : '') . "</td>";
            }

            echo "
  <td rowspan='{$headerRows}'>
    <b>Paid: " . number_format($s['receipt_amount'], 2) . "</b>
    <br><small>Pending: " . number_format($s['pending_fee'], 2) . "</small>";

if (!empty($s['concession_by']) || !empty($s['concession_amt'])) {
    echo "<br><small>Concession By: " . htmlspecialchars($s['concession_by']) . "</small>
          <br><small>Concession Amount: " . number_format($s['concession_amt'], 2) . "</small>";
}

echo "</td>";


            for ($r = 1; $r < $headerRows; $r++) {
                echo "<tr>";
                for ($i = 0; $i < $headsPerRow; $i++) {
                    $idx = $r * $headsPerRow + $i;
                    $h = $allHeads[$idx] ?? '';
                    $val = ($h && isset($s['heads'][$h])) ? $s['heads'][$h] : '';
                    if ($h && $val) $dateTotals[$h] += $val;
                    echo "<td>" . ($val !== '' ? number_format($val, 2) : '') . "</td>";
                }
                echo "</tr>";
            }

            // Show payment summary line
            // Determine reference/tracking number display
// Determine reference/tracking number display
$utrDisplay = '-';
if (isset($s['utr_no']) && trim($s['utr_no']) !== '') {
    $utrDisplay = htmlspecialchars(trim($s['utr_no']));
}

// Build payment summary line
echo "<tr><td colspan='12' class='bank-line'>"
    . "Cash: " . ($s['payment_type'] == 'Cash' ? number_format($s['receipt_amount'], 2) : '0.00')
    . " | DD: " . ($s['payment_type'] == 'DD' ? number_format($s['receipt_amount'], 2) : '0.00')
    . " | UPI: " . ($s['payment_type'] == 'UPI' ? number_format($s['receipt_amount'], 2) : '0.00')
    . " | NEFT/RTGS: " . ($s['payment_type'] == 'NEFT / RTGS' ? number_format($s['receipt_amount'], 2) : '0.00')
    . " || Ref No: " . $utrDisplay
    . "</td></tr>";



            $dateGrand += $s['receipt_amount'];
        }

        // Date total
        for ($r = 0; $r < $headerRows; $r++) {
            echo "<tr class='date-total'>";
            if ($r == 0) echo "<td>Date Total</td>"; else echo "<td></td>";
            for ($i = 0; $i < $headsPerRow; $i++) {
                $idx = $r * $headsPerRow + $i;
                $h = $allHeads[$idx] ?? '';
                $val = ($h) ? number_format($dateTotals[$h], 2) : '';
                echo "<td>$val</td>";
            }
            if ($r == 0) echo "<td><b>" . number_format($dateGrand, 2) . "</b></td>"; else echo "<td></td>";
            echo "</tr>";
        }

        echo "</tbody></table><br>";
    }
}
?>
</body>
</html>
