<?php 
// Templates
function alert($msg){
	echo "<div class=\"updated\"><p>".$msg."</p></div>";
}
if(!function_exists('ajaxui_js')){
	function ajaxui_js(){
		
		if( ! isset( $_GET['page'] ) ) 
			return;

		if( $_GET['page'] == 'page_pageranks' ) {
			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery-ui-tabs');
		}
	}
}
if(!function_exists('ajaxui_css')){
	function ajaxui_css()
	{
		 if( $_GET['page'] == 'page_pageranks' ) {
			 echo '<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.0/themes/base/jquery-ui.css" rel="stylesheet" />';
		 }	
	}
}
function prtools_css(){
	echo "<link rel=\"stylesheet\" href=\"".get_option('siteurl')."/wp-content/plugins/pagerank-tools/ui/styles.css\" type=\"text/css\" media=\"screen\" />";
}
?>