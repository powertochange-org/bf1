<?php
/*
 * Cru Doctrine
 * Page Builder - Save
 * Keith Roehrenbeck | Campus Crusade for Christ
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
        $_db->beginTransaction();
        if($insert) {   //insert
            $prep = $_db->prepare("INSERT INTO element (PageId, Type, Ord, Loc) VALUES (?, ?, ?, ?)");
            $prep->bindValue(1, (int)$pageId, PDO::PARAM_INT);
            $prep->bindValue(2, $elementType, PDO::PARAM_STR);
            $prep->bindValue(3, (int)$order, PDO::PARAM_INT);
            $prep->bindValue(4, $_loc, PDO::PARAM_STR);
            $prep->execute();
            $elementId = $_db->lastInsertId();
        } else {        //update
            $prep = $_db->prepare("UPDATE element SET Ord = ?, Loc = ? WHERE ElementId = ?");
            $prep->bindValue(1, (int)$order, PDO::PARAM_INT);
            $prep->bindValue(2, $_loc, PDO::PARAM_STR);
            $prep->bindValue(3, (int)$elementId, PDO::PARAM_INT);
            $prep->execute();
        }
        $_db->commit();

        //create type-specific element
        switch($elementType){
            case 'textbox':
                //get values
                $text       = $element->text;

                //save to db
                $_db->beginTransaction();
                if($insert) {//insert
                    $prep = $_db->prepare("INSERT INTO Textbox (ID, text) VALUES (?, ?)");
                    $prep->bindValue(1, (int)$elementId, PDO::PARAM_INT);
                    $prep->bindValue(2, $text, PDO::PARAM_STR);
                    $prep->execute();
                } else {            //update
                    $prep = $_db->prepare("UPDATE Textbox SET text = ? WHERE ID = ?");
                    $prep->bindValue(1, $text, PDO::PARAM_STR);
                    $prep->bindValue(2, (int)$elementId, PDO::PARAM_INT);
                    $prep->execute();
                }
                $_db->commit();

                break;

            case 'media':
                //get values
                $url        = $element->url;
                $height     = $element->height;
                $width      = $element->width;
                $caption    = $element->caption;

                //save to db
                $_db->beginTransaction();
                if($insert) {       //insert
                    $prep = $_db->prepare("INSERT INTO Media (ID, Caption, filename, height, width) VALUES (?, ?, ?, ?, ?)");
                    $prep->bindValue(1, (int)$elementId,    PDO::PARAM_INT);
                    $prep->bindValue(2, $caption,           PDO::PARAM_STR);
                    $prep->bindValue(3, $url,               PDO::PARAM_STR);
                    $prep->bindValue(4, (int)$height,       PDO::PARAM_INT);
                    $prep->bindValue(5, (int)$width,        PDO::PARAM_INT);
                    $prep->execute();
                } else {            //update
                    $prep = $_db->prepare("UPDATE Media SET Caption = ?, filename = ?, height = ?, width = ? WHERE ID = ?");
                    $prep->bindValue(1, $catpion,               PDO::PARAM_STR);
                    $prep->bindValue(2, $url,           PDO::PARAM_STR);
                    $prep->bindValue(3, (int)$height,       PDO::PARAM_INT);
                    $prep->bindValue(4, (int)$width,        PDO::PARAM_INT);
                    $prep->bindValue(5, (int)$elementId,    PDO::PARAM_INT);
                    $prep->execute();
                }
                $_db->commit();

                break;

            case 'image':
                //get values
                $url        = $element->url;
                $height     = $element->height;
                $width      = $element->width;
                $caption    = $element->caption;

                //save to db
                $_db->beginTransaction();
                if($insert) {       //insert
                    $prep = $_db->prepare("INSERT INTO Image (ID, Caption, filename, height, width) VALUES (?, ?, ?, ?, ?)");
                    $prep->bindValue(1, (int)$elementId,    PDO::PARAM_INT);
                    $prep->bindValue(2, $caption,           PDO::PARAM_STR);
                    $prep->bindValue(3, $url,               PDO::PARAM_STR);
                    $prep->bindValue(4, (int)$height,       PDO::PARAM_INT);
                    $prep->bindValue(5, (int)$width,        PDO::PARAM_INT);
                    $prep->execute();
                } else {            //update
                    $prep = $_db->prepare("UPDATE Image SET Caption = ?, filename = ?, height = ?, width = ? WHERE ID = ?");
                    $prep->bindValue(1, $caption,           PDO::PARAM_STR);
                    $prep->bindValue(2, $url,               PDO::PARAM_STR);
                    $prep->bindValue(3, (int)$height,       PDO::PARAM_INT);
                    $prep->bindValue(4, (int)$width,        PDO::PARAM_INT);
                    $prep->bindValue(5, (int)$elementId,    PDO::PARAM_INT);
                    $prep->execute();
                }
                $_db->commit();

                break;

            case 'input':
                //get values
                $question   = $element->question;
                $personal   = $element->personal == 'true'    ? true : false;
                $coach      = $element->coach == 'true'       ? true : false;
                $min        = $element->min;
                //save to db
                $_db->beginTransaction();
                if($insert) {//insert
                    $prep = $_db->prepare("INSERT INTO Input (ID, question, personal, coach, min) VALUES (?, ?, ?, ?, ?)");
                    $prep->bindValue(1, (int)$elementId,    PDO::PARAM_INT);
                    $prep->bindValue(2, $question,          PDO::PARAM_STR);
                    $prep->bindValue(3, (int)$personal,     PDO::PARAM_INT);
                    $prep->bindValue(4, (int)$coach,        PDO::PARAM_INT);
                    $prep->bindValue(5, (int)$min,          PDO::PARAM_INT);
                    $prep->execute();
                } else {    //update
                    $prep = $_db->prepare("UPDATE Input SET question = ?, personal = ?, coach = ?, min = ? WHERE ID = ?");
                    $prep->bindValue(1, $question,          PDO::PARAM_STR);
                    $prep->bindValue(2, (int)$personal,     PDO::PARAM_BOOL);
                    $prep->bindValue(3, (int)$coach,        PDO::PARAM_BOOL);
                    $prep->bindValue(4, (int)$min,          PDO::PARAM_INT);
                    $prep->bindValue(5, (int)$elementId,    PDO::PARAM_INT);
                    $prep->execute();
                }
                $_db->commit();

                break;

            case 'whitespace':
                //get values
                $height     = $element->height;
                //save to db
                $_db->beginTransaction();
                if($insert) {//insert
                    $prep = $_db->prepare("INSERT INTO whitespace (ID, Height) VALUES (?, ?)");
                    $prep->bindValue(1, (int)$elementId,    PDO::PARAM_INT);
                    $prep->bindValue(2, (int)$height,       PDO::PARAM_INT);
                    $prep->execute();
                } else {    //update
                    $prep = $_db->prepare("UPDATE Input SET Height = ? WHERE ID = ?");
                    $prep->bindValue(1, (int)$height,       PDO::PARAM_INT);
                    $prep->bindValue(2, (int)$personal,     PDO::PARAM_BOOL);
                    $prep->execute();
                }
                $_db->commit();

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
            $_db->beginTransaction();

            $prep = $_db->prepare("DELETE FROM ".$elementType." WHERE ID = ? ");
            $prep->bindValue(1, (int)$elementId, PDO::PARAM_INT);
            $prep->execute();

            $prep = $_db->prepare("DELETE FROM element WHERE ElementId = ? ");
            $prep->bindValue(1, (int)$elementId, PDO::PARAM_INT);
            $prep->execute();

            $_db->commit();
        }
    }
}

try {

    global $pageId, $section, $title, $visibility, $order;

    //create pdo object
    $db = new PDO('mysql:host=crudoctrine.db.6550033.hostedresource.com;port=3306;dbname=crudoctrine', 'crudoctrine', 'D6LLd2mxU6Z34i');
    
    //begin transaction
    $db->beginTransaction();

    //process page information
    if($pageId == 0){//insert
        $prep = $db->prepare("INSERT INTO Page (SectionId, Title, Ord, Visibility) VALUES (?,?,?,?)");
        $prep->bindValue(1, (int)$section,  PDO::PARAM_INT);
        $prep->bindValue(2, $title,         PDO::PARAM_STR);
        $prep->bindValue(3, (int)$order,    PDO::PARAM_INT);
        $prep->bindValue(4, $visibility,    PDO::PARAM_STR);
        $prep->execute();
        $pageId = $db->lastInsertId();
    } else {        //update
        $prep = $db->prepare("UPDATE Page SET SectionId = ?, Title = ?, Ord = ?, Visibility = ? WHERE ID = ?");
        $prep->bindValue(1, (int)$section,  PDO::PARAM_INT);
        $prep->bindValue(2, $title,         PDO::PARAM_STR);
        $prep->bindValue(3, (int)$order,    PDO::PARAM_INT);
        $prep->bindValue(4, $visibility,    PDO::PARAM_STR);
        $prep->bindValue(5, (int)$pageId,   PDO::PARAM_INT);
        $prep->execute();
    }

    $db->commit();
    
    //process main xml
    $xml = simplexml_load_string($main);
    processElements($xml, 'main', $db);

    //process right xml
    $xml = simplexml_load_string($right);
    processElements($xml, 'right', $db);

    //process trash xml
    $xml = simplexml_load_string($trash);
    emptyTrash($xml, $db);

    $db = null;

    echo 1;

} catch (PDOException $e) {

    echo "Error!: " . $e->getMessage() . "<br/>";
    die();

}


?>
