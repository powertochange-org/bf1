<?php
/*
 * Cru Doctrine
 * Modules - Response
 * Keith Roehrenbeck | Campus Crusade for Christ
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

//get pdo
$db = new PDO('mysql:host=crudoctrine.db.6550033.hostedresource.com;port=3306;dbname=crudoctrine', 'crudoctrine', 'D6LLd2mxU6Z34i');

//save response
if($submit){

    if($new){   //new response

        //prepare query
        $query = $db->prepare("INSERT INTO response (Email, InputId, Response, Personal, Coach) VALUES (?,?,?,?,?)");
        $query->bindValue(1, $email,            PDO::PARAM_STR);
        $query->bindValue(2, (int)$id,          PDO::PARAM_INT);
        $query->bindValue(3, $response,         PDO::PARAM_STR);
        $query->bindValue(4, (int)$coach,       PDO::PARAM_INT);
        $query->bindValue(5, (int)$response,    PDO::PARAM_INT);

        //execute query
        $query->execute();

    } else {    //edit response

        //prepare query
        $query = $db->prepare("UPDATE response SET Response = ?, Personal = ?, Coach = ? WHERE Email = ? AND InputId = ?");
        $query->bindValue(1, $response,         PDO::PARAM_STR);
        $query->bindValue(2, (int)$personal,    PDO::PARAM_INT);
        $query->bindValue(3, (int)$coach,       PDO::PARAM_INT);
        $query->bindValue(4, $email,            PDO::PARAM_STR);
        $query->bindValue(5, (int)$id,          PDO::PARAM_INT);

        //execute query
        $query->execute();

    }
    $db = null;
    echo 'Response Saved';

    exit();

}

//get response
$db_response = $db->prepare("SELECT * FROM response WHERE Email = ? AND InputId = ?");
$db_response->bindValue(1, $email,      PDO::PARAM_STR);
$db_response->bindValue(2, (int)$id,    PDO::PARAM_INT);
$db_response->execute();

$_response      = $db_response->fetch(PDO::FETCH_ASSOC);

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

$db = null;

//return response values
header('Content-Type: application/xml; charset=ISO-8859-1');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>".PHP_EOL;
echo '<response>'.PHP_EOL;
echo    '<new>'        .$new.               '</new>'.PHP_EOL;
echo    '<id>'         .$id.		    '</id>'.PHP_EOL;
echo    '<text>'       .$response.	    '</text>'.PHP_EOL;
echo    '<personal>'   .$personal.	    '</personal>'.PHP_EOL;
echo    '<coach>'      .$coach.		    '</coach>'.PHP_EOL;
echo '</response>'.PHP_EOL;

exit();

?>
