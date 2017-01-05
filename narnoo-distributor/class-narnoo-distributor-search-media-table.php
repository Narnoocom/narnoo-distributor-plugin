<?php
/**
 * Narnoo Distributor - Search media table.
 **/
class Narnoo_Distributor_Search_Media_Table extends WP_List_Table {
	
	public $search_media_id    = '',
	       $search_category    = '',
	       $search_subcategory = '',
	       $search_suburb      = '',
		   $search_location    = '',
		   $search_latitude    = '',
		   $search_longitude   = '',
		   $search_radius      = '',
		   $search_privilege   = '',
		   $search_keywords    = '';
		
	function __construct( $args = array() ) {
		parent::__construct( $args );
		
		$this->search_media_id     = isset( $_POST['search-submit'] ) && isset( $_POST['search_media_id'   ] ) ? trim( $_POST['search_media_id'   ] ) : ( isset( $_GET['search_media_id'   ] ) ? trim( $_GET['search_media_id'   ] ) : $this->search_media_id );
		if ( empty( $this->search_media_id ) ) {
			$this->search_category     = isset( $_POST['search-submit'] ) && isset( $_POST['search_category'   ] ) ? trim( $_POST['search_category'   ] ) : ( isset( $_GET['search_category'   ] ) ? trim( $_GET['search_category'   ] ) : $this->search_category    );
			$this->search_subcategory  = isset( $_POST['search-submit'] ) && isset( $_POST['search_subcategory'] ) ? trim( $_POST['search_subcategory'] ) : ( isset( $_GET['search_subcategory'] ) ? trim( $_GET['search_subcategory'] ) : $this->search_subcategory );
			$this->search_suburb       = isset( $_POST['search-submit'] ) && isset( $_POST['search_suburb'     ] ) ? trim( $_POST['search_suburb'     ] ) : ( isset( $_GET['search_suburb'     ] ) ? trim( $_GET['search_suburb'     ] ) : $this->search_suburb      );
			$this->search_location     = isset( $_POST['search-submit'] ) && isset( $_POST['search_location'   ] ) ? trim( $_POST['search_location'   ] ) : ( isset( $_GET['search_location'   ] ) ? trim( $_GET['search_location'   ] ) : $this->search_location    );
			$this->search_latitude     = isset( $_POST['search-submit'] ) && isset( $_POST['search_latitude'   ] ) ? trim( $_POST['search_latitude'   ] ) : ( isset( $_GET['search_latitude'   ] ) ? trim( $_GET['search_latitude'   ] ) : $this->search_latitude    );
			$this->search_longitude    = isset( $_POST['search-submit'] ) && isset( $_POST['search_longitude'  ] ) ? trim( $_POST['search_longitude'  ] ) : ( isset( $_GET['search_longitude'  ] ) ? trim( $_GET['search_longitude'  ] ) : $this->search_longitude   );
			$this->search_radius       = isset( $_POST['search-submit'] ) && isset( $_POST['search_radius'     ] ) ? trim( $_POST['search_radius'     ] ) : ( isset( $_GET['search_radius'     ] ) ? trim( $_GET['search_radius'     ] ) : $this->search_radius      );
			$this->search_privilege    = isset( $_POST['search-submit'] ) && isset( $_POST['search_privilege'  ] ) ? trim( $_POST['search_privilege'  ] ) : ( isset( $_GET['search_privilege'  ] ) ? trim( $_GET['search_privilege'  ] ) : $this->search_privilege   );
			$this->search_keywords     = isset( $_POST['search-submit'] ) && isset( $_POST['search_keywords'   ] ) ? trim( $_POST['search_keywords'   ] ) : ( isset( $_GET['search_keywords'   ] ) ? trim( $_GET['search_keywords'   ] ) : $this->search_keywords    );
		}
	}
	
