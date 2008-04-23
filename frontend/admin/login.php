<?php
/* $Id: login.php 15 2005-02-28 16:04:31Z justin $ */
include("../includes/globals.php");
if($session->isAdmin()) {
    header("Location: index.php");
    exit;
}
if(!empty($_POST['username']) && !empty($_POST['password'])) {
    $session->authAdmin($_POST['username'], $_POST['password']);
}
else{
    if($_GET['failed']) {
        print "<center><font color=\"red\"><b>The information you entered is incorrect</b></font>";
    }
    if($_GET['msg']) {
        print "<center><b><font color=\"red\">$msg</b></font>";
    }
    $tpl->display("admin/login.htm");
}

?>
