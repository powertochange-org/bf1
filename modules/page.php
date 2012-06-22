<?php
/*
 * Cru Doctrine
 * Modules - Module Page
 * Campus Crusade for Christ
 */

try {
    //initialize the database object
    $db = Database::obtain();

    //get module sections & pages
    $sql = "SELECT * FROM section WHERE ModuleId = " .(int)$module['ID']. " ORDER BY Ord ASC";

    //execute query 
    $sections = $db->fetch_array($sql); 

    foreach($sections as $row) {
        //get pages in section
        $visibility = getVisibilityClause($type);
        $sql = "SELECT * 
                FROM page p
                WHERE p.SectionId = ".(int)$row['ID']." AND
                ".$visibility."
                ORDER BY Ord ASC";

        //execute query 
        $pages = $db->fetch_array($sql);
        $count = count($pages);

        //add to module if section has at least one page
        if($count > 0){
            $row['pages'] = $pages;
            $module['sections'][] = $row;
        }
    }

    //get page data
    $main_elements  = array();
    $right_elements = array();
    $notes          = array();

    //get elements
    $sql = "SELECT * FROM element WHERE PageId = ".$page['ID']." ORDER BY Ord";
    //execute query 
    $db_elements = $db->fetch_array($sql);  

    //get element content and construct element arrays
    foreach($db_elements as $db_element) {
        //get element id and type
        $elemId     = $db_element['ElementId'];
        $elemType   = $db_element['Type'];

        //execute query
        $sql = "SELECT * FROM ".$elemType." WHERE ID = ".$elemId;
        $db_content = $db->query_first($sql);

        //content string
        $content = '';

        switch($elemType) {
            case 'textbox': //textbox
                $content    = $db_content['Text'];
                //$content    = nl2br($content);
                break;

            case 'media':   //media
                $filename   = $db_content['Filename'];
                $height     = $db_content['Height'];
                $width      = $db_content['Width'];
                $caption    = $db_content['Caption']    != ''? '<div class="caption">'.$db_content['Caption'].'</div>' : '';
                $content    = '<div align="center"><div class="media {width:'.$width.', height:'.($height+16).' }" href="'.$filename.'"></div>'.$caption.'</div>';
                break;

            case 'image':   //image
                $filename   = $db_content['Filename'];
                $height     = $db_content['Height']     != 0 ? 'height="'.$db_content['Height'].'"' : '';
                $width      = $db_content['Width']      != 0 ? 'width="'.$db_content['Width'].'"'   : '';
                $caption    = $db_content['Caption']    != ''? '<div class="caption">'.$db_content['Caption'].'</div>' : '';
                $content    = '<div align="center"><img src="'.$filename.'" '.$height.' '.$width.' />'.$caption.'</div>';
                break;

            case 'input':   //input
                $question   = $db_content['Question'];
                $personal   = $db_content['Personal']   == 1 ? 'checked disabled' : '';
                $coach      = $db_content['Coach']      == 1 ? 'checked disabled' : '';
                $min        = $db_content['Min'];
                $content    = '<div class="input"><div class="question">'.$question.'</div>';
                $content   .= '<div class="response"><textarea name="response" min="'.$min.'"></textarea></div>';
                $content   .= '<div class="alert"></div>';
                $content   .= '<div class="flags"><div><input type="checkbox" name="personal" '.$personal.' /><label>Flag For Personal Followup</label></div><div><input type="checkbox" name="coach" '.$coach.' /><label>Flag For Coach Followup</label></div></div>';
                $content   .= '</div>';
                break;

            case 'whitespace':   //whitespace
                $height     = $db_content['Height'];
                $content    = '<div style="height: '.$height.'px;"></div>';
                break;
        }

        //construct element
        $element = array();
        $element['id']      = $elemId;
        $element['type']    = $elemType;
        $element['content'] = $content;

        //add element to element array
        switch($db_element['Loc']) {
            case 'main':
                $main_elements[] = $element;
                break;

            case 'right':
                $right_elements[] = $element;
        }

        //determine column count
        $columns = count($right_elements) > 0 ? 'two-column' : 'one-column';
    }

    //get notes
    //execute query
    $sql = "SELECT n.ElementId FROM note n INNER JOIN element e ON n.ElementId = e.ElementId  WHERE e.PageId = ".$page['ID']." AND n.Email = '".$email."'";
    $db_notes = $db->fetch_array($sql);

    foreach($db_notes as $db_note) {
        $notes[] = $db_note['ElementId'];
    }

} catch (PDOException $e) {
    echo $e->getMessage();
}

