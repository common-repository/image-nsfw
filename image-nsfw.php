<?php
/**
 * @package ModerateContent
 */
/*
Plugin Name: Image (NSFW)
Plugin URI: https://www.moderatecontent.com
Description: Stops the upload of NSFW images. Using the FREE api at moderatecontent.com to rate content and block it if it's adult.
Version: 1.0.12
Author: ModerateContent.com
Author URI: https://www.moderatecontent.com
License: GPLv2 or later
Text Domain: moderatecontent
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

global $wpdb;

define( 'MODERATECONTENT__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MODERATECONTENT__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once(MODERATECONTENT__PLUGIN_DIR.'activation.php');
register_activation_hook( __FILE__, 'MODERATECONTENT__PLUGIN_activate' );
register_deactivation_hook( __FILE__, 'MODERATECONTENT__PLUGIN_deactivate' );
register_uninstall_hook( __FILE__, 'MODERATECONTENT__PLUGIN_uninstall' );
// add_action( 'upgrader_process_complete', 'MODERATECONTENT__PLUGIN_updated', 10, 2 );

require_once(MODERATECONTENT__PLUGIN_DIR.'admin.php');
require_once(MODERATECONTENT__PLUGIN_DIR.'evaluate.php');
// require_once(MODERATECONTENT__PLUGIN_DIR.'bbpress.php');

function create_event($filter, $label, $default){
    $event = (object)[];
    $event->filter = $filter;
    $event->hander = $label;
    $event->option = (object)[];
    $event->option->label = 'MODERATECONTENT__PLUGIN_OPTION_' . $filter;
    $event->option->default_value = $default;
    return $event;
}

$GLOBALS['MODERATECONTENT__events'] = [];
$GLOBALS['MODERATECONTENT__events'][] = create_event('wp_handle_upload', 'MODERATECONTENT__PLUGIN_handle_upload', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('wp_insert_post_data', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('bp_core_render_message_content', 'MODERATECONTENT__PLUGIN_the_content', "true"); 
$GLOBALS['MODERATECONTENT__events'][] = create_event('bp_get_activity_action', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('bp_activity_comment_content', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('bp_activity_new_update_content', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('bp_activity_post_comment_content', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('bp_activity_post_update_content', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('bp_activity_post_update_object', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('bp_get_activity_content_body', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('bp_get_activity_feed_item_description', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('bp_get_activity_latest_update_excerpt', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('groups_activity_new_update_content', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('group_forum_topic_title_before_save', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('group_forum_topic_tags_before_save', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('group_forum_topic_text_before_save', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('group_forum_post_text_before_save', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('groups_group_status_before_save', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('groups_group_name_before_save', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('groups_group_description_before_save', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('groups_group_slug_before_save', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('groups_member_user_title_before_save', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('groups_member_invite_sent_before_save', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('groups_member_comments_before_save', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('xprofile_group_name_before_save', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('xprofile_group_description_before_save', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('xprofile_field_name_before_save', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('xprofile_field_description_before_save', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('xprofile_data_value_before_save', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('groups_activity_new_forum_post_content', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('groups_activity_new_forum_topic_content', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('groups_at_message_notification_subject', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('groups_at_message_notification_message', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('groups_update_group_forum', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('messages_notification_new_message_message', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('bp_get_message_notice_subject', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('bp_get_message_notice_text', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('bp_get_messages_subject_value', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('bp_get_messages_content_value', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('messages_message_content_before_save', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('messages_notice_message_before_save', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('messages_notice_subject_before_save', 'MODERATECONTENT__PLUGIN_the_content', "true");
$GLOBALS['MODERATECONTENT__events'][] = create_event('bp_get_the_thread_subject', 'MODERATECONTENT__PLUGIN_the_content', "true");

foreach($GLOBALS['MODERATECONTENT__events'] as $event){
    add_filter($event->filter, $event->hander, 10, 2);
}

// add_filter( 'wp_handle_upload', 'MODERATECONTENT__PLUGIN_handle_upload');
// add_filter( 'wp_insert_post_data', 'MODERATECONTENT__PLUGIN_post_published_notification' , 10, 2);
// add_filter( 'bp_core_render_message_content', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 );  
// add_filter(' bp_get_activity_action', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('bp_activity_comment_content', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('bp_activity_new_update_content', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('bp_activity_post_comment_content', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('bp_activity_post_update_content', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('bp_activity_post_update_object', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('bp_get_activity_content_body', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('bp_get_activity_feed_item_description', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('bp_get_activity_latest_update_excerpt', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('groups_activity_new_update_content', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('group_forum_topic_title_before_save', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('group_forum_topic_tags_before_save', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('group_forum_topic_text_before_save', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('group_forum_post_text_before_save', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('groups_group_status_before_save', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('groups_group_name_before_save', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('groups_group_description_before_save', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('groups_group_slug_before_save', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('groups_member_user_title_before_save', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('groups_member_invite_sent_before_save', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('groups_member_comments_before_save', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('xprofile_group_name_before_save', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('xprofile_group_description_before_save', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('xprofile_field_name_before_save', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('xprofile_field_description_before_save', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('xprofile_data_value_before_save', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('groups_activity_new_forum_post_content', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('groups_activity_new_forum_topic_content', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('groups_at_message_notification_subject', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('groups_at_message_notification_message', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('groups_update_group_forum', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('messages_notification_new_message_message', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter( 'bp_get_message_notice_subject', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter( 'bp_get_message_notice_text', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter( 'bp_get_messages_subject_value', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter( 'bp_get_messages_content_value', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter( 'messages_message_content_before_save', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter( 'messages_notice_message_before_save', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter( 'messages_notice_subject_before_save', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter( 'bp_get_the_thread_subject', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 

// add_filter( 'wp_handle_upload', function($a) { MODERATECONTENT__PLUGIN_l('wp_handle_upload'); });
// add_filter( 'wp_insert_post_data', function($a) { MODERATECONTENT__PLUGIN_l('wp_insert_post_data'); });
// add_filter( 'bp_core_render_message_content', function($a) { MODERATECONTENT__PLUGIN_l('bp_core_render_message_content'); return $a; });  
// add_filter('bp_get_activity_action', function($a) { MODERATECONTENT__PLUGIN_l('bp_get_activity_action'); return $a; });  
// add_filter('bp_activity_comment_content', function($a) { MODERATECONTENT__PLUGIN_l('bp_activity_comment_content'); return $a; });  
// add_filter('bp_activity_new_update_content', function($a) { MODERATECONTENT__PLUGIN_l('bp_activity_new_update_content'); return $a; });  
// add_filter('bp_activity_post_comment_content', function($a) { MODERATECONTENT__PLUGIN_l('bp_activity_post_comment_content'); return $a; });  
// add_filter('bp_activity_post_update_content', function($a) { MODERATECONTENT__PLUGIN_l('bp_activity_post_update_content'); return $a; });  
// add_filter('bp_activity_post_update_object', function($a) { MODERATECONTENT__PLUGIN_l('bp_activity_post_update_object'); return $a; });  
// add_filter('bp_get_activity_content_body', function($a) { MODERATECONTENT__PLUGIN_l('bp_get_activity_content_body'); return $a; });  
// add_filter('bp_get_activity_feed_item_description', function($a) { MODERATECONTENT__PLUGIN_l('bp_get_activity_feed_item_description'); return $a; });  
// add_filter('bp_get_activity_latest_update_excerpt', function($a) { MODERATECONTENT__PLUGIN_l('bp_get_activity_latest_update_excerpt'); return $a; });  
// add_filter('groups_activity_new_update_content', function($a) { MODERATECONTENT__PLUGIN_l('groups_activity_new_update_content'); return $a; });  
// add_filter('group_forum_topic_title_before_save', function($a) { MODERATECONTENT__PLUGIN_l('group_forum_topic_title_before_save'); return $a; });  
// add_filter('group_forum_topic_tags_before_save', function($a) { MODERATECONTENT__PLUGIN_l('group_forum_topic_tags_before_save'); return $a; });  
// add_filter('group_forum_topic_text_before_save', function($a) { MODERATECONTENT__PLUGIN_l('group_forum_topic_text_before_save'); return $a; });  
// add_filter('group_forum_post_text_before_save', function($a) { MODERATECONTENT__PLUGIN_l('group_forum_post_text_before_save'); return $a; });  
// add_filter('groups_group_status_before_save', function($a) { MODERATECONTENT__PLUGIN_l('groups_group_status_before_save'); return $a; });  
// add_filter('groups_group_name_before_save', function($a) { MODERATECONTENT__PLUGIN_l('groups_group_name_before_save'); return $a; });  
// add_filter('groups_group_description_before_save', function($a) { MODERATECONTENT__PLUGIN_l('groups_group_description_before_save'); return $a; });  
// add_filter('groups_group_slug_before_save', function($a) { MODERATECONTENT__PLUGIN_l('groups_group_slug_before_save'); return $a; });  
// add_filter('groups_member_user_title_before_save', function($a) { MODERATECONTENT__PLUGIN_l('groups_member_user_title_before_save'); return $a; });  
// add_filter('groups_member_invite_sent_before_save', function($a) { MODERATECONTENT__PLUGIN_l('groups_member_invite_sent_before_save'); return $a; });  
// add_filter('groups_member_comments_before_save', function($a) { MODERATECONTENT__PLUGIN_l('groups_member_comments_before_save'); return $a; });  
// add_filter('xprofile_group_name_before_save', function($a) { MODERATECONTENT__PLUGIN_l('xprofile_group_name_before_save'); return $a; });  
// add_filter('xprofile_group_description_before_save', function($a) { MODERATECONTENT__PLUGIN_l('xprofile_group_description_before_save'); return $a; });  
// add_filter('xprofile_field_name_before_save', function($a) { MODERATECONTENT__PLUGIN_l('xprofile_field_name_before_save'); return $a; });  
// add_filter('xprofile_field_description_before_save', function($a) { MODERATECONTENT__PLUGIN_l('xprofile_field_description_before_save'); return $a; });  
// add_filter('xprofile_data_value_before_save', function($a) { MODERATECONTENT__PLUGIN_l('xprofile_data_value_before_save'); return $a; });  
// add_filter('groups_activity_new_forum_post_content', function($a) { MODERATECONTENT__PLUGIN_l('groups_activity_new_forum_post_content'); return $a; });  
// add_filter('groups_activity_new_forum_topic_content', function($a) { MODERATECONTENT__PLUGIN_l('groups_activity_new_forum_topic_content'); return $a; });  
// add_filter('groups_at_message_notification_subject', function($a) { MODERATECONTENT__PLUGIN_l('groups_at_message_notification_subject'); return $a; });  
// add_filter('groups_at_message_notification_message', function($a) { MODERATECONTENT__PLUGIN_l('groups_at_message_notification_message'); return $a; });  
// add_filter('groups_update_group_forum', function($a) { MODERATECONTENT__PLUGIN_l('groups_update_group_forum'); return $a; });  
// add_filter('messages_notification_new_message_message', function($a) { MODERATECONTENT__PLUGIN_l('messages_notification_new_message_message'); return $a; });  
// add_filter( 'bp_get_message_notice_subject', function($a) { MODERATECONTENT__PLUGIN_l('bp_get_message_notice_subject'); return $a; });  
// add_filter( 'bp_get_message_notice_text', function($a) { MODERATECONTENT__PLUGIN_l('bp_get_message_notice_text'); return $a; });  
// add_filter( 'bp_get_messages_subject_value', function($a) { MODERATECONTENT__PLUGIN_l('bp_get_messages_subject_value'); return $a; });  
// add_filter( 'bp_get_messages_content_value', function($a) { MODERATECONTENT__PLUGIN_l('bp_get_messages_content_value'); return $a; });  
// add_filter( 'messages_message_content_before_save', function($a) { MODERATECONTENT__PLUGIN_l('messages_message_content_before_save'); return $a; });  
// add_filter( 'messages_notice_message_before_save', function($a) { MODERATECONTENT__PLUGIN_l('messages_notice_message_before_save'); return $a; });  
// add_filter( 'bp_get_the_thread_subject', function($a) { MODERATECONTENT__PLUGIN_l('bp_get_the_thread_subject'); return $a; });   
// add_filter( 'bp_get_activity_feed_item_description', function($a) { MODERATECONTENT__PLUGIN_l('bp_get_activity_feed_item_description'); return $a; });  
// add_filter('bp_create_excerpt', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter( 'bp_activity_content_before_save', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter( 'bp_activity_action_before_save', 'MODERATECONTENT__PLUGIN_the_content', 10, 2 ); 
// add_filter('bp_create_excerpt', function($a) { MODERATECONTENT__PLUGIN_l('bp_create_excerpt'); return $a; });  
// add_filter( 'bp_activity_content_before_save', function($a) { MODERATECONTENT__PLUGIN_l('bp_activity_content_before_save'); return $a; });  
// add_filter( 'bp_activity_action_before_save', function($a) { MODERATECONTENT__PLUGIN_l('bp_activity_action_before_save'); return $a; });  
// add_filter( 'bp_get_message_thread_subject', function($a) { MODERATECONTENT__PLUGIN_l('bp_get_message_thread_subject'); return $a; });  
// add_filter( 'bp_get_message_thread_excerpt', function($a) { MODERATECONTENT__PLUGIN_l('bp_get_message_thread_excerpt'); return $a; });  
// add_filter( 'messages_message_subject_before_save', function($a) { MODERATECONTENT__PLUGIN_l('messages_message_subject_before_save'); return $a; });  
// add_filter( 'bp_get_the_thread_message_content', function($a) { MODERATECONTENT__PLUGIN_l('bp_get_the_thread_message_content'); return $a; });  

wp_enqueue_script( 'admin_3', MODERATECONTENT__PLUGIN_URL . 'js/moderatecontent_plugin_admin.js', array( 'jquery' ), '1.0.1', true );
