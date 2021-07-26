# db_optimization

This PHP code creates a backup of a given database, then checks each table in the database. If necessary, it then repairs and optimizes those tables.

There are two version of code - version 0.3 is for use with "mysql_connect" connections to a database (for older versions of PHP), version 0.4 is for use with PDO connections ("mysql_connect" is depricated in PHP 7.4 and 8).

# config.php
This file holds your credentials - database host (server), database name, username and password.  If you want to receive email reports for the process, enter email information here too.

# mysql_functions.php
This file now has two functions, one for a PDO connection and one for 'mysql_connect.'  You should no longer have to edit this file. 

# dbMaintV0-3.php
As noted above, version three uses "mysql_connect" for your database connection.  This is an older version of the code that works with PHP 5.x and early versions of 7.x.

The file is currently written to be run as a cronjob.  But, it can also be run manually and information can be written to the browser screen.  In this file uncomment all the 'print' lines to write the information to the browser.

Additionally, the code is written to send a report to one or more email addresses.  Email addresses can be added to the config.php file or embedded in the code (see line 181 for 'from' and line 186 for 'to' email addresses.  The 'to' field is an array with one or more addresses.  If you do not want to send email(s), comment out the 'foreach' beginning on line 188.

# dbMaintV0-4.php
This version of the code uses PDO to connect to the database.  PHP versions from 7.4 and up will not accept "mysql_connect."

As with the older version of this code, this file is currently written to be run as a cronjob.  But, it can also be run manually from the browser.  If you want output from the process to be written to the browser's screen, uncomment the "for testing ->" lines and the 'print' lines as you see fit (you may not want all info sent to the screen).

By default, this version also emails a report to one or more email addresses.  Email addresses can be added to the config.php file or embedded in the code (see line 195 for 'from' and line 200 for 'to' email addresses.  As before, the 'to' variable is an array of one or more addresses.  If you do not want to send email(s), comment out the 'foreach' on line 202.
