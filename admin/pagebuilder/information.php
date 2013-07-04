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

  if (!$new) {
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
    $pageType   = $result['Type'];
  }

  $db->close();

} 
catch (PDOException $e) {
  echo $e->getMessage();
  exit();
}
?>
<div>
  <form id="formInformation" action="" method="POST">
    <fieldset id="information">
      <div><label>Page Title</label>
        <input id="pageTitle" value="<?php echo $_title; ?>"/><a class="required"></a>
      </div>
      <div>
        <label>Visibility</label>
        <select id="pageVisibility">
          <option value="<?php echo ALL; ?>"                    <?php echo $visibility == ''                     ? 'selected' : ''; ?>   >Select Visibility Level</option>
          <option value="<?php echo STUDENT; ?>"                <?php echo $visibility == STUDENT                ? 'selected' : ''; ?>   >Student</option>
          <option value="<?php echo INTERN; ?>"                 <?php echo $visibility == INTERN                 ? 'selected' : ''; ?>   >Intern</option>
          <!--option value="<?php //echo OTHER; ?>"                  <?php //echo $visibility == OTHER                  ? 'selected' : ''; ?>   >Other</option-->
          <!--option value="<?php //echo PART_TIME_FIELD_STAFF; ?>"  <?php //echo $visibility == PART_TIME_FIELD_STAFF  ? 'selected' : ''; ?>   >Part Time Field Staff</option-->
          <!--option value="<?php //echo VOLUNTEER; ?>"              <?php //echo $visibility == VOLUNTEER              ? 'selected' : ''; ?>   >Volunteer</option-->
          <option value="<?php echo ALL; ?>"                    <?php echo $visibility == ALL                    ? 'selected' : ''; ?>   >All</option>
        </select><a class="required"></a>
      </div>
      <div>
        <label>Page Type</label>
          <select id="pageType">
            <option value="<?php echo NORMAL_PAGE; ?>"      <?php echo $pageType == ''               ? 'selected' : ''; ?>   >Select Page Type</option>
            <option value="<?php echo NORMAL_PAGE; ?>"      <?php echo $pageType == NORMAL_PAGE      ? 'selected' : ''; ?>   >Normal</option>
            <option value="<?php echo ASSESSMENT_PAGE; ?>"  <?php echo $pageType == ASSESSMENT_PAGE  ? 'selected' : ''; ?>   >Assessment</option>
          </select><a class="required"></a>
        </div>
    </fieldset>
    <fieldset id="feedback">
      <div id="errors">
        <?php echo $errors; ?>
      </div>
    </fieldset>
    <input type="hidden" name="p" id="p" value="design" />
    <button id="submit" name="submit" type="submit">PAGE DESIGN</button>
    <button id="cancel" name="cancel" type="submit" onclick="cancelFunc();return(false);">CANCEL</button>
  </form>
</div>

<script type="text/javascript">
  function cancelFunc() {
    window.location.href = "/admin/?p=modules&id=<?php echo $moduleId; ?>";
  }

  //validate form submission
  $('#formInformation').submit(function() {
    var submit = false;
    var errors = '';

    if ($('#formInformation #pageTitle').val().length == 0) {
      $('#formInformation #title').css('border-color', 'orange').siblings('a').css('display','inline-block');
      errors += '<div>Please enter a title for this page.</div>';
    }

    if ($('#formInformation #pageVisibility').val() == 'blank'){
      $('#formInformation #visibility').css('border-color', 'orange').siblings('a').css('display','inline-block');
      errors += '<div>Please select a visibility level.</div>';
    }

    if ($('#formInformation #pageType').val() == 'blank'){
      $('#formInformation #pageType').css('border-color', 'orange').siblings('a').css('display','inline-block');
      errors += '<div>Please select a page type.</div>';
    }

    if (errors !== '') {
      $('#errors').html(errors);
      submit = false;
    } 
    else {
      submit = true;
    }

    return submit;
  });
</script>