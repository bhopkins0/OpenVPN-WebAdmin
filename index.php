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


// Login attempt
if ($_POST["pw"] && isset($_SESSION["auth"])) {

    // Fail
    if ($_POST["pw"] !== $adminpw) {
        $res = "1";
    }

    // Success
    if ($_POST["pw"] == $adminpw) {
        $_SESSION["auth"] = "1";
        $_SESSION["key"] = random_int(1,9999999999); // Idea here is to prevent potential CSRF attack
        header("Location: /");
    die();
}

}

// Authenticated user wants to log out
if ($_POST["lo"] == $_SESSION["key"]) {
        $_SESSION["auth"] = "";
        $_SESSION["key"] = "";
}

// Authenticated user wants to create a new client
if (isset($_POST["commonname"]) && $_SESSION["auth"] == "1" && $_POST["key"] == $_SESSION["key"]) {
    $commonname = strtolower($_POST["commonname"]);

    // CN is not alphanumeric and is greater than 32
    if (strlen($commonname) > 32 || !preg_match("/^[A-Za-z0-9]*$/", $commonname)) {
        $_SESSION["err"] = 2;
        header("Location: /");
        die();
    }

    // CN is blank
    if (strlen($commonname) < 1) {
        $_SESSION["err"] = 3;
        header("Location: /");
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
        $_SESSION["err"] = 1;
        header("Location: /");
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
    $_SESSION["success"] = 1;
    header("Location: /");
    die();
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
    <body>
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
        echo '<p class="lead text-danger">Common name must be alphanumeric and less than 32 characters long.</p>';
        $_SESSION["err"] = 0;
    }
    if ($_SESSION["err"] == 3) {
        echo '<p class="lead text-danger">Common name can not be empty.</p>';
        $_SESSION["err"] = 0;
    }
    if ($_SESSION["success"] == 1) {
        echo '<p class="lead text-success">Client configuration generated successfully. You can download it in the Client Manager.</p>';
        $_SESSION["success"] = 0;
    }
echo <<<EOL
<form method="post">
  <div class="form-group">
    <input type="text" class="form-control" name="commonname" placeholder="CN (Common Name)">
    <input type="hidden" id="key" name="key" value="$key">
  </div><br>
  <input class="btn btn-outline-primary w-100" type="submit" value="Generate Configuration">
</form>
<div class="card-deck mb-3 text-center">
<div class="card mb-4 box-shadow">
<div class="card-header">
<h4 class="my-0 font-weight-normal">Manage Clients</h4>
</div>
<div class="card-body">
<p class="lead">Delete clients & download client .ovpn files</p>
<a class="btn btn-primary btn-block" href="manageclients.php">Go to client manager</a>
</div>
</div>
<div class="card mb-4 box-shadow">
<div class="card-header">
<h4 class="my-0 font-weight-normal">Connected Clients</h4>
</div>
<div class="card-body">
<p class="lead">List of connected clients and information about them</p>
<a class="btn btn-primary btn-block" href="clients.php">Go to connected clients</a>
</div>
</div>
<div class="card mb-4 box-shadow">
<div class="card-header">
<h4 class="my-0 font-weight-normal">Network Graph</h4>
</div>
<div class="card-body">
<p class="lead">Bandwidth statistics of the VPN server</p>
<a class="btn btn-primary btn-block" href="netstats.php">Go to network graph</a>
</div>
</div>
<form method="post">
<input type="hidden" id="lo" name="lo" value="$key">
<input class="btn btn-danger w-100" type="submit" value="Logout">
</form>
            </div>
        </main>
    </body>
</html>

EOL;
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
