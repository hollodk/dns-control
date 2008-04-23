<?php
/* $Id: viewzone.php 15 2005-02-28 16:04:31Z justin $ */
include("includes/globals.php");
$session->isAuth();

$zone = $dns->writeZone($_SESSION['valid_user'], true);
print "<pre>";
print $zone;
print "</pre>";

?>
