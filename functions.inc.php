<?php

function fetch_pr(){
	global $wpdb;
	global $prtools_url_table;
	global $prtools_pr_table;
	
	$prtools_settings=get_option('pagerank_tools_settings');
	
	$lastupdate=time()-$prtools_settings['fetch_interval']*60;
	$lastupdate_url=time()-$prtools_settings['fetch_url_interval']*24*60*60;
	$lastupdate_url_new=time()-$prtools_settings['fetch_url_interval_new']*24*60*60;

	
	$sql="SELECT * FROM ".$prtools_url_table." ORDER BY lastupdate DESC LIMIT 0,1";
	
	$prtools_last = $wpdb->get_row($sql);
	
	/*
	echo "LU: ".$prtools_last->lastupdate."<br>";
	$s=time()-$prtools_last->lastupdate;
	echo "S: ".$s."<br>";
	echo "T: ".time()."<br>";
	*/
	
	/*
	* Checking time of last pr request
	***************************************/
	if($prtools_last->lastupdate<$lastupdate){
		
		// Getting sites which are updatable by settings
		$sql="SELECT * FROM ".$prtools_url_table." WHERE lastupdate<".$lastupdate_url." OR (lastupdate<".$lastupdate_url_new." AND pr=-1) ORDER BY lastupdate LIMIT 0,".$prtools_settings['fetch_num'];
		
		$prtools_rows=$wpdb->get_results($sql);
		
		/*
		* Checking pageranks
		***************************************/	
		foreach($prtools_rows AS $prtools_row){
			$pr=getpagerank($prtools_row->url);
			if($pr==""){$pr=-1;}
			
			// echo "PR: ".$pr." (".$prtools_row->url.")<br>";
			
			$wpdb->update($prtools_url_table, array('lastupdate'=>time(),'pr'=>$pr), array('url'=>$prtools_row->url));
			if($pr>-1){
				$wpdb->insert($prtools_pr_table,array('entrydate'=>time(),'url'=>$prtools_row->url,'pr'=>$pr));
			}		
		}
	}

}

function update_url_table(){
	global $wpdb;
	global $prtools_url_table;
	
	$urls=wp_get_post_urls();
	
	foreach($urls AS $url){
		$prtools_rows = $wpdb->get_results( "SELECT * FROM ".$prtools_url_table." WHERE url='".$url."'");
		if(count($prtools_rows)==0){
			$wpdb->insert($prtools_url_table,array('entrydate'=>time(),'url_type'=>'post','url'=>$url,'pr'=>-1));
		}
	}	
	
	$urls=wp_get_page_urls();
	
	foreach($urls AS $url){
		$prtools_rows = $wpdb->get_results( "SELECT * FROM ".$prtools_url_table." WHERE url='".$url."'");
		if(count($prtools_rows)==0){
			$wpdb->insert($prtools_url_table,array('entrydate'=>time(),'url_type'=>'page','url'=>$url,'pr'=>-1));
		}
	}
	
	$urls=wp_get_cat_urls();
	
	foreach($urls AS $url){
		$prtools_rows = $wpdb->get_results( "SELECT * FROM ".$prtools_url_table." WHERE url='".$url."'");
		if(count($prtools_rows)==0){
			$wpdb->insert($prtools_url_table,array('entrydate'=>time(),'url_type'=>'category','url'=>$url,'pr'=>-1));
		}
	}
	
	$urls=wp_get_tag_urls();
	
	foreach($urls AS $url){
		$prtools_rows = $wpdb->get_results( "SELECT * FROM ".$prtools_url_table." WHERE url='".$url."'");
		if(count($prtools_rows)==0){
			$wpdb->insert($prtools_url_table,array('entrydate'=>time(),'url_type'=>'tag','url'=>$url,'pr'=>-1));
		}
	}
	
	$home=get_bloginfo("url").'/';
	$prtools_rows = $wpdb->get_results( "SELECT * FROM ".$prtools_url_table." WHERE url='".$home."'");
	if(count($prtools_rows)==0){
		$wpdb->insert($prtools_url_table,array('entrydate'=>time(),'url_type'=>'home','url'=>$home,'pr'=>-1));
	}
}

?>