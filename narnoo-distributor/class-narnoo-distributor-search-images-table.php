<?php
/**
 * Narnoo Distributor - Search Images table.
 **/
class Narnoo_Distributor_Search_Images_Table extends Narnoo_Distributor_Search_Media_Table {
	public $search_media_type = 'image';
	
	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'caption':
			case 'entry_date':
			case 'image_id':
			case 'owner':
			case 'operator_id':
				return $item[ $column_name ];
			default:
				return print_r( $item, true );
		}
	}

	function column_thumbnail_image( $item ) {    
		$actions = array(
			'download'    	=> sprintf( 
									'<a href="?%s">%s</a>', 
									build_query( 
										array(
											'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
											'paged' => $this->get_pagenum(),
											'action' => 'download', 
											'search_media_type'  => $this->search_media_type,
											'search_media_id'    => $this->search_media_id,
											'search_category'    => $this->search_category,
											'search_subcategory' => $this->search_subcategory,
											'search_suburb'      => $this->search_suburb,
											'search_location'    => $this->search_location,
											'search_latitude'    => $this->search_latitude,
											'search_longitude'   => $this->search_longitude,
											'search_radius'      => $this->search_radius,
											'search_privilege'   => $this->search_privilege,
											'search_keywords'    => $this->search_keywords,											
											'operators[]' => $item['operator_id'],
											'images[]' => $item['image_id'], 
										)
									),
									__( 'Download', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) 
								),
		);
		return sprintf( 
			'<input type="hidden" name="url%1$s" value="%2$s" /> %3$s <br /> %4$s', 
			$item['image_id'],
			$item['thumbnail_image'],
			"<img src='" . $item['thumbnail_image'] . "' />", 
			$this->row_actions($actions) 
		);
	}
	
	function column_cb($item) {
		return sprintf(
			'<input type="checkbox" class="item-id-cb" name="images[]" value="%s" /><input type="checkbox" name="operators[]" value="%s" style="display: none;" />', 
			$item['image_id'], $item['operator_id']
		);    
	}
	
	function get_columns() {
		return array(
			'cb'				=> '<input type="checkbox" />',
			'thumbnail_image'	=> __( 'Thumbnail', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'owner'             => __( 'Owner', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'caption'			=> __( 'Caption', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'entry_date'		=> __( 'Entry Date', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'image_id'			=> __( 'Image ID', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'operator_id'		=> __( 'Operator ID', NARNOO_DISTRIBUTOR_I18N_DOMAIN )
		);
	}
	
	function get_bulk_actions() {
		$actions = array(
			'download'		=> __( 'Download', NARNOO_DISTRIBUTOR_I18N_DOMAIN )
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
		
		if ( isset( $_REQUEST['back'] ) || isset( $_REQUEST['search-submit'] ) ) {
			return true;
		}

		$action = $this->current_action();
		if ( false !== $action ) {
			$image_ids = isset( $_REQUEST['images'] ) ? $_REQUEST['images'] : array();
			$operator_ids = empty( $_REQUEST['operators'] ) ? array() : $_REQUEST['operators'];
			$num_ids = count( $image_ids );
			if ( empty( $image_ids ) || ! is_array( $image_ids ) || $num_ids === 0 || ! is_array( $operator_ids ) || $num_ids !== count( $operator_ids ) ) {
				return true;				
			}
			
			switch ( $action ) {
			
				// perform download
				case 'download':					
					?>
					<h3><?php _e( 'Download', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></h3>
					<p><?php echo sprintf( __( "Requesting download links for the following %s image(s):", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $num_ids ); ?></p>
					<ol>
					<?php
					foreach( $image_ids as $index => $id ) {
						Narnoo_Distributor_Helper::print_operator_ajax_script_body( $id, 'downloadImage', array( $operator_ids[ $index ], $id ) );
					}
					?>
					</ol>
					<?php 
					Narnoo_Distributor_Helper::print_ajax_script_footer( $num_ids, __( 'Back to images', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) );

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
		
		$list = parent::get_current_page_data();
		
		if ( ! is_null( $list ) ) {
			$data['total_pages'] = max( 1, intval( $list->total_pages ) );
			foreach ( $list->search_media as $image ) {
				$item['owner'] = $image->media_owner_business_name;
				$item['thumbnail_image'] = $image->thumb_media_path;
				$item['caption'] = $image->media_caption;
				$item['entry_date'] = $image->entry_date;
				$item['image_id'] = $image->media_id;
				$item['operator_id'] = $image->operator_id;
				$data['items'][] = $item;
			}
		}

		return $data;
	}
}    