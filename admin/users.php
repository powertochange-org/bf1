<?php
/*
 * Cru Doctrine
 * Admin - Users
 * Campus Crusade for Christ
 */

try {
    //get session values
    $email  = isset($_SESSION['email']) ? $_SESSION['email']  : '';
    $type   = isset($_SESSION['type']) ? $_SESSION['type']  : '';
    $region = isset($_SESSION['region']) ? $_SESSION['region']  : '';

    //users
    $users = array();
    //coaches
    $coaches = array();
    //regions
    $regions = array();
    //user types
    $user_types = array();

    //initialize the database object
    $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
    $db->connect();

    //get users
    $sql = null;
    if ($type == SUPER) {
      $sql =   "SELECT u.Email, u.FName, u.LName, u.Password, u.Type AS TypeID, u.Region AS RegionID, u.Loc, u.Reg_Date, u.Reg_Status, r.Name AS Region, t.Name AS Type, c.Coach AS Coach_Email
                FROM user u
                INNER JOIN region r ON u.Region = r.ID
                INNER JOIN user_type t ON u.Type = t.ID
                LEFT JOIN coach c ON u.Email = c.Student
                ORDER BY Region, u.LName;";
    } 
    else if ($type == REGIONAL_ADMIN) {
      $sql =   "SELECT u.Email, u.FName, u.LName, u.Password, u.Type AS TypeID, u.Region AS RegionID, u.Loc, u.Reg_Date, u.Reg_Status, r.Name AS Region, t.Name AS Type, c.Coach AS Coach_Email
                FROM user u
                INNER JOIN region r ON u.Region = r.ID
                INNER JOIN user_type t ON u.Type = t.ID
                LEFT JOIN coach c ON u.Email = c.Student
                WHERE u.Region = ".$region."
                ORDER BY Region, u.LName;";
    } 
    else {
      $sql =   "SELECT u.Email, u.FName, u.LName, u.Password, u.Type AS TypeID, u.Region AS RegionID, u.Loc, u.Reg_Date, u.Reg_Status, r.Name AS Region, t.Name AS Type, c.Coach AS Coach_Email
                FROM user u
                INNER JOIN region r ON u.Region = r.ID
                INNER JOIN user_type t ON u.Type = t.ID
                LEFT JOIN coach c ON u.Email = c.Student
                WHERE c.Coach = '".$db->escape($email)."'
                ORDER BY Region, u.LName;";
    }
    $users = $db->fetch_array($sql);

    //get assigned coach
    foreach ($users as &$user) {
        if (!is_null($user['Coach_Email'])) {
            $sql      = "SELECT u.FName AS Coach_FName, u.LName AS Coach_LName 
                         FROM user u
                         WHERE u.Email = '".$db->escape($user['Coach_Email'])."'";
            $result   = $db->query_first($sql);
            $user['Coach_FName']  = $result['Coach_FName'];
            $user['Coach_LName']  = $result['Coach_LName'];
       }
       else {
           $user['Coach_FName']  = '';
           $user['Coach_LName']  = '';
       }
    }
    unset($user); //break the reference with the last element

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
    $sql = '';
    if ($type == SUPER) {
        $sql =  "SELECT ID, Name
                 FROM  user_type
                 ORDER BY Name;";
    } else {
        $sql =  "SELECT ID, Name
                 FROM  user_type
                 WHERE ID > ".REGIONAL_ADMIN."
                 ORDER BY Name;";
    }
    $user_types = $db->fetch_array($sql);

    $db->close();
} catch (PDOException $e) {
    echo $e->getMessage();
}
?>

