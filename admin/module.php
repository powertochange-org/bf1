<?php
/*
 * Cru Doctrine
 * Admin - Modules - Edit Module
 * Campus Crusade for Christ
 */

//module id
$moduleId = $_GET['id'];

//get module from db
$module = array();

try {

    global $module;

    //initialize the database object
    $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
    $db->connect();

    //get module information
    $sql = "SELECT * FROM module WHERE ID = ".$moduleId;

    //assign module information to array
    $module['info'] = $db->query_first($sql);

    //get sections
    $sql = "SELECT * FROM section WHERE moduleId = ".$moduleId." ORDER BY Ord";
    //execute query
    $db_sections = $db->fetch_array($sql);

    //construct section array
    $sections = array();

    foreach($db_sections as $db_section){
        $section = array();
        $sectionId = $db_section['ID'];
        $section['info'] = $db_section;

        //get corresponding pages
        $sql = "SELECT * FROM page WHERE sectionId = ".$sectionId." ORDER BY Ord";
        //execute query
        $db_pages = $db->fetch_array($sql);

        //attach pages to section
        $section['pages'] = $db_pages;

        $sections[] = $section;
    }

    //attach sections to module
    $module['sections'] = $sections;

    $db->close();

} catch (PDOException $e){
    echo $e->getMessage();
}


?>

<div id="module">

    <div id="information">

        <form id="editModule" action="?p=modules&id=<?php echo $moduleId; ?>&request=edit_module" method="post">
            <input type="hidden" name="order" value="<?php echo $module['info']['Ord']; ?>"/>
            <button type="submit" value="submit" class="corners-all shadow-light"><span class="ui-icon ui-icon-pencil"></span>Edit</button>
        </form>

        <div id="title">
            <div id="number"><?php echo $module['info']['Number']; ?></div>
            <div id="name"><?php echo $module['info']['Name']; ?><div id="bar"></div></div>
        </div>

        <div id="description">
            <?php echo $module['info']['Descr']; ?>
        </div>

    </div>

    <div id="sections">

        <form id="addSection" action="?p=modules&id=<?php echo $moduleId; ?>&request=edit_section" method="post">
            <input type="hidden" name="order" value="<?php echo count($module['sections']); ?>"/>
            <button type="submit" value="submit" class="corners-all shadow-light"><span class="ui-icon ui-icon-plus"></span>Add Section</button>
        </form>

        <?php

            //sections & pages
            foreach($module['sections'] as $section) {
                echo '<div class = "section" id = "'.$section['info']['ID'].'">';

                echo '<div class="sectioninfo">';

                echo '<div class="colapse ui-icon ui-icon-triangle-1-s"></div>';

                //section title
                echo '<div class="title">'.$section['info']['Title'].'</div>';

                //drag icon
                echo '<div class="drag ui-icon ui-icon-grip-solid-horizontal"></div>';

                //remove section
                echo '<a class="remove corners-all" href="#"><span class="ui-icon ui-icon-minus"></span> Remove</a>';

                //add page button
                echo '<a class="addpage corners-right" href="pagebuilder?section='.$section['info']['ID'].'&module='.$moduleId.'&ord='.(count($section['pages'])).'"><span class="ui-icon ui-icon-plus"></span> Add Page</a>';

                //edit section
                echo '<a class="edit corners-left" href="?p=modules&id='.$moduleId.'&request=edit_section&sid='.$section['info']['ID'].'"><span class="ui-icon ui-icon-pencil"></span> Edit</a>';

                echo '</div>';
                
                //pages
                echo '<div class="pages">';
                foreach($section['pages'] as $page) {
                    echo '<div class="page" id="'.$page['ID'].'">';

                    echo '<div class="pageinfo">';

                    //page title
                    echo '<div class="title">'.$page['Title'].'</div>';

                    //drag icon
                    echo '<div class="drag ui-icon ui-icon-grip-solid-horizontal"></div>';

                    //remove page
                    echo '<a class="remove corners-all" href="#"><span class="ui-icon ui-icon-minus"></span> Remove</a>';

                    //edit page
                    echo '<form action="pagebuilder" method="get">
                            <input type="hidden" name="page" value="'.$page['ID'].'" />
                            <input type="hidden" name="section" value="'.$page['SectionId'].'" />
                            <input type="hidden" name="module" value="'.$moduleId.'" />
                            <input type="hidden" name="ord" value="'.$page['Ord'].'" />
                            <button type="submit" class="edit corners-all"><span class="ui-icon ui-icon-pencil"></span>Edit</button>
                          </form>';

                    echo '</div>';

                    echo '</div>';

                }
                echo '</div></div>';
             }

        ?>
    </div>
