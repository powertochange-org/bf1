<?php
/*
 * Cru Doctrine
 * Page Builder - Information
 * Campus Crusade for Christ
 */
?>
<div>
    <div id="selector">
        <div id="left"></div>
        <div id="templates">
          <div id="blank" class="template">Blank</div>
        </div>
        <div id="right"></div>
    </div>

    <form action="?p=design" method="POST">
      <input name="template" value="<?php echo $_POST['template']; ?>" type="hidden"/>
      <button name="submit" type="submit">PAGE DESIGN</button>
    </form>
</div>

<script type="text/javascript">
    $(function() {
        $('#templates').selectable();
    });
</script>

<?php
    if(isset($_POST['template'])) {
      echo '  <script type="text/javascript">
                var page_template      = "'.$_POST['template'].'";
              </script>';
    }
?>