<?php
/*
 * Cru Doctrine
 * Modules - Response
 * Campus Crusade for Christ
 */

//get values
session_start();
$email      = $_SESSION['email'];
$submit     = isset($_POST['submit'])   ? true                          : false;
$new        = isset($_POST['_new'])     ? $_POST['_new'] == 'true'      : true;

$id         = isset($_POST['id'])       ? $_POST['id']                  : 0;
$response   = isset($_POST['response']) ? $_POST['response']            : '';
$personal   = isset($_POST['personal']) ? $_POST['personal'] == 'true'  : 0;
$coach      = isset($_POST['coach'])    ? $_POST['coach'] == 'true'     : 0;

$errors     = isset($_POST['errors'])   ? $_POST['errors']          : '';

require_once("../config.inc.php"); 
require_once("../Database.singleton.php");

//initialize the database object
$db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
$db->connect();

//save response
if($submit){

    if($new){//new response

        //prepare query
        $data['Email'] = $email;
        $data['InputId'] = (int)$id;
        $data['Response'] = $response;
        $data['Personal'] = $personal;
        $data['Coach'] = $coach;
        
        //execute query
        $db->insert("response", $data);
        
    } else {//edit response

        //prepare query
        $data['Response'] = $response;
        $data['Personal'] = (int)$personal;
        $data['Coach'] = (int)$coach;

        //execute query
        $db->update("response", $data, "Email = '".$db->escape($email)."' AND InputId = " .(int)$id);

    }
    $db->close();
    echo 'Response Saved';

    exit();
}

//get response
$sql = "SELECT * FROM response WHERE Email = '".$db->escape($email)."' AND InputId = " .(int)$id;
$_response = $db->query_first($sql);

if(count($_response) > 1){
    $new        = false;

    $response   = $_response['Response'];
    $personal   = $_response['Personal'];
    $coach      = $_response['Coach'];

} else {

    $new        = true;

    $response   = '';
    $personal   = 0;
    $coach      = 0;

}

$db->close();

//return response values
header('Content-Type: application/xml; charset=ISO-8859-1');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>".PHP_EOL;
echo '<response>'.PHP_EOL;
echo    '<new>'        .$new.               '</new>'.PHP_EOL;
echo    '<id>'         .$id.            '</id>'.PHP_EOL;
echo    '<text>'       .$response.      '</text>'.PHP_EOL;
echo    '<personal>'   .$personal.      '</personal>'.PHP_EOL;
echo    '<coach>'      .$coach.         '</coach>'.PHP_EOL;
echo '</response>'.PHP_EOL;

exit();
?>