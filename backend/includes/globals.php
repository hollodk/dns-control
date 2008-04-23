<?php
/* $Id: globals.php 61 2005-02-26 19:43:12Z justin $ */
require("/var/www/html/includes/config.inc.php");


$tpl_path = $base_path . "/includes/templates";
require($base_path . "/includes/functions.inc.php");
require($base_path . "/includes/adodb/adodb.inc.php");
require($base_path . "/includes/adodb/adodb-errorhandler.inc.php");
require($base_path . "/includes/adodb/session/adodb-session.php");

$db = NewADOConnection('mysql');
$db->Connect($db_host, $db_user, $db_pass, $db_name);

require($base_path . "/includes/classes/dns.inc.php");

$dns = new DNS($db, $zone_path, $conf_file, $tpl_path);
$dns->dns1 = $dns1;
$dns->dns2 = $dns2;
$dns->dnshostmaster = $dnshostmaster;

?>
