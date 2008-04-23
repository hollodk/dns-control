<?php
/* $Id: logout.php 32 2005-03-03 19:17:23Z justin $ */
include("includes/globals.php");
$session->isAuth();
$session->destroy();
?>
