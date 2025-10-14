<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../includes/header.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
        <div class="header">
        <img src="../assets/logo.png" alt="">
        <div class="heading">
          <h6 class="h6">TARARANI VIDYAPEETH'S</h6>
          <h4>KAMALA COLLEGE, KOLHAPUR</h4>
          <h6 class="h6a"><b>(AUTONOMOUS)</b></h6>
          <h6 class="h6"><b>NAAC Accredited ‘A’ Grade (3.12 CGPA), College with Potential for Excellence.</b></h6>
        </div>
        <div class="header-icons">
            <a class="profile" href="update_profile.php">
                <i class="fa-solid fa-user-gear"></i>
            </a>
            <a class="logout" href="logout.php">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </div><hr>
<div class="sidebar collapsed" id="sidebar">
  <div class="menu-content">
    <div class="dropdown">
      <a href="#"><button class="dropbtn action-button">Home</button></a>
    </div>
    <div class="dropdown">
      <button class="dropbtn action-button">Master</button>
      <div class="dropdown-content">
        <a href="addclass.php">Add Class</a>
        <a href="addsub.php">Add Subject</a>
        <a href="castem.php">Add Caste</a>
        <a href="religionm.php">Add Religion</a>
        <a href="feem.php">Add Fee Structure</a>
        <a href="register.php">Student Registration</a>
        <a href="verify.php">Student Verification</a>
        <a href="rct.php">Fee Receipt</a>
        <a href="roll_call.php">Roll Calls</a>
        <a href="#">---------</a>
        <a href="#">A/c Master</a>
        <a href="#">Personal Address</a>
        <a href="#">Edition Master</a>
        <a href="#">Add Posts Master</a>
        <a href="#">Add Units Master</a>
        <a href="#">Add Moulds Master</a>
        <a href="#">Add Products Master</a>
        <a href="#">Add Machines Master</a>
        <a href="#">Add Accessories Master</a>
        <a href="#">Permission</a>
      </div>
    </div>

    <div class="dropdown">
      <button class="dropbtn action-button">Data Entry</button>
      <div class="dropdown-content">
        <a href="#">Daily Bills</a>
        <a href="#">Multiple Ad's of one client</a>
        <a href="#">Receipts</a>
        <a href="#">Debit Notes</a>
        <a href="#">Credit Notes</a>
        <a href="#">Adjust ON ACCOUNTED RECEIPTS</a>
        <a href="#">Adjust ON ACCOUNTED CR.NOTES</a>
      </div>
    </div>

    <div class="dropdown">
      <button class="dropbtn action-button">Reports</button>
      <div class="dropdown-content">
        <div class="has-submenu">
          <a href="#">Billing</a>
          <div class="submenu">
            <a href="#">Ratewise Billing</a>
            <a href="#">Commission on Billing</a>
            <a href="#">Pagewise Billing</a>
            <a href="#">Representative Billing</a>
            <a href="#">Representative Billing-Detailed</a>
          </div>
        </div>
        <a href="dcr.php">Daily Collection Report</a>
        <a href="roll_call.php">Roll Calls</a>
        <a href="fee_dues.php">Fee Dues</a>
        <a href="castewise_report.php">Castewise Report</a>
        <a href="#">Subjectwise Report</a>
        <a href="#">Coursewise Fee Collection</a>
        <a href="#">Monthly Reports</a>
        <a href="#">Outstanding Statements</a>
        <a href="ledger.php">Ledger</a>
        <a href="#">Abstract of A/c's</a>
        <a href="#">Receipts</a>
        <a href="#">Credit Notes</a>
        <a href="#">Debit Notes</a>
        <a href="#">Advitisements to Print</a>
        <a href="#">Summery</a>
      </div>
    </div>

    <div class="dropdown">
      <button class="dropbtn action-button">Print</button>
      <div class="dropdown-content">
        <a href="#">Bills Calculation and Printing</a>
        <a href="#">Duplicate Bill Print</a>
        <a href="#">Bill Register</a>
        <a href="#">Receipt Register</a>
      </div>
    </div>

    <div class="dropdown">
      <button class="dropbtn action-button">About</button>
    </div>
  </div>
</div>

<button class="toggle-btn" onclick="toggleSidebar(this)">▶</button>

<script>
  function toggleSidebar(btn) {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('expanded');
    btn.textContent = sidebar.classList.contains('expanded') ? '◀' : '▶';

    // Toggle visibility of action buttons
    const buttons = document.querySelectorAll('.action-button');
    buttons.forEach(button => {
      button.classList.toggle('hide-buttons', !sidebar.classList.contains('expanded'));
    }); 
  }

  // Detect click outside sidebar to close it
  document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.querySelector('.toggle-btn');

    // If sidebar is open and click is outside sidebar and toggle button
    if (
      sidebar.classList.contains('expanded') &&
      !sidebar.contains(e.target) &&
      !toggleBtn.contains(e.target)
    ) {
      sidebar.classList.remove('expanded');
      toggleBtn.textContent = '▶';

      const buttons = document.querySelectorAll('.action-button');
      buttons.forEach(button => {
        button.classList.add('hide-buttons');
      });
    }
  });
</script>
</body>
</html>