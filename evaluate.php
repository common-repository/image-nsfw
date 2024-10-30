<?php
function MODERATECONTENT__PLUGIN_the_content($content){
    foreach($GLOBALS['MODERATECONTENT__events'] as $event){
        if ($event->filter == current_filter()){
            if (get_option($event->option->label) != "true"){
                MODERATECONTENT__PLUGIN_l("Event: ".$event->filter." DISABLED");
                return $content;
            }
        }
        
    }
    MODERATECONTENT__PLUGIN_l("Event: " . current_filter());
    MODERATECONTENT__PLUGIN_l("MODERATECONTENT__PLUGIN_the_content");
    // if (is_array($content)){
        // MODERATECONTENT__PLUGIN_l("MODERATECONTENT__PLUGIN_the_content ----------- FOUND ARRAY");
    // } else {
        // MODERATECONTENT__PLUGIN_l("MODERATECONTENT__PLUGIN_the_content: " . $content);
    // }
    $data=[];
    $data["post_content"] = $content;
    $data = MODERATECONTENT__PLUGIN_post_published_notification($data,[]);
    // MODERATECONTENT__PLUGIN_l("MODERATECONTENT__PLUGIN_the_content: " . $data["post_content"]);
    return $data["post_content"];
}

function MODERATECONTENT__PLUGIN_post_published_notification($data , $postarr){
    MODERATECONTENT__PLUGIN_l("Event: wp_insert_post_data");
    MODERATECONTENT__PLUGIN_l("MODERATECONTENT__PLUGIN_post_published_notification START");
    $post_content = $data["post_content"];
    $post_content = str_replace("\\","",$post_content);
    preg_match_all('/<img[^>]+>/i',$post_content, $images); 
    foreach($images[0] as $key => $image){
        MODERATECONTENT__PLUGIN_l("Image Found: " . $image);
        $warning_level = get_option('MODERATECONTENT__PLUGIN_warning_level');
        $exploited_flag = ($warning_level=="exploited")?"true":"false";
        $origional_img = $image;
        preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $image, $sources);
        $origional_src = $sources[1];
        $json = MODERATECONTENT__PLUGIN_review_file($origional_src, "-- url --", $exploited_flag);
        if ($json->error_code == "0"){
            $take_action_flag = false;
           
            if ($json->rating_label == "exploited") $take_action_flag = true;
            if ($warning_level == "teen" && $json->rating_label == "adult") $take_action_flag = true;
            if ($warning_level == "adult" && $json->rating_label == "adult") $take_action_flag = true;
            if ($take_action_flag == true){
                if (get_option('MODERATECONTENT__PLUGIN_action_remove') == "true"){
                    $upload_dir = wp_upload_dir();
                    $adult_file = $upload_dir["basedir"] . "/rating_adult_box.png";
                    if (!file_exists($adult_file)){
                        copy(MODERATECONTENT__PLUGIN_DIR . "img/rating_adult_box.png", $adult_file);
                    }
                    $new_source = $upload_dir["baseurl"] . "/rating_adult_box.png";
                    $new_image = str_replace($origional_src, $new_source, $origional_img);
                    $post_content = str_replace($origional_img,$new_image,$post_content);
                }
                if (get_option('MODERATECONTENT__PLUGIN_action_blur') == "true"){
                    MODERATECONTENT__PLUGIN_take_action_blur($url, $file, $json);
                }
                if (get_option('MODERATECONTENT__PLUGIN_action_blur_option_to_reveal') == "true"){
                    MODERATECONTENT__PLUGIN_take_action_blur_option_to_reveal($url, $file, $json);
                }
                if (get_option('MODERATECONTENT__PLUGIN_action_email') == "true"){
                    MODERATECONTENT__PLUGIN_take_action_email($url, $file, $json);
                }
            } else {
                MODERATECONTENT__PLUGIN_l("Approved File:" . $file);
            }
        }
    }
    $data["post_content"] = $post_content;
    return $data;
}

