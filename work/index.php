<?php
/*
 * Cru Doctrine
 * My Work
 * Campus Crusade for Christ
 */

//header
include('../header.php');

if(!$loggedin){
    header('Location: /#login');
}

//page title
$title = 'My Work';

$modules    = array();
$progress   = array();
$flags      = array();
$notes      = array();

try {
    global $modules, $progress, $flags, $notes;

    //initialize the database object
    $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
    $db->connect();     
        
    $sql = "SELECT * FROM module WHERE Number > 0 ORDER BY Ord;";

    //get modules
    $modules = $db->fetch_array($sql);

    //get progress
    $sql = "SELECT pr.Status, p.ID AS page, s.ID AS section, m.ID AS module, pr.`Update`
            FROM progress pr
            INNER JOIN page p ON pr.ID = p.ID
            INNER JOIN section s ON p.SectionId = s.ID
            INNER JOIN module m ON s.ModuleId = m.ID
            WHERE pr.Email = '".$db->escape($email)."'
            AND pr.Type = '".PAGE."'
            ORDER BY m.Ord, s.Ord, p.Ord";

    $progress = $db->fetch_array($sql);

    //get flags
    $sql = "SELECT r.*, s.Title AS Section, p.Title AS Page, p.ID AS PageId, i.Question, m.ID AS module
            FROM response r
            INNER JOIN input    i    ON r.InputId    = i.ID
            INNER JOIN element  e    ON i.ID         = e.ElementId
            INNER JOIN page     p    ON e.PageId     = p.ID
            INNER JOIN section  s    ON p.SectionId  = s.ID
            INNER JOIN module   m    ON s.ModuleId   = m.ID
            WHERE r.Email = '".$db->escape($email)."'
            AND (r.Personal = 1 OR r.Coach = 1)
            ORDER BY m.Ord, s.Ord, p.Ord";

    $flags = $db->fetch_array($sql);

    //get notes
    $sql = "SELECT n.*, s.Title AS Section, p.Title AS Page, p.ID AS PageId, m.ID AS module
            FROM note n
            INNER JOIN element  e    ON n.ElementId  = e.ElementID
            INNER JOIN page     p    ON e.PageId     = p.ID
            INNER JOIN section  s    ON p.SectionId  = s.ID
            INNER JOIN module   m    ON s.ModuleId   = m.ID
            WHERE n.Email = '".$db->escape($email)."'
            ORDER BY m.Ord, s.Ord, p.Ord, e.ElementId";

    $notes = $db->fetch_array($sql);
    
    $db->close();

} catch (PDOException $e){
    echo $e->getMessage();
}

//process data
$_modules   = '';
foreach($modules as $module) {
    $id                         = $module['ID'];
    $_modules[$id]              = $module;
    $_modules[$id]['status']    = 'incomplete';
}

foreach($progress as $prog) {
    $id                         = $prog['module'];
    $_modules[$id]['page']      = $prog['page'];
    $_modules[$id]['status']    = $prog['Status'];
    $_modules[$id]['update']    = $prog['Update'];
}

foreach($flags as $flag) {
    $id                         = $flag['module'];
    $_modules[$id]['flags'][]   = $flag;
}

foreach($notes as $note) {
    $id                         = $note['module'];
    $_modules[$id]['notes'][]   = $note;
}

//calculate eta
//TODO: Determine how to account for periods of inactivity
$cur_mod    = $_modules[$progress[count($progress)-1]['module']]['Ord'];
$rec_date   = date_create($progress[count($progress)-1]['Update']);
$beg_date   = date_create($progress[0]['Update']);
//$dif_time   = date_diff($beg_date,$rec_date);
//$avg_time   = ($dif_time->format("%d") + ($dif_time->format("%m")*30) + ($dif_time->format("%Y")*365) ) / ($cur_mod > 1 ? $cur_mod - 1 : 1);
//$tot_time   = $avg_time * count($modules);
//$eta        = date_add($beg_date, date_interval_create_from_date_string($tot_time.' days'));

//create strings
$str_bar = '';
$str_sta = '';
$str_mod = '';
$bar_wid = floor((1 / (count($_modules)))*99);

