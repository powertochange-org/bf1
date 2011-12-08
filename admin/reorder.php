<?php
/*
 * Cru Doctrine
 * Admin - Reorder Handler
 * Campus Crusade for Christ
 */

try {

    //get values
    $ajax       = isset($_POST['ajax'])     ? true                                          : false;

    $type       = isset($_POST['type'])     ? $_POST['type']                                : '';
    $xml        = isset($_POST['items'])    ? simplexml_load_string($_POST['items'])        : '';

    $errors     = isset($_POST['errors'])   ? $_POST['errors']                              : '';

	// grab the existing $db object
	$db=Database::obtain();

    foreach($xml->item as $item){

        //get values
        $id     = $item['id'];
        $order  = $item['order'];

        echo $id.' -> '.$order.PHP_EOL;

        //prepare query
        $data['Ord'] = (double)$order;

        //execute query
        $db->update($type., $data, "ID = " .(int)$id);
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