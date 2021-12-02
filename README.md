# OpenVPN-WebAdmin Version 0.3 -- Work in Progress
Manage OpenVPN configurations in a web browser

This is functional code for a personal project. It has not been vetted for security vulnerabilities.


# Installation

I have only tested this on LEMP stacks (PHP 7.4) running Ubuntu >= 20.04.

1. Fill out the blank information in the `config.php`
2. Run `php installation.php` 
3. Install OpenVPN on a separate server using the [OpenVPN Installer for Webadmin](https://github.com/bhopkins0/OpenVPN-Installer-For-Webadmin). **This is required to ensure compatibility.**


# Todo: 
* Instead of using <p></p> for errors, use bootstrap alerts
* Add a navbar instead of using cards
* Add page to display login attempts
* Maybe do something with DNS logs?
* Release a custom nginx conf file to mitigate potential attacks (ex: PHP crashes and config.php gets exposed)
* Support multiple accounts
* Clean up code: indent, make comments, tidy up if statements, etc
* Make page refresh after adding client
* Sanitize input
* Security enchancements
