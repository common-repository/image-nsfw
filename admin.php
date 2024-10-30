<?php

function MODERATECONTENT__PLUGIN_create_menu() {
    $api_key = get_option('MODERATECONTENT__PLUGIN_unique_key', "");
 
    add_menu_page('Settings', 'Image (NSFW)', 'manage_options', 'MODERATECONTENT__PLUGIN_settings_page', 'MODERATECONTENT__PLUGIN_settings_page' , "dashicons-format-image" ); //MODERATECONTENT__PLUGIN_URL . "/assets/icon.png"
    add_submenu_page( 'MODERATECONTENT__PLUGIN_settings_page', 'Settings', 'Settings', 'manage_options', 'MODERATECONTENT__PLUGIN_settings_page', 'MODERATECONTENT__PLUGIN_settings_page');
    if (strlen($api_key) > 20){
        add_submenu_page( 'MODERATECONTENT__PLUGIN_settings_page', 'Images', 'Images', 'manage_options', 'MODERATECONTENT__PLUGIN_images_page', 'MODERATECONTENT__PLUGIN_images_page');
        add_submenu_page( 'MODERATECONTENT__PLUGIN_settings_page', 'Logs', 'Logs', 'manage_options', 'MODERATECONTENT__PLUGIN_logs_page', 'MODERATECONTENT__PLUGIN_logs_page');
    }
    add_action( 'admin_init', 'MODERATECONTENT__PLUGIN_register_settings' );
}
add_action('admin_menu', 'MODERATECONTENT__PLUGIN_create_menu');

function MODERATECONTENT__PLUGIN_register_key(){
    $key = "111";
    $email = get_option('admin_email');
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $url = 'https://www.moderatecontent.com/documentation/api';
        $post_data = array( 'email' => $email, 'wp_source_flag' => 'true' );
        $result = wp_remote_post( $url, array( 'body' => $post_data ) );
        $result = json_decode($result["body"]);
        $key = $result->api_key;
        delete_option( 'MODERATECONTENT__PLUGIN_unique_key' );
        delete_option( 'MODERATECONTENT__PLUGIN_debug' );
        delete_option( 'MODERATECONTENT__PLUGIN_action_remove' );
        delete_option( 'MODERATECONTENT__PLUGIN_action_email' );
        delete_option( 'MODERATECONTENT__PLUGIN_action_email_address' );
        delete_option( 'MODERATECONTENT__PLUGIN_action_blur' );
        delete_option( 'MODERATECONTENT__PLUGIN_action_blur_option_to_reveal' );
        delete_option( 'MODERATECONTENT__PLUGIN_warning_level' );
        delete_option( 'MODERATECONTENT__PLUGIN_manual_review_level' );
        
        add_option( 'MODERATECONTENT__PLUGIN_unique_key', $result->api_key, '', 'yes' );
        add_option( 'MODERATECONTENT__PLUGIN_debug', "false", '', 'yes' );
        add_option( 'MODERATECONTENT__PLUGIN_action_remove', "true", '', 'yes' );
        add_option( 'MODERATECONTENT__PLUGIN_action_email', "false", '', 'yes' );
        add_option( 'MODERATECONTENT__PLUGIN_action_email_address', get_option('admin_email'), '', 'yes' );
        add_option( 'MODERATECONTENT__PLUGIN_action_blur', "false", '', 'yes' );
        add_option( 'MODERATECONTENT__PLUGIN_action_blur_option_to_reveal', "false", '', 'yes' );
        add_option( 'MODERATECONTENT__PLUGIN_warning_level', "teen", '', 'yes' );
        add_option( 'MODERATECONTENT__PLUGIN_manual_review_level', "all", '', 'yes' );

        foreach($GLOBALS['MODERATECONTENT__events'] as $event){
            delete_option( $event->option->label );
            add_option( $event->option->label, $event->option->default_value, '', 'yes' );
        }

    }
    echo $key;
    wp_die();
}
add_action( 'wp_ajax_MODERATECONTENT__PLUGIN_register_key', 'MODERATECONTENT__PLUGIN_register_key' );



