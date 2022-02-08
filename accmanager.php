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

if ($_SESSION["auth"] !== "1") {
        header('Location: /');
        die();
}


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

// Change Password
/*

if ($_SESSION["auth"] == "1" && $_POST["key"] == $_SESSION["key"]) {

// Make sure old password is correct, passwords match, and fit password requirements


}

*/
?>
<html>
    <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPN Admin - Account Manager</title>
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
<h1 class="display-4">Account Manager</h1>
<hr>
<div>
<!--<h4>Change Password</h2>
<form method="post">
<div class="form-group">
<input type="password" class="form-control" name="oldpassword" placeholder="Current Password">
</div><br>
<div class="form-group">
<input type="password" class="form-control" name="newpassword" placeholder="New Password">
</div><br>
<div class="form-group">
<input type="password" class="form-control" name="cpassword" placeholder="Confirm New Password">
</div><br>
<input type="hidden" id="key" name="key" value="<?php echo $_SESSION['key']; ?>">
<input class="btn btn-danger w-100" type="submit" value="Change Password">
</form>
</div>
<hr>-->
<h4>API Management and Login Attempts</h3>
        <div class="table-responsive">
<table class="table">
  <thead>
    <tr>
      <th scope="col">API Key</th>
      <th scope="col"></th>
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
<div class="text-center">
<a class="btn btn-primary" href="accmanager.php?genapi=1&key=<?php echo $_SESSION['key']; ?>">Generate API Key</a>
<a class="btn btn-primary" href="accmanager.php?dlloginattempts=1&key=<?php echo $_SESSION['key'];?>">Download Login Attempts</a>
</div>
<br><br><hr><a class="btn btn-outline-danger w-100" href="/">Back to Homepage</a>
        </main>
    </body>
</html>
