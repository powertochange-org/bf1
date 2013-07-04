<?php
/*
 * Cru Doctrine
 * Admin - Edit Module
 * Campus Crusade for Christ
 */

try {
  //get values
  $submit      = isset($_POST['submit'])   ? true                  : false;
  $new         = isset($_GET['id'])        ? false                 : true;
  $ajax        = isset($_POST['ajax'])     ? true                  : false;

  $moduleId    = isset($_GET['id'])        ? $_GET['id']           : '';
  $number      = isset($_POST['number'])   ? $_POST['number']      : '';
  $title       = isset($_POST['title'])    ? $_POST['title']       : '';
  $order       = isset($_POST['order'])    ? $_POST['order']       : '';
  //$caption   = isset($_POST['caption'])  ? $_POST['caption']     : '';
  $description = isset($_POST['descr'])    ? $_POST['descr']       : '';
  //$photo     = isset($_POST['photo'])    ? $_POST['photo']       : '';
  $banner      = isset($_POST['banner'])   ? $_POST['banner']      : '';
  $homepage    = isset($_POST['homepage']) ? $_POST['homepage']    : '';

  $errors      = isset($_POST['errors'])   ? $_POST['errors'] : '';

  $description = stripslashes($description);

  require_once("../config.inc.php"); 
  require_once("../Database.singleton.php");

  //initialize the database object
  $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
  $db->connect();

  //check for form submission
  if($submit) {    //form was submitted, process data
    if($new) {   //new module
      //prepare query
      $data['Number'] = (double)$number;
      $data['Name'] = $title;
      $data['Ord'] = (double)$order;
      //$data['Caption'] = $caption;
      $data['Descr'] = $description;
      //$data['Photo'] = $photo;
      $data['Banner'] = $banner;
      $data['FrontImg'] = $homepage;

      //execute query
      $moduleId = $db->insert("module", $data);
    } 
    else {    //edit module
      //prepare query
      $data['Number'] = (double)$number;
      $data['Name'] = $title;
      $data['Ord'] = (double)$order;
      //$data['Caption'] = $caption;
      $data['Descr'] = $description;
      //$data['Photo'] = $photo;
      $data['Banner'] = $banner;
      $data['FrontImg'] = $homepage;

      //execute query
      $db->update("module", $data, "ID = ".$db->escape($moduleId));
    }

    //if ajax, return module attributes as xml
    if ($ajax) {
      header('Content-Type: application/xml; charset=ISO-8859-1');
      echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
      echo '<module>';
      echo '<new>'        .$new.              '</new>';
      echo '<id>'         .$moduleId.         '</id>';
      echo '<number>'     .$number.           '</number>';
      echo '<title>'      .$title.            '</title>';
      echo '<order>'      .$order.            '</order>';
      //echo '<caption>'    .$caption.        '</caption>';
      echo '<descr>'      .$description.      '</descr>';
      //echo '<photo>'      .$photo.          '</photo>';
      echo '<banner>'     .$banner.           '</banner>';
      echo '<homepage>'   .$homepage.         '</homepage>';
      echo '</module>';

      exit();
    } 
    else {
      header ("Location: ?p=modules&id=".$moduleId);
    }
  } 
  else if (!$new) { //get data for existing module
    //get module information
    $sql = "SELECT * FROM module WHERE ID = ".$moduleId;

    //assign module information to array
    $result = $db->query_first($sql);

    //assign values
    $number     = $result['Number'];
    $title      = $result['Name'];
    $order      = $result['Ord'];
    //$caption    = $result['Caption'];
    $description      = $result['Descr'];
    //$photo      = $result['Photo'];
    $banner     = $result['Banner'];
    $homepage   = $result['FrontImg'];
  }

  $db->close();

} 
catch (PDOException $e) {
  echo $e->getMessage();
  exit();
}
?>
<div id="editmodule">
  <form id="formEditModule" action="?p=modules<?php echo !$new ? '&id='.$moduleId : ''; ?>&request=edit_module" method="post">
    <fieldset id="information">
      <legend>Module Information</legend>
      <div>
        <label>Number</label>
        <input type="text" id="number" value="<?php echo $number; ?>" /><a class="required"></a>
      </div>
      <div>
        <label>Title</label>
        <input type="text" id="title" value="<?php echo $title; ?>" /><a class="required"></a>
      </div>
      <!--div>
        <label>Caption</label>
        <textarea id="caption"><?php //echo $caption; ?></textarea>
      </div-->
      <div>
        <label>Description</label>
        <textarea id="description"><?php echo $description; ?></textarea>
      </div>
    </fieldset>
    <fieldset id="images" >
      <legend>Images</legend>
      <!--div>
        <label>Photo</label>
        <input type="text" id="photo" class="filename" value="<?php //echo $photo; ?>" />
        <button type="button" id ="photoBrowse" id="photoBrowse">Browse</button>
      </div-->
      <div>
        <label>Banner</label>
        <input type="text" id="banner" class="filename" value="<?php echo $banner; ?>" />
        <button type="button" name ="bannerBrowse" id="bannerBrowse">Browse</button>
      </div>
      <div>
        <label>Home Page</label>
        <input type="text" id="homepage" class="filename" value="<?php echo $homepage; ?>" />
        <button type="button" name ="homepageBrowse" id="homepageBrowse">Browse</button>
      </div>
    </fieldset>
    <fieldset id="feedback">
      <div id="upload"></div>
      <div id="errors"><?php echo $errors; ?></div>
    </fieldset>
    <input type="hidden" id="order" value="<?php echo $order; ?>" />
    <button type="submit" name="submit" id="submit">Create Module</button>
  </form>
