<?php
/*
 * Cru Doctrine
 * Admin - Add User
 * Nicholas Crown
 */

try {

    //get values
    $submit     = isset($_POST['submit'])   ? true                  : false;
    $ajax       = isset($_POST['ajax'])     ? true                  : false;

    $email      = isset($_GET['email'])     ? $_GET['email']        : '';
    $progress   = isset($_POST['progress']) ? $_POST['progress']    : '';
    $coach      = isset($_POST['coach'])    ? $_POST['coach']       : '';
    $status     = isset($_POST['status'])   ? $_POST['status']      : '';

    $errors     = isset($_POST['errors'])   ? $_POST['errors'] : '';

	// grab the existing $db object
	$db=Database::obtain();

    //check for form submission
    if($submit){    //form was submitted, process data

        //prepare query
        $data['Reg_Status'] = $status;

        //execute query
        $db->update("user", $data, "ID = '".$db->escape($email)."'");
        
        //if ajax, return user attributes as xml
        if ($ajax) {

            header('Content-Type: application/xml; charset=ISO-8859-1');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
            echo '<user>';
            echo '<email>'      .$email.		    '</email>';
            echo '<progress>'   .$progress.		    '</progress>';
            echo '<coach>'      .$coach.		    '</coach>';
            echo '<status>'     .$status.		    '</status>';
            echo '</user>';

            exit();

        } else {

            header ("Location: ?p=users");

        }

    } else { //get data for user

        //get status
		//prepare query
		$sql = "SELECT Reg_Status, FName, LName FROM user WHERE Email = ".$email;

		//execute query
		$result     = $db->query_first($sql);
        $status     = $result['Reg_Status'];
        $name       = $result['FName'].' '.$result['LName'];
        
        //get progress
        
        //get coach
		//prepare query
		$sql = "SELECT Coach FROM coach WHERE Student = ".$email;
        $result     = $db->query_first($sql);
        $coach      = $result['Coach'];

    }

	$db->close();

} catch (PDOException $e) {
    echo $e->getMessage();
    exit();
}

?>

<div id="adduser">

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
                <label>Account Status</label><input type="hidden" name="status" value="<?php echo $progress; ?>" /><a id="inactive" class="switch">Inactive</a><a id="active" class="switch">Active</a>
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
