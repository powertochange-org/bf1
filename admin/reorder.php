<?php
/*
 * Cru Doctrine
 * Admin - Reorder Handler
 * Keith Roehrenbeck | Campus Crusade for Christ
 */

try {

    //get values
    $ajax       = isset($_POST['ajax'])     ? true                                          : false;

    $type       = isset($_POST['type'])     ? $_POST['type']                                : '';
    $xml        = isset($_POST['items'])    ? simplexml_load_string($_POST['items'])        : '';

    $errors     = isset($_POST['errors'])   ? $_POST['errors']                              : '';

    //initialize pdo object
    $db = new PDO('mysql:host=crudoctrine.db.6550033.hostedresource.com;port=3306;dbname=crudoctrine', 'crudoctrine', 'D6LLd2mxU6Z34i');

    //open transaction
    $db->beginTransaction();

    foreach($xml->item as $item){

        //get values
        $id     = $item['id'];
        $order  = $item['order'];

        echo $id.' -> '.$order.PHP_EOL;

        //prepare query
        $query = $db->prepare("UPDATE ".$type." SET Ord = ? WHERE ID = ?");
        $query->bindValue(1, (double)$order,    PDO::PARAM_INT);
        $query->bindValue(2, (int)$id,          PDO::PARAM_INT);

        //execute query
        $query->execute();

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
