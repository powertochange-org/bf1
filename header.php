<?php
/*
 * Cru Doctrine
 * Header
 * Keith Roehrenbeck | Campus Crusade for Christ
 */

//check for session
$loggedin   = false;
$email      = '';
$fname      = '';
$lname      = '';
$type       = '';

session_start();

if(isset($_SESSION['email'])){
    $loggedin   = true;
    $email      = $_SESSION['email'];
    $fname      = $_SESSION['fname'];
    $lname      = $_SESSION['lname'];
    $type       = $_SESSION['type'];
    $userMessage= 'Welcome '.$fname.'!';
}

require_once("config.inc.php"); 
require_once("Database.singleton.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="description" content="Cru Doctrine" />
<meta name="robots" content="noindex" />

<?php
//title
echo"<title>".$title." | Cru Doctrine</title>";
?>

<!--JQUERY-->
<!--core-->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.js"></script>
<!--ui-->
<script type="text/javascript" src="/crudoctrine/jquery/ui/js/jquery-ui-1.8.custom.min.js"></script>
<link type="text/css" href="/crudoctrine/jquery/ui/css/crudoctrine-grey/jquery-ui-1.8.1.custom.css" rel="Stylesheet" />
<!--media-->
<script type="text/javascript" src="/crudoctrine/jquery/media/jquery.media.js"></script>

<!--END JQUERY-->

<!--CSS-->
<link rel="stylesheet" type="text/css" media="screen" href="/crudoctrine/CSS/layout.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/crudoctrine/CSS/print.css" />
<!--END CSS-->

</head>

<body>

<div id="container"><!--PAGE CONTAINER-->

    <div id="header"><!--HEADER-->
        <div id="logo"></div>
        <div id="title"></div>

	<div id="navbar"><!--NAVBAR-->

            <div id="userbox"><!--USERBOX-->

                <div id="loginDialog" >
                    <div id="form" class="shadow-medium corners-all">

                    </div>
                    <div id="tab"></div>
                </div>
                <div id="loginbox" class="ui-corner-top"><?php echo $loggedin ? $userMessage.' | <a href="/crudoctrine/logout.php" id="logout">LOG OUT</a>' : '<a href="/crudoctrine/login.php" id="login">LOG IN</a>'; ?></div>

            </div><!--END USERBOX-->
            <div class="option" ><a href="/crudoctrine/">WELCOME</a></div>
	    <!--<div class="option" ><a href="/crudoctrine/profile/">MY PROFILE</a></div>-->
	    <div class="option" ><a href="/crudoctrine/work/">MY WORK</a></div>
	    <div class="option" ><a href="#">COMMUNITY</a></div>
	    <div class="option" ><a href="#">RESOURCES</a></div>
            <?php echo $_SESSION['type']=='super' ? '<div class="option" ><a href="/crudoctrine/admin/">ADMIN</a></div>' : ''; ?>

	</div><!--ENDS NAVBAR-->

    </div><!--ENDS HEADER-->



<script type="text/javascript">

    $(function(){
       //check for anchor
       var anchor=window.location.hash;
       switch(anchor){
           case '#login':
               $('#loginbox #login').click();
               break;
       }
    });

    $('#loginbox #login').toggle(
        function(){
            login();
            return false;
        },
        function(){
            $('#loginbox').removeClass('active');
            $('#loginDialog').hide(100);
        }
    );

    $('#loginbox #logout').click(function(){
       logout();
    });

    function login(){
        $.ajax({
            url: "/crudoctrine/login.php",
            dataType: "html",
            type: "post",
            success: function(msg){
                //show login form
                $('#loginDialog #form').html(msg);
                $('#loginbox').addClass('active');
                $('#loginDialog').show(100);
           }
        });
    }

    function logout() {
        $.ajax({
            url: "/crudoctrine/logout.php",
            dataType: "html",
            type: "post",
            success: function(msg){
                //show user logged out
                $(msg).find('#loginbox').each(function(){
                   $('#loginbox').html($(this).html());
                });
            }
       });
    }

</script>