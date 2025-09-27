<?php
include '../includes/db.php';
$q = $_GET['q'] ?? '';

if(strlen($q) < 2) exit;

$stmt = $conn->prepare("SELECT r_id, prn_no, r_stu_name, r_stu_father, r_stu_sur, 
                               r_stu_admi_cls, r_stu_castcat, r_stu_ph, type
                        FROM admts 
                        WHERE r_id LIKE ? OR prn_no LIKE ? OR r_stu_name LIKE ? OR r_stu_ph LIKE ?
                        LIMIT 10");
$like = "%$q%";
$stmt->bind_param("ssss", $like, $like, $like, $like);
$stmt->execute();
$res = $stmt->get_result();

echo "<table>";
while($row = $res->fetch_assoc()){
    $fullName = htmlspecialchars($row['r_stu_name'] . " " . $row['r_stu_father'] . " " . $row['r_stu_sur']);
    $cls = htmlspecialchars($row['r_stu_admi_cls']);
    $cat = htmlspecialchars($row['r_stu_castcat']);
    $type = htmlspecialchars($row['type']);
    echo "<tr onclick=\"selectStudent('{$row['r_id']}', '{$row['prn_no']}', '{$fullName}', '{$cls}', '{$cat}', '{$type}')\">
            <td>{$row['prn_no']}</td>
            <td>{$fullName}</td>
            <td>{$cls}</td>
            <td>{$cat}</td>
            <td>{$row['r_stu_ph']}</td>
            <td>{$type}</td>
          </tr>";
}
echo "</table>";
