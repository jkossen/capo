#!/bin/sh
WDIR="/var/www/jkossen.nl/public/capo/scripts"
DB="capo"
DBUSER="capo"
DBPASS="ddldKLDwdjkldDnmvcsv923ejkdlas9;"

OLD_DATE=$(date -d "1 hour ago" +"%Y-%m-%d %H:%M")

cd "$WDIR"

mysql -u "$DBUSER" --password="$DBPASS" -e "SELECT base_url FROM cacti_instance WHERE active = 1 AND (import_date <= '$OLD_DATE' OR queue_import = 1)" "$DB" |grep -v 'base_url' |while read BASE_URL; do
    ./import.sh "$BASE_URL"
done

