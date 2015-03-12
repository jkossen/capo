#!/bin/sh
if [ -z "$1" ]; then
    echo "USAGE: nagios_check_capo.sh path/to/capo_dir" >& 2
    exit 1
fi

WDIR="$1"
cd "$WDIR"

DBHOST="$(grep database_host app/config/parameters.yml |awk '{print $2}' |sed -e 's/"//g')"
DBUSER="$(grep database_user app/config/parameters.yml |awk '{print $2}' |sed -e 's/"//g')"
DB="$(grep database_name app/config/parameters.yml |awk '{print $2}' |sed -e 's/"//g')"
DBPASS="$(grep database_password app/config/parameters.yml |awk '{print $2}' |sed -e 's/"//g')"
STALE_DATE=$(date -d "90 minutes ago" +"%Y-%m-%d %H:%M")

SQL_NR_OF_CACTI_INSTANCES="SELECT COUNT(1) FROM cacti_instance WHERE active = 1"
SQL_NR_OF_GRAPHS="SELECT COUNT(1) FROM graph, cacti_instance WHERE graph.cacti_instance_id = cacti_instance.id AND cacti_instance.active = 1"
SQL_NR_OF_WMAPS="SELECT COUNT(1) FROM weathermap, cacti_instance WHERE weathermap.cacti_instance_id = cacti_instance.id AND cacti_instance.active = 1"
SQL_NR_OF_USERS="SELECT COUNT(1) FROM fos_user, usergroup where fos_user.enabled = 1 and fos_user.group_id = usergroup.id and usergroup.active = 1"
SQL_NR_OF_GROUPS="SELECT COUNT(1) FROM usergroup where active = 1"
SQL_NR_OF_SAVED_SELECTIONS="SELECT COUNT(1) FROM graph_selections, fos_user, usergroup where graph_selections.active = 1 and graph_selections.user_id = fos_user.id and fos_user.enabled = 1 and fos_user.group_id = usergroup.id and usergroup.active = 1"
SQL_NR_OF_APIUSERS="SELECT COUNT(1) FROM api_user where active = 1"
SQL_NR_OF_EVENTLOGS="SELECT COUNT(1) FROM event_log" 
SQL_STALE="SELECT COUNT(base_url) FROM cacti_instance WHERE active = 1 AND import_date <= '$STALE_DATE'"

EXIT_OK="0"
EXIT_WARN="1"
EXIT_CRIT="2"
EXIT_UNKNOWN="3"

export PATH="$PATH:/bin:/sbin:/usr/bin:/usr/local/bin"

MYSQL_CMD="mysql --skip-column-names -h "$DBHOST" -u "$DBUSER" --password="$DBPASS" -e"

NR_OF_GRAPHS=$($MYSQL_CMD "$SQL_NR_OF_GRAPHS" "$DB")
MYSQL_EXITCODE=$?

if [ "$MYSQL_EXITCODE" != "0" ]; then
    PERFDATA_UNKNOWN="nr_of_cacti_instances=-1;;;; nr_of_graphs=-1;;;; nr_of_weathermaps=-1;;;; nr_of_users=-1;;;; nr_of_groups=-1;;;; nr_of_saved_selections=-1;;;; nr_of_apiusers=-1;;;; nr_of_eventlogs=-1;;;; stale_cacti_instances=-1;;;;"
    echo "UNKNOWN: unable to check capo status| $PERFDATA_UNKNOWN"
    exit $EXIT_UNKNOWN
fi

NR_OF_CACTI_INSTANCES=$($MYSQL_CMD "$SQL_NR_OF_CACTI_INSTANCES" "$DB")
NR_OF_WMAPS=$($MYSQL_CMD "$SQL_NR_OF_WMAPS" "$DB")
NR_OF_USERS=$($MYSQL_CMD "$SQL_NR_OF_USERS" "$DB")
NR_OF_GROUPS=$($MYSQL_CMD "$SQL_NR_OF_GROUPS" "$DB")
NR_OF_SAVED_SELECTIONS=$($MYSQL_CMD "$SQL_NR_OF_SAVED_SELECTIONS" "$DB")
NR_OF_APIUSERS=$($MYSQL_CMD "$SQL_NR_OF_APIUSERS" "$DB")
NR_OF_EVENTLOGS=$($MYSQL_CMD "$SQL_NR_OF_EVENTLOGS" "$DB")
NR_OF_STALE=$($MYSQL_CMD "$SQL_STALE" "$DB")

PERFDATA="nr_of_cacti_instances=$NR_OF_CACTI_INSTANCES;;;; nr_of_graphs=$NR_OF_GRAPHS;;;; nr_of_weathermaps=$NR_OF_WMAPS;;;; nr_of_users=$NR_OF_USERS;;;; nr_of_groups=$NR_OF_GROUPS;;;; nr_of_saved_selections=$NR_OF_SAVED_SELECTIONS;;;; nr_of_apiusers=$NR_OF_APIUSERS;;;; nr_of_eventlogs=$NR_OF_EVENTLOGS;;;; stale_cacti_instances=$NR_OF_STALE;;;;"

if [ "$NR_OF_STALE" = "0" ]; then
    echo "OK: no stale cacti instance data detected | $PERFDATA"
    exit $EXIT_OK
else
    echo "WARNING: stale data for $NR_OF_STALE cacti instances detected | $PERFDATA"
    exit $EXIT_WARN
fi
