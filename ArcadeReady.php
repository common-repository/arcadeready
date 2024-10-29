<?php
defined( 'ABSPATH' ) or die( 'Come on, please!' );
/*
Plugin Name: Arcade Ready
Plugin URI: http://arcadeready.com/
Description: Turn your wordpress blog into an arcade wonder!
Version:     1.1.1
Author: Bytephp
Author URI: http://bytephp.com/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Arcade Ready is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Arcade Ready is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

*/

define( 'ARCADEREADY_VERSION', '1.1.1' );
define( 'ARCADEREADY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'ARCADEREADY_PLUGIN_URL', plugins_url( '', __FILE__ ) );

class ArcadeReady_Core {

	public $settings;
	public $messageArr;
	public $mimes = array();
	public $frameTypes = array();

	public function __construct(){
		include_once( ARCADEREADY_PLUGIN_PATH . 'lib/statics.php');
		include_once( ARCADEREADY_PLUGIN_PATH . 'lib/gameHandler.php');
		include_once( ARCADEREADY_PLUGIN_PATH . 'lib/shortcodeHandler.php');
		include_once( ARCADEREADY_PLUGIN_PATH . 'lib/metaHandler.php');
		$this->handle_mimes();
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'adminMenu' ) );
			add_action( 'admin_init',  array( $this, 'alterExcerptTitle' ) );
			add_action( 'admin_enqueue_scripts',  array( $this, 'loadAdminScripts' ) );
			add_action('admin_footer', array( 'ArcadeReady', 'buildGameList' ) );
			add_action('media_buttons', array( 'ArcadeReady', 'showGamesListButton' ) );
			register_activation_hook  ( __FILE__, array( $this, 'activatePlugin' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivatePlugin' ) );
			register_uninstall_hook   ( __FILE__, array( 'ArcadeReady', 'uninstallPlugin' ) );
			add_filter( 'post_updated_messages', array( $this, 'ar_gamesUpdatedMessages' ) );
		}
		add_action( 'init', array( $this, 'gameCategoryTaxonomy' ) );
		add_action( 'init', array( $this, 'gameTagsTaxonomy' ) );
		add_action( 'init', array( $this, 'arcadeGamePost' ) );
		$this->loadAdminMessage( 'ARadminMessages' );
		$this->settings = maybe_unserialize( get_option( 'ArcadeReadyOptions' ) );
		if ( $this->settings['needFlush'] == true ) {
			add_action( 'admin_init',  array( $this, 'flush' ) );
		}
	}
	public function loadAdminScripts() {
		wp_enqueue_style( 'ARadminStyle', ARCADEREADY_PLUGIN_URL . '/admin/css/style.css' );
		wp_enqueue_style( 'ARfastyle', ARCADEREADY_PLUGIN_URL . '/lib/css/font-awesome.min.css' );
		wp_enqueue_script( 'ARadminjs', ARCADEREADY_PLUGIN_URL . '/admin/js/core.js', array( 'jquery', 'jquery-ui-datepicker' ) );
		$ARdataObject = ArcadeReady::javascriptTranslations( $this->frameTypes );
		$ARdataObject['dateFormat'] = get_option( 'date_format', 'dd-mm-yy' );
		wp_localize_script( 'ARadminjs', 'ARdataObject', $ARdataObject );
	}
	public function flush( ) {
		flush_rewrite_rules();
		$this->settings['needFlush'] = false;
		$this->updateOptions( $this->settings );
	}
	private function updateOptions( $settings ) {
		if ( update_option( 'ArcadeReadyOptions', $settings ) ) {
			ArcadeReady::setMessage( 'ARadminMessages', __( 'Arcade Ready settings updated successfully.' ), 'success' );
			return true;
		} else {
			ArcadeReady::setMessage( 'ARadminMessages', __( 'Arcade Ready settings update failed' ), 'error' );
			return false;
		}
	}
	public function loadAdminMessage( $id = false, $return = false ) {
		if ( $id == false ) {
			return;
		}
		$messageArr = get_transient( $id );
		if ( $messageArr === false ) {
			return;
		}
		delete_transient( $id );
		if ( $return == true ) {
			return $messageArr;
		}
		$this->messageArr = $messageArr;
		add_action( 'admin_notices', array( $this, 'displayAdminMessage' ) );
	}
	public function displayAdminMessage() {
		foreach ( $this->messageArr as $message ) {
			echo '<div class="notice ' .  $message['typeTag'] . ' ' . $message['dismissableTag'] . '"><p>' . $message['message'] . '</p></div>';
		}
	}
	public function adminMenu() {
		$AR_pageTitle = 'Arcade Ready Options';
		$AR_menuTitle = 'Arcade Ready';
		$AR_capability = 'manage_options';
		$AR_menuSlug = 'arcadereadymenu';
		$AR_function = '';
		$AR_icon = '';
		$AR_position = '26.7';
	}
	public function alterExcerptTitle() {
		remove_meta_box( 'postexcerpt', 'ar_games', 'side' );
		add_meta_box('postexcerpt', __('Short Description (excerpt):'), 'post_excerpt_meta_box', 'ar_games', 'normal', 'high');
	}
	public function arcadeGamePost() {
		$labels = array(
		'name'                  => _x( 'Games', 'Post Type General Name', 'arcade-ready-tr' ),
		'singular_name'         => _x( 'Game', 'Post Type Singular Name', 'arcade-ready-tr' ),
		'menu_name'             => __( 'Arcade Ready', 'arcade-ready-tr' ),
		'name_admin_bar'        => __( 'Games', 'arcade-ready-tr' ),
		'archives'              => __( 'Game Archives', 'arcade-ready-tr' ),
		'parent_item_colon'     => __( 'Parent Item:', 'arcade-ready-tr' ),
		'all_items'             => __( 'All Games', 'arcade-ready-tr' ),
		'add_new_item'          => __( 'Add New Game', 'arcade-ready-tr' ),
		'add_new'               => __( 'Add Game', 'arcade-ready-tr' ),
		'new_item'              => __( 'New Game', 'arcade-ready-tr' ),
		'edit_item'             => __( 'Edit Game', 'arcade-ready-tr' ),
		'update_item'           => __( 'Update Game', 'arcade-ready-tr' ),
		'view_item'             => __( 'View Game', 'arcade-ready-tr' ),
		'search_items'          => __( 'Search Games', 'arcade-ready-tr' ),
		'not_found'             => __( 'Not found', 'arcade-ready-tr' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'arcade-ready-tr' ),
		'featured_image'        => __( 'Featured Image', 'arcade-ready-tr' ),
		'set_featured_image'    => __( 'Set featured image', 'arcade-ready-tr' ),
		'remove_featured_image' => __( 'Remove featured image', 'arcade-ready-tr' ),
		'use_featured_image'    => __( 'Use as featured image', 'arcade-ready-tr' ),
		'insert_into_item'      => __( 'Insert into Game', 'arcade-ready-tr' ),
		'uploaded_to_this_item' => __( 'Uploaded to this Game', 'arcade-ready-tr' ),
		'items_list'            => __( 'Games list', 'arcade-ready-tr' ),
		'items_list_navigation' => __( 'Games list navigation', 'arcade-ready-tr' ),
		'filter_items_list'     => __( 'Filter games list', 'arcade-ready-tr' ),
	);
	$rewrite = array(
		'slug'                  => $this->settings['rewrite'],
	);
	$args = array(
		'label'                 => __( 'Game', 'arcade-ready-tr' ),
		'description'           => __( 'Arcade Ready Game Management', 'arcade-ready-tr' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'revisions' ),
		'taxonomies'            => array( 'gamecategory' ),
		'hierarchical'          => false,
		'public'                => false,
		'exclude_from_search'   => true,
		'publicly_queryable'    => false,

		'show_ui'               => true,
		'show_in_nav_menus'     => false,
		'show_in_menu'          => true,
		'show_in_admin_bar'     => false,
		'menu_position'         => 5,
		'can_export'            => true,
		'has_archive'           => false,
		'rewrite'               => $rewrite,
	);
	register_post_type( 'ar_games', $args );
	}
	public function ar_gamesUpdatedMessages( $messages ) {
	    $post             = get_post();
	    $post_type        = get_post_type( $post );
	    $post_type_object = get_post_type_object( $post_type );

	    $messages['ar_games'] = array(
	        0  => '',
	        1  => __( 'Game updated.', 'arcade-ready-tr' ),
	        2  => __( 'Custom field updated.', 'arcade-ready-tr' ),
	        3  => __( 'Custom field deleted.', 'arcade-ready-tr' ),
	        4  => __( 'Game updated.', 'arcade-ready-tr' ),
	        5  => isset( $_GET['revision'] ) ? sprintf( __( 'Game restored to revision from %s', 'arcade-ready-tr' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
	        6  => __( 'Game published.', 'arcade-ready-tr' ),
	        7  => __( 'Game saved.', 'arcade-ready-tr' ),
	        8  => __( 'Game submitted.', 'arcade-ready-tr' ),
	        9  => sprintf(
	            __( 'Game scheduled for: <strong>%1$s</strong>.', 'arcade-ready-tr' ),
	            date_i18n( _x( 'M j, Y @ G:i', 'Date format for scheduling.', 'arcade-ready-tr' ), strtotime( $post->post_date ) )
	        ),
	        10 => __( 'Game draft updated.', 'arcade-ready-tr' ),
	    );
	    return $messages;
	}
	public function gameCategoryTaxonomy() {
		$labels = array(
			'name'                       => _x( 'Game Categories', 'Taxonomy General Name', 'arcade-ready-tr' ),
			'singular_name'              => _x( 'Game Category', 'Taxonomy Singular Name', 'arcade-ready-tr' ),
			'menu_name'                  => __( 'Game Categories', 'arcade-ready-tr' ),
			'all_items'                  => __( 'All game categories', 'arcade-ready-tr' ),
			'parent_item'                => __( 'Parent Game Category', 'arcade-ready-tr' ),
			'parent_item_colon'          => __( 'Parent Game Category:', 'arcade-ready-tr' ),
			'new_item_name'              => __( 'New Game Category Name', 'arcade-ready-tr' ),
			'add_new_item'               => __( 'Add New Game Category', 'arcade-ready-tr' ),
			'edit_item'                  => __( 'Edit Game Category', 'arcade-ready-tr' ),
			'update_item'                => __( 'Update Game Category', 'arcade-ready-tr' ),
			'view_item'                  => __( 'View Item', 'arcade-ready-tr' ),
			'separate_items_with_commas' => __( 'Separate game categories with commas', 'arcade-ready-tr' ),
			'add_or_remove_items'        => __( 'Add or remove game categories', 'arcade-ready-tr' ),
			'choose_from_most_used'      => __( 'Choose from the most used game categories', 'arcade-ready-tr' ),
			'popular_items'              => __( 'Popular Items', 'arcade-ready-tr' ),
			'search_items'               => __( 'Search game categories', 'arcade-ready-tr' ),
			'not_found'                  => __( 'No Game Categories Found', 'arcade-ready-tr' ),
			'no_terms'                   => __( 'No items', 'arcade-ready-tr' ),
			'items_list'                 => __( 'Items list', 'arcade-ready-tr' ),
			'items_list_navigation'      => __( 'Items list navigation', 'arcade-ready-tr' ),
		);
		$rewrite = array(
			'slug'                  	 => $this->settings['categoryRewrite'],
		);
		$args = array(
			'labels'                     => $labels,
			'rewrite' 					 => $rewrite,
			'hierarchical'               => true,
			'public'                     => false,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_in_menu'				 => true,
			'show_tagcloud'              => false,
		);
		register_taxonomy( 'gamecategory', array( 'ar_games' ), $args );

	}
	public function gameTagsTaxonomy() {
		$labels = array(
			'name'                       => _x( 'Game Tags', 'Taxonomy General Name', 'arcade-ready-tr' ),
			'singular_name'              => _x( 'Game Tag', 'Taxonomy Singular Name', 'arcade-ready-tr' ),
			'menu_name'                  => __( 'Game Tags', 'arcade-ready-tr' ),
			'all_items'                  => __( 'All game tags', 'arcade-ready-tr' ),
			'new_item_name'              => __( 'New Game Tag Name', 'arcade-ready-tr' ),
			'add_new_item'               => __( 'Add New Game Tag', 'arcade-ready-tr' ),
			'edit_item'                  => __( 'Edit Game Tag', 'arcade-ready-tr' ),
			'update_item'                => __( 'Update Game Tag', 'arcade-ready-tr' ),
			'view_item'                  => __( 'View Item', 'arcade-ready-tr' ),
			'separate_items_with_commas' => __( 'Separate game tags with commas', 'arcade-ready-tr' ),
			'add_or_remove_items'        => __( 'Add or remove game tags', 'arcade-ready-tr' ),
			'choose_from_most_used'      => __( 'Choose from the most used game tags', 'arcade-ready-tr' ),
			'popular_items'              => __( 'Popular Items', 'arcade-ready-tr' ),
			'search_items'               => __( 'Search game tags', 'arcade-ready-tr' ),
			'not_found'                  => __( 'No Game Tags Found', 'arcade-ready-tr' ),
			'no_terms'                   => __( 'No items', 'arcade-ready-tr' ),
			'items_list'                 => __( 'Items list', 'arcade-ready-tr' ),
			'items_list_navigation'      => __( 'Items list navigation', 'arcade-ready-tr' ),
		);
		$rewrite = array(
			'slug'                  	 => $this->settings['tagsRewrite'],
		);
		$args = array(
			'labels'                     => $labels,
			'rewrite' 					 => $rewrite,
			'hierarchical'               => false,
			'public'                     => false,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_in_menu'				 => true,
			'show_tagcloud'              => false,
		);
		register_taxonomy( 'gametags', array( 'ar_games' ), $args );
	}

	public function handle_mimes () {
		$this->mimes['swf'] = 'application/x-shockwave-flash';
		$this->frameTypes[] = 'application/x-shockwave-flash';
		add_filter('upload_mimes', array( $this,'add_mimes' ) );
	}
	public function add_mimes( $mimes ) {
		foreach ( $this->mimes as $mime => $mimeType ) {
			$mimes[ $mime ] = $mimeType;
		}
		return $mimes;
	}
	public function activatePlugin() {
		$options = array(
			'rewrite'	   	  => 'games',
			'categoryRewrite' => 'gamecategory',
			'tagsRewrite'	  => 'gametag',
			'fullArcade'   	  => false,
			'sidebarGames' 	  => true,
			'needFlush' 	  => true
		);
		$result = add_option( 'ArcadeReadyOptions', $options );
		if ( $result == false ) {
			$this->settings['needFlush'] = true;
			$this->updateOptions( $this->settings );
		}
	}
	public function deactivatePlugin() {
		flush_rewrite_rules();
	}
}

new ArcadeReady_Core();
?>
