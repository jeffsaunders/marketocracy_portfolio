#!/usr/bin/expect
spawn rsync  -avz --remove-source-files 192.168.111.215:/api/trade_processing/trade_output* /var/www/html/portfolio.marketocracy.com/web/xml/tradeHistory
expect "password:"
send "KfabyZcbE3\n"
expect eof

spawn rsync  -avz --remove-source-files 192.168.111.215:/api2/trade_processing/trade_output* /var/www/html/portfolio.marketocracy.com/web/xml/tradeHistory
expect "password:"
send "KfabyZcbE3\n"
expect eof

#if [catch wait] {
#    puts "rsync failed"
#    exit 1
#}
exit 0