function MODERATECONTENT__PLUGIN_handle_upload( $fileinfo ){
    foreach($GLOBALS['MODERATECONTENT__events'] as $event){
        if ($event->filter == current_filter()){
            if (get_option($event->option->label) != "true"){
                MODERATECONTENT__PLUGIN_l("Event: wp_handle_upload DISABLED");
                return $fileinfo;
            }
        }
    }
    MODERATECONTENT__PLUGIN_l("Event: wp_handle_upload");
    MODERATECONTENT__PLUGIN_l("File: " . $fileinfo["file"]);
    MODERATECONTENT__PLUGIN_l("Url: " . $fileinfo["url"]);
    MODERATECONTENT__PLUGIN_l("Type: " . $fileinfo["type"]);
    $file = $fileinfo["file"];
    $url = $fileinfo["url"];
    $type = $fileinfo["type"];


    if ($type == "image/jpeg" || $type == "image/jpg" || $type == "image/gif" || $type == "image/png"){
        MODERATECONTENT__PLUGIN_l("Review File Start: " . $url);
        $warning_level = get_option('MODERATECONTENT__PLUGIN_warning_level');
        $exploited_flag = ($warning_level=="exploited")?"true":"false";
        $json = MODERATECONTENT__PLUGIN_review_file($url, $file, $exploited_flag);
        if ($json->error_code == "0"){
            $take_action_flag = false;
            if ($json->rating_label == "exploited") $take_action_flag = true;
            if ($warning_level == "teen" && $json->rating_label == "adult") $take_action_flag = true;
            if ($warning_level == "adult" && $json->rating_label == "adult") $take_action_flag = true;
            if ($take_action_flag == true){
                if (get_option('MODERATECONTENT__PLUGIN_action_remove') == "true"){
                    $fileinfo = MODERATECONTENT__PLUGIN_take_action_remove($fileinfo);
                }
                if (get_option('MODERATECONTENT__PLUGIN_action_blur') == "true"){
                    MODERATECONTENT__PLUGIN_take_action_blur($url, $file, $json);
                }
                if (get_option('MODERATECONTENT__PLUGIN_action_blur_option_to_reveal') == "true"){
                    MODERATECONTENT__PLUGIN_take_action_blur_option_to_reveal($url, $file, $json);
                }
                if (get_option('MODERATECONTENT__PLUGIN_action_email') == "true"){
                    MODERATECONTENT__PLUGIN_take_action_email($url, $file, $json);
                }
                
            } else {
                MODERATECONTENT__PLUGIN_l("Approved File:" . $file);
            }
        }
        MODERATECONTENT__PLUGIN_l("Review File End: " . $url . " " . $json->rating_label);
    } else {
        MODERATECONTENT__PLUGIN_l("Not file type: image/jpeg, image/jpg, image/gif, image/png");
    }
    return $fileinfo;
}

function MODERATECONTENT__PLUGIN_review_file($url, $file, $exploited_flag="false"){
    // $moderate_url = "https://api.moderatecontent.com/moderate/?key=".get_option('MODERATECONTENT__PLUGIN_unique_key',"")."&url=".$url."&exploited_flag=".$exploited_flag;
    $moderate_url = "https://www.moderatecontent.com/api/web_api?exploited=".$exploited_flag."&key=".get_option('MODERATECONTENT__PLUGIN_unique_key',"")."&url=".$url."&exploited_flag=".$exploited_flag;
    MODERATECONTENT__PLUGIN_l('Moderate Url: ' . $moderate_url);
    $ch = curl_init($moderate_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);

    if (curl_error($ch) != ""){
        MODERATECONTENT__PLUGIN_l("Curl Error: " . curl_error($ch));
    } else {
        MODERATECONTENT__PLUGIN_l("Curl Success");
    }

    curl_close($ch);
    $json = json_decode($result);

    if ( !function_exists( 'bp_is_active') ) {
        MODERATECONTENT__PLUGIN_create_eval_record($file, $url, $json);
    } else {
        if (file_exists($file)){
            $path_info = pathinfo($file);
            $url_backup = MODERATECONTENT__PLUGIN_URL."flagged_images/".md5($url).".".$path_info['extension'];
            $file_backup = MODERATECONTENT__PLUGIN_DIR."flagged_images/".md5($url).".".$path_info['extension'];
            copy($file, $file_backup);
        } else {
            $url_backup = $url;
        }
        MODERATECONTENT__PLUGIN_create_eval_record($file, $url_backup, $json);
    }

    
    MODERATECONTENT__PLUGIN_l("Moderate Response:" . $result, true);
    
    return $json;
}

function MODERATECONTENT__PLUGIN_take_action_blur($url, $file, $json){
    // add overlay to image with css
}

function MODERATECONTENT__PLUGIN_take_action_blur_option_to_reveal($url, $file, $json){
    // add overlay to image with css
    // add warning message
    // on click reveal
}

function MODERATECONTENT__PLUGIN_take_action_email($url, $file, $json){
    if (MODERATECONTENT__is_duplicate($file, $url)) return "";
    
    MODERATECONTENT__PLUGIN_l('MODERATECONTENT__PLUGIN_take_action_email');
    $admin_email = get_option('MODERATECONTENT__PLUGIN_action_email_address');
    if ($admin_email == "") $admin_email = get_option('admin_email');

    $blog_name =  get_option('blogname');
    $subject = 'WordPress - ' . $blog_name . ': Suspicous File Upload';
    $message  = "";
    $message .= 'WordPress - ' . $blog_name . ': Suspicous File Upload' .chr(13).chr(10).chr(13).chr(10);
    $current_user = wp_get_current_user();
    if ( $current_user->exists() ){
        $message .= 'Username: ' .$current_user->user_login.chr(13).chr(10);
        $message .= 'User email: ' .$current_user->user_email.chr(13).chr(10);
        if ($current_user->user_firstname != "")
            $message .= 'User first name: ' .$current_user->user_firstname.chr(13).chr(10);
        if ($current_user->user_lastname != "")
            $message .= 'User last name: ' .$current_user->user_lastname.chr(13).chr(10);
        $message .= 'User display name: ' .$current_user->display_name.chr(13).chr(10);
        $message .= 'User ID: ' .$current_user->ID.chr(13).chr(10);
    }
    $message .= 'User IP: ' .MODERATECONTENT__get_the_user_ip().chr(13).chr(10);
    $message .= 'File: ' .$file.chr(13).chr(10);
    $message .= 'Url: ' .$url.chr(13).chr(10);
    $message .= 'Rating: ' .$json->rating_label.chr(13).chr(10);
    $message .= 'Timestamp: ' .date('Y-m-d h:i:s A').chr(13).chr(10);

    MODERATECONTENT__PLUGIN_l('Send Email to: ' . $admin_email);
    $mail_result = wp_mail( $admin_email, $subject, $message );
    MODERATECONTENT__PLUGIN_l('Send Email Result: ' . $mail_result);
}

