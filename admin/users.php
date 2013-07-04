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
  //user statuses
  $user_statuses = array();

  //initialize the database object
  $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
  $db->connect();

  //get users
  $sql = null;
  if ($type == SUPER) {
    $sql = "SELECT u.Email, u.FName, u.LName, u.Password, u.Type AS TypeID, u.Region AS RegionID, u.Loc, u.Reg_Date, u.Status AS StatusID, r.Name AS Region, s.Name AS Status, t.Name AS Type, c.Coach AS Coach_Email
            FROM user u
            INNER JOIN region r ON u.Region = r.ID
            INNER JOIN user_status s ON u.Status = s.ID
            INNER JOIN user_type t ON u.Type = t.ID
            LEFT JOIN coach c ON u.Email = c.Student
            WHERE u.Status = ".ACTIVE."
            ORDER BY Region, u.LName;";
  } 
  else if ($type == REGIONAL_ADMIN) {
    $sql = "SELECT u.Email, u.FName, u.LName, u.Password, u.Type AS TypeID, u.Region AS RegionID, u.Loc, u.Reg_Date, u.Status AS StatusID, r.Name AS Region, s.Name AS Status, t.Name AS Type, c.Coach AS Coach_Email
            FROM user u
            INNER JOIN region r ON u.Region = r.ID
            INNER JOIN user_status s ON u.Status = s.ID
            INNER JOIN user_type t ON u.Type = t.ID
            LEFT JOIN coach c ON u.Email = c.Student
            WHERE u.Region = ".$region." AND
            u.Status = ".ACTIVE."
            ORDER BY Region, u.LName;";
  } 
  else {
    $sql = "SELECT u.Email, u.FName, u.LName, u.Password, u.Type AS TypeID, u.Region AS RegionID, u.Loc, u.Reg_Date, u.Status AS StatusID, r.Name AS Region, s.Name AS Status, t.Name AS Type, c.Coach AS Coach_Email
            FROM user u
            INNER JOIN region r ON u.Region = r.ID
            INNER JOIN user_status s ON u.Status = s.ID
            INNER JOIN user_type t ON u.Type = t.ID
            LEFT JOIN coach c ON u.Email = c.Student
            WHERE c.Coach = '".$db->escape($email)."' AND
            u.Status = ".ACTIVE."
            ORDER BY Region, u.LName;";
  }
  $users = $db->fetch_array($sql);

  //get assigned coach
  foreach ($users as &$user) {
    if (!is_null($user['Coach_Email'])) {
      $sql = "SELECT u.FName AS Coach_FName, u.LName AS Coach_LName 
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
  $coaches = getActiveCoaches($db);

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
} catch (PDOException $e) {
  echo $e->getMessage();
}
?>

<form id="formAddNewRow" action="add_user.php">
    <input type="hidden" name="id" id="id" class="DT_RowId" />
    <h2>New User</h2>
    <fieldset id="user">
      <div>
        <label>First Name</label>
        <input type="text" name="firstName" placeholder="First Name" value="" rel="0" class="required"/>
      </div>
      <div>
        <label>Last Name</label>
        <input type="text" name="lastName" placeholder="Last Name" value="" rel="1" class="required"/>
      </div>
      <div>
        <label>Email</label>
        <input type="text" name="email" placeholder="Email" value="" rel="2" class="required"/>
      </div>
      <div>
          <label>Password</label>
          <input type="password" name="password" placeholder="Password" value="" rel="3" class="required"/>
      </div>
      <div>
          <label>Confirm Password</label>
          <input type="password" name="confirmPassword" rel="4" placeholder="Confirm Password" value="" class="required"/>
      </div>
      <div>
         <label>Type</label>
         <select name="type" rel="5" class="required">
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
        <input type="hidden" name="regDate" value="<?php echo date('Ymd'); ?>" rel="6"/>
      </div>
      <div>
        <input type="hidden" name="status" value="<?php echo ACTIVE; ?>" rel="7"/>
      </div>
      <div>
        <label>Region</label>
        <input id="regionName" name="regionName" placeholder="Type Name" type="text" class="required" rel="8"/>
        <input id="region" name="region" type="hidden" value="" rel="9"/>
      </div>
      <div>
        <label>Coach</label>
        <input id="coachName" name="coachName" placeholder="Type Name" type="text" rel="10"/>
        <input id="coach" name="coach" type="hidden" value="" rel="11"/>
      </div>
      <span class="datafield" style="display:none" rel="12"><a class="table-action-EditUser">Edit</a></span>
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
        <input type="text" name="firstName" value="" placeholder="First Name" rel="0" class="required"/>
      </div>
      <div>
        <label>Last Name</label>
        <input type="text" name="lastName" value="" placeholder="Last Name" rel="1" class="required"/>
      </div>
      <div>
        <label>Email</label>
        <input type="text" name="email" value="" placeholder="Email" rel="2" class="required"/>
      </div>
      <div>
          <label>Password</label>
          <input type="password" name="password" placeholder="Password" value="" rel="3" class="required"/>
      </div>
      <div>
          <label>Confirm Password</label>
          <input type="password" name="confirmPassword" placeholder="Confirm Password" rel="4" value="" class="required"/>
      </div>
      <div>
         <label>Type</label>
         <select name="type" rel="5" class="required">
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
        <input type="hidden" name="regDate" value="" rel="6"/>
      </div>
      <div>
        <label>Status</label>
        <select name="status" rel="7" class="required">
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
      <div>
        <label>Region</label>
        <input id="regionName" name="regionName" placeholder="Type Name" type="text" class="required" rel="8"/>
        <input id="region" name="region" type="hidden" value="" rel="9"/>
      </div>
      <div>
        <label>Coach</label>
        <input id="coachName" name="coachName" placeholder="Type Name" type="text" rel="10"/>
        <input id="coach" name="coach" type="hidden" value="" rel="11"/>
      </div>
      <span class="datafield" style="display:none" rel="12"><a class="table-action-EditUser">Edit</a></span>
    </fieldset>
    <button id="formEditUserOk" type="submit">Save</button>
    <button id="formEditUserCancel" type="button">Cancel</button>
</form>

<div id="users">
  <div id="custom_toolbar">
    <form id="formArchiveUsers" action="" method="post">
      <?php
        if ($type == SUPER) {
          echo '<button type="submit" class="ui-corner-right corners-all shadow-light button" value="submit"><span class="ui-icon ui-icon-pause"></span>Archive Users</button>';
        }
      ?>
    </form>
  </div>
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
            <th>Region ID</th>
            <th>Coach</th>
            <th>Coach Email</th>
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
                        <td>'.$row['Status'].'</td>
                        <td>'.$row['Region'].'</td>
                        <td>'.$row['RegionID'].'</td>
                        <td>'.$row['Coach_FName'].' '.$row['Coach_LName'].'</td>
                        <td>'.$row['Coach_Email'].'</td>
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
          "mColumns": [ 0, 1, 2, 5, 8, 10 ]
        },
          {
          "sExtends": "print"
        },
          {
          "sExtends": "xls",
          "mColumns": [ 0, 1, 2, 5, 8, 10 ]
        },
          {
          "sExtends": "copy",
          "mColumns": [ 0, 1, 2, 5, 8, 10 ]
        }
        ]
      },
      "aoColumnDefs": [
        { "bSearchable": false, "bVisible": false, "aTargets": [ 3 ] },
        { "bSearchable": false, "bVisible": false, "aTargets": [ 4 ] },
        { "bSearchable": false, "bVisible": false, "aTargets": [ 6 ] },
        { "bSearchable": false, "bVisible": false, "aTargets": [ 7 ] },
        { "bSearchable": false, "bVisible": false, "aTargets": [ 9 ] },
        { "bSearchable": false, "bVisible": false, "aTargets": [ 11 ] }
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
        height: 500,
        autoOpen: false,
        modal: true
      }
    });;

    $("#formAddNewRow").validate();
    $("#formEditUser").validate();
  });

  $('#formAddNewRow #coachName').typeahead({
    name: 'Coach',
    valueKey: 'name',
    local: <?php echo json_encode($coaches); ?>
  }).on('typeahead:opened', function(event) {
    $('#formAddNewRow #coach').val('');
    $('#formAddNewRow #coachName').val('');
    $('#formAddNewRow #coachName').typeahead('setQuery', '');
  }).on('typeahead:selected', function(event, datum) {
    $('#formAddNewRow #coach').val(datum.id);
  }).on('typeahead:autocompleted', function(event, datum) {
    $('#formAddNewRow #coach').val(datum.id);
  });

  $('#formAddNewRow #regionName').typeahead({
    name: 'Region',
    valueKey: 'name',
    local: <?php echo json_encode($regions); ?>
  }).on('typeahead:opened', function(event) {
    $('#formAddNewRow #region').val('');
    $('#formAddNewRow #regionName').val('');
    $('#formAddNewRow #regionName').typeahead('setQuery', '');
  }).on('typeahead:selected', function(event, datum) {
    $('#formAddNewRow #region').val(datum.id);
  }).on('typeahead:autocompleted', function(event, datum) {
    $('#formAddNewRow #region').val(datum.id);
  });

  $('#formEditUser #coachName').typeahead({
    name: 'Coach',
    valueKey: 'name',
    local: <?php echo json_encode($coaches); ?>
  }).on('typeahead:opened', function(event) {
    $('#formEditUser #coach').val('');
    $('#formEditUser #coachName').val('');
    $('#formEditUser #coachName').typeahead('setQuery', '');
  }).on('typeahead:selected', function(event, datum) {
    $('#formEditUser #coach').val(datum.id);
  }).on('typeahead:autocompleted', function(event, datum) {
    $('#formEditUser #coach').val(datum.id);
  });

  $('#formEditUser #regionName').typeahead({
    name: 'Region',
    valueKey: 'name',
    local: <?php echo json_encode($regions); ?>
  }).on('typeahead:opened', function(event) {
    $('#formEditUser #region').val('');
    $('#formEditUser #regionName').val('');
    $('#formEditUser #regionName').typeahead('setQuery', '');
  }).on('typeahead:selected', function(event, datum) {
    $('#formEditUser #region').val(datum.id);
  }).on('typeahead:autocompleted', function(event, datum) {
    $('#formEditUser #region').val(datum.id);
  });

  $('#formArchiveUsers').submit(function() {
    $.ajax({
      url: "archive_users.php",
      type: "post",
      dataType: "html",
      success: function(msg) {
        //append form to DOM and display a dialog
        $('#contentpane').append(msg);
        $('#formArchiveUsersResponse').dialog({
          title: "Archive Users",
          buttons: {
            "Ok": function() {
              $(this).dialog("close");
            }
          },
          close: function() {
            $(this).dialog( "destroy" );
            $('#formArchiveUsersResponse').remove();
          },
          width: 'auto',
          resizable: false,
          modal: true
        });
      }
    });

    //prevent form from submitting traditionaly
    return false;
  });
</script>