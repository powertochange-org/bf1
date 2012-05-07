<?php
/*
 * Cru Doctrine
 * Admin - Page Builder - Page Design
 * Campus Crusade for Christ
 */

try {
    //initialize the database object
    $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
    $db->connect();

    if(!$new) { //get elements for existing page

        //element arrays
        $main_elements  = array();
        $right_elements = array();

        //get elements
        $sql = "SELECT * FROM element WHERE PageId = ".$id." ORDER BY Ord";
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

                case 'textbox':     //textbox
                    $content    = $db_content['Text'];
                    break;

                case 'media':       //media
                    $filename   = $db_content['Filename'];
                    $height     = $db_content['Height'];
                    $width      = $db_content['Width'];
                    $caption    = $db_content['Caption']    != '' ? '<div class="caption">'.$db_content['Caption'].'</div>' : '';
                    $content    = '<div align="center"><div class="media {width:'.$width.', height:'.$height.' }" href="'.$filename.'" data-href="'.$filename.'"></div>'.$caption.'</div>';
                    break;

                case 'image':       //image
                    $filename   = $db_content['Filename'];
                    $height     = $db_content['Height']      != 0 ? 'height="'.$db_content['Height'].'"' : '';
                    $width      = $db_content['Width']       != 0 ? 'width="'.$db_content['Width'].'"'   : '';
                    $caption    = $db_content['Caption']    != '' ? '<div class="caption">'.$db_content['Caption'].'</div>' : '';
                    $content    = '<div align="center"><img src="'.$filename.'" '.$height.' '.$width.' />'.$caption.'</div>';
                    break;

                case 'input':       //input
                    $question   = $db_content['Question'];
                    $personal   = $db_content['Personal']   == 1 ? 'checked' : '';
                    $coach      = $db_content['Coach']      == 1 ? 'checked' : '';
                    $min        = $db_content['Min'];
                    $content    = '<div class="input"><div class="question">'.$question.'</div>';
                    $content   .= '<div class="response"><textarea name="response" min="'.$min.'"></textarea></div>';
                    $content   .= '<div class="flags"><div><input type="checkbox" name="personal" disabled '.$personal.' /><label>Flag For Personal Followup</label></div><div><input type="checkbox" name="coach" disabled '.$coach.' /><label>Flag For Coach Followup</label></div></div>';
                    $content   .= '</div>';
                    break;

                case 'whitespace':  //whitespace
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
            switch($db_element['Loc']){

                case 'main':
                    $main_elements[] = $element;
                    break;

                case 'right':
                    $right_elements[] = $element;

            }

        }

    }

    $db->close();

} catch (PDOException $e) {
    echo $e->getMessage();
    exit();
}

//function to insert element
function insertElement($_id, $_type, $_content) {
    //fill template
    $element  = '<div id="'.$_type.'" class="'.$_type.' tool" eId="'.$_id.'">'.PHP_EOL;
    $element .= '   <div class="element">'.PHP_EOL;
    $element .= '       <div class="menu ui-widget-header ui-corner-all">'.PHP_EOL;
    $element .= '           <div class="edit" onclick="$(this).parent().parent().addClass(\'editing\'); openEditor($(this).parent().parent().parent().attr(\'id\'));"></div>'.PHP_EOL;
    $element .= '           <div class="settings"></div>'.PHP_EOL;
    $element .= '           <div class="delete" onclick="$(this).parent().parent().parent().addClass(\'deleted\').appendTo(\'#trashbin\'); makeDraggable();"></div>'.PHP_EOL;
    $element .= '           <div class="dragbar"><span class=" ui-icon ui-icon-grip-dotted-vertical"></span></div>'.PHP_EOL;
    $element .= '       </div>'.PHP_EOL;
    $element .= '       <div class="content">'.PHP_EOL;
    $element .= '           '.$_content.PHP_EOL;
    $element .= '       </div>'.PHP_EOL;
    $element .= '   </div>'.PHP_EOL;
    $element .= '</div>'.PHP_EOL;

    //add to DOM
    echo $element;
}
?>

