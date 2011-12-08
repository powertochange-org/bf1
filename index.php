<?php
/* 
 * Cru Doctrine
 * Home Page
 * Campus Crusade for Christ
 */

//page title
$title = 'Welcome';

//header
include_once('header.php');

//get modules from db
$modules = array();

try {

    global $modules;

    //initialize the database object
	$db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
	$db->connect();     

    $sql = "SELECT * FROM module ORDER BY Ord;";

	//execute query and return to module array
    $modules = $db->fetch_array($sql);

	$db->close();

} catch (PDOException $e){
    echo $e->getMessage();
}
?>

<link rel="stylesheet" type="text/css" media="screen" href="css/home.css" />

<div id="content">

    <div id="slider">

        <?php

            if(count($modules) > 0){
                foreach ($modules as $module){
                    if($module['Number'] > 1) {
                        echo '  <div id="module'.str_replace('.', '', $module['Number']).'" class="module">
                                    <div class="slide">
                                        <div class="label"><span>Module '.$module['Number'].'</span></div>
                                        <div class="pic"><img src="'.$module['FrontImg'].'" height="300" width="515"></div>
                                        <div class="name">'.$module['Name'].'</div>
                                        <div class="desc">'.$module['Descr'].'</div>
                                        <a class="start ui-corner-all ui-state-default" href="modules?m='.$module['ID'].'">Go To Module<span class="ui-icon ui-icon-circle-triangle-e"></span></a>>
                                    </div>
                                </div>';
                    }
                    
                }
            }

        ?>

    </div>

</div>

<script type="text/javascript">

    $(function(){
        $('#module11').css('width', '555px').addClass('active');
    });

    $('.module').click(function() {

        if($(this).hasClass('active') == false){

            //close current
            $('.active').animate({
                    width: '45px'
                }, 500, function() {
                    // Animation complete.
                });
            $('.active').removeClass('active');

            //open clicked module
            $(this).addClass('active');
            $(this).animate({
                width: '555px'
            }, 500, function() {
                // Animation complete.
            });

        }
    });

    //jquery class interaction states

    $('.remove, .addpage, .edit, button').addClass('ui-state-default');

</script>
<?php

//footer
include('footer.php');

?>
