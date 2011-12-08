<?php
/*
 * Cru Doctrine
 * Admin - Module - Edit Section
 * Campus Crusade for Christ
 */

try {

    //get values
    $submit     = isset($_POST['submit'])   ? true                  : false;
    $new        = isset($_GET['sid'])       ? false                 : true;
    $ajax       = isset($_POST['ajax'])     ? true                  : false;

    $moduleId   = isset($_GET['id'])        ? $_GET['id']           : '';
    $sectionId  = isset($_GET['sid'])       ? $_GET['sid']          : '';
    $title      = isset($_POST['title'])    ? $_POST['title']       : '';
    $order      = isset($_POST['order'])    ? $_POST['order']       : '';

    $errors     = isset($_POST['errors'])   ? $_POST['errors'] : '';

	// grab the existing $db object
	$db=Database::obtain();

    //check for form submission
    if($submit){

        if($new){   //new section
	        //prepare query
	        $data['ModuleId'] = (int)$moduleId;
	        $data['Title'] = $title;
	        $data['Ord'] = (double)$order;

	        //execute query
	        $sectionId = $db->insert("section", $data);

        } else {    //edit section

	        //prepare query
	        $data['ModuleId'] = (int)$moduleId;
	        $data['Title'] = $title;
	        $data['Ord'] = (double)$order;

            //execute query
            $db->update("section", $data, "ID = ".$db->escape($sectionId));
        }

        //if ajax, return module attributes as xml
        if ($ajax) {

            header('Content-Type: application/xml; charset=ISO-8859-1');
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
            echo '<section>';
            echo '<new>'        .$new.                      '</new>';
            echo '<id>'         .$sectionId.		    '</id>';
            echo '<moduleId>'   .$moduleId.		    '</moduleId>';
            echo '<title>'      .$title.		    '</title>';
            echo '<order>'      .$order.		    '</order>';
            echo '</section>';

            exit();

        } else {

            header ("Location: ?p=modules&id=".$moduleId);

        }

    } else if (!$new){ //get data for existing section

        //get section information
        $sql = "SELECT * FROM section WHERE ID = ".$sectionId;

        //assign section information to array
		$result = $db->fetch_array($sql);

        //assign values
        $title      = $result['Title'];
        $order      = $result['Ord'];
    }

	$db->close();

} catch (PDOException $e) {
    echo $e->getMessage();
    exit();
}

?>

<div id="editsection">

    <form action="?p=modules&id=<?php echo $_GET['id']; ?>&request=edit_section" method="post">

        <fieldset id="information">
            <legend>Section Information</legend>
            <div>
                <label>Title</label><input type="text" name="title" value="<?php echo $title; ?>" /><a class="required"></a>
            </div>
        </fieldset>

        <fieldset id="feedback">
            <div id="errors"><?php echo (isset($_POST['errors'])) ? $_POST['errors'] : ''; ?></div>
        </fieldset>

        <input type="hidden" name="moduleId" value="<?php echo $moduleId; ?>" />
        <input type="hidden" name="order" value="<?php echo $order; ?>" />

        <button type="submit" name="submit">Create Section</button>

    </form>

</div>

<script type="text/javascript">

    //hide submit button
    $(function() {
        $('form button:[name=submit]').hide();
    });

    //validate form submission
    $('#editsection form').submit(function(){
        var submit = false;
        var errors = '';

        if ($('#editsection input:[name=title]').val().length == 0){
            $('#editsection input:[name=title]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please enter a section title.</div>';
        }

        if (errors !== ''){
           $('#editsection #errors').html(errors);
           submit = false;
        } else {
           submit = true;
        }

        if(submit){
            $.ajax({
                url: 'edit_section.php?id=<?php echo $moduleId; echo !$new ? '&sid='.$sectionId : ''; ?>',
                type: 'POST',
                data: {
                    ajax        : true,
                    submit      : true,
                    title       : $('form input:[name=title]').val(),
                    order       : $('form input:[name=order]').val()
                },
                dataType: "xml",
                success: function(xml){
                    //update module list
                    $(xml).find('section').each(function(){

                        //get values
                        var id      = $(this).find('id').text();
                        var title   = $(this).find('title').text();
                        var module  = $(this).find('moduleId').text();

                        //determine if new
                        if($(this).find('new').text() == '1') {

                            var section = '';

                            section +=      '<div class = "section" id = "'+id+'">';
                            section +=      '   <div class="sectioninfo">';
                            section +=      '       <div class="colapse ui-icon ui-icon-triangle-1-s"></div>';
                            section +=      '       <div class="title">'+title+'</div>';
                            section +=      '       <div class="drag ui-icon ui-icon-grip-solid-horizontal"></div>';
                            section +=      '       <a class="remove" href="#"><span class="ui-icon ui-icon-minus"></span> Remove</a>';
                            section +=      '       <a class="addpage" href="pagebuilder?section='+id+'&module='+module+'&ord=0"><span class="ui-icon ui-icon-plus"></span> Add Page</a>';
                            section +=      '       <a class="edit" href="#"><span class="ui-icon ui-icon-pencil"></span> Edit</a>';
                            section +=      '   </div>';
                            section +=      '   <div class="pages"></div>';
                            section +=      '</div>';

                            $('#sections').append(section);

                        } else {

                            $('#sections .section[sId='+id+']').find('.sectioninfo').find('.title').html(title);

                        }

                    });

                    $('#editsection').dialog("close");

                }

            });
        }
        
        return false;

    });

</script>
