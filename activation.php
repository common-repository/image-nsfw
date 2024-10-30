<?php
function MODERATECONTENT__PLUGIN_activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'moderate_content_requests';

    $sql = "CREATE TABLE `$table_name` ( "
            . "`id` INT NOT NULL AUTO_INCREMENT , "
            . "`file` BLOB , "
            . "`url` BLOB , "
            . "`status` VARCHAR(32) , "
            . "`rating` VARCHAR(32) , "
            . "`user_login` VARCHAR(32) , "
            . "`user_email` VARCHAR(32) , "
            . "`user_display_name` VARCHAR(32) , "
            . "`user_firstname` VARCHAR(32) , "
            . "`user_lastname` VARCHAR(32) , "
            . "`user_id` VARCHAR(32) , "
            . "`user_ip` VARCHAR(32) , "
            . "`score_everyone` DOUBLE , "
            . "`score_teen` DOUBLE , "
            . "`score_adult` DOUBLE , "
            . "`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , "
            . "UNIQUE KEY id (id)) $charset_collate;";
            echo $sql;

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    add_option( 'MODERATECONTENT__PLUGIN_debug', "false", '', 'yes' );
}

function MODERATECONTENT__PLUGIN_uninstall(){
    delete_option( 'MODERATECONTENT__PLUGIN_unique_key' );
    delete_option( 'MODERATECONTENT__PLUGIN_debug' );
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'moderate_content_requests';
    $sql = "DROP TABLE IF EXISTS $table_name;";

    $wpdb->query( $sql );
}