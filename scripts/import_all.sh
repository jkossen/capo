#!/bin/sh
WDIR="$(dirname $0)"
cd "$WDIR"

DBHOST="$(grep database_host ../app/config/parameters.yml |awk '{print $2}' |sed -e 's/"//g')"
DBUSER="$(grep database_user ../app/config/parameters.yml |awk '{print $2}' |sed -e 's/"//g')"
DB="$(grep database_name ../app/config/parameters.yml |awk '{print $2}' |sed -e 's/"//g')"
DBPASS="$(grep database_password ../app/config/parameters.yml |awk '{print $2}' |sed -e 's/"//g')"
STALE_DATE=$(date -d "1 hour ago" +"%Y-%m-%d %H:%M")
SQL="SELECT base_url FROM cacti_instance WHERE active = 1 AND (import_date <= '$STALE_DATE' OR queue_import = 1)"

export PATH="$PATH:/bin:/sbin:/usr/bin:/usr/local/bin"

mysql -h "$DBHOST" -u "$DBUSER" --password="$DBPASS" -e "$SQL" "$DB" |grep -v 'base_url' |while read BASE_URL; do
    "$WDIR/import.sh" "$BASE_URL"
done
