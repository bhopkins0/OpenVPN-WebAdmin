<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // Quick error checking before doing anything else
        if (!isset($_POST["sql_server"])) {
                $_SESSION["err"] = 1;
                header("Location: installation.php");
                die();
        }
        if (!isset($_POST["sql_db"])) {
                $_SESSION["err"] = 2;
                header("Location: installation.php");
                die();
        }
        if (!isset($_POST["sql_user"])) {
                $_SESSION["err"] = 3;
                header("Location: installation.php");
                die();
        }
        if (!isset($_POST["sql_pass"])) {
                $_SESSION["err"] = 4;
                header("Location: installation.php");
                die();
        }
        if (!isset($_POST["vpn_server"])) {
                $_SESSION["err"] = 5;
                header("Location: installation.php");
                die();
        }
        if (!isset($_POST["vpn_user"])) {
                $_SESSION["err"] = 6;
                header("Location: installation.php");
                die();
        }
        if (!isset($_POST["vpn_pass"])) {
                $_SESSION["err"] = 7;
                header("Location: installation.php");
                die();
        }
        if (!isset($_POST["admin_user"])) {
                $_SESSION["err"] = 8;
                header("Location: installation.php");
                die();
        }
        if (!isset($_POST["admin_pass"])) {
                $_SESSION["err"] = 9;
                header("Location: installation.php");
                die();
        }
        if (!isset($_POST["admin_cpass"]) || $_POST["admin_pass"] !== $_POST["admin_cpass"]) {
                $_SESSION["err"] = 10;
                header("Location: installation.php");
                die();
        }
        if (strlen($_POST["admin_pass"]) > 50 || strlen($_POST["admin_pass"]) < 8) {
                $_SESSION["err"] = 11;
                header("Location: installation.php");
                die();
        }
        if (strlen($_POST["admin_user"]) > 32) {
                $_SESSION["err"] = 12;
                header("Location: installation.php");
                die();
        }
        if (preg_match('/[^a-z_\-0-9]/i', $_POST["admin_user"])) {
                $_SESSION["err"] = 14;
                header("Location: installation.php");
                die();
        }
        if (ip2long($_POST["vpn_server"]) === FALSE || ip2long($_POST["vpn_server"]) == -1) {
                $_SESSION["err"] = 15;
                header("Location: installation.php");
                die();
        }
        $sql_server = $_POST["sql_server"];
        $sql_db = $_POST["sql_db"];
        $sql_user = $_POST["sql_user"];
        $sql_pass = addslashes($_POST["sql_pass"]);
        $admin_user = strtolower($_POST["admin_user"]);
        $admin_pass = password_hash($_POST["admin_pass"], PASSWORD_BCRYPT);
        $vpn_server = $_POST["vpn_server"];
        $vpn_user = $_POST["vpn_user"];
        $vpn_pass = addslashes($_POST["vpn_pass"]);
        $time = time();
        $conn = new mysqli($sql_server, $sql_user, $sql_pass, $sql_db);
        if ($conn->connect_error) {
                $_SESSION["err"] = 13;
                $_SESSION["errmsg"] = $conn->connect_error;
                header("Location: installation.php");
                die();
        }
        $sql = "CREATE TABLE config (
        cn VARCHAR(256) CHARACTER SET utf8 COLLATE utf8_general_ci,
        status VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci
        )";
        if ($conn->query($sql) === TRUE) {

        } else {
                $_SESSION["err"] = 13;
                $_SESSION["errmsg"] = $conn->error;
                header("Location: installation.php");
                die();
        }

        $conn->close();

        $conn = new mysqli($sql_server, $sql_user, $sql_pass, $sql_db);
        if ($conn->connect_error) {
                $_SESSION["err"] = 13;
                $_SESSION["errmsg"] = $conn->connect_error;
                header("Location: installation.php");
                die();
        }
        $sql = "CREATE TABLE accounts (
        username VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci,
        password VARCHAR(72) CHARACTER SET utf8 COLLATE utf8_general_ci,
        creation_time INT(64)
        )";
        if ($conn->query($sql) === TRUE) {

        } else {
                $_SESSION["err"] = 13;
                $_SESSION["errmsg"] = $conn->error;
                header("Location: installation.php");
                die();
        }

        $conn->close();

        $conn = new mysqli($sql_server, $sql_user, $sql_pass, $sql_db);
        if ($conn->connect_error) {
                $_SESSION["err"] = 13;
                $_SESSION["errmsg"] = $conn->connect_error;
                header("Location: installation.php");
                die();
        }
        $sql = "CREATE TABLE login_attempts (
        username VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci,
        ip VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci,
        result VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci,
        login_time INT(64)
        )";
        if ($conn->query($sql) === TRUE) {

        } else {
                $_SESSION["err"] = 13;
                $_SESSION["errmsg"] = $conn->error;
                header("Location: installation.php");
                die();
        }

        $conn->close();

        $conn = new mysqli($sql_server, $sql_user, $sql_pass, $sql_db);
        if ($conn->connect_error) {
                $_SESSION["err"] = 13;
                $_SESSION["errmsg"] = $conn->connect_error;
                header("Location: installation.php");
                die();
        }

        $sql = "INSERT INTO accounts (username, password, creation_time)
        VALUES ('$admin_user', '$admin_pass', $time)";

        if ($conn->query($sql) === TRUE) {

        } else {
                $_SESSION["err"] = 13;
                $_SESSION["errmsg"] = $conn->error;
                header("Location: installation.php");
                die();
        }

        $conn->close();

        $config = '<?php' . PHP_EOL;
        $config .= '$dbservername = "'.$sql_server.'";' . PHP_EOL;
        $config .= '$dbusername = "'.$sql_user.'";' . PHP_EOL;
        $config .= '$dbpassword = "'.$sql_pass.'";' . PHP_EOL;
        $config .= '$dbname = "'.$sql_db.'";' . PHP_EOL;
        $config .= '$vpnserver = "'.$vpn_server.'";' . PHP_EOL;
        $config .= '$vpnserveruser = "'.$vpn_user.'";' . PHP_EOL;
        $config .= '$vpnserverpw = "'.$vpn_pass.'";' . PHP_EOL;
        if (isset($_POST["restrict2vpn"])) {
                $config .= '$restrict2vpn = "1";' . PHP_EOL;
        } else {
                $config .= '$restrict2vpn = "0";' . PHP_EOL;
        }
        if (isset($_POST["using_cloudflare"])) {
                $config .= 'if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {' . PHP_EOL;
                $config .= '  $_SERVER["REMOTE_ADDR"] = $_SERVER["HTTP_CF_CONNECTING_IP"];' . PHP_EOL;
                $config .= '}' . PHP_EOL;
        }

        $fp = fopen('config.php', 'w');
        fwrite($fp, $config);
        fclose($fp);
        unlink("installation.php");
}

