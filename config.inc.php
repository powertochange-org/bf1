<?php
//constants

//database server
define('DB_SERVER', "localhost:8889");

//database login name
define('DB_USER', "root");
//database login password
define('DB_PASS', "root");

//database name
define('DB_DATABASE', "crudoctrine");

//miscellaneous
define("COMPLETE", "complete");
define("STARTED", "started");
define("MODULE", "module");
define("SECTION", "section");
define("PAGE", "page");
define("SUPER", 1);
define("REGIONAL_ADMIN", 2);
define("COACH", 3);
define("STUDENT", 4);
define("INTERN", 5);
define("OTHER",6);
define("ACTIVE", "Active");
define("INACTIVE", "Inactive");
define("WELCOME_PAGE", "welcome.php");

//functions

function in_array_r($needle, $haystack, $strict = true) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }
    return false;
}
?>