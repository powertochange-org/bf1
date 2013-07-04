<?php
/*
 * Cru Doctrine
 * Reset Password
 * Campus Crusade for Christ
 */

ob_start();
//header
include('../header.php');

require_once("../config.inc.php"); 
require_once("../Database.singleton.php");
require_once("../function.inc.php");
require_once("../swift/lib/swift_required.php");

//page title
$title = null;
$title = 'Reset Password';

try {
  //get url values
  $key               = isset($_GET['key'])            ? $_GET['key']          : '';

  //get post values
  $submit            = isset($_POST['submit'])        ? true                  : false;
  $email             = isset($_POST['email'])         ? $_POST['email']       : '';
  $message           = isset($_POST['message'])       ? $_POST['message']     : '';

  //initialize the database object
  $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
  $db->connect();

  if($key != '') { //key was submitted
    //0. validate the key
    //prepare query
    $sql = null;
    $sql = "SELECT * FROM password_reset WHERE Reset_Key = '".$db->escape($key)."'";
    //get results
    $result = null;
    $result = $db->query_first($sql);

    //check result to verify the supplied key
    if($result != 0) {
      //check the expiry date on the key
      $expiryDate = $result['Expiry_Date'];
      if(strtotime($expiryDate) > time()) {
        //1. login the user (session)
        //prepare query
        $email = $result['Email'];
        $sql = null;
        $sql = "SELECT * FROM user WHERE Email = '".$db->escape($email)."'";
        //get results
        $result = null;
        $result = $db->query_first($sql);
        $_SESSION['email']  = $email;
        $_SESSION['fname']  = $result['FName'];
        $_SESSION['lname']  = $result['LName'];
        $_SESSION['type']   = $result['Type'];
        $_SESSION['region'] = $result['Region'];

        //2. delete the record from the password_reset table
        $sql = null;
        $sql = "DELETE FROM password_reset WHERE Reset_Key = '".$db->escape($key)."'";
        $db->query($sql);
        
        //3. redirect the user to the Change Password page
        header ("Location: /profile/?p=".CHANGE_PASSWORD);
      }
      else {
        //delete the stale password_reset record
        $sql = "DELETE FROM password_reset WHERE Reset_Key = '".$db->escape($key)."'";
        $db->query($sql);
        $message = "The password reset key you submitted has expired.  Please request another password reset.";
      }
    }
    else {
      $message = "The password reset key you submitted has expired.  Please request another password reset.";
    }
  }

  if($submit) { //form was submitted, process data
    //0. validate that the email submitted is a valid user
    //prepare query
    $sql = null;
    $sql = "SELECT * FROM user WHERE Email = '".$db->escape($email)."'";      
    //get results
    $result = null;
    $result = $db->query_first($sql);

    //check result to verify the supplied email
    if(!$result == 0) { //valid email
      //1. generate the key
      $firstName = null;
      $firstName = $result['FName'];
      $expFormat = null;
      $expFormat = mktime(date("H"), date("i"), date("s"), date("m")  , date("d")+3, date("Y"));
      $expDate = null;
      $expDate = date("Y-m-d H:i:s",$expFormat);
      $key = null;
      $key = md5($firstName.$email.rand(0,10000).$expDate);

      //2. insert a record into the password_reset table
      //prepare query
      $sql = null;
      $sql = "INSERT INTO password_reset VALUES ('".$db->escape($email)."','".$db->escape($key)."','".$db->escape($expDate)."') ON DUPLICATE KEY UPDATE Reset_Key = '".$db->escape($key)."', Expiry_Date = '".$db->escape($expDate)."'";
      //execute query
      $db->query($sql);

      //3. send the password reset email to the user
      $transport = null;
      if(SMTP_SSL == '') {
        $transport = Swift_SmtpTransport::newInstance(SMTP_SERVER, SMTP_PORT)
          ->setUsername(ADMIN_EMAIL_USERNAME)
          ->setPassword(ADMIN_EMAIL_PASSWORD);
      }
      else {
        $transport = Swift_SmtpTransport::newInstance(SMTP_SERVER, SMTP_PORT, SMTP_SSL)
          ->setUsername(ADMIN_EMAIL_USERNAME)
          ->setPassword(ADMIN_EMAIL_PASSWORD);
      }

      $mailer = null;
      $mailer = Swift_Mailer::newInstance($transport);

      $passwordLink = null;
      $passwordLink = "http://". $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT']."/profile/reset_password.php?key=" . $key;
      $emailMessageBody = null;
      $emailMessageBody  = "Dear ".$firstName.":\r\n\n";
      $emailMessageBody .= "Please visit the following link to reset your password:\r\n";
      $emailMessageBody .= "-----------------------\r\n";
      $emailMessageBody .= $passwordLink . "\n";
      $emailMessageBody .= "-----------------------\r\n";
      $emailMessageBody .= "Please be sure to copy the entire link into your browser. The link will expire after 3 days for security reasons.\r\n\r\n";
      $emailMessageBody .= "Regards,\r\n\n";
      $emailMessageBody .= ADMIN_EMAIL_FULLNAME;

      $emailMessage = Swift_Message::newInstance('Password Reset Request')
        ->setFrom(array(ADMIN_EMAIL_USERNAME => ADMIN_EMAIL_FULLNAME))
        ->setTo(array($email))
        ->setBody($emailMessageBody);

      $result = $mailer->send($emailMessage);

      $message = "A email containing a link to reset your password has been sent to the address provided.";
    }
    else {
      $message = "A email containing a link to reset your password has been sent to the address provided.";
    }
  }

  $db->close();
}
catch (PDOException $e) {
  echo $e->getMessage();
  exit();
}
?>

<link rel="stylesheet" type="text/css" media="screen" href="profile.css" />
<div id="content">
  <div id="profile">
    <div id="pagetitle">
        <?php echo $title?>
    </div>
    <div id="leftmenu">
    </div>
    <div id="contentpane">
      <form id="formResetPassword" action="" method="post">
        <fieldset id="resetPassword">
          <div id="instructions">
            <p>Please enter your email address below and click the 'Reset Password' button.</p>
          </div>
           <div>
            <label>Email:</label>
            <input type="input" id="email" value=""/>
          </div>
        </fieldset>
        <fieldset id="feedback">
          <div id="message">
            <?php echo $message; ?>
          </div>
        </fieldset>
        <button type="submit" id="submit" class="ui-corner-right corners-all shadow-light button" value="submit"><span class="ui-icon ui-icon-pencil"></span>Reset Password</button>
      </form>
    </div>
  </div>
</div>

<script type="text/javascript">
   //validate form submission
  $('#formResetPassword').submit(function() {
    var submit = false;
    var errors = '';
    $('#feedback #message').html(errors);

    if ($('#formResetPassword #email').val().length == 0) {
      $('#formResetPassword #email').css('border-color', 'orange').siblings('a').css('display','inline-block');
      errors += '<div>Please enter an email address.</div>';
    }

    if (errors !== ''){
      $('#feedback #message').html(errors);
      return false;
    }
    else {
      return true;
    }
  });
</script>
<?php
  //footer
  include('../footer.php');
?>