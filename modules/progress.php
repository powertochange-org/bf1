<?php
/*
 * Cru Doctrine
 * Modules - Progress
 * Campus Crusade for Christ
 */

//get values
session_start();
$email      = isset($_SESSION['email'])                                       ? $_SESSION['email']   : '';
$type       = isset($_SESSION['type'])                                        ? $_SESSION['type']    : '';
$submit     = isset($_POST['submit'])                                         ? true                 : false;
$assessment = (isset($_POST['assessment']) && $_POST['assessment'] == 'true') ? true                 : false;
$isSection  = (isset($_POST['isSection'])  && $_POST['isSection']  == 'true') ? true                 : false;
$answer     = isset($_POST['answer'])                                         ? $_POST['answer']     : 0;
$pageId     = isset($_POST['pageId'])                                         ? $_POST['pageId']     : 0;
$sectionId  = isset($_POST['sectionId'])                                      ? $_POST['sectionId']  : 0;
$moduleId   = isset($_POST['moduleId'])                                       ? $_POST['moduleId']   : 0;
$pageOrd    = isset($_POST['pageOrd'])                                        ? $_POST['pageOrd']    : 0;
$sectionOrd = isset($_POST['sectionOrd'])                                     ? $_POST['sectionOrd'] : 0;
$moduleOrd  = isset($_POST['moduleOrd'])                                      ? $_POST['moduleOrd']  : 0;
$cur_date   = date( 'Y-m-d' );
$errors     = isset($_POST['errors'])                                         ? $_POST['errors']     : '';

require_once("../config.inc.php"); 
require_once("../Database.singleton.php");
require_once("../function.inc.php"); 

//initialize the database object
$db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
$db->connect();

//assessment pages
if($assessment) {
  //if this is a section, then fetch the first page
  if ($isSection) {
    $sql = "SELECT p.ID AS Page
            FROM page p
            WHERE p.Ord = 0 
            AND p.SectionId = ".(int)$answer;

    $page = $db->query_first($sql);
    if($db->affected_rows > 0) {
      $answer = $page['Page'];
    }
  }
  //verify that the answer page is incomplete
  $sql = "SELECT Status 
          FROM progress 
          WHERE ID = ".(int)$answer."
          AND Email = '".$db->escape($email)."'
          AND TYPE = '".$db->escape(PAGE)."'";
  //execute query 
  $newPageStatus = $db->query_first($sql);
  if($db->affected_rows > 0) {
    //return the incorrect answer page
    header('Content-Type: application/xml; charset=ISO-8859-1');
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>".PHP_EOL;
    echo '<next>'.PHP_EOL;
    echo    '<type>'.PAGE.'</type>'.PHP_EOL;
    echo    '<id>'.$answer.'</id>'.PHP_EOL;
    echo '</next>'.PHP_EOL;
  }
  else {
    $submit = true;
  }
}

