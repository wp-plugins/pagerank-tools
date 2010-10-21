<?php
/*
Plugin Name: Pagerank tools
Plugin URI: http://www.rheinschmiede.de
Description: View and monitor pagerank of your wordpress sites. 
Version: 0.11
Author: Sven Lehnert, Sven Wagener
Author URI: http://www.rheinschmiede.de
*/

/**********************************************************************
This program is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
***********************************************************************/

include('lib/wp_url.inc.php');
include('res/pagerank.php');

include('functions.inc.php');

include('admin/dashboard.php');
include('admin/pageranks.php');
include('admin/pageranks_listing.inc.php');
include('admin/settings.inc.php');

register_activation_hook(__FILE__,'prtools_install');

add_action('admin_menu','add_prtools');
add_action('wp_footer','fetch_pr');

$prtools_url_table=$wpdb->prefix."prtools_url";
$prtools_pr_table=$wpdb->prefix."prtools_pr";

global $prtools_url_table;
global $prtools_pr_table;

/**
 * PR fetcher menue
 */
function add_prtools() {
 		// add_menu_page(__('PR'),__('PR Tools'), 'administrator', 'menueprtools', 'prtools_dashbord');
		add_submenu_page( 'tools.php', __( 'Pagerank tools', 'prtools'),__( 'Pagerank tools', 'prtools' ), 'administrator', 'page_pageranks', 'page_pageranks' );
}

/**
 * PR fetcher installation
 */
function prtools_install() {
	global $wpdb;
	
	/**
	 * Installing tables
	 */
	$prtools_url_table=$wpdb->prefix."prtools_url";
	$prtools_pr_table=$wpdb->prefix."prtools_pr";
	
	if($wpdb->get_var("SHOW TABLES LIKE '".$prtools_url_table."'") != $prtools_url_table) {
	
		$sql = "CREATE TABLE ".$prtools_url_table." (
		ID int(11) NOT NULL AUTO_INCREMENT,
		entrydate int(11) DEFAULT '0' NOT NULL,
		lastupdate int(11) DEFAULT '0' NOT NULL,
		url_type char(50) NOT NULL,
		url VARCHAR(500) NOT NULL,
		pr int(11) NOT NULL,
		UNIQUE KEY id (id)
		);";
			
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	
	if($wpdb->get_var("SHOW TABLES LIKE '".$prtools_pr_table."'") != $prtools_pr_table) {
	
		$sql = "CREATE TABLE ".$prtools_pr_table." (
		ID int(11) NOT NULL AUTO_INCREMENT,
		entrydate int(11) DEFAULT '0' NOT NULL,
		url VARCHAR(500) NOT NULL,
		pr int(11) NOT NULL,
		UNIQUE KEY id (id)
		);";
		
		echo $sql;
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	update_url_table();
	add_option("prtools_db_version",'1.0');
	
	/**
	 * Setting standard options
	 */
	if(get_option('pagerank_tools_settings')==""){
		$prtools_settings['fetch_interval']=10;
		$prtools_settings['fetch_url_interval']=30;
		$prtools_settings['fetch_url_interval_new']=5;
		$prtools_settings['fetch_num']=5;
		
		update_option('pagerank_tools_settings',$prtools_settings);	
	}
}

?>