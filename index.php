<?php
/* 
 * Cru Doctrine
 * Home Page
 * Campus Crusade for Christ
 */

//header
require('header.php');

try {
    //get modules from db
    $modules = array();
    
    //initialize the database object
    $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
    $db->connect();

    $sql = "SELECT * FROM module ORDER BY Ord;";

    //execute query and return to module array
    $modules = $db->fetch_array($sql);

    //fetch user's current module
    $sql = "SELECT m.Ord AS Module
            FROM progress pr
            INNER JOIN module m ON pr.ID = m.ID
            WHERE pr.Email = '".$db->escape($email)."'
            AND pr.Type = '".MODULE."'
            ORDER BY m.Ord DESC
            LIMIT 1;";
            
    $progress = $db->query_first($sql);
    $currentModule = $progress['Module'];

    $db->close();
}
catch (PDOException $e) {
    echo $e->getMessage();
}

?>
<link rel="stylesheet" type="text/css" media="screen" href="/css/home.css" />
<div id="content">
    <?php
        if($loggedin) {
            if(count($modules) > 0) {
                echo '<div id="slider">';
                foreach ($modules as $module) {
                    if($module['Number'] > 0) {
                        echo '  <div id="module'.str_replace('.', '', $module['Number']).'" class="module">
                                    <div class="slide">
                                        <div class="label"><span>Module '.number_format($module['Number'], 0).'</span></div>
                                        <div class="pic"><img src="'.$module['FrontImg'].'" height="300" width="515"></div>
                                        <div class="name">'.$module['Name'].'</div>
                                        <div class="desc">'.$module['Descr'].'</div>';
                        echo    ($module['Ord'] == 1 || $type <= COACH || $type == OTHER || $currentModule >= $module['Ord']) ? '<a class="start ui-corner-all ui-state-default" href="modules?m='.$module['ID'].'">Go To Module<span class="ui-icon ui-icon-circle-triangle-e"></span></a>' : '';
                        echo '      </div>
                                </div>';
                    }
                }
                echo '</div>';
            }
        }
        else {
          include(WELCOME . '.php');
        }
    ?>
</div>
<script type="text/javascript">
  $(function() {
      $('#module10').css('width', '555px').addClass('active');
  });

  $('.module').click(function() {
    if($(this).hasClass('active') == false) {

      //close current
      $('.active').animate({
              width: '45px'
          }, 500, function() {
              // Animation complete.
          });
      $('.active').removeClass('active');

      //open clicked module
      $(this).addClass('active');
      $(this).animate({
          width: '555px'
      }, 500, function() {
          // Animation complete.
      });

    }
  });

  //jquery class interaction states
  $('.remove, .addpage, .edit, button').addClass('ui-state-default');
</script>
<?php
//footer
require('footer.php');
?>