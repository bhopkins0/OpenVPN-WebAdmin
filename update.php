<?php
session_start();
include 'config.php';
include 'Net/SSH2.php';

$currentversion = "0.7";
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

// Update

if ($_SESSION["auth"] == "1" && $_GET["key"] == $_SESSION["key"]) {

$latestversion = file_get_contents('https://raw.githubusercontent.com/bhopkins0/OpenVPN-WebAdmin/main/latestversion');

if ($latestversion == $currentversion) {
header("Location: accmanager.php");
die();
}

if ($latestversion > $currentversion) {


// retrieve newest home.php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://raw.githubusercontent.com/bhopkins0/OpenVPN-WebAdmin/main/home.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
$out = curl_exec($ch);
curl_close($ch);

$fp = fopen('home.php', 'w');
fwrite($fp, $out);
fclose($fp);

// retrieve newest index.php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://raw.githubusercontent.com/bhopkins0/OpenVPN-WebAdmin/main/index.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
$out = curl_exec($ch);
curl_close($ch);
$fp = fopen('index.php', 'w');
fwrite($fp, $out);
fclose($fp);


// retrieve newest vpnmanager.php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://raw.githubusercontent.com/bhopkins0/OpenVPN-WebAdmin/main/vpnmanager.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
$out = curl_exec($ch);
curl_close($ch);
$fp = fopen('vpnmanager.php', 'w');
fwrite($fp, $out);
fclose($fp);

// retrieve newest manageclients.php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://raw.githubusercontent.com/bhopkins0/OpenVPN-WebAdmin/main/manageclients.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
$out = curl_exec($ch);
curl_close($ch);
$fp = fopen('manageclients.php', 'w');
fwrite($fp, $out);
fclose($fp);

// retrieve newest accmanager.php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://raw.githubusercontent.com/bhopkins0/OpenVPN-WebAdmin/main/accmanager.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
$out = curl_exec($ch);
curl_close($ch);
$fp = fopen('accmanager.php', 'w');
fwrite($fp, $out);
fclose($fp);

// retrieve newest api.php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://raw.githubusercontent.com/bhopkins0/OpenVPN-WebAdmin/main/api.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
$out = curl_exec($ch);
curl_close($ch);
$fp = fopen('api.php', 'w');
fwrite($fp, $out);
fclose($fp);

// update scripts

$ssh = new Net_SSH2($vpnserver);
if (!$ssh->login($vpnserveruser, $vpnserverpw)) {
   exit('Error');
}
$ssh->exec('wget -qO- https://raw.githubusercontent.com/bhopkins0/OpenVPN-Installer-For-Webadmin/main/update.sh | bash');

// Update newest update.sh

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://raw.githubusercontent.com/bhopkins0/OpenVPN-WebAdmin/main/update.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
$out = curl_exec($ch);
curl_close($ch);
$fp = fopen('update.php', 'w');
fwrite($fp, $out);
fclose($fp);

header("Location: accmanager.php");
die();
}

}

?>
