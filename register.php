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
      //$location   = isset($_POST['location'])      ? $_POST['location']    : '';
      $regDate    = isset($_POST['regDate'])       ? $_POST['regDate']     : '';
      $coach      = isset($_POST['coach'])         ? $_POST['coach']       : '';

      $errors     = isset($_POST['errors'])        ? $_POST['errors']      : '';

      require_once("config.inc.php"); 
      require_once("Database.singleton.php");

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
          $data['Password']   = $password;
          $data['Type']       = $type;
          $data['Region']     = $region;
          //$data['Loc']        = $location;
          $data['Reg_Date']   = date('Ymd');
          $data['Reg_Status'] = ACTIVE;

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
          $sql = "SELECT * FROM user WHERE Email = '".$db->escape($email)."' AND Password = '".$db->escape($password)."'";

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
              //$_SESSION['loc']    = $result['Loc'];

              //if ajax, return user attributes as xml
              if ($ajax) {
                  $db->close();
                  header ("Location: /");
                  exit();
              } else {
                  $db->close();
                  header ("Location: /");
                  exit();
              }
          } else { //login failed
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
            $sql     =  "SELECT Email, FName, LName
                         FROM  user
                         WHERE Type < ".STUDENT."
                         ORDER BY LName;";

            $coaches = $db->fetch_array($sql);

            //get regions for selection
            $sql     =  "SELECT ID, Name
                         FROM  region
                         ORDER BY Name;";

            $regions = $db->fetch_array($sql);

            //get user types for selection
            $sql     =  "SELECT ID, Name
                         FROM  user_type
                         WHERE ID > ".REGIONAL_ADMIN."
                         ORDER BY Name;";

            $user_types = $db->fetch_array($sql);
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
    <form action="register.php" method="post">
        <fieldset id="user">
          <legend>Please Register</legend>
          <div>
            <label>First Name</label>
            <input type="text" name="firstName" value="<?php echo $firstName; ?>" /><a class="required"></a>
          </div>
          <div>
            <label>Last Name</label>
            <input type="text" name="lastName" value="<?php echo $lastName; ?>" /><a class="required"></a>
          </div>
          <div>
            <label>Email</label>
            <input type="text" name="email" value="<?php echo $email; ?>" /><a class="required"></a>
          </div>
          <div>
              <label>Password</label>
              <input type="password" name="password" value="<?php echo $password; ?>" /><a class="required"></a>
          </div>
          <div>
              <label>Confirm Password</label>
              <input type="password" name="confirmPassword" value="<?php echo $password; ?>" /><a class="required"></a>
          </div>
          <div>
             <label>Type</label>
             <select name="type">
                 <option value="" 'selected'>Select User Type</option>
                 <?php
                   if(count($user_types) > 0){
                     foreach ($user_types as $row){
                       echo '<option value="'.$row['ID'].'" >';
                       echo $row['Name'].'</option>';
                     }
                   }
                 ?>
             </select><a class="required"></a>
          </div>
          <div>
              <label>Region</label>
              <select name="region">
                <option value="" 'selected'>Select A Region</option>
            <?php
                if(count($regions) > 0){
                    foreach ($regions as $row){
                        echo '<option value="'.$row['ID'].'" >';
                        echo $row['Name'].'</option>';
                    }
                }
            ?>
            </select><a class="required"></a>
          </div>
          <!--div>
            <label>Location</label>
            <input type="text" name="location" value="<?php //echo $location; ?>" />
          </div-->
          <div>
              <label>Coach</label>
              <select name="coach">
                <option value="" 'selected'>Select A Coach</option>
            <?php
                if(count($coaches) > 0){
                    foreach ($coaches as $row){
                        echo '<option value="'.$row['Email'].'" >';
                        echo $row['FName'].' '.$row['LName'].'</option>';
                    }
                }
            ?>
            </select>
          </div>
        </fieldset>

        <fieldset id="feedback">
            <div id="errors">
                <?php echo $errors; ?>
            </div>
        </fieldset>

        <button type="submit" name="submit" class="ui-state-default ui-corner-all">Save<span class="ui-icon ui-icon-circle-triangle-e"></span></button>
        <!--button type="submit" name="cancel" onclick="cancelFunc();return(false);" class="ui-state-default ui-corner-all">Cancel<span class="ui-icon ui-icon-circle-triangle-e"></span></button-->

    </form>
</div>

<script type="text/javascript">
    //hide submit button
    /*$(function() {
        $('form button:[name=submit]').hide();
        $('form button:[name=cancel]').hide();
    });*/

    function cancelFunc(){
        window.location.href = "/";
    }

    //validate form submission
    $('#register form').submit(function(){
        var submit = false;
        var errors = '';
        $('#errors').html(errors);

        if ($('#register form input:[name=firstName]').val().length == 0) {
            $('#register form input:[name=firstName]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please enter a first name.</div>';
        }

        if ($('#register form input:[name=lastName]').val().length == 0) {
            $('#register form input:[name=lastName]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please enter a last name.</div>';
        }

        if ($('#register form input:[name=email]').val().length == 0) {
            $('#register form input:[name=email]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please enter an email address.</div>';
        }

        if ($('#register form input:[name=password]').val().length == 0) {
            $('#register form input:[name=password]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please enter a password.</div>';
        }

        if ($('#register form input:[name=confirmPassword]').val().length == 0) {
            $('#register form input:[name=confirmPassword]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please enter a confirm password.</div>';
        }

        if ($('#register form input:[name=password]').val() != $('#register form input:[name=confirmPassword]').val()) {
            $('#register form input:[name=password]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            $('#register form input:[name=confirmPassword]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>The password and confirm password do not match.</div>';
        }

        if ($('#register form select:[name=type]').val().length == 0) {
            $('#register form select:[name=type]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please select a type.</div>';
        }

        if ($('#register form select:[name=region]').val().length == 0) {
            $('#register form select:[name=region]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please select a region.</div>';
        }

        if (errors !== ''){
           $('#register form #errors').html(errors);
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
                    firstName   : $('#register form input:[name=firstName]').val(),
                    lastName    : $('#register form input:[name=lastName]').val(),
                    email       : $('#register form input:[name=email]').val(),
                    password    : $('#register form input:[name=password]').val(),
                    type        : $('#register form select:[name=type]').val(),
                    region      : $('#register form select:[name=region]').val(),
                    //location    : $('#register form input:[name=location]').val(),
                    coach       : $('#register form select:[name=coach]').val()
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