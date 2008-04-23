<?
/* $Id: dns.inc.php 66 2005-02-26 20:01:51Z justin $ */

class DNS {

    var $db      = ""; //sql object
    var $path    = ""; //path to named files
    var $entries = ""; //global named.conf options
    var $error   = ""; //error message
    var $tpl     = array(); //template array

    function DNS(&$db, $path, $conf_file, $tpl_path) {
        $this->db = $db;
        $this->path = $path;
        $this->entries = "options {\n    directory \"$path\";\n};\n\n";
        $this->conf_file = $conf_file;
        $this->tpl_path = $tpl_path;
        $this->loadTpl();
    }

    function addZone($domain, $address, $password) {
        if($this->isDomain($domain) && $this->isIP($address)) {
            if($this->isZone($domain)) {
                $this->raiseError("Domain exists or is in the queue to be created\n");
                return false;
            }
            $sql = sprintf("INSERT INTO add_queue (domain, address, password) VALUES (%s, %s, %s)",
                            $this->db->quote($domain), $this->db->quote($address),  $this->db->quote($password));
            $this->db->Execute($sql);
            return true;
        }
        else {
            $this->raiseError("Invalid IP\n");
            return false;
        }
    }

    function isLock($process) {
        $sprocess = $this->db->quote($process);
        $now = time();
        $result = $this->db->getRow("SELECT lockid, timestamp FROM locks WHERE process = $sprocess AND timestamp > $now");
        if($result['lockid']) {
            return true;
        }
        else {
            $this->delLock($process);
            return false;
        }
    }
        
    function createLock($process) {
        if($this->isLock($process)) {
            $this->raiseError("Process Already running\n");
            return false;
        }
        else {
            $process = $this->db->quote($process);
            $timestamp = $this->db->quote(time() + 600);
            $this->db->Execute("INSERT INTO locks (process, timestamp) VALUES ($process, $timestamp)");
            return true;
        }
    }

    function delLock($process) {
        $process = $this->db->quote($process);
        $this->db->Execute("DELETE FROM locks WHERE process = $process");
    }


    function reloadZone($domain = "") {
        $domain = escapeshellarg($domain);
        exec("/usr/sbin/rndc reload $domain", $exec);
    }

    //public
    function removeZone($domain) {
        $domainid = $this->db->quote($this->domainId($domain));
        $this->db->Execute("INSERT INTO del_queue(domainid) VALUES ($domainid)");
        return true;
    }

    //private 
    function delZone($domain) {
        if($this->isDomain($domain)) {
            $domainid = $this->db->quote($this->domainId($domain));
            $this->db->Execute("DELETE FROM records_a WHERE domainid = $domainid");
            $this->db->Execute("DELETE FROM records_cname WHERE domainid = $domainid");
            $this->db->Execute("DELETE FROM records_mx WHERE domainid = $domainid");
            $this->db->Execute("DELETE FROM domains WHERE domainid = $domainid");
            $letter = substr($domain, 0, 1);
            $zonefile = $this->path . "/$letter/" . $domain;
            if(file_exists($zonefile)) {
                unlink($zonefile);
            }
            $this->writeConf();
            $this->reloadZone();
        }
    }

    function writeConf() {
        $query = $this->db->Execute("SELECT distinct(domain) FROM domains ORDER BY domain");
	$this->entries = $this->tpl['named.conf'];
        while($row = $query->fetchRow()) {
            $domain = $row['domain'];
            $letter = substr($domain, 0, 1);
            $this->entries .= "zone \"$domain\" {\n    type master;\n    file \"$letter/$domain\";\n};\n\n";
            $domains .= " $domain ";
        }

        $f = fopen($this->conf_file, "w");
        fwrite($f, $this->entries);
        fclose($f);

    }

    function zoneIp($domain) {
        $domainid = $this->domainId($domain);
        $ip = $this->db->getOne("SELECT address FROM domains WHERE domainid = '$domainid'");
        return $ip;
    }

    function rebuildZones() {
        $zones = $this->db->Execute("SELECT domain FROM domains ORDER BY domain");
        while($row = $zones->fetchRow()) {
            print "Rebuilding $row[domain]......";
            flush();
            $this->writeZone($row['domain']);
            print "done\n";
            flush();
        }
        $this->reloadZone();
    }

