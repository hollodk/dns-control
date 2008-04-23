<?php
/* $Id: globals.php 66 2005-06-20 20:32:27Z justin $ */
require("config.inc.php");


$tpl_path = $base_path . "/includes/templates";
$ADODB_SESSION_DRIVER  = 'mysql';
$ADODB_SESSION_CONNECT =& $db_host;
$ADODB_SESSION_USER    =& $db_user;
$ADODB_SESSION_PWD     =& $db_pass ;
$ADODB_SESSION_DB      =& $db_name;

require($base_path . "/includes/functions.inc.php");
require($base_path . "/includes/adodb/adodb.inc.php");
require($base_path . "/includes/adodb/adodb-errorhandler.inc.php");
require($base_path . "/includes/adodb/session/adodb-session.php");
require($base_path . "/includes/smarty/Smarty.class.php");

$tpl = new Smarty;
$tpl->template_dir = $base_path . '/templates/';
$tpl->compile_dir = $base_path . '/compiled_templates/';
$tpl->cache_dir = $base_path . '/include/cache/';
$tpl->left_delimiter = "<{";
$tpl->right_delimiter = "}>";
$tpl->assign('base_url', $base_url);
$tpl->assign('version', '1.0.1');
$tpl->assign('app_name', 'RocketControl DNS');

$db = NewADOConnection('mysql');
$db->Connect($db_host, $db_user, $db_pass, $db_name);

require($base_path . "/includes/classes/session.inc.php");
require($base_path . "/includes/classes/dns.inc.php");

$session = new Session($db, $tpl);
$dns = new DNS($db, $zone_path, $conf_file, $tpl_path);
$dns->dns1 = $dns1;
$dns->dns2 = $dns2;
$dns->dnshostmaster = $dnshostmaster;
?>
