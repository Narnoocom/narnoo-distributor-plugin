<?php
/**
 * Narnoo Operator - Search Videos table.
 **/
class Narnoo_Distributor_Search_Videos_Table extends Narnoo_Distributor_Search_Media_Table {
	public $search_media_type = 'video';

	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'caption':
			case 'entry_date':
			case 'video_id':
			case 'operator_id':
				return $item[ $column_name ];
			default:
				return print_r( $item, true );
		}
	}

	function column_thumbnail_image( $item ) {    
		$actions = array(
			'add_to_channel'  => sprintf( 
									'<a href="?%s">%s</a>', 
									build_query( 
										array(
											'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
											'paged' => $this->get_pagenum(),
											'action' => 'add_to_channel', 
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
											'videos[]' => $item['video_id'], 
											'operators[]' => $item['operator_id'],
											'url' . $item['video_id'] => $item['thumbnail_image']
										)
									),
									__( 'Add to channel', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) 
								),
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
											'videos[]' => $item['video_id'], 
										)
									),
									__( 'Download', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) 
								),
		);
		return sprintf( 
			'<input type="hidden" name="url%1$s" value="%2$s" /> %3$s <br /> %4$s', 
			$item['video_id'],
			$item['thumbnail_image'],
			"<img src='" . $item['thumbnail_image'] . "' />", 
			$this->row_actions($actions) 
		);
	}
	
	function column_cb($item) {
		return sprintf(
			'<input type="checkbox" class="item-id-cb" name="videos[]" value="%s" /><input type="checkbox" name="operators[]" value="%s" style="display: none;" />', 
			$item['video_id'], $item['operator_id']
		);    
	}								
	function get_columns() {
		return array(
			'cb'				=> '<input type="checkbox" />',
			'thumbnail_image'	=> __( 'Thumbnail', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'caption'			=> __( 'Caption', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'entry_date'		=> __( 'Entry Date', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'video_id'		    => __( 'Video ID', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'operator_id'		=> __( 'Operator ID', NARNOO_DISTRIBUTOR_I18N_DOMAIN )
		);
	}

	function get_bulk_actions() {
		$actions = array(
			'add_to_channel'=> __( 'Add to channel', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
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
		
		if ( isset( $_REQUEST['extra_button'] ) ) {
			// redirect to channel page if user clicked "View channel" after adding videos
			if ( isset( $_REQUEST['view_channel'] ) && $_REQUEST['view_channel'] === 'view_channel' ) {
				?>
				<p><img src="<?php echo admin_url(); ?>images/wpspin_light.gif" /> <?php printf( __( "Redirecting to channel '%s'...", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), htmlspecialchars( stripslashes( $_REQUEST['narnoo_channel_name'] ) ) ); ?></p>
				<script type="text/javascript">
				window.location = "admin.php?page=narnoo-distributor-channels&channel=<?php echo isset( $_REQUEST['narnoo_channel_id'] ) ? $_REQUEST['narnoo_channel_id'] : ''; ?>&channel_name=<?php echo isset( $_REQUEST['narnoo_channel_name'] ) ? urlencode( stripslashes( $_REQUEST['narnoo_channel_name'] ) ) : ''; ?>&channel_page=<?php echo isset( $_REQUEST['narnoo_channel_page'] ) ? $_REQUEST['narnoo_channel_page'] : ''; ?>";
				</script>
				<?php
				exit();
			}				
		}
				
		$action = $this->current_action();
		if ( false !== $action ) {
			$video_ids = isset( $_REQUEST['videos'] ) ? $_REQUEST['videos'] : array();
			$operator_ids = empty( $_REQUEST['operators'] ) ? array() : $_REQUEST['operators'];
			$num_ids = count( $video_ids );
			if ( empty( $video_ids ) || ! is_array( $video_ids ) || $num_ids === 0 || ! is_array( $operator_ids ) || $num_ids !== count( $operator_ids ) ) {
				return true;				
			}
			
			switch ( $action ) {
			
				// confirm add to channel
				case 'add_to_channel':
					// retrieve list of channels		
					$list = null;
					$request = Narnoo_Distributor_Helper::init_api( 'media' );
					if ( ! is_null( $request ) ) {
						try {
							$list = $request->getChannelList();
							if ( ! is_array( $list->distributor_channel_list ) ) {
								throw new Exception( sprintf( __( "Error retrieving channels. Unexpected format in response page #%d.", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $current_page ) );
							}
						} catch ( Exception $ex ) {
							Narnoo_Distributor_Helper::show_api_error( $ex );
						} 				
					}
							
					// no channels retrieved
					if ( is_null( $list ) ) {
						return true;
					}
					if ( count( $list->distributor_channel_list ) === 0 ) {
						Narnoo_Distributor_Helper::show_error( sprintf( __( '<strong>ERROR:</strong> No channels found. Please <strong><a href="%s">create a channel</a></strong> first!', NARNOO_DISTRIBUTOR_I18N_DOMAIN ), "?" . build_query( array( 'page' => 'narnoo-distributor-channels', 'action' => 'create' ) ) ) );
						return true;
					}

					$total_pages = max( 1, intval( $list->total_pages ) );
					
					?>
					<h3><?php _e( 'Confirm add to channel', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></h3>
					<?php
					
					foreach ( $list->distributor_channel_list as $channel ) { ?>
						<input type="hidden" name="channel<?php echo $channel->channel_id; ?>" value="<?php echo esc_attr( $channel->channel_name ); ?>" /><?php
					}
					?>
					<p>
						<?php printf( __( 'Please select channel to add the following %d video(s) to:', NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $num_ids ); ?>
						<?php echo Narnoo_Distributor_Helper::get_channel_select_html_script( $list->distributor_channel_list, $total_pages, 1, '' ); ?>
					</p>
					<ol>
					<?php 
					foreach ( $video_ids as $id ) { 
						?>
						<input type="hidden" name="videos[]" value="<?php echo $id; ?>" />
						<li><span><?php echo __( 'Video ID:', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) . ' ' . $id; ?></span><span><img style="vertical-align: middle; padding-left: 20px;" src="<?php echo ( isset( $_REQUEST[ 'url' . $id ] ) ? $_REQUEST[ 'url' . $id ] : '' ); ?>" /></span></li>
						<?php 
					} 
					?>
					</ol>
					<input type="hidden" name="action" value="do_add_to_channel" />
					<p class="submit">
						<input type="submit" name="submit" id="channel_select_button" class="button-secondary" value="<?php _e( 'Confirm Add to Channel', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?>" />
						<input type="submit" name="cancel" id="cancel" class="button-secondary" value="<?php _e( 'Cancel' ); ?>" />
					</p>
					<?php
					
					return false;
					
				// perform actual add to channel
				case 'do_add_to_channel':
					if ( ! isset( $_POST['narnoo_channel_id'] ) ) {
						return true;
					}
					$channel_id = $_POST['narnoo_channel_id'];
					$channel_name =  isset( $_POST['narnoo_channel_name'] ) ? stripslashes( $_POST['narnoo_channel_name'] ) : '';
					$channel_page = isset( $_POST['narnoo_channel_page'] ) ? $_POST['narnoo_channel_page'] : '';
					
					?>
					<h3><?php _e( 'Add to channel', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></h3>
					<p><?php echo sprintf( __( "Adding the following %s video(s) to channel '%s' (ID %d):", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $num_ids, $channel_name, $channel_id ); ?></p>
					<input type="hidden" name="view_channel" value="view_channel" />
					<input type="hidden" name="narnoo_channel_id" value="<?php echo $channel_id; ?>" />
					<input type="hidden" name="narnoo_channel_name" value="<?php echo esc_attr( $channel_name ); ?>" />
					<input type="hidden" name="narnoo_channel_page" value="<?php echo $channel_page; ?>" />
					<ol>
					<?php
					foreach( $video_ids as $id ) {
						Narnoo_Distributor_Helper::print_media_ajax_script_body( $id, 'addToChannel', array( $id, $channel_id ) );
					}
					?>
					</ol>
					<?php 
					Narnoo_Distributor_Helper::print_ajax_script_footer( $num_ids, __( 'Back to videos', NARNOO_DISTRIBUTOR_I18N_DOMAIN ), __( 'View channel', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) );

					return false;

				// perform download
				case 'download':					
					?>
					<h3><?php _e( 'Download', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></h3>
					<p><?php echo sprintf( __( "Requesting download links for the following %s video(s):", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $num_ids ); ?></p>
					<ol>
					<?php
					foreach( $video_ids as $index => $id ) {
						Narnoo_Distributor_Helper::print_operator_ajax_script_body( $id, 'downloadVideo', array( $operator_ids[ $index ], $id ) );
					}
					?>
					</ol>
					<?php 
					Narnoo_Distributor_Helper::print_ajax_script_footer( $num_ids, __( 'Back to videos', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) );

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
			foreach ( $list->search_media as $video ) {
				$item['thumbnail_image'] = $video->video_thumb_image_path;
				$item['caption'] = $video->video_caption;
				$item['entry_date'] = $video->entry_date;
				$item['video_id'] = $video->video_id;
				$item['operator_id'] = $video->operator_id;
				$data['items'][] = $item;
			}
		}

		return $data;
	}
}    