    function writeZone($domain, $return = false) {
        $letter = substr($domain, 0, 1);
        if(!file_exists($this->path . "/" . $letter) &&!$return) {
            mkdir($this->path . "/" . $letter, 0755);
        }
        $domainid = $this->domainId($domain);
        //main zone
        $ip = $this->zoneIp($domain);
        $find    = array("{dns_server_1}", "{dns_server_2}", "{dns_hostmaster}", "{serial}");
        $replace = array($this->dns1, $this->dns2, $this->dnshostmaster, time());
        $zone = str_replace($find, $replace, $this->tpl['zone']);
        
        //mx records
        $query = $this->db->Execute("SELECT address, priority FROM records_mx WHERE domainid = '$domainid'");
        if($query->numRows() > 0) {
            $find = array("{priority}", "{address}");
            while($row = $query->fetchRow()) {
                $replace = array($row['priority'], $row['address']);
                $records_mx .= str_replace($find, $replace, $this->tpl['record_mx']);
            }
        }
        //a records
        $find = array("{name}", "{address}");
        $replace = array("    ", $ip);
        $records_a = str_replace($find, $replace, $this->tpl['record_a']);
        $query = $this->db->Execute("SELECT name, address FROM records_a WHERE domainid = '$domainid'");
        if($query->numRows() > 0) {
            while($row = $query->fetchRow()) {
                $replace = array($row['name'], $row['address']);
                $records_a .= str_replace($find, $replace, $this->tpl['record_a']);
            }
        }
        //cname records
        $query = $this->db->Execute("SELECT name, address FROM records_cname WHERE domainid = '$domainid'");
        if($query->numRows() > 0) {
            $find = array("{name}", "{address}");
            while($row = $query->fetchRow()) {
                $replace = array($row['name'], $row['address']);
                $records_cname.= str_replace($find, $replace, $this->tpl['record_cname']);
            }
        }

        $zone .= $records_mx . $records_a . $records_cname;

        if($return) {
            return $zone;
        }
        else {
            $f = fopen($this->path . "/$letter/" . $domain, "w");
            fwrite($f, $zone);
            fclose($f);
        }
    }

    function processAddqueue() {
        $query = $this->db->Execute("SELECT queueid, domain, address, password FROM add_queue WHERE completed = '0' ORDER BY domain");
        if($query->numRows() > 0 ) {
            while($row = $query->fetchRow()) {
                $mx = $this->db->quote('mail.' . $row['domain'] . '.');
                $queueid = $this->db->quote($row['queueid']);
                $domain = $this->db->quote($row['domain']);
                $address = $this->db->quote($row['address']);
                $password = $this->db->quote($row['password']);
                $this->db->Execute("INSERT INTO domains (domain, address, password) VALUES ($domain, $address, $password)");
                $domainid = $this->db->quote($this->domainId($row['domain']));
                $this->db->Execute("INSERT INTO records_a (domainid, name, address) VALUES ($domainid, 'mail', $address)");
                $this->db->Execute("INSERT INTO records_a (domainid, name, address) VALUES ($domainid, '*', $address)");
                $this->db->Execute("INSERT INTO records_a (domainid, name, address) VALUES ($domainid, 'www', $address)");
                $this->db->Execute("INSERT INTO records_mx (domainid, priority, address) VALUES ($domainid, '10', $mx)");
                $this->writeZone($row['domain']);
                $this->db->Execute("UPDATE add_queue SET completed = '1' WHERE queueid = $queueid");
                $this->writeConf();
                $this->reloadZone();
            }
            return true;
        }
        else {
            $this->raiseError("Nothing to process\n");
            return false;
        }
            
    }

    function processDelqueue() {
        $query = $this->db->Execute("SELECT queueid, domainid FROM del_queue WHERE completed = '0'");
        if($query->numRows() > 0 ) {
            while($row = $query->fetchRow()) {
                $queueid = $this->db->quote($row['queueid']);
                $domainid = $this->db->quote($row['domainid']);
                $domain = $this->db->getOne("SELECT domain FROM domains WHERE domainid = $domainid");
                $this->delZone($domain);
                $this->db->Execute("UPDATE del_queue SET completed = '1' WHERE queueid = $queueid");
                $this->writeConf();
            }
            $this->reloadZone();
            return true;
        }
        else {
            $this->raiseError("Nothing to process\n");
            return false;
        }
    }

    function processModqueue() {
        $query = $this->db->Execute("SELECT queueid, domainid FROM mod_queue WHERE completed = '0'");
        if($query->numRows() > 0 ) {
            while($row = $query->fetchRow()) {
                $queueid = $this->db->quote($row['queueid']);
                $domainid = $this->db->quote($row['domainid']);
                $domain = $this->db->getOne("SELECT domain FROM domains WHERE domainid = $domainid");
                $this->writeZone($domain);
                $this->db->Execute("UPDATE mod_queue SET completed = '1' WHERE queueid = $queueid");
                $this->reloadZone($domain);
            }
            $this->writeConf();
            return true;
        }
        else {
            $this->raiseError("Nothing to process\n");
            return false;
        }
    }

