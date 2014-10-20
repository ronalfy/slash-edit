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
	private $endpoint = 'edit';
	
	//Singleton
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	} //end get_instance
	
	private function __construct() {
		add_action( 'init', array( $this, 'init' ), 20 );
		add_action( 'template_redirect', array( $this, 'maybe_redirect' ) );
		add_filter( 'rewrite_rules_array', array( $this, 'add_rewrite_rules' ) );
		$this->endpoint = sanitize_title( apply_filters( 'slash_edit_endpoint', 'edit' ) );	
	} //end constructor
	
	public static function activate() {
		update_option( 'slash_edit_install', 'true' );
	}
	
	public function add_rewrite_rules( $rules ) {
		//Get taxonomies
		$taxonomies = get_taxonomies();
		$blog_prefix = '';
		if ( is_multisite() && !is_subdomain_install() && is_main_site() ) { /* stolen from /wp-admin/options-permalink.php */
			$blog_prefix = 'blog/';
		}
		$exclude = array(
			'category',
			'post_tag',
			'nav_menu',
			'link_category',
			'post_format'
		);
		foreach( $taxonomies as $key => $taxonomy ) {
			if ( in_array( $key, $exclude ) ) continue;
			$rules[ "{$blog_prefix}{$key}/([^/]+)/{$this->endpoint}(/(.*))?/?$" ] = 'index.php?' . $key . '=$matches[1]&' . $this->endpoint . '=$matches[3]';
		}	
		return $rules;	
	}
	public static function deactivate() {
	}
	
	public function init() {
		global $wp_rewrite;

		//Delete rewrite rules if plugin is deactivated
		if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'deactivate' ) {
			$plugin_basename = plugin_basename( __FILE__ );
			
			//Let's see if we're being deactivated
			if ( $_GET[ 'plugin' ] === $plugin_basename ) {
				if ( wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 	'deactivate-plugin_' . $plugin_basename ) ) {
						add_rewrite_endpoint( $this->endpoint, EP_NONE );
						flush_rewrite_rules( false );	
				}
			}
		}
		
		//Refresh rewrite rules if plugin is activated
		add_rewrite_endpoint( $this->endpoint, EP_PERMALINK | EP_PAGES | EP_CATEGORIES | EP_TAGS | EP_AUTHORS ); //todo - adding EP_ATTACHMENT messes up EP_PERMALINK and EP_PAGES
		if ( get_option( 'slash_edit_install', 'false' ) == 'true' ) {		
			flush_rewrite_rules( false );
			delete_option( 'slash_edit_install' );
		}
	} //end init
	
	public function maybe_redirect() {
		global $wp_query;

		if ( !isset( $wp_query->query_vars[ $this->endpoint ] ) ) return;		
		
		$edit_url = false;
		if ( is_attachment() || is_single() || is_page() ) { /* Post, page, attachment, or CPTs */
			//Get the post, page, or cpt id
			$post = get_queried_object();
			$post_id = isset( $post->ID ) ? $post->ID : false;
			if ( $post_id === false ) return;
			
			//Build the url
			$edit_url = esc_url_raw( add_query_arg( array( 'post' => absint( $post_id ), 'action' => 'edit' ), admin_url( 'post.php' ) ) );
		} elseif ( is_author() ) { /* Author Page */
			$user_data = get_queried_object();
			if ( is_a( $user_data, 'WP_User' ) ) {
				$user_id = $user_data->ID;
				//Build the url
				$edit_url = esc_url_raw( add_query_arg( array( 'user_id' => absint( $user_id ), 'action' => 'edit' ), admin_url( 'user-edit.php' ) ) );
			} 
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$tax_data = get_queried_object();
			if ( is_object( $tax_data ) && isset( $tax_data->term_id ) ) {
				$term_id = $tax_data->term_id;
				$taxonomy = $tax_data->taxonomy;
				//Build the url
				$edit_url = esc_url_raw( add_query_arg( array( 'tag_ID' => absint( $term_id ), 'taxonomy' => $taxonomy, 'action' => 'edit', 'post_type' => get_post_type() ), admin_url( 'edit-tags.php' ) ) );
			}
		}
		
		//Return if nothing to redirect to
		if ( $edit_url === false ) return;		
		
		
		//Redirect yo
		wp_safe_redirect( $edit_url );
		exit;
	}
	
} //end class Slash_Edit

add_action( 'plugins_loaded', 'slash_edit_instantiate' );
function slash_edit_instantiate() {
	Slash_Edit::get_instance();
} //end slash_edit_instantiate

register_activation_hook( __FILE__, array( 'Slash_Edit', 'activate' ) );
register_activation_hook( __FILE__, array( 'Slash_Edit', 'deactivate' ) );

add_filter( 'slash_edit_endpoint', 'rjh_slash_edit_endpoint' );
function rjh_slash_edit_endpoint( $endpoint ) {
	return 'edición';
}