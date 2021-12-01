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
?>
<html>
    <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPN Admin - Connected Clients</title>
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
      max-width:650px;
      margin: auto;
      padding: 15px;
    }


    </style>
    </head>
    <body>
        <main class="vpn">
<h1 class="display-4">Connected Clients</h1>
        <div class="table-responsive">
<table class="table">
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
$ssh = new Net_SSH2($vpnserver);
if (!$ssh->login($vpnserveruser, $vpnserverpw)) {
    exit('Error occured.');
}
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
            </div>
<a class="btn btn-outline-danger w-100" href="/">Back to Homepage</a>
        </main>
    </body>
</html>
