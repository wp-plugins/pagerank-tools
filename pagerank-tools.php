<?php
/*
Plugin Name: Pagerank Tools
Plugin URI: http://themekraft.com/plugin/pagerank-tools/
Description: Monitor the Google Pagerank of your Blog URls.
Version: 1.1.3
Author: Sven Wagener
Author URI: http://rheinschmiede.de
*/

/**********************************************************************
This program is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
***********************************************************************/

global $prtools_url_table;
global $prtools_pr_table;
global $prtools_version;
global $prtools_debug;
global $prtools_absolute_path;
global $prtools_absolute_path_absolute;
global $prtools_plugin_path;
global $prtools_titles;
global $prtools_extended;
global $wpdb;

$prtools_debug=false;
$prtools_version="1.1.2";

$prtools_url_table=$wpdb->prefix."prtools_url";
$prtools_pr_table=$wpdb->prefix."prtools_pr";

$prtools_name=__('Pagerank tools','prtools');

define( 'PRTOOLS_FOLDER',  pr_tools_get_folder() );
define( 'PRTOOLS_URLPATH', pr_tools_get_url_path() );

$prtools_absolute_path_absolute=dirname(__FILE__);
$prtools_absolute_path=substr($prtools_absolute_path_absolute,strlen($_SERVER['DOCUMENT_ROOT']),strlen($prtools_absolute_path_absolute)-strlen($_SERVER['DOCUMENT_ROOT']));

$prtools_plugin_path=substr(dirname(__FILE__),strlen($_SERVER['DOCUMENT_ROOT']),strlen(dirname(__FILE__))-strlen($_SERVER['DOCUMENT_ROOT']));
if(substr($prtools_plugin_path,0,1)!="/"){$prtools_plugin_path="/".$prtools_plugin_path;}

include($prtools_absolute_path_absolute.'/lib/io.inc.php');
include($prtools_absolute_path_absolute.'/lib/html.inc.php');
include($prtools_absolute_path_absolute.'/lib/wp_url.inc.php');
include($prtools_absolute_path_absolute.'/res/pagerank.php');
include($prtools_absolute_path_absolute.'/ui/functions_layout.inc.php');

$prtools_extended=false;
if(file_exists($prtools_absolute_path_absolute."/extended.php")){include($prtools_absolute_path_absolute."/extended.php");}

include($prtools_absolute_path_absolute.'/functions.inc.php');
include($prtools_absolute_path_absolute.'/updates.php');

include($prtools_absolute_path_absolute.'/admin/main.php');
include($prtools_absolute_path_absolute.'/admin/pagerank_overview.php');
include($prtools_absolute_path_absolute.'/admin/settings.php');
include($prtools_absolute_path_absolute.'/admin/get_pro.php');

register_activation_hook(__FILE__,'prtools_install');

add_action('admin_head','pr_ajaxui_css');
add_action('admin_head','prtools_css');
add_action('init','pr_ajaxui_js');

add_action('admin_menu','add_prtools');

if( !is_admin() )
	add_action('wp_footer','fetch_pr');

/**
 * Updating Plugin
 */
function update_pr_tools(){
	global $prtools_version;
	
	$installed_version = get_option( 'pr_tools_version' );
	
	if( $installed_version == '' || version_compare( '1.1.2', $installed_version, '>' ) ){
		update_pr_tools_to_1_1_2();
	}
}
add_action( 'wp_loaded', 'update_pr_tools' );

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
	global $prtools_url_table;
	global $prtools_pr_table;	
	
	/**
	 * Installing tables
	 */
	
	if($wpdb->get_var("SHOW TABLES LIKE '".$prtools_url_table."'") != $prtools_url_table) {
	
		$sql = "CREATE TABLE ".$prtools_url_table." (
		ID int(11) NOT NULL AUTO_INCREMENT,
		object_id INT( 11 ) NOT NULL,
		object_type CHAR( 50 ) NOT NULL,
		entrydate int(11) DEFAULT '0' NOT NULL,
		lastupdate int(11) DEFAULT '0' NOT NULL,
		lastcheck int(11) NOT NULL,
		url_type char(50) NOT NULL,
		url VARCHAR(500) NOT NULL,
		title text NOT NULL,
		pr int(11) NOT NULL,
		diff_last_pr int(1) NOT NULL,
		pr_entries int(11) NOT NULL,
		queue INT( 1 ) NOT NULL,
		active INT( 1 ) NOT NULL,
		UNIQUE KEY id (id)
		);";
			
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	
	if($wpdb->get_var("SHOW TABLES LIKE '".$prtools_pr_table."'") != $prtools_pr_table) {
	
		$sql = "CREATE TABLE ".$prtools_pr_table." (
		ID int(11) NOT NULL AUTO_INCREMENT,
		url_id INT( 11 ) NOT NULL,
		entrydate int(11) DEFAULT '0' NOT NULL,
		url VARCHAR(500) NOT NULL,
		pr int(11) NOT NULL,
		UNIQUE KEY id (id)
		);";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	update_url_table();
	add_option("prtools_db_version",'1.0');
	
	/**
	 * Setting standard options
	 */
	if(get_option('pagerank_tools_settings')==""){
		$prtools_settings['fetch_interval']=5;
		$prtools_settings['fetch_url_interval']=120;
		$prtools_settings['fetch_url_interval_new']=14;
		$prtools_settings['fetch_num']=1;
		$prtools_settings['fetch_titles_num']=2;
		$prtools_settings['running_number']=1;
		
		update_option('pagerank_tools_settings',$prtools_settings);	
	}
}
function pr_tools_get_url_path(){
	$sub_path = substr( PRTOOLS_FOLDER, strlen( ABSPATH ), ( strlen( PRTOOLS_FOLDER ) ) );
	$script_url = get_bloginfo( 'wpurl' ) . '/' . $sub_path;
	return $script_url;
}
	
function pr_tools_get_folder(){
	$sub_folder = substr( dirname(__FILE__), strlen( ABSPATH ), ( strlen( dirname(__FILE__) ) - strlen( ABSPATH ) ) );
	$script_folder = ABSPATH . $sub_folder;
	return $script_folder;
}