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

if ($_SESSION["auth"] !== "1") {
        header('Location: /');
        die();
}

// User wants to revoke a client
if (isset($_GET["delconfig"]) && $_SESSION["auth"] == "1" && $_GET["key"] == $_SESSION["key"]) {
    $commonname = $_GET["delconfig"];
    $conn = mysqli_connect($dbservername, $dbusername, $dbpassword, $dbname);
    if (!$conn) {
        die("An error occurred.");
    }
    $sql = "SELECT cn FROM config WHERE cn='$commonname' AND status='active'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) < 1) {
        $_SESSION["err"] = 1;
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
        $_SESSION["err"] = 2;
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
        $_SESSION["err"] = 3;
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
    <title>VPN Admin - Manage Clients</title>
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
    <body>
        <main class="vpn">
        <h1 class="display-4">Manage Clients</h1>
     <div>
<?php
if ($_SESSION["auth"] == "1") {
    $key = $_SESSION["key"];
    if ($_SESSION["err"] == 1) {
        echo '<p class="lead text-danger">Unable to delete configuration: Common name not found in database</p>';
        $_SESSION["err"] = 0;
    }
    if ($_SESSION["err"] == 2) {
        echo '<p class="lead text-danger">Unable to delete from database. Client may have been revoked.</p>';
        $_SESSION["err"] = 0;
    }
    if ($_SESSION["err"] == 3) {
        echo '<p class="lead text-danger">Error downloading the OpenVPN configuration..</p>';
        $_SESSION["err"] = 0;
    }

echo <<<EOL

<table class="table">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Common Name</th>
      <th scope="col"></th>
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
    echo '<tr><th scope="row">'.$incvar.'</th><td>'. $row["cn"] .'</td><td><a href="manageclients.php?dl='.$row["cn"].'&key='.$key.'" class="btn btn-primary">Download</a></td><td><a href="manageclients.php?delconfig='.$row["cn"].'&key='.$key.'" class="btn btn-danger">Delete</a></td></tr>';
    $incvar=$incvar+1;
  }
}
$conn->close();

echo <<<EOF
  </tbody>
</table>
            </div>
<a class="btn btn-outline-danger w-100" href="/">Back to Homepage</a>
        </main>
    </body>
</html>

EOF;
die();
}
?>
