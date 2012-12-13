<?php

function prtools_overview(){
	global $prtools_extended;

				
	update_url_table();
	// update_pr_tools();
	
	if ( isset( $_GET['url_id'] ) && $prtools_extended ) { prtools_url(); } else { 	
		
		global $wpdb;
		global $prtools_url_table;
		global $prtools_pr_table;
		global $prtools_sum_urls;
		global $prtools_sum_urls_query;
		global $prtools_absolute_path;		
		
		$sql = "SELECT count(*) AS count FROM " . $prtools_url_table . " WHERE active='1'";
		$prtools_stat = $wpdb->get_row( $sql );
		
		$prtools_sum_urls = $prtools_stat->count;
		
		$sql = "SELECT * FROM " . $prtools_url_table . " WHERE active='1' ORDER by pr DESC";
		$sql = apply_filters( 'prtools_main_sql', $sql );
		$prtools_rows = $wpdb->get_results( $sql );
		
		$prtools_sum_urls_query=count($prtools_rows);
				
?>

<!-- Head of entry //-->
<div class="tab-head">
  <h2>
    <?php _e('Pageranks','prtools'); ?>
    <?php if ( isset( $_GET['url'] ) && $prtools_extended ) echo ' ' . $_GET['url']; ?>
  </h2>
</div>

<div id="donate">
	<h3>Help us to develope:</h3>
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="hosted_button_id" value="PY6K39JZAQ776">
	<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal — The safer, easier way to pay online.">
	<img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1">
	</form>
</div>


<?php do_action( 'prtools_main_head'); ?>

<!-- Listing pageranks of all sites //-->
<table class="widefat">
<?php 
	
	$tablehead = '<thead><tr>';
	$tablehead.= '<th scope="col">' . __('PR','prtools') . '</th>';
	$tablehead.= '<th scope="col">' . __('URL','prtools') . '</th>';
	$tablehead.= '<th scope="col">&nbsp;</th><th scope="col">' . __('Type','prtools') . '</th>';
	$tablehead.= '<th scope="col">' . __('Update','prtools') . '</th>';
	$tablehead.= '</tr></thead>';
	
	$tablehead=apply_filters( 'prtools_main_tablehead', $tablehead );

	echo $tablehead;
    
    ?>
  	<tbody>
    <?php 
		
		foreach ( $prtools_rows as $row ) {
		
			if($row->lastupdate!=0){
				$row->date=date("d.m.Y",$row->lastupdate);
			}else{
				$row->date="n/a";
			}
			
			if($row->pr==-1){
				$pr="n/a";
			}elseif($row->pr==-2){
				$pr="-";
			}else{
				$pr=$row->pr;
			}
			
			$prtools_row = '<tr>';
			$prtools_row.= '<td scope="row">' . $pr . '</td>';
			$prtools_row.= '<td scope="row"><a href="' . $row->url . '" target="_blank">' . $row->url . '</a></td>';
			$prtools_row.= '<td scope="row">
								<a href="' . $row->url . '" target="_blank">[visit]</a>
								<a href="#" border="0" title="Delete" onclick="question_redirect(\'' . __('Do you really want to delete','prtools') . ' ' . $row->url . ' and all data from Pagerank tools?\', \'' . str_replace( '%7E', '~', $_SERVER['REQUEST_URI']) . '&delete_url=' . $row->url . '\' );">[delete]</a>
							</td>';
			
			$prtools_row.= '<td scope="row">' . $row->object_type . '</td>';
			$prtools_row.= '<td scope="row">' . $row->date . '</td>';
			$prtools_row.= '</tr>';
			
			$prtools_row=apply_filters( 'prtools_main_tablerow' , $prtools_row , $row );
			
			echo $prtools_row;
    	} 
		?>
  </tbody>
</table>
<div style="height:20px"></div>
<?php
	}
	do_action( 'prtools_main_bottom' );
}
?>