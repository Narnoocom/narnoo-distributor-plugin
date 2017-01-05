<?php
/**
 * Narnoo Distributor - Search/Add Operators table.
 **/
class Narnoo_Distributor_Search_Add_Operators_Table extends WP_List_Table {
	
	public $func_type = 'search';
	
	public $search_country     = '',
	       $search_category    = '',
	       $search_subcategory = '',
	       $search_state       = '',
	       $search_suburb      = '',
		   $search_postal_code = '';
		
	function __construct( $args = array() ) {
		parent::__construct( $args );
		
		$this->search_country      = isset( $_POST['search-submit'] ) && isset( $_POST['search_country'    ] ) ? trim( $_POST['search_country'    ] ) : ( isset( $_GET['search_country'    ] ) ? trim( $_GET['search_country'    ] ) : $this->search_country     );
		$this->search_category     = isset( $_POST['search-submit'] ) && isset( $_POST['search_category'   ] ) ? trim( $_POST['search_category'   ] ) : ( isset( $_GET['search_category'   ] ) ? trim( $_GET['search_category'   ] ) : $this->search_category    );
		$this->search_subcategory  = isset( $_POST['search-submit'] ) && isset( $_POST['search_subcategory'] ) ? trim( $_POST['search_subcategory'] ) : ( isset( $_GET['search_subcategory'] ) ? trim( $_GET['search_subcategory'] ) : $this->search_subcategory );
		$this->search_state        = isset( $_POST['search-submit'] ) && isset( $_POST['search_state'      ] ) ? trim( $_POST['search_state'      ] ) : ( isset( $_GET['search_state'      ] ) ? trim( $_GET['search_state'      ] ) : $this->search_state       );
		$this->search_suburb       = isset( $_POST['search-submit'] ) && isset( $_POST['search_suburb'     ] ) ? trim( $_POST['search_suburb'     ] ) : ( isset( $_GET['search_suburb'     ] ) ? trim( $_GET['search_suburb'     ] ) : $this->search_suburb      );
		$this->search_postal_code  = isset( $_POST['search-submit'] ) && isset( $_POST['search_postal_code'] ) ? trim( $_POST['search_postal_code'] ) : ( isset( $_GET['search_postal_code'] ) ? trim( $_GET['search_postal_code'] ) : $this->search_postal_code );
	}
	
	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'operator_id':
			case 'category':
			case 'sub_category':
			case 'operator_businessname':
			case 'country_name':
			case 'state':
			case 'suburb':
			case 'latitude':
			case 'longitude':
			case 'postcode':
			case 'keywords':
				return $item[ $column_name ];
			default:
				return print_r( $item, true );
		}
	}

	function column_operator_businessname( $item ) {   
		$actions = array(
			'add'    	=> sprintf( 
									'<a href="?%s">%s</a>', 
									build_query( 
										array(
											'page'               => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
											'paged'              => $this->get_pagenum(),
											'func_type'          => $this->func_type,
											'action'             => 'add', 
											'operators[]'        => $item['operator_id'], 
											'operator_names[]'   => $item['operator_businessname'],
											'search_country'     => $this->search_country,
											'search_category'    => $this->search_category,
											'search_subcategory' => $this->search_subcategory,
											'search_state'       => $this->search_state,
											'search_suburb'      => $this->search_suburb,
											'search_postal_code' => $this->search_postal_code
										)
									),
									__( 'Add', NARNOO_DISTRIBUTOR_I18N_DOMAIN )
								),
		);
		
		return sprintf( 
			'%1$s <br /> %2$s', 
			$item['operator_businessname'], 
			$this->row_actions($actions) 
		);
	}
	
	function column_cb($item) {
		return sprintf(
			'<input class="operator-id-cb" type="checkbox" name="operators[]" value="%s" /><input class="operator-name-cb" type="checkbox" name="operator_names[]" value="%s" style="display: none;" />', 
			$item['operator_id'], esc_attr( $item['operator_businessname'] )
		);    
	}

	function get_columns() {
		return array(
			'cb'                    => '<input type="checkbox" />',
			'operator_businessname' => __( 'Business', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'operator_id'           => __( 'ID', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'category'              => __( 'Category', NARNOO_DISTRIBUTOR_I18N_DOMAIN ), 
			'sub_category'          => __( 'Subcategory', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'country_name'          => __( 'Country', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'state'                 => __( 'State', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			//'suburb'                => __( 'Suburb', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'latitude'              => __( 'Latitude', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'longitude'             => __( 'Longitude', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'postcode'              => __( 'Postcode', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'keywords'              => __( 'Keywords', NARNOO_DISTRIBUTOR_I18N_DOMAIN )
		);
	}
	
	function get_bulk_actions() {
		$actions = array(
			'add'		=> __( 'Add', NARNOO_DISTRIBUTOR_I18N_DOMAIN )
		);
		return $actions;
	}

	/**
	 * Process actions and returns true if the rest of the table SHOULD be rendered.
	 * Returns false otherwise.
	 **/
	function process_action() {
		if ( isset( $_REQUEST['cancel'] ) ) {
			Narnoo_Distributor_Helper::show_notification( __( 'Action cancelled.', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) );
			return true;
		}
		
		if ( isset( $_REQUEST['back'] ) ) {
			return true;
		}
				
		$action = $this->current_action();
		if ( isset( $_REQUEST['add_list'] ) ) {
			$action = 'add';
		} else if ( isset( $_REQUEST['search-submit'] ) ) {
			$action = false;
		}
		if ( false !== $action ) {
			if ( isset( $_REQUEST['add_list'] ) ) {
				$operator_ids = empty( $_REQUEST['add_operators_list'] ) ? array() : explode( ',', $_REQUEST['add_operators_list'] );
			} else {
				$operator_ids = empty( $_REQUEST['operators'] ) ? array() : $_REQUEST['operators'];
			}
			
			$num_ids = count( $operator_ids );
			if ( empty( $operator_ids ) || ! is_array( $operator_ids ) || $num_ids === 0 ) {
				if ( isset( $_REQUEST['add_list'] ) ) {
					Narnoo_Distributor_Helper::show_error( __( 'Please key in the IDs of one or more operators to add.', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) );
				}
				return true;				
			}
			
			if ( ! isset( $_REQUEST['add_list'] ) ) {
				$operator_names = isset( $_REQUEST['operator_names'] ) ? $_REQUEST['operator_names'] : array();
				$num_names = count( $operator_names );
				if ( empty( $operator_names ) || ! is_array( $operator_names ) || $num_names !== $num_ids ) {
					return true;				
				}
				foreach( $operator_names as $key => $operator_name ) {
					$operator_names[ $key ] = stripslashes( $operator_name );
				}
			}
			
			switch ( $action ) {
			
				// perform actual add
				case 'add':
					?>
					<h3><?php _e( 'Add' ); ?></h3>
					<p><?php echo sprintf( __( "Adding the following %s operator(s):", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $num_ids ); ?></p>
					<ol>
					<?php
					foreach( $operator_ids as $key => $id ) {
						$id = trim( $id );
						Narnoo_Distributor_Helper::print_ajax_script_body( 
							$id, 'addOperator', array( $id ),
							'ID #' . $id . ( isset( $_REQUEST['add_list']) ? '' : ': ' . esc_html( $operator_names[ $key ] ) )
						);
					}
					?>
					</ol>
					<?php 
					Narnoo_Distributor_Helper::print_ajax_script_footer( $num_ids, __( 'Back to operators', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) );

					return false;
					
			} 	// end switch( $action )
		}	// endif ( false !== $action )
		
		return true;
	}
	
	/**
	 * Request the current page data from Narnoo API server.
	 **/
	function get_current_page_data() {
		$data = array( 'total_pages' => 1, 'items' => array() );
		
		$list = null;
		$current_page = $this->get_pagenum();


        $request = Narnoo_Distributor_Helper::init_api('operator');

		if ( ! is_null( $request ) ) {
			
			try {
				

				$list = $request->searchOperators( $this->search_country, $this->search_category, $this->search_subcategory, $this->search_state, $this->search_suburb, $this->search_postal_code, $current_page );
				
				if ( ! is_array( $list->search_operators ) ) {
					throw new Exception( sprintf( __( "Error retrieving operators. Unexpected format in response page #%d.", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $current_page ) );
				}
		

			} catch ( Exception $ex ) {
				Narnoo_Distributor_Helper::show_api_error( $ex );
			} 
		

		}
		
		if ( ! is_null( $list ) ) {
			$data['total_pages'] = max( 1, intval( $list->total_pages ) );
			foreach ( $list->search_operators as $operator ) {
				$item['operator_id'          ] = $operator->operator_id;
				$item['category'             ] = $operator->category;
				$item['sub_category'         ] = $operator->sub_category;
				$item['operator_businessname'] = $operator->operator_businessname;
				$item['country_name'         ] = $operator->country_name;
				$item['state'                ] = $operator->state;
				$item['suburb'               ] = $operator->suburb;
				$item['latitude'             ] = $operator->latitude;
				$item['longitude'            ] = $operator->longitude;
				$item['postcode'             ] = $operator->postcode;
				$item['keywords'             ] = $operator->keywords;
				$data['items'][] = $item;				
			}
		}

		return $data;
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
			
		//$data = $this->get_current_page_data();
		//$this->items = $data['items'];
		
		$this->set_pagination_args( array(
		//	'total_items'	=> count( $data['items'] ),
		//	'total_pages'	=> $data['total_pages']
		) );  
		
		?>
		<p class="narnoo-operators-add-operators-box">
			<label for="add_operators_list"><?php _e( 'Enter IDs of operators to add (separate each ID by a comma e.g. <code>12, 13</code>):', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></label>
			<input id="add_operators_list" name="add_operators_list" type="text" value="<?php echo isset( $_REQUEST['add_operators_list'] ) ? esc_attr( $_REQUEST['add_operators_list'] ) : ''; ?>" />
			<input id="add_list" class="button" type="submit" name="add_list" value="<?php _e( 'add', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?>" />
		</p>
		<p class="narnoo-search-operators-box">
			<span>Or search for operators using the form below:</span><br />
			<label for="search_country"><?php _e( 'country:', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></label> 
			<input id="search_country" name="search_country" type="text" value="<?php echo esc_attr( $this->search_country ); ?>" /><br /> 
			<label for="search_category"><?php _e( 'category:', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></label> 
			<input id="search_category" name="search_category" type="text" value="<?php echo esc_attr( $this->search_category ); ?>" /><br /> 
			<label for="search_subcategory"><?php _e( 'subcategory:', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></label> 
			<input id="search_subcategory" name="search_subcategory" type="text" value="<?php echo esc_attr( $this->search_subcategory ); ?>" /><br /> 
			<label for="search_state"><?php _e( 'state:', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></label> 
			<input id="search_state" name="search_state" type="text" value="<?php echo esc_attr( $this->search_state ); ?>" /><br /> 
			<label for="search_suburb"><?php _e( 'suburb:', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></label> 
			<input id="search_suburb" name="search_suburb" type="text" value="<?php echo esc_attr( $this->search_suburb ); ?>" /><br /> 
			<label for="search_postal_code"><?php _e( 'postal_code:', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></label> 
			<input id="search_postal_code" name="search_postal_code" type="text" value="<?php echo esc_attr( $this->search_postal_code ); ?>" />
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
				$('#search-submit').click(function(e, ui) {
					// rebuild form action query string to ensure search params are in sync
					$form = $('#narnoo-search-add-operators-form');
					if ($form.length > 0) {
						new_query = $form.attr('action');
						new_query = updateQueryStringParameter( new_query, 'search_country'    , $('#search_country'    ).val() );
						new_query = updateQueryStringParameter( new_query, 'search_category'   , $('#search_category'   ).val() );
						new_query = updateQueryStringParameter( new_query, 'search_subcategory', $('#search_subcategory').val() );
						new_query = updateQueryStringParameter( new_query, 'search_state'      , $('#search_state'      ).val() );
						new_query = updateQueryStringParameter( new_query, 'search_suburb'     , $('#search_suburb'     ).val() );
						new_query = updateQueryStringParameter( new_query, 'search_postal_code', $('#search_postal_code').val() );
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
	 * Add screen options for Search/Add operators page.
	 **/
	static function add_screen_options() {
		global $narnoo_distributor_operators_table;
		$narnoo_distributor_operators_table = new Narnoo_Distributor_Search_Add_Operators_Table();
	}
	
	/**
	 * Enqueue scripts and print out CSS stylesheets for this page.
	 **/
	static function load_scripts( $hook ) {
		global $narnoo_distributor_operators_page;
		
		if ( $narnoo_distributor_operators_page !== $hook || ! isset( $_REQUEST['func_type'] ) || $_REQUEST['func_type'] !== 'search' ) {	// ensure scripts are only loaded on this Page
			return;
		}
		
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'search_add_operators_table.js', plugins_url( 'js/search_add_operators_table.js', __FILE__ ), array( 'jquery' ) );
		
		?>
		<style type="text/css">
		.wp-list-table .column-operator_businessname { width: 15%; }
		.wp-list-table .column-operator_id { width: 5%; }
		.wp-list-table .column-category { width: 10%; }
		.wp-list-table .column-state { width: 5%; }
		.narnoo-search-operators-box label { display: inline-block; padding-left: 50px; width: 100px; }
		</style>
		<?php
	}	
}    