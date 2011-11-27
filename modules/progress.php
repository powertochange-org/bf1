<?php
/*
 * Cru Doctrine
 * Modules - Progress
 * Keith Roehrenbeck | Campus Crusade for Christ
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

//get pdo
$db = new PDO('mysql:host=crudoctrine.db.6550033.hostedresource.com;port=3306;dbname=crudoctrine', 'crudoctrine', 'D6LLd2mxU6Z34i');

//update progress
if($submit){

    //mark current page complete
    $query = $db->prepare("UPDATE progress SET Status = ?, `Update` = ? WHERE Email = ? AND PageId = ?;");
    $query->bindValue(1, 'complete',        PDO::PARAM_STR);
    $query->bindValue(2, $cur_date,         PDO::PARAM_STR);
    $query->bindValue(3, $email,            PDO::PARAM_STR);
    $query->bindValue(4, (int)$pageId,      PDO::PARAM_INT);
    $query->execute();

    //get next page
    $db_next = $db->prepare("   SELECT p.ID AS Page, s.ID AS Section, m.ID AS Module
                                FROM page p
                                INNER JOIN section s ON p.SectionId = s.ID
                                INNER JOIN module m ON s.ModuleId = m.ID
                                WHERE   (p.Ord = ".($pageOrd + 1)." AND s.ID = ".$sectionId." AND m.ID = ".$moduleId.") OR
                                        (p.Ord = 0 AND s.Ord = ".($sectionOrd + 1)." AND m.ID = ".$moduleId.") OR
                                        (p.Ord = 0 AND s.Ord = 0 AND m.Ord = ".($moduleOrd + 1).");");

    $db_next->bindValue(1, (int)($pageOrd + 1),     PDO::PARAM_STR);
    $db_next->bindValue(2, (int)$sectionId,         PDO::PARAM_STR);
    $db_next->bindValue(3, (int)$moduleId,          PDO::PARAM_STR);
    $db_next->bindValue(4, (int)($sectionOrd + 1),  PDO::PARAM_STR);
    $db_next->bindValue(5, (int)$moduleId,          PDO::PARAM_STR);
    $db_next->bindValue(6, (int)($moduleOrd + 1),   PDO::PARAM_STR);
    $db_next->execute();

    if(count($db_next > 0)){
        $next       = $db_next->fetch(PDO::FETCH_ASSOC);
        $nextId     = $next['Page'];

        //mark next page started
        $query = $db->prepare("INSERT INTO progress (Email, PageId, Status, `Update`) VALUES (?,?,?,?)");
        $query->bindValue(1, $email,            PDO::PARAM_STR);
        $query->bindValue(2, (int)$nextId,      PDO::PARAM_INT);
        $query->bindValue(3, 'started',         PDO::PARAM_STR);
        $query->bindValue(4, $cur_date,         PDO::PARAM_STR);
        $query->execute();

        $type   = $next['Module'] == $moduleId ? 'page' : 'module';
        $id     = $type == 'page' ? $nextId : $next['Module'];

        //return next page
        header('Content-Type: application/xml; charset=ISO-8859-1');
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>".PHP_EOL;
        echo '<next>'.PHP_EOL;
        echo    '<type>'       .$type.              '</type>'.PHP_EOL;
        echo    '<id>'         .$id.		    '</id>'.PHP_EOL;
        echo '</next>'.PHP_EOL;

    } else {

        //return end of modules
        header('Content-Type: application/xml; charset=ISO-8859-1');
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>".PHP_EOL;
        echo '<next>'.PHP_EOL;
        echo    '<type>module</type>'.PHP_EOL;
        echo    '<id>0</id>'.PHP_EOL;
        echo '</next>'.PHP_EOL;

    }

}

$db = null;

?>
