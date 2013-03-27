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

  //get name of Region
  $sql = "SELECT Name
          FROM  region
          WHERE ID = ".$region.";";

  $regionName = $db->query_first($sql);

  //get coach for user
  $sql = "SELECT u.Email, u.FName, u.LName
          FROM  user u
          INNER JOIN coach c ON u.Email = c.Coach
          WHERE c.Student = '".$db->escape($email)."';";

  $coach     = $db->query_first($sql);
  $coachName = $coach['FName'].' '.$coach['LName'];

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
      <input type="text" name="firstName" value="<?php echo $firstName;?>" readonly/>
    </div>
    <div>
      <label>Last Name:</label>
      <input type="text" name="lastName" value="<?php echo $lastName;?>" readonly/>
    </div>
    <div>
      <label>Email:</label>
      <input type="text" name="email" value="<?php echo $email;?>" readonly/>
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
      <label>Region:</label>
      <input type="hidden" name="region" value="<?php echo $region;?>"/>
      <input type="text" name="regionName" value="<?php echo $regionName['Name'];?>" readonly/>
    </div>
    <div>
      <label>Coach:</label>
      <input type="hidden" name="coach" value="<?php echo $coach['Email'];?>"/>
      <input type="text" name="coachName" value="<?php echo $coachName;?>" readonly/>
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
        firstName   : $('#viewUser input:[name=firstName]').val(),
        lastName    : $('#viewUser input:[name=lastName]').val(),
        email       : $('#viewUser input:[name=email]').val(),
        region      : $('#viewUser input:[name=region]').val(),
        coach       : $('#viewUser input:[name=coach]').val()
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
          height: 650,
          width: 650,
          resizable: false,
          modal: true
        });
      }
    });

    //prevent form from submitting traditionaly
    return false;
  });
</script>