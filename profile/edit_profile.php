<?php
/*
 * Cru Doctrine
 * Admin - Edit Profile
 * Campus Crusade for Christ
 */

try {
  session_start();

  //get values
  $submit     = isset($_POST['submit'])        ? true                  : false;
  $ajax       = isset($_POST['ajax'])          ? true                  : false;

  $id         = isset($_SESSION['email'])      ? $_SESSION['email']    : '';
  $email      = isset($_POST['email'])         ? $_POST['email']       : '';
  $firstName  = isset($_POST['firstName'])     ? $_POST['firstName']   : '';
  $lastName   = isset($_POST['lastName'])      ? $_POST['lastName']    : '';
  //$password   = isset($_POST['password'])    ? $_POST['password']    : '';
  //$type       = isset($_POST['type'])        ? $_POST['type']        : '';
  $region     = isset($_POST['region'])        ? $_POST['region']      : '';
  $coach      = isset($_POST['coach'])         ? $_POST['coach']       : '';

  $errors     = isset($_POST['errors'])   ? $_POST['errors'] : '';

  $coaches = array();
  $regions = array();

  require_once("../config.inc.php"); 
  require_once("../Database.singleton.php");

  //$password  = stripslashes($password);
  $firstName = stripslashes($firstName);
  $lastName = stripslashes($lastName);

  //initialize the database object
  $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
  $db->connect();

  //check for form submission
  if($submit) { //form was submitted, process data
    //determine whether the email address was changed
    if($id != $email) {
      //update the foreign keys
      //disable foreign key constraints
      $db->query('SET foreign_key_checks = 0');

      //update note(s)
      $data = array();
      $data['Email'] = $email;
      //execute query
      $db->update("note", $data, "Email = '".$db->escape($id)."'");
            
      //update progress
      //execute query
      $db->update("progress", $data, "Email = '".$db->escape($id)."'");

      //update response(s)
      //execute query
      $db->update("response", $data, "Email = '".$db->escape($id)."'");

      //update coach
      $data = null;
      $data = array();
      $data['Student'] = $email;
      //execute query
      $db->update("coach", $data, "Student = '".$db->escape($id)."'");

      $data = null;
      $data = array();
      $data['Coach'] = $email;
      //execute query
      $db->update("coach", $data, "Coach = '".$db->escape($id)."'");

      //enable foreign key constraints
      $db->query('SET foreign_key_checks = 1');
    }

    //update user
    //prepare query
    $data = null;
    $data = array();
    $data['Email']      = $email;
    $data['FName']      = $firstName;
    $data['LName']      = $lastName;
    //$data['Password']   = $password;
    //$data['Type']       = $type;
    $data['Region']     = $region;

    //execute query
    $db->update("user", $data, "Email = '".$db->escape($id)."'");

    //determine whether this student already has a coach
    $sql     = "SELECT COUNT(*) from coach where Student = '".$db->escape($email)."'";
    $result  = $db->query_first($sql);

    if ($result['COUNT(*)'] > 0) {
      if ($coach != '') {
        //update coach
        //prepare query
        $data = array();
        $data['Coach'] = $coach;

        //execute query
        $db->update("coach", $data, "Student = '".$db->escape($email)."'");
      }
      else {
        //delete coach record(s)
        $sql = "DELETE FROM coach WHERE Student = '".$db->escape($email)."'";
        $db->query($sql);
      }
    }
    else {
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
    }

    //if ajax, return user attributes as xml
    if ($ajax) {
      //get name of Region
      $sql = "SELECT Name
              FROM  region
              WHERE ID = ".$region.";";

      $regionName = $db->query_first($sql)['Name'];

      //get coach for user
      $sql = "SELECT CONCAT(u.FName, ' ', u.LName) AS FullName
              FROM  user u
              INNER JOIN coach c ON u.Email = c.Coach
              WHERE c.Student = '".$db->escape($email)."';";

      $coachName = $db->query_first($sql)['FullName'];

      header('Content-Type: application/xml; charset=ISO-8859-1');
      echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
      echo '<user>';
      echo '<email>'      .$email.       '</email>';
      echo '<firstName>'  .$firstName.   '</firstName>';
      echo '<lastName>'   .$lastName.    '</lastName>';
      echo '<region>'     .$region.      '</region>';
      echo '<regionName>' .$regionName.  '</regionName>';
      echo '<coach>'      .$coach.       '</coach>';
      echo '<coachName>'  .$coachName.   '</coachName>';
      echo '</user>';

      //Update the session variables with the new values
      $_SESSION['email']  = $email;
      $_SESSION['fname']  = $firstName;
      $_SESSION['lname']  = $lastName;
      //$_SESSION['type']   = $type;
      $_SESSION['region'] = $region;

      $db->close();
      exit();
    }
  }
  else { //get data for profile edit
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
  }

  $db->close();
} 
catch (PDOException $e) {
    echo $e->getMessage();
    exit();
}
?>

