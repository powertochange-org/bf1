<?php
/*
 * Cru Doctrine
 * Admin - Delete User
 * Campus Crusade for Christ
 */

try {
  //get values
  $id         = isset($_POST['id'])            ? $_POST['id']          : '';

  require_once("../config.inc.php"); 
  require_once("../Database.singleton.php");

  //initialize the database object
  $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
  $db->connect();

  //determine whether this user has a coach
  $sql     = "SELECT COUNT(*) from coach where Student = '".$db->escape($id)."'";
  $result  = $db->query_first($sql);
  if ($result['COUNT(*)'] > 0) {
    //delete coach record
    $sql = "DELETE FROM coach WHERE Student = '".$db->escape($id)."'";
    $db->query($sql);

    //delete user record
    $sql = "DELETE FROM user WHERE Email = '".$db->escape($id)."'";
    $db->query($sql);   
  }
  else {
    //delete user record
    //delete user record
    $sql = "DELETE FROM user WHERE Email = '".$db->escape($id)."'";
    $db->query($sql); 
  }

  $db->close();
} 
catch (PDOException $e) {
    echo $e->getMessage();
    exit();
}
?>