<?php
/*
 * Cru Doctrine
 * Modules
 * Keith Roehrenbeck | Campus Crusade for Christ
 */

//ensure user authentication
$auth = false;

session_start();
if(isset($_SESSION['email'])){
    $auth = true;
}

if(!$auth){
    header('Location: #login');
}

//get session values
$email  = $_SESSION['email'];
$type   = $_SESSION['type'];

//module, section, and page arrays 
$module     = array();
$section    = array();
$page       = array();

try {

	// grab the existing $db object
	$db=Database::obtain();

    //determine page content
    $mod = isset($_GET['m'])        ? $_GET['m']        : '';
    $sec = isset($_GET['s'])        ? $_GET['s']        : '';
    $pag = isset($_GET['p'])        ? $_GET['p']        : '';
    $req = isset($_GET['request'])  ? $_GET['request']  : '';

    $pagetype = '';

    if($mod!=''){

        if($req!=''){

            //page title
            $title = 'Module '.$mod.' '.$req;
            
            //page type
            $pagetype = $req;

        } else{

            //get module information
			$sql =  "SELECT *, s.ID AS FirstSection
                     FROM module m
                     INNER JOIN section s ON s.ModuleId = m.ID
                     WHERE m.ID = ".(int)$mod.
                     " ORDER BY s.Ord ASC";

            $module = $db->query_first($sql);

            //page title
            $title = 'Module '.$module['Number'];
            
            //page type
            $pagetype = 'module';

        }

    } elseif($sec!=''){

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
        $page['visibility'] = $result['Visibility'];
        
        //page title
        $title = 'Module '.$module['Number'];
        
        //page type
        $pagetype = 'page';

    } elseif($pag!=''){

        //get page, section, & module information
        $sql = "SELECT p.*, s.ModuleId, s.Title AS SectionTitle, s.Ord AS SectionOrder, m.Number, m.Name AS ModuleName, m.Ord AS ModuleOrder, m.Banner
                FROM page p
                INNER JOIN section s on p.SectionId = s.ID
                INNER JOIN module m on s.ModuleId = m.Id
                WHERE p.ID = ".(int)$pag.;

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
        $page['visibility'] = $result['Visibility'];

        //page title
        $title = 'Module '.$module['Number'];

        //page type
        $pagetype='page';

    } else{

        //page title
        $title = 'Modules';

        //page type
        $pagetype='directory';

    }

    //fetch user progress to validate loading page
    $sql = "SELECT pr.Status, p.Ord AS page, s.Ord AS section, m.Ord AS module
            FROM progress pr
            INNER JOIN page p ON pr.PageId = p.ID
            INNER JOIN section s ON p.SectionId = s.ID
            INNER JOIN module m ON s.ModuleId = m.ID
            WHERE pr.Email = '".$db->escape($email)."'
            ORDER BY m.Ord, s.Ord, p.Ord";

    $progress = $db->query_first($sql);

    //ensure user has proper access to loading page
    $auth = false;

    switch($pagetype){
        case 'module':
            $auth = $progress['module'] >= $module['Ord'] ? true : false;
            break;

        case 'page':
            if($progress['module']>=$module['Order']){
                if($progress['section'] >= $section['Order']){
                    $auth = $progress['page'] >= $page['Order'] ? true : false;
                }
            }
            break;
    }

//    if(!$auth){
//        header('Location: /crudoctrine/work/');
//    }

	$db->close();

} catch (PDOException $e){
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

//footer
include('../footer.php');

?>