<?php
include "../includes/db.php";
$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM receipts WHERE receipt_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$receipt = $stmt->get_result()->fetch_assoc();

if (!$receipt) {
    die("Receipt not found.");
}

// decode fee JSON
$fees = json_decode($receipt['fee_particulars'], true);

// Separate into university & college fees
$universityFees = array_filter($fees, fn($f) => $f['fee_scope'] === 'university' && $f['paid'] > 0);
$collegeFees    = array_filter($fees, fn($f) => $f['fee_scope'] === 'college' && $f['paid'] > 0);

// Normalize array length for table alignment
$maxRows = max(count($universityFees), count($collegeFees));
$uniArr = array_values($universityFees);
$colArr = array_values($collegeFees);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Receipt #<?= $receipt['receipt_no'] ?></title>
  <style>
    body { font-family: Arial, sans-serif; font-family: "Inter"; font-size: 14px; }
    table { width:100%; border-collapse:collapse; margin-top:8px; }
    th { background:#eee; }
    td { padding:4px; }
    #secTab td { padding: 6px; }
  </style>
</head>
<body>
  <body oncontextmenu="return false" onkeydown="return false" onmousedown="return false">
 
    <table>
        <tr style="border: 2px solid black;">
            <td style="width: 8%; padding: 15px;">
              <img src="../assets/logo.png" alt="" style="width: 60px; height: 80px;">
            </td>
            <td>
                <p>Tararani Vidhyapeeth's</p>
                <h2>Kamala College, Kolhapur</h2>
                <p>Rajarampuri 1st Lane, Kolhapur</p>
            </td>
        </tr>
    </table>
    
    <h4 style="text-align:center; margin:6px 0;">FEE RECEIPT</h4>

    <table style="border: 2px solid black;" id="secTab">
        <tr>
            <td><b>Rec No.:</b></td>
            <td><?= $receipt['receipt_no'] ?></td>
            <td colspan="4"></td>
            <td><b>Date:</b></td>
            <td><?= $receipt['receipt_date'] ?></td>
        </tr>
        <tr>
            <td><b>Class:</b></td>
            <td colspan="7"><?= $receipt['student_class'] ?></td>
        </tr>
        <tr>
            <td><b>Category:</b></td>
            <td><?= $receipt['category'] ?></td>
            <td colspan="4"></td>
            <td><b>Fee Type:</b></td>
            <td><?= $receipt['fee_type'] ?></td>
        </tr>
        <tr>
            <td><b>Name:</b></td>
            <td><?= $receipt['student_name'] ?></td>
            <td colspan="4"></td>
            <td><b>Academic Year:</b></td>
            <td><?= $receipt['stu_acad_year'] ?></td>
        </tr>
    </table>

    <?php
$hasUni = !empty($uniArr);
$hasCol = !empty($colArr);
$maxRows = max(count($uniArr), count($colArr));
?>

<?php if ($hasUni && $hasCol): ?>
<table style="width:100%; border-collapse:collapse;">
  <tr>
    <td style="width:37%; font-weight: bold;">Particular</td>
        <td style="width:13%; font-weight: bold; padding-left: 60px; border-right: 1px solid #b2b2b2ff;">Paid</td>
        <td style="width:37%; font-weight: bold;">Particular</td>
        <td style="width:13%; font-weight: bold; padding-left: 60px;">Paid</td>
  </tr>
  <?php for($i=0; $i<$maxRows; $i++): ?>
  <tr>
    <td><?= $uniArr[$i]['fl_nm'] ?? '' ?></td>
    <td style="text-align:right; border-right: 1px solid #b2b2b2;"><?= isset($uniArr[$i]) ? ''.number_format($uniArr[$i]['paid'],2) : '' ?></td>

    <td><?= $colArr[$i]['fl_nm'] ?? '' ?></td>
    <td style="text-align:right;"><?= isset($colArr[$i]) ? ''.number_format($colArr[$i]['paid'],2) : '' ?></td>
  </tr>
  <?php endfor; ?>
</table>

<?php elseif ($hasUni): ?>
<table style="width: 50%; border-collapse:collapse;">
  <tr>
    <td style="width:50%; font-weight: bold; padding-left: 0;">Particular</td>
    <td style="width:50%; font-weight: bold; padding-left: 150px;">Paid</td>
  </tr>
  <?php foreach ($uniArr as $u): ?>
  <tr>
    <td><?= $u['fl_nm'] ?></td>
    <td style="text-align:right; "><?= number_format($u['paid'],2) ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<?php elseif ($hasCol): ?>
<table style="width: 50%; border-collapse:collapse;">
  <tr>
    <td style="width:50%; font-weight: bold; padding-left: 0;">Particular</td>
    <td style="width:50%; font-weight: bold; padding-left: 150px;">Paid</td>
  </tr>
  <?php foreach ($colArr as $c): ?>
  <tr>
    <td><?= $c['fl_nm'] ?></td>
    <td style="text-align:right;"><?= number_format($c['paid'],2) ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<?php endif; ?>
    <table>
      <tr style="border-top: 2px solid black; border-bottom: 2px solid black;">
        <td style="padding: 4px;"><span style="font-weight:bold;">Total Paid:</span></td>
        <td style="text-align:right; padding: 4px;">
          ₹<?= number_format($receipt['receipt_amount'],2) ?>
        </td>
      </tr>
    </table>
    <table>
      <tr>
          <?php
          function numberToWordsIndia($number) {
              $number = number_format($number, 2, '.', ''); // ensure two decimal places
              list($integerPart, $decimalPart) = explode('.', $number);

              $integerWords = convertNumberToWords($integerPart);
              if (intval($decimalPart) > 0) {
                  $decimalWords = convertNumberToWords($decimalPart);
                  return ucfirst($integerWords) . " Rupees and " . $decimalWords . " Paise Only";
              } else {
                  return ucfirst($integerWords) . " Rupees Only";
              }
          }

          function convertNumberToWords($number) {
              $hyphen      = '-';
              $conjunction = ' and ';
              $separator   = ', ';
              $dictionary  = array(
                  0                   => 'zero',
                  1                   => 'one',
                  2                   => 'two',
                  3                   => 'three',
                  4                   => 'four',
                  5                   => 'five',
                  6                   => 'six',
                  7                   => 'seven',
                  8                   => 'eight',
                  9                   => 'nine',
                  10                  => 'ten',
                  11                  => 'eleven',
                  12                  => 'twelve',
                  13                  => 'thirteen',
                  14                  => 'fourteen',
                  15                  => 'fifteen',
                  16                  => 'sixteen',
                  17                  => 'seventeen',
                  18                  => 'eighteen',
                  19                  => 'nineteen',
                  20                  => 'twenty',
                  30                  => 'thirty',
                  40                  => 'forty',
                  50                  => 'fifty',
                  60                  => 'sixty',
                  70                  => 'seventy',
                  80                  => 'eighty',
                  90                  => 'ninety',
                  100                 => 'hundred',
                  1000                => 'thousand',
                  100000              => 'lakh',
                  10000000            => 'crore'
              );

              if ($number < 21) {
                  return $dictionary[$number];
              } elseif ($number < 100) {
                  $tens = ((int)($number / 10)) * 10;
                  $units = $number % 10;
                  return $dictionary[$tens] . ($units ? $hyphen . $dictionary[$units] : '');
              } elseif ($number < 1000) {
                  $hundreds = (int)($number / 100);
                  $remainder = $number % 100;
                  return $dictionary[$hundreds] . ' hundred' . ($remainder ? $conjunction . convertNumberToWords($remainder) : '');
              } else {
                  foreach ([10000000 => 'crore', 100000 => 'lakh', 1000 => 'thousand'] as $value => $name) {
                      if ($number >= $value) {
                          $quotient = (int)($number / $value);
                          $remainder = $number % $value;
                          return convertNumberToWords($quotient) . " " . $name . ($remainder ? $separator . convertNumberToWords($remainder) : '');
                      }
                  }
              }
              return '';
          }
          ?>
        <td colspan="2" style="padding: 4px; font-style: italic;">
          In words: <?= ucwords(convertNumberToWords($receipt['receipt_amount'])) ?> only
        </td>
      </tr>
      <tr style="width: 20%;">
        <td style="width: 2%;"><b>Payment Type: </b><?= $receipt['payment_type'] ?> <?= $receipt['utr_no'] ? "(".$receipt['utr_no'].")" : "" ?></td>
      </tr>
      <tr>
        <td style="width: 20%;"><b>Pending Fees: </b>₹<?= number_format($receipt['pending_fee'],2) ?></td>
      </tr>
      <?php if ($receipt['concession_amt'] > 0): ?>
<tr>
  <td colspan="2"><strong>Concession By:</strong> <?= htmlspecialchars($receipt['concession_by']) ?></td>
  <td colspan="2"><strong>Concession Amount:</strong> ₹<?= number_format($receipt['concession_amt'], 2) ?></td>
</tr>
<?php endif; ?>

    </table>

        <script>
// === Disable Right-Click Context Menu ===
document.addEventListener('contextmenu', event => event.preventDefault());

// === Disable common DevTools shortcuts ===
document.addEventListener('keydown', function(e) {
    // F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C, Ctrl+U
    if (
        e.keyCode === 123 || 
        (e.ctrlKey && e.shiftKey && ['I','J','C'].includes(e.key.toUpperCase())) ||
        (e.ctrlKey && e.key.toUpperCase() === 'U')
    ) {
        e.preventDefault();
        return false;
    }
});

// === Detect DevTools open (interval check) ===
(function() {
    const element = new Image();
    Object.defineProperty(element, 'id', {
        get: function() {
            alert('⚠️ Developer tools are disabled on this page!');
            window.close();
        }
    });
    console.log(element);
})();

// === Disable text selection & copying ===
document.addEventListener('selectstart', e => e.preventDefault());
document.addEventListener('copy', e => e.preventDefault());
document.addEventListener('cut', e => e.preventDefault());
document.addEventListener('paste', e => e.preventDefault());

// === Disable drag/drop ===
document.addEventListener('dragstart', e => e.preventDefault());
document.addEventListener('drop', e => e.preventDefault());

// === Make the whole page non-editable ===
document.body.contentEditable = false;
document.designMode = "off";
</script>

    <script>window.print();</script>
</body>
</html>