<!--metadata-->
<script type="text/javascript" src="/jquery/metadata/jquery.metadata.js"></script>
<!--uploadify-->
<script type="text/javascript" src="/jquery/uploadify/jquery.uploadify.v2.1.0.js"></script>
<script type="text/javascript" src="/jquery/uploadify/swfobject.js"></script>
<link rel="stylesheet" href="/jquery/uploadify/uploadify.css" type="text/css" />
<!--jwysiwyg-->
<script type="text/javascript" src="/jquery/jwysiwyg/jquery.wysiwyg.js"></script>
<link rel="stylesheet" href="/jquery/jwysiwyg/jquery.wysiwyg.css" type="text/css" />
<!--jwplayer-->
<script type="text/javascript" src="/jquery/jwplayer/jwplayer.js"></script>

<div>

    <div id="toolbox">

        <div id="textbox" class="textbox tool" eId="0"><span>Text</span></div>

        <div id="media" class="media tool" eId="0"><span>Media</span></div>

        <div id="image" class="image tool" eId="0"><span>Image</span></div>

        <div id="input" class="input tool" eId="0"><span>Input</span></div>

        <div id="whitespace" class="whitespace tool" eId="0"><span>Whitespace</span></div>
        
        <hr>

        <div id="trash" class="trash">

            <div id="trashbin"></div>
            
        </div>

    </div>

    <div id="canvas">

        <div id="canvasmain">
            <?php
                if(isset($main_elements)){
                    foreach($main_elements as $element){
                        insertElement($element['id'], $element['type'], $element['content']);
                    }
                }
            ?>
        </div>

        <div id="canvasright">
            <?php
                if(isset($right_elements)){
                    foreach($right_elements as $element){
                        insertElement($element['id'], $element['type'], $element['content']);
                    }
                }
            ?>
        </div>

    </div>

    <form id="saveForm" action="../?p=modules&id=<?php echo $moduleId; ?>" method="POST">
        <button type="submit" name="save">SAVE</button>
        <button name="cancel" type="submit" onclick="cancelFunc();return(false);">CANCEL</button>
    </form>

    <div id="textbox_editor">
        
    </div>

    <div id="image_editor">
        <div>
            1. Source
        </div>
        <hr>
        <fieldset id="source">
            <div>
                <input type="radio" name="src" value="upload" checked /><label> Upload: </label><input class="upload" type="text" name="upload" value="" /> <button type="button" name ="browse" id="imageBrowse">Browse</button>
            </div>
            <div>
                <input type="radio" name="src" value="url"/><label> Insert URL: </label><input class="url" type="text" name="url" value="" />
            </div>
            <div id="imageupload"></div>
        </fieldset>
        <div>
            2. Caption
        </div>
        <hr>
        <fieldset id="caption">
            <div>
                <textarea name="caption"></textarea>
            </div>
        </fieldset>
        <div>
            3. Options
        </div>
        <hr>
        <fieldset id="options" >
            <div id="size">
                <div class="size small"><div class="label">Small</div></div>
                <div class="size medium"><div class="label">Medium</div></div>
                <div class="size large"><div class="label">Large</div></div>
                <div class="size custom"><div class="label">Custom</div></div>
                <div id="height">
                    <label>H</label><input class="dim" type="text" name="height" value="">
                </div>
                <div id="width">
                    <label>W</label><input class="dim" type="text" name="width" value="">
                </div>
            </div>
        </fieldset>
    </div>

    <div id="media_editor">
        <div>
            1. Source
        </div>
        <hr>
        <fieldset id="source">
            <div>
                <input type="radio" name="src" value="upload" checked /><label> Upload: </label><input class="upload" type="text" name="upload" value="" /> <button type="button" name="browse" id="mediaBrowse">Browse</button>
            </div>
            <div>
                <input type="radio" name="src" value="url"/><label> Insert URL: </label><input class="url" type="text" name="url" value="" />
            </div>
            <div id="mediaupload"></div>
        </fieldset>
        <div>
            2. Caption
        </div>
        <hr>
        <fieldset id="caption">
            <div>
                <textarea name="caption"></textarea>
            </div>
        </fieldset>
        <div>
            3. Options
        </div>
        <hr>
        <fieldset id="options" >
            <div id="size">
                <div class="size small"><div class="label">Small</div></div>
                <div class="size medium"><div class="label">Medium</div></div>
                <div class="size large"><div class="label">Large</div></div>
                <div class="size custom"><div class="label">Custom</div></div>
                <div id="height">
                    <label>H</label><input class="dim" type="text" name="height" value="">
                </div>
                <div id="width">
                    <label>W</label><input class="dim" type="text" name="width" value="">
                </div>
            </div>
        </fieldset>
    </div>

    <div id="input_editor">
        <div>
            1. Question
        </div>
        <hr>
        <fieldset id="question">
            <div>
                <textarea name="question"></textarea>
            </div>
        </fieldset>
        <div>
            2. Default Flags & Minimum Response
        </div>
        <hr>
        <fieldset id="options">
            <div>
                <input type="checkbox" name="personal" /><label> Personal Followup </label>
                <input type="checkbox" name="coach" /><label> Coach Followup </label>
            </div>
            <div>
                <label>Minimum Response Length: </label><input type="text" name="responseLength" value="5" />
            </div>
        </fieldset>
    </div>

    <div id="save_dialog">
        <div id="message"></div>
        <div id="progress"><div id="bar"></div></div>
    </div>

    <div id="element">
        <div class="element">
            <div class="menu ui-widget-header ui-corner-all">
                <div class="edit" onclick="$(this).parent().parent().addClass('editing'); openEditor($(this).parent().parent().parent().attr('id'));"></div>
                <div class="settings"></div>
                <div class="delete" onclick="$(this).parent().parent().parent().addClass('deleted').appendTo('#trashbin'); makeDraggable();"></div>
                <div class="dragbar"><span class=" ui-icon ui-icon-grip-dotted-vertical"></span></div>
            </div>
            <div class="content">

            </div>
        </div>
    </div>

