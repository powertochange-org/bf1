<?php
/*
 * Cru Doctrine
 * Admin - Edit Module
 * Keith Roehrenbeck | Campus Crusade for Christ
 */

try {

    //get values
    $submit     = isset($_POST['submit'])   ? true                  : false;
    $new        = isset($_GET['id'])        ? false                 : true;
    $ajax       = isset($_POST['ajax'])     ? true                  : false;

    $moduleId   = isset($_GET['id'])        ? $_GET['id']           : '';
    $number     = isset($_POST['number'])   ? $_POST['number']      : '';
    $title      = isset($_POST['title'])    ? $_POST['title']       : '';
    $order      = isset($_POST['order'])    ? $_POST['order']       : '';
    $caption    = isset($_POST['caption'])  ? $_POST['caption']     : '';
    $descr      = isset($_POST['descr'])    ? $_POST['descr']       : '';
    $photo      = isset($_POST['photo'])    ? $_POST['photo']       : '';
    $banner     = isset($_POST['banner'])   ? $_POST['banner']      : '';
    $homepage   = isset($_POST['homepage']) ? $_POST['homepage']    : '';

    $errors     = isset($_POST['errors'])   ? $_POST['errors'] : '';

	// grab the existing $db object
	$db=Database::obtain();

    //check for form submission
    if($submit){    //form was submitted, process data

        if($new){   //new module

	        //prepare query
	        $data['Number'] = (double)$number;
	        $data['Name'] = $title;
	        $data['Ord'] = (double)$order;
	        $data['Caption'] = $caption;
	        $data['Descr'] = $descr;
	        $data['Photo'] = $photo;
	        $data['Banner'] = $banner;
	        $data['FrontImg'] = $homepage;

	        //execute query
	        $moduleId = $db->insert("module", $data);

        } else {    //edit module

	        //prepare query
	        $data['Number'] = (double)$number;
	        $data['Name'] = $title;
	        $data['Ord'] = (double)$order;
	        $data['Caption'] = $caption;
	        $data['Descr'] = $descr;
	        $data['Photo'] = $photo;
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
            echo '<new>'        .$new.                      '</new>';
            echo '<id>'         .$moduleId.		    '</id>';
            echo '<number>'     .$number.		    '</number>';
            echo '<title>'      .$title.		    '</title>';
            echo '<order>'      .$order.		    '</order>';
            echo '<caption>'    .$caption.		    '</caption>';
            echo '<descr>'      .$descr.		    '</descr>';
            echo '<photo>'      .$photo.		    '</photo>';
            echo '<banner>'     .$banner.		    '</banner>';
            echo '<homepage>'   .$homepage.		    '</homepage>';
            echo '</module>';

            exit();

        } else {

            header ("Location: ?p=modules&id=".$moduleId);

        }

    } else if (!$new){ //get data for existing module

        //get module information
        $sql = "SELECT * FROM module WHERE ID = ".$moduleId;

        //assign module information to array
        $result = $db->query_first($sql);

        //assign values
        $number     = $result['Number'];
        $title      = $result['Name'];
        $order      = $result['Ord'];
        $caption    = $result['Caption'];
        $descr      = $result['Descr'];
        $photo      = $result['Photo'];
        $banner     = $result['Banner'];
        $homepage   = $result['FrontImg'];

    }

	$db->close();

} catch (PDOException $e) {
    echo $e->getMessage();
    exit();
}

?>

<div id="editmodule">

    <form action="?p=modules<?php echo !$new ? '&id='.$moduleId : ''; ?>&request=edit_module" method="post">

        <fieldset id="information">
            <legend>Module Information</legend>
            <div>
                <label>Number</label><input type="text" name="number" value="<?php echo $number; ?>" /><a class="required"></a>
            </div>
            <div>
                <label>Title</label><input type="text" name="title" value="<?php echo $title; ?>" /><a class="required"></a>
            </div>
            <div>
                <label>Caption</label><textarea name="caption"><?php echo $caption; ?></textarea>
            </div>
            <div>
                <label>Description</label><textarea name="descr"><?php echo $descr; ?></textarea>
            </div>
        </fieldset>

        <fieldset id="images" >
            <legend>Images</legend>
            <div>
                <label>Photo</label><input type="text" name="photo" class="filename" value="<?php echo $photo; ?>" /><button type="button" name ="photoBrowse" id="photoBrowse">Browse</button>
            </div>
            <div>
                <label>Banner</label><input type="text" name="banner" class="filename" value="<?php echo $banner; ?>" /><button type="button" name ="bannerBrowse" id="bannerBrowse">Browse</button>
            </div>
            <div>
                <label>Home Page</label><input type="text" name="homepage" class="filename" value="<?php echo $homepage; ?>" /><button type="button" name ="homepageBrowse" id="homepageBrowse">Browse</button>
            </div>
        </fieldset>

        <fieldset id="feedback">
            <div id="upload"></div>
            <div id="errors"><?php echo $errors; ?></div>
        </fieldset>

        <input type="hidden" name="order" value="<?php echo $order; ?>" />

        <button type="submit" name="submit">Create Module</button>

    </form>

