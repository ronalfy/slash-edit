<?php
/*
Plugin Name: Slash Edit
Plugin URI: http://wordpress.org/extend/plugins/slash-edit/
Description: Edit your posts or pages with a simple "/edit" at the end
Author: ronalfy
Version: 1.1.1
Requires at least: 3.9.1
Author URI: http://www.ronalfy.com
Contributors: ronalfy
*/ 
class Slash_Edit {
	private static $instance = null;
	private $endpoint = 'edit';
	private $last_rewrite_version_update = '1.1.0'; //Will increment any time I need to change rewrite rules
	
	//Singleton
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	} //end get_instance
	
	private function __construct() {
		add_action( 'init', array( $this, 'init' ), 20 );
		add_action( 'template_redirect', array( 'Slash_Edit', 'maybe_redirect' ) );
		add_filter( 'rewrite_rules_array', array( 'Slash_Edit', 'add_rewrite_rules' ) );
		add_action( 'save_post', array( 'Slash_Edit', 'save_post' ) );
		$this->endpoint = sanitize_title( apply_filters( 'slash_edit_endpoint', 'edit' ) );
		
	} //end constructor
	
	public function get_endpoint() {
		return $this->endpoint;	
	}
	
	public static function activate() {
		update_option( 'slash_edit_install', 'true' );
	}
	
	public static function add_rewrite_rules( $rules ) {
		//Get taxonomies
		$taxonomies = get_taxonomies();
		$blog_prefix = '';
		$endpoint = Slash_Edit::get_instance()->get_endpoint();
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
			$rules[ "{$blog_prefix}{$key}/([^/]+)/{$endpoint}(/(.*))?/?$" ] = 'index.php?' . $key . '=$matches[1]&' . $endpoint . '=$matches[3]';
		}
		//Add home_url/edit to rewrites
		$add_frontpage_edit_rules = false;
		if ( !get_page_by_path( $endpoint ) ) {
			$add_frontpage_edit_rules = true;
		} else {
			$page = get_page_by_path( $endpoint );
			if ( is_a( $page, 'WP_Post' ) && $page->post_status != 'publish' ) {
				$add_frontpage_edit_rules = true;
			} 
		}
		if ( $add_frontpage_edit_rules ) {
			$edit_array_rule = array( "{$endpoint}/?$" => 'index.php?' . $endpoint . '=frontpage' );
			$rules = $edit_array_rule + $rules;
		}	
		return $rules;	
	}
	public static function deactivate() {
	}
	
	public  function init() {
		global $wp_rewrite;
		$endpoint = Slash_Edit::get_instance()->get_endpoint();
		
		//Determine if we need to flush rules for a new version of the plugin
		$version = get_option( 'slash_edit_version', '1.0.0' );
		if ( version_compare( $this->last_rewrite_version_update, $version, 'gt' ) ) {
			update_option( 'slash_edit_version', $this->last_rewrite_version_update );
			flush_rewrite_rules( false );	
		}
		
		//Delete rewrite rules if plugin is deactivated
		if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'deactivate' ) {
			$plugin_basename = plugin_basename( __FILE__ );
						
			//Let's see if we're being deactivated
			if ( $_GET[ 'plugin' ] === $plugin_basename ) {
				if ( wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 	'deactivate-plugin_' . $plugin_basename ) ) {
						add_rewrite_endpoint( $endpoint, EP_NONE );
						flush_rewrite_rules( false );	
				}
			}
		}
				
		//Refresh rewrite rules if plugin is activated
		add_rewrite_endpoint( $endpoint, EP_PERMALINK | EP_PAGES | EP_CATEGORIES | EP_TAGS | EP_AUTHORS ); //todo - adding EP_ATTACHMENT messes up EP_PERMALINK and EP_PAGES
		if ( get_option( 'slash_edit_install', 'false' ) == 'true' ) {		
			flush_rewrite_rules( false );
			delete_option( 'slash_edit_install' );
		}
	} //end init
	
	public static function maybe_redirect() {
		global $wp_query;
		$endpoint = Slash_Edit::get_instance()->get_endpoint();
		if ( !isset( $wp_query->query_vars[ $endpoint ] ) ) return;	
		
		$edit_url = false;
		if ( is_attachment() || is_single() || is_page() ) { /* Post, page, attachment, or CPTs */
			//Get the post, page, or cpt id
			$post = get_queried_object();
			$post_id = isset( $post->ID ) ? $post->ID : false;
			if ( $post_id === false ) return;
			
			//Build the url
			$edit_url = add_query_arg( array( 'post' => absint( $post_id ), 'action' => 'edit' ), admin_url( 'post.php' ) );
		} elseif ( is_author() ) { /* Author Page */
			$user_data = get_queried_object();
			if ( is_a( $user_data, 'WP_User' ) ) {
				$user_id = $user_data->ID;
				//Build the url
				$edit_url = add_query_arg( array( 'user_id' => absint( $user_id ), 'action' => 'edit' ), admin_url( 'user-edit.php' ) );
			} 
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$tax_data = get_queried_object();
			if ( is_object( $tax_data ) && isset( $tax_data->term_id ) ) {
				$term_id = $tax_data->term_id;
				$taxonomy = $tax_data->taxonomy;
				//Build the url
				$edit_url = add_query_arg( array( 'tag_ID' => absint( $term_id ), 'taxonomy' => $taxonomy, 'action' => 'edit', 'post_type' => get_post_type() ), admin_url( 'edit-tags.php' ) );
			}
		} 
		//Fail safe for home_url/edit/
		if ( $edit_url === false &&  'page' == get_option( 'show_on_front') && get_option( 'page_on_front' ) && 'frontpage' == get_query_var( 'edit' ) ) {
			//Build the url
			$edit_url = add_query_arg( array( 'post' => get_option( 'page_on_front' ), 'action' => 'edit' ), admin_url( 'post.php' ) );
		} elseif ( 'frontpage' == get_query_var( 'edit' ) ) {
			//No front page set - so redirect back to homepage
			$edit_url = home_url();	
		}
		
		//Filter to rule them all
		$edit_url = apply_filters( 'slash_edit_url', $edit_url ); //Return false for no redirect
		
		//Return if nothing to redirect to
		if ( $edit_url === false ) return;		
		
		
		//Redirect yo
		wp_safe_redirect( esc_url_raw( $edit_url ) );
		exit;
	}
	
	//Update rewrite rules if a parent page with slug 'edit' is edited and/or update - This way if there is a page with path www.domain.com/edit/, the page has priority
	public static function save_post( $post_id = 0 ) {
		$endpoint = Slash_Edit::get_instance()->get_endpoint();
		if ( wp_is_post_revision( $post_id ) )
			return;
		global $post;
		if ( !is_object( $post ) )
			$post = get_post( $post_id );
		if( $post->post_parent == 0 && $post->post_name == $endpoint  && $post->post_type == 'page' ) {
			flush_rewrite_rules( false );
		}
	}
	
} //end class Slash_Edit

add_action( 'plugins_loaded', 'slash_edit_instantiate' );
function slash_edit_instantiate() {
	Slash_Edit::get_instance();
} //end slash_edit_instantiate

register_activation_hook( __FILE__, array( 'Slash_Edit', 'activate' ) );
register_activation_hook( __FILE__, array( 'Slash_Edit', 'deactivate' ) );