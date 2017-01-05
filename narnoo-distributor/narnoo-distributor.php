<?php
/*
Plugin Name: Narnoo Distributor
Plugin URI: http://narnoo.com/
Description: Allows Tourism organisations that use Wordpress to manage and include their Narnoo account into their Wordpress site. You will need a Narnoo API key pair to include your Narnoo media. You can find this by logging into your account at Narnoo.com and going to Account -> View APPS.
Version: 2.0
Author: Narnoo Wordpress developer
Author URI: http://www.narnoo.com/
License: GPL2 or later
*/

/*  Copyright 2016  Narnoo.com  (email : info@narnoo.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// plugin definitions
define( 'NARNOO_DISTRIBUTOR_PLUGIN_NAME', 'Narnoo Distributor' );
define( 'NARNOO_DISTRIBUTOR_CURRENT_VERSION', '2.0.0' );
define( 'NARNOO_DISTRIBUTOR_I18N_DOMAIN', 'narnoo-distributor' );

define( 'NARNOO_DISTRIBUTOR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NARNOO_DISTRIBUTOR_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'NARNOO_DISTRIBUTOR_SETTINGS_PAGE', 'options-general.php?page=narnoo-distributor-api-settings' );

// include files
if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-helper.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-categories-table.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-operators-table.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-search-add-operators-table.php' );
//require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-albums-table.php' );
//require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-images-table.php' );
//require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-brochures-table.php' );
//require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-channels-table.php' );
//require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-videos-table.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-operator-media-table.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-operator-albums-table.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-operator-images-table.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-operator-brochures-table.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-operator-videos-table.php' );
//require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-operator-products-accordion-table.php' );
//require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-search-media-table.php' );
//require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-search-images-table.php' );
//require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-search-brochures-table.php' );
//require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-search-videos-table.php' );
//require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-search-operator-media-table.php' );
//require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-search-operator-images-table.php' );
//require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-search-operator-brochures-table.php' );
//require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-search-operator-videos-table.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-library-images-table.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-operator-library-images-table.php' );
//require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'class-narnoo-distributor-imported-media-meta-box.php' );

// NARNOO PHP SDK 2.0 //
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'libs/narnoo/http/WebClient.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'libs/narnoo/operatorconnect.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'libs/narnoo/distributor.php' );
require_once( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'libs/narnoo/listbuilder.php' );


// begin!
new Narnoo_Distributor();

class Narnoo_Distributor {

	/**
	 * Plugin's main entry point.
	 **/
	function __construct() {
		register_uninstall_hook( __FILE__, array( 'NarnooDistributor', 'uninstall' ) );

		add_action( 'init', array( &$this, 'create_custom_post_types' ) );

		if ( is_admin() ) {
			add_action( 'plugins_loaded', array( &$this, 'load_language_file' ) );
			add_filter( 'plugin_action_links', array( &$this, 'plugin_action_links' ), 10, 2 );

			add_action( 'admin_notices', array( &$this, 'display_reminders' ) );
			add_action( 'admin_menu', array( &$this, 'create_menus' ), 9 );
			add_action( 'admin_init', array( &$this, 'admin_init' ) );
			add_action( 'admin_enqueue_scripts', array( 'Narnoo_Distributor_Categories_Table', 'load_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( 'Narnoo_Distributor_Operators_Table', 'load_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( 'Narnoo_Distributor_Search_Add_Operators_Table', 'load_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( 'Narnoo_Distributor_Operator_Products_Accordion_Table', 'load_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( 'Narnoo_Distributor_Search_Media_Table', 'load_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( 'Narnoo_Distributor_Search_Operator_Media_Table', 'load_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'load_admin_scripts' ) );

			add_filter( 'media_upload_tabs', array( &$this, 'add_narnoo_library_menu_tab' ) );
			add_action( 'media_upload_narnoo_library', array( &$this, 'media_narnoo_library_menu_handle') );
			add_action( 'media_upload_narnoo_distributor_library', array( &$this, 'media_narnoo_distributor_library_menu_handle') );

			add_action( 'wp_ajax_narnoo_distributor_api_request', array( 'Narnoo_Distributor_Helper', 'ajax_api_request' ) );
			add_action( 'wp_ajax_narnoo_add_image_to_wordpress_media_library', array( 'Narnoo_Distributor_Helper', 'ajax_add_image_to_wordpress_media_library' ) );

			//Meta Boxes
			add_action('add_meta_boxes', array( &$this, 'add_noo_album_meta_box'));
			add_action( 'save_post', array( &$this, 'save_noo_album_meta_box'));
			add_action('add_meta_boxes', array( &$this, 'add_noo_print_meta_box'));
			add_action( 'save_post', array( &$this, 'save_noo_print_meta_box'));

			//Meta Boxes - Operators
			add_action('add_meta_boxes', array( &$this, 'add_noo_op_album_meta_box'));
			add_action( 'save_post', array( &$this, 'save_noo_op_album_meta_box'));


			//new Narnoo_Distributor_Imported_Media_Meta_Box();
		} else {

			add_action( 'wp_enqueue_scripts', array( &$this, 'load_scripts' ) );
			add_filter( 'widget_text', 'do_shortcode' );
		}

		add_action( 'wp_ajax_narnoo_distributor_lib_request', 			array( &$this, 'narnoo_distributor_ajax_lib_request' ) );
		add_action( 'wp_ajax_nopriv_narnoo_distributor_lib_request', 	array( &$this, 'narnoo_distributor_ajax_lib_request' ) );
		add_filter( 'pre_get_posts', 									array( &$this, 'add_cpts_to_search') );
	}

	/**
	 * Register custom post types for Narnoo Operator Posts.
	 **/
	function create_custom_post_types() {
		// create custom post types
		$post_types = get_option( 'narnoo_custom_post_types', array() );

		foreach( $post_types as $category => $fields ) {
			register_post_type(
				'narnoo_' . $category,
				array(
					'label' => ucfirst( $category ),
					'labels' => array(
						'singular_name' => ucfirst( $category ),
					),
					'hierarchical' => true,
					'rewrite' => array( 'slug' => $fields['slug'] ),
					'description' => $fields['description'],
					'public' => true,
					'exclude_from_search' => true,
					'has_archive' => true,
					'publicly_queryable' => true,
					'show_ui' => true,
					'show_in_menu' => 'narnoo-distributor-categories',
					'show_in_admin_bar' => true,
					'supports' => array( 'title', 'excerpt', 'thumbnail', 'editor', 'author', 'revisions', 'custom-fields', 'page-attributes' ),
				)
			);
		}

		flush_rewrite_rules();
	}


	/**
	 * Add All Custom Post Types to search
	 *
	 * Returns the main $query.
	 *
	 * @access      public
	 * @since       1.0
	 * @return      $query
	*/

	function add_cpts_to_search($query) {

		// Check to verify it's search page
		if( is_search() ) {
			// Get post types
			$post_types = get_post_types(array('public' => true, 'exclude_from_search' => false), 'objects');
			$searchable_types = array('destination','narnoo_accommodation','narnoo_attraction','narnoo_service','narnoo_dining');
			// Add available post types
			if( $post_types ) {
				foreach( $post_types as $type) {
					$searchable_types[] = $type->name;
				}
			}
			$query->set( 'post_type', $searchable_types );
		}
		return $query;
	}


	/**
	 * Add Narnoo Library tabs to Wordpress media upload menu.
	 **/
	function add_narnoo_library_menu_tab( $tabs ) {
		$newTabs = array(
			'narnoo_library' => __( 'Narnoo Library', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'narnoo_distributor_library' => __( 'Narnoo Operator Library', NARNOO_DISTRIBUTOR_I18N_DOMAIN )
		);
		return array_merge( $tabs, $newTabs );
	}

	/**
	 * Handle display of Narnoo library in Wordpress media upload menu.
	 **/
	function media_narnoo_library_menu_handle() {
		return wp_iframe( array( &$this, 'media_narnoo_library_menu_display' ) );
	}

	function media_narnoo_library_menu_display() {
		media_upload_header();
		$narnoo_distributor_library_images_table = new Narnoo_Distributor_Library_Images_Table();
		?>
			<form id="narnoo-images-form" class="media-upload-form" method="post" action="">
				<?php
				$narnoo_distributor_library_images_table->prepare_items();
				$narnoo_distributor_library_images_table->display();
				?>
			</form>
		<?php
	}

	/**
	 * Handle display of Narnoo Operator library in Wordpress media upload menu.
	 **/
	function media_narnoo_distributor_library_menu_handle() {
		return wp_iframe( array( &$this, 'media_narnoo_distributor_library_menu_display' ) );
	}

	function media_narnoo_distributor_library_menu_display() {
		media_upload_header();
		$narnoo_distributor_operator_library_images_table = new Narnoo_Distributor_Operator_Library_Images_Table();
		?>
			<form id="narnoo-operator-images-form" class="media-upload-form" method="post" action="">
				<?php
				$narnoo_distributor_operator_library_images_table->prepare_items();
				$narnoo_distributor_operator_library_images_table->views();
				$narnoo_distributor_operator_library_images_table->display();
				?>
			</form>
		<?php
	}

	/**
	 * Clean up upon plugin uninstall.
	 **/
	static function uninstall() {
		unregister_setting( 'narnoo_distributor_settings', 'narnoo_distributor_settings', array( &$this, 'settings_sanitize' ) );
	}

	/**
	 * Add settings link for this plugin to Wordpress 'Installed plugins' page.
	 **/
	function plugin_action_links( $links, $file ) {
		if ( $file == plugin_basename( dirname(__FILE__) . '/narnoo-distributor.php' ) ) {
			$links[] = '<a href="' . NARNOO_DISTRIBUTOR_SETTINGS_PAGE . '">' . __('Settings') . '</a>';
		}

		return $links;
	}

	/**
	 * Load language file upon plugin init (for future extension, if any)
	 **/
	function load_language_file() {
		load_plugin_textdomain( NARNOO_DISTRIBUTOR_I18N_DOMAIN, false, NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'languages/' );
	}

	/**
	 * Display reminder to key in API keys in admin backend.
	 **/
	function display_reminders() {
		$options = get_option( 'narnoo_distributor_settings' );

		if ( empty( $options['access_key'] ) || empty( $options['secret_key'] ) ) {
			Narnoo_Distributor_Helper::show_notification(
				sprintf(
					__( '<strong>Reminder:</strong> Please key in your Narnoo API settings in the <strong><a href="%s">Settings->Narnoo API</a></strong> page.', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
					NARNOO_DISTRIBUTOR_SETTINGS_PAGE
				)
			);
		}
	}

	/**
	 * Add admin menus and submenus to backend.
	 **/
	function create_menus() {
		// add Narnoo API to settings menu
		add_options_page(
			__( 'Narnoo API Settings', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			__( 'Narnoo API', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'manage_options',
			'narnoo-distributor-api-settings',
			array( &$this, 'api_settings_page' )
		);

		// add main Narnoo Imports menu
		add_menu_page(
			__( 'Imported Operators', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			__( 'Imported Operators', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'manage_options',
			'narnoo-distributor-categories',
			array( &$this, 'categories_page' ),
			NARNOO_DISTRIBUTOR_PLUGIN_URL . 'images/icon-products-16.png',
			12
		);

		// add submenus to Narnoo Imports menu
		$page = add_submenu_page(
			'narnoo-distributor-categories',
			__( 'Narnoo Imports - Categories', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			__( 'Categories', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'manage_options',
			'narnoo-distributor-categories',
			array( &$this, 'categories_page' )
		);
		add_action( "load-$page", array( 'Narnoo_Distributor_Categories_Table', 'add_screen_options' ) );
		global $narnoo_distributor_categories_page;
		$narnoo_distributor_categories_page = $page;

		// add main Narnoo menu
		add_menu_page(
			__( 'Narnoo', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			__( 'Narnoo', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'manage_options',
			'narnoo-distributor-operators',
			array( &$this, 'operators_page' ),
			NARNOO_DISTRIBUTOR_PLUGIN_URL . 'images/icon-16.png',
			11
		);

		// add submenus to Narnoo menu
		$page = add_submenu_page(
			'narnoo-distributor-operators',
			__( 'Narnoo - Operators', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			__( 'Operators', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'manage_options',
			'narnoo-distributor-operators',
			array( &$this, 'operators_page' )
		);
		add_action( "load-$page", array( 'Narnoo_Distributor_Operators_Table', 'add_screen_options' ) );
		global $narnoo_distributor_operators_page;
		$narnoo_distributor_operators_page = $page;

		$page = add_submenu_page(
			'narnoo-distributor-operators',
			__( 'Narnoo Operator Media', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			__( 'Operator Media', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'manage_options',
			'narnoo-distributor-operator-media',
			array( &$this, 'operator_media_page' )
		);
		add_action( "load-$page", array( 'Narnoo_Distributor_Operator_Media_Table', 'add_screen_options' ) );
		global $narnoo_distributor_operator_media_page;
		$narnoo_distributor_operator_media_page = $page;

	}

	/**
	 * Upon admin init, register plugin settings and Narnoo shortcodes button, and define input fields for API settings.
	 **/
	function admin_init() {
		register_setting( 'narnoo_distributor_settings', 'narnoo_distributor_settings', array( &$this, 'settings_sanitize' ) );

		add_settings_section(
			'api_settings_section',
			__( 'API Settings', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			array( &$this, 'settings_api_section' ),
			'narnoo_distributor_api_settings'
		);

		add_settings_field(
			'access_key',
			__( 'Acesss key', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			array( &$this, 'settings_access_key' ),
			'narnoo_distributor_api_settings',
			'api_settings_section'
		);

		add_settings_field(
			'secret_key',
			__( 'Secret key', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			array( &$this, 'settings_secret_key' ),
			'narnoo_distributor_api_settings',
			'api_settings_section'
		);

		add_settings_field(
			'token_key',
			__( 'Token key', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			array( &$this, 'settings_token_key' ),
			'narnoo_distributor_api_settings',
			'api_settings_section'
		);

		add_settings_field(
			'product_import',
			__( 'Import Operator Products', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			array( &$this, 'settings_operator_import' ),
			'narnoo_distributor_api_settings',
			'api_settings_section'
		);

		// register Narnoo shortcode button and MCE plugin
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

	}

	function settings_api_section() {
		echo '<p>' . __( 'You can edit your Narnoo API settings below.', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) . '</p>';
	}

	function settings_access_key() {
		$options = get_option( 'narnoo_distributor_settings' );
		echo "<input id='access_key' name='narnoo_distributor_settings[access_key]' size='40' type='text' value='" . esc_attr($options['access_key']). "' />";
	}

	function settings_secret_key() {
		$options = get_option( 'narnoo_distributor_settings' );
		echo "<input id='secret_key' name='narnoo_distributor_settings[secret_key]' size='40' type='text' value='" . esc_attr($options['secret_key']). "' />";
	}

    function settings_token_key() {
        $options = get_option('narnoo_distributor_settings');
        echo "<input id='token_key' name='narnoo_distributor_settings[token_key]' size='40' type='text' value='" . esc_attr($options['token_key']). "' />";
    }

    function settings_operator_import() {
        $options = get_option('narnoo_distributor_settings');

        $html = '<input type="checkbox" id="checkbox_operator" name="narnoo_distributor_settings[operator_import]" value="1"' . checked( 1, $options['operator_import'], false ) . '/>';
	    $html .= '<label for="checkbox_operator">Check this box to import Operator products into your website</label>';

	    echo $html;
    }

	/**
	 * Sanitize input settings.
	 **/
	function settings_sanitize( $input ) {
		$new_input['access_key'] 		= trim( $input['access_key'] );
		$new_input['secret_key'] 		= trim( $input['secret_key'] );
        $new_input['token_key'] 		= trim( $input['token_key'] );
        $new_input['operator_import']   = trim( $input['operator_import'] );
		return $new_input;
	}

	/**
	 * Display API settings page.
	 **/
	function api_settings_page() {
		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_DISTRIBUTOR_PLUGIN_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo API Settings', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) ?></h2>
			<form action="options.php" method="post">
				<?php settings_fields( 'narnoo_distributor_settings' ); ?>
				<?php do_settings_sections( 'narnoo_distributor_api_settings' ); ?>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>
			</form>
			<?php

      		$request 		= Narnoo_Distributor_Helper::init_api();
      		//$cache	 		= Narnoo_Distributor_Helper::init_noo_cache();

			$distributor = null;
			if ( ! is_null( $request ) ) {
				try {
						//$distributor = $cache->get('distributor_details');
						if(empty($distributor)){
							$distributor = $request->getAccount();
							//if(!empty($distributor->success)){
								//	$cache->set('distributor_details', $distributor, 43200);
							//}
						}

         		} catch ( Exception $ex ) {
					$distributor = null;
					Narnoo_Distributor_Helper::show_api_error( $ex );
				}
			}

			if ( ! is_null( $distributor ) ) {


	         	?>
                    <h3><?php _e( 'Distributor Details', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) ?></h3>

                    <table class="form-table">
                        <tr><th><?php _e( 'ID', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->distributor_details->narnoo_id; ?></td></tr>
                        <tr><th><?php _e( 'Name', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->distributor_details->distributor_name; ?></td></tr>
                        <tr><th><?php _e( 'Email', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->distributor_details->distributor_email; ?></td></tr>
                        <tr><th><?php _e( 'Contact Name', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->distributor_details->distributor_contact; ?></td></tr>
                        <tr><th><?php _e( 'Suburb', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->distributor_details->distributor_suburb; ?></td></tr>
                        <tr><th><?php _e( 'State', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->distributor_details->distributor_state; ?></td></tr>
                        <tr><th><?php _e( 'Phone', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->distributor_details->distributor_phone; ?></td></tr>
                        <tr><th><?php _e( 'URL', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->distributor_details->distributor_url; ?></td></tr>
                       </table>
                <?php
                } else {
                ?>
                    <h3><?php _e( 'Distributor Details', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) ?></h3>
                    <table class="form-table">
                        <tr><th><?php _e( 'ID', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->distributor_details->distributor_id; ?></td></tr>
                        <tr><th><?php _e( 'Email', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->distributor_details->email; ?></td></tr>
                        <tr><th><?php _e( 'Business Name', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->distributor_details->distributor_businessname; ?></td></tr>
                        <tr><th><?php _e( 'Contact Name', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->distributor_details->distributor_contactname; ?></td></tr>
                        <tr><th><?php _e( 'Country', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->distributor_details->country_name; ?></td></tr>
                        <tr><th><?php _e( 'Post Code', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->distributor_details->postcode; ?></td></tr>
                        <tr><th><?php _e( 'Suburb', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->distributor_details->suburb; ?></td></tr>
                        <tr><th><?php _e( 'State', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->distributor_details->state; ?></td></tr>
                        <tr><th><?php _e( 'Phone', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->distributor_details->phone; ?></td></tr>
                        <tr><th><?php _e( 'URL', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th><td><?php echo $distributor->distributor_details->distributor_url; ?></td></tr>
                       </table>
                <?php
                }

			?>
		</div>
		<?php
	}

	/**
	 * Display Narnoo Operators page.
	 **/
	function operators_page() {
/*
        $operator_version = 'operator2' ; // for api 2.0

        $request = Narnoo_Distributor_Helper::init_api($operator_version);

        $list = $request->getOperators();

        var_dump($list);
*/

		global $narnoo_distributor_operators_table;
		if ( $narnoo_distributor_operators_table->func_type === 'search' ) {
			$this->search_add_operators_page();
			return;
		}
		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_DISTRIBUTOR_PLUGIN_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo - Operators', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) ?>
				<a class="add-new-h2" href="?page=narnoo-distributor-operators&func_type=search"><?php _e( 'Search/Add Operators', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></a></h2>
			<form id="narnoo-operators-form" method="post" action="?<?php echo esc_attr( build_query( array( 'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '', 'paged' => $narnoo_distributor_operators_table->get_pagenum() ) ) ); ?>">
			<?php
			if ( $narnoo_distributor_operators_table->prepare_items() ) {
				$narnoo_distributor_operators_table->display();
			}
			?>
			</form>
		</div>
		<?php

	}

	/**
	 * Display Search/Add Narnoo Operators page.
	 **/
	function search_add_operators_page() {
		global $narnoo_distributor_operators_table;
		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_DISTRIBUTOR_PLUGIN_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo - Search/Add Operators', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) ?>
				<a class="add-new-h2" href="?page=narnoo-distributor-operators"><?php _e( "View Added Operators", NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></a></h2>
			<form id="narnoo-search-add-operators-form" method="post" action="?<?php echo esc_attr( build_query( array(
				'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
				'paged' => $narnoo_distributor_operators_table->get_pagenum(),
				'func_type' => $narnoo_distributor_operators_table->func_type,
				'search_country'     => $narnoo_distributor_operators_table->search_country    ,
				'search_category'    => $narnoo_distributor_operators_table->search_category   ,
				'search_subcategory' => $narnoo_distributor_operators_table->search_subcategory,
				'search_state'       => $narnoo_distributor_operators_table->search_state      ,
				'search_suburb'      => $narnoo_distributor_operators_table->search_suburb     ,
				'search_postal_code' => $narnoo_distributor_operators_table->search_postal_code,
			) ) ); ?>">
				<?php
				if ( $narnoo_distributor_operators_table->prepare_items() ) {
					$narnoo_distributor_operators_table->display();
				}
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Display Narnoo Operator Media page.
	 **/
	function operator_media_page() {
		global $narnoo_distributor_operator_media_table;

		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_DISTRIBUTOR_PLUGIN_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo Operator Media', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) ?></h2>
			<form id="narnoo-media-form" method="post" action="?<?php echo esc_attr( build_query( array(
				'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
				'paged' => $narnoo_distributor_operator_media_table->get_pagenum(),
				'media_type' => $narnoo_distributor_operator_media_table->media_view_type,
				'operator_id' => $narnoo_distributor_operator_media_table->operator_id,
				'operator_name' => $narnoo_distributor_operator_media_table->operator_name,
				'album_page' => $narnoo_distributor_operator_media_table->current_album_page,
				'album' => $narnoo_distributor_operator_media_table->current_album_id,
				'album_name' => $narnoo_distributor_operator_media_table->current_album_name
			) ) ); ?>">
			<?php
			if ( $narnoo_distributor_operator_media_table->prepare_items() ) {
				$narnoo_distributor_operator_media_table->views();

				if ( $narnoo_distributor_operator_media_table->media_view_type === 'Albums' ) {
					?><br /><br /><?php
					if ( ! empty( $narnoo_distributor_operator_media_table->current_album_name ) ) {
						?><h4>Currently viewing album: <?php echo $narnoo_distributor_operator_media_table->current_album_name; ?></h4><?php
						_e( 'Select album:', NARNOO_DISTRIBUTOR_I18N_DOMAIN );
						echo $narnoo_distributor_operator_media_table->select_album_html_script;
						submit_button( __( 'Go', NARNOO_DISTRIBUTOR_I18N_DOMAIN ), 'button-secondary action', "album_select_button", false );
					}
				}

				$narnoo_distributor_operator_media_table->display();
			}
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Display Narnoo Categories page.
	 **/
	function categories_page() {
		global $narnoo_distributor_categories_table;


		?>
		<div class="wrap">
			<div class="icon32"><img src="<?php echo NARNOO_DISTRIBUTOR_PLUGIN_URL; ?>/images/icon-32.png" /><br /></div>
			<h2><?php _e( 'Narnoo - Categories', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) ?></h2>
			<form id="narnoo-categories-form" method="post" action="?<?php echo esc_attr( build_query( array( 'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '', 'paged' => $narnoo_distributor_categories_table->get_pagenum() ) ) ); ?>">
			<?php
			if ( $narnoo_distributor_categories_table->prepare_items() ) {
				$narnoo_distributor_categories_table->display();
			}
			?>
			</form>
		</div>
		<?php
	}


	/*
	*
	*	title: Narnoo add narnoo album to a page
	*	date created: 15-09-16
	*/
	function add_noo_album_meta_box()
	{

	            add_meta_box(
	                'noo-album-box-class',      		// Unique ID
				    'Select Narnoo Album', 		 		    // Title
				    array( &$this,'box_display_album_information'),    // Callback function
				    array('page','destination','post'),         					// Admin page (or post type)
				    'side',         					// Context
				    'low'         					// Priority
	             );

	}

	/*
	*
	*	title: Display the album select box
	*	date created: 15-09-16
	*/
	function box_display_album_information( $post )
	{

	global $post;
    //$values = get_post_custom( $post->ID );
    $selected = get_post_meta($post->ID,'noo_album_select_id',true);
    //$selected = isset( $values['noo_album_select_id'] ) ? esc_attr( $values['noo_album_select_id'] ) : '';

	// We'll use this nonce field later on when saving.
    wp_nonce_field( 'album_meta_box_nonce', 'box_display_album_information_nonce' );

		$current_page 		      = 1;
		//$cache	 				  = Narnoo_Distributor_Helper::init_noo_cache();
		$request 				  = Narnoo_Distributor_Helper::init_api( 'media' );

		//Get Narnoo Ablums.....
		if ( ! is_null( $request ) ) {

			//$list = $cache->get('albums_'.$current_page);

			if( empty($list) ){

					try {

						$list = $request->getAlbums( $current_page );
						if ( ! is_array( $list->distributor_albums ) ) {
							throw new Exception( sprintf( __( "Error retrieving albums. Unexpected format in response page #%d.", NARNOO_OPERATOR_I18N_DOMAIN ), $current_page ) );
						}

						if(!empty( $list->success ) ){
								//$cache->set('albums_'.$current_page, $list, 43200);
						}

					} catch ( Exception $ex ) {
						//Narnoo_Distributor_Helper::show_api_error( $ex ); don't need to show anything
					}

			}

			//Check the total pages and run through each so we can build a bigger list of albums

		}


    ?> <p>
        <label for="my_meta_box_select">Narnoo Album:</label>
        <select name="noo_album_select" id="noo_album_select">
        	<option value="">None</option>
            <?php foreach ($list->distributor_albums as $album) { ?>
            		<option value="<?php echo $album->album_id; ?>" <?php selected( $selected, $album->album_id ); ?>><?php echo ucwords( $album->album_name ); ?></option>
            <?php } ?>
        </select>
        <p><small><em>Select an album and this will be displayed the page.</em></small></p>
    </p>
  	<?php

	}

	function save_noo_album_meta_box( $post_id ){

		// Bail if we're doing an auto save
	    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

	    // if our nonce isn't there, or we can't verify it, bail
	    if( !isset( $_POST['box_display_album_information_nonce'] ) || !wp_verify_nonce( $_POST['box_display_album_information_nonce'], 'album_meta_box_nonce' ) ) return;

	    // if our current user can't edit this post, bail
	    if( !current_user_can( 'edit_post' ) ) return;

	    if( isset( $_POST['noo_album_select'] ) ){
        	update_post_meta( $post_id, 'noo_album_select_id', esc_attr( $_POST['noo_album_select'] ) );
    	}

	}


	/*
	*
	*	title: Narnoo add narnoo album to a page
	*	date created: 15-09-16
	*/
	function add_noo_print_meta_box()
	{

	            add_meta_box(
			          'noo-print-box-class',      		// Unique ID
						    'Enter Narnoo Print ID', 		 		    // Title
						    array( &$this,'box_display_print_information'),    // Callback function
						    array('page','destination','post'),         					// Admin page (or post type)
						    'side',         					// Context
						    'low'         					// Priority
			        );

	}

	/*
	*
	*	title: Display the print select box
	*	date created: 15-09-16
	*/
	function box_display_print_information( $post )
	{

	global $post;
    //$values = get_post_custom( $post->ID );
    $selected = get_post_meta($post->ID,'noo_print_id',true);
    //$selected = isset( $values['noo_album_select_id'] ) ? esc_attr( $values['noo_album_select_id'] ) : '';

	// We'll use this nonce field later on when saving.
    wp_nonce_field( 'print_meta_box_nonce', 'box_display_print_information_nonce' );



    ?> <p>
        <label for="print_box_text">Narnoo Print Item:</label>
        <input type="text" name="noo_print_box_text" id="noo_print_box_text" value="<?php echo $selected; ?>" />
    </p>
        <p><small><em>Enter a print ID to display a PDF on the page.</em></small></p>
    </p>
  	<?php

	}

	function save_noo_print_meta_box( $post_id ){

		// Bail if we're doing an auto save
	    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

	    // if our nonce isn't there, or we can't verify it, bail
	    if( !isset( $_POST['box_display_print_information_nonce'] ) || !wp_verify_nonce( $_POST['box_display_print_information_nonce'], 'print_meta_box_nonce' ) ) return;

	    // if our current user can't edit this post, bail
	    if( !current_user_can( 'edit_post' ) ) return;

	    if( isset( $_POST['noo_print_box_text'] ) ){
        	update_post_meta( $post_id, 'noo_print_id', wp_kses( $_POST['noo_print_box_text'] ) );
    	}

	}


	/*************************************************************************
					OPERATOR PAGES META BOXES
	/*************************************************************************/
	/*
	*
	*	title: Narnoo add narnoo album to a page
	*	date created: 15-09-16
	*/
	function add_noo_op_album_meta_box( )
	{



        add_meta_box(
            'noo-album-box-class',      		// Unique ID
		    'Select Narnoo Album', 		 		    // Title
		    array( &$this,'box_display_op_album_information'),    // Callback function
		    array('narnoo_attraction','narnoo_accommodation','narnoo_service','narnoo_dining'),         					// Admin page (or post type)
		    'side',         					// Context
		    'low'         					// Priority
         );

	}

	/*
	*
	*	title: Display the album select box
	*	date created: 15-09-16
	*/
	function box_display_op_album_information( $post )
	{

	global $post;

    //First check that this is a Narnoo imported product
    $dataSource = get_post_meta($post->ID,'data_source',true);
    if( empty($dataSource) || $dataSource != 'narnoo'){
    	return  _e( 'This product has not been imported via Narnoo.com',  NARNOO_DISTRIBUTOR_I18N_DOMAIN);
    }

    $operatorId = get_post_meta($post->ID,'operator_id',true);
    if( empty($operatorId) ){
    	return _e( 'There is no Narnoo ID associated with this product',  NARNOO_DISTRIBUTOR_I18N_DOMAIN);
    }

    //$values = get_post_custom( $post->ID );
    $selected = get_post_meta($post->ID,'noo_op_album_select_id',true);

	// We'll use this nonce field later on when saving.
    wp_nonce_field( 'op_album_meta_box_nonce', 'box_display_op_album_information_nonce' );

		$current_page 		      = 1;
		//$cache	 				  = Narnoo_Distributor_Helper::init_noo_cache();
		$request 				  = Narnoo_Distributor_Helper::init_api( 'operator' );

		//Get Narnoo Ablums.....
		if ( ! is_null( $request ) ) {

			//$list = $cache->get('albums_'.$current_page);

			if( empty($list) ){

					try {

						$list = $request->getAlbums( $operatorId,$current_page );
						if ( ! is_array( $list->operator_albums ) ) {
							throw new Exception( sprintf( __( "Error retrieving albums. Unexpected format in response page #%d.", NARNOO_OPERATOR_I18N_DOMAIN ), $current_page ) );
						}

						if(!empty( $list->success ) ){
								//$cache->set('albums_'.$current_page, $list, 43200);
						}

					} catch ( Exception $ex ) {
						//Narnoo_Distributor_Helper::show_api_error( $ex ); don't need to show anything
					}

			}

			//Check the total pages and run through each so we can build a bigger list of albums

		}


    ?> <p>
        <label for="my_meta_box_select">Narnoo Album:</label>
        <select name="noo_op_album_select" id="noo_op_album_select">
        	<option value="">None</option>
            <?php foreach ($list->operator_albums as $album) { ?>
            		<option value="<?php echo $album->album_id; ?>" <?php selected( $selected, $album->album_id ); ?>><?php echo ucwords( $album->album_name ); ?></option>
            <?php } ?>
        </select>
        <p><small><em>Select an album and this will be displayed the page.</em></small></p>
    </p>
  	<?php

	}

	function save_noo_op_album_meta_box( $post_id ){

		// Bail if we're doing an auto save
	    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

	    // if our nonce isn't there, or we can't verify it, bail
	    if( !isset( $_POST['box_display_op_album_information_nonce'] ) || !wp_verify_nonce( $_POST['box_display_op_album_information_nonce'], 'op_album_meta_box_nonce' ) ) return;

	    // if our current user can't edit this post, bail
	    if( !current_user_can( 'edit_post' ) ) return;

	    if( isset( $_POST['noo_op_album_select'] ) ){
        	update_post_meta( $post_id, 'noo_op_album_select_id', esc_attr( $_POST['noo_op_album_select'] ) );
    	}

	}

	/*************************************************************************
					OPERATOR PAGES META BOXES [end]
	/*************************************************************************/


	/**
	 * Process frontend AJAX requests triggered by shortcodes.
	 **/
	function narnoo_distributor_ajax_lib_request() {
		ob_start();
		require( $_POST['lib_path'] );
		echo json_encode( array( 'response' => ob_get_clean() ) );
		die();
	}


	/**
	 * Loads common Javascript/CSS files for admin area.
	 **/
	function load_admin_scripts() {
		?>
		<style type="text/css" media="screen">
		#icon-narnoo-distributor-categories.icon32 {
			background: url(<?php echo NARNOO_DISTRIBUTOR_PLUGIN_URL . '/images/icon-32.png'; ?>) no-repeat;
		}
		</style>
		<?php
	}
}
