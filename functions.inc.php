<?php

function fetch_pr(){
	global $wpdb;
	global $prtools_debug;
	global $prtools_url_table;
	global $prtools_pr_table;
	
	$prtools_settings = get_option( 'pagerank_tools_settings' );

	$lastcheck = time() - $prtools_settings['fetch_interval'] * 60; // Point of time when last pr check have to be done
	$lastcheck_url = time() - $prtools_settings['fetch_url_interval'] * 24 * 60 * 60; // Point of time when last pr check of urls have to be done
	$lastcheck_url_new = time() - $prtools_settings['fetch_url_interval_new'] * 24 * 60 * 60; // Point of time when last pr check of new urls have to be done
	
	// Getting urls which are updatable by settings
	$sql = "SELECT * FROM " . $prtools_url_table . " 
			
			WHERE 
				(
					(
						( 
							lastcheck<" . $lastcheck_url . " AND pr<>-2 
						) OR ( 
							lastcheck<" . $lastcheck_url_new . " AND pr=-2 
						) 
					)
					AND lastcheck<" . $lastcheck . "
				)
				
				OR queue=1
			
			ORDER BY lastcheck ASC LIMIT 0," . $prtools_settings[ 'fetch_num' ];	
			
	$url_rows = $wpdb->get_results( $sql );
	
	/*
	* Checking pageranks
	***************************************/
	foreach( $url_rows AS $url_row ){
		// If PR is a new rank update and insert
		$pr = getpagerank( $url_row->url );
		pr_update_url_db_entries( $url_row->url, $pr );
		$prtools_settings[ 'last_google_request' ] = time();
	}
	
	update_option( 'pagerank_tools_settings', $prtools_settings );			
}

function fetch_pr_sidewide(){
	global $wpdb;
	global $prtools_debug;
	global $prtools_url_table;
	global $prtools_pr_table;

	// Getting sites which are updatable by settings
	$sql="SELECT * FROM ".$prtools_url_table;
	$url_rows=$wpdb->get_results($sql);
	
	/*
	* Checking pageranks
	***************************************/	
	foreach( $url_rows AS $url_row ){
		$pr = getpagerank( $url_row->url );
		pr_update_url_db_entries( $url_row->url, $pr );
	}
}

function pr_update_url_db_entries( $url, $pr ){
	global $wpdb, $prtools_url_table, $prtools_pr_table;
	
	$url_row = $wpdb->get_row( $wpdb->prepare( 'SELECT pr, pr_entries FROM ' .  $prtools_url_table . ' WHERE url="' . $url . '"', NULL ) );
	
	// N/A
	if( $pr == "" )
		$pr = -1; 
	
	// If PR is a new rank update and/or insert
	if( $pr != $url_row->pr ){
		
			$diff_last_pr = $pr - $url_row->pr;
			$url_row->pr_entries++;
			
			$wpdb->update( $prtools_url_table, array( 'lastupdate' => time(), 'queue' => 0, 'lastcheck' => time(), 'pr' => $pr, 'diff_last_pr' => $diff_last_pr, 'pr_entries' => $url_row->pr_entries ), array( 'url' => $url ) );
			
			if( -2 == $url_row->pr ) // If Value was new
				$wpdb->update( $prtools_pr_table, array( 'entrydate'=>time(), 'url' => $url, 'pr' => $pr ) , array( 'url' => $url ) );
			else
				$wpdb->insert( $prtools_pr_table, array( 'entrydate'=>time(), 'url' => $url, 'pr' => $pr ) );
	}else{
		$wpdb->update( $prtools_url_table, array( 'lastcheck' => time(), 'queue' => 0 ), array( 'url' => $url ) );
	}
}

function get_blog_urls(){
	$urls = array();
	
	$urls= array_merge( $urls, wp_get_post_urls() );
	$urls= array_merge( $urls, wp_get_page_urls() );
	$urls= array_merge( $urls, wp_get_cat_urls() );
	$urls= array_merge( $urls, wp_get_tag_urls() );	
	$urls[]=get_bloginfo("url").'/';
	
	return $urls;
}

function update_url_table( $update_posts = TRUE, $update_pages = TRUE ){
	global $wpdb;
	global $prtools_url_table;
	global $prtools_pr_table;
	
	if( $update_posts ){
		$posts=get_posts("numberposts=-1&post_status='publish'&post_type=post");
		
		foreach($posts as $post){
			pr_update_url( get_permalink( $post->ID ), $post->ID, 'post' );
		}
	}
	
	if( $update_pages ){
		$posts=get_posts("numberposts=-1&post_status='publish'&post_type=page");
		
		foreach($posts as $post){
			pr_update_url( get_permalink( $post->ID ), $post->ID, 'page' );
		}
	}
	
	$categories = get_categories();
	foreach( $categories AS $category ){
		 pr_update_url( get_category_link( $category->term_id ), $category->term_id, 'post_category' ); 
	}
	
	$tags = get_tags();
	foreach( $tags AS $tag){
		pr_update_url( get_tag_link( $tag->term_id ), $tag->term_id, 'post_tag' ) ;
	}
	
	$home = get_bloginfo( 'url' ) . '/';
	pr_update_url($home, 0, 'home' );
}

