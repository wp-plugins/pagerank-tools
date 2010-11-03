<?php
function prtools_overview(){
	update_url_table();	
	
	global $wpdb;
	global $prtools_url_table;
	global $prtools_pr_table;
	
	$table_name = $wpdb->prefix . "prtools_url";
	$prtools_rows = apply_filters( 'prtools_overview_get_urls',$wpdb->get_results( "SELECT * FROM ".$table_name." ORDER by pr DESC" ));
				
?>
<!-- Head of entry //-->
<div class="tab-head">
    <h2><?php _e('Pageranks'.$_GET['url'],'prtools'); ?></h2>
</div>

<?php do_action( 'prtools_overview_before_table'); ?> 

<!-- Listing pageranks of all sites //-->
<table class="widefat">
	<?php 
	
	$tablehead = '<thead><tr>';
	$tablehead.= '<th scope="col">' . __('PR','prtools') . '</th>';
	$tablehead.= '<th scope="col">' . __('URL','prtools') . '</th>';
	$tablehead.= '<th scope="col">&nbsp;</th><th scope="col">' . __('Type','prtools') . '</th>';
	$tablehead.= '<th scope="col">' . __('Update','prtools') . '</th>';
	$tablehead.= '</tr></thead>';
	
	
	$tablehead=apply_filters( 'prtools_overview_tablehead', $tablehead );

	echo $tablehead;
    
    ?>
    <tbody>
		<?php 
		
		foreach ( $prtools_rows as $row ) {
		
			if($row->lastupdate!=0){
				$date=date("d.m.Y",$row->lastupdate);
			}else{
				$date="n/a";
			}
			
			if($row->pr==-1){
				$row->pr="n/a";
			}
			
			$prtools_row = '<tr>';
			$prtools_row.= '<td scope="row">' . $row->pr . '</td>';
			$prtools_row.= '<td scope="row"><a href="' . $row->url . '" target="_blank">' . $row->url . '</a></td>';
			// $prtools_row.= '<td scope="row"><a href="' . str_replace( '%7E', '~', $_SERVER['REQUEST_URI']) . '&url=' . $row->url . '">[history]</a><a href="' . $row->url . '" target="_blank">[visit]</a></td>';
			$prtools_row.= '<td scope="row"><a href="' . $row->url . '" target="_blank">[visit]</a></td>';
			$prtools_row.= '<td scope="row">' . $row->url_type . '</td>';
			$prtools_row.= '<td scope="row">' . $date . '</td>';
			$prtools_row.= '</tr>';
			
			$prtools_row=apply_filters( 'prtools_overview_tablerow' , $prtools_row , $row );
			
			echo $prtools_row;
    	} 
		
		?>
	</tbody>
</table>
<?php   
	do_action( 'prtools_overview_after_table' );
}
?>