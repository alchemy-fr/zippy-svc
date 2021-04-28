#!/bin/sh

cd /srv/app/ \
  && bin/console app:archives:clean-old
