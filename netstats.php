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
    <title>VPN Admin - Network Stats</title>
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
<h1 class="display-4">Network Stats</h1>
<?php
$ssh = new Net_SSH2($vpnserver);
if (!$ssh->login($vpnserveruser, $vpnserverpw)) {
    exit('Error occured.');
}
$ssh->exec('vnstati -vs -o /var/vnstat.png');
echo '<img class="img-fluid" src="data:image/png;base64,'.$ssh->exec('base64 -w 0 /var/vnstat.png').'" alt="Network stats">';
?>
<br><br><a class="btn btn-outline-danger w-100" href="/">Back to Homepage</a>
        </main>
    </body>
</html>
