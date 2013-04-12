<?php
/*
 * Cru Doctrine
 * Admin - Archive Users
 * Campus Crusade for Christ
 */

try {
  require_once("../config.inc.php"); 
  require_once("../Database.singleton.php");

  //initialize the database object
  $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
  $db->connect();

  $message = null;

  //archive users from previous year
  //prepare query
  $data = null;
  $data['Status'] = INACTIVE;

  //execute query
  $db->update("user", $data, "YEAR(Reg_Date) < YEAR(CURDATE()) AND TYPE > ".REGIONAL_ADMIN);
  //$db->update("user", $data, "Reg_Date < '".$db->escape('2012')."' AND TYPE > ".REGIONAL_ADMIN);

  $message = $db->affected_rows. " users successfully archived!";

  $db->close();
}
catch (PDOException $e) {
  echo $e->getMessage();
  exit();
}
?>
<form id="formArchiveUsersResponse" action="" method="post">
  <fieldset id="feedback">
    <div id="message"><?php echo $message; ?></div>
  </fieldset>
</form>