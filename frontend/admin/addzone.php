<?php
/* $Id: addrecord.php 29 2005-02-28 21:47:53Z justin $ */
include("../includes/globals.php");
$session->isAdmin();
if($_POST['act'] == 'add') {
    if(!$dns->addZone($_POST['domain'], $_POST['ip'], $_POST['password'])) {
        $message = $dns->error;
         header("Location: index.php?failed=1&msg=". urlencode($message));
    }
    else {
        $message = "Zone Added";
        header("Location: index.php?msg=". urlencode($message));
    }
}
else {
    $tpl->display("admin/addzone.htm");
}	

?>
