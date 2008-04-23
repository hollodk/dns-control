<?php
/* $Id: index.php 27 2005-02-28 21:37:51Z justin $ */
include("../includes/globals.php");
$session->isAdmin();
if(empty($_REQUEST['act']) || !isset($_REQUEST['q'])) {
    $tpl->display('admin/search_form.htm');
}
else {
    $search_term = "";
    if($_REQUEST['contains'] == "1") {
        $search_term = $db->quote('%'.$_REQUEST['q']. '%');
    }
    else {
        $search_term = $db->quote($_REQUEST['q']. '%');
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
    $perpage = 25;
    $count = $db->getOne("SELECT count(domainid) FROM domains WHERE domain like $search_term");
    $paging = generate_pagination($_SERVER['PHP_SELF']."?act=result&q=".$_REQUEST['q']."&contains=".$_REQUEST['contains'], $count, $perpage, $start);
    $results = $db->getAll("SELECT domainid, domain, password FROM domains WHERE domain like $search_term LIMIT $start, $perpage");
    $tpl->assign("domains", $results);
    $tpl->assign("paging", $paging);
    $tpl->display('admin/search_results.htm');
}
?>
