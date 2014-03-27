#!/bin/sh

#
# configuration
WDIR="$(dirname $0)"
cd "$WDIR"

TMPFILE=$(/bin/mktemp /tmp/capo.XXXXXXXX)
BASE_URL="$1"
URL="${BASE_URL}capo_export.php"
DBHOST="$(grep database_host ../app/config/parameters.yml |awk '{print $2}' |sed -e 's/"//g')"
DBUSER="$(grep database_user ../app/config/parameters.yml |awk '{print $2}' |sed -e 's/"//g')"
DB="$(grep database_name ../app/config/parameters.yml |awk '{print $2}' |sed -e 's/"//g')"
DBPASS="$(grep database_password ../app/config/parameters.yml |awk '{print $2}' |sed -e 's/"//g')"
RETRIEVAL_CODE="$(grep capo_retrieval_code ../app/config/parameters.yml |awk '{print $2}' |sed -e 's/"//g')"

export PATH="$PATH:/bin:/sbin:/usr/bin:/usr/local/bin"

#
# functions
cleanup() {
	if [ -f "$TMPFILE" ]; then
        rm -f "$TMPFILE"
    fi
}

exit_err() {
    echo "ERROR: $1" >&2
    cleanup
    exit $2
}

exit_int() {
    echo "Signal caught. Exiting ..." >&2
    cleanup
    exit 2
}

retrieve_data() {
    wget -t 1 --timeout 120 -O "$TMPFILE" --post-data="code=$RETRIEVAL_CODE" "$1"
    
    if [ $? != 0 ]; then
        exit_err "ERROR: failed to retrieve $URL" 1
    fi
}

integrate_data() {
    if [ -s "$TMPFILE" ]; then
	    mysql -h "$DBHOST" -u "$DBUSER" --password="$DBPASS" $DB < $TMPFILE

	    if [ $? != 0 ]; then
		    exit_err "ERROR: mysql import failed" 1
        else
            IMPORT_DATE=$(date +"%Y-%m-%d %H:%M:%S")
            SQL="UPDATE cacti_instance SET import_date = '$IMPORT_DATE', queue_import = 0 WHERE base_url = '$BASE_URL'"

            mysql -h "$DBHOST" -u "$DBUSER" --password="$DBPASS" $DB -e "$SQL"
	    fi
    else
	    exit_err "ERROR: size of SQL file $TMPFILE is 0" 1
    fi
}

#
# run
trap exit_int HUP INT TERM

if [ -z "$BASE_URL" ]; then
    exit_err 'BASE_URL not set.' 1
fi

retrieve_data "$URL"
integrate_data
cleanup
