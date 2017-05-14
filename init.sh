#!/usr/bin/env sh

php -S 0.0.0.0:80 -t /app/public/ /app/router.php &

socat -d -d -x udp4-listen:53,reuseaddr,fork udp:127.0.0.11:53 &

sockd -f /app/sockd.conf