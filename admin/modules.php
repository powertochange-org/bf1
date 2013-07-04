<?php
/*
 * Cru Doctrine
 * Admin - Modules
 * Campus Crusade for Christ
 */

try {    
  //get modules from db
  $modules = array();	
  
  //initialize the database object
  $db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE); 
  $db->connect();
  
  //execute query and return to module array
  $sql = "SELECT * FROM module ORDER BY Ord";
  //execute query
  $modules = $db->fetch_array($sql);

  $db->close();
}
catch (PDOException $e){
    echo $e->getMessage();
}
?>
<div id="modules">
  <div id="list">
    <form id="createNew" action="?p=modules&request=edit_module" method="post">
        <input type="hidden" class="ui-corner-left" name="order" value="<?php echo count($modules); ?>" />
        <button type="submit" class="ui-corner-right corners-all shadow-light button" value="submit"><span class="ui-icon ui-icon-plus"></span>Add Module</button>
    </form>
    <?php
      if(count($modules) > 0) {
          foreach ($modules as $row) {
              echo '  <div class="module" id="'.$row['ID'].'">
                          <div class="title corners-left">
                              <div class="number">'.$row['Number'].'</div>
                              <div class="name">'.$row['Name'].'</div>
                          </div>
                          <div class="descr">'.$row['Descr'].'</div>
                          <div class="drag ui-icon ui-icon-grip-solid-horizontal"></div>
                          <a class="edit ui-state-default corners-all button" href="?p=modules&id='.$row['ID'].'"><span class="ui-icon ui-icon-pencil"></span>Edit</a>
                      </div>';
          }
      }
    ?>
  </div>
</div>

<script type="text/javascript">
  $('#list').sortable({
      handle: '.drag',
      placeholder: 'ui-state-highlight',
      forcePlaceholderSize: true,
      update: function(event, ui){
          reorder('module', $(this).sortable('toArray'));
      }
  });

  $('#createNew').submit(function() {
    //get new module form
    $.ajax({
      url: "edit_module.php",
      dataType: "html",
      success: function(msg) {
        //append form to DOM and display and dialog
        $('#modules').append(msg);
        $('#editmodule').dialog({
            title: "New Module",
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
</script>