<?php
/* $Id: index.php 27 2005-02-28 21:37:51Z justin $ */
include("../includes/globals.php");
$session->isAdmin();

if($_GET['act'] == 'runqueue') {
    exit;
    if(!$dns->createLock("DNS")) {
        $message = $dns->error;
        header("Location: index.php?failed=1&msg=" . urlencode($message));
    }
    else {
        $dns->processModqueue();
        $dns->processAddqueue();
        $dns->processDelqueue();
        $message = "Queue has been run\n";
        $dns->delLock("DNS");
        header("Location: index.php?msg=" . urlencode($message));
    }
}

if(empty($_GET['start'])) {
        $start = "0";
}
else {
    if(is_numeric($_GET['start'])) {
        $start = $_GET['start'];
    }
    else {
        $start = "0";
    }
}
if($_GET['failed'] == "1") {
    $color = 'red';
}
else {
    $color = 'green';
}
$perpage = 25;
$count = $db->getOne("SELECT count(domainid) FROM domains WHERE domainid != '1' ORDER BY domain");
$paging = generate_pagination($_SERVER['PHP_SELF']."?", $count, $perpage, $start);
$domains = $db->getAll("SELECT domainid, domain, password FROM domains WHERE domainid != '1' ORDER BY domain  LIMIT $start, $perpage");
$tpl->assign('color', $color);
$tpl->assign('paging', $paging);
$tpl->assign('totaldomains', $count);
$tpl->assign('msg', $_GET['msg']);
$tpl->assign('domains', $domains);
$tpl->display('admin/index.htm');
?>
