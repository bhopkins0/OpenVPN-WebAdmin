<?php
session_start();
include 'config.php';
if ($restrict2vpn == "1") {
if ($_SERVER["REMOTE_ADDR"] !== $vpnserver) {
echo <<<EOF

<h1>You are not connected to the VPN.</h1>
EOF;
die();
}
}
?>
<?php

if ($_POST["pw"] && !isset($_SESSION["auth"])) {

if ($_POST["pw"] !== $pw) {
$res = "1";
}

if ($_POST["pw"] == $pw) {
$_SESSION["auth"] = "1";
$_SESSION["key"] = random_int(1,9999999999);
header("Refresh:0");
die();
}

}
?>
<?php

if (isset($_POST["commonname"]) && $_SESSION["auth"] == "1" && $_POST["key"] == $_SESSION["key"]) {
$commonname = $_POST["commonname"];
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
die("An error occurred.");
}
$sql = "SELECT cn FROM config WHERE cn='$commonname' AND status='active'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
header("Location: ".$_SERVER["PHP_SELF"]."?err=1");
die();
}
mysqli_close($conn);

$conn = new mysqli($servername, $username, $password, $dbname);
if (!$conn) {
die("An error occurred.");
}
$sql = "INSERT INTO config (cn, status)
VALUES ('$commonname', 'active')";
if (mysqli_query($conn, $sql)) {
} else {
die("Error occured....");
}

shell_exec("sudo sshpass -p '".$vpnserverpw."' ssh ".$vpnserveruser."@".$vpnserver." 'sh /var/create.sh $commonname' 2>&1");
shell_exec("sudo wget http://".$vpnserver."/".$commonname.".ovpn -O ".$_SERVER["DOCUMENT_ROOT"]."/".$commonname.".ovpn");
shell_exec("sudo sshpass -p '".$vpnserverpw."' ssh ".$vpnserveruser."@".$vpnserver." 'rm /var/www/html/$commonname.ovpn' 2>&1");
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header("Content-disposition: attachment; filename=\"".$commonname.".ovpn\"");
readfile($commonname.".ovpn");
unlink($commonname.".ovpn");
header("Location: ".$_SERVER["PHP_SELF"]);
die();
}

?>
<?php

if (isset($_GET["delconfig"]) && $_SESSION["auth"] == "1" && $_GET["key"] == $_SESSION["key"]) {
$commonname = $_GET["delconfig"];
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
die("An error occurred.");
}
$sql = "SELECT cn FROM config WHERE cn='$commonname' AND status='active'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) < 1) {
header("Location: ".$_SERVER["PHP_SELF"]."?err=2");
die();
}
mysqli_close($conn);

shell_exec("sudo sshpass -p '".$vpnserverpw."' ssh ".$vpnserveruser."@".$vpnserver." 'sh /var/revoke.sh $commonname' 2>&1");
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "UPDATE config SET status='inactive' WHERE cn='$commonname'";

if ($conn->query($sql) === TRUE) {
header("Location: ".$_SERVER["PHP_SELF"]);
die();
} else {
header("Location: ".$_SERVER["PHP_SELF"]."?err=3");
die();
}
}
?>

<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VPN Admin</title>
<link rel="stylesheet" href="bootstrap.min.css">
<style>
html,
body {
  height: 100%;
}

body {
  display: flex;
  align-items: center;
  padding-top: 40px;
  padding-bottom: 40px;
  background-color: #f5f5f5;
}

.vpn {
  width: 100%;
  max-width: 500px;
  margin: auto;
  padding: 15px;
}


</style>
</head>

<main class="vpn">
<h1 class="display-4">VPN Admin</h1>
<?php
if ($res == "1") {
echo <<<EOF
<p class="lead text-danger">Incorrect Password</p>
EOF;
}
?>
<div>
<?php
if ($_SESSION["auth"] == "1") {
$key = $_SESSION["key"];
if ($_GET["err"] == "1") {
echo '<p class="lead text-danger">Common Name is taken</p>';
}
if ($_GET["err"] == "2") {
echo '<p class="lead text-danger">Unable to delete configuration: Common name not found in database</p>';
}
if ($_GET["err"] == "2") {
echo '<p class="lead text-danger">Unable to delete from database. Client may be revoked.</p>';
}
echo <<<EOL
<form method="post">
  <div class="form-group">
    <input type="text" class="form-control" name="commonname" placeholder="CN (Common Name)">
    <input type="hidden" id="key" name="key" value="$key">
  </div><br>
  <input class="btn btn-primary w-100" type="submit" value="Generate Configuration">
</form>

<table class="table">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Common Name</th>
      <th scope="col"></th>
    </tr>
  </thead>
  <tbody>
EOL;
$conn = new mysqli($servername, $username, $password, $dbname);
if (!$conn) {
  die("Error occured...");
}
$sql = "SELECT cn FROM config WHERE status='active'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
  $incvar=1;
  while($row = $result->fetch_assoc()) {
    $key = $_SESSION["key"];
    echo '<tr><th scope="row">'.$incvar.'</th><td>'. $row["cn"] .'</td><td><a href="index.php?delconfig='.$row["cn"].'&key='.$key.'" class="btn btn-danger">Delete</a></td></tr>';
    $incvar=$incvar+1;
  }
}
$conn->close();

echo <<<EOF

  </tbody>
</table>
</div>
</main>
</body>
</html>

EOF;
die();
}
?>
<form method="post">
  <div class="form-group">
    <input type="password" class="form-control" name="pw" placeholder="Password">
  </div><br>
  <input class="btn btn-primary w-100" type="submit" value="Login">
</form>
</div>
</main>
</body>
</html>
