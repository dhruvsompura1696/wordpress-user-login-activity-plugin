<?php
/*
Plugin Name: User Activity
Plugin URI: http://wordpress.org/plugins/hello-dolly/
Description: This plugin will export the posts and users list weekly, monthly and yearly
Author: Dhruv Sompura
Version: 1.0.0
*/

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
define( 'UA_PLUGIN_PATH' , plugin_dir_path(__FILE__) );
define( 'UA_PLUGIN_URL' , plugin_dir_url(__FILE__) );

require(UA_PLUGIN_PATH.'/Main.php');
$main = new Main();

/*
* Creating login activity table
*/
register_activation_hook( __FILE__, 'neca_user_login_activity_table' );
function neca_user_login_activity_table()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    $table_name = $wpdb->prefix . 'neca_user_login_activity';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            session_token varchar(100) NOT NULL,
            user_id int(11) NOT NULL,
            username varchar(200) NOT NULL,
            time_login datetime NOT NULL,
            time_logout datetime NULL,
            time_last_seen datetime NOT NULL,
            ip_address varchar(200) NOT NULL,
            browser varchar(200) NOT NULL,
            browser_version varchar(100) NOT NULL,
            operating_system varchar(100) NOT NULL,
            login_status varchar(50) NOT NULL,
            PRIMARY KEY (`id`)) ENGINE = InnoDB;";
    dbDelta( $sql );
}


function update_user_login_activity()
{
    $uid = get_current_user_id();
    $session_token = wp_get_session_token();
}