?>

<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Installation</title>
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

.install {
  width: 100%;
  max-width: 500px;
  margin: auto;
  padding: 15px;
}


</style>
</head>
<body>
<main class="install">
<?php
  
// This is ugly but it works for right now
if ($_SESSION["err"] == 1) {
        echo '<div class="alert alert-danger" role="alert">Error: MySQL host blank</div>';
        $_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 2) {
        echo '<div class="alert alert-danger" role="alert">Error: Database name blank</div>';
        $_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 3) {
        echo '<div class="alert alert-danger" role="alert">Error: MySQL username blank</div>';
        $_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 4) {
        echo '<div class="alert alert-danger" role="alert">Error: MySQL password blank</div>';
        $_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 5) {
        echo '<div class="alert alert-danger" role="alert">Error: VPN server IP blank</div>';
        $_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 6) {
        echo '<div class="alert alert-danger" role="alert">Error: VPN username blank</div>';
        $_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 7) {
        echo '<div class="alert alert-danger" role="alert">Error: VPN password blank</div>';
        $_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 8) {
        echo '<div class="alert alert-danger" role="alert">Error: WebAdmin username blank</div>';
        $_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 9) {
        echo '<div class="alert alert-danger" role="alert">Error: WebAdmin password blank</div>';
        $_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 10) {
        echo '<div class="alert alert-danger" role="alert">Error: WebAdmin passwords do not match</div>';
        $_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 11) {
        echo '<div class="alert alert-danger" role="alert">Error: WebAdmin password must be between 8 and 50 characters</div>';
        $_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 12) {
        echo '<div class="alert alert-danger" role="alert">Error: WebAdmin username must be less than 32 characters</div>';
        $_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 13) {
        echo '<div class="alert alert-danger" role="alert">MySQL Error ('.$_SESSION["errmsg"].')</div>';
        $_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 14) {
        echo '<div class="alert alert-danger" role="alert">Error: WebAdmin username must be alphanumeric</div>';
        $_SESSION["err"] = 0;
}
if ($_SESSION["err"] == 15) {
        echo '<div class="alert alert-danger" role="alert">Error: VPN server must be an IP address</div>';
        $_SESSION["err"] = 0;
}
?>
<h1 class="display-6">Installation</h1>
<div>
<form method="post">
<div class="mb-3">
<label for="sql_server">MySQL Host</label>
<input type="text" class="form-control" name="sql_server" id="sql_server" placeholder="localhost">
</div>
<div class="mb-3">
<label for="sql_db">Database Name</label>
<input type="text" class="form-control" name="sql_db" id="sql_db" placeholder="vpnadmin">
</div>
<div class="mb-3">
<label for="sql_user">MySQL Username</label>
<input type="text" class="form-control" name="sql_user" id="sql_user" placeholder="username">
</div>
<div class="mb-3">
<label for="sql_pass">MySQL Password</label>
<input type="password" class="form-control" name="sql_pass" id="sql_pass" placeholder="password">
</div>
<div class="mb-3">
<label for="vpn_server">VPN Server IP</label>
<input type="text" class="form-control" name="vpn_server" id="vpn_server" placeholder="xxx.xxx.xxx.xxx">
</div>
<div class="mb-3">
<label for="vpn_user">VPN Server Username (SSH)</label>
<input type="text" class="form-control" name="vpn_user" id="vpn_user" placeholder="username">
</div>
<div class="mb-3">
<label for="vpn_pass">VPN Server Password (SSH)</label>
<input type="password" class="form-control" name="vpn_pass" id="vpn_pass" placeholder="password">
</div>
<div class="mb-3">
<label for="admin_user">WebAdmin Username - must be less than 32 characters and alphanumeric</label>
<input type="text" class="form-control" name="admin_user" id="admin_user" placeholder="username">
</div>
<div class="mb-3">
<label for="admin_pass">WebAdmin Password - must be between 8 and 50 characters</label>
<input type="password" class="form-control" name="admin_pass" id="admin_pass" placeholder="password">
</div>
<div class="mb-3">
<label for="admin_cpass">Confirm WebAdmin Password</label>
<input type="password" class="form-control" name="admin_cpass" id="admin_cpass" placeholder="password">
</div>
<div class="form-check">
<input class="form-check-input" type="checkbox" value="" id="restrict2vpn" name="restrict2vpn">
<label class="form-check-label" for="restrict2vpn">Only allow access from VPN IP</label>
</div>
<div class="form-check">
<input class="form-check-input" type="checkbox" value="" id="using_cloudflare" name="using_cloudflare">
<label class="form-check-label" for="using_cloudflare">I am using Cloudflare</label>
</div>
<input class="btn btn-primary w-100" type="submit" value="Install">
</form>
<p class="text-muted text-end">Brent's OpenVPN WebAdmin v0.4</p>
</div>
</main>
</body>
</html>
