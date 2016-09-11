#!/usr/bin/env bash
/usr/bin/php -f /station_parser.php
/etc/init.d/nginx start
/usr/bin/supervisord -n