</div>

<script type="text/javascript">
    //hide submit button
    $(function() {
        $('#formEditModule #submit').hide();
    });

    //file uploads
    /*$('#photoBrowse').uploadify({
        'uploader'  : '/jquery/uploadify/uploadify.swf',
        'script'    : '/jquery/uploadify/uploadify.php',
        'cancelImg' : '/jquery/uploadify/cancel.png',
        'auto'      : true,
        'folder'    : '/upload/images',
        'queueID'   : 'upload',
        'wmode'     : 'transparent',
        'onComplete': function(event, queueID, fileObj, response, data){
            if (response == '1') {
                $('#formEditModule #images #photoBrowse').val(fileObj.filePath);
            } else {
                alert(response);
            }
        }
    });*/

    $('#bannerBrowse').uploadify({
        'uploader'  : '/jquery/uploadify/uploadify.swf',
        'script'    : '/jquery/uploadify/uploadify.php',
        'cancelImg' : '/jquery/uploadify/cancel.png',
        'auto'      : true,
        'folder'    : '/upload/images',
        'queueID'   : 'upload',
        'wmode'     : 'transparent',
        'onComplete': function(event, queueID, fileObj, response, data){
            if (response == '1') {
                $('#formEditModule #images #banner').val(fileObj.filePath);
            } else {
                alert(response);
            }
        }
    });

    $('#homepageBrowse').uploadify({
        'uploader'  : '/jquery/uploadify/uploadify.swf',
        'script'    : '/jquery/uploadify/uploadify.php',
        'cancelImg' : '/jquery/uploadify/cancel.png',
        'auto'      : true,
        'folder'    : '/upload/images',
        'queueID'   : 'upload',
        'wmode'     : 'transparent',
        'onComplete': function(event, queueID, fileObj, response, data){
            if (response == '1') {
                $('#formEditModule #images #homepage').val(fileObj.filePath);
            } else {
                alert(response);
            }
        }
    });

    //validate form submission
    $('#formEditModule').submit(function(){
        var submit = false;
        var errors = '';

        if ($('#formEditModule #number').val().length == 0){
            $('#formEditModule #number').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please enter a module number.</div>';
        }

        if ($('#formEditModule #title').val().length == 0) {
            $('#formEditModule #title').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please enter a title for this module.</div>';
        }

        if (errors !== ''){
           $('#formEditModule #errors').html(errors);
           submit = false;
        } else {
           submit = true;
        }

        if(submit){
          $.ajax({
            url: 'edit_module.php<?php echo !$new ? '?id='.$moduleId : ''; ?>',
            type: 'POST',
            data: { 
                ajax        : true,
                submit      : true,
                number      : $('#formEditModule #number').val(),
                title       : $('#formEditModule #title').val(), 
                order       : $('#formEditModule #order').val(),
                //caption     : $('#formEditModule #caption').val(),
                descr       : $('#formEditModule #description').val(),
                //photo       : $('#formEditModule #photo').val(),
                banner      : $('#formEditModule #banner').val(),
                homepage    : $('#formEditModule #homepage').val()
            },
            dataType: "xml",
            success: function(xml) {
              $(xml).find('module').each(function() {
                //get values
                var id            = $(this).find('id').text();
                var number        = $(this).find('number').text();
                var title         = $(this).find('title').text();
                var description   = $(this).find('descr').text();

                //determine type
                if($(this).find('new').text() == '1') {
                  $('#list').append('<div class="module" id="'+id+'"><div class="title"><div class="number">'+number+'</div><div class="name">'+title+'</div></div><div class="descr">'+description+'</div><div class="drag ui-icon ui-icon-grip-solid-horizontal"></div><a class="edit ui-state-default" href="?p=modules&id='+id+'"><span class="ui-icon ui-icon-pencil"></span>Edit</a></div>');
                } 
                else {
                  $('#information #number').html(number);
                  $('#information #name').html(title);
                  $('#information #description').html(description);
                }
              });
              $('#editmodule').dialog("close");
            }
          });
        }
        return false;
    });
</script>