<?php
/*
 * Cru Doctrine
 * Modules - Note
 * Keith Roehrenbeck | Campus Crusade for Christ
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

//get pdo
$db = new PDO('mysql:host=crudoctrine.db.6550033.hostedresource.com;port=3306;dbname=crudoctrine', 'crudoctrine', 'D6LLd2mxU6Z34i');

//save note
if($submit){

    if($new){   //new note

        //prepare query
        $query = $db->prepare("INSERT INTO note (Email, ElementId, Type, Note, _x, _y, _w, _h) VALUES (?,?,?,?,?,?,?,?)");
        $query->bindValue(1, $email,            PDO::PARAM_STR);
        $query->bindValue(2, (int)$id,          PDO::PARAM_INT);
        $query->bindValue(3, 'note',            PDO::PARAM_STR);
        $query->bindValue(4, $note,             PDO::PARAM_STR);
        $query->bindValue(5, (int)$x,           PDO::PARAM_INT);
        $query->bindValue(6, (int)$y,           PDO::PARAM_INT);
        $query->bindValue(7, (int)$w,           PDO::PARAM_INT);
        $query->bindValue(8, (int)$h,           PDO::PARAM_INT);

        //execute query
        $query->execute();

    } else {    //edit note

        if($note == ''){ //note is empty, delete
            //prepare query
            $query = $db->prepare("DELETE FROM note WHERE Email = ? AND ElementId = ?");
            $query->bindValue(1, $email,            PDO::PARAM_STR);
            $query->bindValue(2, (int)$id,          PDO::PARAM_INT);

            //execute query
            $query->execute();

            echo 'Note Deleted';
        } else {
            //prepare query
            $query = $db->prepare("UPDATE note SET Type = ?, Note = ?, _x = ?, _y = ?, _w = ?, _h = ? WHERE Email = ? AND ElementId = ?");
            $query->bindValue(1, 'note',            PDO::PARAM_STR);
            $query->bindValue(2, $note,             PDO::PARAM_STR);
            $query->bindValue(3, (int)$x,           PDO::PARAM_INT);
            $query->bindValue(4, (int)$y,           PDO::PARAM_INT);
            $query->bindValue(5, (int)$w,           PDO::PARAM_INT);
            $query->bindValue(6, (int)$h,           PDO::PARAM_INT);
            $query->bindValue(7, $email,            PDO::PARAM_STR);
            $query->bindValue(8, (int)$id,          PDO::PARAM_INT);

            //execute query
            $query->execute();

            echo 'Note Saved';
        }

    }
    exit();

}

//get note
$db_note = $db->prepare("SELECT * FROM note WHERE Email = ? AND ElementId = ?");
$db_note->bindValue(1, $email,      PDO::PARAM_STR);
$db_note->bindValue(2, (int)$id,    PDO::PARAM_INT);
$db_note->execute();

$_note  = $db_note->fetch(PDO::FETCH_ASSOC);

if(count($_note) > 1){
    $new    = false;

    $note   = $_note['Note'];
    $x      = $_note['_x'];
    $y      = $_note['_y'];
    $w      = $_note['_w'];
    $h      = $_note['_h'];

} else {
    $new    = true;

    $body   = '';
    $x      = 0;
    $y      = 0;
    $w      = 130;
    $h      = 130;

}

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