</div>

<script type="text/javascript">

    $('#sections').sortable({
        items: '.section',
        handle: '.drag',
        placeholder: 'ui-state-highlight',
        forcePlaceholderSize: true,
        cancel: 'button, a',
        update: function(event, ui){
            reorder('section', $(this).sortable('toArray'));
        }
    });

    $('.pages').sortable({
        handle: '.drag',
        placeholder: 'ui-state-highlight',
        forcePlaceholderSize: true,
        cancel: 'button, a',
        update: function(event, ui){
            reorder('page', $(this).sortable('toArray'));
        }
    });

    $('.section .colapse').toggle(
        function(event, ui){
            $(this).addClass('ui-icon-triangle-1-e').removeClass('ui-icon-triangle-1-s');
            $(this).parent().siblings('.pages').slideUp('fast');
        },
        function(event, ui){
            $(this).addClass('ui-icon-triangle-1-s').removeClass('ui-icon-triangle-1-e');
            $(this).parent().siblings('.pages').slideDown('fast');
        }
    );

    $('#addSection').submit(function(){
        //get new section form
        $.ajax({
            url: "edit_section.php?id=<?php echo $moduleId; ?>",
            dataType: "html",
            type: "post",
            data: {order : $('.section').size()},
            success: function(msg){
                //append form to DOM and display dialog
                $('#module').append(msg);
                $('#editsection').dialog({
                    title: "New Section",
                    buttons: {
                        "Ok": function() {
                            $(this).find('form').submit();
                        },
                        "Cancel": function() {
                            $(this).dialog("close");
                        }
                    },
                    close: function(){
                        $(this).dialog( "destroy" );
                        $('#editsection').remove();
                    },
                    height: 300,
                    width: 650,
                    resizable: false,
                    modal: true
                });
            }
        });

        //prevent form from submitting traditionaly
        return false;

    });

    $('#editModule').submit(function(){
        //get edit module form
        $.ajax({
            url: "edit_module.php?id=<?php echo $moduleId; ?>",
            dataType: "html",
            success: function(msg){
                //append form to DOM and display and dialog
                $('#module').append(msg);
                $('#editmodule').dialog({
                    title: "Edit Module",
                    buttons: {
                        "Ok": function() {
                            $(this).find('form').submit();
                        },
                        "Cancel": function() {
                            $(this).dialog("close");
                        }
                    },
                    close: function(){
                        $(this).dialog( "destroy" );
                        $('#editmodule').remove();
                    },
                    height: 650,
                    width: 650,
                    resizable: false,
                    modal: true
                });
            }
        });

        //prevent form from submitting traditionaly
        return false;

    });

    $('.section .sectioninfo').find('.edit').click(function(){
        //get edit section form
        $.ajax({
            url: "edit_section.php?id=<?php echo $moduleId; ?>&sid="+$(this).parent().parent().attr('id'),
            dataType: "html",
            success: function(msg){
                //append form to DOM and display and dialog
                $('#module').append(msg);
                $('#editsection').dialog({
                    title: "Edit Section",
                    buttons: {
                        "Ok": function() {
                            $(this).find('form').submit();
                        },
                        "Cancel": function() {
                            $(this).dialog("close");
                        }
                    },
                    close: function(){
                        $(this).dialog( "destroy" );
                        $('#editsection').remove();
                    },
                    height: 300,
                    width: 650,
                    resizable: false,
                    modal: true
                });
            }
        });

        //prevent form from submitting traditionaly
        return false;

    });

</script>