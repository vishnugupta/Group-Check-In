*** Important Notice ***

This is not officially supported by Think Ministry, Inc. The intent of this software is to be an functional application that demonstrates interaction with the MinistryPlatform API via PHP. Please consult LICENSE.txt for copyright/warranty information. Questions regarding this software should not be directed to Think Ministry support.


*** Requirements/Recommendations ***

1. Developed and tested against PHP version 5.3.6. (There has been at least one reported issue with PHP 5.2.17)
2. Must be a current Think Ministry, Inc. Enterprise level customer with valid API credentials to benefit from this code.



*** PRE-INSTALLATION NOTES ***

If you install PHP on IIS 7+, please take the following additional step.

Open IIS Manager and go to IIS -> PHP Manager
Locate "PHP Settings", then click "Manage All Settings"
Locate "short_open_tag" and change the value from "Off" to "On"


*** Initial Configuration ***

1. Place all files from the zip package into any directory you'd like
	ex: http://www.example.com/gci/

2. Execute the SQL in install.sql in your MinistryPlatform database. This will do the following:
	- Create 3 stored procedures that Group Check-In requires
	- Create a security role for this application to validate against.

	**** DO NOT POST THE SQL FILE TO YOUR WEB SERVER ****

3. Open /config/config.php and populate your API connection information
	** You will need the ID of the security role created during the previous step. You can find this in MinistryPlatform under System Admin -> Security Roles, click on "Group Check-In", and view the ID when the detail screen appears.

4. Save your changes to config.php.

5. Visit the home page in any modern (HTML5+) browser, ideally an iOS device. However, this app should load and function correctly in the current versions of Firefox, Chrome, and Safari if desired.

6. That's it! You're all set. Test the log-in process to ensure your connection works.
7.
