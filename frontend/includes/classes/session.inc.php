<?php
/* $Id: session.inc.php 27 2005-02-20 15:32:52Z root $ */

class Session {
 
    var $db = "";   
    var $tpl = "";   
    var $username = "";
    var $userid = "";
    var $password = "";
    var $var_name = "";

    function Session(&$db, &$tpl) {
        session_start();
        $this->db = $db;
        $this->tpl = $tpl;
    }

    function auth($username, $password) {
        $this->username = trim($username);
        $this->password = trim($password);
   
        if(!empty($_SESSION['valid_user'])) {
            return true;
        } 
        else {
            $sql = sprintf("SELECT domainid FROM domains WHERE domain = %s AND password = %s", 
                           $this->db->quote($this->username), $this->db->quote($this->password));
            $this->userid = $this->db->getOne($sql);
            if(!empty($this->userid)) {
                //auth
                $_SESSION['valid_user'] = $this->username;
                $_SESSION['domainid'] = $this->userid;
                if(!empty($_SESSION['redirect'])) {
                    header("Location: ".$_SESSION['redirect']); 
                }
                else {
                    header("Location: index.php");
                }
                return true;
            }
            else {
                //no auth
                header("Location: login.php?failed=1");
                return false;
            }
        }
    }

    function authAdmin($username, $password) {
        $this->username = trim($username);
        $this->password = trim($password);
  
        if(!empty($_SESSION['valid_admin'])) {
            return true;
        } 
        else {
            $sql = sprintf("SELECT userid FROM admins WHERE username = %s AND password = md5(%s)", 
                           $this->db->quote($this->username), $this->db->quote($this->password));
            $this->userid = $this->db->getOne($sql);
            if(!empty($this->userid)) {
                //auth
                $_SESSION['valid_admin'] = $this->username;
                $_SESSION['userid'] = $this->userid;
                if(!empty($_SESSION['redirect'])) {
                    header("Location: ".$_SESSION['redirect']); 
                }
                else {
                    header("Location: index.php");
                }
                return true;
            }
            else {
                //no auth
                header("Location: login.php?failed=1");
                return false;
            }
        }
    }

    function numUsers() {
        return $this->db->getOne("SELECT count(*) FROM sessions");
    }

    function isAuth() {
        if(!empty($_SESSION['valid_user'])) {
            return true;
        } 
        else {
            $this->login();
        }
    }

    function isAdmin() {
        if(!empty($_SESSION['valid_admin'])) {
            return true;
        } 
        else {
            $this->adminlogin();
        }
    }

    function adminlogin() {
        $this->register("redirect",  $_SERVER['PHP_SELF']);
        if(eregi("login.php", $_SERVER['PHP_SELF'])) {
            return true;
        }
        else {
            header("Location: login.php");
        }
    }


    function login() {
        $this->register("redirect",  $_SERVER['PHP_SELF']);
        if(eregi("login.php", $_SERVER['PHP_SELF'])) {
            return true;
        }
        else {
            header("Location: login.php");
            exit;
        }
    }

    function register($name, $value) {
        $_SESSION[$name] = $value;
        return true;
    }

    function unregister($name) {
        $_SESSION[$name] = "";
        unset($_SESSION[$name]);
    }

    function destroy() {
        $_SESSION = array();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-42000, '/');
        } 
        $destroy = session_destroy();
        if($destroy) {
            header("Location: login.php?logout=1");
            return true;
        }
        else {
            print "ERROR!";
            return false;
        }
    }
}
?>