//function to insert element
function insertElement($_id, $_type, $_content) {
    //fill template
    $element  = '<div id="'.$_type.$_id.'" class="'.$_type.' element" eId="'.$_id.'">'.PHP_EOL;
    $element .= ($_type != 'input') ? '<a class="note add" href="javascript:;" ></a>' : '';
    $element .= '   '.$_content.PHP_EOL;
    $element .= '</div>'.PHP_EOL;

    //add to DOM
    echo $element;
}
?>

<script src="/jquery/elastic/jquery.elastic-1.6.js" type="text/javascript" charset="utf-8"></script>
<script src="/jquery/jQuery-URL-Parser/jquery.url.js" type="text/javascript" charset="utf-8"></script>
<div id="module<?php echo str_replace('.', '', $module['Number']); ?>" class="page">
    <div id="title">
        <div id="number">Module <?php echo $module['Number']; ?></div>
        <div id="name"><?php echo $module['Name']; ?></div>
    </div>
    <div id="banner">
        <img src="<?php echo '../'.$module['Banner']; ?>"</img>
    </div>
    <div id="sectiontitle">
        <?php echo $section['Title']; ?>
    </div>
    <div id="leftmenu">
        <ul>
        <?php
          if(is_array($module['sections'])) {
            foreach($module['sections'] as $sec){
                //add line for section
                echo '<li class="section"><a href="?s='.$sec['ID'].'" ';
                echo $sec['ID'] == $section['ID'] ? 'class="active" ' : '';
                echo '>'.$sec['Title'].'</a>';
                if(count($sec['pages']) > 1) {
                    $active = ($sec['ID'] == $section['ID']) ? true : false;
                    echo '<div class="colapse ui-icon ';
                    echo $active ? ' ui-icon-triangle-1-s " ' : 'ui-icon-triangle-1-e "';
                    echo '"></div>';
                    echo '<div class="pages ';
                    echo $active ? 'active ' : '';
                    echo '">';
                    foreach($sec['pages'] as $pag){
                        $active = ($pag['ID'] == $page['ID']) ? true : false;
                        echo '<a href="?p='.$pag['ID'].'" ';
                        echo $active ? 'class="active" ' : '';
                        echo '>'.$pag['Title'].'</a>';
                    }
                    echo '</div>';
                }
                echo '</li>';
            }
          }
        ?>
        </ul>
    </div>
    <div id="contentpane" class="<?php echo $columns; ?>">
        <div id="main">
            <?php
                if(isset($main_elements)){
                    foreach($main_elements as $element){
                        insertElement($element['id'], $element['type'], $element['content']);
                    }
                }
            ?>
        </div>
        <div id="right">
            <?php
                if(isset($right_elements)){
                    foreach($right_elements as $element){
                        insertElement($element['id'], $element['type'], $element['content']);
                    }
                }
            ?>
        </div>
        <div id="notes">
        </div>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
    <div id="bottom">
        <div id="errors"></div>
        <form id="submit" action="#" method="post">
          <button name="continue" type="submit" class="shadow-light corners-all ui-state-default">Continue<span class="ui-icon ui-icon-triangle-1-e"></span></button>
        </form>
        <div class="clear"></div>
    </div>
</div>

