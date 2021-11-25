<?php
$dbservername = "localhost"; // MySQL servername
$dbusername = ""; // MySQL username
$dbpassword = ""; // MySQL password
$dbname = ""; // MySQL database containing the 'config' table
$adminpw = ''; // Password to access the WebAdmin
$vpnserver = ""; // IP address of the OpenVPN server
$vpnserveruser = ""; // SSH username of the OpenVPN server
$vpnserverpw = ''; // SSH password of the OpenVPN server
$restrict2vpn = "0"; // Change this to "1" if you want the webadmin to only be accessible while connected to the VPN server

if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
  $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
}