function MODERATECONTENT__PLUGIN_register_settings(){
    register_setting( 'MODERATECONTENT__PLUGIN_settings_group', 'MODERATECONTENT__PLUGIN_unique_key' );
    register_setting( 'MODERATECONTENT__PLUGIN_settings_group', 'MODERATECONTENT__PLUGIN_debug' );
    register_setting( 'MODERATECONTENT__PLUGIN_settings_group', 'MODERATECONTENT__PLUGIN_action_remove' );
    register_setting( 'MODERATECONTENT__PLUGIN_settings_group', 'MODERATECONTENT__PLUGIN_action_email' );
    register_setting( 'MODERATECONTENT__PLUGIN_settings_group', 'MODERATECONTENT__PLUGIN_action_email_address' );
    register_setting( 'MODERATECONTENT__PLUGIN_settings_group', 'MODERATECONTENT__PLUGIN_action_blur' );
    register_setting( 'MODERATECONTENT__PLUGIN_settings_group', 'MODERATECONTENT__PLUGIN_action_blur_option_to_reveal' );
    register_setting( 'MODERATECONTENT__PLUGIN_settings_group', 'MODERATECONTENT__PLUGIN_warning_level' );
    register_setting( 'MODERATECONTENT__PLUGIN_settings_group', 'MODERATECONTENT__PLUGIN_manual_review_level' );

    foreach($GLOBALS['MODERATECONTENT__events'] as $event){
        register_setting( 'MODERATECONTENT__PLUGIN_settings_group', $event->option->label );
    }
}

