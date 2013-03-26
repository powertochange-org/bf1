<?php
/*
 * Cru Doctrine
 * My Profile - Change Password
 * Campus Crusade for Christ
 */

try {
  //get session values
  $email             = isset($_SESSION['email'])      ? $_SESSION['email']    : '';

  //get post values
  $submit            = isset($_POST['submit'])        ? true                  : false;
  $ajax              = isset($_POST['ajax'])          ? true                  : false;
  $password          = isset($_POST['password'])      ? $_POST['password']    : '';
  $confirmPassword   = isset($_POST['password'])      ? $_POST['password']    : '';
  $errors            = isset($_POST['errors'])        ? $_POST['errors']      : '';

  //initialize the database object
  $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
  $db->connect();

  if($submit) { //form was submitted, process data
    //update password
    //prepare query
    $data = null;
    $data = array();
    //hash the supplied password with some salt
    $passwordHash = null;
    $passwordHash = hash("sha512", $password.$email);
    $data['Password'] = $passwordHash;

    //execute query
    $db->update("user", $data, "Email = '".$db->escape($email)."'");
    $errors = "Password successfully changed!";
  }

  $db->close();
}
catch (PDOException $e) {
    echo $e->getMessage();
    exit();
}
?>
<form id="formChangePassword" action="" method="post">
  <button type="submit" name="submit" class="ui-corner-right corners-all shadow-light button" value="submit"><span class="ui-icon ui-icon-pencil"></span>Change Password</button>
  <fieldset id="changePassword">
    <div>
      <label>Password:</label>
      <input type="password" name="password" value=""/>
    </div>
    <div>
      <label>Confirm Password:</label>
      <input type="password" name="confirmPassword" value=""/>
    </div>
  </fieldset>
  <fieldset id="feedback">
      <div id="errors"><?php echo $errors; ?></div>
  </fieldset>
</form>

<script type="text/javascript">
   //validate form submission
  $('#formChangePassword').submit(function() {
    var submit = false;
    var errors = '';
    $('#feedback #errors').html(errors);

    if ($('#changePassword input:[name=password]').val().length == 0) {
      $('#changePassword input:[name=password]').css('border-color', 'orange').siblings('a').css('display','inline-block');
      errors += '<div>Please enter a password.</div>';
    }

    if ($('#changePassword input:[name=confirmPassword]').val().length == 0) {
      $('#changePassword input:[name=confirmPassword]').css('border-color', 'orange').siblings('a').css('display','inline-block');
      errors += '<div>Please enter a confirm password.</div>';
    }

    if ($('#changePassword input:[name=password]').val() != $('#changePassword input:[name=confirmPassword]').val()) {
      $('#changePassword input:[name=password]').css('border-color', 'orange').siblings('a').css('display','inline-block');
      $('#changePassword input:[name=confirmPassword]').css('border-color', 'orange').siblings('a').css('display','inline-block');
      errors += '<div>The password and confirm password do not match.</div>';
    }

    if (errors !== ''){
      $('#feedback #errors').html(errors);
      return false;
    }
    else {
      return true;
    }
  });
</script>