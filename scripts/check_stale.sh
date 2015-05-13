#!/bin/sh
WDIR="/var/www/capo"
cd "$WDIR"

DBHOST="$(grep database_host app/config/parameters.yml |awk '{print $2}' |sed -e 's/"//g')"
DBUSER="$(grep database_user app/config/parameters.yml |awk '{print $2}' |sed -e 's/"//g')"
DB="$(grep database_name app/config/parameters.yml |awk '{print $2}' |sed -e 's/"//g')"
DBPASS="$(grep database_password app/config/parameters.yml |awk '{print $2}' |sed -e 's/"//g')"
STALE_DATE=$(date -d "24 hours ago" +"%Y-%m-%d %H:%M")
SQL="SELECT base_url FROM cacti_instance WHERE active = 1 AND import_date <= '$STALE_DATE'"

EXIT_OK="0"
EXIT_WARN="1"
EXIT_CRIT="2"
EXIT_UNKNOWN="3"

export PATH="$PATH:/bin:/sbin:/usr/bin:/usr/local/bin"

NR_OF_STALE=$(mysql --skip-column-names -h "$DBHOST" -u "$DBUSER" --password="$DBPASS" -e "$SQL" "$DB" |wc -l)

echo $NR_OF_STALE

if [ "$NR_OF_STALE" = "0" ]; then
    echo "OK: no stale cacti instance data detected | stale_cacti_instances=0"
    exit $EXIT_OK
else
    echo "CRITICAL: stale data for $NR_OF_STALE cacti instances detected | stale_cacti_instances=$NR_OF_STALE"
    exit $EXIT_CRIT
fi
