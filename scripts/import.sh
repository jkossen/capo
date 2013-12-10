#!/bin/sh
TMPFILE=$(/bin/mktemp /tmp/capo.XXXXXXXX)
POSTFILE='/var/www/jkossen.nl/public/capo/scripts/capo_export.post'
BASE_URL="$1"
URL="${BASE_URL}capo_export.php"
DBUSER="capo"
DB="capo"
DBPASS="ddldKLDwdjkldDnmvcsv923ejkdlas9;"

if [ -z "$BASE_URL" ]; then
    echo 'ERROR: BASE_URL not set.' >&2
    exit 1
fi

/usr/bin/wget -t 1 --timeout 120 -O "$TMPFILE" --post-data="$(cat $POSTFILE)" "$URL"

if [ $? != 0 ]; then
	echo "ERROR: failed to retrieve $URL" >&2
	rm "$TMPFILE"
	exit 1
fi

if [ -s "$TMPFILE" ]; then
	/usr/bin/mysql -u $DBUSER --password="$DBPASS" $DB < $TMPFILE

	if [ $? != 0 ]; then
		echo "ERROR: mysql import failed" >&2
    else
        IMPORT_DATE=$(date +"%Y-%m-%d %H:%M:%S")
        SQL="UPDATE cacti_instance SET import_date = '$IMPORT_DATE', queue_import = 0 WHERE base_url = '$BASE_URL'"

        echo $SQL
        /usr/bin/mysql -u $DBUSER --password="$DBPASS" $DB -e "$SQL"
	fi
else
	echo "ERROR: size of SQL file $TMPFILE is 0" >&2
	rm "$TMPFILE"
	exit 1
fi

rm "$TMPFILE"

