<?php

/**
 * Helper functions used throughout plugin.
 * */
class Narnoo_Distributor_Helper {

    /**
     * Returns true if current Wordpress version supports wp_enqueue_script in HTML body (3.3 and above); false otherwise.
     * */
    static function wp_supports_enqueue_script_in_body() {
        global $wp_version;
        $version = explode('.', $wp_version);
        if (intval($version[0] < 3) || ( intval($version[0]) == 3 && intval($version[1]) < 3 )) {
            return false;
        }
        return true;
    }

    /**
     * Show generic notification message.
     * */
    static function show_notification($msg) {
        echo '<div class="updated"><p>' . $msg . '</p></div>';
    }

    /**
     * Show generic error message.
     * */
    static function show_error($msg) {
        echo '<div class="error"><p>' . $msg . '</p></div>';
    }

    /**
     * In case of API error (e.g. invalid API keys), display error message.
     * */
    static function show_api_error($ex, $prefix_msg = '') {
        $error_msg = $ex->getMessage();
        $msg = '<strong>' . __('Narnoo API error:', NARNOO_DISTRIBUTOR_I18N_DOMAIN) . '</strong> ' . $prefix_msg . ' ' . $error_msg;
        if (false !== strchr(strtolower($error_msg), ' authentication fail')) {
            $msg .= '<br />' . sprintf(
                __('Please ensure your API settings in the <strong><a href="%1$s">Settings->Narnoo API</a></strong> page are correct and try again.', NARNOO_DISTRIBUTOR_I18N_DOMAIN), NARNOO_DISTRIBUTOR_SETTINGS_PAGE
                );
        }
        self::show_error($msg);
    }

    /**
     * Inits and returns distributor request object with user's access and secret keys.
     * If either app or secret key is empty, returns null.
     * */
    static function init_api($type = '') {

        // update this to include the access_key secret_key and access_token
        $options = get_option('narnoo_distributor_settings');
       

        if ( empty($options['access_key']) || empty($options['secret_key'])  ) {
            return null;
        }

        /**
        *
        *   Store keys in a different setting option
        *
        */
        $_token   = get_option( 'narnoo_distributor_token' );

        
        /**
        *
        *   Check to see if we have access keys and a token.
        *
        */
        if( !empty( $options['access_key'] ) && !empty( $options['secret_key'] ) && empty($_token) ){
            /**
            *
            *   Call the Narnoo authentication to return our access token
            *
            */
            $requestToken = new Narnooauthen( $options['access_key'],$options['secret_key'] );
            $token        =  $requestToken->authenticate();
            if(!empty($token)){
                /**
                *
                *   Update Narnoo access token
                *
                */
                update_option( 'narnoo_distributor_token', $token, 'yes' );
                
            }else{
                return null;
            }


        }

        /**
        *
        *   Create authentication Header to access the API.
        *
        **/

        $api_settings = array(
            "API-KEY: ".$options['access_key'],
            "API-SECRET-KEY: ".$options['secret_key'],
            "Authorization: ".$_token
            );


        if ($type === 'operator') {
            
            $request = new Operatorconnect($api_settings);
            
        } elseif( $type === 'builder' ){

            $request = new Listbuilder($api_settings);

        }else{
            
            $request = new Distributor($api_settings);
            
        }

        return $request;
    }


    /*
    *
    * Inits our PHP FastCache options
    *
    */

    static function init_noo_cache(){

        $config = array(
            "path"      =>  NARNOO_DISTRIBUTOR_PLUGIN_PATH . "libs/cache",
            );
        $cache = phpFastCache("files",$config);
        

        return $cache;
    }

    /**
     * Retrieves list of operator IDs that have been imported into Wordpress database.
     * */
    static function get_imported_operator_ids() {
        $imported_ids = array();

        $narnoo_custom_post_types = get_option('narnoo_custom_post_types', array());
        foreach ($narnoo_custom_post_types as $category => $fields) {
            $imported_posts = get_posts(array('post_type' => 'narnoo_' . $category, 'numberposts' => -1));
            foreach ($imported_posts as $post) {
                $id = get_post_meta($post->ID, 'operator_id', true);
                if (!empty($id)) {
                    $imported_ids[] = $id;
                }
            }
        }

        return $imported_ids;
    }

    /**
     * Retrieves Wordpress post ID for imported operator ID, if it exists.
     * Returns false if no such operator exists in Wordpress DB.
     * */
    static function get_post_id_for_imported_operator_id($operator_id) {
        $imported_ids = array();

        $narnoo_custom_post_types = get_option('narnoo_custom_post_types', array());
        foreach ($narnoo_custom_post_types as $category => $fields) {
            $imported_posts = get_posts(array('post_type' => 'narnoo_' . $category, 'numberposts' => -1));
            foreach ($imported_posts as $post) {
                $id = get_post_meta($post->ID, 'operator_id', true);
                if ($id === $operator_id) {
                    return $post->ID;
                }
            }
        }

        return false;
    }

    /**
     * Retrieves Wordpress post ID for imported sub-category, if it exists.
     * Returns false if no such subcategory post exists in Wordpress DB.
     * */
    static function get_post_id_for_imported_sub_category($category, $sub_category) {
        $imported_ids = array();

        $imported_posts = get_posts(array('post_type' => 'narnoo_' . $category, 'numberposts' => -1, 'parent' => 0));
        foreach ($imported_posts as $post) {
            $sub_category_archive = get_post_meta($post->ID, 'narnoo_sub_category_archive', true);
            if ($sub_category_archive === $sub_category) {
                return $post->ID;
            }
        }

        return false;
    }

    /**
     * Prints out the HTML/Javascript for a single distributor media item that will be processed via AJAX.
     * */
    static function print_media_ajax_script_body($id, $func_name, $params_array, $text = '') {
        self::print_ajax_script_body($id, $func_name, $params_array, $text, 'media');
    }

    /**
     * Prints out the HTML/Javascript for a single distributor-operator media item that will be processed via AJAX.
     * */
    static function print_operator_ajax_script_body($id, $func_name, $params_array, $text = '') {
        self::print_ajax_script_body($id, $func_name, $params_array, $text, 'operator');
    }

