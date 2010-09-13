<?php
include("../includes/globals.php");
$session->isAdmin();
if(isset($_POST['act']) == 'add') {
    if(!$dns->addZone($_POST['domain'], $_POST['ip'], $_POST['password'], $_POST['ns1'], $_POST['ns2'], $_POST['desc'])) {
        $message = $dns->error;
        header("Location: index.php?failed=1&msg=". urlencode($message));
    }
    else {
        $message = "Domain ".$_POST['domain']." Added";
        header("Location: index.php?msg=". urlencode($message));
    }
}
else {
    $tpl->assign('dns1', $dns1);
    $tpl->assign('dns2', $dns2);
    $tpl->display("admin/addzone.htm");
}
?>
