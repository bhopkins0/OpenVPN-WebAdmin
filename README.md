# OpenVPN-WebAdmin Version 0.1 -- Work in Progress
Manage OpenVPN configurations in a web browser

This is functional code serving as a rough draft for the project. 

It is barebones and does not incorporate adequate security features for production use. 
# Todo: 
* Display status.log
* Support multiple accounts
* Clean up code: indent, make comments, tidy up if statements, etc
* Switch from sshpass
* Switch from HTTP to SCP for retrieving the configuration file from the OpenVPN server 
* Support tls-auth
* Make page refresh after adding client
* Display bandwidth statistics of server
* Create an installation file to create the MySQL table and admin password
* Sanitize input 
* Use something other than $_GET for reporting errors
* Security enchancements
