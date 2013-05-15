<?php
/*
 * Cru Doctrine
 * Header
 * Campus Crusade for Christ
 */

if (!isset($_SESSION)) {
  ini_set('session.gc_maxlifetime', 7200);
  ini_set('session.gc_probability', 1);
  ini_set('session.gc_divisor', 100);
  session_start();
}

//page title
$title = 'Welcome';

//check for session
$loggedin    = false;
$email       = '';
$fname       = '';
$lname       = '';
$type        = '';
$status      = '';
$userMessage = '';

if(isset($_SESSION['email'])) {
    $loggedin   = true;
    $email      = $_SESSION['email'];
    $fname      = isset($_SESSION['fname']) ? $_SESSION['fname']    : '';
    $lname      = isset($_SESSION['lname']) ? $_SESSION['lname']    : '';
    $type       = isset($_SESSION['type']) ? $_SESSION['type']      : '';
    $status     = isset($_SESSION['status']) ? $_SESSION['status']  : '';
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
  <meta http-equiv="refresh" content="7200;url=logout.php" />

  <?php
    //title
    echo"<title>".$title." | Cru Doctrine</title>";
  ?>

  <!--JQUERY-->
  <!--core-->
  <!--script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script-->
  <script type="text/javascript" src="/jquery/datatables/media/js/jquery.js"></script>
  <!--ui-->
  <script type="text/javascript" src="/jquery/ui/js/jquery-ui-1.8.custom.min.js"></script>
  <link type="text/css" href="/jquery/ui/css/crudoctrine-grey/jquery-ui-1.8.1.custom.css" rel="Stylesheet" />
  <!--media-->
  <script type="text/javascript" src="/jquery/media/jquery.media.js"></script>
  <!--validation-->
  <script type="text/javascript" src="/jquery/validation/jquery.validate.min.js"></script>
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
        <?php
          if($loggedin) {
            //echo '<div class="option" ><a href="profile/">MY PROFILE</a></div>';
            echo '<div id="hometab" class="option">
                    <a id="home" href="/">HOME</a>
                  </div>
                   <div id="myprofiletab" class="option">
                    <a id="myprofile" href="/profile">MY PROFILE</a>
                  </div>
                   <div id="myworktab" class="option">
                    <a id="mywork" href="/work">MY WORK</a>
                  </div>
                  <!--div id="communitytab" class="option">
                    <a id="community" href="/">COMMUNITY</a>
                  </div>
                  <div id="resourcestab" class="option">
                    <a id="resources" href="/">RESOURCES</a>
                  </div-->';
            echo (($type == SUPER || $type == REGIONAL_ADMIN || $type == COACH) && $status == ACTIVE) ? '<div id="admintab" class="option"><a id="admin" href="/admin">ADMIN</a></div>' : '';
          }
        ?>
        <div id="abouttab" class="option" ><a id="about" href="/about.php">ABOUT</a></div>
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