<script type="text/javascript">
    //$('textarea').elastic();

    //if the page is an assessment page, then hide the submit button
    $(function() {
      if(<?php echo $page['Type']; ?> == <?php echo ASSESSMENT_PAGE; ?>) {
        $('form button:[name=continue]').hide();
      }
    });

    //initialize media elements
    $.fn.media.mapFormat('mp3','quicktime');
    $.fn.media.mapFormat('flv','quicktime');
    $('div.media').media({
        attrs:     { wmode: 'opaque', scale: 'aspect' },
        params:    { wmode: 'opaque', scale: 'aspect' }
    });

    $('.section .ui-icon-triangle-1-e').toggle(
        function(){
            expand($(this));
        },

        function(){
            colapse($(this));
        }
    );

    $('.section .ui-icon-triangle-1-s').toggle(
        function(){
            colapse($(this));
        },
        
        function(){
            expand($(this));
        }
    );

    function colapse(object) {
        object.addClass('ui-icon-triangle-1-e').removeClass('ui-icon-triangle-1-s');
        object.siblings('.pages').slideUp('fast');
    }

    function expand(object) {
        object.addClass('ui-icon-triangle-1-s').removeClass('ui-icon-triangle-1-e');
        object.siblings('.pages').slideDown('fast');
    }
</script>

<script type="text/javascript">
    //notes
    $('.element a.note').toggle(
        function() {
            var id   = $(this).parent().attr('eid');
            var note = $('.note#'+id);
            if(note.length > 0){
                openNote(id);
            } else {
                newNote(id);
            }
            $(this).addClass('close').removeClass('add').removeClass('open');
            
        },
        function() {
            var id = $(this).parent().attr('eid');
            closeNote(id);
            $(this).addClass('open').removeClass('close');
        }
    );

    function getPosition(id) {
        var y  = ($('div:[eid='+id+']').position().top) - ($('div.element:first').position().top);
        return y;
    }

    function getNote(id) {
        //get note
        $.ajax({
            url: "note.php",
            type: "POST",
            data: {id:id},
            dataType: "html",
            success: function(msg) {
                //append form to DOM and position
                $('#notes').append(msg);
                var note = $('#notes').find('.note:last');
                note.find('textarea').elastic();
                note.draggable({ handle: '.dragbar', stop: function(event, ui) { saveNote(id); } });
                note.find('.closenote').click(function(){
                    $('div:[eid='+id+']').find('.note').click();
                    return false;
                });
                note.find('.deletenote').click(function(){
                    deleteNote(id);
                    return false;
                });
                note.find('textarea').change(function(){
                    saveNote(id);
                });
                $('div[eid='+id+']').find('.note').click();
            }
        });
    }

    function newNote(id) {
        //get target position
        var y = getPosition(id);

        //new note
        $.ajax({
            url: "note.php",
            type: "POST",
            data: {id:id},
            dataType: "html",
            success: function(msg){
                //append note to DOM and position
                $('#notes').append(msg);
                var note = $('#notes').find('.note:last');
                note.css('top',y+'px');
                note.css('left','1600px');
                note.find('textarea').elastic();
                note.draggable({ handle: '.dragbar', stop: function(event, ui) { saveNote(id); } });
                note.find('.closenote').click(function(){
                    $('div:[eid='+id+']').find('.note').click();
                    return false;
                });
                note.find('.deletenote').click(function(){
                    deleteNote(id);
                    return false;
                });
                note.animate({
                    left: '500px'
                }, 200, function() {
                    
                });
                note.find('textarea').change(function(){
                    saveNote(id);
                });
            }
        });
    }

    function openNote(id) {
        $('.note#'+id).removeClass('collapsed');
    }

    function closeNote(id) {
        $('.note#'+id).addClass('collapsed');
    }

    function deleteNote(id) {
        $('.note#'+id).find('textarea').val('');
        saveNote(id);
    }

    function saveNote(id) {
        //get values
        var _note   = $('.note#'+id);
        var note    = _note.find('textarea').val();
        var x       = _note.css('left');
        var y       = _note.css('top');
        var w       = _note.find('textarea').css('width');
        var h       = _note.find('textarea').css('height');

        var _new    = _note.hasClass('new');

        if(!_new || note != ''){
            //save note
            $.ajax({
                url: "note.php",
                type: "POST",
                data: { submit:true,
                        _new:_new,
                        id:id,
                        note:note,
                        x:x,
                        y:y,
                        w:w,
                        h:h  },
                dataType: "html",
                success: function(msg){
                    switch(msg){
                        case 'Note Saved':
                            _note.addClass('saved');
                            break;

                        case 'Note Deleted':
                            _note.fadeOut('fast').remove();
                            break;
                    }
                }
            });
        }
    }        
