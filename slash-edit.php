<?php
/*
Plugin Name: Slash Edit
Plugin URI: http://wordpress.org/extend/plugins/slash-edit/
Description: Edit your posts or pages with a simple "/edit" at the end
Author: ronalfy
Version: 1.0
Requires at least: 3.9.1
Author URI: http://www.ronalfy.com
Contributors: ronalfy
*/ 
class Slash_Edit {
	private static $instance = null;
	
	//Singleton
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	} //end get_instance
	
	private function __construct() {
		add_action( 'init', array( $this, 'init' ), 9 );
		add_action( 'template_redirect', array( $this, 'maybe_redirect' ) );
		
	} //end constructor
	
	public static function activate() {
		update_option( 'slash_edit_install', 'true' );
	}
	public static function deactivate() {
	}
	
	public function init() {
		//Delete rewrite rules if plugin is deactivated
		if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'deactivate' ) {
			$plugin_basename = plugin_basename( __FILE__ );
			
			//Let's see if we're being deactivated
			if ( $_GET[ 'plugin' ] === $plugin_basename ) {
				if ( wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 	'deactivate-plugin_' . $plugin_basename ) ) {
						add_rewrite_endpoint( 'edit', EP_NONE );
						flush_rewrite_rules( false );	
				}
			}
		}
		
		//Refresh rewrite rules if plugin is activated
		add_rewrite_endpoint( 'edit', EP_PERMALINK | EP_PAGES );
		if ( get_option( 'slash_edit_install', 'false' ) == 'true' ) {
			flush_rewrite_rules( false );
			delete_option( 'slash_edit_install' );
		}
	} //end init
	
	public function maybe_redirect() {
		global $wp_query;
		if ( !isset( $wp_query->query_vars[ 'edit' ] ) ) return;
		
		//Get the post, page, or cpt id
		$post = get_queried_object();
		$post_id = isset( $post->ID ) ? $post->ID : false;
		if ( $post_id === false ) return;
		
		//Build the url
		$edit_url = esc_url_raw( add_query_arg( array( 'post' => absint( $post_id ), 'action' => 'edit' ), admin_url( 'post.php' ) ) );
		
		//Redirect yo
		wp_safe_redirect( $edit_url );
		exit;
	}
	
} //end class Slash_Edit

add_action( 'plugins_loaded', 'slash_edit_instantiate' );
function slash_edit_instantiate() {
	Slash_Edit::get_instance();
} //end sce_instantiate

register_activation_hook( __FILE__, array( 'Slash_Edit', 'activate' ) );
register_activation_hook( __FILE__, array( 'Slash_Edit', 'deactivate' ) );