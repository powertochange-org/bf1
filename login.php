<?php
/*
 * Cru Doctrine
 * Login
 * Campus Crusade for Christ
 */

try {
  //get values
  $submit     = isset($_POST['submit'])      ? true                  : false;
  $ajax       = isset($_POST['ajax'])        ? true                  : false;

  $email      = isset($_POST['email'])       ? $_POST['email']       : '';
  $password   = isset($_POST['password'])    ? $_POST['password']    : '';
  $rememberMe = isset($_POST['rememberMe'])  ? $_POST['rememberMe']  : false;
  $redir      = isset($_POST['redir'])       ? $_POST['redir']       : '';

  $errors     = isset($_POST['errors'])      ? $_POST['errors']      : '';

  require_once("config.inc.php"); 
  require_once("Database.singleton.php");

  //check for form submission
  if($submit) { //form was submitted, process data
    //initialize the database object
    $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
    $db->connect();
    
    $sql = "SELECT * FROM user WHERE Email = '".$db->escape($email)."'";
    //get results
    $result = null;
    $result = $db->query_first($sql);
    $storedPassword = null;
    $storedPassword = $result['Password'];

    //hash the supplied password with some salt
    $passwordHash = null;
    $passwordHash = hash("sha512", $password.$email);

    //check result to verify login
    if($storedPassword == $passwordHash) { //success
      //log user in
      session_start();
      $_SESSION['email']  = $email;
      $_SESSION['fname']  = $result['FName'];
      $_SESSION['lname']  = $result['LName'];
      $_SESSION['type']   = $result['Type'];
      $_SESSION['region'] = $result['Region'];
      $_SESSION['status'] = $result['Status'];

      //$_SESSION['documentRoot']  = $_SERVER['REQUEST_URI'];

      if($rememberMe) {
        $year = time() + 31536000;
        setcookie('remember_me', $email, $year);
      }
      elseif(!$rememberMe) {
        if(isset($_COOKIE['remember_me'])) {
          $past = time() - 100;
          setcookie(remember_me, gone, $past);
        }
      }

      //if ajax, return user attributes as xml
      if ($ajax) {
        header ("Location: /");
      }
      else {
        header ("Location: /");
      }
    }
    else { //fail
      //return errors
      $errors .= 'Login failed. Please check your email and password.';

      //if ajax, return error
      if ($ajax) {
        echo 'error';
        exit();
      }
    }
    $db->close();
  }
} 
catch (PDOException $e) {
  echo $e->getMessage();
  exit();
}
?>

<link rel="stylesheet" type="text/css" media="screen" href="/css/login.css" />
<div id="login">
  <form id="formLogin" action="login.php" method="post">
    <fieldset id="credentials">
      <legend>Please Login</legend>
      <div>
        <label>Email</label>
        <input type="text" id="email" value="<?php echo $_COOKIE['remember_me']; ?>" /><a class="required"></a>
      </div>
      <div>
        <label>Password</label>
        <input type="password" id="password" value="" /><a class="required"></a>
      </div>
      <div>
        <label>Remember Me</label>
        <input type="checkbox" id="rememberMe"
          <?php 
            if(isset($_COOKIE['remember_me'])) {
              echo 'checked="checked"';
            }
            else {
              echo '';
            }
          ?>/>
      </div>
    </fieldset>
    <fieldset id="feedback">
      <div id="errors"><?php echo $errors; ?></div>
    </fieldset>
    <button type="submit" id="formLoginLogin" class="ui-state-default ui-corner-all">Login<span class="ui-icon ui-icon-circle-triangle-e"></span></button>
  </form>
  <div id="passwordReset">
    <a href="/profile/reset_password.php">Please reset my password.</a>
  </div>
</div>

<script type="text/javascript">
    //hide submit button
    /*$(function() {
        $('#formLogin #formLoginLogin').hide();
    });*/

    //validate form submission
    $('#login form').submit(function() {
      var submit = false;
      var errors = '';

      if ($('#login #email').val().length == 0) {
        $('#login #email').css('border-color', 'orange');
        errors += '<div>Please enter your email.</div>';
      }

      if ($('#login #password').val().length == 0) {
        $('#login #password').css('border-color', 'orange');
        errors += '<div>Please enter your password.</div>';
      }

      if (errors !== '') {
        $('#login #errors').html(errors);
        submit = false;
      } 
      else {
        submit = true;
      }

      if(submit) {
        $.ajax({
          url: 'login.php',
          type: 'POST',
          data: {
              ajax       : true,
              submit     : true,
              email      : $('form #email').val(),
              password   : $('form #password').val(),
              rememberMe : $('form #rememberMe').val()
          },
          dataType: "html",
          success: function(msg) {
            if(msg != 'error') {
              $('#loginbox  #login').click();
              $('#header').html($(msg).find('#header').html());
              window.location.reload(true);
            } 
            else {
              $('#login #errors').html('<div>Login failed. Please check your email and password.</div>')
            }
          }
        });
      }
      return false;
    });

    //jquery class interaction states
    $('button').addClass('ui-state-default');

    $('.ui-state-default').hover(
      function(){
        $(this).addClass("ui-state-hover");
      },
      function(){
        $(this).removeClass("ui-state-hover");
      }
    );
</script>