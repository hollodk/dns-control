<?php
/* $Id: index.php 27 2005-02-28 21:37:51Z justin $ */
include("includes/globals.php");
$session->isAuth();
$domain = $_SESSION['valid_user'];
$a = $dns->getRecords($domain, "a");
$cname = $dns->getRecords($domain, "cname");
$mx = $dns->getRecords($domain, "mx");
if($_GET['failed'] == "1") {
    $color = 'red';
}
else {
    $color = 'green';
}
$tpl->assign('domain', $domain);
$tpl->assign('color', $color);
$tpl->assign('msg', $_GET['msg']);
$tpl->assign('ip', $dns->zoneIp($domain));
$tpl->assign('records_a', $a);
$tpl->assign('records_cname', $cname);
$tpl->assign('records_mx', $mx);
$tpl->display("index.htm");

?>
