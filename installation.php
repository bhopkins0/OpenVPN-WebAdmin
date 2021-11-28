<?php
include 'config.php';
$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
$sql = "CREATE TABLE config (
cn VARCHAR(256) CHARACTER SET utf8 COLLATE utf8_general_ci,
status VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci
)";

if ($conn->query($sql) === TRUE) {
  echo "MySQL table created successfully";
  unlink("installation.php");
} else {
  echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
