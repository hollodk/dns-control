#!/usr/bin/php -q
<?php
require(dirname(__FILE__).'/includes/globals.php');

if($offline_mode) {
    print "Offline Mode\n";
    exit(0);
}

if(!$dns->createLock("DNS")) {
    print $dns->error;
    exit;
}

//Mod
if(!$dns->processModqueue()) {
	print $dns->error;
}
else {
	print "Mods completed\n";
}

//Mod
if(!$dns->processAddqueue()) {
	print $dns->error;
}
else {
	print "Adds completed\n";
}

//del
if(!$dns->processDelqueue()) {
	print $dns->error;
}
else {
	print "Delete completed\n";
}
$dns->delLock("DNS");

?>

