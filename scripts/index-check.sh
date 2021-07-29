#!/bin/bash
#
# watchdog script to make sure index-check php script is running
#

# Check to see if it's running
processCheck=`ps -ef | grep i[n]dex-check.php`

# If we have a blank response then it's not running
if test "$processCheck" = "" ; then
	# Start it
	php /var/www/html/portfolio.marketocracy.com/daemons/index-check.php &
fi
