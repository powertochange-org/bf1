<?php
/*
 * Cru Doctrine
 * Admin - Edit User
 * Campus Crusade for Christ
 */

try {
    //get values
    $submit     = isset($_POST['submit'])        ? true                  : false;
    $ajax       = isset($_POST['ajax'])          ? true                  : false;

    $email      = isset($_GET['email'])          ? $_GET['email']        : '';
    $oldEmail   = isset($_GET['oldEmail'])       ? $_GET['oldEmail']     : '';
    $firstName  = isset($_POST['firstName'])     ? $_POST['firstName']   : '';
    $lastName   = isset($_POST['lastName'])      ? $_POST['lastName']    : '';
    $password   = isset($_POST['password'])      ? $_POST['password']    : '';
    $type       = isset($_POST['type'])          ? $_POST['type']        : '';
    $region     = isset($_POST['region'])        ? $_POST['region']      : '';
    //$location   = isset($_POST['location'])      ? $_POST['location']    : '';
    $regDate    = isset($_POST['regDate'])       ? $_POST['regDate']     : '';
    $status     = isset($_POST['status'])        ? $_POST['status']      : '';
    $progress   = isset($_POST['progress'])      ? $_POST['progress']    : '';
    $coach      = isset($_POST['coach'])         ? $_POST['coach']       : '';

    $errors     = isset($_POST['errors'])        ? $_POST['errors']      : '';

    require_once("../config.inc.php"); 
    require_once("../Database.singleton.php");

    //initialize the database object
    $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
    $db->connect();

    //check for form submission
    if($submit) { //form was submitted, process data
        //update user
        //prepare query
        //$data['Email']      = $email;
        $data['FName']      = $firstName;
        $data['LName']      = $lastName;
        //$data['Password']   = $password;
        $data['Type']       = $type;
        $data['Region']     = $region;
        //$data['Loc']        = $location;
        $data['Reg_Date']   = $regDate;
        $data['Reg_Status'] = $status ? 'Active'  : 'Inactive';

        //execute query
        $db->update("user", $data, "Email = '".$db->escape($email)."'");

        //update progress
        //prepare query
        //execute query

        if ($coach != '') {
          //determine whether this student already has a coach
          $sql     = "SELECT COUNT(*) from coach where Student = '".$db->escape($email)."'";
          $result  = $db->query_first($sql);
          if ($result['COUNT(*)'] > 0) {
            //update coach
            //prepare query
            $data = array();
            $data['Coach'] = $coach;

            //execute query
            $db->update("coach", $data, "Student = '".$db->escape($email)."'");
          }
          else {
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
            header('Content-Type: application/xml; charset=ISO-8859-1');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
            echo '<user>';
            echo '<email>'      .$email.       '</email>';
            echo '<oldEmail>'   .$oldEmail.    '</oldEmail>';
            echo '<firstName>'  .$firstName.   '</firstName>';
            echo '<lastName>'   .$lastName.    '</lastName>';
            echo '<password>'   .$password.    '</password>';
            echo '<type>'       .$type.        '</type>';
            echo '<region>'     .$region.      '</region>';
            //echo '<location>'   .$location.    '</location>';
            echo '<regDate>'    .$regDate.     '</regDate>';
            echo '<status>'     .$status.      '</status>';
            echo '<progress>'   .$progress.    '</progress>';
            echo '<coach>'      .$coach.       '</coach>';
            echo '</user>';

            exit();
        } 
        else {

            header ("Location: ?p=users");
        }
    }
    else { //get data for user

        //get user
        $sql        = "SELECT Email, FName, LName, Password, Type, Region, Loc, Reg_Date, Reg_Status FROM user WHERE Email = '".$db->escape($email)."'";
        $result     = $db->query_first($sql);
        $name       = $result['FName'].' '.$result['LName'];
        $firstName  = $result['FName'];
        $lastName   = $result['LName'];
        $password   = $result['Password'];
        $type       = $result['Type'];
        $region     = $result['Region'];
        //$location   = $result['Loc'];
        $regDate    = $result['Reg_Date'];
        $status     = $result['Reg_Status'];
        
        //get progress
        
        //get coach
        $sql        = "SELECT Coach FROM coach WHERE Student = '".$db->escape($email)."'";
        $result     = $db->query_first($sql);
        $coach      = $result['Coach'];

        //get coaches for selection
        $sql     =  "SELECT Email, FName, LName
                     FROM  user
                     WHERE Type NOT IN ('student', 'intern')
                     ORDER BY LName;";

        $coaches = $db->fetch_array($sql);
    }
    $db->close();
} 
catch (PDOException $e) {
    echo $e->getMessage();
    exit();
}
?>

<div id="edituser">
    <form action="?p=users<?php echo '&email='.$email; ?>" method="post">
        <h2><?php echo $name ?></h2>
        <fieldset id="user">
          <div>
            <label>First Name</label>
            <input type="text" name="firstName" value="<?php echo $firstName; ?>" /><a class="required"></a>
          </div>
          <div>
            <label>Last Name</label>
            <input type="text" name="lastName" value="<?php echo $lastName; ?>" /><a class="required"></a>
          </div>
          <!--div>
            <label>Email</label>
            <input type="text" name="email" value="<?php //echo $email; ?>" /><a class="required"></a>
          </div-->
          <!--div>
              <label>Password</label>
              <input type="password" name="password" value="<?php //echo $password; ?>" /><a class="required"></a>
          </div>
          <div>
              <label>Confirm Password</label>
              <input type="password" name="confirmPassword" value="<?php //echo $password; ?>" /><a class="required"></a>
          </div-->
          <div>
             <label>Type</label>
             <select name="type">
                 <option value=""            <?php echo $type == ''         ? 'selected' : ''; ?>   >Select User Type</option>
                 <option value="intern"      <?php echo $type == 'intern'   ? 'selected' : ''; ?>   >Intern</option>
                 <option value="student"     <?php echo $type == 'student'  ? 'selected' : ''; ?>   >Student</option>
                 <option value="coach"       <?php echo $type == 'coach'    ? 'selected' : ''; ?>   >Coach</option>
                 <option value="regAdmin"    <?php echo $type == 'regAdmin' ? 'selected' : ''; ?>   >Regional Admin</option>
                 <option value="other"       <?php echo $type == 'other'    ? 'selected' : ''; ?>   >Other</option>
                 <option value="super"       <?php echo $type == 'super'    ? 'selected' : ''; ?>   >Super</option>
             </select><a class="required"></a>
          </div>
          <div>
            <label>Region</label>
            <input type="text" name="region" value="<?php echo $region; ?>" />
          </div>
          <!--div>
            <label>Location</label>
            <input type="text" name="location" value="<?php //echo $location; ?>" />
          </div-->
          <div>
            <label>Registration Date</label>
            <input type="text" name="regDate" readonly="readonly" value="<?php echo $regDate; ?>" />
          </div>
          <div>
            <input type="checkbox" name="status" value="" <?php echo $status == 'Active' ? 'checked' : ''; ?> /><label> Active </label>
          </div>
          <!--div>
              <label>Progress</label><select name="progress" ></select>
          </div-->
          <div>
              <label>Coach</label>
              <select name="coach">
                <option value=""   <?php echo $coach == '' ? 'selected' : ''; ?>   >Select A Coach</option>
            <?php
                if(count($coaches) > 0){
                    foreach ($coaches as $row){
                        echo '<option value="'.$row['Email'].'" ';
                        echo $coach == $row['Email'] ? ' selected>' : ''.'>';
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

        <button type="submit" name="submit">Save</button>
        <button name="cancel" type="submit" onclick="cancelFunc();return(false);">Cancel</button>

    </form>
</div>

<script type="text/javascript">

    //hide submit button
    $(function() {
        $('form button:[name=submit]').hide();
        $('form button:[name=cancel]').hide();
    });

    function cancelFunc(){
        window.location.href = "/admin/?p=users";
    }

    //validate form submission
    $('#edituser form').submit(function(){
        var submit = false;
        var errors = '';
        $('#errors').html(errors);

        if ($('input:[name=firstName]').val().length == 0) {
            $('input:[name=firstName]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please enter a first name for this user.</div>';
        }

        if ($('input:[name=lastName]').val().length == 0) {
            $('input:[name=lastName]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please enter a last name for this user.</div>';
        }

        /*if ($('input:[name=email]').val().length == 0) {
            $('input:[name=email]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please enter an email address for this user.</div>';
        }*/

        /*if ($('input:[name=password]').val().length == 0) {
            $('input:[name=password]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please enter a password for this user.</div>';
        }*/

        /*if ($('input:[name=confirmPassword]').val().length == 0) {
            $('input:[name=confirmPassword]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please enter a confirm password for this user.</div>';
        }*/

        /*if ($('input:[name=password]').val() != $('input:[name=confirmPassword]').val()) {
            $('input:[name=password]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            $('input:[name=confirmPassword]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>The password and confirm password do not match.</div>';
        }*/

        if ($('select:[name=type]').val().length == 0) {
            $('select:[name=type]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please select a type for this user.</div>';
        }

        if (errors !== ''){
           $('#errors').html(errors);
           submit = false;
        } else {
           submit = true;
        }

        if(submit) {
            $.ajax({
                url: 'edit_user.php?email=<?php echo $email; ?>',
                type: 'POST',
                data: { 
                    ajax        : true,
                    submit      : true,
                    firstName   : $('form input:[name=firstName]').val(),
                    lastName    : $('form input:[name=lastName]').val(),
                    email       : $('form input:[name=email]').val(),
                    //oldEmail    : <?php //echo $email; ?>,
                    //password    : $('form input:[name=password]').val(),
                    type        : $('form select:[name=type]').val(),
                    region      : $('form input:[name=region]').val(),
                    //location    : $('form input:[name=location]').val(),
                    regDate     : $('form input:[name=regDate]').val(),
                    status      : $('form input:checkbox[name=status]').attr('checked'),
                    //progress    : $('form input:[name=progress]').val(),
                    coach       : $('form select:[name=coach]').val()
                },
                dataType: "xml",
                success: function(xml) {
                    $(xml).find('user').each(function(){
                        //get values
                        var firstName   = $(this).find('firstName').text();
                        var lastName    = $(this).find('lastName').text();
                        //var email       = $(this).find('email').text();
                        //var password    = $(this).find('password').text();
                        var type        = $(this).find('type').text();
                        var region      = $(this).find('region').text();
                        //var location    = $(this).find('location').text();
                        var regDate     = $(this).find('regDate').text();
                        var status      = $(this).find('status').text();
                        //var progress    = $(this).find('progress').text();
                        var coach       = $(this).find('coach').text();
                    });
                    $('#edituser').dialog("close");
                }
            });
        }
        return false;
    });
</script>