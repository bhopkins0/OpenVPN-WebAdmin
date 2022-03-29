# OpenVPN-WebAdmin Version 0.7
Manage OpenVPN configurations in a web browser


![example](https://github.com/bhopkins0/OpenVPN-WebAdmin/raw/main/vpnadmin0.7.png)

DISCLAIMER:
All of the code I publish on Github is either for my own personal use/projects or to help me get better at programming. As of 3/14/2022, this means a few things:

* Don't expect great error reporting.
* Don't expect the code to be indented. It's my bad habit and my text editor (nano) does not auto-indent.
* It might not work on your system. This is because I designed it for my specific system and needs.
* There *could* be security vulnerabilities. It is recommended to select 'Only allow access from VPN IP' during installation.


# Features

As of version 0.7, the webadmin has the following features:

* Create and revoke OpenVPN client configurations
* Download OpenVPN client configurations
* View a list of clients connected to the VPN server
* Start or stop the OpenVPN daemon
* Change the port the OpenVPN server listens on
* View a graph of network statistics
* Generate and delete API keys
* Create OpenVPN configurations from an API 
* Ability to only allow access from the VPN server's IP address
* View failed/successful login attempts to the webadmin


# Installation

I have only tested this on LEMP stacks (PHP 7.4 and PHP 8.0) running Ubuntu >= 20.04. 


0. Install LEMP stack  and set up MySQL (create database and user for the webadmin)
1. Install OpenVPN on a separate server using the [OpenVPN Installer for Webadmin](https://github.com/bhopkins0/OpenVPN-Installer-For-Webadmin). **This is required to ensure compatibility.**
2. Run `chown -R www-data:www-data /var/www/YOUR_WEB_DIR/`
3. Go to the installation page in a web browser (installation.php)
4. Update your nginx configuration file with [this](https://github.com/bhopkins0/OpenVPN-WebAdmin/wiki/Example-nginx-configuration-for-OpenVPN-Webadmin).


# Todo: 

* Ability to change DNS servers
* Ability to change password
* Maybe do something with DNS logs?
* Support multiple accounts
* Clean up code: indent, make comments, tidy up if statements, etc
* Sanitize input
* Security enchancements