function MODERATECONTENT__PLUGIN_settings_page() {
    $api_key = get_option('MODERATECONTENT__PLUGIN_unique_key', "");
    ?>
    <h1>Settings</h1>
    <table  style="border: 1px solid #dfdede;background-color: white;width:98%;padding: 20px;">
    <tr>
        <td colspan="3" style="padding:10px;text-align:left;">
            <a href="https://moderatecontent.com" target="_blank"><img style="max-width: 200px;" src="https://moderatecontent.com/img/mdr8/logo_v3.png" /></a><br />
            <p>Welcome to <b>Image (NSFW)</b>, a plugin to protect your site and users from inappropriate content uploads. Powered by the API at <a href="https://www.moderatecontent.com/"><b>ModerateContent.com.</b></a></b></p>
    <?php
    if (strlen($api_key) > 20){
        echo "<p>A key has been generated and is now activated for the product.</p>";
    } else {
        ?>
        <p>To get started please register an api key. We send <b>your email address</b> to moderatecontent.com to generate the key. </p>
        <div id="MODERATECONTENT__PLUGIN_get_api_key" data-api_key="<?php echo get_option('MODERATECONTENT__PLUGIN_unique_key',""); ?>" class="button button-primary" >Register - One Click (Free)</div>
        <div id="MODERATECONTENT__PLUGIN_get_api_key_error" style="color:red;"></div>
        <?php
    }
    if (strlen($api_key) > 20){
    ?>
            
        </td>
    </tr>
    <form method="post" action="options.php">
    <?php
    settings_fields( 'MODERATECONTENT__PLUGIN_settings_group' );
    do_settings_sections( 'MODERATECONTENT__PLUGIN_settings_group' );
    ?>
    <tr>
        <td style="padding:10px;background-color:#303030;color:white;">Key</td>
        <td style="padding:10px;background-color:#303030;color:white;">Value</td>
        <td style="padding:10px;background-color:#303030;color:white;"></td>
    </tr>
    <tr>
        <td style="padding:10px;background-color:#f0f0f0;">API Key</td>
        <td style="padding:10px;background-color:#f0f0f0;"">
            <input type="hidden" name="MODERATECONTENT__PLUGIN_unique_key" value="<?php echo get_option('MODERATECONTENT__PLUGIN_unique_key',""); ?>" />
            <?php echo get_option('MODERATECONTENT__PLUGIN_unique_key'); ?>
            <div id="MODERATECONTENT__PLUGIN_test_api_key" data-api_key="<?php echo get_option('MODERATECONTENT__PLUGIN_unique_key',""); ?>" class="button button-primary" style="height: 20px;font-size: 10px;line-height: 20px;">Test</div>
            <div id="MODERATECONTENT__PLUGIN_test_api_key_test_result"></div>
        </td>
        <td style="padding:10px;background-color:#f0f0f0;"">
            <span style="font-size:12px;">ModerateContent.com API key.</span>
        </td>
    </tr>
    <tr>
        <td style="padding:10px;background-color:#f0f0f0;">Enable Debug Logging</td>
        <td style="padding:10px;background-color:#f0f0f0;"">
            <?php 
                $html = '      <select name="MODERATECONTENT__PLUGIN_debug">';
                if (get_option('MODERATECONTENT__PLUGIN_debug') == "true"){
                    $html .= '          <option value="true" selected>True</option>';
                    $html .= '          <option value="false">False</option>';
                } else {
                    $html .= '          <option value="true">True</option>';
                    $html .= '          <option value="false" selected>False</option>';
                }
                $html .= '      </select>';
                echo $html;
             ?>
             
        </td>
        <td style="padding:10px;background-color:#f0f0f0;"">
            <span style="font-size:12px;">Set to true to see detailed messages on the plugin "Logs" page.</span>
        </td>
    </tr>
    <tr>
        <td style="padding:10px;background-color:#f0f0f0;">Enable Action Remove</td>
        <td style="padding:10px;background-color:#f0f0f0;"">
            <?php 
                $html = '      <select name="MODERATECONTENT__PLUGIN_action_remove">';
                if (get_option('MODERATECONTENT__PLUGIN_action_remove') == "true"){
                    $html .= '          <option value="true" selected>True</option>';
                    $html .= '          <option value="false">False</option>';
                } else {
                    $html .= '          <option value="true">True</option>';
                    $html .= '          <option value="false" selected>False</option>';
                }
                $html .= '      </select>';
                echo $html;
             ?>
        </td>
        <td style="padding:10px;background-color:#f0f0f0;">
            <span style="font-size:12px;">Removes offending image when an inappropriate image (Adult) is detected.</span>
        </td>
    </tr>
    <tr>
        <td style="padding:10px;background-color:#f0f0f0;">Enable Action Email</td>
        <td style="padding:10px;background-color:#f0f0f0;"">
            <?php 
                $html = '      <select name="MODERATECONTENT__PLUGIN_action_email">';
                if (get_option('MODERATECONTENT__PLUGIN_action_email') == "true"){
                    $html .= '          <option value="true" selected>True</option>';
                    $html .= '          <option value="false">False</option>';
                } else {
                    $html .= '          <option value="true">True</option>';
                    $html .= '          <option value="false" selected>False</option>';
                }
                $html .= '      </select>';
                echo $html;

                $email = get_option('MODERATECONTENT__PLUGIN_action_email_address');
                if ($email == "") $email = get_option('admin_email');
             ?>
             <br /><input type="text" name="MODERATECONTENT__PLUGIN_action_email_address" style="padding: 5px;font-size: 15px;width: 100%;" value="<?php echo $email; ?>" />
        </td>
        <td style="padding:10px;background-color:#f0f0f0;">
            <span style="font-size:12px;">Sends the site admin an email when an inappropriate image (Adult) is detected.</span>
        </td>
    </tr>
    <!-- <tr>
        <td style="padding:10px;background-color:#f0f0f0;">Enable Action Blur With Option</td>
        <td style="padding:10px;background-color:#f0f0f0;"">
            <?php 
                $html = '      <select name="MODERATECONTENT__PLUGIN_action_blur_option_to_reveal">';
                if (get_option('MODERATECONTENT__PLUGIN_action_blur_option_to_reveal') == "true"){
                    $html .= '          <option value="true" selected>True</option>';
                    $html .= '          <option value="false">False</option>';
                } else {
                    $html .= '          <option value="true">True</option>';
                    $html .= '          <option value="false" selected>False</option>';
                }
                $html .= '      </select>';
                echo $html;
             ?>
             
        </td>
        <td style="padding:10px;background-color:#f0f0f0;"">
            <span style="font-size:12px;">Blurs the image when an inappropriate image (Adult) is detected with an option to reveal.</span>
        </td>
    </tr> -->
    <!-- <tr>
        <td style="padding:10px;background-color:#f0f0f0;">Enable Action Blur</td>
        <td style="padding:10px;background-color:#f0f0f0;"">
            <?php 
                $html = '      <select name="MODERATECONTENT__PLUGIN_action_blur">';
                if (get_option('MODERATECONTENT__PLUGIN_action_blur') == "true"){
                    $html .= '          <option value="true" selected>True</option>';
                    $html .= '          <option value="false">False</option>';
                } else {
                    $html .= '          <option value="true">True</option>';
                    $html .= '          <option value="false" selected>False</option>';
                }
                $html .= '      </select>';
                echo $html;
             ?>
             
        </td>
        <td style="padding:10px;background-color:#f0f0f0;"">
            <span style="font-size:12px;">Blurs the image when an inappropriate image (Adult) is detected.</span>
        </td>
    </tr> -->

    <tr>
        <td style="padding:10px;background-color:#f0f0f0;">Warn When Image </td>
        <td style="padding:10px;background-color:#f0f0f0;"">
            <?php 
                $warn = get_option('MODERATECONTENT__PLUGIN_warning_level');
                $html = '      <select name="MODERATECONTENT__PLUGIN_warning_level">';
                $html .= '          <option value="teen"' . (($warn=="teen")?"selected":"") .'>Teen</option>';
                $html .= '          <option value="adult"' . (($warn=="adult")?"selected":"") .'>Adult</option>';
                $html .= '          <option value="exploited"' . (($warn=="exploited")?"selected":"") .'>Exploited</option>';
                $html .= '      </select>';
                echo $html;
             ?>
        </td>
        <td style="padding:10px;background-color:#f0f0f0;">
            <span style="font-size:12px;">Take selected action(s) when the image equals this rating or worse.</span>
        </td>
    </tr>

    <tr>
        <td style="padding:10px;background-color:#f0f0f0;">Manual Review Level  </td>
        <td style="padding:10px;background-color:#f0f0f0;">
            <?php 
                $warn2 = get_option('MODERATECONTENT__PLUGIN_manual_review_level');
                $html = '      <select name="MODERATECONTENT__PLUGIN_manual_review_level">';
                $html .= '          <option value="all"' . (($warn2=="all")?"selected":"") .'>All</option>';
                $html .= '          <option value="teen"' . (($warn2=="teen")?"selected":"") .'>Teen</option>';
                $html .= '          <option value="adult"' . (($warn2=="adult")?"selected":"") .'>Adult</option>';
                $html .= '          <option value="exploited"' . (($warn2=="exploited")?"selected":"") .'>Exploited</option>';
                $html .= '      </select>';
                echo $html;
             ?>
        </td>
        <td style="padding:10px;background-color:#f0f0f0;">
            <span style="font-size:12px;">Images at or below this warning level appear in the (Images) panel.</span>
        </td>
    </tr>
    </table>
    <table  style="border: 1px solid #dfdede;background-color: white;width:98%;padding: 20px;">
    <tr>
        <td colspan="3"  style="padding:10px;background-color:#303030;color:white;">Enabled Events</td>
    </tr>
    <tr>
        <?php
        foreach($GLOBALS['MODERATECONTENT__events'] as $event){
            $html  = '<tr>';
            $html .= '<td style="padding:10px;background-color:#f0f0f0;width:32%;">';
            $html .=    $event->filter . " " ;
            $html .= '</td>';
            $html .= '<td style="padding:10px;background-color:#f0f0f0;width:32%;">';
            $html .= '      <select name="'.$event->option->label.'">';
            if (get_option($event->option->label) == "true"){
                $html .= '          <option value="true" selected>True</option>';
                $html .= '          <option value="false">False</option>';
            } else {
                $html .= '          <option value="true">True</option>';
                $html .= '          <option value="false" selected>False</option>';
            }
            $html .= '      </select>';
            $html .= '</td>';
            $html .= '<td style="padding:10px;background-color:#f0f0f0;width:32%;">';
            $html .= '  Should this event be moderated.';
            $html .= '</td>';
            $html .= '</tr>';
            echo $html;
        }

        ?>
        </td>
    </tr>
    </table>
    <table  style="border: 1px solid #dfdede;background-color: white;width:98%;padding: 20px;">
    <tr>
        <td colspan="3" style="padding:10px;text-align:right;"><input  type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></td>
    </tr>
    </form>
    </table>
    <?php
    }
}

