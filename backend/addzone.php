#!/usr/bin/php -q
<?php
require(dirname(__FILE__).'/includes/globals.php');

if($argc == 4) {
    if(!$dns->addZone($argv[1], $argv[2], $argv[3])) {
        print $dns->error . "\n";
    }
    else {
        print "Added $argv[1]:$argv[2] password:$argv[3]\n";
    }
}
else {
    print "Usage: $argv[0] <zone> <ip> <password>\n";
}

?>
