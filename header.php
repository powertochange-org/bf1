<?php
/*
 * Cru Doctrine
 * Header
 * Campus Crusade for Christ
 */

//if (!isset ($_COOKIE['PHPSESSID'])) {
    session_start();
//}

//page title
$title = 'Welcome';

//check for session
$loggedin    = false;
$email       = '';
$fname       = '';
$lname       = '';
$type        = '';
$userMessage = '';

if(isset($_SESSION['email'])) {
    $loggedin   = true;
    $email      = $_SESSION['email'];
    $fname      = isset($_SESSION['fname']) ? $_SESSION['fname']    : '';
    $lname      = isset($_SESSION['lname']) ? $_SESSION['lname']    : '';
    $type       = isset($_SESSION['type']) ? $_SESSION['type']      : '';
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
<script type="text/javascript" src="/jquery/ui/js/jquery-ui-1.8.custom.min.js"></script>
<link type="text/css" href="/jquery/ui/css/crudoctrine-grey/jquery-ui-1.8.1.custom.css" rel="Stylesheet" />
<!--media-->
<script type="text/javascript" src="/jquery/media/jquery.media.js"></script>
<!--END JQUERY-->

<!--CSS-->
<link rel="stylesheet" type="text/css" media="screen" href="/css/layout.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/css/print.css" />
<!--END CSS-->
</head>

<body>
<!--PAGE CONTAINER-->
<div id="container">
    <!--HEADER-->
   <div id="header">
    <a href="/"><div id="logo"></div></a>
    <div id="title"></div>
    <div id="navbar">
        <!--NAVBAR-->
        <div id="userbox">
            <!--USERBOX-->
            <div id="loginDialog">
                <div id="form" class="shadow-medium corners-all"></div>
                <div id="tab"></div>
            </div>
            <div id="registerDialog">
                <div id="form" class="shadow-medium corners-all"></div>
                <div id="tab"></div>
            </div>
            <div id="loginbox" class="ui-corner-top">
                <?php echo $loggedin ? $userMessage.' | <a href="/logout.php" id="logout">LOG OUT</a>' : '<a href="/login.php" id="login">LOG IN</a> | <a href="/register.php" id="register">REGISTER</a>'; ?>
            </div>
        </div>
        <!--END USERBOX-->
        <div class="option" ><a href="about.php">ABOUT</a></div>
        <?php
          if($loggedin) {
            //echo '<div class="option" ><a href="profile/">MY PROFILE</a></div>';
            echo '<div class="option">
                    <a href="/">HOME</a>
                  </div>
                   <div class="option">
                    <a href="/work">MY WORK</a>
                  </div>
                  <!--div class="option">
                    <a href="/">COMMUNITY</a>
                  </div>
                  <div class="option">
                    <a href="/">RESOURCES</a>
                  </div-->';
            echo $type=='super' ? '<div class="option"><a href="/admin">ADMIN</a></div>' : '';
         }
        ?>
    </div>
    <!--ENDS NAVBAR-->
   </div>
   <!--ENDS HEADER-->

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

    $('#loginbox #register').toggle(
        function(){
            register();
            return false;
        },
        function(){
            $('#loginbox').removeClass('active');
            $('#registerDialog').hide(100);
        }
    );

    $('#loginbox #logout').click(function(){
       logout();
    });

    function login(){
        $.ajax({
            url: "login.php",
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
            url: "logout.php",
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

    function register() {
        $.ajax({
            url: "register.php",
            dataType: "html",
            type: "post",
            success: function(msg){
                //show register form
                $('#registerDialog #form').html(msg);
                $('#loginbox').addClass('active');
                $('#registerDialog').show(100);
           }
        });
    }
</script>