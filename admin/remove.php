<?php
/*
 * Cru Doctrine
 * Admin - Remove Handler
 * Keith Roehrenbeck | Campus Crusade for Christ
 */

function deleteModule($id){

    global $db;

    //delete attached sections
    $sections = $db->query('SELECT * FROM section WHERE ModuleId = '.$id)->fetchAll(PDO::FETCH_ASSOC);
    foreach($sections as $section){
        deleteSection($section['ID']);
    }
    
    //delete module
    $db->exec('DELETE FROM module WHERE ID='.$id);
}

function deleteSection($id){

    global $db;

    //delete attached pages
    $pages = $db->query('SELECT * FROM page WHERE SectionId = '.$id)->fetchAll(PDO::FETCH_ASSOC);
    foreach($pages as $page){
        deletePage($page['ID']);
    }
    
    //delete section
    $db->exec('DELETE FROM section WHERE ID='.$id);
}

function deletePage($id){

    global $db;
    
    //delete attached elements
    $elements = $db->query('SELECT * FROM element WHERE PageId = '.$id)->fetchAll(PDO::FETCH_ASSOC);
    foreach($elements as $element){
        $db->exec('DELETE FROM '.$element['Type'].' WHERE ID='.$element['ElementId']);
        $db->exec('DELETE FROM element WHERE ElementId='.$element['ElementId']);
    }

    //delete page
    $db->exec('DELETE FROM page WHERE ID='.$id);
}

try {

    //get values
    $ajax       = isset($_POST['ajax'])     ? true                                          : false;

    $type       = isset($_POST['type'])     ? $_POST['type']                                : '';
    $id         = isset($_POST['id'])       ? $_POST['id']                                  : '';

    $errors     = isset($_POST['errors'])   ? $_POST['errors']                              : '';

    //initialize pdo object
    $db = new PDO('mysql:host=crudoctrine.db.6550033.hostedresource.com;port=3306;dbname=crudoctrine', 'crudoctrine', 'D6LLd2mxU6Z34i');

    //open transaction
    $db->beginTransaction();

    switch($type){

        case 'module':
            deleteModule($id);
            break;

        case 'section':
            deleteSection($id);
            break;

        case 'page':
            deletePage($id);
            break;

    }

    $db->commit();

    $db = null;

    //if ajax, return success
    if ($ajax) {

        echo 1;

    }

} catch (PDOException $e) {
    echo $e->getMessage();
    exit();
}

?>