<form id="formEditProfile" action="" method="post">
    <h2>Edit Profile</h2>
    <fieldset id="editUser">
      <div>
        <label>First Name</label>
        <input type="text" name="firstName" value="<?php echo $firstName;?>" class="required"/>
      </div>
      <div>
        <label>Last Name</label>
        <input type="text" name="lastName" value="<?php echo $lastName;?>" class="required"/>
      </div>
      <div>
        <label>Email</label>
        <input type="text" name="email" value="<?php echo $email;?>" class="required"/>
      </div>
      <!--div>
        <label>Password:</label>
        <input type="password" name="password" value="<?php //echo $password;?>" readonly/>
      </div>
      <div>
        <label>Confirm Password:</label>
        <input type="password" name="confirmPassword" value="<?php //echo $password;?>" readonly/>
      </div-->
      <div>
          <label>Region</label>
          <select name="region" class="required">
            <option value="" 'selected' >Select A Region</option>
            <?php
              if(count($regions) > 0) {
                foreach ($regions as $row) {
                  echo '<option value="'.$row['ID'].'"';
                  echo $region == $row['ID'] ? ' selected>' : ''.'>';
                  echo $row['Name'].'</option>';
                }
              }
            ?>
        </select>
      </div>
      <div>
          <label>Coach</label>
          <select name="coach">
            <option value="" 'selected' >-- None --</option>
            <?php
              if(count($coaches) > 0) {
                foreach ($coaches as $row) {
                  echo '<option value="'.$row['Email'].'"';
                  echo $coach == $row['Email'] ? ' selected>' : ''.'>';
                  echo $row['FName'].' '.$row['LName'].'</option>';
                }
              }
            ?>
        </select>
      </div>
    </fieldset>
    <fieldset id="feedback">
        <div id="errors"><?php echo $errors; ?></div>
    </fieldset>
    <button id="formEditProfileCancel" name="cancel" type="button">Cancel</button>
    <button id="formEditProfileOk" name="submit" type="submit">Save</button>
</form>

<script type="text/javascript">
    //hide submit button
    $(function() {
        $('form button:[name=submit]').hide();
        $('form button:[name=cancel]').hide();
    });

    //validate form submission
    $('#formEditProfile').submit(function() {
        var submit = false;
        var errors = '';
        $('#errors').html(errors);

        if ($('#editUser input:[name=firstName]').val().length == 0) {
            $('#editUser input:[name=firstName]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please enter a first name.</div>';
        }

        if ($('#editUser input:[name=lastName]').val().length == 0) {
            $('#editUser input:[name=lastName]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please enter a last name.</div>';
        }

        if ($('#editUser input:[name=email]').val().length == 0) {
            $('#editUser input:[name=email]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please enter an email address.</div>';
        }

        if ($('#editUser select:[name=region]').val().length == 0) {
            $('#editUser select:[name=region]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please select a region.</div>';
        }

        if (errors !== ''){
           $('#feedback #errors').html(errors);
           submit = false;
        } else {
           submit = true;
        }

        if(submit) {
          $.ajax({
              url: 'edit_profile.php',
              type: 'POST',
              data: {
                ajax        : true,
                submit      : true,
                firstName   : $('#editUser input:[name=firstName]').val(),
                lastName    : $('#editUser input:[name=lastName]').val(),
                email       : $('#editUser input:[name=email]').val(),
                region      : $('#editUser select:[name=region]').val(),
                coach       : $('#editUser select:[name=coach]').val()
              },
              dataType: "xml",
              success: function(xml) {
                  $(xml).find('user').each(function() {
                    //get values
                    var email      = $(this).find('email').text();
                    var firstName  = $(this).find('firstName').text();
                    var lastName   = $(this).find('lastName').text();
                    var region     = $(this).find('region').text();
                    var regionName = $(this).find('regionName').text();
                    var coach      = $(this).find('coach').text();
                    var coachName  = $(this).find('coachName').text();

                    //update the form values
                    $('#viewUser input:[name=firstName]').val(firstName);
                    $('#viewUser input:[name=lastName]').val(lastName);
                    $('#viewUser input:[name=email]').val(email);
                    $('#viewUser input:[name=region]').val(region);
                    $('#viewUser input:[name=regionName]').val(regionName);
                    $('#viewUser input:[name=coach]').val(coach);
                    $('#viewUser input:[name=coachName]').val(coachName);
                  });
                  $('#formEditProfile').dialog("close");
              }
          });
        }
        return false;
    });
</script>