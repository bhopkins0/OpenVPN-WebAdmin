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

// Delete API key

if (isset($_GET["del"]) && $_SESSION["auth"] == "1" && $_GET["key"] == $_SESSION["key"]) {
    $delapi = $_GET["del"];
    $conn = mysqli_connect($dbservername, $dbusername, $dbpassword, $dbname);
    if (!$conn) {
        die("An error occurred.");
    }
    $sql = "SELECT api FROM apikeys WHERE api='$delapi' AND status='active'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) < 1) {
        header("Location: accmanager.php");
        die();
    }
    mysqli_close($conn);
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE apikeys SET status='inactive' WHERE api='$delapi'";

    if ($conn->query($sql) === TRUE) {
        header("Location: accmanager.php");
        die();
    } else {
        $_SESSION["err"] = 2;
        header("Location: accmanager.php");
        die();
    }
}

// Download Login Attempts

if ($_GET["dlloginattempts"] === "1" && $_SESSION["auth"] == "1" && $_GET["key"] == $_SESSION["key"]) {
   $conn = mysqli_connect($dbservername, $dbusername, $dbpassword, $dbname);
    if (!$conn) {
        die("An error occurred.");
    }
    $sql = "SELECT username, ip, result, login_time FROM login_attempts";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
       header('Content-Description: File Transfer');
       header('Content-Type: application/octet-stream');
       header("Content-disposition: attachment; filename=\"loginattempts.txt\"");
          while($row = $result->fetch_assoc()) {
             echo $row['username'] .' - '. long2ip($row['ip']). ' - '. $row['result']. ' - '. date('r',$row['login_time'])."\n";
          }
       die();
    }
    mysqli_close($conn);
    die();
}

// Create API key

if ($_GET["genapi"] === "1" && $_SESSION["auth"] == "1" && $_GET["key"] == $_SESSION["key"]) {
    $newapi = bin2hex(openssl_random_pseudo_bytes(16));
    $conn = mysqli_connect($dbservername, $dbusername, $dbpassword, $dbname);
    if (!$conn) {
        die("An error occurred.");
    }
    $sql = "SELECT api FROM apikeys WHERE api='$newapi'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 1) {
        header("Location: accmanager.php");
        die();
    }
    mysqli_close($conn);


    $conn = mysqli_connect($dbservername, $dbusername, $dbpassword, $dbname);
    $sql = "INSERT INTO apikeys (api, status) VALUES ('$newapi', 'active')";
    if (mysqli_query($conn, $sql)) {
    header("Location: accmanager.php");
    }
    mysqli_close($conn);
    die();
}

?>

<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Account Manager</title>
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
          <a class="nav-link" href="vpnmanager.php">VPN Manager</a>
        </li>

      </ul>
root@vps-7cd486b5:/var/www/vpnadmin#
        <li class="nav-item">
          <a class="nav-link active" href="accmanager.php">WebAdmin Manager</a>
        </li>
      </ul>

    </div>
  </div>
</nav>
<div class="mt-4 text-white d-flex align-items-center justify-content-center">
<div class="table-responsive">
<table class="table table-dark">
  <thead>
    <tr>
      <th scope="col">API Key</th>
      <th scope="col">Action</th>
    </tr>
  </thead>
  <tbody>
<?php
$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
if (!$conn) {
  die("Error occured...");
}
$sql = "SELECT api FROM apikeys WHERE status='active'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
  $incvar=1;
  while($row = $result->fetch_assoc()) {
    $key = $_SESSION["key"];
    echo '<tr><td>'. $row["api"] .'</td><td><a href="accmanager.php?del='.$row["api"].'&key='.$key.'" class="btn btn-danger">Delete</a></td></tr>';
  }
}
$conn->close();


?>
  </tbody>
</table>
</div></div>
<div class="mt-4 text-white d-flex align-items-center justify-content-center">
<div>
<a class="btn btn-primary" href="accmanager.php?genapi=1&key=<?php echo $_SESSION['key']; ?>">Generate API Key</a>
<a class="btn btn-primary" href="accmanager.php?dlloginattempts=1&key=<?php echo $_SESSION['key'];?>">Download Login Attempts</a>
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal">
  Update Webadmin
</button>
</div>
</div>
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

<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modallabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-dark" id="modallabel">Update WebAdmin</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-dark">This update tool will retrieve the latest files from GitHub and replace the current ones. If the tool doesn't work, run the following command in the web directory: <pre class="text-center text-dark">chown www-data:www-data *</pre></p>
        <a class="btn btn-outline-danger w-100" href="update.php?key=<?php echo $_SESSION['key']; ?>">I understand, proceed with the update</a>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary">Exit</button>
      </div>
    </div>
  </div>
</div>
<script src="bootstrap.bundle.min.js"></script>
</body>
</html>
