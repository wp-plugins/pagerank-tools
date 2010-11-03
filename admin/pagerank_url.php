<?php
function prtools_url(){
	update_url_table();	
	
	global $wpdb;
	global $prtools_url_table;
	global $prtools_pr_table;
	
	$prtools_rows = $wpdb->get_results( "SELECT * FROM ".$prtools_pr_table." WHERE url='". $_GET['url']."' ORDER BY ".$prtools_pr_table.".entrydate DESC");

?>

<!-- Head of entry //-->
<div class="tab-head">
	<h2><?php _e('Pagerank History of','prtools'); ?> <?php echo $_GET['url']; ?></h2>
</div>

<div class="spacer"></div>

<div class="tab-menue">
	<input class="button-secondary action" type="button" value="Back to overview" onClick="history.back(-1);" />
</div>

<!-- Listing pagerank  history of site //-->
<table class="widefat">
	<thead>
		<tr><th scope='col'><?php _e('PR','prtools'); ?></th><th scope='col'><?php _e('URL','prtools'); ?></a></th><th scope='col'>&nbsp;</a></th><th scope='col'><?php _e('Type','prtools'); ?></th><th scope='col'><?php _e('Date','prtools'); ?></th></tr>
	</thead>
     
    <tbody>
    	<?php $i=0; ?>
		<?php foreach ( $prtools_rows as $row ) { ?>
        <?php $i++; ?>
        <tr>
            <td scope='row'><?php echo $row->pr; ?></td>
            <td scope='row'><a href="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>&url=<?php echo $row->url; ?>"><?php echo $row->url; ?></a></td>
            <td scope='row'>
             	<a href="<?php echo $row->url; ?>" target="_blank">[visit]</a>
            </td>
            <td scope='row'><?php echo $row->url_type; ?></td>
            <td scope='row'><?php echo date("d.m.Y",$row->entrydate); ?></td>
        </tr>
        <?php  } ?>
        <?php  if($i==0){ ?>
        <tr>
            <td scope='row' colspan='5'>There could be no pagerank data fetched yet.</td>
        </tr>
        <?php  } ?>
    </tbody>

</table>

<?php 
}
?>