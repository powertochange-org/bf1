<?php
/*
 * Cru Doctrine
 * Admin - Progress Report
 * Campus Crusade for Christ
 */
try {
  //get session values
  $email  = isset($_SESSION['email'])  ? $_SESSION['email']  : '';
  $type   = isset($_SESSION['type'])   ? $_SESSION['type']   : '';
  $region = isset($_SESSION['region']) ? $_SESSION['region'] : '';

  //modules
  $modules = array();
  //users
  $users = array();
  //regions
  $regions = array();

  //report string
  $str_report = '';

  //initialize the database object
  $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
  $db->connect();

  //get modules for the column names
  $modulesSQL = "SELECT ID, Number, Name
                 FROM  module
                 ORDER BY Number;";

  $modules = $db->fetch_array($modulesSQL);

  //get users & regions
  $usersSQL = null;
  $regionSQL = null;

  if ($type == SUPER) {
    $usersSQL = "SELECT u.Email, u.FName, u.LName, u.Region AS RegionID, r.Name AS Region
                 FROM user u
                 INNER JOIN region r ON u.Region = r.ID
                 ORDER BY Region, u.LName, u.FName;";

    $regionSQL = "SELECT ID, Name
                  FROM  region
                  ORDER BY Name;";
  }
  else if ($type == REGIONAL_ADMIN) {
    $usersSQL = "SELECT u.Email, u.FName, u.LName, u.Region AS RegionID, r.Name AS Region
                 FROM user u
                 INNER JOIN region r ON u.Region = r.ID
                 WHERE u.Region = ".$region."
                 ORDER BY Region, u.LName, u.FName;";

    $regionSQL = "SELECT ID, Name
                  FROM  region r
                  INNER JOIN user u ON r.ID = u.Region
                  WHERE u.Email = '".$db->escape($email)."' 
                  ORDER BY Name;";
  }
  else {
    $usersSQL = "SELECT u.Email, u.FName, u.LName, u.Region AS RegionID, r.Name AS Region
                 FROM user u
                 INNER JOIN region r ON u.Region = r.ID
                 LEFT JOIN coach c ON u.Email = c.Student
                 WHERE c.Coach = '".$db->escape($email)."'
                 ORDER BY Region, u.LName, u.FName;";

    $regionSQL = "SELECT ID, Name
                  FROM  region r
                  INNER JOIN user u ON r.ID = u.Region
                  WHERE u.Email = '".$db->escape($email)."' 
                  ORDER BY Name;";
  }
  $users = $db->fetch_array($usersSQL);
  $regions = $db->fetch_array($regionSQL);

  //get user's progress
  if(count($users) > 0) {
    foreach ($users as &$user) {
      $sql = "SELECT ID
              FROM progress pr
              WHERE Email = '".$db->escape($user['Email'])."'
              AND Type = '".MODULE."'
              AND Status = '".COMPLETE."'
              ORDER BY ID ASC";
      $progress = $db->fetch_array($sql);
      $user['Progress'] = $progress;
    }
  }
  //break the reference with the last element
  unset($user); 

  //iterate through the regions, and the teams' progress and build the report
  if(count($regions) > 0) {
    foreach ($regions as $region) {
      $str_report .= '<div class="team" id="'.$region['ID'].'">
                        <div class="name">
                           <span class="ui-icon ui-icon-triangle-1-e"></span>'.PHP_EOL;
      $str_report .=       (($type == COACH) ? 'My Team' : $region['Name']).PHP_EOL;
      $str_report .=   '</div>
                        <span class="check"></span>
                        <div class="progress">
                          <div id="progress-report" class="progress-table">
                            <div class="progress-top">
                              <div class="progress-top-cell" style="text-align:left; width:115px;">
                              </div>'.PHP_EOL;
                      if(count($modules) > 0) {
                        foreach ($modules as $module) {
      $str_report .=         '<div class="progress-top-cell">'.PHP_EOL;
      $str_report .=            $module['Number'].PHP_EOL;
      $str_report .=         '</div>'.PHP_EOL;
                        }
                      }
      $str_report .=       '</div>
                            <div class="progress-middle">'.PHP_EOL;
                      if(count($users) > 0) {
                        foreach ($users as $user) {
                          if ($user['RegionID'] == $region['ID'] || ($type == COACH)) {
      $str_report .=         '<div class="progress-left">'.PHP_EOL;
      $str_report .=            ($user['FName'].' '.$user['LName']).PHP_EOL;
      $str_report .=         '</div>'.PHP_EOL;
                            if(count($modules) > 0) {
                              foreach ($modules as $module) {
      $str_report .=         '<div class="progress-right">'.PHP_EOL;
                                if (in_array_r($module['ID'], $user['Progress'])) {
      $str_report .=           'X'.PHP_EOL;
                                }
      $str_report .=         '</div>'.PHP_EOL;
                              }
                            }
                          }
                        }
                      }
      $str_report .=         '<div class="progress-bottom">
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>'.PHP_EOL;
    }
  }

   $db->close();
} 
catch (PDOException $e) {
    echo $e->getMessage();
}
?>

<link rel="stylesheet" type="text/css" media="screen" href="reports.css" />
<div id="progress-report">
  <?php echo $str_report; ?>
</div>

<script type="text/javascript">
  $('.team .ui-icon-triangle-1-e').toggle(
    function() {
      expand($(this), 'progress');
    },
    function(){
      colapse($(this), 'progress');
    }
  );

  $('.team .ui-icon-triangle-1-s').toggle(
    function() {
      colapse($(this), 'progress');
    },
    function() {
      expand($(this), 'progress');
    }
  );

  function colapse(object, type) {
    object.addClass('ui-icon-triangle-1-e').removeClass('ui-icon-triangle-1-s');
    object.parent().siblings('.'+type).slideUp('fast');
  }

  function expand(object, type) {
    object.addClass('ui-icon-triangle-1-s').removeClass('ui-icon-triangle-1-e');
    object.parent().siblings('.'+type).slideDown('fast');
  }
</script>