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
if ($_SESSION["auth"] != "1") {
header("Location: index.php");
die();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {

// Change port
if (isset($_POST["cport"]) && isset($_POST["key"])) {

if ($_POST["key"] != $_SESSION["key"]) {
header("Location: vpnmanager.php");
die();
}

if (!ctype_digit($_POST["cport"])) {
// Port provided contains characters aside from numbers
$_SESSION["err"] = 2;
header("Location: vpnmanager.php");
die();
}

$newport = $_POST["cport"];

    $ssh = new Net_SSH2($vpnserver);
    if (!$ssh->login($vpnserveruser, $vpnserverpw)) {
        exit('Error occured.');
    }
    $ssh->exec('/var/openvpn_scripts/changeport.sh '.$newport);
    $ssh->exec('service openvpn restart');
    $_SESSION["err"] = 1;
    $_SESSION["nport"] = $newport;
    header("Location: vpnmanager.php");
    die();
}

// Start or Stop
if (isset($_POST["startorstop"])) {

if ($_POST["startorstop"] != $_SESSION['key']) {
header("Location: vpnmanager.php");
die();
}

    $ssh = new Net_SSH2($vpnserver);
    if (!$ssh->login($vpnserveruser, $vpnserverpw)) {
        exit('Error occured.');
    }
    $ssh->exec('/var/openvpn_scripts/status.sh 3');
}

}
?>

<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VPN Manager</title>
<link rel="stylesheet" href="bootstrap.min.css">
</head>
<body class="bg-dark">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggler" aria-controls="navbarToggler" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarToggler">
      <a class="navbar-brand" href="#">VPN Admin</a>
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" aria-current="page" href="home.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="manageclients.php">Client Manager</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="vpnmanager.php">VPN Manager</a>
        </li>

      </ul>
      <ul class="navbar-nav mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="accmanager.php">WebAdmin Manager</a>
        </li>
      </ul>

    </div>
  </div>
</nav>
<?php
if ($_SESSION["err"] == 1) {
echo <<<EOL

<div class="mt-4 text-white d-flex align-items-center justify-content-center">
<div class="alert alert-success text-center" role="alert">
<p>Port successfully changed to <strong>{$_SESSION["nport"]}</strong></p>
<p>You will need to create new client configurations or change the port in your clients' configuration!</p>
</div>
</div>
EOL;

unset($_SESSION["nport"]);
$_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 2) {
echo <<<EOL

<div class="mt-4 text-white d-flex align-items-center justify-content-center">
<div class="alert alert-danger" role="alert">
Port must only be an integer
</div>
</div>
EOL;

$_SESSION["err"] = -1;

}
?>
<div class="mt-4 text-white d-flex align-items-center justify-content-center">
<div class="table-responsive">
<table class="table table-dark">
  <thead>
    <tr>
      <th scope="col">Settings</th>
      <th scope="col">Action</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Change DNS Server</td>
      <td><form method="post"><div class="input-group mb-3">
  <input type="text" class="form-control" placeholder="Coming soon" aria-label="Change DNS" aria-describedby="cdns" disabled>
<input class="btn btn-primary" type="submit" id="cdns" value="Change DNS" disabled>
</div></form></td>
    </tr>
    <tr>
      <td>Change Port</td>
      <td><form method="post"><div class="input-group mb-3">
<input type="hidden" id="key" name="key" value="<?php echo $_SESSION['key']; ?>">
  <input type="text" class="form-control" name="cport" placeholder="Change port" aria-label="Change Port" aria-describedby="cport">
<input class="btn btn-primary" type="submit" id="cport" value="Change Port">
</div></form></td>
    </tr>
    <tr>
      <td>Start/Stop OpenVPN</td>
      <td><form method="post">
<input type="hidden" id="startorstop" name="startorstop" value="<?php echo $_SESSION['key']; ?>">

<?php
$ssh = new Net_SSH2($vpnserver);
    if (!$ssh->login($vpnserveruser, $vpnserverpw)) {
        exit('Error occured.');
    }
    echo $ssh->exec('/var/openvpn_scripts/status.sh 2');

?>
</form></td>
    </tr>
  </tbody>
</table>
</div></div>
<?php
$key = $_SESSION["key"];
echo <<<EOL
<div class="p-4 mt-2 d-flex align-items-center justify-content-center">
<form method="post" action="index.php" class="col-sm-9 col-md-6 col-lg-8">
<input type="hidden" id="lo" name="lo" value="$key">
<input class="btn btn-outline-danger w-100" type="submit" value="Logout">
</form>
</div>
EOL;?>
<script src="bootstrap.bundle.min.js"></script>
</body>
</html>
