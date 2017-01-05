<?php
/**
 * Meta box that appears in Edit/Add posts (with Narnoo custom post type) for imported Narnoo media.
 **/
class Narnoo_Distributor_Imported_Media_Meta_Box {	
	function __construct() {
		add_action( 'admin_enqueue_scripts', array( &$this, 'load_scripts' ) );
		add_action( 'add_meta_boxes', array( &$this, add_meta_box ) );
		add_action( 'save_post', array( &$this, 'save_postdata' ) );
	}
	
	/**
	 * Saves the ordering of indices in custom field arrays when user saves post.
	 **/
	function save_postdata( $post_id ) {
		// Ensure this is not an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;		
		}
		
		// Ensure save came from the Edit Post screen with nonce
		if ( ! wp_verify_nonce( $_POST['narnoo_imported_media_nonce'], plugin_basename( __FILE__ ) ) ) {
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		$media_types = array( 'albums', 'images', 'brochures', 'videos', 'text' );
		foreach( $media_types as $media ) {
			$field_name = 'narnoo-imported-media-' . $media . '-indices';
			if ( ! isset( $_POST[ $field_name ] ) ) {
				continue;
			}

			$indices = $_POST[ $field_name ];
			$original_items = get_post_meta( $post_id, 'narnoo_' . $media, true );
			
			// ensure number of items in custom field array matches the number of POSTed indices 
			if ( count( $indices ) !== count( $original_items ) ) {
				return;
			}
			
			// reorder the items
			$reordered_items = array();
			foreach( $indices as $original_index ) {
				$reordered_items[] = $original_items[ $original_index ];
			}
			
			// update the items
			update_post_meta( $post_id, 'narnoo_' . $media, $reordered_items );
		}
	}
	