//determine the next page
if($submit) {
  //mark current page complete
  $data = array();
  $data['Status'] = COMPLETE;
  $data['Update'] = $cur_date;
    
  //execute query
  $db->update("progress", $data, "Email = '".$db->escape($email)."' AND ID = ".(int)$pageId." AND TYPE = '".$db->escape(PAGE)."'");

  //1. first attempt to get the next page
  //   select all pages that have an Ord > than the existing Ord, in the current section and module, and that should be visible to the current user
  $next = null;
  $visibility = getVisibilityClause($type);

  $sql = "SELECT p.ID AS Page, p.Visibility AS Visibility, s.ID AS Section, m.ID AS Module
          FROM page p
          INNER JOIN section s ON p.SectionId = s.ID
          INNER JOIN module m ON s.ModuleId = m.ID
          WHERE (p.Ord > ".$pageOrd." AND s.ID = ".$sectionId." AND m.ID = ".$moduleId.") AND
                ".$visibility."
                ORDER BY m.Ord, s.Ord, p.Ord;";

  //execute query 
  $next = $db->query_first($sql);

  if($db->affected_rows == 0) {
    //2. next, attempt to get a page from the next section
    //   select all pages from all sections in the current module where the section Ord is > than the current section Ord and that should be visible to the current user
    $sql = "SELECT p.ID AS Page, p.Visibility AS Visibility, s.ID AS Section, m.ID AS Module
            FROM page p
            INNER JOIN section s ON p.SectionId = s.ID
            INNER JOIN module m ON s.ModuleId = m.ID
            WHERE (s.Ord > ".$sectionOrd." AND m.ID = ".$moduleId.") AND
                  ".$visibility."
                  ORDER BY m.Ord, s.Ord, p.Ord;";
    
    $next = $db->query_first($sql);
  }

  if($db->affected_rows == 0) {
    //3. lastly, attempt to get a page from the first section in the next module
    //   select all pages from the first section of the next module
    $sql = "SELECT p.ID AS Page, p.Visibility AS Visibility, s.ID AS Section, m.ID AS Module
            FROM page p
            INNER JOIN section s ON p.SectionId = s.ID
            INNER JOIN module m ON s.ModuleId = m.ID
            WHERE (s.Ord = 0 AND m.Ord = ".($moduleOrd + 1).") AND
                  ".$visibility."
                  ORDER BY m.Ord, s.Ord, p.Ord;";
    
    $next = $db->query_first($sql);
  }

  if($db->affected_rows > 0) {
    $type   = $next['Module'] == $moduleId ? PAGE : MODULE;
    $id     = $type == PAGE ? $next['Page'] : $next['Module'];

    //verify that next page is incomplete
    $sql = "SELECT Status 
            FROM progress 
            WHERE ID = ".(int)$next['Page']."
            AND Email = '".$db->escape($email)."'
            AND TYPE = '".$db->escape(PAGE)."'";
    //execute query 
    $newPageStatus = $db->query_first($sql);
    if($db->affected_rows == 0) {
      //mark next page started
      $data = array();
      $data['Email']  = $email;
      $data['ID'] = (int)$next['Page'];
      $data['Type'] = PAGE;
      $data['Status'] = STARTED;
      $data['Update'] = $cur_date;

      //execute query
      $db->insert("progress", $data);
    }
    if ($type == MODULE) {
      //verify that next module is incomplete
      $sql = "SELECT Status 
              FROM progress 
              WHERE ID = ".$next['Module']."
              AND Email = '".$db->escape($email)."' 
              AND TYPE = '".$db->escape(MODULE)."'";
      //execute query 
      $newModuleStatus = $db->query_first($sql);
      if($db->affected_rows == 0) {
        //mark next module started
        $data = array();
        $data['Email']  = $email;
        $data['ID'] = $next['Module'];
        $data['Type'] = MODULE;
        $data['Status'] = STARTED;
        $data['Update'] = $cur_date;

        //execute query
        $db->insert("progress", $data);
      }
      //mark current module complete
      $data = array();
      $data['Status'] = COMPLETE;
      $data['Update'] = $cur_date;

      //execute query
      $db->update("progress", $data, "Email = '".$db->escape($email)."' AND ID = ".(int)$moduleId." AND TYPE = '".$db->escape(MODULE)."'");
    }

    //return next page
    header('Content-Type: application/xml; charset=ISO-8859-1');
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>".PHP_EOL;
    echo '<next>'.PHP_EOL;
    echo    '<type>'.$type.'</type>'.PHP_EOL;
    echo    '<id>'.$id.'</id>'.PHP_EOL;
    echo '</next>'.PHP_EOL;
  }
  else {
    //mark the last module complete
    $data = array();
    $data['Status'] = COMPLETE;
    $data['Update'] = $cur_date;

    //execute query
    $db->update("progress", $data, "Email = '".$db->escape($email)."' AND ID = ".(int)$moduleId." AND TYPE = '".$db->escape(MODULE)."'");

    //return end of modules
    header('Content-Type: application/xml; charset=ISO-8859-1');
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>".PHP_EOL;
    echo '<next>'.PHP_EOL;
    echo    '<type>module</type>'.PHP_EOL;
    echo    '<id>-1</id>'.PHP_EOL;
    echo '</next>'.PHP_EOL;
  }
}
$db->close();
?>