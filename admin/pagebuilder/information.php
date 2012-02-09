<?php
/*
 * Cru Doctrine
 * Admin - Page Builder - Information
 * Campus Crusade for Christ
 */

try {

    //initialize the database object
    $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
    $db->connect();

    if (!$new){ //get data for existing page

        //get page information
        $sql = "SELECT * FROM page WHERE ID = ".$id;

        //execute query
        //assign section information to array
        $result = $db->query_first($sql);

        //assign values
        $sectionId  = $result['SectionId'];
        $_title     = $result['Title'];
        $order      = $result['Ord'];
        $visibility = $result['Visibility'];
    }

    $db->close();

} catch (PDOException $e) {
    echo $e->getMessage();
    exit();
}

?>

<div>

    <form action="" method="POST">

        <fieldset id="information">
            <div><label>Page Title</label><input name="title" value="<?php echo $_title; ?>"/><a class="required"></a></div>
            <div>
                <label>Visibility</label>
                <select name="visibility">
                    <option value="Blank"       <?php echo $visibility == ''         ? 'selected' : ''; ?>   >Select Visibility Level</option>
                    <option value="Intern"      <?php echo $visibility == 'Intern'   ? 'selected' : ''; ?>   >Intern</option>
                    <option value="Student"     <?php echo $visibility == 'Student'  ? 'selected' : ''; ?>   >Student</option>
                    <option value="Other"       <?php echo $visibility == 'Other'    ? 'selected' : ''; ?>   >Other</option>
                    <option value="All"         <?php echo $visibility == 'All'      ? 'selected' : ''; ?>   >All</option>
                </select><a class="required"></a>
            </div>
        </fieldset>

        <fieldset id="feedback">

            <div id="errors">
                <?php echo $errors; ?>
            </div>

        </fieldset>

        <input type="hidden" name="p" value="design" />

        <button name="submit" type="submit">PAGE DESIGN</button>
        <button name="cancel" type="submit" onclick="cancelFunc();return(false);">CANCEL</button>

    </form>

</div>

<script type="text/javascript">

    function cancelFunc(){
        window.location.href = "/admin/?p=modules&id=<?php echo $moduleId; ?>";
    }

    //validate form submission
    $('form').submit(function(){
        var submit = false;
        var errors = '';

        if ($('input:[name=title]').val().length == 0) {
            $('input:[name=title]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please enter a title for this page.</div>';
        }

        if ($('select:[name=visibility]').val() == 'blank'){
            $('select:[name=visibility]').css('border-color', 'orange').siblings('a').css('display','inline-block');
            errors += '<div>Please select a visibility level.</div>';
        }

        if (errors !== ''){
           $('#errors').html(errors);
           submit = false;
        } else {
           submit = true;
        }

        return submit;

    });

</script>