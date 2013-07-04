<?php
/*
 * Cru Doctrine
 * Admin - Archived Users
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
  //user statuses
  $user_statuses = array();

  //initialize the database object
  $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
  $db->connect();

  //get users
  $sql = null;
  if ($type == SUPER) {
    $sql = "SELECT u.Email, u.FName, u.LName, u.Password, u.Type AS TypeID, u.Region AS RegionID, u.Loc, u.Reg_Date, u.Status AS StatusID, r.Name AS Region, s.Name AS Status, t.Name AS Type
            FROM user u
            INNER JOIN region r ON u.Region = r.ID
            INNER JOIN user_status s ON u.Status = s.ID
            INNER JOIN user_type t ON u.Type = t.ID
            WHERE u.Status = ".INACTIVE."
            ORDER BY Region, u.LName;";
  } 
  else if ($type == REGIONAL_ADMIN) {
    $sql = "SELECT u.Email, u.FName, u.LName, u.Password, u.Type AS TypeID, u.Region AS RegionID, u.Loc, u.Reg_Date, u.Status AS StatusID, r.Name AS Region, s.Name AS Status, t.Name AS Type
            FROM user u
            INNER JOIN region r ON u.Region = r.ID
            INNER JOIN user_status s ON u.Status = s.ID
            INNER JOIN user_type t ON u.Type = t.ID
            WHERE u.Region = ".$region." AND
            u.Status = ".INACTIVE."
            ORDER BY Region, u.LName;";
    } 
  else {
    $sql = "SELECT u.Email, u.FName, u.LName, u.Password, u.Type AS TypeID, u.Region AS RegionID, u.Loc, u.Reg_Date, u.Status AS StatusID, r.Name AS Region, s.Name AS Status, t.Name AS Type
            FROM user u
            INNER JOIN region r ON u.Region = r.ID
            INNER JOIN user_status s ON u.Status = s.ID
            INNER JOIN user_type t ON u.Type = t.ID
            LEFT JOIN coach c ON u.Email = c.Student
            WHERE c.Coach = '".$db->escape($email)."' AND
            u.Status = ".INACTIVE."
            ORDER BY Region, u.LName;";
    }
  $users = $db->fetch_array($sql);

  //get regions for selection
  $regions = getRegions($db);

  //get user types for selection
  if ($type == SUPER) {
    $user_types = getUserTypes($db, SUPER-1);
  }
  else {
    $user_types = getUserTypes($db);
  }

  //get user statuses for selection
  $user_statuses = getUserStatuses($db);

  $db->close();
}
catch (PDOException $e) {
  echo $e->getMessage();
}
?>

<form id="formEditUser" action="edit_user.php">
  <input type="hidden" name="id" id="id"  class="DT_RowId" />
  <h2>Edit User</h2>
  <fieldset id="user">
    <div>
      <input type="hidden" name="regDate" value="" rel="0"/>
    </div>
    <div>
      <label>First Name</label>
      <input type="text" name="firstName" value="" rel="2" class="required"/>
    </div>
    <div>
      <label>Last Name</label>
      <input type="text" name="lastName" value="" rel="3" class="required"/>
    </div>
    <div>
      <label>Email</label>
      <input type="text" name="email" value="" rel="4" class="required"/>
    </div>
    <div>
        <label>Password</label>
        <input type="password" name="password" value="" rel="5" class="required"/>
    </div>
    <div>
        <label>Confirm Password</label>
        <input type="password" name="confirmPassword" rel="6" value="" class="required"/>
    </div>
    <div>
       <label>Type</label>
       <select name="type" rel="7" class="required">
         <option value="" 'selected'>Select User Type</option>
          <?php
              if(count($user_types) > 0){
                  foreach ($user_types as $row){
                      echo '<option value="'.$row['id'].'" >';
                      echo $row['name'].'</option>';
                  }
              }
          ?>
       </select>
    </div>
    <div>
        <label>Region</label>
        <select name="region" rel="1" class="required">
          <option value="" 'selected' >Select A Region</option>
          <?php
              if(count($regions) > 0){
                  foreach ($regions as $row){
                      echo '<option value="'.$row['id'].'" >';
                      echo $row['name'].'</option>';
                  }
              }
          ?>
      </select>
    </div>
    <div>
      <label>Status</label>
      <select name="status" rel="8" class="required">
        <option value="" 'selected' >Select A Status</option>
          <?php
            if(count($user_statuses) > 0){
              foreach ($user_statuses as $row){
                echo '<option value="'.$row['id'].'" >';
                echo $row['name'].'</option>';
              }
            }
          ?>
      </select>
    </div>
    <span class="datafield" style="display:none" rel="9"><a class="table-action-EditUser">Edit</a></span>
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
          <th>Reg Date</th>
          <th>Region</th>
          <th>First Name</th>
          <th>Last Name</th>
          <th>Email</th>
          <th>Password</th>
          <th>Confirm Password</th>
          <th>Type</th>
          <th>Status</th>
          <th>Edit</th>
        </tr>
      </thead>
      <tbody>
        <?php
          if(count($users) > 0) {
            foreach ($users as $row) {
              echo '<tr id="'.$row['Email'].'">
                      <td>'.$row['Reg_Date'].'</td>
                      <td>'.$row['Region'].'</td>
                      <td>'.$row['FName'].'</td>
                      <td>'.$row['LName'].'</td>
                      <td>'.$row['Email'].'</td>
                      <td>'.$row['Password'].'</td>
                      <td>'.$row['Password'].'</td>
                      <td>'.$row['Type'].'</td>
                      <td>'.$row['Status'].'</td>
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
    "sScrollXInner": "100%",
    "bScrollCollapse": true,
    "bAutoWidth": false,
    "sDom": 'T<"clear">lfrtip',
    "oTableTools": {
      "sSwfPath": "/jquery/datatables/extras/TableTools/media/swf/copy_csv_xls_pdf.swf",
      "aButtons": [
        {
          "sExtends": "pdf",
          "mColumns": [ 0, 1, 2, 3, 4, 7 ]
        },
        {
          "sExtends": "print"
        },
        {
          "sExtends": "xls",
          "mColumns": [ 0, 1, 2, 3, 4, 7 ]
        },
        {
          "sExtends": "copy",
          "mColumns": [ 0, 1, 2, 3, 4, 7 ]
        }
      ]
    },
    "aoColumnDefs": [
      { "bSearchable": false, "bVisible": false, "aTargets": [ 5 ] },
      { "bSearchable": false, "bVisible": false, "aTargets": [ 6 ] },
      { "bSearchable": false, "bVisible": false, "aTargets": [ 8 ] }
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
            height: 500,
            autoOpen: false, 
            modal: true
          }
        }
      ],
      sUpdateURL: "edit_user.php",
      sDeleteURL: "delete_user.php",
      /*sAddDeleteToolbarSelector: ".dataTables_length",*/
      oDeleteRowButtonOptions: {
        label: "Remove",
        icons: { primary: 'ui-icon-trash' }
      },
  });;

    $("#formEditUser").validate();
  });
</script>