    function loadTpl() {
        $this->tpl['zone'] = file_get_contents($this->tpl_path . "/zone.tpl");
        $this->tpl['record_a'] = file_get_contents($this->tpl_path . "/record_a.tpl");
        $this->tpl['record_cname'] = file_get_contents($this->tpl_path . "/record_cname.tpl");
        $this->tpl['record_mx'] = file_get_contents($this->tpl_path . "/record_mx.tpl");
        $this->tpl['named.conf'] = file_get_contents($this->tpl_path . "/named.conf.tpl");
    }
    
    function domainId($domain) {
        $sql = sprintf("SELECT domainid FROM domains WHERE domain = %s", $this->db->quote($domain));
        $domainid = $this->db->getOne($sql);
        return $domainid;
    }

    function zoneInfo($domainid) {
        $domainid = $this->db->quote($domainid);
        $info = $this->db->getRow("SELECT domain, password, address FROM domains WHERE domainid = $domainid");
        return $info;
    }

    function updateZonePass($domainid, $password) {
        $domainid = $this->db->quote($domainid);
        $password = $this->db->quote($password);
        $this->db->Execute("UPDATE domains SET password = $password WHERE domainid = $domainid");
        return true;
    }

    function updateIp($domainid, $address) {
        if(!$this->isIp($address)) {
            $this->raiseError("Invalid IP\n");
            return false;
        }
        else {
            $domainid = $this->db->quote($domainid);
            $address = $this->db->quote($address);
            $this->db->Execute("UPDATE domains SET address = $address WHERE domainid = $domainid");
            $this->db->Execute("INSERT INTO mod_queue (domainid) VALUES ($domainid)");
            return true;
        }
    }

    function recordInfo($domain, $recordid, $type) {
        $domainid = $this->domainId($domain);
        $recordid = $this->db->quote($recordid);
        switch(strtolower($type)) {
            case 'a':
                $sql = "SELECT name, address FROM records_a WHERE domainid = $domainid AND recordid=$recordid";
            break;
            case 'cname':
                $sql = "SELECT name, address FROM records_cname WHERE domainid = $domainid AND recordid=$recordid";
            break;
            case 'mx':
                $sql = "SELECT priority AS name, address FROM records_mx WHERE domainid = $domainid AND recordid=$recordid";
            break;
            default:
                $sql = false;
                $this->raiseError("Invalid Entry\n");
                return false;
            break;
        }
        if($sql) {
            $result = $this->db->getRow($sql);
            return $result;
        }
        else {
            $this->raiseError("Record does not exist!\n");
            return false;
        }
    }

    function delRecord($domain, $recordid, $type) {
        $domainid = $this->domainId($domain);
        $recordid = $this->db->quote($recordid);
        switch(strtolower($type)) {
            case 'a':
                $sql = "DELETE FROM records_a WHERE recordid = $recordid AND domainid = $domainid";
            break;
            case 'cname':
                $sql = "DELETE FROM records_cname WHERE recordid = $recordid AND domainid = $domainid";
            break;
            case 'mx':
                $sql = "DELETE FROM records_mx WHERE recordid = $recordid AND domainid = $domainid";
            break;
            default:
                $sql = false;
                $this->raiseError("Invalid Entry\n");
                return false;
            break;
        }
        if($sql) {
            $this->db->Execute($sql);
            $this->db->Execute("INSERT INTO mod_queue (domainid) VALUES ($domainid)");
            return true;
        }
    }

    function modRecord($domain, $recordid, $address, $type) {
        $domainid = $this->domainId($domain);
        $recordid = $this->db->quote($recordid);
        if(empty($address['address'])) {
            $this->raiseError("Invalid entry\n");
            return false;
        }
        $maddress = $this->db->quote($address['address']);
        switch(strtolower($type)) {
            case 'a':
                if(!$this->isIP($address['address'])) {
                    $this->raiseError("Invalid IP");
                    return false;
                }
                $sql = "UPDATE records_a set address = $maddress WHERE recordid = $recordid AND domainid = $domainid";
            break;
            case 'cname':
                $sql = "UPDATE records_cname set address = $maddress WHERE recordid = $recordid AND domainid = $domainid";
            break;
            case 'mx':
                $ip = $address['address'];
                $name = $address['name'];
                if(!preg_match("/.*\.$/", $ip)) {
                    $this->raiseError("MX records must end in a period\n");
                    return false;
                }
                $name = $this->db->quote($name);
                $ip = $this->db->quote($ip);
                $sql = "UPDATE records_mx set priority = $name, address = $ip WHERE recordid = $recordid AND domainid = $domainid";
            break;
            default:
                $sql = false;
                $this->raiseError("Invalid Entry\n");
                return false;
            break;
        }
        if($sql) {
            $this->db->Execute($sql);
            $this->db->Execute("INSERT INTO mod_queue (domainid) VALUES ($domainid)");
            return true;
        }
    }