$i = 0;
foreach($_modules as $module) {
  $bar_class= '';

  if ($i == 0) {
    $bar_class = 'corners-left';
  } 
  elseif ($i == (count($modules)-1)) {
    $bar_class = 'corners-right';
  }

  $i++;
  $str_bar .= '<div class="bar '.$module['status'].' '.$bar_class.'" id="'.$module['Ord'].'" style="width:'.$bar_wid.'%;"></div>'.PHP_EOL;
  if($module['status'] == STARTED) {
    $str_sta = '<div class="name">Module '.number_format($module['Number'], 0).'<br />'.$module['Name'].'</div>
                <a href="/modules/?p='.$module['page'].'" class="corners-all ui-state-default">Continue<span class="ui-icon ui-icon-triangle-1-e"></span></a>';
  }

  if ($module['status'] != 'incomplete') {
    $str_mod .= '<div class="module" id="'.$module['ID'].'">
                   <div class="name">
                     <span class="ui-icon ui-icon-triangle-1-'.($module['Ord'] == $cur_mod ? 's' : 'e').'"></span>
                     Module '.number_format($module['Number'], 0).' - '.$module['Name'].'
                   </div>
                   <span class="check"></span>
                   <div class="flagsnotes '.($module['Ord'] == $cur_mod ? 'active' : '').'">'.PHP_EOL;
    if ($flags) {
      if(array_key_exists('flags', $module)) {
        foreach($module['flags'] as $flag) {
          $str_mod .= '<div class="flag" id="'.$flag['InputId'].'">
                         <span class="icon"></span>
                         <span class="question"><a href="/modules/?p='.$flag['PageId'].'">'.$flag['Section'].' - '.$flag['Page'].'</a>: <br />'.$flag['Question'].'</span>
                         <span class="response">'.$flag['Response'].'</span>
                       </div>'.PHP_EOL;
        }
      }
    }
    if ($notes) {
      if(array_key_exists('notes', $module)) {
        foreach($module['notes'] as $note) {
          $str_mod .= '<div class="note" id="'.$note['ElementId'].'">
                         <span class="icon"></span>
                         <a href="/modules/?p='.$note['PageId'].'">'.$note['Section'].' - '.$note['Page'].'</a>
                         <span>'.$note['Note'].'</span>
                       </div>'.PHP_EOL;
        }
      }
    }
    $str_mod .= '</div>'.PHP_EOL.'</div>'.PHP_EOL;
  }
}
?>

<link rel="stylesheet" type="text/css" media="screen" href="work.css" />
<div id="content">
    <div id="work">
        <div id="pagetitle">
            My Work
        </div>
        <div id="contentpane">
            <div id="progress">
                <div id="bar">
                    <?php echo $str_bar; ?>
                </div>
                <?php
                  if ($str_sta != '') {
                    echo '<div id="status" class="corners-all"><div class="point"></div>'.$str_sta.'</div>';
                  }
                  else {
                    echo '<div id="complete">Congratulations on completing Biblical Foundations 1!</div>';
                  }
                ?>
            </div>
            <div id="modules"><?php echo $str_mod; ?></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('.module .ui-icon-triangle-1-e').toggle(
      function(){
        expand($(this));
      },

      function(){
        colapse($(this));
      }
    );

    $('.module .ui-icon-triangle-1-s').toggle(
      function(){
        colapse($(this));
      },

      function(){
        expand($(this));
      }
    );

    function colapse(object) {
      object.addClass('ui-icon-triangle-1-e').removeClass('ui-icon-triangle-1-s');
      object.parent().siblings('.flagsnotes').slideUp('fast');
    }

    function expand(object) {
      object.addClass('ui-icon-triangle-1-s').removeClass('ui-icon-triangle-1-e');
      object.parent().siblings('.flagsnotes').slideDown('fast');
    }
</script>

<script type="text/javascript">
  var cur_mod = "<?php echo ($cur_mod); ?>";
  var bar = $('#'+cur_mod+'.bar');
  $("#status").position({
    my: "top",
    at: "left bottom",
    of: "#"+cur_mod+".bar",
    offset: "0, 10"
  });
</script>

<?php
//footer
include('../footer.php');
?>
