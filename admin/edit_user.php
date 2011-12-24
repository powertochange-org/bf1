<?php
/*
 * Cru Doctrine
 * Admin - Edit User
 * Campus Crusade for Christ
 */


//get values
$submit     = isset($_POST['submit'])   ? true                  : false;
$ajax       = isset($_POST['ajax'])     ? true                  : false;

$email      = isset($_GET['email'])     ? $_GET['email']        : '';
$progress   = isset($_POST['progress']) ? $_POST['progress']    : '';
$coach      = isset($_POST['coach'])    ? $_POST['coach']       : '';
$status     = isset($_POST['status'])   ? $_POST['status']      : '';

$errors     = isset($_POST['errors'])   ? $_POST['errors'] : '';

try {

    global $progress, $coach, $status;
	
	//initialize the database object
    $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
    $db->connect();

    //check for form submission
    if($submit){//form was submitted, process data

        //update status
        //prepare query
        $data['Reg_Status'] = $status;

        //execute query
        $db->update("user", $data, "Email = '".$db->escape($email)."'");
        
        //update progress
                
        //update coach
        //prepare query
        $data['Coach'] = $coach;
        //execute query
        $db->update("coach", $data, "Student = '".$db->escape($email)."'");

        //if ajax, return user attributes as xml
        if ($ajax) {

            header('Content-Type: application/xml; charset=ISO-8859-1');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
            echo '<user>';
            echo '<email>'      .$email.            '</email>';
            echo '<progress>'   .$progress.         '</progress>';
            echo '<coach>'      .$coach.            '</coach>';
            echo '<status>'     .$status.           '</status>';
            echo '</user>';

            exit();

        } else {

            header ("Location: ?p=users");

        }

    } else { //get data for user

        //get status
        $sql        = "SELECT Reg_Status, FName, LName FROM user WHERE Email = '".$db->escape($email)."'";
        $result     = $db->query_first($sql);
        $status     = $result['Reg_Status'];
        $name       = $result['FName'].' '.$result['LName'];
        
        //get progress
        
        //get coach
        $sql        = "SELECT Coach FROM coach WHERE Student = '".$db->escape($email)."'";
        $result     = $db->query_first($sql);
        $coach      = $result['Coach'];

    }

    $db->close();

} catch (PDOException $e) {
    echo $e->getMessage();
    exit();
}

?>

<div id="edituser">

    <form action="?p=users<?php echo '&email='.$email; ?>" method="post">

        <h2><?php echo $name ?></h2>

        <fieldset id="options">
            <legend>Options</legend>
            <div>
                <label>Progress</label><select name="progress" ></select>
            </div>
            <div>
                <label>Coach</label><input type="text" name="coach_input" value="" /><input type="hidden" name="coach" value="<?php echo $coach; ?>" />
            </div>
            <div>
                <label>Account Status</label><input type="hidden" name="status" value="<?php echo $status; ?>" /><a id="inactive" class="switch">Inactive</a><a id="active" class="switch">Active</a>
            </div>
        </fieldset>

        <fieldset id="feedback">
            <div id="errors"><?php echo $errors; ?></div>
        </fieldset>

        <button type="submit" name="submit">Save Changes</button>

    </form>

</div>

<script type="text/javascript">

    //hide submit button
    $(function() {
        $('form button:[name=submit]').hide();
    });

    //validate form submission
    $('#edituser form').submit(function(){
        var submit = false;
        var errors = '';

        if (errors !== ''){
           $('#editmodule #errors').html(errors);
           submit = false;
        } else {
           submit = true;
        }

        if(submit){
            $.ajax({
                url: 'edit_user.php?email=<?php echo $email; ?>',
                type: 'POST',
                data: { 
                    ajax        : true,
                    submit      : true,
                    progress    : $('form input:[name=progress]').val(),
                    coach       : $('form input:[name=coach]').val(),
                    status      : $('form input:[name=status]').val()
                },
                dataType: "xml",
                success: function(xml){
                    
                    $(xml).find('user').each(function(){

                        //get values
                        var email       = $(this).find('email').text();
                        var progress    = $(this).find('progress').text();
                        var coach       = $(this).find('coach').text();
                        var status      = $(this).find('status').text();

                    });

                    $('#edituser').dialog("close");

                }
            });
        }

        return false;

    });

</script>