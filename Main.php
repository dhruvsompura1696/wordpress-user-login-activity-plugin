<?php

class Main
{
    public function __construct()
    {
        add_action( 'wp_enqueue_scripts', array($this,'ua_add_scripts') ); 
        add_action( 'admin_enqueue_scripts', array($this,'ua_add_scripts') ); 
        add_filter("script_loader_tag", array($this,"add_module_to_my_script"), 10, 3);  
        add_action('wp_ajax_update_user_login_activity',array($this,'update_user_login_activity')); 
        add_action( 'wp_logout', array($this,'wp_kama_logout_action') );
        add_action('admin_menu', array($this,'my_menu') );

    }


    public function my_menu() {

       
        ?>
            <style>
                #toplevel_page_login-frequancy img
                {
                    height:20px;
                }
            </style>
        <?php
        add_menu_page('Login Frequancy', 'Login Frequancy', 'manage_options', 'login-frequancy', array($this,'login_frequancy_html'),plugin_dir_url( __FILE__ ).'/assets/images/icon.png',100);
        add_submenu_page( 'login-frequancy', 'Frequancy Details', 'Frequancy Details', 'manage_options', 'frequancy-details', array($this,'frequancy_details_html') ); 
        

    }

    public function frequancy_details_html()
    {
        require(UA_PLUGIN_PATH.'/classes/login_details_wp_table.php');
    }

    public function login_frequancy_html()
    {
        require(UA_PLUGIN_PATH.'/classes/login_wp_table.php');
    }

    public function wp_kama_logout_action( $user_id ){
        global $wpdb;
        $table_name = $wpdb->prefix . 'neca_user_login_activity';
        $entries = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $user_id ORDER BY id DESC LIMIT 1",ARRAY_A);
        // neca_dd($entries);

        $wpdb->update(  $table_name, 
                        array( 
                            'time_logout' => date('Y-m-d h:i:s'), 
                            'time_last_seen' => date('Y-m-d h:i:s'),
                            'login_status' => 'logout'
                        ),
                        array('id'=>$entries[0]['id'])
                    );
        // action...
    }

    public function update_user_login_activity()
    {
        if(is_user_logged_in())
        {
            global $wpdb;
            echo "login";
            echo "<pre>";
            print_r($_POST);
            echo "</pre>";
            $uid = get_current_user_id();
            $user = get_user_by( 'id', $uid );
            $session_token = wp_get_session_token();
            $uname = $user->data->user_login;
            $ip_add = $_SERVER['REMOTE_ADDR'];
            $browser_name = $_POST['browser']['name'];
            $browser_version = $_POST['browser']['version'];
            $os = $_POST['os'];
            // neca_dd($user);

            $table_name = $wpdb->prefix . 'neca_user_login_activity';

            $total_entries = $wpdb->get_results("SELECT count(*) as TOTAL FROM $table_name WHERE user_id = $uid AND session_token = '".$session_token."' ",ARRAY_A);
            echo "total_entries==";
            print_r($total_entries);

            if($total_entries[0]['TOTAL'] == 0)
            {
                echo "IF";
                $wpdb->insert($table_name, array(
                    'session_token' => $session_token,
                    'user_id' => $uid,
                    'username' => $uname,
                    'time_login' => date('Y-m-d h:i:s'),
                    'time_last_seen' => date('Y-m-d h:i:s'),
                    'ip_address' => $ip_add,
                    'browser' => $browser_name,
                    'browser_version' => $browser_version,
                    'operating_system' => $os,
                    'login_status' => 'login',
                ));
            }
            else
            {
                echo "ELSEE";
                $entries = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $uid AND session_token = '".$session_token."' ORDER BY id DESC LIMIT 1",ARRAY_A);
                // neca_dd($entries);

                $wpdb->update(  $table_name, 
                                array( 'time_last_seen' => date('Y-m-d h:i:s')),
                                array('id'=>$entries[0]['id'])
                            );

            }
        }
        exit;
    }

    public function ua_add_scripts() {

        wp_enqueue_script('ua_custom_js',UA_PLUGIN_URL . '/assets/js/custom.js',array( 'jquery' ),strtotime(date('d-m-Y h:i:s')));      
        wp_localize_script( 'ua_custom_js', 'ua_admin_ajax', admin_url('admin-ajax.php') );
        $user_array = array(
                            'is_user_logged_in' => is_user_logged_in() ? true : false,                            
                            'session_token' => wp_get_session_token()
                        );
        wp_localize_script( 'ua_custom_js', 'ua_user_details', $user_array );  
    
    }   

    
    function add_module_to_my_script($tag, $handle, $src)
    {
        if ("ua_custom_js" === $handle) {
            $tag = '<script type="module" src="' . esc_url($src) . '"></script>';
        }

        return $tag;
    }
}

?>