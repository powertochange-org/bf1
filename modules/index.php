<?php
/*
 * Cru Doctrine
 * Modules
 * Campus Crusade for Christ
 */

//ensure user authentication
$auth = false;

session_start();
if(isset($_SESSION['email'])){
    $auth = true;
}

if(!$auth){
    header('Location: /#login');
}

//get session values
$email  = $_SESSION['email'];
$type   = $_SESSION['type'];

//module, section, and page arrays 
$module     = array();
$section    = array();
$page       = array();

require_once("../config.inc.php"); 
require_once("../Database.singleton.php");
require_once("../function.inc.php"); 

try {
    global $module, $section, $page;

    //initialize the database object
    $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
    $db->connect();

    //determine page content
    $mod = isset($_GET['m'])        ? $_GET['m']        : '';
    $sec = isset($_GET['s'])        ? $_GET['s']        : '';
    $pag = isset($_GET['p'])        ? $_GET['p']        : '';
    $req = isset($_GET['request'])  ? $_GET['request']  : '';

    $pagetype = '';

    //transition to the next module
	if($mod != '') {
        if($req != '') {
            //page title
            $title = 'Module '.$mod.' '.$req;
            
            //page type
            $pagetype = $req;
        } 
        else {
            //get module information
            $sql =  "SELECT m.ID, m.Number, m.Name, m.Ord, m.Descr, m.Banner, m.FrontImg, s.ID AS FirstSection
                     FROM module m
                     INNER JOIN section s ON s.ModuleId = m.ID
                     WHERE m.ID = ".(int)$mod."
                     AND s.Ord = 0";

            $module = $db->query_first($sql);
            $module['Order'] = $module['Ord'];

            if($db->affected_rows > 0) {
                //page title
                $title = 'Module '.$module['Number'];

                //page type
                $pagetype = MODULE;
            } else {
              header('Location: /work');
            }
        }
    //transition to the next section
    } 
    elseif($sec != '') {
        //get section, module, & first page information
        $sql = "SELECT s.*, m.Number, m.Name AS ModuleName, m.Ord AS ModuleOrder, m.Banner, p.ID AS PageId, p.Ord AS PageOrder, p.Visibility
                FROM section s 
                INNER JOIN module m on s.ModuleId = m.Id 
                INNER JOIN page p on s.ID = p.SectionId 
                WHERE s.ID = ".(int)$sec.
                " ORDER BY p.Ord ASC";

        $result = $db->query_first($sql);
        
        //module information
        $module['ID']       = $result['ModuleId'];
        $module['Number']   = $result['Number'];
        $module['Name']     = $result['ModuleName'];
        $module['Order']    = $result['ModuleOrder'];
        $module['Banner']   = $result['Banner'];
        
        //section information
        $section['ID']      = $result['ID'];
        $section['Title']   = $result['Title'];
        $section['Order']   = $result['Ord'];
        
        //page information
        $page['ID']         = $result['PageId'];
        $page['Order']      = $result['PageOrder'];
        $page['Visibility'] = $result['Visibility'];
        
        //page title
        $title = 'Module '.$module['Number'];
        
        //page type
        $pagetype = PAGE;

    //transition to the next page
    } 
    elseif($pag != '') {
        //get page, section, & module information
        $sql = "SELECT p.*, s.ModuleId, s.Title AS SectionTitle, s.Ord AS SectionOrder, m.Number, m.Name AS ModuleName, m.Ord AS ModuleOrder, m.Banner
                FROM page p
                INNER JOIN section s on p.SectionId = s.ID
                INNER JOIN module m on s.ModuleId = m.Id
                WHERE p.ID = ".(int)$pag;

        $result = $db->query_first($sql);

        //module information
        $module['ID']       = $result['ModuleId'];
        $module['Number']   = $result['Number'];
        $module['Name']     = $result['ModuleName'];
        $module['Order']    = $result['ModuleOrder'];
        $module['Banner']   = $result['Banner'];

        //section information
        $section['ID']      = $result['SectionId'];
        $section['Title']   = $result['SectionTitle'];
        $section['Order']   = $result['SectionOrder'];

        //page information
        $page['ID']         = $result['ID'];
        $page['Order']      = $result['Ord'];
        $page['Visibility'] = $result['Visibility'];

        //page title
        $title = 'Module '.$module['Number'];

        //page type
        $pagetype = PAGE;
    } 
    else {
        //page title
        $title = 'Modules';

        //page type
        $pagetype = 'directory';
    }

    //ensure user has proper access to loading page
    $auth = false;

    if ($pagetype == PAGE && ($type > COACH && $type != OTHER)) {
      //fetch user progress to validate loading page
      $sql = "SELECT pr.Status, p.Ord AS Page, s.Ord AS Section, m.Ord AS Module
              FROM progress pr
              INNER JOIN page p ON pr.ID = p.ID
              INNER JOIN section s ON p.SectionId = s.ID
              INNER JOIN module m ON s.ModuleId = m.ID
              WHERE pr.Email = '".$db->escape($email)."'
              AND pr.Type = '".PAGE."'
              AND pr.ID = ".$page['ID'];

      $progress = $db->query_first($sql);

      if($progress['Module'] >= $module['Order']) {
          if($progress['Section'] >= $section['Order']) {
              $auth = $progress['Page'] >= $page['Order'] ? true : false;
          }
      }
    } 
    else {
      //fetch user progress to validate loading page
      $sql = "SELECT pr.Status, m.Ord AS Module
              FROM progress pr
              INNER JOIN module m ON pr.ID = m.ID
              WHERE pr.Email = '".$db->escape($email)."'
              AND pr.Type = '".MODULE."'
              AND pr.ID = ".$module['ID'];

      $progress = $db->query_first($sql);

      //seed progress with the first page
      if ($db->affected_rows > 0) {
        $auth = $progress['Module'] >= $module['Order'] ? true : false;
      } 
      else {
        //get the first page ID
        $sql     = "SELECT p.ID AS Page
                    FROM page p
                    INNER JOIN section s ON p.SectionId = s.ID
                    INNER JOIN module m ON s.ModuleId = m.ID
                    WHERE (p.Ord = 0 AND s.Ord = 0 AND m.Ord = ".$module['Order'].");";

        $result  = $db->query_first($sql);

        //insert the first page progress record
        $data = array();
        $data['Email']  = $email;
        $data['ID'] = $result['Page'];
        $data['Type'] = PAGE;
        $data['Status'] = STARTED;
        $data['Update'] = date( 'Y-m-d' );
        //execute query
        $db->insert("progress", $data);

        //insert the first module progress record
        $data = array();
        $data['Email']  = $email;
        $data['ID'] = $module['ID'];
        $data['Type'] = MODULE;
        $data['Status'] = STARTED;
        $data['Update'] = date( 'Y-m-d' );
        //execute query
        $db->insert("progress", $data);

        $auth = true;
      }
    }

    if(!$auth) {
        header('Location: '.$_SERVER['HTTP_REFERER']);
    }

} catch (PDOException $e) {
    echo $e->getMessage();
}

$content = $pagetype.'.php';

//header
include('../header.php');

//content
?>

<link rel="stylesheet" type="text/css" media="screen" href="modules.css" />

<div id="content">
    
    <?php include($content); ?>
    
</div>

<script type="text/javascript">
    //jquery class interaction states
    $('.ui-state-default').hover(
        function(){
            $(this).addClass("ui-state-hover");
        },
        function(){
            $(this).removeClass("ui-state-hover");
        }
    );
</script>

<?php
$db->close();
//footer
include('../footer.php');
?>