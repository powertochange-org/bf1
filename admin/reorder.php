<?php
/*
 * Cru Doctrine
 * Admin - Reorder Handler
 * Campus Crusade for Christ
 */

require_once("../config.inc.php"); 
require_once("../Database.singleton.php");

try {

    //get values
    $ajax       = isset($_POST['ajax'])     ? true : false;

    $type       = isset($_POST['type'])     ? $_POST['type'] : '';
    $items      = isset($_POST['items'])    ? $_POST['items'] : '';

    $errors     = isset($_POST['errors'])   ? $_POST['errors'] : '';

    $items = str_replace('\"', '"', $items);
    $xml = simplexml_load_string($items);

    //initialize the database object
    $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
    $db->connect();

    foreach($xml->item as $item){

        //get values
        $id     = $item['id'];
        $order  = $item['order'];

        echo $id.' -> '.$order.PHP_EOL;

        //prepare query
        $data['Ord'] = (double)$order;

        //execute query
        $db->update($type, $data, "ID = " .(int)$id);
    }

    $db->close();

    //if ajax, return success
    if ($ajax) {

        echo 1;

    }

} catch (PDOException $e) {
    echo $e->getMessage();
    exit();
}
?>