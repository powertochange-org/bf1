<?php
/*
 * Cru Doctrine
 * Admin
 * Campus Crusade for Christ
 */

require_once("../config.inc.php"); 
require_once("../Database.singleton.php");

//check user authorization
$auth = false;

session_start();
if($_SESSION['type'] == SUPER || $_SESSION['type'] == REGIONAL_ADMIN || $_SESSION['type'] == COACH){
    $auth = true;
}

if(!$auth){
    header('Location: /#login');
}
//end check user authorization

//page title
$title  = 'Administration';

//header
include('../header.php');

//get page variables
$page   = isset($_GET['p'])         ? $_GET['p']        : 'users';
$page   = isset($_GET['request'])   ? $_GET['request']  : $page;
$id     = isset($_GET['id'])        ? $_GET['id']       : false;

switch($page) {
    case MODULES:
      if($id) {
        $page = MODULE;
      }
      break;
    case REPORTS:
      if($id) {
        $page = $id;
      }
      break;
}

$page  .= '.php';

//content
?>

<link rel="stylesheet" type="text/css" media="screen" href="admin.css" />

<!--uploadify-->
<script type="text/javascript" src="/jquery/uploadify/jquery.uploadify.v2.1.0.js"></script>
<script type="text/javascript" src="/jquery/uploadify/swfobject.js"></script>
<link rel="stylesheet" href="/jquery/uploadify/uploadify.css" type="text/css" />
<!--datatables-->
<script type="text/javascript" src="/jquery/datatables/media/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="/jquery/datatables/media/css/jquery.dataTables.css" type="text/css" />
<script type="text/javascript" src="/jquery/datatables/extras/TableTools/media/js/TableTools.min.js"></script>
<link rel="stylesheet" href="/jquery/datatables/extras/TableTools/media/css/TableTools.css" type="text/css" />
<script type="text/javascript" src="/jquery/datatables-editable/media/js/jquery.jeditable.js"></script>
<script type="text/javascript" src="/jquery/datatables-editable/media/js/jquery.dataTables.editable.js"></script>

<div id="content">
    <div id="admin">
        <div id="pagetitle">
            Administration
        </div>
        <div id="leftmenu">
          <ul>
            <?php
              if ($type == SUPER) {
                echo ($page == 'modules') ? '<li><a href="?p=modules" class="active">Modules</a></li>' : '<li><a href="?p=modules" class="">Modules</a></li>';
              }
            ?>
            <!--li><a href="?p=articles" class="<?php //echo $_GET['p'] == 'articles' ? 'active' : ''; ?>">Articles</a></li-->
            <!--li><a href="?p=homepage" class="<?php //echo $_GET['p'] == 'homepage' ? 'active' : ''; ?>">Home Page</a></li-->
            <li><a href="?p=users" class="<?php echo $page == 'users' ? 'active' : ''; ?>">Users</a></li>
            <li><a href="?p=reports" class="<?php echo $page == 'reports' ? 'active' : ''; ?>">Reports</a></li>
            <!--li><a href="?p=settings" class="<?php  //echo $_GET['p'] == 'settings'   ? 'active' : ''; ?>">Settings</a></li-->
          </ul>
        </div>
        <div id="contentpane">
            <?php
                include($page);
            ?>
            <div id="confirm">
                <div id="message"></div>
            </div>
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

    function reorder(type, items) {
        //construct xml to send
        var xml = '<items>';

        $.each(items, function(index, value) {
            xml += '<item id="'+value+'" order="'+index+'" ></item>';
        });

        xml += '</items>';

        //send request
        $.ajax({
            url: "reorder.php",
            dataType: "html",
            type: 'post',
            data: {
                ajax    : true,
                type    : type,
                items   : xml
            },
            success: function(msg){

            }
        });
    }

    function remove(type, id){

        //send request
        $.ajax({
            url: "remove.php",
            dataType: "html",
            type: 'post',
            data: {
                ajax    : true,
                type    : type,
                id      : id
            },
            success: function(msg){
                $('.'+type+'[id='+id+']').remove();
            }
        });
    }

    $('.remove').click(function() {
       //get values
       var type = $(this).parent().parent().attr('class');
       var id   = $(this).parent().parent().attr('id');

       //prepare dialog
       $('#confirm').dialog( 'option', 'title', 'Remove '+type+'?');
       $('#confirm #message').html('<div>Remove '+type+'?</div><div>This cannot be undone.</div>');
       $('#confirm').dialog( "option" , 'buttons' , {
         'Ok': function(){ remove(type, id); $(this).dialog('close'); },
         'Cancel': function(){ $(this).dialog('close');}
       });

       //show dialog
       $('#confirm').dialog('open');
     });

    $('#confirm').dialog({
        autoOpen: false,
        resizable: false,
        modal: true
    });

    //jquery class interaction states

    $('.remove, .addpage, .edit, button').addClass('ui-state-default');

    $('.ui-state-default').hover(
        function(){
            $(this).addClass("ui-state-hover");
        },
        function(){
            $(this).removeClass("ui-state-hover");
        }
    );
</script>

<?php
//footer
include('../footer.php');
?>