</div>

<!-- PASSED VALUES -->
<script type="text/javascript">
    var _pageId      = <?php echo $id != '' ? $id : 0; ?>;
    var _title       = <?php echo "'".$_title."'"; ?>;
    var _section     = <?php echo $sectionId; ?>;
    var _order       = <?php echo $order; ?>;
    var _visibility  = <?php echo "'".$visibility."'"; ?>;
</script>

<!-- UI CONTROLS -->
<script type="text/javascript">

    function openEditor(type) {
        var div = '#'+type+'_editor';
        $(div).dialog('open');
    }

    function makeDraggable() {
        $('#toolbox .tool').draggable({
            helper: 'clone',
            grid: [10, 10],
            revert: 'invalid',
            start: function(event, ui) {
                $(this).addClass('tool-adding');
            },
            stop: function(event, ui) {
                $(this).removeClass('tool-adding');
            }
        });
    }

    function makeSortable() {
        $('#canvasmain').sortable({ handle: '.dragbar', forcePlaceholderSize: true, containment: '#canvasmain' });
        $('#canvasright').sortable({ handle: '.dragbar', forcePlaceholderSize: true, containment: '#canvasright' });
    }

    function makeHoverable() {
        $('#canvas .tool').hover(
            function(){
                $(this).find('.menu').fadeIn('fast');
            },
            function(){
                $(this).find('.menu').fadeOut('fast');
            }
        );
    }

    $('#trash').toggle(
        function(event, ui){
            $('#trashbin').animate({
                opacity: 0.8,
                width: '750px',
                marginLeft: '100px'
              }, 200, 'linear'
            )
        },
        function(event, ui){
            $('#trashbin').animate({
                opacity: 0,
                width: '0px',
                marginLeft: '0px'
              }, 200, 'linear'
            )
        }
    );

    $(function() {
        //intialize toolbox
        makeDraggable();

        //make canvas items sortable
        makeSortable();

        //initialize media elements
        $.fn.media.mapFormat('mp3','quicktime');
        $.fn.media.mapFormat('flv','quicktime');
        $('div.media').media({
            attrs:     { wmode: 'opaque', scale: 'aspect' },
            params:    { wmode: 'opaque', scale: 'aspect' }
        });

        //initialize & configure editors

        //input editor
        $('#input_editor').dialog({
            autoOpen: false,
            //modal: true,
            height: 400,
            width: 550,
            resizable: false,
            buttons: {
                "Ok": function() {
                    //assemble image tag
                    var question = $(this).find('textarea[name=question]').val();
                    var personal = $(this).find('input:checkbox[name=personal]').attr('checked');
                    var coach = $(this).find('input:checkbox[name=coach]').attr('checked');
                    var length = $(this).find('input:text[name=responseLength]').val();

                    var input = '<div class="input"><div class="question">'+question+'</div>';
                    input += '<div class="response"><textarea name="response" min="'+length+'"></textarea></div>';
                    input += '<div class="flags"><div><input type="checkbox" name="personal" READONLY /><label>Flag For Personal Followup</label></div><div><input type="checkbox" name="coach" readonly /><label>Flag For Coach Followup</label></div></div>';
                    $('.editing').find('.content').html(input);
                    $('.editing').find('input:checkbox[name=personal]').attr('checked', personal).attr('disabled', personal);
                    $('.editing').find('input:checkbox[name=coach]').attr('checked', coach).attr('disabled', coach);

                    $(this).dialog("close");
                },
                "Cancel": function() {
                    $(this).dialog("close");
                }
            },

            open: function(event, ui) {
                $('embed').hide();

                if($('.editing').find('.content').html().length > 0) {
                    //get initial values
                    var question = $('.editing .content').find('.question').html();
                    var personal = $('.editing .content').find('input:checkbox[name=personal]').attr('checked');
                    var coach = $('.editing .content').find('input:checkbox[name=coach]').attr('checked');
                    var length = $('.editing .content').find('textarea[name=response]').attr('min');

                    //set values
                    $(this).find('textarea[name=question]').val(question);
                    $(this).find('input:checkbox[name=personal]').attr('checked', personal);
                    $(this).find('input:checkbox[name=coach]').attr('checked', coach);
                    $(this).find('input:text[name=responseLength]').val(length);
                }

            },

            close: function(event, ui) {
                $('embed').show();

                //clear values
                $(this).find('textarea[name=question]').val('');
                $(this).find('input:checkbox[name=personal]').attr('checked', false);
                $(this).find('input:checkbox[name=coach]').attr('checked', false);
                $(this).find('input:text[name=responseLength]').val('5');
                $('.editing').removeClass('editing');
            },

            resize: function(event, ui) {}

        });

        //media editor
        $('#media_editor').dialog({
            autoOpen: false,
            //modal: true,
            height: 500,
            width: 550,
            resizable: false,
            buttons: {
                "Ok": function() {
                    //assemble media tag
                    var type = $(this).find('input:checked').val();
                    var src = $(this).find('input:text[name='+type+']').val();

                    var media = '<div align="center"><div class="media {';
                    
                    //caption
                    var caption = $(this).find('textarea[name=caption]').val();

                    //add height and width if applicable

                    var width = $(this).find('input:text[name=width]').val();
                    if(width.length > 0) {
                        media += 'width:'+width+', ';
                    }

                    var height = $(this).find('input:text[name=height]').val();
                    if(height.length > 0) {
                        media += 'height:'+height+' ';
                    }

                    //close media div
                    media += '}" href="'+src+'" data-href="'+src+'"></div>';

                    //add caption if applicable
                    if(caption.length > 0){
                        media += '<div class="caption">'+caption+'</div>';
                    }

                    //close div
                    media += '</div>';

                    $('.editing').find('.content').html(media);
                    $('div.media').media({
                        attrs:     { wmode: 'opaque', scale: 'aspect' },
                        params:    { wmode: 'opaque', scale: 'aspect' }
                    });

                    $(this).dialog("close");
                },
                "Cancel": function() {
                    $(this).dialog("close");
                }
            },
            open: function(event, ui) {
                $('embed').hide();

                $(this).find('#size').selectable({
                    filter: '.size',
                    selected: function() {
                        //small
                        if($(this).find('.ui-selected').hasClass('small')){
                            $(this).find('input').val('200');
                            $(this).find('input').prop("disabled", true);
                        }

                        //medium
                        if($(this).find('.ui-selected').hasClass('medium')){
                            $(this).find('input').val('400');
                            $(this).find('input').prop("disabled", true);
                        }

                        //small
                        if($(this).find('.ui-selected').hasClass('large')){
                            $(this).find('input').val('600');
                            $(this).find('input').prop("disabled", true);
                        }

                        //small
                        if($(this).find('.ui-selected').hasClass('custom')){
                            $(this).find('input').val('');
                            $(this).find('input').removeAttr("disabled");
                        }
                    }
                });

                $(this).find('.custom').addClass('ui-selected');

                //check for existing content
                if($('.editing .content').html().length !== 0) {
                    //get source
                    var src = $('.editing .content').find('.media').attr('data-href');

                    //determine if local or external
                    if (src != undefined) {
                        if (src.substring(0, 4) == 'http') {
                            //external
                            $(this).find('input:radio[value=upload]').prop('checked', false);
                            $(this).find('input:radio[value=url]').prop('checked', true);
                            $(this).find('input:text[name=url]').val(src);
                        } else {
                            //local
                            $(this).find('input:radio[value=upload]').prop('checked', true);
                            $(this).find('input:radio[value=url]').prop('checked', false);
                            $(this).find('input:text[name=upload]').val(src);
                        }
                    }

                    //set height & width
                    var height = $('.editing .content').find('.media').find('object').attr('height');
                    var width = $('.editing .content').find('.media').find('object').attr('width');
                    $(this).find('input:text[name=height]').val(height);
                    $(this).find('input:text[name=width]').val(width);

                    //set caption
                    var caption = $('.editing .content').find('.caption').html();
                    $(this).find('textarea[name=caption]').val(caption);
                }

            },

            close: function(event, ui) {
                $('embed').show();

                //clear values
                $(this).find('input:radio[value=upload]').prop('checked', true);
                $(this).find('input:radio[value=url]').prop('checked', false);
                $(this).find('input:text[name=upload]').val('');
                $(this).find('input:text[name=url]').val('');
                $(this).find('input:text[name=height]').val('');
                $(this).find('input:text[name=width]').val('');
                $(this).find('textarea[name=caption]').val('');

                $('.editing').removeClass('editing');
            },

            resize: function(event, ui) {}
        });

        //image editor
        $('#image_editor').dialog({
            autoOpen: false,
            //modal: true,
            height: 500,
            width: 550,
            resizable: false,
            buttons: {
                "Ok": function() {

                    //assemble image tag
                    var img = '<div align="center"><img src="';
                    var type = $(this).find('input:checked').val();

                    var src = $(this).find('input:text[name='+type+']').val();
                    img += src+'"';

                    //add height and width if applicable
                    var height = $(this).find('input:text[name=height]').val();
                    if(height.length > 0) {
                        img += 'height = "'+height+'" ';
                    }

                    var width = $(this).find('input:text[name=width]').val();
                    if(width.length > 0) {
                        img += 'width = "'+width+'" ';
                    }

                    //close image div
                    img += ' />';

                    //add caption if applicable
                    var caption = $(this).find('textarea[name=caption]').val();
                    if(caption.length > 0){
                        img += '<div class="caption">'+caption+'</div>';
                    }

                    //close div
                    img += '</div>';

                    //insert image into element
                    $('.editing').find('.content').html(img);
                    
                    $(this).dialog("close");
                },
                "Cancel": function() {
                    $(this).dialog("close");
                }
            },

            open: function(event, ui) {
                $('embed').hide();

                $(this).find('#size').selectable({
                    filter: '.size',
                    selected: function(){

                        //small
                        if($(this).find('.ui-selected').hasClass('small')){
                            $(this).find('input').val('200');
                            $(this).find('input').attr("disabled", true);
                        }

                        //medium
                        if($(this).find('.ui-selected').hasClass('medium')){
                            $(this).find('input').val('400');
                            $(this).find('input').attr("disabled", true);
                        }

                        //small
                        if($(this).find('.ui-selected').hasClass('large')){
                            $(this).find('input').val('600');
                            $(this).find('input').attr("disabled", true);
                        }

                        //small
                        if($(this).find('.ui-selected').hasClass('custom')){
                            $(this).find('input').val('');
                            $(this).find('input').removeAttr("disabled");
                        }

                    }
                });
                
                $(this).find('.custom').addClass('ui-selected');

                //check for existing content
                if ($('.editing .content').html() !== '') {

                    //get source
                    var src = $('.editing .content').find('img').attr('src');

                    //determine if local or external
                    if (src != undefined) {
                        if (src.substring(0, 4) == 'http') {
                            //external
                            $(this).find('input:radio[value=upload]').prop('checked', false);
                            $(this).find('input:radio[value=url]').prop('checked', true);
                            $(this).find('input:text[name=url]').val(src);
                        } else {
                            //local
                            $(this).find('input:radio[value=upload]').prop('checked', true);
                            $(this).find('input:radio[value=url]').prop('checked', false);
                            $(this).find('input:text[name=upload]').val(src);
                        }
                    }

                    //set height & width if set in html
                    var height = $('.editing .content').find('img').attr('height');
                    if(height !== undefined){
                        $(this).find('input:text[name=height]').val(height);
                    }

                    var width = $('.editing .content').find('img').attr('width');
                    if(width !== undefined){
                        $(this).find('input:text[name=width]').val(width);
                    }

                    //set caption
                    var caption = $('.editing .content').find('.caption').html();
                    $(this).find('textarea[name=caption]').val(caption);

                }
            },

            close: function(event, ui) {

                $('embed').show();

                //clear values
                $(this).find('input:text[name=upload]').val('');
                $(this).find('input:text[name=url]').val('');
                $(this).find('input:text[name=height]').val('');
                $(this).find('input:text[name=width]').val('');

                $('.editing').removeClass('editing');
            },

            resize: function(event, ui) {}

        });

        //file uploads
        $('#imageBrowse').uploadify({
            'uploader'  : '/jquery/uploadify/uploadify.swf',
            'script'    : '/jquery/uploadify/uploadify.php',
            'cancelImg' : '/jquery/uploadify/cancel.png',
            'auto'      : true,
            'folder'    : '/upload/images',
            'queueID'   : 'imageupload',
            'wmode'     : 'transparent',
            'onComplete': function(event, queueID, fileObj, response, data){
                if (response == '1') {
                    $('#image_editor').find('input:text[name=upload]').val(fileObj.filePath);
                    $('#image_editor').find('input:radio[value=upload]').attr('checked', true);
                } else {
                    alert(response);
                }
            }
        });

        $('#mediaBrowse').uploadify({
            'uploader'  : '/jquery/uploadify/uploadify.swf',
            'script'    : '/jquery/uploadify/uploadify.php',
            'cancelImg' : '/jquery/uploadify/cancel.png',
            'auto'      : true,
            'folder'    : '/upload/media',
            'queueID'   : 'mediaupload',
            'wmode'     : 'transparent',
            'onComplete': function(event, queueID, fileObj, response, data) {
                if (response == '1') {
                    $('#media_editor').find('input:text[name=upload]').val(fileObj.filePath);
                    $('#media_editor').find('input:radio[value=upload]').attr('checked', true);
                } else {
                    alert(response);
                }
            }
        });

        //text editor
        $('#textbox_editor').dialog({
            autoOpen: false,
            //modal: true,
            height: 300,
            width: 550,
            resizable: false,
            buttons: {
                "Ok": function() {
                    var text = $(this).find('textarea').val();
                    $('.editing').find('.content').html(text);
                    $(this).dialog("close");
                },
                "Cancel": function() {                    
                    $(this).dialog("close");
                }
            },

            open: function(event, ui) {
                $('embed').hide();

                //add textarea
                $(this).append('<textarea id="edit_text"></textarea>');

                //get text and add to text area
                var text = $('.editing').find('.content').html();
                $('#edit_text').val(text);

                //initialize wysiwyg editor
                $('#edit_text').wysiwyg();
                $('.wysiwyg').css('width','100%');
                $('iframe').css('width','100%');
                $('.panel').css('width','100%');

            },

            close: function(event, ui) {
                $('embed').show();

                $(this).children().remove();
                $('.editing').removeClass('editing');
            },

            resize: function(event, ui) {}

        });       

        //initialize canvas
        makeHoverable();

        //make canvas droppable
        $('#canvasmain').droppable({
            over: function(event, ui) {

                $(this).addClass('dropover');

            },

            out: function(event, ui) {

                $(this).removeClass('dropover');

            },

            drop: function(event, ui) {

                $(this).removeClass('dropover');

                //check for previously deleted item
                if($('.tool-adding').hasClass('deleted')) {
                    //add deleted element to canvas
                    $(this).append($('.tool-adding').removeClass('ui-draggable tool-adding deleted'));
                } else {
                    //add to canvas
                    $(this).append($('.tool-adding').clone().html($('#element').html()).removeClass('tool-adding'));

                    //make adjustements for whitespace tool
                    $(this).find('.whitespace .edit').remove();
                    $(this).find('.whitespace .settings').remove();
                    $(this).find('.whitespace .content').html('<div></div>');
                    $(this).find('.whitespace .content').find('div').resizable({
                       handles: 's',
                       grid: 10
                    });
                }

                //make elements sortable
                makeSortable()

                //make elements hoverable
                makeHoverable();

            }
        });

        $('#canvasright').droppable({
            accept: '.input, .whitespace',

            over: function(event, ui) {

                $(this).addClass('dropover');

            },

            out: function(event, ui) {

                $(this).removeClass('dropover');

            },

            drop: function(event, ui) {

                $(this).removeClass('dropover');

                //check for previously deleted item
                if($('.tool-adding').hasClass('deleted')) {
                    //add deleted element to canvas
                    $(this).append($('.tool-adding')).removeClass('deleted').removeClass('tool-adding');
                } else {
                    //add blank element to canvas
                    $(this).append($('.tool-adding').clone().html($('#element').html()).removeClass('tool-adding'));

                    //make adjustements for whitespace tool
                    $(this).find('.whitespace .edit').remove();
                    $(this).find('.whitespace .settings').remove();
                    $(this).find('.whitespace .content').html('<div></div>');
                    $(this).find('.whitespace .content').find('div').resizable({
                       handles: 's',
                       grid: 10
                    });
                }
                
                //make elements sortable
                makeSortable();

                //make elements hoverable
                makeHoverable();

            }
        });
    });