</script>

<script type="text/javascript">
    //input responses
    $('.input.element').each(function() {
        var id = $(this).attr('eid');
        getResponse(id);
        $(this).find('textarea, input').change(function(){
            $('#input'+id).find('.response').removeClass('saved');
            saveResponse(id);
        }).click(function(){
            $(this).parent().animate({
               outlineColor:'#cccccc'
            }, 400);
            $(this).parent().find('.alert').animate({opacity:0},'fast');
        });
    });

    function responseAlert(id, type, message) {
        var alert   = $('#input'+id).find('.alert');
        var remove  = type == 'error' ? 'success' : 'error';
        var color   = type == 'error' ? 'red' : 'green';
        alert.animate({opacity:0},'fast', function() {
            $(this).html(message).addClass(type).removeClass(remove).animate({opacity:1},'fast');
            $(this).prev().find('textarea').animate({
               outlineColor:color
            }, 100);
        });
    }

    function getResponse(id) {
        //get response
        $.ajax({
            url: "response.php",
            type: "POST",
            data: {id:id},
            dataType: "xml",
            success: function(xml) {
                $(xml).find('response').each(function() {
                    //get values
                    var _new        = $(this).find('new').text() == '1';
                    var id          = $(this).find('id').text();
                    var response    = $(this).find('text').text();
                    var personal    = $(this).find('personal').text() == '1';
                    var coach       = $(this).find('coach').text() == '1';
                    var input       = $('#input'+id);

                    //determine if existing
                    if(!_new) {//existing
                      //insert response
                      input.find('textarea').val(response);

                      if(personal) {
                        input.find('input:checkbox[name=personal]').attr('checked', true);
                      }

                      if(coach) {
                        input.find('input:checkbox[name=coach]').attr('checked', true);
                      }

                      //mark response saved
                      input.find('.response').addClass('saved');
                    } else {//new
                      //mark response new
                      input.find('.response').addClass('new');
                    }
                });
            }
        });
    }

    function saveResponse(id) {
        //get values
        var input       = $('#input'+id);
        var response    = input.find('textarea').val();
        response        = response != undefined ? response : '';
        var length      = input.find('textarea').attr('min');
        var personal    = input.find('input:checkbox[name=personal]').attr('checked');
        var coach       = input.find('input:checkbox[name=coach]').attr('checked');
        var _new        = input.find('.response').hasClass('new');

        //check for updated response
        if(response.length > 0 && !(input.find('.response').hasClass('saved'))) {
            //validate response
            if(response.length > length) {
                //save response
                $.ajax({
                    url: "response.php",
                    type: "POST",
                    data: { submit:true,
                            _new:_new,
                            id:id,
                            response:response,
                            personal:personal,
                            coach:coach },
                    dataType: "html",
                    success: function(msg){
                        if(msg == "Response Saved"){
                            //mark input saved
                            input.find('.response').addClass('saved').removeClass('new');
                            responseAlert(id, 'success', 'Response saved.');
                        }else{
                            //notify user that save failed
                            responseAlert(id, 'error', 'Failed to save response.');
                        }
                    }
                });
            } 
            else {                        
                //notify user that reponse is invalid
                responseAlert(id, 'error', 'Response too short.');
            }
        }
        else {
            //notify user that reponse is invalid
            //responseAlert(id, 'error', 'Response too short.');
        }
    }
