<?php
/**
 * Narnoo Distributor - Channels table.
 **/
class Narnoo_Distributor_Channels_Table extends WP_List_Table {		
	public $current_channel_id = '0';
	public $current_channel_name = '';
	public $current_channel_page = 1;
	
	public $select_channel_html_script = '';
	
	function __construct( $args = array() ) {
		parent::__construct( $args );
		
		if ( isset( $_POST['narnoo_channel_name'] ) ) {
			if ( isset( $_POST['narnoo_channel_name'] ) ) {
				$this->current_channel_name = stripslashes( $_POST['narnoo_channel_name'] );
			}
			if ( isset( $_POST['narnoo_channel_id'] ) ) {
				$this->current_channel_id = $_POST['narnoo_channel_id'];
			}
			if ( isset( $_POST['narnoo_channel_page'] ) ) {
				$this->current_channel_page = intval( $_POST['narnoo_channel_page'] ); 
			}
		} else {
			if ( isset( $_REQUEST['channel_name'] ) ) {
				$this->current_channel_name = stripslashes( $_REQUEST['channel_name'] );
			}
			if ( isset( $_REQUEST['channel'] ) ) {
				$this->current_channel_id = $_REQUEST['channel'];
			}
			if ( isset( $_REQUEST['channel_page'] ) ) {
				$this->current_channel_page = intval( $_REQUEST['channel_page'] );
			}
		}

		// get the current (or first, if unspecified) page of channels
		$list = null;
		$this->current_channel_page = max( 1, $this->current_channel_page );
		$current_page = $this->current_channel_page;
		$request = Narnoo_Distributor_Helper::init_api( 'media' );
		if ( ! is_null( $request ) ) {
			try {
				$list = $request->getChannelList( $current_page );
				if ( ! is_array( $list->distributor_channel_list ) ) {
					throw new Exception( sprintf( __( "Error retrieving channels. Unexpected format in response page #%d.", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $current_page ) );
				}
			} catch ( Exception $ex ) {
				Narnoo_Distributor_Helper::show_api_error( $ex );
			} 				
		}
		
		if ( ! is_null( $list ) ) {
			$total_pages = max( 1, intval( $list->total_pages ) );
		
			// use current specified channel name if it exists in current page;
			// otherwise set it to first channel name in current page
			$first_channel = null;
			$is_current_channel_name_valid = false;
			foreach ( $list->distributor_channel_list as $channel ) {
				$channel_name = stripslashes( $channel->channel_name );
				if ( is_null( $first_channel ) ) {
					$first_channel = $channel;
				}
				if ( empty( $this->current_channel_name ) ) {
					$this->current_channel_name = $channel_name;
					$this->current_channel_id = $channel->channel_id;
				}
				if ( $this->current_channel_name === $channel_name ) {
					$is_current_channel_name_valid = true;
				}
			}
			
			if ( ! $is_current_channel_name_valid ) {
				Narnoo_Distributor_Helper::show_error( sprintf( __( "<strong>ERROR:</strong> Unknown channel name '%s'.", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $this->current_channel_name ) );
				if ( ! is_null( $first_channel ) ) {
					$this->current_channel_name = stripslashes( $first_channel->channel_name );
					$this->current_channel_id = $first_channel->channel_id;
				}
			}

			$this->select_channel_html_script = Narnoo_Distributor_Helper::get_channel_select_html_script( $list->distributor_channel_list, $total_pages, $this->current_channel_page, $this->current_channel_name );
		}		
		
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'caption':
			case 'entry_date':
			case 'video_id':
				return $item[ $column_name ];
			default:
				return print_r( $item, true );
		}
	}

	function column_thumbnail_image( $item ) {    
		$actions = array(
			'remove'    	=> sprintf( 
									'<a href="?%s">%s</a>', 
									build_query( 
										array(
											'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
											'paged' => $this->get_pagenum(),
											'action' => 'remove', 
											'videos[]' => $item['video_id'], 
											'url' . $item['video_id'] => $item['thumbnail_image'],
											'channel_page' => $this->current_channel_page, 
											'channel' => $this->current_channel_id, 
											'channel_name' => $this->current_channel_name
										)
									),
									__( 'Remove from channel', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) 
								),
			'download'    	=> sprintf( 
									'<a href="?%s">%s</a>', 
									build_query( 
										array(
											'page' => isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '',
											'paged' => $this->get_pagenum(),
											'action' => 'download', 
											'videos[]' => $item['video_id'], 
											'channel_page' => $this->current_channel_page, 
											'channel' => $this->current_channel_id, 
											'channel_name' => $this->current_channel_name
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
			'<input type="checkbox" name="videos[]" value="%s" />', $item['video_id']
		);    
	}

	function get_columns() {
		return array(
			'cb'				=> '<input type="checkbox" />',
			'thumbnail_image'	=> __( 'Thumbnail', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'caption'			=> __( 'Caption', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'entry_date'		=> __( 'Entry Date', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
			'video_id'			=> __( 'Video ID', NARNOO_DISTRIBUTOR_I18N_DOMAIN )
		);
	}
	
	function get_bulk_actions() {
		$actions = array(
			'remove'    	=> __( 'Remove from channel', NARNOO_DISTRIBUTOR_I18N_DOMAIN ),
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
		
		if ( isset( $_REQUEST['back'] ) || isset( $_REQUEST['channel_select_button'] ) ) {
			return true;
		}
		
		$action = $this->current_action();
		if ( false !== $action ) {
			if ( $action === 'create' ) {
				?>
				<h3><?php _e( 'Create channel', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></h3>
				<table class="form-table">
					<tr>
						<th><?php _e( "Please key in a new channel name:", NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></th>
						<td><input type="text" class="regular-text" name="new_channel_name" id="new_channel_name" /></td>
					</tr>
				</table>
				<input type="hidden" name="action" value="do_create" />
				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button-secondary" value="<?php _e( 'Create' ); ?>" />
					<input type="submit" name="cancel" id="cancel" class="button-secondary" value="<?php _e( 'Cancel' ); ?>" />
				</p>
				<?php

				return false;
			}
			
			// perform actual creation of new channel
			if ( $action === 'do_create' ) {
				$new_channel_name = isset( $_REQUEST['new_channel_name'] ) ? $_REQUEST['new_channel_name'] : '';
				?>
				<h3><?php _e( 'Create channel', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></h3>
				<p><?php echo sprintf( __( "Creating the following channel:", NARNOO_DISTRIBUTOR_I18N_DOMAIN ) ) . ' ' . esc_html( $new_channel_name ); ?></p>
				<ol>
				<?php
				Narnoo_Distributor_Helper::print_media_ajax_script_body( 'unknown', 'createChannel', array( $new_channel_name ) );
				?>
				</ol>
				<?php 
				Narnoo_Distributor_Helper::print_ajax_script_footer( 1, __( 'Back to channels', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) );

				return false;
			}
			
			$video_ids = isset( $_REQUEST['videos'] ) ? $_REQUEST['videos'] : array();
			$num_ids = count( $video_ids );
			if ( empty( $video_ids ) || ! is_array( $video_ids ) || $num_ids === 0 ) {
				return true;				
			}
			
			switch ( $action ) {

				// confirm remove from channel
				case 'remove':
					$channel_id = $this->current_channel_id;
					$channel_name = $this->current_channel_name;
					if ( empty( $channel_name ) ) {
						Narnoo_Distributor_Helper::show_error( __( 'Unspecified channel name. Action cancelled.' ), NARNOO_DISTRIBUTOR_I18N_DOMAIN );
						return true;
					}
					?>
					<h3><?php _e( 'Confirm remove from channel', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></h3>
					<p><?php echo sprintf( __( "Please confirm removal of the following %d video(s) from the channel '%s' (ID %d):", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $num_ids, esc_html( $channel_name ), $channel_id ); ?></p>
					<input type="hidden" name="channel" value="<?php echo $channel_id; ?>" />
					<input type="hidden" name="channel_name" value="<?php echo esc_attr( $channel_name ); ?>" />
					<ol>
					<?php 
					foreach ( $video_ids as $id ) { 
						?>
						<input type="hidden" name="videos[]" value="<?php echo $id; ?>" />
						<li><span>Video ID: <?php echo $id; ?></span><span><img style="vertical-align: middle; padding-left: 20px;" src="<?php echo ( isset( $_REQUEST[ 'url' . $id ] ) ? esc_attr( $_REQUEST[ 'url' . $id ] ) : '' ); ?>" /></span></li>
						<?php 
					} 
					?>
					</ol>
					<input type="hidden" name="action" value="do_remove" />
					<p class="submit">
						<input type="submit" name="submit" id="submit" class="button-secondary" value="<?php _e( 'Confirm Removal' ); ?>" />
						<input type="submit" name="cancel" id="cancel" class="button-secondary" value="<?php _e( 'Cancel' ); ?>" />
					</p>
					<?php
					
					return false;
					
				// perform actual removal from channel
				case 'do_remove':
					$channel_id = $this->current_channel_id;
					$channel_name = $this->current_channel_name;
					if ( empty( $channel_name ) ) {
						Narnoo_Distributor_Helper::show_error( __( 'Unspecified channel name. Action cancelled.' ), NARNOO_DISTRIBUTOR_I18N_DOMAIN );
						return true;
					}
					?>
					<h3><?php _e( 'Remove from channel' ); ?></h3>
					<p><?php echo sprintf( __( "Removing the following %s video(s) from channel '%s' (ID %d):", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $num_ids, $channel_name, $channel_id ); ?></p>
					<ol>
					<?php
					foreach( $video_ids as $id ) {
						Narnoo_Distributor_Helper::print_media_ajax_script_body( $id, 'removeFromChannel', array( $id, $channel_id ) );
					}
					?>
					</ol>
					<?php 
					Narnoo_Distributor_Helper::print_ajax_script_footer( $num_ids, __( 'Back to channels', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) );

					return false;
					
				// perform download
				case 'download':					
					?>
					<h3><?php _e( 'Download', NARNOO_DISTRIBUTOR_I18N_DOMAIN ); ?></h3>
					<p><?php echo sprintf( __( "Requesting download links for the following %s video(s):", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $num_ids ); ?></p>
					<ol>
					<?php
					foreach( $video_ids as $id ) {
						Narnoo_Distributor_Helper::print_media_ajax_script_body( $id, 'downloadVideo', array( $id ) );
					}
					?>
					</ol>
					<?php 
					Narnoo_Distributor_Helper::print_ajax_script_footer( $num_ids, __( 'Back to channels', NARNOO_DISTRIBUTOR_I18N_DOMAIN ) );

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
		
		// no channel name specified; just return empty data
		$current_channel_name = $this->current_channel_name;
		if ( empty( $current_channel_name ) ) {
			return $data;
		}

		$list = null;
		$current_page = $this->get_pagenum();
		$request = Narnoo_Distributor_Helper::init_api( 'media' );
		if ( ! is_null( $request ) ) {
			try {
				$list = $request->getChannelVideos( $current_channel_name, $current_page );
				if ( ! is_array( $list->distributor_channel_videos ) ) {
					throw new Exception( sprintf( __( "Error retrieving channel videos. Unexpected format in response page #%d.", NARNOO_DISTRIBUTOR_I18N_DOMAIN ), $current_page ) );
				}
			} catch ( Exception $ex ) {
				Narnoo_Distributor_Helper::show_api_error( $ex );
			} 				
		}
		
		if ( ! is_null( $list ) ) {
			$data['total_pages'] = max( 1, intval( $list->total_pages ) );
			foreach ( $list->distributor_channel_videos as $video ) {
				$item['thumbnail_image'] = $video->video_thumb_image_path;
				$item['caption'] = $video->video_caption;
				$item['entry_date'] = $video->entry_date;
				$item['video_id'] = $video->video_id;
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
			
		$data = $this->get_current_page_data();
		$this->items = $data['items'];
		
		$this->set_pagination_args( array(
			'total_items'	=> count( $data['items'] ),
			'total_pages'	=> $data['total_pages']
		) );  			
		
		return true;
	}
	
	/**
	 * Add screen options for channels page.
	 **/
	static function add_screen_options() {
		global $narnoo_distributor_channels_table;
		$narnoo_distributor_channels_table = new Narnoo_Distributor_Channels_Table();
	}
}    