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
?>

<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VPN Admin</title>
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
          <a class="nav-link active" aria-current="page" href="home.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="manageclients.php">Client Manager</a>
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
<div class="mt-4 text-white d-flex align-items-center justify-content-center">
  <?php
    $ssh = new Net_SSH2($vpnserver);
    if (!$ssh->login($vpnserveruser, $vpnserverpw)) {
        exit('Error');
    }

    echo $ssh->exec('/var/openvpn_scripts/status.sh 1').'</h1></div>';
    $ssh->exec('vnstati -vs -o /var/vnstat.png');
    echo '<div class="mt-5 d-flex align-items-center justify-content-center"><img class="img-fluid" src="data:image/png;base64,'.$ssh->exec('base64 -w 0 /var/vnstat.png').'" alt="Network stats"></div>';

?>
<div class='p-1 mt-5 d-flex align-items-center justify-content-center'>
<div class="table-responsive">
<table class="table table-dark">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Common Name</th>
      <th scope="col">IP:PORT</th>
      <th scope="col">Data Recieved</th>
      <th scope="col">Data Sent</th>
      <th scope="col">Connected Since</th>
    </tr>
  </thead>
  <tbody>
<?php
$connected_clients = array_filter(explode("\n", $ssh->exec("sed -n '/Connected Since/,/ROUTING TABLE/{/Connected Since/b;/ROUTING TABLE/b;p}' /var/log/openvpn/status.log")));
$incvar = 1;
foreach ($connected_clients as $nclient) {
        $nclient = explode(",", $nclient);
        $bytesrec = (int)$nclient[2] . ' Bytes';
        $bytessent = (int)$nclient[3] . ' Bytes';
        if ((int)$nclient[2] > 1000 && (int)$nclient[2] < 1000000) {
                $bytesrec = round(((int)$nclient[2] / 1000),2) . ' KB';
        }
        if ((int)$nclient[2] > 1000000 && (int)$nclient[2] < 1000000000) {
                $bytesrec = round(((int)$nclient[2] / 1000000),2) . ' MB';
        }
        if ((int)$nclient[2] > 1000000000 && (int)$nclient[2] < 1000000000000) {
                $bytesrec = round(((int)$nclient[2] / 1000000000),2) . ' GB';
        }
        if ((int)$nclient[2] > 1000000000000 && (int)$nclient[2] < 1000000000000000) {
                $bytesrec = round(((int)$nclient[2] / 1000000000000),2) . ' TB';
        }
        if ((int)$nclient[3] > 1000 && (int)$nclient[3] < 1000000) {
                $bytessent = round(((int)$nclient[3] / 1000),2) . ' KB';
        }
        if ((int)$nclient[3] > 1000000 && (int)$nclient[3] < 1000000000) {
                $bytessent = round(((int)$nclient[3] / 1000000),2) . ' MB';
        }
        if ((int)$nclient[3] > 1000000000 && (int)$nclient[3] < 1000000000000) {
                $bytessent = round(((int)$nclient[3] / 1000000000),2) . ' GB';
        }
        if ((int)$nclient[3] > 1000000000000 && (int)$nclient[3] < 1000000000000000) {
                $bytessent = round(((int)$nclient[3] / 1000000000000),2) . ' TB';
        }

        echo '<tr><th scope="row">'.$incvar.'</th>';
        echo '<td>'.$nclient[0].'</td>';
        echo '<td>'.$nclient[1].'</td>';
        echo '<td>'.$bytessent.'</td>';
        echo '<td>'.$bytesrec.'</td>';
        echo '<td>'.$nclient[4].'</td>';
        $incvar=$incvar+1;
}

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
EOL;?>
<script src="bootstrap.bundle.min.js"></script>
</body>
</html>