    function addRecord($type, $domain, $name, $address) {
        $domainid = $this->domainId($domain);
        if($domainid) {
            $safename = $this->db->quote($name);
            $safeaddress = $this->db->quote($address);
            switch(strtolower($type)) {
                case 'a':
                    if(!$this->isIP($address)) {
                        $this->raiseError("Invalid IP");
                        return false;
                    }
                    $query = $this->db->Execute("SELECT recordid FROM records_a WHERE domainid = '$domainid' AND name = $safename LIMIT 1");
                    if($query->numRows() > 0) {
                        $this->raiseError("Record Exists\n");
                        return false;
                    }
                    $query = $this->db->Execute("SELECT recordid FROM records_cname WHERE domainid = '$domainid' AND name = $safename LIMIT 1");
                    if($query->numRows() > 0) {
                        $this->raiseError("Conflict Detected! CNAME already exists for $safename\n");
                        return false;
                    }
                    $this->db->Execute("INSERT INTO records_a (domainid, name, address) VALUES ('$domainid', $safename, $safeaddress)");
                break;
                case 'cname':
                    $query = $this->db->Execute("SELECT recordid FROM records_cname WHERE domainid = '$domainid' AND name = $safename LIMIT 1");
                    if($query->numRows() > 0) {
                        $this->raiseError("Record Exists\n");
                        return false;
                    }
                    $query = $this->db->Execute("SELECT recordid FROM records_a WHERE domainid = '$domainid' AND name = $safename LIMIT 1");
                    if($query->numRows() > 0) {
                        $this->raiseError("Conflict Detected! An A record already exists for $safename\n");
                        return false;
                    }
                    $this->db->Execute("INSERT INTO records_cname (domainid, name, address) VALUES ('$domainid', $safename, $safeaddress)");
                break;
                case 'mx':
                    if(!preg_match("/.*\.$/", $address)) {
                        $this->raiseError("MX records must end in a period\n");
                        return false;
                    }
                    $this->db->Execute("INSERT INTO records_mx (domainid, priority, address) VALUES ('$domainid', $safename, $safeaddress)");
                break;
                default:
                    $this->raiseError("Invalid Record Type\n");
                    return false;
                break;
            }
            $q = $this->db->Execute("SELECT domainid FROM mod_queue WHERE domainid = '$domainid' AND completed = '0'");
            if($q->numRows() > 0) {
                return true;
            }
            else {
                $this->db->Execute("INSERT INTO mod_queue (domainid) VALUES ('$domainid')");
                return true;
            }
        }
        else {
            $this->raiseError("Invalid Domain\n");
            return false;
        }

    }

    function getRecords($domain, $type) {
        $domainid = $this->db->quote($this->domainid($domain));
        switch(strtolower($type)) {
            case 'a':
                $records = $this->db->getAll("SELECT recordid, name, address FROM records_a WHERE domainid = $domainid ORDER BY name");
            break;
            case 'cname':
                $records = $this->db->getAll("SELECT recordid, name, address FROM records_cname WHERE domainid = $domainid ORDER BY name");
            break;
            case 'mx':
                $records = $this->db->getAll("SELECT recordid, priority, address FROM records_mx WHERE domainid = $domainid ORDER BY priority");
            break;
            default:
                return false;
            break;
        }
        return $records;
    }


    function isZone($domain) {
        $sql = sprintf("SELECT count(domainid) FROM domains WHERE domain = %s", $this->db->quote($domain));
        $result = $this->db->getOne($sql);
        if($result > 0) {
            return true;
        }
        else {
            $sql = sprintf("SELECT count(queueid) FROM add_queue WHERE domain = %s AND completed = '0'", $this->db->quote($domain));
            $result = $this->db->getOne($sql);
            if($result > 0) {
                return true;
            }
            return false;
        }
    }

    function isDomain($domain) {
        if(preg_match("/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/", $domain)) {
            return true;
        }
        else {
            return false;
        }
    }

    function isIP($ip) {
        if(preg_match("/\b(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/", $ip)) {
            return true;
        }
        else {
            return false;
        }
    }

    function raiseError($error) {
        $this->error =  $error;
    }
}

?>
