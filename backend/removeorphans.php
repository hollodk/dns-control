#!/usr/bin/php -q
<?php
require(dirname(__FILE__).'/includes/globals.php');

if($offline_mode) {
    print "Offline Mode\n";
    exit(0);
}
if(!$dns->createLock("ORPHAN")) {
    print $dns->error;
    exit;
}
function alter_record(&$domain, $key) {
    global $orphans, $dns, $db, $zone_path;
    if(empty($zone_path)) {
        print "Zone path not set!\n";
        $dns->delLock("ORPHAN");
        exit(-1);
    }
    $domain = preg_replace("/.*\//", "", $domain);
    $domain = trim($domain);
    if($dns->isDomain($domain)) {
        $d = $db->quote($domain);
        $q = $db->getOne("SELECT count(domainid) FROM domains WHERE domain = $d");
        if($q  == "0") {
            $orphans[] = $domain;
            print "Removing: $domain .... ";
            $letter = substr($domain, 0, 1);
            unlink("$zone_path/$letter/$domain");
            print "Done!\n";
        }
    }
}

$zones = exec("find $zone_path -type f", $domains);
array_walk($domains, 'alter_record');

$dns->delLock("ORPHAN");

?>

