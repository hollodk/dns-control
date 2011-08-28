<?php
/* $Id: index.php 27 2005-02-28 21:37:51Z justin $ */
include("../includes/globals.php");
$session->isAdmin();

$domainid = $db->quote($_REQUEST['domainid']);
$domain = $db->getOne("SELECT domain FROM domains WHERE domainid = $domainid");
$password = $db->getOne("SELECT password FROM domains WHERE domainid = $domainid");

$_SESSION['valid_user'] = null;
$_SESSION['domainid'] = null;

$session->auth($domain,$password);
header('Location: ../index.php');
?>