function MODERATECONTENT__PLUGIN_logs_page() {
    $url = admin_url();
    if ($_GET["action"]== "clear_log_file"){
        $file = MODERATECONTENT__PLUGIN_DIR . "logs/" . get_option('MODERATECONTENT__PLUGIN_unique_key',"").'_log.txt';
        if (file_exists($file))
            unlink($file);
    } else if ($_GET["action"]== "test_image_evaluation"){
        MODERATECONTENT__PLUGIN_review_file("http://www.moderatecontent.com/img/logo.png", "test image evaluation file");
    }
    ?>
    <h1>Logs</h1>
    <table  style="border: 1px solid #dfdede;background-color: white;width:98%;padding: 20px;" id="MODERATECONTENT__PLUGIN_content">
        <tr>
            <td style="padding:10px;text-align:left;">
                <a href="https://moderatecontent.com" target="_blank"><img style="max-width: 200px;" src="https://moderatecontent.com/img/mdr8/logo_v3.png" /></a><br />
                <p>The log of activity when users upload images. For more detailed logs, set "<b>Enable Debug Logging</b>" on the "<b>Settings</b>" page in the "<b>Image (NSFW)</b>" plugin to "<b>True</b>"</p>
                <div style="margin-bottom:20px;">
                    <div onclick="location.href='<?php echo $url; ?>admin.php?page=MODERATECONTENT__PLUGIN_logs_page&action=clear_log_file';" class="button button-primary">Clear Log Files</div>
                    <div onclick="location.href='<?php echo $url; ?>admin.php?page=MODERATECONTENT__PLUGIN_logs_page&action=test_image_evaluation';" class="button button-primary">Test Image Evaluation</div>
                </div>
            </td>
        </tr>
        <tr>
            <td style="padding:10px;background-color:#303030;color:white;">Details</td>
        </tr>
        <tr>
            <td style="padding:10px;background-color:#f0f0f0;">
                <?php
                $file = MODERATECONTENT__PLUGIN_DIR . "logs/" . get_option('MODERATECONTENT__PLUGIN_unique_key',"").'_log.txt';
                if (file_exists($file)){
                    $file_lines = file($file);
                    $file_lines = array_reverse($file_lines);
                    foreach($file_lines as $line){
                        $html .= str_replace(chr(13).chr(10),"<hr />",$line);
                    }
                } else {
                    $html .= "No image moderation attemps have been made yet, try uploading an image.";
                }
                echo $html;
                ?>
            </td>
        </tr>
    </table>
    <?php
}

