<?php
/*
 * Cru Doctrine
 * Admin - Page Builder - Information
 * Keith Roehrenbeck | Campus Crusade for Christ
 */

try {

    //initialize pdo object
    $db = new PDO('mysql:host=crudoctrine.db.6550033.hostedresource.com;port=3306;dbname=crudoctrine', 'crudoctrine', 'D6LLd2mxU6Z34i');

    if (!$new){ //get data for existing page

        //get page information
        $db_page = $db->query("SELECT * FROM page WHERE ID = ".$id)->fetchAll(PDO::FETCH_ASSOC);

        //assign section information to array
        $result = $db_page[0];

        //assign values
        $sectionId  = $result['SectionId'];
        $_title     = $result['Title'];
        $order      = $result['Ord'];
        $visibility = $result['Visibility'];

    }

    $db = null;

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
                    <option value="blank"       <?php echo $visibility == ''         ? 'selected' : ''; ?>   >Select Visibility Level</option>
                    <option value="intern"      <?php echo $visibility == 'intern'   ? 'selected' : ''; ?>   >Intern</option>
                    <option value="student"     <?php echo $visibility == 'student'  ? 'selected' : ''; ?>   >Student</option>
                    <option value="other"       <?php echo $visibility == 'other'    ? 'selected' : ''; ?>   >Other</option>
                    <option value="all"         <?php echo $visibility == 'all'      ? 'selected' : ''; ?>   >All</option>
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

    </form>

</div>

<script type="text/javascript">

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