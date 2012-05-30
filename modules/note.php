<?php
/*
 * Cru Doctrine
 * Modules - Note
 * Campus Crusade for Christ
 */

//get values
session_start();
$email      = $_SESSION['email'];
$submit     = isset($_POST['submit'])   ? true                      : false;
$new        = isset($_POST['_new'])     ? $_POST['_new'] == 'true'  : true;

$id         = isset($_POST['id'])       ? $_POST['id']              : 0;
$note       = isset($_POST['note'])     ? $_POST['note']            : '';
$x          = isset($_POST['x'])        ? $_POST['x']               : '';
$y          = isset($_POST['y'])        ? $_POST['y']               : '';
$w          = isset($_POST['w'])        ? $_POST['w']               : '';
$h          = isset($_POST['h'])        ? $_POST['h']               : '';

$errors     = isset($_POST['errors'])   ? $_POST['errors']          : '';

require_once("../config.inc.php"); 
require_once("../Database.singleton.php");

$note = stripslashes($note);

//initialize the database object
$db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
$db->connect();

//save note
if($submit) {
    if($new) {//new note
        //prepare query
        $data['Email'] = $email;
        $data['ElementId'] = (int)$id;
        $data['Note'] = $note;
        $data['_x'] = (int)$x;
        $data['_y'] = (int)$y;
        $data['_w'] = (int)$w;
        $data['_h'] = (int)$h;

        //execute query
        $primary_id = $db->insert("note", $data);
        
    } 
    else {//edit note
        if($note == '') { //note is empty, delete
            //prepare query
            $sql = "DELETE FROM note WHERE Email = '".$db->escape($email)."' AND ElementId = " .(int)$id;

            //execute query
            $db->query($sql);

            echo 'Note Deleted';
        } 
        else {
            //prepare query
            $data['Note'] = $note;
            $data['_x'] = (int)$x;
            $data['_y'] = (int)$y;
            $data['_w'] = (int)$w;
            $data['_h'] = (int)$h;

            //execute query
            $db->update("note", $data, "Email = '".$db->escape($email)."' AND ElementId = " .(int)$id);

            echo 'Note Saved';
        }
    }
    $db->close();
    exit();
}

//get note
//prepare query
$sql = "SELECT * FROM note WHERE Email = '".$db->escape($email)."' AND ElementId = " .(int)$id;

//execute query
$_note = $db->query_first($sql);

if(count($_note) > 1) {
  $new    = false;
  $note   = $_note['Note'];
  $x      = $_note['_x'];
  $y      = $_note['_y'];
  $w      = $_note['_w'];
  $h      = $_note['_h'];

} 
else {
  $new    = true;
  $body   = '';
  $x      = 0;
  $y      = 0;
  $w      = 130;
  $h      = 130;
}
$db->close();
?>

<div class="note <?php echo $new ? 'new' : ''; ?>" id="<?php echo $id; ?>" style="<?php echo 'top:'.$y.'px; left:'.$x.'px;"';?>">
    <div class="bg">
        <div class="top shadow-light"></div>
        <div class="right shadow-light"></div>
        <div class="corner"></div>
    </div>
    <div class="dragbar">
        <a href="#" class="deletenote">
            <span class="ui-icon ui-icon-close"></span>
        </a>
        <a href="#" class="closenote">
            <span class="ui-icon ui-icon-minus"></span>
        </a>
    </div>
    <form>
        <textarea name="body" style="<?php echo 'height:'.$h.'px; width:'.$w.'px;"';?>"><?php echo $note; ?></textarea>
        <button type="submit">Save</button>
    </form>
</div>