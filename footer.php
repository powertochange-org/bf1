<?php
/*
 * Cru Doctrine
 * Footer
 * Campus Crusade for Christ
 */

  echo  '  <div id="footer">';
  echo  '    <div id="CCC">&copy; '.date("Y").' Campus Crusade for Christ</div>';
  echo  '    <div id="links">
               <a href="http://www.ccci.org/about-us/policies/terms-of-use/index.htm">Terms of Use</a><a href="http://www.ccci.org/about-us/policies/Privacy-policy/index.htm"> | Privacy Policy</a>
             </div>';
  if($loggedin) {
    echo '   <div id="links">
               <a href="/">Home</a><a href="/work"> | My Work</a><!--a href="/"> | Online Community</a><a href="/">| Featured Resources</a-->
             </div>';
  }
  echo  '  </div>
         </div>
         <!--ENDS CONTAINER-->';
?>
<script type="text/javascript">
    $('.ui-state-default').hover(
        function(){
            $(this).addClass("ui-state-hover");
        },
        function(){
            $(this).removeClass("ui-state-hover");
        }
    );
</script>