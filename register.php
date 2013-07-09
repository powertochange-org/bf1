<?php
/*
 * Cru Doctrine
 * Register
 * Campus Crusade for Christ
 */

try {
  //get values
  $submit     = isset($_POST['submit'])        ? true                  : false;
  $ajax       = isset($_POST['ajax'])          ? true                  : false;

  $email      = isset($_POST['email'])         ? $_POST['email']       : '';
  $firstName  = isset($_POST['firstName'])     ? $_POST['firstName']   : '';
  $lastName   = isset($_POST['lastName'])      ? $_POST['lastName']    : '';
  $password   = isset($_POST['password'])      ? $_POST['password']    : '';
  $type       = isset($_POST['type'])          ? $_POST['type']        : '';
  $region     = isset($_POST['region'])        ? $_POST['region']      : '';
  $regDate    = isset($_POST['regDate'])       ? $_POST['regDate']     : '';
  $coach      = isset($_POST['coach'])         ? $_POST['coach']       : '';

  $errors     = isset($_POST['errors'])        ? $_POST['errors']      : '';

  require_once("config.inc.php"); 
  require_once("Database.singleton.php");
  require_once("function.inc.php");

  $password  = stripslashes($password);
  $firstName = stripslashes($firstName);
  $lastName = stripslashes($lastName);

  $coaches = null;
  $regions = null;
  $user_types = null;

  //initialize the database object
  $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
  $db->connect();

  //check for form submission
  if($submit) { //form was submitted, process data
    //create user
    //prepare query
    $data['Email']      = $email;
    $data['FName']      = $firstName;
    $data['LName']      = $lastName;
    //hash the supplied password with some salt
    $passwordHash = null;
    $passwordHash = hash("sha512", $password.$email);
    $data['Password']   = $passwordHash;
    $data['Type']       = $type;
    $data['Region']     = $region;
    $data['Reg_Date']   = date('Ymd');
    $data['Status'] = ACTIVE;

    //execute query
    $db->insert("user", $data);

    if ($coach != '') {
      //create coach
      //prepare query
      $data = array();
      $data['Coach'] = $coach;
      $data['Student'] = $email;
      $data['Year'] = date('Y');
      $data['Type'] = COACH;

      //execute query
      $db->insert("coach", $data);
    }

    //verify that user exists (login)
    $sql = "SELECT * FROM user WHERE Email = '".$db->escape($email)."' AND Password = '".$db->escape($passwordHash)."'";

    //get results
    $result = $db->query_first($sql);        

    //check result to verify login
    if(!$result == 0) { // login succeeded
        //log user in
        session_start();
        $_SESSION['email']  = $email;
        $_SESSION['fname']  = $result['FName'];
        $_SESSION['lname']  = $result['LName'];
        $_SESSION['type']   = $result['Type'];
        $_SESSION['region'] = $result['Region'];
        $_SESSION['status'] = $result['Status'];

        //if ajax, return user attributes as xml
        if ($ajax) {
            $db->close();
            header ("Location: /");
            exit();
        } 
        else {
            $db->close();
            header ("Location: /");
            exit();
        }
    } 
    else { //login failed
      //return errors
      $errors .= 'Registration failed. Please check that you provided all the required information.';

      //if ajax, return error
      if ($ajax) {
          echo 'error';
          $db->close();
          exit();
      }
    }
  }
  else { //get data for user creation

    //get coaches for selection
    $coaches = getActiveCoaches($db);

    //get regions for selection
    $regions = getRegions($db);

    //get user types for selection
    $user_types = getUserTypes($db);
  }
  $db->close();
} 
catch (PDOException $e) {
  echo $e->getMessage();
  exit();
}
?>

