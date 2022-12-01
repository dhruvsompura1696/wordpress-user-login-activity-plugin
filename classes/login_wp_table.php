<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class My_List_Table extends WP_List_Table {
    public $example_data = array();
    function __construct() {
        parent::__construct();
    }
    

    function no_items() {
        echo 'No login frequancy found';
    }    

    public function get_data()
    {
        
       
        $this->example_data = array(
            array('ID' => 1,'booktitle' => 'Quarter Share', 'author' => 'Nathan Lowell',
                  'isbn' => '978-0982514542'),
            array('ID' => 2, 'booktitle' => '7th Son: Descent','author' => 'J. C. Hutchins',
                  'isbn' => '0312384378'),
            array('ID' => 3, 'booktitle' => 'Shadowmagic', 'author' => 'John Lenahan',
                  'isbn' => '978-1905548927'),
            array('ID' => 4, 'booktitle' => 'The Crown Conspiracy', 'author' => 'Michael J. Sullivan',
                  'isbn' => '978-0979621130'),
            array('ID' => 5, 'booktitle'     => 'Max Quick: The Pocket and the Pendant', 'author'    => 'Mark Jeffrey',
                  'isbn' => '978-0061988929'),
            array('ID' => 6, 'booktitle' => 'Jack Wakes Up: A Novel', 'author' => 'Seth Harwood',
                  'isbn' => '978-0307454355')
        );
        global $wpdb;
        $user_table = $wpdb->prefix.'users';
        $login_table = $wpdb->prefix.'neca_user_login_activity';
        // echo "<pre>";
        // print_r($_GET);
        // echo "</pre>";
        $login_activities = $wpdb->get_results("SELECT  *,count(login.id) as total_login_count FROM $user_table as user LEFT JOIN $login_table as login ON user.ID = login.user_id WHERE user.user_login LIKE '%".$_GET['s']."%' GROUP BY user.ID ORDER BY total_login_count DESC, user.user_login ASC",ARRAY_A);

        return $login_activities;
    }

    function get_columns(){
        $columns = array(
          'user_id'    => 'USER ID',
          'username'      => 'User Name',
          'total_login_count'      => 'Total Logins',
          'today_logins'      => 'Today Logins',
          'online'      => 'Online',
          'action'      => 'Action'
        );
        return $columns;
    }
      
    function prepare_items() {

        $per_page = 10;
        $current_page = $this->get_pagenum();
        $total_items = count($this->get_data());

        $filter_data = array_slice($this->get_data(),(($current_page-1)*$per_page),$per_page);

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        // $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->set_pagination_args( 
            array(
                'total_items' => $total_items,                 
                'per_page'    => $per_page
            ) 
        );

        //$this->items = $this->get_data();
        $this->items = $filter_data;
    }

    function extra_tablenav( $which ) {
        if ( $which == "top" ){
           //The code that goes before the table is here
           $this->search_box('search', 'search_id');
        }
        if ( $which == "bottom" ){
           //The code that goes after the table is there
        //    echo"Hi, I'm after the table";
        }
     }

    function column_default( $item, $column_name ) {
        
        global $wpdb;
        $login_table = $wpdb->prefix.'neca_user_login_activity';
        $uid = $item['ID'];
        $last_login_activity = $wpdb->get_results("SELECT * FROM $login_table WHERE user_id = $uid ORDER BY id DESC LIMIT 1",ARRAY_A);
        $time_last_seen = isset($last_login_activity) && is_array($last_login_activity) && count($last_login_activity) > 0 ? $last_login_activity[0]['time_last_seen'] : '';
        $login_status =  isset($last_login_activity) && is_array($last_login_activity) && count($last_login_activity) > 0 ? $last_login_activity[0]['login_status'] : '';
        $seconds = strtotime(date('Y-m-d h:i:s')) - strtotime($time_last_seen);

        $days    = floor($seconds / 86400);
        $hours   = floor(($seconds - ($days * 86400)) / 3600);
        $minutes = floor(($seconds - ($days * 86400) - ($hours * 3600))/60);
        $seconds = floor(($seconds - ($days * 86400) - ($hours * 3600) - ($minutes*60))); 
        switch( $column_name ) {           
            case 'user_id':
                return '<a target="_blank" href="'.get_edit_user_link($item[ 'ID' ]).'">'.$item[ 'ID' ].'</a>';
            case 'username':
                return $item['username'] != '' ? '<a target="_blank" href="'.get_edit_user_link($item[ 'ID' ]).'">'.$item['username'].'</a>' : '<a target="_blank" href="'.get_edit_user_link($item[ 'ID' ]).'">'.$item['user_login'].'</a>';
            case 'total_login_count':
                return $item[ 'total_login_count' ];
            case 'today_logins':
                    $todat_date = date('Y-m-d');
                    $today_logins = $wpdb->get_results("SELECT COUNT(*) as total FROM $login_table WHERE user_id = $uid AND DATE(time_login) = '".$todat_date."' ",ARRAY_A);
                    // print_r($today_logins);
                    return $today_logins[0][ 'total' ];
            case 'online':
                    // echo "minutes==".$minutes;
                    $login_status = $time_last_seen != '' && $minutes < 3 && $login_status == 'login' ? 'online' : 'offline';
                    return '<div class="neca_login_status">
                                <div class="indicator '.$login_status.'"></div>
                            </div>';
            case 'action':
                return '
                        <a href="" class="">View Details</a>
                ';
          default:
            return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
        }
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'user_id'  => array('ID',false),
            'total_login_count'   => array('total_login_count',false)
        );
        return $sortable_columns;
    }

    public function search_box( $text, $input_id ) { ?>
        <p class="search-box" style="float:left;">
          <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
          <input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
          <?php submit_button( $text, 'button', false, false, array('id' => 'search-submit') ); ?>
      </p>
    <?php }
}

$myListTable = new My_List_Table();
echo '<div class="wrap"><h2>Users Login Frequancy</h2><hr/>';
?>
    <style>
        .neca_login_status {
            width: auto;
            margin: 10px auto;
            text-align: center;
            cursor: default;
            transition: 0.5s ease;
        }
        .neca_login_status .indicator {
            height: 10px;
            width: 10px;
            border-radius: 100%;
            top: 50%;
            transform: translateY(-50%);
        }
        .neca_login_status .indicator.online {
            background-color: #39cb58;
        }
        .neca_login_status .indicator.offline {
            background-color: #f00;
        }
        .neca_login_status .message {
            /*   margin-left: 50px; */
            line-height: 50px;
            color: #fff;
            font-family: 'Source Sans Pro';
            font-size: 20px;
            font-weight: 100;
        }        
    </style>
<?php

echo '<form method="GET" style="clear: both;">';
echo '<input type="hidden" name="page" value="login-frequancy" />';
$myListTable->prepare_items(); 
$myListTable->display(); 
echo '</form>';

echo '</div>'; 
