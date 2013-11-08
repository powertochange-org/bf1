<?php
/*
 * Cru Doctrine
 * Admin - PageBuilder
 * Campus Crusade for Christ
 */

//page title
$title = 'Page Builder';

//get values
$new            = isset($_GET['page'])            ? false                     : true;

$id             = isset($_GET['page'])            ? $_GET['page']             : '';
$sectionId      = isset($_GET['section'])         ? $_GET['section']          : '';
$moduleId       = isset($_GET['module'])          ? $_GET['module']           : '';
$order          = isset($_GET['order'])           ? $_GET['order']            : '';

$pageTitle      = isset($_POST['pageTitle'])      ? $_POST['pageTitle']       : '';
$pageVisibility = isset($_POST['pageVisibility']) ? $_POST['pageVisibility']  : '';
$pageType       = isset($_POST['pageType'])       ? $_POST['pageType']        : '';

$errors         = isset($_POST['errors'])         ? $_POST['errors']          : '';

//header
include('../../header.php');

//content
?>

<link rel="stylesheet" type="text/css" media="screen" href="pagebuilder.css" />

<div id="content">
    <div id="pagebuilder">
        <div id="contentpane">
            <div id="pagetitle">
                <div id="title_text">Page Builder</div>
                <div id="steps">
                    <div <?php echo isset($_POST['p']) ? ($_POST['p'] == 'info'     ? 'class="active"' : '') : 'class="active"'; ?>>1. Page Information</div>
                    <div <?php echo isset($_POST['p']) ? ($_POST['p'] == 'design'   ? 'class="active"' : '') : '';               ?>>2. Page Design</div>
                </div>
            </div>
            <?php
                $page = '';
                if(isset($_POST['p'])){
                    switch($_POST['p']){
                        case 'info':
                            $page = 'information.php';
                            break;

                        case 'design':
                            $page = 'design.php';
                            break;
                    }
                } else {
                    $page = 'information.php';
                }
                include($page);
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
include('../../footer.php');
?>