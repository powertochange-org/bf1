<?php
/*
 * Cru Doctrine
 * Reset Password
 * Campus Crusade for Christ
 */
$auth = false;

session_start();
if(isset($_SESSION['email'])){
    $auth = true;
}
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

    //$curDate = date("Y-m-d H:i:s");
    //if ($SQL = $mySQL->prepare("SELECT `UserID` FROM `recoveryemails_enc` WHERE `Key` = ? AND `UserID` = ? AND `expDate` >= ?"))

    $sql = null;
    $sql = "SELECT * FROM user WHERE Email = '".$db->escape($email)."'";      
    //get results
    $result = null;
    $result = $db->query_first($sql);

    //check result to verify the supplied key
    if(!$result == 0) { //valid key
      //1. login the user (session)

      //2. remove the record from the password_reset table

      //3. redirect the user to the Change Password page
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
      $data = array();
      $data['Email']        = $email;
      $data['Key']          = $key;
      $data['Expiry_Date']  = $expDate;
      //execute query
      $db->insert("password_reset", $data);

      //3. send the password reset email to the user
      $transport = null;
      $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, "ssl")
        ->setUsername(ADMIN_EMAIL_USERNAME)
        ->setPassword(ADMIN_EMAIL_PASSWORD);

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

      $emailMessage = Swift_Message::newInstance('Test Subject')
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
            <input type="input" name="email" value=""/>
          </div>
        </fieldset>
        <fieldset id="feedback">
            <div id="message"><?php echo $message; ?></div>
        </fieldset>
        <button type="submit" name="submit" class="ui-corner-right corners-all shadow-light button" value="submit"><span class="ui-icon ui-icon-pencil"></span>Reset Password</button>
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

    if ($('#resetPassword input:[name=email]').val().length == 0) {
      $('#resetPassword input:[name=email]').css('border-color', 'orange').siblings('a').css('display','inline-block');
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