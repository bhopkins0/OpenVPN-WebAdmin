<?php
include 'config.php';
include 'Net/SSH2.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && strlen($_POST["api"]) == 32 && preg_match("/^[A-Za-z0-9]+$/", $_POST["api"]) && isset($_POST["operation"])) {
  $api = $_POST["api"];
  $operation = $_POST["operation"];
  $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
  $sql = "SELECT api FROM apikeys WHERE api='$api'";
  $result = mysqli_query($conn, $sql);
  if (mysqli_num_rows($result) < 1) {
    http_response_code(403);
    echo "<h1>403 - Forbidden</h1>";
    die();
  } else {
    if ($operation == "downloadclient") {
      $cn = $_POST["cn"];
    // CN is not alphanumeric and is greater than 32
    if (strlen($cn) > 32 || !preg_match("/^[A-Za-z0-9]*$/", $cn)) {
        echo "Common name must be less than 32 characters and alphanumeric";
        die();
    }

    // CN is blank
    if (strlen($cn) < 1) {
        echo "Common name must not be blank";
        die();
    }
    $nconn = mysqli_connect($dbservername, $dbusername, $dbpassword, $dbname);
    if (!$nconn) {
        die("An error occurred.");
    }
    $nsql = "SELECT cn FROM config WHERE cn='$cn' AND status='active'";
    $nresult = mysqli_query($nconn, $nsql);
    if (mysqli_num_rows($nresult) == 0) {
        // The CN not in use
        echo "Invalid common name";
        die();
    }
    mysqli_close($nconn);

    $ssh = new Net_SSH2($vpnserver);
    if (!$ssh->login($vpnserveruser, $vpnserverpw)) {
        exit('Error');
    }

    // Download .ovpn file if it exists
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header("Content-disposition: attachment; filename=\"".$cn.".ovpn\"");
    echo $ssh->exec('cat /var/openvpn_clients/'.$cn.'.ovpn');
    die();


    }
    if ($operation == "genclient") {
      $cn = $_POST["cn"];
    // CN is not alphanumeric and is greater than 32
    if (strlen($cn) > 32 || !preg_match("/^[A-Za-z0-9]*$/", $cn)) {
        echo "Common name must be less than 32 characters and alphanumeric";
        die();
    }

    // CN is blank
    if (strlen($cn) < 1) {
        echo "Common name must not be blank";
        die();
    }
    $nconn = mysqli_connect($dbservername, $dbusername, $dbpassword, $dbname);
    if (!$nconn) {
        die("An error occurred.");
    }
    $nsql = "SELECT cn FROM config WHERE cn='$cn' AND status='active'";
    $nresult = mysqli_query($nconn, $nsql);
    if (mysqli_num_rows($nresult) > 0) {
        // The CN is already in use
        echo "Common name already taken";
        die();
    }
    mysqli_close($nconn);

    $nconn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if (!$nconn) {
        die("Error");
    }
    $nsql = "INSERT INTO config (cn, status)
    VALUES ('$cn', 'active')";
    if (mysqli_query($nconn, $nsql)) {

    } else {
        die("Error");
    }

    $ssh = new Net_SSH2($vpnserver);
    if (!$ssh->login($vpnserveruser, $vpnserverpw)) {
        exit('Error');
    }

    // Run the create.sh script on the server and download the new .ovpn file
    $ssh->exec('/var/openvpn_scripts/create.sh '.$cn);
    echo "Client $cn generated successfully";
    die();

    }


  }
  $conn->close();

die();
}

http_response_code(403);
echo "<h1>403 - Forbidden</h1>";

?>
