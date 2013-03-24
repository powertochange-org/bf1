<?php
/*
 * Cru Doctrine
 * My Profile
 * Campus Crusade for Christ
 */

//ensure user authentication
$auth = false;

session_start();
if(isset($_SESSION['email'])){
    $auth = true;
}

if(!$auth){
    header('Location: /#login');
}

//page title
$title = 'My Profile';

//header
include('../header.php');

require_once("../config.inc.php"); 
require_once("../Database.singleton.php");
require_once("../function.inc.php");

//get url values
$page     = isset($_GET['p'])  ? $_GET['p']  : PROFILE;
$fullPage = $page.'.php';

//content
?>
<link rel="stylesheet" type="text/css" media="screen" href="profile.css" />
<div id="content">
  <div id="profile">
    <div id="pagetitle">
        <?php echo $title?>
    </div>
    <div id="leftmenu">
      <ul>
        <li><a href="?p=<?php echo PROFILE ?>" class="<?php echo $page == PROFILE ? 'active' : ''; ?>">View Profile</a></li>
        <li><a href="?p=<?php echo CHANGE_PASSWORD ?>" class="<?php echo $page == CHANGE_PASSWORD ? 'active' : ''; ?>">Change Password</a></li>
      </ul>
    </div>
    <div id="contentpane">
      <?php
        include($fullPage);
      ?>
    </div>
  </div>
</div>
<script type="text/javascript">
  function load(page) {
    $.ajax({
      type: "POST",
      url: page,
      success: function(msg){
        $('#contentpane').html(msg);
      }
    });
  }
</script>
<?php
//footer
include('../footer.php');
?>