</script>

<!-- SAVING -->
<script type="text/javascript">
    function cancelFunc(){
        window.location.href = "/admin/?p=modules&id=<?php echo $moduleId; ?>";
    }

    var saved = false;

    $('#saveForm').submit(function(){
        if(!saved){
            savePage();
            return false;
        }
    });

    function savePage() {
        //main canvas
        var xmlMain = '<elements>';

        //process canvasmain elements
        $('#canvasmain .tool').each(function(index){
            var type    = $(this).attr('id');
            var id      = $(this).attr('eId');
            var order   = index;
            xmlMain += '<element type="'+type+'" id="'+id+'" order="'+order+'">';
            switch(type){
                case 'textbox':
                    var text = $(this).find('.content').html();
                    xmlMain += '<text><![CDATA['+text+']]></text>';
                    break;

                case 'media':
                    var url = $(this).find('.content').find('.media').find('object').attr('data');
                    var height = $(this).find('.content').find('.media').find('object').attr('height');
                    var width = $(this).find('.content').find('.media').find('object').attr('width');
                    var caption = $(this).find('.content').find('.caption').html();
                    xmlMain += '<url>'+url+'</url><height>'+height+'</height><width>'+width+'</width><caption>'+caption+'</caption>';
                    break;

                case 'image':
                    var url = $(this).find('.content').find('img').attr('src');
                    var height = $(this).find('.content').find('img').attr('height');
                    var width = $(this).find('.content').find('img').attr('width');
                    var caption = $(this).find('.content').find('.caption').html();
                    xmlMain += '<url>'+url+'</url><height>'+height+'</height><width>'+width+'</width><caption>'+caption+'</caption>';
                    break;

                case 'input':
                    var question = $(this).find('.content').find('.question').html();
                    var personal = $(this).find('.content').find('.flags').find('input:checkbox[name=personal]').attr('checked');
                    var coach = $(this).find('.content').find('.flags').find('input:checkbox[name=coach]').attr('checked');
                    var min = $(this).find('.content').find('.response').find('textarea').attr('min');
                    xmlMain += '<question>'+question+'</question><personal>'+personal+'</personal><coach>'+coach+'</coach><min>'+min+'</min>';
                    break;

                case 'whitespace':
                    var height = $(this).find('.content div').height();
                    xmlMain += '<height>'+height+'</height>';
                    break;

            }
            xmlMain += '</element>';
        });

        xmlMain += '</elements>';

        //right canvas
        var xmlRight = '<elements>';

        //process canvasright elements
        $('#canvasright .tool').each(function(index){
            var type    = $(this).attr('id');
            var id      = $(this).attr('eId');
            var order   = index;
            xmlRight += '<element type="'+type+'" id="'+id+'" order="'+order+'">';
            switch(type){
                case 'input':
                    var question = $(this).find('.content').find('.question').html();
                    var personal = $(this).find('.content').find('.flags').find('input:checkbox[name=personal]').attr('checked');
                    var coach = $(this).find('.content').find('.flags').find('input:checkbox[name=coach]').attr('checked');
                    var min = $(this).find('.content').find('.response').find('textarea').attr('min');
                    xmlRight += '<question>'+question+'</question><personal>'+personal+'</personal><coach>'+coach+'</coach><min>'+min+'</min>';
                    break;

                case 'whitespace':
                    var height = $(this).find('.content div').height();
                    xmlRight += '<height>'+height+'</height>';
                    break;

            }
            xmlRight += '</element>';
        });

        xmlRight += '</elements>';

        //trash
        var xmlTrash = '<elements>';

        //process trashbin elements
        $('#trashbin .tool').each(function(){
           var type    = $(this).attr('id');
           var id      = $(this).attr('eId');
           xmlTrash += '<element type="'+type+'" id="'+id+'"></element>';
        });

        xmlTrash += '</elements>'

        //send data to server for processing
        $.ajax({
            url: 'save.php',
            type: 'POST',
            data: ({    pageId:     _pageId,
                        title:      _title,
                        section:    _section,
                        order:      _order,
                        visibility: _visibility,
                        main:       xmlMain,
                        right:      xmlRight,
                        trash:      xmlTrash
            }),
            cache: false,
            beforeSend: function(XMLHttpRequest) {
                $("#save_dialog").dialog({
                    modal: true,
                    title: 'Saving Page',
                    draggable: false,
                    resizable: false,
                    closeOnEscape: false,
                    height: 80,
                    open: function() {
                        $('embed').hide();
                    },
                    close: function() {
                        $('embed').show();
                    }
                });

                $("#save_dialog #message").html('Sending data...');
                $("#progress #bar").progressbar({
                   value: 20
                });

            },
            success: function(data, textStatus, XMLHttpRequest) {
                $("#save_dialog #message").html('Data sent.');
                $("#progress #bar").progressbar( "value" , 75 );

                if(data == '1') { //save successfull
                    $("#save_dialog #message").html('Save successful!');
                    saved = true;
                } else { //save unsuccessfull
                    $("#save_dialog #message").html('Save unsuccessful, an unknown error occurred.');
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                $("#save_dialog #message").html('Save unsuccessfull, an error occured');
                $("#progress #bar").progressbar( "value" , 75 );                
            },
            complete: function(XMLHttpRequest, textStatus) {
                $("#progress #bar").progressbar( "value" , 100 );
                $( "#save_dialog" ).dialog( "option", "buttons", { "Ok": function() { $('#saveForm').submit(); } } );
            }
        });
    }
</script>