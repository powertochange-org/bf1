<?php
/*
 * Cru Doctrine
 * Functions
 * Campus Crusade for Christ
 */

require_once("config.inc.php"); 

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
?>