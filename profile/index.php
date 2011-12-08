<?php
/*
 * Cru Doctrine
 * My Profile
 * Campus Crusade for Christ
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

//page title
$title = 'My Profile';

//header
include('../header.php');

//content
?>

<link rel="stylesheet" type="text/css" media="screen" href="profile.css" />

<div id="content">

    <div id="profile">

        <div id="pagetitle">
            My Profile
        </div>

        <div id="leftmenu">
            <ul>
                <li><a href="?p=info" onclick="load('information.php');return false;">Information</a></li>
                <li><a href="?p=settings" onclick="load('progress.php');return false;">Settings</a></li>
            </ul>
        </div>

        <div id="contentpane">
            <?php
                $page = '';
                if(isset($_GET['p'])){
                    switch($_GET['p']){

                        case 'info':
                           $page = 'information.php';

                        case 'settings':
                            $page = 'settings.php';

                    }
                } else {

                    $page = 'information.php';

                }
                
                include($page);
            ?>
        </div>

    </div>

</div>

<script type="text/javascript">

    function load(page) {

        $.ajax({
           type: "POST",
           url: page,
           success: function(msg){
               $('#contentpane').html(msg);
           }
        });

    }

</script>


<?php

//footer
include('../footer.php');

?>