function MODERATECONTENT__PLUGIN_images_page() {
    if ($_GET["action"]== "clear_image_logs"){
        global $wpdb;
        $table_name = $wpdb->prefix . 'moderate_content_requests';

        $sql = "DELETE FROM " . $table_name . ";";
        $wpdb->get_results( $sql, OBJECT );
    }
    wp_enqueue_script("moderatecontent_plugin_image1",MODERATECONTENT__PLUGIN_URL."js/moderatecontent_plugin_image.js",array("jquery"), '1.0.0', true);
    ?>
    <h1>Images</h1>
    <table  style="border: 1px solid #dfdede;background-color: white;width:98%;padding: 20px;" id="MODERATECONTENT__PLUGIN_content">
        <tr>
            <td colspan="3" style="padding:10px;text-align:left;">
               <a href="https://moderatecontent.com"  target="_blank"><img style="max-width: 200px;" src="https://moderatecontent.com/img/mdr8/logo_v3.png" /></a><br />
               <p>The list of all the images your users have uploaded and their ratings.</p>
               <div style="margin-bottom:20px;">
                    <div onclick="location.href='<?php echo $url; ?>admin.php?page=MODERATECONTENT__PLUGIN_images_page&action=clear_image_logs';" class="button button-primary">Clear Image Logs</div>
                </div>
            </td>
        </tr>
        <tr>
            <td style="padding:10px;background-color:#303030;color:white;">Image</td>
            <td style="padding:10px;background-color:#303030;color:white;">Rating</td>
            <td style="padding:10px;background-color:#303030;color:white;">Details</td>
        </tr>
    </table>
    <script type="text/javascript">
        var MODERATECONTENT__PLUGIN_URL = "<?php echo MODERATECONTENT__PLUGIN_URL; ?>";
    </script>
    <?php
}

