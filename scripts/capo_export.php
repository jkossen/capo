<?php
/*
 * Restrict access to localhost.
 * Basically copied from Symfony2 web/app_dev.php.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('capo_head.php');
require('include/global.php');
header('Content-type: text/plain');

/*
 * Fetch needed data
 */
$protocol = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";

$base_url = $protocol . $_SERVER['HTTP_HOST'] . $url_path;

$hosts = db_fetch_assoc('SELECT id, description, hostname FROM host');
$host_ids = Array();
foreach ($hosts as $host) {
    $host_ids[] = $host['id'];
}

$graph_templates = db_fetch_assoc('SELECT id, hash, name FROM graph_templates');

$weathermaps = db_fetch_assoc('SELECT m.id, m.titlecache, m.filehash FROM weathermap_maps m LEFT JOIN weathermap_auth a ON m.id = a.mapid WHERE m.active = "on" AND a.userid = 0');

$sql = 'SELECT @ci_id := `id` FROM cacti_instance WHERE base_url = "%s"';

printf("$sql;\n", $base_url);

/**
 * Retrieve data and store in temporary tables
 */

// create temporary hosts table to store temporary original data
$sql = '
CREATE TEMPORARY TABLE
    tmp_host (
        cacti_instance_id int(11) unsigned,
        orig_id int(11) unsigned,
        description varchar(150) COLLATE utf8_unicode_ci,
        hostname varchar(250) COLLATE utf8_unicode_ci,
        PRIMARY KEY(orig_id)
    )';

print $sql . ";\n";

if (count($hosts) > 0) {
    $sql = '
INSERT INTO tmp_host (
        cacti_instance_id, orig_id, description, hostname
    )
VALUES (@ci_id, "0", "null", "null");

INSERT INTO
    tmp_host (
        cacti_instance_id, orig_id, description, hostname
    )
VALUES
';
    for ($i=0; $i<count($hosts); $i++) {
        $host = $hosts[$i];
        $sql_tpl = '(@ci_id, "%s", "%s", "%s")';
        $sql .= sprintf($sql_tpl, $host['id'], $host['description'], $host['hostname']);
        if ($i < count($hosts)-1) {
            $sql .= ",\n";
        }
    }
    print $sql . ";\n";
}

// create temporary graph templates table to store temporary original data
$sql = '
CREATE TEMPORARY TABLE
    tmp_graph_template (
        orig_id int(11) unsigned,
        cacti_instance_id int(11) unsigned,
        hash varchar(32) COLLATE utf8_unicode_ci,
        name varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        PRIMARY KEY(orig_id)
    )';

print $sql . ";\n";

if (count($graph_templates) > 0) {
    $sql = '
INSERT INTO
    tmp_graph_template (
        orig_id, cacti_instance_id, hash, name
    )
VALUES ("0", @ci_id, "null", "null");

INSERT INTO
    tmp_graph_template (
        orig_id, cacti_instance_id, hash, name
    )
VALUES
';

    for ($i=0; $i<count($graph_templates); $i++) {
        $template = $graph_templates[$i];
        $sql_tpl = '("%s", @ci_id, "%s", "%s")';
        $sql .= sprintf($sql_tpl, $template['id'], $template['hash'], $template['name']);
        
        if ($i < count($graph_templates)-1) {
            $sql .= ",\n";
        }
    }
    print $sql . ";\n";
}

// retrieve graphs
$graph_sql = '
SELECT
    gtg.id as gtg_id,
    gtg.graph_template_id as graph_template_id,
    gtg.title as title,
    gtg.title_cache as title_cache,
    g.id as graph_local_id,
    g.host_id as host_id
    FROM
        graph_local g
    LEFT JOIN
        graph_templates_graph gtg ON gtg.local_graph_id = g.id';

$graphs = db_fetch_assoc($graph_sql);

// create temporary graphs table to store some temporary original data
$sql = '
CREATE TEMPORARY TABLE
    tmp_graph (
        gtg_id mediumint(8) unsigned,
        graph_template_id mediumint(8) unsigned,
        title varchar(255),
        title_cache varchar(255),
        graph_local_id mediumint(8) unsigned,
        host_id mediumint(8) unsigned,
        PRIMARY KEY(graph_local_id)
    )';

print $sql . ";\n";

