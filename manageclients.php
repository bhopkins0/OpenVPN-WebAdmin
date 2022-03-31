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


if (isset($_POST["commonname"]) && $_SESSION["auth"] == "1" && $_POST["key"] == $_SESSION["key"]) {
    $commonname = strtolower($_POST["commonname"]);

    // CN is not alphanumeric and is greater than 32
    if (strlen($commonname) > 32 || !preg_match("/^[A-Za-z0-9]*$/", $commonname)) {
        $_SESSION["err"] = 1;
        header("Location: manageclients.php");
        die();
    }

    // CN is blank
    if (strlen($commonname) < 1) {
        $_SESSION["err"] = 2;
        header("Location: manageclients.php");
        die();
    }
    $conn = mysqli_connect($dbservername, $dbusername, $dbpassword, $dbname);
    if (!$conn) {
        die("An error occurred.");
    }
    $sql = "SELECT cn FROM config WHERE cn='$commonname' AND status='active'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        // The CN is already in use
        $_SESSION["err"] = 3;
        header("Location: manageclients.php");
        die();
    }
    mysqli_close($conn);

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if (!$conn) {
        die("Error");
    }
    $sql = "INSERT INTO config (cn, status)
    VALUES ('$commonname', 'active')";
    if (mysqli_query($conn, $sql)) {

    } else {
        die("Error");
    }

    $ssh = new Net_SSH2($vpnserver);
    if (!$ssh->login($vpnserveruser, $vpnserverpw)) {
        exit('Error');
    }

    // Run the create.sh script on the server and download the new .ovpn file
    $ssh->exec('/var/openvpn_scripts/create.sh '.$commonname);
    header("Location: manageclients.php");
    die();
}



// Delete client

if (isset($_GET["delconfig"]) && $_SESSION["auth"] == "1" && $_GET["key"] == $_SESSION["key"]) {
    $commonname = $_GET["delconfig"];
    $conn = mysqli_connect($dbservername, $dbusername, $dbpassword, $dbname);
    if (!$conn) {
        die("An error occurred.");
    }
    $sql = "SELECT cn FROM config WHERE cn='$commonname' AND status='active'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) < 1) {
        // Needs error reporting
        header("Location: manageclients.php");
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
        header("Location: manageclients.php");
        die();
    } else {
        // Needs error reporting
        header("Location: manageclients.php");
        die();
    }
}

// User downloads configuration
if (isset($_GET["dl"]) && $_SESSION["auth"] == "1" && $_GET["key"] == $_SESSION["key"]) {
    $commonname = $_GET["dl"];
    $conn = mysqli_connect($dbservername, $dbusername, $dbpassword, $dbname);
    if (!$conn) {
        die("An error occurred.");
    }
    $sql = "SELECT cn FROM config WHERE cn='$commonname' AND status='active'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) < 1) {
        // Needs error reporting here
        header("Location: manageclients.php");
        die();
    }
    mysqli_close($conn);
    $ssh = new Net_SSH2($vpnserver);
    if (!$ssh->login($vpnserveruser, $vpnserverpw)) {
        exit('Error occured.');
    }

    // Download .ovpn file if it exists
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header("Content-disposition: attachment; filename=\"".$commonname.".ovpn\"");
    echo $ssh->exec('cat /var/openvpn_clients/'.$commonname.'.ovpn');
    die();
}
?>

<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Client Manager</title>
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
          <a class="nav-link active" href="manageclients.php">Client Manager</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="vpnmanager.php">VPN Manager</a>
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
<div class="alert alert-danger" role="alert">
CN must be alphanumeric and less than 32 characters
</div>
</div>
EOL;

$_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 2) {
echo <<<EOL

<div class="mt-4 text-white d-flex align-items-center justify-content-center">
<div class="alert alert-danger" role="alert">
CN must not be blank
</div>
</div>
EOL;

$_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 3) {
echo <<<EOL

<div class="mt-4 text-white d-flex align-items-center justify-content-center">
<div class="alert alert-danger" role="alert">
CN is already in use
</div>
</div>
EOL;

$_SESSION["err"] = 0;
}
?>
<div class="mt-4 text-white d-flex align-items-center justify-content-center">
<div>
<h4 class="text-center">Create new client</h4>
<form class="form-inline" method="post">
<div class="form-group mb-3">
<input type="text" class="form-control" name="commonname" placeholder="CN (Common Name)">
<input type="hidden" id="key" name="key" value="<?php echo $_SESSION['key']; ?>">
</div>
<input class="btn btn-primary w-100" type="submit" value="Generate Configuration">
</div>
</div>
<div class="mt-4 text-white d-flex align-items-center justify-content-center">
<div class="table-responsive">
<table class="table table-dark">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Common Name</th>
      <th scope="col"></th>
      <th scope="col"></th>
    </tr>
  </thead>
  <tbody>
<?php

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
    echo '<tr><th scope="row">'.$incvar.'</th><td>'. $row["cn"] .'</td><td><a href="manageclients.php?dl='.$row["cn"].'&key='.$key.'" class="btn btn-primary">Download</a></td><td><a href="manageclients.php?delconfig='.$row["cn"].'&key='.$key.'" class="btn btn-danger">Delete</a></td></tr>';
    $incvar++;
  }
}
$conn->close();


?>
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
<div class="mt-4 text-white d-flex align-items-center justify-content-center">
<div>
EOL;?>
<script src="bootstrap.bundle.min.js"></script>
</body>
</html>
