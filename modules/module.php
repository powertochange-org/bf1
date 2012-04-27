<?php
/*
 * Cru Doctrine
 * Modules - Module Home
 * Campus Crusade for Christ
 */
?>
<div id="module<?php echo str_replace('.', '', $module['Number']); ?>" class="module">
    <div id="background_img">
        <img src="<?php echo '../'.$module['FrontImg']; ?>"</img>
    </div>
    <div id="continue">
        <a href="?s=<?php echo $module['FirstSection']; ?>" class="ui-state-default ui-corner-all shadow-medium">Continue<span class="ui-icon ui-icon-circle-triangle-e"></span></a>
    </div>
    <div id="title">
        <div id="number">Module <?php echo $module['Number']; ?></div>
        <div id="name"><?php echo $module['Name']; ?></div>
    </div>
    <div id="description">
        <?php echo $module['Descr']; ?>
    </div>
</div>