<link rel="stylesheet" type="text/css" media="screen" href="/css/login.css" />
<div id="register">
  <form id="formRegister" action="register.php" method="post">
    <fieldset id="user">
      <legend>Please Register</legend>
      <div>
        <label>First Name</label>
        <input type="text" id="firstName" placeholder="First Name" value="" />
        <a class="required"></a>
      </div>
      <div>
        <label>Last Name</label>
        <input type="text" id="lastName" placeholder="Last Name" value="" />
        <a class="required"></a>
      </div>
      <div>
        <label>Email</label>
        <input type="text" id="email" placeholder="Email Address" value="" />
        <a class="required"></a>
      </div>
      <div>
        <label>Password</label>
        <input type="password" id="password" placeholder="Password" value="" />
        <a class="required"></a>
      </div>
      <div>
        <label>Confirm Password</label>
        <input type="password" id="confirmPassword" placeholder="Confirm Password" value="" />
        <a class="required"></a>
      </div>
      <div>
        <label>Type</label>
        <select id="type">
          <option value="" 'selected'>Select User Type</option>
          <?php
            if(count($user_types) > 0) {
              foreach ($user_types as $row) {
                echo '<option value="'.$row['id'].'" >';
                echo $row['name'].'</option>';
              }
            }
          ?>
        </select>
        <a class="required"></a>
      </div>
      <div>
        <label>Region</label>
        <input id="regionSearch" class="typeahead" placeholder="Type Name" type="text">
        <input id="region" type="hidden">
        <a class="required"></a>
        <div id="help">
          (For Cru Interns, select your sending region.
          For all others, select "none".)
        </div>
      </div>
      <div>
        <label>Coach</label>
        <input id="coachSearch" class="typeahead" placeholder="Type Name" type="text">
        <input id="coach" type="hidden">
      </div>
    </fieldset>
    <fieldset id="feedback">
        <div id="errors">
            <?php echo $errors; ?>
        </div>
    </fieldset>
    <button type="submit" id="formRegisterSave" name="submit" class="ui-state-default ui-corner-all">Save<span class="ui-icon ui-icon-circle-triangle-e"></span></button>
    <button type="submit" id="formRegisterCancel" name="cancel" onclick="cancelFunc();return(false);" class="ui-state-default ui-corner-all">Cancel<span class="ui-icon ui-icon-circle-triangle-e"></span></button>
  </form>
</div>