<form id="formAddNewRow" action="#">
    <input type="hidden" name="id" id="id"  class="DT_RowId" />
    <h2>New User</h2>
    <fieldset id="user">
      <div>
        <label>First Name</label>
        <input type="text" name="firstName" value="" rel="0" class="required"/>
      </div>
      <div>
        <label>Last Name</label>
        <input type="text" name="lastName" value="" rel="1" class="required"/>
      </div>
      <div>
        <label>Email</label>
        <input type="text" name="email" value="" rel="2" class="required"/>
      </div>
      <div>
          <label>Password</label>
          <input type="password" name="password" value="" rel="3" class="required"/>
      </div>
      <div>
          <label>Confirm Password</label>
          <input type="password" name="confirmPassword" rel="4" value="" class="required"/>
      </div>
      <div>
         <label>Type</label>
         <select name="type" rel="5" class="required">
           <option value="" 'selected'>Select User Type</option>
            <?php
                if(count($user_types) > 0){
                    foreach ($user_types as $row){
                        echo '<option value="'.$row['ID'].'" >';
                        echo $row['Name'].'</option>';
                    }
                }
            ?>
         </select>
      </div>
      <div>
        <input type="hidden" name="regDate" value="<?php echo date('Ymd'); ?>" rel="6"/>
      </div>
      <div>
        <input type="hidden" name="status" value="<?php echo ACTIVE; ?>" rel="7"/>
      </div>
      <div>
          <label>Region</label>
          <select name="region" rel="8" class="required">
            <option value="" 'selected' >Select A Region</option>
            <?php
                if(count($regions) > 0){
                    foreach ($regions as $row){
                        echo '<option value="'.$row['ID'].'" >';
                        echo $row['Name'].'</option>';
                    }
                }
            ?>
        </select>
      </div>
      <div>
          <label>Coach</label>
          <select name="coach" rel="9">
            <option value="" 'selected'>-- None --</option>
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
      <span class="datafield" style="display:none" rel="10"><a class="table-action-EditUser">Edit</a></span>
    </fieldset>
    <button id="btnAddNewRowOk" type="submit">Save</button>
    <button id="btnAddNewRowCancel" type="button">Cancel</button>
</form>

<form id="formEditUser" action="edit_user.php">
    <input type="hidden" name="id" id="id"  class="DT_RowId" />
    <h2>Edit User</h2>
    <fieldset id="user">
      <div>
        <label>First Name</label>
        <input type="text" name="firstName" value="" rel="0" class="required"/>
      </div>
      <div>
        <label>Last Name</label>
        <input type="text" name="lastName" value="" rel="1" class="required"/>
      </div>
      <div>
        <label>Email</label>
        <input type="text" name="email" value="" rel="2" class="required"/>
      </div>
      <div>
          <label>Password</label>
          <input type="password" name="password" value="" rel="3" class="required"/>
      </div>
      <div>
          <label>Confirm Password</label>
          <input type="password" name="confirmPassword" rel="4" value="" class="required"/>
      </div>
      <div>
         <label>Type</label>
         <select name="type" rel="5" class="required">
           <option value="" 'selected'>Select User Type</option>
            <?php
                if(count($user_types) > 0){
                    foreach ($user_types as $row){
                        echo '<option value="'.$row['ID'].'" >';
                        echo $row['Name'].'</option>';
                    }
                }
            ?>
         </select>
      </div>
      <div>
        <input type="hidden" name="regDate" value="" rel="6"/>
      </div>
      <div>
        <input type="hidden" name="status" value="" rel="7"/>
      </div>
      <div>
          <label>Region</label>
          <select name="region" rel="8" class="required">
            <option value="" 'selected' >Select A Region</option>
            <?php
                if(count($regions) > 0){
                    foreach ($regions as $row){
                        echo '<option value="'.$row['ID'].'" >';
                        echo $row['Name'].'</option>';
                    }
                }
            ?>
        </select>
      </div>
      <div>
          <label>Coach</label>
          <select name="coach" rel="9">
            <option value="" 'selected'>-- None --</option>
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
      <span class="datafield" style="display:none" rel="10"><a class="table-action-EditUser">Edit</a></span>
    </fieldset>
    <button id="formEditUserOk" type="submit">Save</button>
    <button id="formEditUserCancel" type="button">Cancel</button>