function MODERATECONTENT__PLUGIN_action_callback() {
	global $wpdb;
    $table_name = $wpdb->prefix . 'moderate_content_requests';

    $warn2 = get_option('MODERATECONTENT__PLUGIN_manual_review_level');
    
    $response = (object) array();
    $response->page = intval( $_POST['page'] );
    $response->index = $response->page * 10;
    $sql = "SELECT * FROM $table_name ";
    if ($warn2 == 'all') $sql .= "WHERE rating = 'everyone' or rating = 'na' or  rating = 'teen' or rating = 'adult' or rating = 'exploited' ";
    if ($warn2 == 'teen') $sql .= "WHERE rating = 'teen' or rating = 'adult' or rating = 'exploited' ";
    if ($warn2 == 'adult') $sql .= "WHERE rating = 'adult' or rating = 'exploited' ";
    if ($warn2 == 'exploited') $sql .= "WHERE rating = 'exploited' ";
    $sql .= "ORDER BY id DESC LIMIT $response->index, 10;";
	$response->images = $wpdb->get_results( $sql, OBJECT );
        
    $sql = "SELECT count(*) as count FROM $table_name ";
    if ($warn2 == 'all') $sql .= "WHERE rating = 'everyone' or rating = 'na' or  rating = 'teen' or rating = 'adult' or rating = 'exploited' ";
    if ($warn2 == 'teen') $sql .= "WHERE rating = 'teen' or rating = 'adult' or rating = 'exploited' ";
    if ($warn2 == 'adult') $sql .= "WHERE rating = 'adult' or rating = 'exploited' ";
    if ($warn2 == 'exploited') $sql .= "WHERE rating = 'exploited' ";
    $sql .= ";";
	$response->image_count = $wpdb->get_results( $sql, OBJECT )[0]->count;
        
    echo json_encode($response);
    wp_die();
}
add_action( 'wp_ajax_get_images', 'MODERATECONTENT__PLUGIN_action_callback' );

function MODERATECONTENT__PLUGIN_rating_callback() {
	global $wpdb;
        $table_name = $wpdb->prefix . 'moderate_content_requests';
        
        $id = intval( $_POST['id'] );
        $rating = $_POST['rating'];
        if ($rating == "teen" || $rating == "adult" || $rating == "everyone"){
            $sql = "UPDATE $table_name SET rating = '$rating' WHERE id = $id;";
            $wpdb->query( $sql );
            
            $sql = "SELECT * FROM $table_name WHERE id = $id;";
            $response = $wpdb->get_results( $sql, OBJECT );
            
            $current_user = wp_get_current_user();
            $username = $current_user->user_login;
            $file = $response[0]->file;
            MODERATECONTENT__PLUGIN_l("Manual overide on content rating: $rating for file: $file by username: $username.", true);
            
            echo json_encode($response );
        } else {
            echo "Error improper request";
        }
        wp_die();
}
add_action( 'wp_ajax_change_rating', 'MODERATECONTENT__PLUGIN_rating_callback' );