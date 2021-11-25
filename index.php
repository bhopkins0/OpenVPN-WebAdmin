<?php
session_start();
include 'config.php';
include 'Net/SSH2.php';

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

if ($_POST["pw"] !== $adminpw) {
$res = "1";
}

if ($_POST["pw"] == $adminpw) {
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
if (strlen($commonname) > 32 || !preg_match("/^[A-Za-z0-9]*$/", $commonname)) {
$_SESSION["err"] = 4;
header("Location: ".$_SERVER["PHP_SELF"]);
die();
}
if (strlen($commonname) < 1) {
$_SESSION["err"] = 5;
header("Location: ".$_SERVER["PHP_SELF"]);
die();
}
$conn = mysqli_connect($dbservername, $dbusername, $dbpassword, $dbname);
if (!$conn) {
die("An error occurred.");
}
$sql = "SELECT cn FROM config WHERE cn='$commonname' AND status='active'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
$_SESSION["err"] = 1;
header("Location: ".$_SERVER["PHP_SELF"]);
die();
}
mysqli_close($conn);

$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
if (!$conn) {
die("An error occurred.");
}
$sql = "INSERT INTO config (cn, status)
VALUES ('$commonname', 'active')";
if (mysqli_query($conn, $sql)) {
} else {
die("Error occured.");
}
$ssh = new Net_SSH2($vpnserver);
if (!$ssh->login($vpnserveruser, $vpnserverpw)) {
    exit('Error occured..');
}
$ssh->exec('/var/openvpn_scripts/create.sh '.$commonname);
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header("Content-disposition: attachment; filename=\"".$commonname.".ovpn\"");
echo $ssh->exec('cat /var/openvpn_clients/'.$commonname.'.ovpn');
die();
}

?>
<?php

if (isset($_GET["delconfig"]) && $_SESSION["auth"] == "1" && $_GET["key"] == $_SESSION["key"]) {
$commonname = $_GET["delconfig"];
$conn = mysqli_connect($dbservername, $dbusername, $dbpassword, $dbname);
if (!$conn) {
die("An error occurred.");
}
$sql = "SELECT cn FROM config WHERE cn='$commonname' AND status='active'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) < 1) {
$_SESSION["err"] = 2;
header("Location: ".$_SERVER["PHP_SELF"]);
die();
}
mysqli_close($conn);
$ssh = new Net_SSH2($vpnserver);
if (!$ssh->login($vpnserveruser, $vpnserverpw)) {
    exit('Error occured.');
}
$ssh->exec('/var/openvpn_scripts/revoke.sh '.$commonname);
$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "UPDATE config SET status='inactive' WHERE cn='$commonname'";

if ($conn->query($sql) === TRUE) {
header("Location: ".$_SERVER["PHP_SELF"]);
die();
} else {
$_SESSION["err"] = 3;
header("Location: ".$_SERVER["PHP_SELF"]);
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
if ($_SESSION["err"] == 1) {
echo '<p class="lead text-danger">Common Name is taken</p>';
$_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 2) {
echo '<p class="lead text-danger">Unable to delete configuration: Common name not found in database</p>';
$_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 3) {
echo '<p class="lead text-danger">Unable to delete from database. Client may be revoked.</p>';
$_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 4) {
echo '<p class="lead text-danger">Common name must be alphanumeric and less than 32 characters long.</p>';
$_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 5) {
echo '<p class="lead text-danger">Common name can not be empty.</p>';
$_SESSION["err"] = 0;
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
$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
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
EOF;

$ssh = new Net_SSH2($vpnserver);
if (!$ssh->login($vpnserveruser, $vpnserverpw)) {
    exit('Error occured.');
}
$ssh->exec('vnstati -vs -o /var/vnstat.png');
echo '<img class="img-fluid" src="data:image/png;base64,'.$ssh->exec('base64 -w 0 /var/vnstat.png').'" alt="Network stats">';

echo <<<EOF
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
