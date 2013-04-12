<?php
/*
 * Cru Doctrine
 * Functions
 * Campus Crusade for Christ
 */

require_once("config.inc.php");
require_once("Database.singleton.php");

function in_array_r($needle, $haystack, $strict = true) {
  foreach ($haystack as $item) {
    if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
      return true;
    }
  }
  return false;
}

function getVisibilityClause($type) {
  $visibility = null;

  if($type == INTERN || $type == VOLUNTEER || $type == PART_TIME_FIELD_STAFF) {
    $visibility = "(p.Visibility = 0 OR p.Visibility = 5)";
  } 
  else if($type == STUDENT || $type == OTHER) {
    $visibility = "(p.Visibility = 0 OR p.Visibility = 4)";
  }
  else {
    $visibility = "(p.Visibility >= 0)";
  }

  return $visibility;
}

function getActiveCoaches($db) {
  $coaches = array();

  $sql     =  "SELECT u.Email, u.FName, u.LName
               FROM  user u
               WHERE u.Type < ".STUDENT."
               AND u.Status = ".ACTIVE."
               ORDER BY u.LName;";

  $coaches = $db->fetch_array($sql);

  return $coaches;
}

function getRegions($db) {
  $regions = array();

  $sql     =  "SELECT r.ID, r.Name
               FROM  region r
               ORDER BY r.Name;";

  $regions = $db->fetch_array($sql);

  return $regions;
}

function getUserTypes($db, $typeClause = REGIONAL_ADMIN) {
  $user_types = array();

  //get user types for selection
  $sql = "SELECT ut.ID, ut.Name
          FROM  user_type ut
          WHERE ut.ID > ".$typeClause."
          ORDER BY ut.ID;";

  $user_types = $db->fetch_array($sql);

  return $user_types;
}

function getUserStatuses($db) {
  $user_statuses = array();

  $sql =  "SELECT ID, Name
           FROM  user_status
           ORDER BY Name;";

  $user_statuses = $db->fetch_array($sql);

  return $user_statuses;
}
?>