function pr_update_url( $url, $object_id = '' , $object_type = '' ){
	global $wpdb;
	global $prtools_url_table;
	global $prtools_pr_table;
	
	$prtools_row = $wpdb->get_row( "SELECT * FROM " . $prtools_url_table . " WHERE url='" . $url . "'" );
	
	// If URL is not in table
	if( count( $prtools_row ) == 0 ){
		$sql = "SELECT * FROM " . $prtools_url_table . " WHERE object_id='" . $object_id . "' AND object_type='" . $object_type . "' AND active=1";
		$prtools_row = $wpdb->get_row( $sql );
		
		// If there was found no entry for object_id and object_type
		if( count( $prtools_row ) == 0 ){
			
			$wpdb->insert( $prtools_url_table, array( 'entrydate' => time(), 'lastupdate' => time(), 'object_id' => $object_id, 'object_type' => $object_type, 'url' => $url, 'pr' => -2, 'active' => 1 ) );
			$url_id = $wpdb->insert_id;
			
			$wpdb->insert( $prtools_pr_table, array( 'url_id' => $url_id, 'entrydate' => time(), 'url'=>$url, 'pr'=>-2 ) );
			
		// If entry was found but no url
		}else{
			$wpdb->update( $prtools_url_table, array( 'lastupdate' => time(), 'url' => $url, 'pr' => -2 ), array( 'ID' => $prtools_row->ID ) );
			$wpdb->insert( $prtools_pr_table, array( 'url_id' => $prtools_row->ID, 'entrydate' => time(), 'url'=>$url, 'pr'=> -2 ) );
		}
		
	// If URL exists
	}else{
		// Old entries will get Object ID and Type
		if( $prtools->object_id == 0 && $object_id != '' ) $wpdb->query( 'UPDATE ' . $prtools_url_table . ' SET object_id="' . $object_id . '" WHERE ID="' . $prtools_row->ID . '"');
		if( $prtools->object_type == '' && $object_type != '' ) $wpdb->query( 'UPDATE ' . $prtools_url_table . ' SET object_type="' . $object_type . '" WHERE ID="' . $prtools_row->ID . '"');
	}
}

function pr_save_settings(){
	if(isset($_POST['savesettings'])){
		if($_POST['savesettings']!=""){
			
			$prtools_settings=get_option('pagerank_tools_settings');
		
			$prtools_settings['fetch_interval']=$_POST['fetch_interval'];
			$prtools_settings['fetch_url_interval']=$_POST['fetch_url_interval'];
			$prtools_settings['fetch_url_interval_new']=$_POST['fetch_url_interval_new'];
			$prtools_settings['fetch_num']=$_POST['fetch_num'];
		
			update_option('pagerank_tools_settings',$prtools_settings);	
			
			do_action( 'prtools_settings_save');
		}
	}
}
add_action( 'admin_init', 'pr_save_settings' , 10); 

function pr_delete_url(){
	global $wpdb;
	global $prtools_url_table;
	global $prtools_pr_table;
		
	if(isset($_GET['delete_url'])){
		if($_GET['delete_url']!=""){
			$sql = 'DELETE FROM ' . $prtools_url_table . ' WHERE url="' . $_GET['delete_url'] . '"';
			$wpdb->query( $sql );
			
			$sql = 'DELETE FROM ' . $prtools_pr_table . ' WHERE url="' . $_GET['delete_url'] . '"';
			$wpdb->query( $sql );									
		}
	}
}
add_action( 'admin_init', 'pr_delete_url' , 10); 

if(!$prtools_extended){
	function pr_get_pro_stats(){
		global $prtools_plugin_path;
		echo '<div class="statistics">
				<div class="diagram" style="margin:0.83em 0;">
					<a class="get_pro" href="#" title="Get professional version of pagerank tools"><img src="' . $prtools_plugin_path . '/images/statistics_get_pro.jpg" alt="Pagerank overview" border="0"></a>
			</div>
		</div>';
	
	}
	add_action( 'prtools_main_head', 'pr_get_pro_stats' , 10, 0);
	
	function pr_get_pro_footer(){
		echo '<a class="get_pro" href="#" title="Get professional version of pagerank tools"><img src="' . $prtools_plugin_path . '/images/footer_get_pro.jpg" alt="Pagerank overview" border="0"></a>';
	}
	add_action( 'prtools_main_bottom', 'pr_get_pro_footer' , 10, 0);
	
	 function pr_get_pro_email(){
		echo '<br /><br /><a class="get_pro" href="#" title="Get professional version of pagerank tools"><img src="' . $prtools_plugin_path . '/images/email_get_pro.jpg" /></a>';
		
	}	
	add_action( 'prtools_settings_page', 'pr_get_pro_email' , 10, 0); 
}


?>