    /**
     * Prints out the HTML/Javascript for a single item that will be processed via AJAX.
     * */
    static function print_ajax_script_body($id, $func_name, $params_array, $text = '', $func_type = '', $is_import_operators = false) {
        static $count = 0;

        if (empty($text)) {
            $text = __('Item ID:', NARNOO_DISTRIBUTOR_I18N_DOMAIN) . ' ' . $id;
        }
        $text .= '...';
        ?>
        <li>
            <img id="narnoo-icon-process-<?php echo $id; ?>" src="<?php echo admin_url(); ?>images/wpspin_light.gif" /> 
            <img style="display:none;" id="narnoo-icon-success-<?php echo $id; ?>" src="<?php echo admin_url(); ?>images/yes.png" /> 
            <img style="display:none;" id="narnoo-icon-fail-<?php echo $id; ?>" src="<?php echo admin_url(); ?>images/no.png" /> 
            <span><?php echo esc_html($text); ?></span>
            <strong><span id="narnoo-item-<?php echo $id; ?>"><?php _e('Processing...', NARNOO_DISTRIBUTOR_I18N_DOMAIN); ?></span></strong>
        </li>
        <script type="text/javascript">
            <?php if ($is_import_operators && $count === 0) { ?>
                var narnoo_categories = [];
                <?php } ?>
                jQuery(document).ready(function($) {
                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: { action: 'narnoo_distributor_api_request', 
                        type: '<?php echo $func_type; ?>',
                        func_name: '<?php echo $func_name; ?>', 
                        param_array: [ <?php echo "'" . implode("','", $params_array) . "'"; ?> ] },
                        timeout: 60000,
                        dataType: "json",
                        success: 
                        function(response, textStatus, jqXHR) {   
                            $('#narnoo-icon-process-<?php echo $id; ?>').hide();
                            processed++;
                            
                            if (response['success'] === 'success' && response['msg']) {
                                $('#narnoo-icon-success-<?php echo $id; ?>').show();
                                $('#narnoo-item-<?php echo $id; ?>').html(response['msg']);
                                success++;										
                                
                                <?php if ($is_import_operators) { ?>
                                    narnoo_categories.push( response['response']['category'] );
                                    $.ajax({
                                        type: 'POST',
                                        url: ajaxurl,
                                        data: { action: 'narnoo_distributor_api_request', 
                                        type: '<?php echo $func_type; ?>',
                                        func_name: 'add_custom_post_types', 
                                        param_array: [ narnoo_categories ] },
                                        timeout: 60000,
                                        dataType: "json"
                                    });
                                    <?php } ?>
                                } else {
                                    $('#narnoo-icon-fail-<?php echo $id; ?>').show();
                                    $('#narnoo-item-<?php echo $id; ?>').html('<?php _e('AJAX error: Unexpected response', NARNOO_DISTRIBUTOR_I18N_DOMAIN); ?>');										
                                }
                                
                                check_complete($);
                            },
                            error: 
                            function(jqXHR, textStatus, errorThrown) {
                                $('#narnoo-icon-process-<?php echo $id; ?>').hide();
                                $('#narnoo-icon-fail-<?php echo $id; ?>').show();
                                processed++;
                                
                                                                        if (textStatus === 'timeout') {   // server timeout
                                                                            $('#narnoo-item-<?php echo $id; ?>').html('<?php _e('AJAX error: Server timeout', NARNOO_DISTRIBUTOR_I18N_DOMAIN); ?>');
                                                                        } else {                  // other error
                                                                            $('#narnoo-item-<?php echo $id; ?>').html(jqXHR.responseText);
                                                                        }
                                                                        
                                                                        check_complete($);
                                                                    }
                                                                });
});
</script>
<?php
$count++;
}

    /**
     * Prints out the footer HTML/Javascript needed for AJAX processing.
     * */
    static function print_ajax_script_footer($total_count, $back_button_text, $extra_button_text = '') {
        ?>
        <div class="narnoo-completed" style="display:none;">
            <br />
            <p><strong><?php echo sprintf(__("Processing completed. %s of %d item(s) successful.", NARNOO_DISTRIBUTOR_I18N_DOMAIN), '<span id="narnoo-success-count"></span>', $total_count); ?></strong></p>
        </div>
        <p class="submit narnoo-completed" style="display:none;">
            <?php
            if (!empty($extra_button_text)) {
                ?><input type="submit" name="extra_button" id="extra_button" class="button-secondary" value="<?php echo $extra_button_text; ?>" /><?php
            }
            ?>
            <input type="submit" name="back" id="cancel" class="button-secondary" value="<?php echo $back_button_text; ?>" />
        </p>
        <script type="text/javascript">
            var success = 0; 
            var processed = 0;
            function check_complete($) {
                if (processed >= <?php echo $total_count; ?>) {
                    $('#narnoo-success-count').text(success);
                    $('.narnoo-completed').show();
                }							
            }
        </script>
        <?php
    }

    /**
     * Returns HTML and Javascript required for selection of album page/album names, and querying of
     * album pages via AJAX.
     * */
    static function get_album_select_html_script($albums, $total_pages, $current_album_page, $current_album_name, $func_type = '') {
        ob_start();
        ?>
        <input type="hidden" id="narnoo_album_name" name="narnoo_album_name" value="<?php echo esc_attr($current_album_name); ?>" />
        <select name="narnoo_album_page" id="narnoo-album-page-select">
            <?php
            for ($i = 0; $i < $total_pages; $i++) {
                $selected = '';
                if (( $i + 1 ) === $current_album_page) {
                    $selected = 'selected="selected"';
                }
                ?><option value="<?php echo $i + 1; ?>"<?php echo $selected; ?>><?php printf(__('Album page %d', NARNOO_DISTRIBUTOR_I18N_DOMAIN), ($i + 1)); ?></option><?php
            }
            ?>
        </select>

        <?php
            // prepare "select album" element for every page
        for ($i = 0; $i < $total_pages; $i++) {
            $is_current_page = ( $current_album_page === ( $i + 1 ) );

            $hidden = ' data-loaded="yes"';
            $disabled = '';
            if (!$is_current_page) {
                $hidden = ' data-loaded="no" style="display:none;"';
                $disabled = ' disabled="disabled"';
            }
            ?>
            <span class="narnoo-album-select-span" id="narnoo-album-select-span-<?php echo $i + 1; ?>"<?php echo $hidden; ?>>
                <span class="narnoo-album-select-span-process" style="display:none;">
                    <img class="narnoo-icon-process" src="<?php echo admin_url(); ?>images/wpspin_light.gif" />
                    <img class="narnoo-icon-fail" src="<?php echo admin_url(); ?>images/no.png" />
                    <span class="narnoo-album-select-msg"></span>
                </span>
                <select class="narnoo-album-select" name="narnoo_album_id"<?php echo $disabled; ?>>
                    <?php
                    foreach ($albums as $album) {
                        $album_name = stripslashes($album->album_name);
                        $selected = '';
                        if ($current_album_name === $album_name) {
                            $selected = ' selected="selected"';
                        }
                        ?><option value="<?php echo $album->album_id; ?>"<?php echo $selected; ?>><?php echo esc_html($album_name); ?></option><?php
                    }
                    ?>
                </select>
            </span>
            <?php
        }
        ?>
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
                $('#album_select_button').click(function(e, ui) {
                    page = $('#narnoo-album-page-select').val();
                    $selected = $('#narnoo-album-select-span-' + page).find('.narnoo-album-select option:selected');
                    $('#narnoo_album_name').val($selected.html());
                    
                    // rebuild form action query string to ensure album name, id and page are in sync
                    $form = $('#narnoo-albums-form');
                    if ($form.length > 0) {
                        new_query = $form.attr('action');
                        new_query = updateQueryStringParameter(new_query, 'album', $selected.val());
                        new_query = updateQueryStringParameter(new_query, 'album_name', $selected.html() );
                        new_query = updateQueryStringParameter(new_query, 'album_page', page);
                        $form.attr('action', new_query);
                    }
                });
                
                $('#narnoo-album-page-select').change(function(e, ui) {
                    var $this = $(this);
                    var page = $(this).val();
                    
                    $('.narnoo-album-select').attr("disabled", "disabled").hide();
                    $('.narnoo-album-select-span').hide();	
                    
                    var $album_select_span = $('#narnoo-album-select-span-' + page);
                    var $album_select = $album_select_span.find('.narnoo-album-select');
                    var $album_select_span_process = $album_select_span.find('.narnoo-album-select-span-process');
                    var $album_select_icon_fail = $album_select_span.find('.narnoo-icon-fail');
                    var $album_select_icon_process = $album_select_span.find('.narnoo-icon-process');
                    var $album_select_msg = $album_select_span.find('.narnoo-album-select-msg');
                    
                    if ($album_select_span.data('loaded') === 'yes') {
                        $album_select_span.find('.narnoo-album-select').removeAttr("disabled").show();
                        $("#album_select_button").removeAttr('disabled');
                    } else {
                        $album_select_span_process.show();
                        $("#album_select_button").attr('disabled', 'disabled');
                        
                        if ($album_select_span.data('loaded') === 'no') {
                            $album_select_span.data("loaded", "loading");
                            $album_select_icon_fail.hide();
                            $album_select_icon_process.show();
                            $album_select_msg.html("<?php _e('Retrieving album names...', NARNOO_DISTRIBUTOR_I18N_DOMAIN); ?>");
                            
                            // request album names via AJAX from server
                            $.ajax({
                                type: 'POST',
                                url: ajaxurl,
                                data: { action: 'narnoo_distributor_api_request', 
                                type: '<?php echo $func_type; ?>',
                                func_name: 'getAlbums', 
                                param_array: [ page ] },
                                timeout: 60000,
                                dataType: "json",
                                success: 
                                function(response, textStatus, jqXHR) {     
                                    $album_select_icon_process.hide();
                                    
                                    error_msg = "<?php _e('AJAX error: Unexpected response', NARNOO_DISTRIBUTOR_I18N_DOMAIN); ?>";
                                    if (response['success'] === 'success' && response['msg'] && response['response'] && response['response']['distributor_albums']) {
                                        items = response['response']['distributor_albums'];
                                        if (items.length === 0) {
                                            error_msg = "<?php _e('No albums found!'); ?>";
                                        } else {
                                            // populate the select element with album names
                                            options = '';
                                            for (index in items) {
                                                item = items[index];
                                                options += '<option value="' + item['album_id'] + '">' + item['album_name'] + '</option>';
                                            }
                                            $album_select.html(options);
                                            
                                            $album_select_msg.html('');	
                                            $album_select_span.data("loaded", "yes");
                                            if (page === $this.val()) {	// ensure the current page is still selected
                                                $album_select.removeAttr('disabled').show();
                                                $("#album_select_button").removeAttr('disabled');
                                            }
                                            return;
                                        }
                                    }
                                    
                                    $album_select_icon_fail.show();
                                    $album_select_span.data("loaded", "no");
                                    $album_select_msg.html(error_msg);
                                },
                                error: 
                                function(jqXHR, textStatus, errorThrown) {
                                    $album_select_icon_process.hide();
                                    $album_select_icon_fail.show();
                                    $album_select_span.data("loaded", "no");
                                    
                                    if (textStatus === 'timeout') {   // server timeout
                                        $album_select_msg.html('<?php _e('AJAX error: Server timeout', NARNOO_DISTRIBUTOR_I18N_DOMAIN); ?>');
                                    } else {                  // other error
                                        $album_select_msg.html(jqXHR.responseText);
                                    }
                                }
                            });
}
}

$album_select_span.show();
});
});
</script>
<?php
return ob_get_clean();
}

    /**
     * Returns HTML and Javascript required for selection of channel page/channel names, and querying of
     * channel pages via AJAX.
     * */
    static function get_channel_select_html_script($channels, $total_pages, $current_channel_page, $current_channel_name) {
        ob_start();
        ?>
        <input type="hidden" id="narnoo_channel_name" name="narnoo_channel_name" value="<?php echo esc_attr($current_channel_name); ?>" />
        <select name="narnoo_channel_page" id="narnoo-channel-page-select">
            <?php
            for ($i = 0; $i < $total_pages; $i++) {
                $selected = '';
                if (( $i + 1 ) === $current_channel_page) {
                    $selected = 'selected="selected"';
                }
                ?><option value="<?php echo $i + 1; ?>"<?php echo $selected; ?>><?php printf(__('Channel page %d', NARNOO_DISTRIBUTOR_I18N_DOMAIN), ($i + 1)); ?></option><?php
            }
            ?>
        </select>

        <?php
            // prepare "select channel" element for every page
        for ($i = 0; $i < $total_pages; $i++) {
            $is_current_page = ( $current_channel_page === ( $i + 1 ) );

            $hidden = ' data-loaded="yes"';
            $disabled = '';
            if (!$is_current_page) {
                $hidden = ' data-loaded="no" style="display:none;"';
                $disabled = ' disabled="disabled"';
            }
            ?>
            <span class="narnoo-channel-select-span" id="narnoo-channel-select-span-<?php echo $i + 1; ?>"<?php echo $hidden; ?>>
                <span class="narnoo-channel-select-span-process" style="display:none;">
                    <img class="narnoo-icon-process" src="<?php echo admin_url(); ?>images/wpspin_light.gif" />
                    <img class="narnoo-icon-fail" src="<?php echo admin_url(); ?>images/no.png" />
                    <span class="narnoo-channel-select-msg"></span>
                </span>
                <select class="narnoo-channel-select" name="narnoo_channel_id"<?php echo $disabled; ?>>
                    <?php
                    foreach ($channels as $channel) {
                        $channel_name = stripslashes($channel->channel_name);
                        $selected = '';
                        if ($current_channel_name === $channel_name) {
                            $selected = ' selected="selected"';
                        }
                        ?><option value="<?php echo $channel->channel_id; ?>"<?php echo $selected; ?>><?php echo esc_html($channel_name); ?></option><?php
                    }
                    ?>
                </select>
            </span>
            <?php
        }
        ?>
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
                $('#channel_select_button').click(function(e, ui) {
                    page = $('#narnoo-channel-page-select').val();
                    $selected = $('#narnoo-channel-select-span-' + page).find('.narnoo-channel-select option:selected');
                    $('#narnoo_channel_name').val($selected.html());
                    
                    // rebuild form action query string to ensure channel name, id and page are in sync
                    $form = $('#narnoo-channels-form');
                    if ($form.length > 0) {
                        new_query = $form.attr('action');
                        new_query = updateQueryStringParameter(new_query, 'channel', $selected.val());
                        new_query = updateQueryStringParameter(new_query, 'channel_name', $selected.html() );
                        new_query = updateQueryStringParameter(new_query, 'channel_page', page);
                        $form.attr('action', new_query);
                    }
                });
                
                $('#narnoo-channel-page-select').change(function(e, ui) {
                    var $this = $(this);
                    var page = $(this).val();
                    
                    $('.narnoo-channel-select').attr("disabled", "disabled").hide();
                    $('.narnoo-channel-select-span').hide();	
                    
                    var $channel_select_span = $('#narnoo-channel-select-span-' + page);
                    var $channel_select = $channel_select_span.find('.narnoo-channel-select');
                    var $channel_select_span_process = $channel_select_span.find('.narnoo-channel-select-span-process');
                    var $channel_select_icon_fail = $channel_select_span.find('.narnoo-icon-fail');
                    var $channel_select_icon_process = $channel_select_span.find('.narnoo-icon-process');
                    var $channel_select_msg = $channel_select_span.find('.narnoo-channel-select-msg');
                    
                    if ($channel_select_span.data('loaded') === 'yes') {
                        $channel_select_span.find('.narnoo-channel-select').removeAttr("disabled").show();
                        $("#channel_select_button").removeAttr('disabled');
                    } else {
                        $channel_select_span_process.show();
                        $("#channel_select_button").attr('disabled', 'disabled');
                        
                        if ($channel_select_span.data('loaded') === 'no') {
                            $channel_select_span.data("loaded", "loading");
                            $channel_select_icon_fail.hide();
                            $channel_select_icon_process.show();
                            $channel_select_msg.html("<?php _e('Retrieving channel names...', NARNOO_DISTRIBUTOR_I18N_DOMAIN); ?>");
                            
                            // request channel names via AJAX from server
                            $.ajax({
                                type: 'POST',
                                url: ajaxurl,
                                data: { action: 'narnoo_distributor_api_request', 
                                type: '',
                                func_name: 'getChannels', 
                                param_array: [ page ] },
                                timeout: 60000,
                                dataType: "json",
                                success: 
                                function(response, textStatus, jqXHR) {     
                                    $channel_select_icon_process.hide();
                                    
                                    error_msg = "<?php _e('AJAX error: Unexpected response', NARNOO_DISTRIBUTOR_I18N_DOMAIN); ?>";
                                    if (response['success'] === 'success' && response['msg'] && response['response'] && response['response']['distributor_channels']) {
                                        items = response['response']['distributor_channels'];
                                        if (items.length === 0) {
                                            error_msg = "<?php _e('No channels found!'); ?>";
                                        } else {
                                            // populate the select element with channel names
                                            options = '';
                                            for (index in items) {
                                                item = items[index];
                                                options += '<option value="' + item['channel_id'] + '">' + item['channel_name'] + '</option>';
                                            }
                                            $channel_select.html(options);
                                            
                                            $channel_select_msg.html('');	
                                            $channel_select_span.data("loaded", "yes");
                                            if (page === $this.val()) {	// ensure the current page is still selected
                                                $channel_select.removeAttr('disabled').show();
                                                $("#channel_select_button").removeAttr('disabled');
                                            }
                                            return;
                                        }
                                    }
                                    
                                    $channel_select_icon_fail.show();
                                    $channel_select_span.data("loaded", "no");
                                    $channel_select_msg.html(error_msg);
                                },
                                error: 
                                function(jqXHR, textStatus, errorThrown) {
                                    $channel_select_icon_process.hide();
                                    $channel_select_icon_fail.show();
                                    $channel_select_span.data("loaded", "no");
                                    
                                    if (textStatus === 'timeout') {   // server timeout
                                        $channel_select_msg.html('<?php _e('AJAX error: Server timeout', NARNOO_DISTRIBUTOR_I18N_DOMAIN); ?>');
                                    } else {                  // other error
                                        $channel_select_msg.html(jqXHR.responseText);
                                    }
                                }
                            });
}
}

$channel_select_span.show();
});
});
</script>
<?php
return ob_get_clean();
}

    /**
     * Handling of AJAX request fatal error.
     * */
    static function ajax_fatal_error($sErrorMessage = '') {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
        die($sErrorMessage);
    }

    /**
     * Handling of AJAX API requests. 
     * */
    static function ajax_api_request() {
        if (!isset($_POST['func_name']) || !isset($_POST['param_array'])) {
            self::ajax_fatal_error(__('AJAX error: Missing arguments.', NARNOO_DISTRIBUTOR_I18N_DOMAIN));
        }
        $func_name      = $_POST['func_name'];
        $param_array    = $_POST['param_array'];
        $func_type      = $_POST['type'];

        // init the API request object
        if ($func_type !== 'self') {
            $request = Narnoo_Distributor_Helper::init_api($func_type);
            if (is_null($request)) {
                self::ajax_fatal_error(__('Narnoo API error: Incorrect API keys specified.', NARNOO_DISTRIBUTOR_I18N_DOMAIN));
            }
        }

        // attempt to call API function with specified params
        $response = array();
        try {
            if ($func_type === 'self') {
                // call static function in helper class
                $response['response'] = call_user_func_array(array('Narnoo_Distributor_Helper', $func_name), $param_array);
            } else {
                $response['response'] = call_user_func_array(array($request, $func_name), $param_array);
            }

            if (false === $response['response']) {
                self::ajax_fatal_error(__('AJAX error: Invalid function or arguments specified.', NARNOO_DISTRIBUTOR_I18N_DOMAIN));
            }
            $response['success'] = 'success';

            // set success message depending on API function called
            $response['msg'] = __('Success!', NARNOO_DISTRIBUTOR_I18N_DOMAIN);
            $item = $response['response'];
            if (!is_null($item)) {
                if (isset($item->success) && isset($item->success->successMessage)) {
                    // copy success message directly from API response
                    $response['msg'] = $item->success->successMessage;
                }
                if ('downloadBrochure' === $func_name) {
                
                    print_r($item);
                    //die();
                   // $response['msg'] .= ' <a target="_blank" href="' . $item->download_brochure_file . '">' . __('Download PDF brochure', NARNOO_DISTRIBUTOR_I18N_DOMAIN) . '</a>';
                

                } else if ('downloadImage' === $func_name) {
                    $response['msg'] .= ' <a target="_blank" href="' . $item->download_image_file . '">' . __('Download image link', NARNOO_DISTRIBUTOR_I18N_DOMAIN) . '</a>';
                } else if ('downloadVideo' === $func_name) {
                    $item->download_video_stream_path = uncdata($item->download_video_stream_path);
                    $item->original_video_path = uncdata($item->original_video_path);
                    $response['msg'] .= ' <a target="_blank" href="' . $item->download_video_stream_path . '">' . __('Download video stream path', NARNOO_DISTRIBUTOR_I18N_DOMAIN) . '</a>';
                    $response['msg'] .= ' <a target="_blank" href="' . $item->original_video_path . '">' . __('Original video path', NARNOO_DISTRIBUTOR_I18N_DOMAIN) . '</a>';
                } else if ('getAlbums' === $func_name) {
                    // ensure each album name has slashes stripped
                    $albums = $item->distributor_albums;
                    if (is_array($albums)) {
                        foreach ($albums as $album) {
                            $album->album_name = stripslashes($album->album_name);
                        }
                    }
                } 
            }
        } catch (Exception $ex) {
            self::ajax_fatal_error(__('Narnoo API error: ', NARNOO_DISTRIBUTOR_I18N_DOMAIN) . $ex->getMessage());
        }

        echo json_encode($response);
        die();
    }

    /**
     * Retrieves all items of specified media type (i.e. images, videos, brochures, text, albums)
     * for the specified operator (specified by $request object).
     * The ordering of any existing items is preserved (new items are added to the end of the array).
     * */
    static function get_operator_media_type($request, $operator_id, $post_id, $media_type, $media_type_str, $func_name) {
        $items = array();

        switch ($media_type) {
            case 'images':
            $items_fieldname = 'operator_images';
            $id_fieldname = 'image_id';
            break;
            case 'brochures':
            $items_fieldname = 'operator_brochures';
            $id_fieldname = 'brochure_id';
            break;
            case 'videos':
            $items_fieldname = 'operator_videos';
            $id_fieldname = 'video_id';
            break;
            case 'text':
            $items_fieldname = 'operator_products';
            $id_fieldname = 'product_id';
            break;
            case 'albums':
            $items_fieldname = 'operator_albums';
            $id_fieldname = 'album_id';
            break;
            default:
            return $items;
        }

        $current_page = 1;
        $total_pages = 0;
        while ($current_page === 1 || $current_page < $total_pages) {
            try {
                $list = call_user_func_array(array($request, $func_name), array($operator_id, $current_page));
            } catch (Exception $ex) {
                break; // stop if any error encountered
            }

            if ($total_pages === 0) {
                $total_pages = max(1, intval($list->total_pages));
            }

            // convert each result object into array and add it to previous page results
            $result = $list->$items_fieldname;
            if (is_array($result)) {
                foreach ($result as $result_item) {
                    $items[] = json_decode(json_encode($result_item), true);
                }
            } else {
                break;
            }

            $current_page++;
        }

        // if there are any existing imported items, ensure their order is preserved
        if ($post_id !== false) {
            $existing_items = get_post_meta($post_id, 'narnoo_' . $media_type, true);
            $all_items = array();
            if (!empty($existing_items)) {
                // go through existing imported items list, removing any items that don't exist in new list
                foreach ($existing_items as $existing_item_data) {
                    // ensure this item exists in the new list
                    foreach ($items as $key => $item_data) {
                        if ($item_data[$id_fieldname] === $existing_item_data[$id_fieldname]) {
                            // item exists in both old and new lists
                            $all_items[] = $item_data;
                            unset($items[$key]); // remove item from new list
                            break;
                        }
                    }
                }

                // add all remaining items from new list to the end of the $all_items array
                $items = array_merge($all_items, $items);
            }
        }

        return $items;
    }

    /**
     * Adds Narnoo custom post type for specified categories, with pluralized version as slug.
     * */
    static function add_custom_post_types($categories) {
        $custom_post_types = get_option('narnoo_custom_post_types', array());
        foreach ($categories as $category) {
            if (!array_key_exists($category, $custom_post_types)) {
                // known categories to pluralize
                $pluralize = array('attraction', 'accommodation', 'service', 'retail');
                $pluralized_category = $category;
                if (in_array($pluralized_category, $pluralize)) {
                    $pluralized_category .= 's';
                }
                $custom_post_types[$category] = array('slug' => sanitize_title_with_dashes($pluralized_category), 'description' => '');
            }
        }
        update_option('narnoo_custom_post_types', $custom_post_types);

        // return response object
        $response = new stdClass();
        $response->success = new stdClass();
        $response->success->successMessage = 'success';
       // $response->success->successMessage = $categories;
        return $response;
    }

    /**
     * Imports specified operator along with all their media details from Narnoo database into Wordpress posts.
     * */
    static function import_operator($operator_id) {
        global $user_ID;
        $options = get_option('narnoo_distributor_settings');
        
        // init the API request objects
        $request            = self::init_api();
        $requestOperator    = self::init_api('builder');
        
        if (is_null($request) || is_null($requestOperator)) {
            throw new Exception(__('Incorrect API keys specified.', NARNOO_DISTRIBUTOR_I18N_DOMAIN));
        }

        // get operator details
        $operator = $requestOperator->builder( $operator_id,NULL,NULL,NULL,TRUE,TRUE ); //UPDATED THIS LINE OF CODE
        
        //print_r($operator);
        //die();

        $category = strtolower($operator->category);

        // get existing sub_category post, or create new one if it doesn't exist - the main page for the sub_category
        $sub_category_post_id = 0;
        if (!empty($operator->sub_category)) {

            $sub_category_post_id = Narnoo_Distributor_Helper::get_post_id_for_imported_sub_category($category, $operator->sub_category);
            if ($sub_category_post_id === false) {
                $new_sub_category_post = array(
                    'post_title'        => $operator->sub_category,
                    'post_content'      => '',
                    'post_status'       => 'publish',
                    'post_date'         => date('Y-m-d H:i:s'),
                    'post_author'       => $user_ID,
                    'post_type'         => 'narnoo_' . $category,
                    'comment_status'    => 'closed',
                    'ping_status'       => 'closed',
                    );
                $sub_category_post_id = wp_insert_post($new_sub_category_post);
                update_post_meta($sub_category_post_id, 'narnoo_sub_category_archive', $operator->sub_category);
            }
        }

        // get existing post with operator id, if any
        $post_id = Narnoo_Distributor_Helper::get_post_id_for_imported_operator_id($operator_id);

        if ($post_id !== false) {
            // update existing post, ensuring parent is correctly set
            $update_post_fields = array(
                'ID'            => $post_id,
                'post_title'    => $operator->business_name,
                'post_type'     => 'narnoo_' . $category,
                'post_status'   => 'publish',
                'post_parent'   => $sub_category_post_id,
                );
            wp_update_post($update_post_fields);
            
            update_post_meta($post_id, 'data_source',            'narnoo');
            update_post_meta($post_id, 'operator_excerpt',        strip_tags( $operator->description->english->summary_text->text ) );
            update_post_meta($post_id, 'operator_description',    $operator->description->english->full_text->text );

            $feature = get_the_post_thumbnail($post_id);
            if(empty($feature)){
                    if( !empty( $operator->feature_image->xxlarge_image_path ) ){
                        $url            = "https:" . $operator->feature_image->xxlarge_image_path;
                        $desc           = $operator->business_name . " feature image";
                        $feature_image  = media_sideload_image($url, $post_id, $desc, 'src');
                        if(!empty($feature_image)){
                            global $wpdb;
                            $attachment     = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $feature_image )); 
                            set_post_thumbnail( $post_id, $attachment[0] );
                        }
                    }
            }

            $success_message = __('Success! Re-imported operator details to existing %1s post (%2s)', NARNOO_DISTRIBUTOR_I18N_DOMAIN);
        } else {
            // create new post with operator details
            $new_post_fields = array(
                'post_title'        => $operator->business_name,
                'post_content'      => $operator->description->english->full_text->text,
                'post_excerpt'      => strip_tags( $operator->description->english->summary_text->text ),
                'post_status'       => 'publish',
                'post_date'         => date('Y-m-d H:i:s'),
                'post_author'       => $user_ID,
                'post_type'         => 'narnoo_' . $category,
                'comment_status'    => 'closed',
                'ping_status'       => 'closed',
                'post_parent'       => $sub_category_post_id,
                );
            $post_id = wp_insert_post($new_post_fields);

            // set a feature image for this post
            if( !empty( $operator->feature_image->xxlarge_image_path ) ){
                $url            = "https:" . $operator->feature_image->xxlarge_image_path;
                $desc           = $operator->business_name . " feature image";
                $feature_image  = media_sideload_image($url, $post_id, $desc, 'src');
                if(!empty($feature_image)){
                    global $wpdb;
                    $attachment     = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $feature_image )); 
                    set_post_thumbnail( $post_id, $attachment[0] );
                }
            }

            $success_message = __('Success! Imported operator details to new %1s post (%2s)', NARNOO_DISTRIBUTOR_I18N_DOMAIN);
        }

        // insert/update custom fields with operator details into post
        update_post_meta($post_id, 'data_source',            'narnoo');
        update_post_meta($post_id, 'operator_id',            $operator->narnoo_id);
        update_post_meta($post_id, 'category',               $operator->category);
        update_post_meta($post_id, 'sub_category',           $operator->sub_category);
        update_post_meta($post_id, 'businessname',           $operator->business_name);
        update_post_meta($post_id, 'country_name',           $operator->country);
        update_post_meta($post_id, 'state',                  $operator->state);
        update_post_meta($post_id, 'suburb',                 $operator->suburb);
        update_post_meta($post_id, 'location',               $operator->location);
        update_post_meta($post_id, 'postcode',               $operator->postcode);
        update_post_meta($post_id, 'keywords',               $operator->keywords);
        update_post_meta($post_id, 'phone',                  $operator->phone);
        update_post_meta($post_id, 'url',                    $operator->url);
        update_post_meta($post_id, 'email',                  $operator->email);
        update_post_meta($post_id, 'latitude',               $operator->latitude);
        update_post_meta($post_id, 'longitude',              $operator->longitude);
        //Import social media links
        update_post_meta($post_id, 'facebook',               $operator->operator_social->facebook);
        update_post_meta($post_id, 'twitter',                $operator->operator_social->twitter);
        update_post_meta($post_id, 'instagram',              $operator->operator_social->instagram);
        update_post_meta($post_id, 'youtube',                $operator->operator_social->youtube);
        update_post_meta($post_id, 'tripadvisor',            $operator->operator_social->tripadvisor_url);

        /*
        Import the products? Or do we just use the operator information?
        */
        if ( !empty( $options['operator_import'] )  ) {

           $opResponse = self::import_operator_products( $operator->narnoo_id, $operator->category, $operator->sub_category, $operator->business_name, $post_id );
           if(!empty($opResponse)){
                update_post_meta($post_id, 'products',            $opResponse);
           }

        }


        // return response object
        $response = new stdClass();
        $response->success = new stdClass();
        $response->success->successMessage = 
        sprintf( $success_message, 
         '<a target="_blank" href="edit.php?post_type=narnoo_' . esc_attr( $category ) . '">' . esc_html( ucfirst( $category ) ) . ( empty( $operator->sub_category ) ? '' : '/' . $operator->sub_category ) . '</a>',
         '<a target="_blank" href="post.php?post=' . $post_id . '&action=edit">ID #' . $post_id . '</a>'
         );
        $response->category = $category;
        return $response;
    }

    /**
    *
    *   @dateCreated: 13.02.2018
    *   @title: Import an operators products
    *
    */
    static function import_operator_products( $op_id, $category, $subCategory, $businessName, $postId ){

        // init the API request objects
        $request            = self::init_api();
        $requestOperator    = self::init_api('operator');
        
        if (is_null($request) || is_null($requestOperator)) {
            throw new Exception(__('Incorrect API keys specified.', NARNOO_DISTRIBUTOR_I18N_DOMAIN));
        }

        // get operator details
        $products = $requestOperator->getProducts( $op_id );
        if(empty($products) || empty($products->success)){
           return false;
        }

        /*
        *
        *       --- Check that this isn't the first time the custom post type has been created
        *
        *
        $postCheck = self::product_post_type_init( );
        if( empty($postCheck) ){
            throw new Exception(__('Error creating custom post type page.', NARNOO_DISTRIBUTOR_I18N_DOMAIN));
        }
        
        /************************************************************************************
        *
        *          ----- Loop through the products and return the information ----- 
        *
        *************************************************************************************/
        foreach ($products->product as $item) {


            $productDetails = $requestOperator->getProductDetails( $op_id, $item->product_id );
            

            if(!empty($productDetails) || !empty($productDetails->success)){
                        $postData = self::get_post_id_for_imported_product_id( $productDetails->product_id );

                        if ( !empty( $postData['id'] ) && $postData['status'] !== 'trash') {
                                $post_id = $postData['id'];
                                // update existing post, ensuring parent is correctly set
                                $update_post_fields = array(
                                    'ID'            => $post_id,
                                    'post_title'    => $productDetails->product_title,
                                    'post_type'     => 'narnoo_product',
                                    'post_status'   => 'publish',
                                    'post_author'   => $user_ID,
                                    'post_modified' => date('Y-m-d H:i:s')
                                );
                                wp_update_post($update_post_fields);

                                
                                update_post_meta( $post_id, 'product_description', $productDetails->description->english->text);
                                update_post_meta( $post_id, 'product_excerpt',  strip_tags( $productDetails->summary->english->text ));

                               // set a feature image for this post but first check to see if a feature is present

                                $feature = get_the_post_thumbnail($post_id);
                                if(empty($feature)){

                                    if( !empty( $productDetails->feature_image->xxlarge_image_path ) ){
                                    $url = "https:" . $productDetails->feature_image->xxlarge_image_path;
                                    $desc = $productDetails->product_title . " product image";
                                    $feature_image = media_sideload_image($url, $post_id, $desc);
                                    if(!empty($feature_image)){
                                        global $wpdb;
                                        $attachment     = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $feature_image )); 
                                        set_post_thumbnail( $post_id, $attachment[0] );
                                    }
                                }

                                }

                                //$response['msg'] = "Successfully re-imported product details";

                        }else{
                    
                        //create new post with operator details
                        $new_post_fields = array(
                            'post_title'        => $productDetails->product_title,
                            'post_status'       => 'publish',
                            'post_date'         => date('Y-m-d H:i:s'),
                            'post_author'       => $user_ID,
                            'post_type'         => 'narnoo_product',
                            'comment_status'    => 'closed',
                            'ping_status'       => 'closed'
                        );

                        if(!empty($productDetails->summary->english->text)){
                            $new_post_fields['post_excerpt'] = strip_tags( $productDetails->summary->english->text );
                        }

                        if(!empty($productDetails->description->english->text)){
                            $new_post_fields['post_content'] = strip_tags( $productDetails->description->english->text );
                        }
                       
                        $post_id = wp_insert_post($new_post_fields);
                        
                        // set a feature image for this post
                        if( !empty( $productDetails->feature_image->xxlarge_image_path ) ){
                            $url = "https:" . $productDetails->feature_image->xxlarge_image_path;
                            $desc = $productDetails->product_title . " product image";
                            $feature_image = media_sideload_image($url, $post_id, $desc);
                            if(!empty($feature_image)){
                                global $wpdb;
                                $attachment     = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $feature_image )); 
                                set_post_thumbnail( $post_id, $attachment[0] );
                            }
                        }
                        
                        //$response['msg'] = "Successfully imported product details";

                      }
                    

                    // insert/update custom fields with operator details into post
                    
                    if(!empty($productDetails->primary)){
                        update_post_meta($post_id, 'primary_product',               "Primary Product");
                    }else{
                        update_post_meta($post_id, 'primary_product',               "Product");
                    }
                     
                    

                    update_post_meta($post_id, 'narnoo_operator_id',            $op_id); 
                    update_post_meta($post_id, 'narnoo_operator_name',          $businessName);
                    update_post_meta($post_id, 'parent_post_id',                $postId);  
                    update_post_meta($post_id, 'narnoo_product_id',             $productDetails->product_id);
                    update_post_meta($post_id, 'product_min_price',             $productDetails->min_price);
                    update_post_meta($post_id, 'product_avg_price',             $productDetails->avg_price);
                    update_post_meta($post_id, 'product_max_price',             $productDetails->max_price);
                    update_post_meta($post_id, 'product_booking_link',          $productDetails->direct_booking);
                    
                    update_post_meta($post_id, 'narnoo_listing_category',       $category);
                    update_post_meta($post_id, 'narnoo_listing_subcategory',    $subCategory);

                    if( lcfirst( $category ) == 'attraction' ){


                        update_post_meta($post_id, 'narnoo_product_duration',   $productDetails->details->operating_hours);
                        update_post_meta($post_id, 'narnoo_product_start_time', $productDetails->details->start_time);
                        update_post_meta($post_id, 'narnoo_product_end_time',   $productDetails->details->end_time);
                        update_post_meta($post_id, 'narnoo_product_transport',  $productDetails->details->pickup_departure);
                        update_post_meta($post_id, 'narnoo_product_purchase',   $productDetails->details->purchase_options);
                        update_post_meta($post_id, 'narnoo_product_health',     $productDetails->details->health_requirements);
                        update_post_meta($post_id, 'narnoo_product_packing',    $productDetails->details->what_to_bring);
                        update_post_meta($post_id, 'narnoo_product_children',   $productDetails->details->children_information);
                        update_post_meta($post_id, 'narnoo_product_additional', $productDetails->details->additional_information);
                        
                    }
                    /**
                    *
                    *   Import the gallery images as JSON encoded object
                    *
                    */
                    if(!empty($productDetails->gallery)){
                        update_post_meta($post_id, 'narnoo_product_gallery', json_encode($productDetails->gallery) );
                    }else{
                        delete_post_meta($post_id, 'narnoo_product_gallery');
                    }
                    /**
                    *
                    *   Import the video player object
                    *
                    */
                    if(!empty($productDetails->feature_video)){
                        update_post_meta($post_id, 'narnoo_product_video', json_encode($productDetails->feature_video) );
                    }else{
                        delete_post_meta($post_id, 'narnoo_product_video');
                    }
                    /**
                    *
                    *   Import the brochure object
                    *
                    */
                    if(!empty($productDetails->feature_print)){   

                        update_post_meta($post_id, 'narnoo_product_print', json_encode($productDetails->feature_print) );
                    }else{

                        delete_post_meta($post_id, 'narnoo_product_print');
                    }
                        
            } //if success
        
        } //loop
        /************************************************************************************
        *
        *                   ----- End of products loop ----- 
        *
        *************************************************************************************/
        

        return $products->total_products;

    }

    /**
     * Checks to see if the custom post type has been initiated. If not then initiate it.
     * Returns boolean.
     *
     */
    static function product_post_type_init( ) {

        if( post_type_exists( 'narnoo_product' ) ) {
              return TRUE;
        }

        return false;  
            
    }

    /**
     * Retrieves Wordpress post ID for imported product ID, if it exists.
     * Returns false if no such product exists in Wordpress DB.
     * */
    static function get_post_id_for_imported_product_id($product_id) {
            
            $imported_posts = get_posts(array('post_type' => 'narnoo_product','numberposts' => -1));
            foreach ($imported_posts as $post) {
                $id = get_post_meta($post->ID, 'narnoo_product_id', true);
                
                if ($id === $product_id) {
                    $result['id']       = $post->ID;
                    $result['status'] = get_post_status( $post->ID );                    
                    return $result;
                }

            }

        return false;
    }

    /**
     * Inserts specified image from Narnoo Media/Narnoo Operator Media Library into Wordpress Media Library.
     * */
    static function ajax_add_image_to_wordpress_media_library() {
        if (!isset($_POST['image_url']) || !isset($_POST['image_title'])) {
            self::ajax_fatal_error(__('AJAX error: Missing arguments.', NARNOO_DISTRIBUTOR_I18N_DOMAIN));
        }

        $url = $_POST['image_url'];
        $image_title = $_POST['image_title'];

        $tmp = download_url($url);
        $file_array = array(
            'name' => basename($url),
            'tmp_name' => $tmp
            );

        // Check for download errors
        if (is_wp_error($tmp)) {
            @unlink($file_array['tmp_name']);
            self::ajax_fatal_error(sprintf(__('AJAX error: Could not download image <a href="%s">%s</a>.', NARNOO_DISTRIBUTOR_I18N_DOMAIN), $url, $url));
        }

        $id = media_handle_sideload($file_array, 0, $image_title);
        // Check for handle sideload errors.
        if (is_wp_error($id)) {
            @unlink($file_array['tmp_name']);
            self::ajax_fatal_error(__('AJAX error: Could not add to library.', NARNOO_DISTRIBUTOR_I18N_DOMAIN));
        }

        // return response
        echo json_encode(array('success' => 'success'));
        die();
    }
    /**
    *
    *   @comment: Formater to make sure all URL have HTTPS infront of them
    *   @usage: When rendering URL to the page.
    */
    public function url_formating($url){
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }
        return $url;
    }
    /**
    *
    *   @comment: Formater to make sure all phone numbers are correct
    *   @usage: When rendering phone number to the page.
    */
    public function phone_formating($phone = '', $trim = true){

               // If we have not entered a phone number just return empty
                if (empty($phone)) {
                    return '';
                }

                // Strip out any extra characters that we do not need only keep letters and numbers
                $phone = preg_replace("/[^0-9A-Za-z]/", "", $phone);



                // If we have a number longer than 11 digits cut the string down to only 11
                // This is also only ran if we want to limit only to 11 characters
                if ($trim == true && strlen($phone)>12) {
                    $phone = substr($phone, 0, 12);
                }

                // Perform phone number formatting here
                if (strlen($phone) == 7) {
                    return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1-$2", $phone);
                } elseif (strlen($phone) == 9) {
                    return preg_replace("/([0-9a-zA-Z]{1})([0-9a-zA-Z]{4})([0-9a-zA-Z]{4})/", "+61 (0$1) $2-$3", $phone);
                }elseif (strlen($phone) == 10) {

                        //Look for 1800 + 1300 numbers
                        if (strpos($phone, '1800') !== false) {
                          return preg_replace("/([0-9a-zA-Z]{4})([0-9a-zA-Z]{2})([0-9a-zA-Z]{2})([0-9a-zA-Z]{2})/", "$1 $2 $3 $4", $phone);
                        }elseif (strpos($phone, '1300') !== false) {
                          return preg_replace("/([0-9a-zA-Z]{4})([0-9a-zA-Z]{2})([0-9a-zA-Z]{2})([0-9a-zA-Z]{2})/", "$1 $2 $3 $4", $phone);
                        }else{
                          return preg_replace("/([0-9a-zA-Z]{2})([0-9a-zA-Z]{4})([0-9a-zA-Z]{4})/", "+61 ($1) $2-$3", $phone);
                        }

                } elseif (strlen($phone) == 11) { //number has the only 7 in it...
                    return preg_replace("/([0-9a-zA-Z]{2})([0-9a-zA-Z]{1})([0-9a-zA-Z]{4})([0-9a-zA-Z]{4})/", "+$1 (0$2) $3-$4", $phone);
                }elseif (strlen($phone) == 12) { //number has the 07 in it...
                    return preg_replace("/([0-9a-zA-Z]{2})([0-9a-zA-Z]{2})([0-9a-zA-Z]{4})([0-9a-zA-Z]{4})/", "+$1 ($2) $3-$4", $phone);
                }

                return $phone;


    }

}