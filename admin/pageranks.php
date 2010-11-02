<?php function page_pageranks(){ ?>
    <!-- Title //-->
  	<h2><b>Pagerank tools</b></h2>

	<!-- Tabs //-->    
    <div id="config-tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
        <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
            <li class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"><a href="#cap_pageranks"><?php _e('Pageranks','prtools'); ?></a></li>
            <li class="ui-state-default ui-corner-top"><a href="#cap_settings"><?php _e ('Settings', 'prtools') ?></a></li>
        </ul>
    
        <!-- Pagerank page //-->
        <div id="cap_pageranks" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
            <?php prtools_history(); ?>
        </div> 
        
        <!-- Settings page //-->
        <div id="cap_settings" class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide">
            <?php prtools_settings(); ?>
        </div>
    </div>
    <script type="text/javascript">
    	jQuery(document).ready(function($){
        	$("#config-tabs").tabs();
         });
	</script>
</div>
<?php } ?>