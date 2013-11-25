#!/bin/sh
CAPODIR="/var/www/capo"

find "$CAPODIR/app/var/sessions" -type f -name sess_\* -mtime +2 -exec rm -f {} \;

