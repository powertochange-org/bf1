<?php
/*
 * Cru Doctrine
 * Admin - Users
 * Campus Crusade for Christ
 */

//users
$users = array();

try {

    global $users;

    //initialize the database object
    $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
    $db->connect();

    //execute query and return to module array   
    $sql =   "SELECT u.Email, u.FName, u.LName, u.Type, u.Region AS Reg, u.Loc, u.Reg_Date, u.Reg_Status, r.Name AS Region, l.Name AS Location
             FROM user u
             INNER JOIN region r ON u.Region = r.ID
             INNER JOIN location l ON u.Loc = l.ID
             ORDER BY LName;";

    $users = $db->fetch_array($sql);
    
    $db->close();
} catch (PDOException $e){
    echo $e->getMessage();
}

?>

<div id="users">

    <div id="list">

        <form id="createNew" action="?p=users&request=add_user" method="post">
            <button type="submit" value="submit" class="corners-all shadow-light"><span class="ui-icon ui-icon-plus"></span>Add User</button>
        </form>

        <form id="search" action="" method="get">
            <input type="hidden" name="p" value="users" />
            <input type="text" name="search" /><button type="submit" class="ui-state-default corners-right"><div></div></button>
        </form>

        <div id="legend">
            <div id="ga"><a></a>Global</div>
            <div id="ra"><a></a>Regional</div>
            <div id="ic"><a></a>Intern Coach</div>
            <div id="st"><a></a>Staff</div>
            <div id="in"><a></a>Intern</div>
            <div id="ot"><a></a>Other</div>
        </div>

        <?php
            if(count($users) > 0){
                foreach ($users as $row){
                    echo '<div class="user" id="'.$row['Email'].'">
                            <div class="title corners-left">
                              <div class="usericon"></div>
                              <div class="name">'.$row['FName'].' '.$row['LName'].'</div>
                              <div class="location">'.$row['Loc'].'</div>
                            </div>
                            <div class="email">'.$row['Email'].'</div>
                              <a class="edit ui-state-default corners-all" href="" onclick="editUser(\''.$row['Email'].'\');return false">
                              <span class="ui-icon ui-icon-pencil"></span>Edit</a>
                          </div>';
                }
            }
        ?>
    </div>
</div>

<script type="text/javascript">

    $('#createNew').submit(function(){
        //get new user form
        $.ajax({
            url: "add_user.php",
            dataType: "html",
            success: function(msg){
                //append form to DOM and display dialog
                $('#users').append(msg);
                $('#adduser').dialog({
                    title: "New User",
                    buttons: {
                        "Ok": function() {
                            $(this).find('form').submit();
                        },
                        "Cancel": function() {
                            $(this).dialog("close");
                        }
                    },
                    close: function(){
                        $(this).dialog( "destroy" );
                        $('#adduser').remove();
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

    function editUser(email){
        //get edit user form
        $.ajax({
            url: "edit_user.php",
            type: "GET",
            data: { 
                email        : email,
            },
            dataType: "html",
            success: function(msg){
                //append form to DOM and display dialog
                $('#users').append(msg);
                $('#edituser').dialog({
                    title: "Edit User",
                    buttons: {
                        "Ok": function() {
                            $(this).find('form').submit();
                        },
                        "Cancel": function() {
                            $(this).dialog("close");
                        }
                    },
                    close: function(){
                        $(this).dialog( "destroy" );
                        $('#edituser').remove();
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
    };

</script>