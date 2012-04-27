<?php
/*
 * Cru Doctrine
 * Modules - Progress
 * Campus Crusade for Christ
 */

//get values
session_start();
$email      = $_SESSION['email'];
$submit     = isset($_POST['submit'])       ? true                      : false;
$pageId     = isset($_POST['pageId'])       ? $_POST['pageId']          : 0;
$sectionId  = isset($_POST['sectionId'])    ? $_POST['sectionId']       : 0;
$moduleId   = isset($_POST['moduleId'])     ? $_POST['moduleId']        : 0;
$pageOrd    = isset($_POST['pageOrd'])      ? $_POST['pageOrd']         : 0;
$sectionOrd = isset($_POST['sectionOrd'])   ? $_POST['sectionOrd']      : 0;
$moduleOrd  = isset($_POST['moduleOrd'])    ? $_POST['moduleOrd']       : 0;
$cur_date   = date( 'Y-m-d' );
$errors     = isset($_POST['errors'])       ? $_POST['errors']          : '';

require_once("../config.inc.php"); 
require_once("../Database.singleton.php");

//initialize the database object
$db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
$db->connect();

//update progress
if($submit) {

    //mark current page complete
    $data = array();
    $data['Status'] = COMPLETE;
    $data['Update'] = $cur_date;
    
    //execute query
    $db->update("progress", $data, "Email = '".$db->escape($email)."' AND ID = ".(int)$pageId." AND TYPE = '".$db->escape(PAGE)."'");
    
    //get next page
    $sql = "SELECT p.ID AS Page, s.ID AS Section, m.ID AS Module
            FROM page p
            INNER JOIN section s ON p.SectionId = s.ID
            INNER JOIN module m ON s.ModuleId = m.ID
            WHERE   (p.Ord = ".($pageOrd + 1)." AND s.ID = ".$sectionId." AND m.ID = ".$moduleId.") OR
                    (p.Ord = 0 AND s.Ord = ".($sectionOrd + 1)." AND m.ID = ".$moduleId.") OR
                    (p.Ord = 0 AND s.Ord = 0 AND m.Ord = ".($moduleOrd + 1).");";
    //execute query 
    $next = $db->query_first($sql);

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
        //return end of modules
        header('Content-Type: application/xml; charset=ISO-8859-1');
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>".PHP_EOL;
        echo '<next>'.PHP_EOL;
        echo    '<type>module</type>'.PHP_EOL;
        echo    '<id>0</id>'.PHP_EOL;
        echo '</next>'.PHP_EOL;
    }
}
$db->close();
?>