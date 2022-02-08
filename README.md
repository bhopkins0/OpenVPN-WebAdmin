# OpenVPN-WebAdmin Version 0.5
Manage OpenVPN configurations in a web browser

This is functional code for a personal project. It has not been vetted for security vulnerabilities.


# Installation

I have only tested this on LEMP stacks (PHP 7.4) running Ubuntu >= 20.04. 


0. Install LEMP stack  and set up MySQL (create database and user for the webadmin)
1. Install OpenVPN on a separate server using the [OpenVPN Installer for Webadmin](https://github.com/bhopkins0/OpenVPN-Installer-For-Webadmin). **This is required to ensure compatibility.**
2. Run `chown -R www-data:www-data /var/www/YOUR_WEB_DIR/`
3. Go to the installation page in a web browser (installation.php)
4. Update your nginx configuration file with [this](https://github.com/bhopkins0/OpenVPN-WebAdmin/wiki/Example-nginx-configuration-for-OpenVPN-Webadmin).


# Todo: 
* Instead of using `<p></p>` for errors, use bootstrap alerts
* Add a navbar instead of using cards
* Maybe do something with DNS logs?
* Support multiple accounts
* Clean up code: indent, make comments, tidy up if statements, etc
* Sanitize input
* Security enchancements