</script>

<script type="text/javascript">
    //submitting assessment answers
    $('.textbox.element a').click(function(event) {
        if (!$(this).is('.note')) {
          //remove the redirect event
          event.preventDefault();
          var url = $.url($(this).attr("href"));
          var answer = url.param('p');
          var isSection = false;
          if (answer == undefined) {
            answer = url.param('s');
            isSection = true;
          }
          
          //check to see if the answer is correct and redirect
          $.ajax({
            url: "progress.php",
            type: "POST",
            data: {assessment:true,
                   answer:answer,
                   isSection:isSection,
                   pageId:     <?php echo "'".$page['ID']."'"; ?>,
                   sectionId:  <?php echo "'".$section['ID']."'"; ?>,
                   moduleId:   <?php echo "'".$module['ID']."'"; ?>,
                   pageOrd:    <?php echo "'".$page['Order']."'"; ?>,
                   sectionOrd: <?php echo "'".$section['Order']."'"; ?>,
                   moduleOrd:  <?php echo "'".$module['Order']."'"; ?>},
            dataType: "xml",
            success: function(xml) {
                       $(xml).find('next').each(function() {
                         //get values
                         var type = $(this).find('type').text();
                         var id   = $(this).find('id').text();

                         var url  = "/modules/?";
                         switch(type) {
                           case <?php echo "'".PAGE."'"; ?>:
                                url    += 'p';
                                break;

                           case <?php echo "'".MODULE."'"; ?>:
                                url    += 'm';
                                break;
                         }
                         url += '='+id;

                         //update href, remove handler, and trigger
                         $('#bottom #submit').attr('action',url).unbind('submit');
                         $('#bottom #submit').submit();
                       });
                     }
          });
        }
      }
    );
</script>

<script type="text/javascript">
    //submitting & updating progress
    $('#bottom #submit').submit(function() {
        var errors = '';
        $('#errors').fadeOut('fast').html(errors);

       //ensure inputs have been saved if required by the user type
       if((<?php echo $type; ?> > <?php echo COACH; ?>) && (<?php echo $type; ?> != <?php echo OTHER; ?>)) {
         $('.input .response').each(function() {
             if(!$(this).hasClass('saved')){
                 //input not saved
                 errors = 'Please enter a response to the above question(s).<br/>';
             }
         });
       }

       //if no errors, update progress and go to next page
       if(errors.length == 0) {
           $.ajax({
                url: "progress.php",
                type: "POST",
                data: {submit:true,
                       pageId:     <?php echo "'".$page['ID']."'"; ?>,
                       sectionId:  <?php echo "'".$section['ID']."'"; ?>,
                       moduleId:   <?php echo "'".$module['ID']."'"; ?>,
                       pageOrd:    <?php echo "'".$page['Order']."'"; ?>,
                       sectionOrd: <?php echo "'".$section['Order']."'"; ?>,
                       moduleOrd:  <?php echo "'".$module['Order']."'"; ?>},
                dataType: "xml",
                success: function(xml) {
                    $(xml).find('next').each(function() {
                        //get values
                        var type = $(this).find('type').text();
                        var id   = $(this).find('id').text();

                        var url  = "/modules/?";
                        switch(type) {
                            case <?php echo "'".PAGE."'"; ?>:
                                url    += 'p';
                                break;

                            case <?php echo "'".MODULE."'"; ?>:
                                url    += 'm';
                                break;
                        }
                        url += '='+id;

                        //update href, remove handler, and trigger
                        $('#bottom #submit').attr('action',url).unbind('submit');
                        $('#bottom #submit').submit();
                    });
                }
           });
       } else {
           $('#errors').html(errors).fadeIn('fast');
       }

       return false;
    });
</script>

<script type="text/javascript">
    <?php
      foreach($notes as $id){
        echo 'getNote('.$id.');'.PHP_EOL;
      }
    ?>
</script>