</div>

<script type="text/javascript">

    //hide submit button
    $(function() {
        $('form button:[name=submit]').hide();
    });

    //file uploads
    $('#photoBrowse').uploadify({
        'uploader'  : '/crudoctrine/jquery/uploadify/uploadify.swf',
        'script'    : '/crudoctrine/jquery/uploadify/uploadify.php',
        'cancelImg' : '/crudoctrine/jquery/uploadify/cancel.png',
        'auto'      : true,
        'folder'    : '/crudoctrine/upload/images',
        'queueID'   : 'upload',
        'wmode'     : 'transparent',
        'onComplete': function(event, queueID, fileObj, response, data){
            if (response == '1') {
                $('#editmodule #images').find('input:text[name=photo]').val(fileObj.filePath);
            } else {
                alert(response);
            }
        }
    });

    $('#bannerBrowse').uploadify({
        'uploader'  : '/crudoctrine/jquery/uploadify/uploadify.swf',
        'script'    : '/crudoctrine/jquery/uploadify/uploadify.php',
        'cancelImg' : '/crudoctrine/jquery/uploadify/cancel.png',
        'auto'      : true,
        'folder'    : '/crudoctrine/upload/images',
        'queueID'   : 'upload',
        'wmode'     : 'transparent',
        'onComplete': function(event, queueID, fileObj, response, data){
            if (response == '1') {
                $('#editmodule #images').find('input:text[name=banner]').val(fileObj.filePath);
            } else {
                alert(response);
            }
        }
    });

    $('#homepageBrowse').uploadify({
        'uploader'  : '/crudoctrine/jquery/uploadify/uploadify.swf',
        'script'    : '/crudoctrine/jquery/uploadify/uploadify.php',
        'cancelImg' : '/crudoctrine/jquery/uploadify/cancel.png',
        'auto'      : true,
        'folder'    : '/crudoctrine/upload/images',
        'queueID'   : 'upload',
        'wmode'     : 'transparent',
        'onComplete': function(event, queueID, fileObj, response, data){
            if (response == '1') {
                $('#editmodule #images').find('input:text[name=homepage]').val(fileObj.filePath);
            } else {
                alert(response);
            }
        }
    });

    //validate form submission
    $('#editmodule form').submit(function(){
        var submit = false;
        var errors = '';

        if ($('#editmodule input:[name=number]').val().length == 0){
            $('#editmodule input:[name=number]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please enter a module number.</div>';
        }

        if ($('#editmodule input:[name=title]').val().length == 0) {
            $('#editmodule input:[name=title]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please enter a title for this module.</div>';
        }

        if (errors !== ''){
           $('#editmodule #errors').html(errors);
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
                    number      : $('form input:[name=number]').val(),
                    title       : $('form input:[name=title]').val(), 
                    order       : $('form input:[name=order]').val(),
                    caption     : $('form textarea:[name=caption]').val(),
                    descr       : $('form textarea:[name=descr]').val(),
                    photo       : $('form input:[name=photo]').val(),
                    banner      : $('form input:[name=banner]').val(),
                    homepage    : $('form input:[name=homepage]').val()
                },
                dataType: "xml",
                success: function(xml){
                    
                    $(xml).find('module').each(function(){

                        //get values
                        var id      = $(this).find('id').text();
                        var number  = $(this).find('number').text();
                        var title   = $(this).find('title').text();
                        var descr   = $(this).find('descr').text();

                        //determine type
                        if($(this).find('new').text() == '1') {

                            $('#list').append('<div class="module" id="'+id+'"><div class="title"><div class="number">'+number+'</div><div class="name">'+title+'</div></div><div class="descr">'+descr+'</div><div class="drag ui-icon ui-icon-grip-solid-horizontal"></div><a class="edit ui-state-default" href="?p=modules&id='+id+'"><span class="ui-icon ui-icon-pencil"></span>Edit</a></div>');
                            
                        } else {

                           $('#information #number').html(number);
                           $('#information #name').html(title);
                           $('#information #description').html(descr);

                        }

                    });

                    $('#editmodule').dialog("close");

                }
            });
        }

        return false;

    });

</script>