if (count($graphs) > 0) {
    $sql = '
INSERT INTO
    tmp_graph (
        gtg_id, graph_template_id, title, title_cache, graph_local_id, host_id
    )
VALUES
';

    $sql_tpl = '("%s", "%s", "%s", "%s", "%s", "%s")';
    for ($j=0; $j<count($graphs); $j++) {
        $graph = $graphs[$j];

        $sql .= sprintf($sql_tpl, $graph['gtg_id'], $graph['graph_template_id'],
                        $graph['title'], $graph['title_cache'], $graph['graph_local_id'],
                        $graph['host_id']);

        if ($j < count($graphs)-1) {
            $sql .= ",\n";
        }
    }

    print $sql . ";\n";
}

// create temporary weathermaps table to store temporary original data
$sql = '
CREATE TEMPORARY TABLE
    tmp_weathermap (
        cacti_instance_id int(11) unsigned,
        orig_id int(11) unsigned,
        titlecache varchar(255) COLLATE utf8_unicode_ci,
        filehash varchar(255) COLLATE utf8_unicode_ci,
        PRIMARY KEY(orig_id)
    )';

print $sql . ";\n";

if (count($weathermaps) > 0) {
    $sql = '
INSERT INTO
    tmp_weathermap (
        cacti_instance_id, orig_id, titlecache, filehash
    )
VALUES
';
    for ($i=0; $i<count($weathermaps); $i++) {
        $weathermap = $weathermaps[$i];
        $sql_tpl = '(@ci_id, "%s", "%s", "%s")';
        $sql .= sprintf($sql_tpl, $weathermap['id'], $weathermap['titlecache'], $weathermap['filehash']);
        if ($i < count($weathermaps)-1) {
            $sql .= ",\n";
        }
    }

    print $sql . ";\n";
}

/**
 * Clean up removed data
 */

// delete old graphs
$sql = '
DELETE g FROM graph g
LEFT JOIN tmp_graph tmp ON g.graph_local_id = tmp.graph_local_id
WHERE g.cacti_instance_id = @ci_id
AND tmp.graph_local_id IS NULL;
';
print $sql . "\n";

// delete old graph templates
$sql = '
DELETE gt FROM graph_template gt
LEFT JOIN tmp_graph_template tmp ON gt.orig_id = tmp.orig_id
WHERE gt.cacti_instance_id = @ci_id
AND tmp.orig_id IS NULL;
';
print $sql . "\n";

// delete old hosts
$sql = '
DELETE h FROM host h
LEFT JOIN tmp_host tmp ON h.orig_id = tmp.orig_id
WHERE h.cacti_instance_id = @ci_id
AND tmp.orig_id IS NULL;
';

print $sql . "\n";

// delete old weathermaps
$sql = '
DELETE w FROM weathermap w
LEFT JOIN tmp_weathermap tmp ON w.orig_id = tmp.orig_id
WHERE w.cacti_instance_id = @ci_id
AND tmp.orig_id IS NULL;
';

print $sql . "\n";

/**
 * Insert / update new data
 */

// insert / update graph templates
$sql = '
INSERT INTO
    graph_template (
        orig_id, cacti_instance_id, hash, name
    )
SELECT orig_id, cacti_instance_id, hash, name FROM tmp_graph_template
ON DUPLICATE KEY UPDATE
    hash=VALUES(hash),
    name=VALUES(name)
;';

print $sql . "\n";

// insert / update hosts
$sql  = '
INSERT INTO
    host (
        cacti_instance_id, orig_id, description, hostname
    )
SELECT cacti_instance_id, orig_id, description, hostname
FROM tmp_host
ON DUPLICATE KEY UPDATE
    description=VALUES(description),
    hostname=VALUES(hostname);
';

print $sql . "\n";

// insert / update weathermaps
$sql  = '
INSERT INTO
    weathermap (
        cacti_instance_id, orig_id, titlecache, filehash
    )
SELECT cacti_instance_id, orig_id, titlecache, filehash
FROM tmp_weathermap
ON DUPLICATE KEY UPDATE
    titlecache=VALUES(titlecache),
    filehash=VALUES(filehash);
';

print $sql . "\n";

// insert / update graphs
$sql  = '
INSERT INTO graph (
    graph_local_id, graph_template_id, cacti_instance_id, host_id, title,
    title_cache
)
SELECT tmp.graph_local_id, t.id, @ci_id, h.id, tmp.title, tmp.title_cache
    FROM tmp_graph tmp
    JOIN (graph_template t, host h)
    ON
    (tmp.graph_template_id = t.orig_id AND t.cacti_instance_id = @ci_id
        AND tmp.host_id = h.orig_id AND h.cacti_instance_id = @ci_id
    )
ON DUPLICATE KEY UPDATE
    graph_template_id=VALUES(graph_template_id),
    host_id=VALUES(host_id),
    title=VALUES(title),
    title_cache=VALUES(title_cache);';

print $sql . "\n";
