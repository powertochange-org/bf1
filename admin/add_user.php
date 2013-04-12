<?php
/*
 * Cru Doctrine
 * Admin - Add User
 * Campus Crusade for Christ
 */

try {
    //get values
    $email      = isset($_POST['email'])         ? $_POST['email']       : '';
    $firstName  = isset($_POST['firstName'])     ? $_POST['firstName']   : '';
    $lastName   = isset($_POST['lastName'])      ? $_POST['lastName']    : '';
    $password   = isset($_POST['password'])      ? $_POST['password']    : '';
    $type       = isset($_POST['type'])          ? $_POST['type']        : '';
    $region     = isset($_POST['region'])        ? $_POST['region']      : '';
    //$location   = isset($_POST['location'])      ? $_POST['location']    : '';
    $regDate    = isset($_POST['regDate'])       ? $_POST['regDate']     : '';
    $coach      = isset($_POST['coach'])         ? $_POST['coach']       : '';

    require_once("../config.inc.php"); 
    require_once("../Database.singleton.php");

    $password = stripslashes($password);
    $firstName = stripslashes($firstName);
    $lastName = stripslashes($lastName);

    //initialize the database object
    $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
    $db->connect();

    //create user
    //prepare query
    $data['Email']      = $email;
    $data['FName']      = $firstName;
    $data['LName']      = $lastName;
    $data['Password']   = $password;
    $data['Type']       = $type;
    $data['Region']     = $region;
    //$data['Loc']        = $location;
    $data['Reg_Date']   = $regDate;
    $data['Status'] = ACTIVE;

    //execute query
    $db->insert("user", $data);

    if ($coach != '') {
      //create coach relationship
      //prepare query
      $data = array();
      $data['Coach'] = $coach;
      $data['Student'] = $email;
      $data['Year'] = date('Y');
      $data['Type'] = COACH;

      //execute query
      $db->insert("coach", $data);
    }

    $db->close();
    echo $email;
  } 
  catch (PDOException $e) {
      echo $e->getMessage();
      exit();
  }
?>