</form>

<div id="users">
    <div id="list">
        <div class="add_delete_toolbar"></div>
        <table id="user_table">
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Password</th>
                    <th>Confirm Password</th>
                    <th>Type</th>
                    <th>Reg Date</th>
                    <th>Status</th>
                    <th>Region</th>
                    <th>Coach</th>
                    <th>Edit</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if(count($users) > 0){
                        foreach ($users as $row){
                            echo '<tr id="'.$row['Email'].'">
                                    <td>'.$row['FName'].'</td>
                                    <td>'.$row['LName'].'</td>
                                    <td>'.$row['Email'].'</td>
                                    <td>'.$row['Password'].'</td>
                                    <td>'.$row['Password'].'</td>
                                    <td>'.$row['Type'].'</td>
                                    <td>'.$row['Reg_Date'].'</td>
                                    <td>'.$row['Reg_Status'].'</td>
                                    <td>'.$row['Region'].'</td>
                                    <td>'.$row['Coach_FName'].' '.$row['Coach_LName'].'</td>
                                    <td><a class="table-action-EditUser">Edit</a></td>
                                  </tr>';
                        }
                    }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script type="text/javascript">
  $(document).ready(function() {
    $.extend( $.fn.dataTable.defaults, {
        "placeholder": 'None'
    });

    $('#user_table').dataTable({
    "sScrollX": "100%",
    "sScrollXInner": "110%",
    "bScrollCollapse": true,
    "sDom": 'T<"clear">lfrtip',
    "oTableTools": {
      "sSwfPath": "/jquery/datatables/extras/TableTools/media/swf/copy_cvs_xls_pdf.swf",
      "aButtons": [
        {
          "sExtends": "pdf",
          "mColumns": [ 0, 1, 2, 5, 8, 9 ]
        },
        {
          "sExtends": "print"
        },
        {
          "sExtends": "xls",
          "mColumns": [ 0, 1, 2, 5, 8, 9 ]
        },
        {
          "sExtends": "copy",
          "mColumns": [ 0, 1, 2, 5, 8, 9 ]
        }
      ]
    },
    "aoColumnDefs": [
      { "bSearchable": false, "bVisible": false, "aTargets": [ 3 ] },
      { "bSearchable": false, "bVisible": false, "aTargets": [ 4 ] },
      { "bSearchable": false, "bVisible": false, "aTargets": [ 6 ] },
      { "bSearchable": false, "bVisible": false, "aTargets": [ 7 ] }
    ]
  }).makeEditable({
      aoTableActions: [
        {
          sAction: "EditUser",
          sServerActionURL: "edit_user.php",
          oFormOptions: {
            title: 'Edit a user',
            show: "blind",
            hide: "blind",
            width: 400,
            height: 450,
            autoOpen: false, 
            modal: true
          }
        }
      ],
      sUpdateURL: "edit_user.php",
      sAddURL: "add_user.php",
      sDeleteURL: "delete_user.php",
      /*sAddDeleteToolbarSelector: ".dataTables_length",*/
      oAddNewRowButtonOptions: { 
        label: "Add...",
        icons: { primary: 'ui-icon-plus' }
      },
      oDeleteRowButtonOptions: {
        label: "Remove",
        icons: { primary: 'ui-icon-trash' }
      },
      oAddNewRowOkButtonOptions: {
        label: "Save",
        icons: { primary: 'ui-icon-check' },
        name: "action",
        value: "add-new"
      },
      oAddNewRowCancelButtonOptions: { 
        label: "Cancel",
        class: "back-class",
        name: "action",
        value: "cancel-add",
        icons: { primary: 'ui-icon-close' }
      },
      oAddNewRowFormOptions: {
        title: 'Add a new user',
        show: "blind",
        hide: "blind",
        width: 400,
        height: 450,
        autoOpen: false, 
        modal: true
      }
  });;

    $("#formAddNewRow").validate();
    $("#formEditUser").validate();
  });
</script>