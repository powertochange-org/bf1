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
  $type       = isset($_POST['type'])          ? $_POST['type']        : '';
  $firstName  = isset($_POST['firstName'])     ? $_POST['firstName']   : '';
  $lastName   = isset($_POST['lastName'])      ? $_POST['lastName']    : '';
  $region     = isset($_POST['region'])        ? $_POST['region']      : '';
  $coach      = isset($_POST['coach'])         ? $_POST['coach']       : '';

  $errors     = isset($_POST['errors'])   ? $_POST['errors'] : '';

  $coaches = array();
  $coachName = null;
  $regions = array();
  $regionName = null;
  $user_statuses = array();
  $user_types = array();

  require_once("../config.inc.php"); 
  require_once("../Database.singleton.php");
  require_once("../function.inc.php");

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
    $data['Type']       = $type;
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

      $result = null;
      $result = $db->query_first($sql);
      $regionName = $result['Name'];

      //get coach for user
      $sql = "SELECT CONCAT(u.FName, ' ', u.LName) AS FullName
              FROM  user u
              INNER JOIN coach c ON u.Email = c.Coach
              WHERE c.Student = '".$db->escape($email)."';";

      $result = null;
      $result = $db->query_first($sql);
      $coachName = $result['FullName'];

      //get name of user type
      $sql = null;
      $sql = "SELECT Name
              FROM  user_type
              WHERE ID = ".$type.";";

      $result = null;
      $result = $db->query_first($sql);
      $userTypeName = $result['Name'];

      header('Content-Type: application/xml; charset=ISO-8859-1');
      echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
      echo '<user>';
      echo '<email>'        .$email.         '</email>';
      echo '<firstName>'    .$firstName.     '</firstName>';
      echo '<lastName>'     .$lastName.      '</lastName>';
      echo '<type>'         .$type.          '</type>';
      echo '<userTypeName>' .$userTypeName.  '</userTypeName>';
      echo '<region>'       .$region.        '</region>';
      echo '<regionName>'   .$regionName.    '</regionName>';
      echo '<coach>'        .$coach.         '</coach>';
      echo '<coachName>'    .$coachName.     '</coachName>';
      echo '</user>';

      //Update the session variables with the new values
      $_SESSION['email']  = $email;
      $_SESSION['fname']  = $firstName;
      $_SESSION['lname']  = $lastName;
      $_SESSION['type']   = $type;
      $_SESSION['region'] = $region;

      $db->close();
      exit();
    }
  }
  else { //get data for profile edit

    //get coaches for selection
    $coaches = getActiveCoaches($db);

    //get the coach name
    $coachName = getUserNameByID($db, $coach);

    //get regions for selection
    $regions = getRegions($db);

    //get the region name
    $regionName = getRegionNameByID($db, $region);

    //get user statuses for selection
    $user_statuses = getUserStatuses($db);

    //get user types for selection
    $typeClause = null;
    switch ($type) {
        case SUPER:
          $typeClause = SUPER-1;
          break;
        case REGIONAL_ADMIN:
          $typeClause = REGIONAL_ADMIN-1;
          break;
        default:
          $typeClause = REGIONAL_ADMIN;
          break;
    }
    $user_types = getUserTypes($db, $typeClause);
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
      <label>First Name:</label>
      <input type="text" id="firstName" placeholder="First Name" value="<?php echo $firstName;?>"/>
      <a class="required"></a>
    </div>
    <div>
      <label>Last Name:</label>
      <input type="text" id="lastName" placeholder="Last Name" value="<?php echo $lastName;?>"/>
      <a class="required"></a>
    </div>
    <div>
      <label>Email:</label>
      <input type="text" id="email" placeholder="Email" value="<?php echo $email;?>"/>
      <a class="required"></a>
    </div>
    <div>
      <label>Type:</label>
      <select id="type">
        <option value="" 'selected'>Select User Type</option>
        <?php
          if(count($user_types) > 0){
            foreach ($user_types as $row){
              echo '<option value="'.$row['id'].'"';
              echo $type == $row['id'] ? ' selected>' : ''.'>';
              echo $row['name'].'</option>';
            }
          }
        ?>
      </select>
      <a class="required"></a>
    </div>
    <div>
      <label>Region</label>
      <input id="regionSearch" placeholder="Type Name" class="typeahead" value="<?php echo $regionName;?>" type="text">
      <input id="region" value="<?php echo $region;?>" type="hidden">
      <a class="required"></a>
    </div>
    <div>
      <label>Coach</label>
      <input id="coachSearch" placeholder="Type Name" class="typeahead" value="<?php echo $coachName;?>" type="text">
      <input id="coach" value="<?php echo $coach;?>" type="hidden">
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
      $('#formEditProfile #formEditProfileCancel').hide();
      $('#formEditProfile #formEditProfileOk').hide();
  });

  $('#formEditProfile #coachSearch').typeahead({
    name: 'Coach',
    valueKey: 'name',
    local: <?php echo json_encode($coaches); ?>,
    template: '<p><strong>{{name}}</strong> – {{region}}</p>',
    engine: Hogan
  }).on('typeahead:opened', function(event) {
    $('#formEditProfile #coach').val('');
    $('#formEditProfile #coachSearch').val('');
    $('#formEditProfile #coachSearch').typeahead('setQuery', '');
  }).on('typeahead:selected', function(event, datum) {
    $('#formEditProfile #coach').val(datum.id);
  }).on('typeahead:autocompleted', function(event, datum) {
    $('#formEditProfile #coach').val(datum.id);
  });

  $('#formEditProfile #regionSearch').typeahead({
    name: 'Region',
    valueKey: 'name',
    local: <?php echo json_encode($regions); ?>
  }).on('typeahead:opened', function(event) {
    $('#formEditProfile #region').val('');
    $('#formEditProfile #regionSearch').val('');
    $('#formEditProfile #regionSearch').typeahead('setQuery', '');
  }).on('typeahead:selected', function(event, datum) {
    $('#formEditProfile #region').val(datum.id);
  }).on('typeahead:autocompleted', function(event, datum) {
    $('#formEditProfile #region').val(datum.id);
  });

  //validate form submission
  $('#formEditProfile').submit(function() {
    var submit = false;
    var errors = '';
    $('#errors').html(errors);

    if ($('#editUser #firstName').val().length == 0) {
      $('#editUser #firstName').css('border-color', 'orange').siblings('a').css('display','inline-block');
      errors += '<div>Please enter a first name.</div>';
    }
    else {
      $('#editUser #firstName').css('border-color', '').siblings('a').css('display','');
    }

    if ($('#editUser #lastName').val().length == 0) {
      $('#editUser #lastName').css('border-color', 'orange').siblings('a').css('display','inline-block');
      errors += '<div>Please enter a last name.</div>';
    }
    else {
      $('#editUser #lastName').css('border-color', '').siblings('a').css('display','');
    }

    if ($('#editUser #email').val().length == 0) {
      $('#editUser #email').css('border-color', 'orange').siblings('a').css('display','inline-block');
      errors += '<div>Please enter an email address.</div>';
    }
    else {
      $('#editUser #email').css('border-color', '').siblings('a').css('display','');
    }

    if ($('#editUser #type').val().length == 0) {
      $('#editUser #type').css('border-color', 'orange').siblings('a').css('display','inline-block');
      errors += '<div>Please enter a user type.</div>';
    }
    else {
      $('#editUser #type').css('border-color', '').siblings('a').css('display','');
    }

    if ($('#editUser #region').val().length == 0) {
      if ($('#editUser #regionSearch').val().length > 0) {
        $('#editUser #regionSearch').css('border-color', 'orange');
        $('#editUser #region').siblings('a').css('display','inline-block');
        errors += '<div>Please select a valid region.</div>';
      }
      else {
        $('#editUser #regionSearch').css('border-color', 'orange');
        $('#editUser #region').siblings('a').css('display','inline-block');
        errors += '<div>Please select a region.</div>';
      }
    }
    else {
      $('#editUser #regionSearch').css('border-color', '');
      $('#editUser #region').siblings('a').css('display','');
    }

    if (errors !== '') {
      $('#feedback #errors').html(errors);
      submit = false;
    } 
    else {
      submit = true;
    }

    if(submit) {
      $.ajax({
        url: 'edit_profile.php',
        type: 'POST',
        data: {
          ajax        : true,
          submit      : true,
          firstName   : $('#editUser #firstName').val(),
          lastName    : $('#editUser #lastName').val(),
          email       : $('#editUser #email').val(),
          type        : $('#editUser #type').val(),
          region      : $('#editUser #region').val(),
          coach       : $('#editUser #coach').val()
        },
        dataType: "xml",
        success: function(xml) {
          $(xml).find('user').each(function() {
            //get values
            var email        = $(this).find('email').text();
            var firstName    = $(this).find('firstName').text();
            var lastName     = $(this).find('lastName').text();
            var type         = $(this).find('type').text();
            var userTypeName = $(this).find('userTypeName').text();
            var region       = $(this).find('region').text();
            var regionName   = $(this).find('regionName').text();
            var coach        = $(this).find('coach').text();
            var coachName    = $(this).find('coachName').text();

            //update the form values
            $('#viewUser #firstName').val(firstName);
            $('#viewUser #lastName').val(lastName);
            $('#viewUser #email').val(email);
            $('#viewUser #type').val(type);
            $('#viewUser #userTypeName').val(userTypeName);
            $('#viewUser #region').val(region);
            $('#viewUser #regionName').val(regionName);
            $('#viewUser #coach').val(coach);
            $('#viewUser #coachName').val(coachName);
          });
          $('#formEditProfile').dialog("close");
        }
      });
    }
    return false;
  });
</script>