<script type="text/javascript">
  //hide submit button
  /*$(function() {
      $('#formRegister #formRegisterSave').hide();
      $('#formRegister #formRegisterCancel').hide();
  });*/

  $('#formRegister #coachSearch').typeahead({
    name: 'Coach',
    valueKey: 'name',
    local: <?php echo json_encode($coaches); ?>,
    template: '<p><strong>{{name}}</strong> – {{region}}</p>',
    engine: Hogan
  }).on('typeahead:opened', function(event) {
    $('#formRegister #coach').val('');
    $('#formRegister #coachSearch').val('');
    $('#formRegister #coachSearch').typeahead('setQuery', '');
  }).on('typeahead:selected', function(event, datum) {
    $('#formRegister #coach').val(datum.id);
  }).on('typeahead:autocompleted', function(event, datum) {
    $('#formRegister #coach').val(datum.id);
  });

  $('#formRegister #regionSearch').typeahead({
    name: 'Region',
    valueKey: 'name',
    local: <?php echo json_encode($regions); ?>
  }).on('typeahead:opened', function(event) {
    $('#formRegister #region').val('');
    $('#formRegister #regionSearch').val('');
    $('#formRegister #regionSearch').typeahead('setQuery', '');
  }).on('typeahead:selected', function(event, datum) {
    $('#formRegister #region').val(datum.id);
  }).on('typeahead:autocompleted', function(event, datum) {
    $('#formRegister #region').val(datum.id);
  });

  function cancelFunc(){
        window.location.href = "/";
    }

  //validate form submission
  $('#formRegister').submit(function() {
    var submit = false;
    var errors = '';
    $('#formRegister #errors').html(errors);

    if ($('#formRegister #firstName').val().length == 0) {
      $('#formRegister #firstName').css('border-color', 'orange').siblings('a').css('display','');
      errors += '<div>Please enter a first name.</div>';
    }
    else {
      $('#formRegister #firstName').css('border-color', '').siblings('a').css('display','');
    }

    if ($('#formRegister #lastName').val().length == 0) {
      $('#formRegister #lastName').css('border-color', 'orange').siblings('a').css('display','');
      errors += '<div>Please enter a last name.</div>';
    }
    else {
      $('#formRegister #lastName').css('border-color', '').siblings('a').css('display','');
    }

    if ($('#formRegister #email').val().length == 0) {
      $('#formRegister #email').css('border-color', 'orange').siblings('a').css('display','');
      errors += '<div>Please enter an email address.</div>';
    }
    else {
      $('#formRegister #email').css('border-color', '').siblings('a').css('display','');
    }

    if ($('#formRegister #password').val().length == 0) {
      $('#formRegister #password').css('border-color', 'orange').siblings('a').css('display','');
      errors += '<div>Please enter a password.</div>';
    }
    else {
      $('#formRegister #password').css('border-color', '').siblings('a').css('display','');
    }

    if ($('#formRegister #confirmPassword').val().length == 0) {
      $('#formRegister #confirmPassword').css('border-color', 'orange').siblings('a').css('display','');
      errors += '<div>Please enter a confirm password.</div>';
    }
    else {
      $('#formRegister #confirmPassword').css('border-color', '').siblings('a').css('display','');
    }

    if (($('#formRegister #password').val().length > 0) && ($('#formRegister #confirmPassword').val().length > 0)) {
      if ($('#formRegister #password').val() != $('#formRegister #confirmPassword').val()) {
        $('#formRegister #password').css('border-color', 'orange').siblings('a').css('display','');
        $('#formRegister #confirmPassword').css('border-color', 'orange').siblings('a').css('display','');
        errors += '<div>The passwords do not match.</div>';
      }
      else {
        $('#formRegister #password').css('border-color', '').siblings('a').css('display','');
        $('#formRegister #confirmPassword').css('border-color', '').siblings('a').css('display','');
      }
    }

    if ($('#formRegister #type').val().length == 0) {
      $('#formRegister #type').css('border-color', 'orange').siblings('a').css('display','');
      errors += '<div>Please select a type.</div>';
    }
    else {
      $('#formRegister #type').css('border-color', '').siblings('a').css('display','');
    }

    if ($('#formRegister #region').val().length == 0) {
      if ($('#formRegister #regionSearch').val().length > 0) {
        $('#formRegister #regionSearch').css('border-color', 'orange');
        $('#formRegister #region').siblings('a').css('display','');
        errors += '<div>Please select a valid region.</div>';
      }
      else {
        $('#formRegister #regionSearch').css('border-color', 'orange');
        $('#formRegister #region').siblings('a').css('display','');
        errors += '<div>Please select a region.</div>';
      }
    }
    else {
      $('#formRegister #regionSearch').css('border-color', '');
      $('#formRegister #region').siblings('a').css('display','');
    }

    if (errors !== ''){
       $('#formRegister #errors').html(errors);
       submit = false;
    } else {
       submit = true;
    }

    if(submit) {
      $.ajax({
        url: 'register.php',
        type: 'POST',
        data: { 
          ajax        : true,
          submit      : true,
          firstName   : $('#formRegister #firstName').val(),
          lastName    : $('#formRegister #lastName').val(),
          email       : $('#formRegister #email').val(),
          password    : $('#formRegister #password').val(),
          type        : $('#formRegister #type').val(),
          region      : $('#formRegister #region').val(),
          coach       : $('#formRegister #coach').val()
        },
        dataType: "html",
        success: function(msg) {
          if(msg != 'error') {
            $('#loginbox #register').click();
            $('#header').html($(msg).find('#header').html());
            window.location.reload(true);
          } else {
            $('#register #errors').html('<div>Registration failed. Please check that you provided all the required information.</div>')
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