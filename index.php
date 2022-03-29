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
if ($_SERVER["REQUEST_METHOD"] == "POST") {


if (isset($_POST["lo"]) && $_POST["lo"] == $_SESSION["key"]) {
        $_SESSION["auth"] = "";
        $_SESSION["key"] = "";
        header("Location: /");
        die();
}


  $user         = strtolower($_POST["username"]);
  $password     = $_POST["password"];
  $attempt_ip   = ip2long($_SERVER['REMOTE_ADDR']);
  $attempt_time = time();

  if (strlen($user) > 32 || strlen($password) > 50 || strlen($password) < 8) {
  $_SESSION["loginerr"] = 1;
  header("Location: /");
  die();
  }
  if (!preg_match('/^[A-Za-z0-9]+$/', $user)) {
  $_SESSION["loginerr"] = 1;
  header("Location: /");
  die();
  }

  $conn = mysqli_connect($dbservername, $dbusername, $dbpassword, $dbname);
  $sql = "SELECT creation_time FROM accounts WHERE username='$user'";
  $result = mysqli_query($conn, $sql);
  if (mysqli_num_rows($result) < 1) {
  $_SESSION["loginerr"] = 1;
  header("Location: /");
  die();
  }
  mysqli_close($conn);

  $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
  $sql = "SELECT password FROM accounts WHERE username='$user'";
  $result = mysqli_query($conn, $sql);
  if (mysqli_num_rows($result) > 0) {
    while($row = $result->fetch_assoc()) {
      if (password_verify($password, $row["password"])) {
        $_SESSION["auth"]  = "1";
        $success = 1;
        $_SESSION["user"]  = $user;
        $_SESSION["key"] = random_int(1,9999999999); // Idea here is to prevent potential CSRF attack
      } else {
        $_SESSION["loginerr"] = 1;
        $success = 0;
      }
    }
  }
  $conn->close();

if ($success == 1) {
  $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
  $sql = "INSERT INTO login_attempts(username, ip, result, login_time)
  VALUES ('$user', '$attempt_ip', 'success', $attempt_time)";

  if (mysqli_query($conn, $sql)) {
    header("Location: home.php");
  }
  $conn->close();
die();
}
if ($success == 0) {
  $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
  $sql = "INSERT INTO login_attempts(username, ip, result, login_time)
  VALUES ('$user', '$attempt_ip', 'fail', $attempt_time)";

  if (mysqli_query($conn, $sql)) {
    header("Location: /");
  }
  $conn->close();
die();
}
}


?>

<html>
    <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPN Admin</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    </head>
    <body class="bg-dark">
<div class="mt-4 text-white d-flex align-items-center justify-content-center"><div>
        <h1 class="display-4 text-white">VPN Admin</h1>
<div><form method="post">
<?php
if ($_SESSION["loginerr"] == 1) {
        echo '<div class="alert alert-danger" role="alert">Invalid username or password</div>';
        $_SESSION["loginerr"] = 0;
}
?>
<div class="form-group">
<input type="text" class="form-control" name="username" placeholder="Username">
</div><br>
<div class="form-group">
<input type="password" class="form-control" name="password" placeholder="Password">
</div><br>
<input class="btn btn-primary w-100" type="submit" value="Login">
</form>
            </div></div></div>
    </body>
</html>
