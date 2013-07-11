<?php
/*
 * Cru Doctrine
 * My Profile - View Profile
 * Campus Crusade for Christ
 */

try {
  //get session values
  $email     = isset($_SESSION['email'])  ? $_SESSION['email']   : '';
  $firstName = isset($_SESSION['fname'])  ? $_SESSION['fname']   : '';
  $lastName  = isset($_SESSION['lname'])  ? $_SESSION['lname']   : '';
  $type      = isset($_SESSION['type'])   ? $_SESSION['type']    : '';
  $region    = isset($_SESSION['region']) ? $_SESSION['region']  : '';

  //initialize the database object
  $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
  $db->connect();

  //get the region name
  $regionName = getRegionNameByID($db, $region);

  //get coach for user
  $sql = null;
  $sql = "SELECT u.Email, u.FName, u.LName
          FROM  user u
          INNER JOIN coach c ON u.Email = c.Coach
          WHERE c.Student = '".$db->escape($email)."';";

  $coach     = $db->query_first($sql);
  $coachName = $coach['FName'].' '.$coach['LName'];

  //get name of user type
  $sql = null;
  $sql = "SELECT Name
          FROM  user_type
          WHERE ID = ".$type.";";

  $userTypeName = $db->query_first($sql);

  $db->close();
}
catch (PDOException $e) {
    echo $e->getMessage();
    exit();
}
?>
<form id="formViewProfile" action="" method="post">
  <fieldset id="viewUser">
    <div>
      <label>First Name:</label>
      <input type="text" id="firstName" value="<?php echo $firstName;?>" readonly/>
    </div>
    <div>
      <label>Last Name:</label>
      <input type="text" id="lastName" value="<?php echo $lastName;?>" readonly/>
    </div>
    <div>
      <label>Email:</label>
      <input type="text" id="email" value="<?php echo $email;?>" readonly/>
    </div>
    <div>
      <label>Type:</label>
      <input type="hidden" id="type" value="<?php echo $type;?>"/>
      <input type="text" id="userTypeName" value="<?php echo $userTypeName['Name'];?>" readonly/>
    </div>
    <!--div>
      <label>Password:</label>
      <input type="password" id="password" value="<?php //echo $password;?>" readonly/>
    </div>
    <div>
      <label>Confirm Password:</label>
      <input type="password" id="confirmPassword" value="<?php //echo $password;?>" readonly/>
    </div-->
    <div>
      <label>Region:</label>
      <input type="hidden" id="region" value="<?php echo $region;?>"/>
      <input type="text" id="regionName" value="<?php echo $regionName;?>" readonly/>
    </div>
    <div>
      <label>Coach:</label>
      <input type="hidden" id="coach" value="<?php echo $coach['Email'];?>"/>
      <input type="text" id="coachName" value="<?php echo $coachName;?>" readonly/>
    </div>
  </fieldset>
  <button type="submit" class="ui-corner-right corners-all shadow-light button" value="submit"><span class="ui-icon ui-icon-pencil"></span>Edit Profile</button>
</form>

<script type="text/javascript">
  $('#formViewProfile').submit(function() {
    $.ajax({
      url: "edit_profile.php",
      type: "post",
      dataType: "html",
      data: {
        firstName   : $('#viewUser #firstName').val(),
        lastName    : $('#viewUser #lastName').val(),
        email       : $('#viewUser #email').val(),
        type        : $('#viewUser #type').val(),
        region      : $('#viewUser #region').val(),
        coach       : $('#viewUser #coach').val()
      },
      success: function(msg){
        //append form to DOM and display a dialog
        $('#contentpane').append(msg);
        $('#formEditProfile').dialog({
          title: "Edit Profile",
          buttons: {
            "Ok": function() {
                $(this).submit();
            },
            "Cancel": function() {
                $(this).dialog("close");
            }
          },
          close: function(){
            $(this).dialog( "destroy" );
            $('#formEditProfile').remove();
          },
          height: 550,
          width: 450,
          resizable: false,
          modal: true
        });
      }
    });

    //prevent form from submitting traditionaly
    return false;
  });
</script>