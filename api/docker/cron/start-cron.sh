#!/bin/sh

/usr/sbin/crond -l 2 && tail -f /var/log/cron.log
