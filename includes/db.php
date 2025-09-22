<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'kamala_college';
$port = 4306;

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
