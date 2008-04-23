<?php
/* $Id: logout.php 15 2005-02-28 16:04:31Z justin $ */
include("../includes/globals.php");
$session->isAdmin();
$session->destroy();
?>