	/**
	 * Request the current page data from Narnoo API server.
	 **/
	function get_current_page_data() {		
		$list = null;
		
		if ( ! isset( $_REQUEST['search_media_type'] ) ) {
			return $list;
		}

		$current_page = $this->get_pagenum();
		$request = Narnoo_Distributor_Helper::init_api( 'media' );
		if ( ! is_null( $request ) ) {
			try {
				$list = $request->searchMedia( $this->search_media_type, $this->search_media_id, $this->search_category, $this->search_subcategory, $this->search_suburb, $this->search_location, $this->search_latitude, $this->search_longitude, $this->search_radius, $this->search_privilege, $this->search_keywords, $current_page );
				if ( ! is_array( $list->search_media ) ) {
					throw new Exception( sprintf( __( "Error retrieving media. Unexpected format in response page #%d.", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $current_page ) );
				}
			} catch ( Exception $ex ) {
				Narnoo_Distributor_Helper::show_api_error( $ex );
			} 
		}
		
		return $list;
	}

	/**
	 * Process any actions (displaying forms for the actions as well).
	 * If the table SHOULD be rendered after processing (or no processing occurs), prepares the data for display and returns true. 
	 * Otherwise, returns false.
	 **/
	function prepare_items() {		
		if ( ! $this->process_action() ) {
			return false;
		}
		
		$this->_column_headers = $this->get_column_info();
			
		$data = $this->get_current_page_data();
		$this->items = $data['items'];
		
		$this->set_pagination_args( array(
			'total_items'	=> count( $data['items'] ),
			'total_pages'	=> $data['total_pages']
		) );  
		
		?>
		<p class="narnoo-search-media-box">
			<span>Search for media using the form below:</span><br />
			<label for="search_media_type">media_type</label> 
			<select id="search_media_type" name="search_media_type">
				<option value="image"<?php echo $this->search_media_type === 'image' ? ' selected="selected"' : ''; ?>><?php _e( 'image', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></option>
				<option value="brochure"<?php echo $this->search_media_type === 'brochure' ? ' selected="selected"' : ''; ?>><?php _e( 'brochure', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></option>
				<option value="video"<?php echo $this->search_media_type === 'video' ? ' selected="selected"' : ''; ?>><?php _e( 'video', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></option>
			</select><br />
			<label for="search_media_id"><?php _e( 'media_id:', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></label> 
			<input class="narnoo_text" id="search_media_id" name="search_media_id" type="text" value="<?php echo esc_attr( $this->search_media_id ); ?>" /><br /> 
			<?php $disabled = empty( $this->search_media_id ) ? '' : ' disabled="disabled"'; ?>
			<label for="search_category"><?php _e( 'category:', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></label> 
			<input <?php echo $disabled; ?>class="narnoo_text" id="search_category" name="search_category" type="text" value="<?php echo esc_attr( $this->search_category ); ?>" /><br /> 
			<label for="search_subcategory"><?php _e( 'subcategory:', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></label> 
			<input <?php echo $disabled; ?>class="narnoo_text" id="search_subcategory" name="search_subcategory" type="text" value="<?php echo esc_attr( $this->search_subcategory ); ?>" /><br /> 
			<label for="search_suburb"><?php _e( 'suburb:', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></label> 
			<input <?php echo $disabled; ?>class="narnoo_text" id="search_suburb" name="search_suburb" type="text" value="<?php echo esc_attr( $this->search_suburb ); ?>" /><br /> 
			<label for="search_location"><?php _e( 'location:', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></label> 
			<input <?php echo $disabled; ?>class="narnoo_text" id="search_location" name="search_location" type="text" value="<?php echo esc_attr( $this->search_location ); ?>" /><br /> 
			<label for="search_latitude"><?php _e( 'latitude:', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></label> 
			<input <?php echo $disabled; ?>class="narnoo_text" id="search_latitude" name="search_latitude" type="text" value="<?php echo esc_attr( $this->search_latitude ); ?>" /><br /> 
			<label for="search_longitude"><?php _e( 'longitude:', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></label> 
			<input <?php echo $disabled; ?>class="narnoo_text" id="search_longitude" name="search_longitude" type="text" value="<?php echo esc_attr( $this->search_longitude ); ?>" /><br /> 
			<label for="search_radius"><?php _e( 'radius:', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></label> 
			<input <?php echo $disabled; ?>class="narnoo_text" id="search_radius" name="search_radius" type="text" value="<?php echo esc_attr( $this->search_radius ); ?>" /><br /> 
			<label for="search_privilege"><?php _e( 'privilege:', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></label> 
			<input <?php echo $disabled; ?>class="narnoo_radio" type="radio" id="search_privilege_public" name="search_privilege" value="public"<?php echo $this->search_privilege === 'public' ? ' checked="checked"' : ''; ?> /><label class="narnoo_radio_label" for="search_privilege_public">public</label>
			<input <?php echo $disabled; ?>class="narnoo_radio" type="radio" id="search_privilege_private" name="search_privilege" value="private"<?php echo $this->search_privilege === 'private' ? ' checked="checked"' : ''; ?> /><label class="narnoo_radio_label" for="search_privilege_private">private</label><br />
			<label for="search_keywords"><?php _e( 'keywords:', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></label> 
			<input <?php echo $disabled; ?>class="narnoo_text" id="search_keywords" name="search_keywords" type="text" value="<?php echo esc_attr( $this->search_keywords ); ?>" />
			<input id="search-submit" class="button" type="submit" name="search-submit" value="<?php _e( 'search', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?>" />
		</p>
		<script type="text/javascript">
			function updateQueryStringParameter(uri, key, value) {
				var re = new RegExp("([?|&])" + key + "=.*?(&|$)", "i");
				separator = uri.indexOf('?') !== -1 ? "&" : "?";
				value = encodeURIComponent(value);
				if (uri.match(re)) {
					return uri.replace(re, '$1' + key + "=" + value + '$2');
				}
				else {
					return uri + separator + key + "=" + value;
				}
			}
			
			jQuery('document').ready(function($) {
				$('#search_media_id').blur(function(){
					var id = $.trim($(this).val());
					if(id == ''){
						$('.narnoo_text').not($(this)).removeAttr('disabled');
						$('.narnoo_radio').removeAttr('disabled');
					}else{
						$('.narnoo_text').not($(this)).attr('disabled', 'disabled').val('');
						$('.narnoo_radio').attr('disabled', 'disabled').removeAttr('checked');
					}
				});

				$('#search-submit').click(function(e, ui) {
					// rebuild form action query string to ensure search params are in sync
					var $privilege = $('input[name=search_privilege]:checked:not(:disabled)');
					var privilege = $privilege.length > 0 ? $privilege.val() : '';
					$form = $('#narnoo-search-media-form');
					if ($form.length > 0) {
						new_query = $form.attr('action');
						new_query = updateQueryStringParameter( new_query, 'search_media_type' , $('#search_media_type' ).val() );
						new_query = updateQueryStringParameter( new_query, 'search_media_id'   , $('#search_media_id'   ).val() );
						new_query = updateQueryStringParameter( new_query, 'search_category'   , $('#search_category'   ).val() );
						new_query = updateQueryStringParameter( new_query, 'search_subcategory', $('#search_subcategory').val() );
						new_query = updateQueryStringParameter( new_query, 'search_suburb'     , $('#search_suburb'     ).val() );
						new_query = updateQueryStringParameter( new_query, 'search_location'   , $('#search_location'   ).val() );
						new_query = updateQueryStringParameter( new_query, 'search_latitude'   , $('#search_latitude'   ).val() );
						new_query = updateQueryStringParameter( new_query, 'search_longitude'  , $('#search_longitude'  ).val() );
						new_query = updateQueryStringParameter( new_query, 'search_radius'     , $('#search_radius'     ).val() );
						new_query = updateQueryStringParameter( new_query, 'search_privilege'  , privilege );
						new_query = updateQueryStringParameter( new_query, 'search_keywords'   , $('#search_keywords'   ).val() );
						new_query = updateQueryStringParameter( new_query, 'paged', '1' );
						$form.attr('action', new_query);
					}
				});
			});
		</script>
		<?php	

		return true;		
	}
	
	/**
	 * Add screen options for search media page.
	 **/
	static function add_screen_options() {
		global $narnoo_distributor_search_media_table;
		
		$search_media_type = isset( $_POST['search-submit'] ) && isset( $_POST['search_media_type'] ) ? trim( $_POST['search_media_type'] ) : ( isset( $_GET['search_media_type'] ) ? trim( $_GET['search_media_type'] ) : 'image' );
		switch ( $search_media_type ) {
			case 'image': $narnoo_distributor_search_media_table = new Narnoo_Distributor_Search_Images_Table(); break;
			case 'video': $narnoo_distributor_search_media_table = new Narnoo_Distributor_Search_Videos_Table(); break;
			case 'brochure': $narnoo_distributor_search_media_table = new Narnoo_Distributor_Search_Brochures_Table(); break;
		}
	}
	
	/**
	 * Enqueue scripts and print out CSS stylesheets for this page.
	 **/
	static function load_scripts( $hook ) {
		global $narnoo_distributor_search_media_page;
		
		if ( $narnoo_distributor_search_media_page !== $hook ) {	// ensure scripts are only loaded on this Page
			return;
		}
		
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'operator_search_table.js', plugins_url( 'js/operator_search_table.js', __FILE__ ), array( 'jquery' ) );		
		
		?>
		<style type="text/css">
		.narnoo_radio_label { padding-left: 0px !important; }
		.narnoo-search-media-box label { display: inline-block; padding-left: 50px; width: 75px; }
		.narnoo_text:disabled {	background-color: #EBEBE4; }
		</style>
		<?php
	}	
}    