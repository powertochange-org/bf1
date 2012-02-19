<?php
/*
 * Cru Doctrine
 * Page Builder - Save
 * Campus Crusade for Christ
 */

//get page info
$pageId     = $_POST['pageId'];
$title      = $_POST['title'];
$section    = $_POST['section'];
$order      = $_POST['order'];
$visibility = $_POST['visibility'];

//get xml strings
$main       = $_POST['main'];
$right      = $_POST['right'];
$trash      = $_POST['trash'];

$main = stripslashes($main);
$right = stripslashes($right);
$trash = stripslashes($trash);

require_once("../../config.inc.php"); 
require_once("../../Database.singleton.php");

function processElements($_xml, $_loc, $_db){

    global $pageId;

    foreach ($_xml->element as $element) {
        //element attributes
        $elementId      = $element['id'];
        $elementType    = $element['type'];
        $order          = $element['order'];

        //update or insert
        $insert = ($elementId == 0);

        //attach element to page
        if($insert) {//insert

            //prepare query
            $data = array();
            $data['PageId'] = (int)$pageId;
            $data['Type'] = $elementType;
            $data['Ord'] = (int)$order;
            $data['Loc'] = $_loc;

            //execute query
            $elementId = $_db->insert("element", $data);
        } else {//update
    
            //prepare query
            $data = array();
            $data['Ord'] = (int)$order;
            $data['Loc'] = $_loc;
    
            //execute query
            $_db->update("element", $data, "ElementId = " .(int)$elementId); 
        }

        //create type-specific element
        switch($elementType){
            case 'textbox':
                //get values
                $text = $element->text;

                //save to db
                if($insert) {//insert

                    //prepare query
                    $data = array();
                    $data['ID'] = (int)$elementId;
                    $data['Text'] = $text;

                    //execute query
                    $textBoxId = $_db->insert("textbox", $data);
                } else {//update

                    //prepare query
                    $data = array();
                    $data['Text'] = $text;

                    //execute query
                    $_db->update("textbox", $data, "ID = " .(int)$elementId);
                }

                break;

            case 'media':
                //get values
                $url        = $element->url;
                $height     = $element->height;
                $width      = $element->width;
                $caption    = $element->caption;

                //save to db
                if($insert) {//insert
    
                    //prepare query
                    $data = array();
                    $data['ID'] = (int)$elementId;
                    $data['Caption'] = $caption;
                    $data['Filename'] = $url;
                    $data['Height'] = (int)$height;
                    $data['Width'] = (int)$width;

                    //execute query
                    $mediaId = $_db->insert("media", $data);
                } else {//update
    
                    //prepare query
                    $data = array();
                    $data['Caption'] = $caption;
                    $data['Filename'] = $url;
                    $data['Height'] = (int)$height;
                    $data['Width'] = (int)$width;

                    //execute query
                    $_db->update("media", $data, "ID = " .(int)$elementId); 
                }

                break;

            case 'image':
                //get values
                $url        = $element->url;
                $height     = $element->height;
                $width      = $element->width;
                $caption    = $element->caption;

                //save to db

                if($insert) {//insert
    
                    //prepare query
                    $data = array();
                    $data['ID'] = (int)$elementId;
                    $data['Caption'] = $caption;
                    $data['Filename'] = $url;
                    $data['Height'] = (int)$height;
                    $data['Width'] = (int)$width;

                    //execute query
                    $imageId = $_db->insert("image", $data);
                } else {//update
    
                    //prepare query
                    $data = array();
                    $data['Caption'] = $caption;
                    $data['Filename'] = $url;
                    $data['Height'] = (int)$height;
                    $data['Width'] = (int)$width;

                    //execute query
                    $_db->update("image", $data, "ID = " .(int)$elementId); 
                }

                break;

            case 'input':
                //get values
                $question   = $element->question;
                $personal   = $element->personal == 'true' ? true : false;
                $coach      = $element->coach == 'true'    ? true : false;
                $min        = $element->min;
                //save to db
                if($insert) {//insert
    
                    //prepare query
                    $data = array();
                    $data['ID'] = (int)$elementId;
                    $data['Question'] = $question;
                    $data['Personal'] = (int)$personal;
                    $data['Coach'] = (int)$coach;
                    $data['Min'] = (int)$min;

                    //execute query
                    $inputId = $_db->insert("input", $data);
                } else {//update
    
                    //prepare query
                    $data = array();
                    $data['Question'] = $question;
                    $data['Personal'] = (int)$personal;
                    $data['Coach'] = (int)$coach;
                    $data['Min'] = (int)$min;

                    //execute query
                    $_db->update("input", $data, "ID = " .(int)$elementId); 
                }

                break;

            case 'whitespace':
                //get values
                $height     = $element->height;
                //save to db
                if($insert) {//insert
    
                    //prepare query
                    $data = array();
                    $data['ID'] = (int)$elementId;
                    $data['Height'] = (int)$height;

                    //execute query
                    $whitespaceId = $_db->insert("whitespace", $data);
                } else {//update

                    //prepare query
                    $data = array();
                    $data['Height'] = (int)$height;
                    
                    //execute query
                    $_db->update("whitespace", $data, "ID = " .(int)$personal); 
                }

                break;
        }
    }
}

function emptyTrash($_xml, $_db) {

    global $pageId;

    foreach ($_xml->element as $element) {

        //element attributes
        $elementId      = $element['id'];
        $elementType    = $element['type'];

        //remove if existing
        if($elementId > 0) {

           if ($elementType == 'input'){
                $sql = "DELETE FROM response WHERE InputId = ".(int)$elementId;
                //execute query
                $_db->query($sql);
            }

            $sql = "DELETE FROM ".$elementType." WHERE ID = ".(int)$elementId;
            //execute query
            $_db->query($sql);

            $sql = "DELETE FROM element WHERE ElementId = ".(int)$elementId;
            //execute query
            $_db->query($sql);
        }
    }
}

try {

    global $pageId, $section, $title, $visibility, $order;

    //initialize the database object
    $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
    $db->connect();
    
    //process page information
    if($pageId == 0){//insert

        //prepare query
        $data['SectionId'] = (int)$section;
        $data['Title'] = $title;
        $data['Ord'] = (int)$order;
        $data['Visibility'] = $visibility;

        //execute query
        $pageId = $db->insert("page", $data);
    } else {//update

        //prepare query
        $data['SectionId'] = (int)$section;
        $data['Title'] = $title;
        $data['Ord'] = (int)$order;
        $data['Visibility'] = $visibility;

        //execute query
        $db->update("page", $data, "ID = " .(int)$pageId);
    }
    
    //process main xml
    $xml = simplexml_load_string($main);
    processElements($xml, 'main', $db);

    //process right xml
    $xml = simplexml_load_string($right);
    processElements($xml, 'right', $db);

    //process trash xml
    $xml = simplexml_load_string($trash);
    emptyTrash($xml, $db);

    $db->close();

    echo 1;

} catch (PDOException $e) {
   echo "Error!: " . $e->getMessage() . "<br/>";
   die();
}
?>