function MODERATECONTENT__PLUGIN_take_action_remove($fileinfo){
    MODERATECONTENT__PLUGIN_l('MODERATECONTENT__PLUGIN_take_action_remove');
    $upload_dir = wp_upload_dir();
    $adult_file = $upload_dir["basedir"] . "/rating_adult_box.png";
    if (!file_exists($adult_file)){
        copy(MODERATECONTENT__PLUGIN_DIR . "img/rating_adult_box.png", $adult_file);
    }
    $fileinfo["file"] = $adult_file;
    $fileinfo["url"] = $upload_dir["baseurl"] . "/rating_adult_box.png";
    $fileinfo["type"] = "image/png";   
    return $fileinfo;
}

function MODERATECONTENT__PLUGIN_l($msg, $force=false){
    if ($force || get_option('MODERATECONTENT__PLUGIN_debug') == "true"){
        $file = MODERATECONTENT__PLUGIN_DIR . "logs/" . get_option('MODERATECONTENT__PLUGIN_unique_key',"").'_log.txt';
        $msg = date(DATE_ATOM) . ": " .$msg . chr(13).chr(10);
        file_put_contents($file, $msg, FILE_APPEND);
    }
}

function MODERATECONTENT__PLUGIN_create_eval_record($file, $url, $score_json){
    global $wpdb;
    MODERATECONTENT__PLUGIN_l('MODERATECONTENT__PLUGIN_create_eval_record');
    if (MODERATECONTENT__is_duplicate($file, $url)) return "";

    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'moderate_content_requests';
    
    $everyone = strval( $score_json->predictions->everyone );
    $teen = strval( $score_json->predictions->teen );
    $adult = strval( $score_json->predictions->adult );
    
    $file = str_replace("\\","/",$file);

    $current_user = wp_get_current_user();
    $sql_sub = "";
    if ( $current_user->exists() ){
        $sql_sub .= "user_login = '".$current_user->user_login."', ";
        $sql_sub .= "user_email = '".$current_user->user_email."', ";
        $sql_sub .= "user_firstname = '".$current_user->user_firstname."', ";
        $sql_sub .= "user_lastname = '".$current_user->user_lastname."', ";
        $sql_sub .= "user_display_name = '".$current_user->display_name."', ";
        $sql_sub .= "user_id = '".$current_user->ID."', ";
        $sql_sub .= "user_ip = '".MODERATECONTENT__get_the_user_ip()."', ";
    }
    
    $sql = "INSERT INTO $table_name SET "
            . "file = '$file', "
            . "url = '$url', "
            . "rating = '$score_json->rating_label', "
            . $sql_sub
            . "score_everyone = '$everyone', "
            . "score_teen = '$teen', "
            . "score_adult = '$adult', "
            . "status = 'Evaluated' ;";
    
    $wpdb->query( $sql );
}

function MODERATECONTENT__is_duplicate($file, $url) {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'moderate_content_requests';

    $sql = "SELECT `timestamp` FROM " . $table_name . " WHERE `url` = '" .$url. "' AND `timestamp` >= DATE_SUB(NOW(), INTERVAL 4 SECOND);";
    // MODERATECONTENT__PLUGIN_l($sql);
    $result = $wpdb->get_results( $sql );
    foreach ( $result as $row ){
        MODERATECONTENT__PLUGIN_l('MODERATECONTENT__is_duplicate TRUE');
        return true;
    }
    MODERATECONTENT__PLUGIN_l('MODERATECONTENT__is_duplicate FALSE');
    return false;
}

function MODERATECONTENT__get_the_user_ip() {
    if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
    //check ip from share internet
    $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
    //to check ip is pass from proxy
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
    $ip = $_SERVER['REMOTE_ADDR'];
    }
    return apply_filters( 'wpb_get_ip', $ip );
}

// function jen_check_avatar_upload( $upload, $file, $upload_dir_filter ) {

//     // check the $file re type, dims, etc
//     // if you don't like the value of any param, 
//     // return false
//     var_dump($upload, $file, $upload_dir_filter);
//     return true;
 
//  }
//  add_action( 'bp_core_pre_avatar_handle_upload', 'jen_check_avatar_upload', 10, 3 );