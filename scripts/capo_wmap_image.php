<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2012 The Cacti Group                                 |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDTool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
*/


require('capo_head.php');

/* since we'll have additional headers, tell php when to flush them */
ob_start();

$guest_account = true;
include_once("./include/global.php");

$id = -1;

if( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) )
{
    $id = intval($_REQUEST['id']);
}

if($id >=0)
{
    $imageformat = 'png';
    $map = db_fetch_assoc("select * from weathermap_maps where active='on' and weathermap_maps.id=".$id);

    $imagefile = dirname(__FILE__).'/plugins/weathermap/output/'.'/'.$map[0]['filehash'].".".$imageformat;
    //if($action == 'viewthumb') $imagefile = dirname(__FILE__).'/output/'.$map[0]['filehash'].".thumb.".$imageformat;

    $orig_cwd = getcwd();
    chdir(dirname(__FILE__));

    header('Content-type: image/png');

    // readfile_chunked($imagefile);
    readfile($imagefile);

    dir($orig_cwd);
} else {
    die('No such weathermap');
}
?>
