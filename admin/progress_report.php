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

  //report array
  $_teams = array();
  //report string
  $str_report = '';

  //initialize the database object
  $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
  $db->connect();

  //get modules for the column names
  $modulesSQL = "SELECT ID, Number, Name, Ord
                 FROM  module
                 ORDER BY Ord;";

  $modules = $db->fetch_array($modulesSQL);

  //get users & regions
  $usersSQL = null;

  if ($type == SUPER) {
    $usersSQL = "SELECT u.Email, u.FName, u.LName, u.Region AS RegionID, r.Name AS Region
                 FROM user u
                 INNER JOIN region r ON u.Region = r.ID
                 WHERE u.Type > ".COACH."
                 ORDER BY Region, u.LName, u.FName;";
  }
  else if ($type == REGIONAL_ADMIN) {
    $usersSQL = "SELECT u.Email, u.FName, u.LName, u.Region AS RegionID, r.Name AS Region
                 FROM user u
                 INNER JOIN region r ON u.Region = r.ID
                 WHERE u.Region = ".$region."
                 AND u.Type > ".COACH."
                 ORDER BY Region, u.LName, u.FName;";
  }
  else {
    $usersSQL = "SELECT u.Email, u.FName, u.LName, u.Region AS RegionID, r.Name AS Region
                 FROM user u
                 INNER JOIN region r ON u.Region = r.ID
                 LEFT JOIN coach c ON u.Email = c.Student
                 WHERE c.Coach = '".$db->escape($email)."'
                 ORDER BY Region, u.LName, u.FName;";
  }
  $users = $db->fetch_array($usersSQL);

  //get user's progress
  $lastModule   = $modules[(count($modules)-1)]['ID'];
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
      (in_array_r($lastModule, $user['Progress'])) ? $user['Finished'] = true : $user['Finished'] = false;
    }
  }
  //break the reference with the last element
  unset($user);

  //iterate through the lists and build a multi-dimensional array
  foreach ($users as $user) {
    $regionID                                            = (($type == COACH) ? $email : $user['RegionID']);
    $_teams[$regionID]['ID']                             = $regionID;
    $_teams[$regionID]['Name']                           = (($type == COACH) ? 'My Team' : $user['Region']);
    $userEmail                                           = $user['Email'];
    $_teams[$regionID]['Users'][$userEmail]['Email']     = $user['Email'];
    $_teams[$regionID]['Users'][$userEmail]['FName']     = $user['FName'];
    $_teams[$regionID]['Users'][$userEmail]['LName']     = $user['LName'];
    $_teams[$regionID]['Users'][$userEmail]['Progress']  = $user['Progress'];
    $_teams[$regionID]['Users'][$userEmail]['Finished']  = $user['Finished'];
    if($user['Finished']) {
      $_teams[$regionID]['FinishedCount'] != null ? $_teams[$regionID]['FinishedCount']++ : $_teams[$regionID]['FinishedCount'] = 1;
    }
    if(count($user['Progress']) > 0) {
      $_teams[$regionID]['CompletedModules'][] = $user['Progress'][(count($user['Progress'])-1)]['ID'];
    }
  }

  //iterate through the multi-dimensional array and build the report
  if(count($_teams) > 0) {
    foreach ($_teams as $team) {
      $str_report .= '<div class="team" id="'.$team['ID'].'">
                        <div class="name">
                           <span class="ui-icon ui-icon-triangle-1-e"></span>'.PHP_EOL;
      $str_report .=       $team['Name'].' ('.(isset($team['FinishedCount']) ? ($team['FinishedCount']/count($team['Users'])*100) : 0).'% Complete | Avg. Module Completed: '.(isset($team['CompletedModules']) ? round((array_sum($team['CompletedModules'])/count($team['Users']))) : 'None').')'.PHP_EOL;
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
                      if(count($team['Users']) > 0) {
                        foreach ($team['Users'] as $user) {
      $str_report .=      '<div class="progress-left">'.PHP_EOL;
      $str_report .=          ($user['FName'].' '.$user['LName']).PHP_EOL;
      $str_report .=      '</div>'.PHP_EOL;
                          if(count($modules) > 0) {
                            foreach ($modules as $module) {
      $str_report .=          '<div class="progress-right">'.PHP_EOL;
                              if (in_array_r($module['ID'], $user['Progress'])) {
      $str_report .=            'X'.PHP_EOL;
                              }
      $str_report .=          '</div>'.PHP_EOL;
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