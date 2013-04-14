<?php
/*
 * Cru Doctrine
 * Admin - Archived Disciples Report
 * Campus Crusade for Christ
 */
try {
  $disciples  = array();
  $_disciples = array();

  $str_report = '';

  //initialize the database object
  $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
  $db->connect();

  //get the disciples assigned to the current user and their responses
  $sql = "SELECT u.Email, u.FName, u.LName, u.Reg_Date, m.ID AS ModuleID, m.Number AS ModuleNumber, m.Name AS ModuleName, r.InputId AS ResponseID, s.Title AS Section, p.ID AS PageID, p.Title AS Page, i.Question, r.Response
          FROM response r
          INNER JOIN user     u    ON r.Email      = u.Email
          LEFT  JOIN coach    c    ON u.Email      = c.Student
          INNER JOIN input    i    ON r.InputId    = i.ID
          INNER JOIN element  e    ON i.ID         = e.ElementId
          INNER JOIN page     p    ON e.PageId     = p.ID
          INNER JOIN section  s    ON p.SectionId  = s.ID
          INNER JOIN module   m    ON s.ModuleId   = m.ID
          WHERE u.Status = ".INACTIVE."
          AND c.Coach = '".$db->escape($email)."'
          AND r.Coach = 1
          ORDER BY m.Ord, s.Ord, p.Ord";
  $disciples = $db->fetch_array($sql);

  //iterate through the list and build a multi-dimensional array
  foreach($disciples as $disciple) {
      $parsed_date = array();
      $parsed_date = date_parse($disciple['Reg_Date']);
      $year = $parsed_date['year'];
      $email                                                                               = $disciple['Email'];
      $_disciples[$year][$email]['Email']                                                  = $disciple['Email'];
      $_disciples[$year][$email]['FName']                                                  = $disciple['FName'];
      $_disciples[$year][$email]['LName']                                                  = $disciple['LName'];
      $module                                                                              = $disciple['ModuleID'];
      $_disciples[$year][$email]['Modules'][$module]['ModuleID']                           = $disciple['ModuleID'];
      $_disciples[$year][$email]['Modules'][$module]['ModuleNumber']                       = $disciple['ModuleNumber'];
      $_disciples[$year][$email]['Modules'][$module]['ModuleName']                         = $disciple['ModuleName'];
      $response                                                                            = $disciple['ResponseID'];
      $_disciples[$year][$email]['Modules'][$module]['Responses'][$response]['ResponseID'] = $disciple['ResponseID'];
      $_disciples[$year][$email]['Modules'][$module]['Responses'][$response]['Section']    = $disciple['Section'];
      $_disciples[$year][$email]['Modules'][$module]['Responses'][$response]['PageID']     = $disciple['PageID'];
      $_disciples[$year][$email]['Modules'][$module]['Responses'][$response]['Page']       = $disciple['Page'];
      $_disciples[$year][$email]['Modules'][$module]['Responses'][$response]['Question']   = $disciple['Question'];
      $_disciples[$year][$email]['Modules'][$module]['Responses'][$response]['Response']   = $disciple['Response'];
  }

  //iterate through the multi-dimensional array and build the report
  if (count($_disciples) > 0) {
    foreach ($_disciples as $year => $disciples_by_year) {      
      $str_report .= '<div class="year" id="'.$year.'">
                        <div class="name">
                           <span class="ui-icon ui-icon-triangle-1-e"></span>
                           '.$year.'
                        </div>
                        <span class="check"></span>
                        <div class="disciples">'.PHP_EOL;
      //iterate through the disciples
      foreach ($disciples_by_year as $disciple) {
        $str_report .=   '<div class="disciple" id="'.$disciple['Email'].'">
                            <div class="name">
                               <span class="ui-icon ui-icon-triangle-1-e"></span>
                               '.$disciple['FName'].' '.$disciple['LName'].'
                            </div>
                            <span class="check"></span>
                            <div class="modules">'.PHP_EOL;
        //iterate through the modules
        foreach ($disciple['Modules'] as $module) {
          $str_report .=     '<div class="module" id="'.$module['ModuleID'].'">
                               <div class="name">
                                 <span class="ui-icon ui-icon-triangle-1-e"></span>
                                 Module '.number_format($module['ModuleNumber'], 0).' - '.$module['ModuleName'].'
                               </div>
                               <span class="check"></span>
                               <div class="flags">'.PHP_EOL;
          //iterate through the responses
          foreach ($module['Responses'] as $response) {
            $str_report .=      '<div class="flag" id="'.$response['ResponseID'].'">
                                  <span class="icon"></span>
                                  <span class="question"><a href="/modules/?p='.$response['PageID'].'">'.$response['Section'].' - '.$response['Page'].'</a>: <br/>'.$response['Question'].'</span>
                                  <span class="response">'.$response['Response'].'</span>
                                 </div>'.PHP_EOL;
          }
          $str_report .= '</div>'.PHP_EOL.'</div>'.PHP_EOL;
        }
        $str_report .= '</div>'.PHP_EOL.'</div>'.PHP_EOL;
      }
      $str_report .= '</div>'.PHP_EOL.'</div>'.PHP_EOL;
    }
  }
  else {
    $str_report = '<div>You currently have no responses to review.</div>'.PHP_EOL;
  }

  $db->close();
} 
catch (PDOException $e) {
    echo $e->getMessage();
}
?>

<link rel="stylesheet" type="text/css" media="screen" href="reports.css" />
<div id="disciples-report">
  <div id="list">
      <?php echo $str_report; ?>
  </div>
</div>

<script type="text/javascript">
  $('.year .ui-icon-triangle-1-e').toggle(
    function() {
      expand($(this), 'disciples');
    },
    function() {
      colapse($(this), 'disciples');
    }
  );

  $('.year .ui-icon-triangle-1-s').toggle(
    function() {
      colapse($(this), 'disciples');
    },
    function() {
      expand($(this), 'disciples');
    }
  );

  $('.disciple .ui-icon-triangle-1-e').toggle(
    function() {
      expand($(this), 'modules');
    },
    function() {
      colapse($(this), 'modules');
    }
  );

  $('.disciple .ui-icon-triangle-1-s').toggle(
    function() {
      colapse($(this), 'modules');
    },
    function() {
      expand($(this), 'modules');
    }
  );

  $('.module .ui-icon-triangle-1-e').toggle(
    function() {
      expand($(this), 'flags');
    },
    function() {
      colapse($(this), 'flags');
    }
  );

  $('.module .ui-icon-triangle-1-s').toggle(
    function() {
      colapse($(this), 'flags');
    },
    function() {
      expand($(this), 'flags');
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