	/**
	 * Add a meta box for imported Narnoo media for each Narnoo custom post type.
	 **/
	function add_meta_box() {
		global $post;

		// don't display meta box on sub_category archive pages
		$sub_category = get_post_meta( $post->ID, 'narnoo_sub_category_archive', true );
		if ( ! empty( $sub_category ) ) {
			return;
		}
		
		$post_types = get_option( 'narnoo_custom_post_types', array() );
		
		foreach( $post_types as $category => $fields ) {
			add_meta_box( 
				'narnoo_imported_media_' . $category,
				__( 'Narnoo Imported Media', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
				array( &$this, 'render_meta_box_content' ),
				'narnoo_' . $category,
				'normal',
				'core'
			);
		}
	}
	
	/**
	 * Displays specified media table (i.e. images, brochures, albums, videos, text) in meta box.
	 **/
	function display_table( $media ) {
		global $post;
		
		$items = get_post_meta( $post->ID, 'narnoo_' . $media, true );
		if ( ! is_array( $items ) ) {
			$items = array();
		}

		?>
		<p><?php echo sprintf( __( 'The following %d imported item(s) can be accessed in your theme using the custom field array %s.', NARNOO_DISTRIBUTOR_I18N_DOMAIN ), count( $items ), "<code>narnoo_" . $media . "</code>" ); ?></p>
		<table cellspacing="1" class="narnoo_media_table">
			<thead>
				<tr>
					<th class="narnoo_array_index_th"><?php _e( 'Array Index', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th>
					<th><?php _e( 'Imported Fields', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?> ( <a href="#" class="narnoo_toggle_fields"><span class="narnoo_show_id_field" style="display:none;"><?php _e( 'Show ID field only', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></span><span class="narnoo_show_all_fields"><?php _e( 'Show all fields', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></span></a> )</th>
				</tr>
			</thead>
			<tbody>
			<?php
			foreach( $items as $index => $item ) {
				?>
				<tr>
					<td class="narnoo_array_index_td">
						<?php echo $index; ?>
						<input type="hidden" name="narnoo-imported-media-<?php echo esc_attr( $media ); ?>-indices[]" value="<?php echo $index; ?>" />
					</td>
					<td>
					<?php
					$first_field = true;
					foreach( $item as $key => $value ) {
						$html_value = esc_html( $value );
						if ( substr( $key, -4 ) === 'path' ) {
							$html_value = '<a target="_blank" href="' . $html_value . '">' . $html_value . '</a>';
						} 
						?>
						<p><?php echo '<code>' . esc_html( $key ) . '</code> = ' . $html_value ; ?></p>
						<?php
						if ( $first_field ) {
							$first_field = false;
							?><div class="narnoo-other-fields" style="display:none;"><?php
						} 
					}
					?>
						</div>
					</td>
				</tr>
				<?php
			}
			?>
			</tbody>
		</table>
		<?php
	}
	
	/**
	 * Displays the main content of the Narnoo Imported Media meta box.
	 **/
	function render_meta_box_content() {
		global $post;
		
		wp_nonce_field( plugin_basename( __FILE__ ), 'narnoo_imported_media_nonce' );
		?>
		<div id="narnoo-imported-media-tabs-div">
			<ul id="narnoo-imported-media-tabs" class="narnoo-tabs">
				<li class="narnoo-tab-active"><a href="#narnoo-imported-media-albums"><?php _e( 'Albums', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></a></li>
				<li class="hide-if-no-js"><a href="#narnoo-imported-media-images"><?php _e( 'Images', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></a></li>
				<li class="hide-if-no-js"><a href="#narnoo-imported-media-brochures"><?php _e( 'Brochures', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></a></li>
				<li class="hide-if-no-js"><a href="#narnoo-imported-media-videos"><?php _e( 'Videos', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></a></li>
				<li class="hide-if-no-js"><a href="#narnoo-imported-media-text"><?php _e( 'Text', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></a></li>
			</ul>
			
			<div id="narnoo-imported-media-albums" class="tabs-panel narnoo-tabs-panel">
				<?php $this->display_table( 'albums' ); ?>
			</div>
			
			<div id="narnoo-imported-media-images" style="display: none;" class="tabs-panel narnoo-tabs-panel">
				<?php $this->display_table( 'images' ); ?>
			</div>

			<div id="narnoo-imported-media-brochures" style="display: none;" class="tabs-panel narnoo-tabs-panel">
				<?php $this->display_table( 'brochures' ); ?>
			</div>

			<div id="narnoo-imported-media-videos" style="display: none;" class="tabs-panel narnoo-tabs-panel">
				<?php $this->display_table( 'videos' ); ?>
			</div>

			<div id="narnoo-imported-media-text" style="display: none;" class="tabs-panel narnoo-tabs-panel">
				<?php $this->display_table( 'text' ); ?>
			</div>

		</div>
		<?php
	}
	
	/**
	 * Enqueue scripts and print out CSS stylesheets for this page.
	 **/
	static function load_scripts( $hook ) {		
		global $post;
		
		$post_types = get_option( 'narnoo_custom_post_types', array() );
		$post_type = ! empty( $post ) ? $post->post_type : '';
		if ( strncmp( $post_type, 'narnoo_', 7 )  === 0 ) {
			$post_type = substr( $post_type, 7 );
		}
		
		if ( $hook !== 'post-new.php' && $hook !== 'post.php' || empty( $post ) || ! array_key_exists( $post_type, $post_types ) ) {	// ensure scripts are only loaded on this Page
			return;
		}
		
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery.tablednd', plugins_url( 'js/jquery.tablednd.0.6.min.js', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_script( 'imported_media_meta_box.js', plugins_url( 'js/imported_media_meta_box.js', __FILE__ ), array( 'jquery', 'jquery.tablednd' ) );
		?>
		<style type="text/css">
		ul.narnoo-tabs {
			margin-top: 12px;
			margin-bottom: 3px;
			list-style: none;
		}
		
		ul.narnoo-tabs li {
			display: inline;
			padding: 5px;
			-moz-border-radius: 3px 3px 0 0;
			-webkit-border-top-left-radius: 3px;
			-webkit-border-top-right-radius: 3px;
			-khtml-border-top-left-radius: 3px;
			-khtml-border-top-right-radius: 3px;
			border-top-left-radius: 3px;
			border-top-right-radius: 3px;
		}
		
		ul.narnoo-tabs li.narnoo-tab-active {
			border-style: solid solid none;
			border-width: 1px 1px 0;
		}
		
		ul.narnoo-tabs a {
			text-decoration: none;
		}
		
		ul.narnoo-tabs li.narnoo-tab-active {
			background-color: #FFF;
		}
		
		ul.narnoo-tabs li.narnoo-tab-active, div.narnoo-tab-content {
			border-color: #D1E5EE;
		}
		
		.narnoo-tabs .narnoo-tab-active a {
			color: #333;
		}
		
		.narnoo-tabs-panel {
			max-height: 800px;
			overflow-y: auto;
			padding: 10px;
		}

		.narnoo-tabs-panel table {
			border-collapse: collapse;
			width: 99%;
		}

		.narnoo-tabs-panel table,
		.narnoo-tabs-panel tr,
		.narnoo-tabs-panel th,
		.narnoo-tabs-panel td {
			border: 1px solid black;
		}
		
		.narnoo-tabs-panel th,
		.narnoo-tabs-panel td {
			padding: 3px;
		}
		
		.narnoo_array_index_th {
			width: 70px;
		}
		
		.narnoo_array_index {
			width: 60px;
		}
		
		.narnoo_array_index_td {
			cursor: move;
			text-align: center;
		}
		
		.tDnD_whileDrag {
			background-color: #eee;
		}
		</style>
		<?php
	}	
}
