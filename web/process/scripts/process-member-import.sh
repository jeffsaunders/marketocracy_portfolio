#!/usr/bin/expect -f

# Script using the EXPECT shell to call a URL passed to it as $argv 0.

set arg1 [lindex $argv 0]
set timeout 5
spawn lynx -term=linux "$arg1"
expect "Import Another"
send "q\n"
expect "Are you sure you want to quit?"
send "y\n"
