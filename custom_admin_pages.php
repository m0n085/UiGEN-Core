<?php
// #################################################################################
// Medica Pro Familia custom Admin pages

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Custom_List extends WP_List_Table {
    
    private $vhat;
    /** ************************************************************************
     * Normally we would be querying data from a database and manipulating that
     * for use in your list table. For this example, we're going to simplify it
     * slightly and create a pre-built array. Think of this as the data that might
     * be returned by $wpdb->query().
     * 
     * @var array 
     **************************************************************************/
   	
   	private $args;

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct($what){
        global $status, $page;
        $this->what = $what;

        $this->args = array (
	   		// 'search'         => 'foo',
	   		// 'search_columns' => array( 'first_name' ),
	   		'fields'         => 'all_with_meta',
	   		'role'			 => $this->what,
	   	);

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'movie',     //singular name of the listed records
            'plural'    => 'movies',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }


    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
        	case 'user_email':
        		return $item->$column_name;
        	case 'address':
        		return '<address>'.$item->$column_name.'</address>';
            default:
            	return get_user_meta($item->ID, $column_name, true);
        }
    }


    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_first_name($item){
        
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="user-edit.php?user_id=%d&wp_http_referer=%s&role=%s">Edit</a>',$item->ID, urlencode($_SERVER["REQUEST_URI"]), $this->what),
            'delete'    => sprintf('<a href="?page=%s&action=delete&user=%d&_wpnonce=%s">Delete</a>', $_REQUEST['page'], $item->ID, wp_create_nonce('delete '.$this->what.'-'.$item->ID)),
        );
        
        //Return the title contents
        return sprintf('%1$s%2$s',
            /*$1%s*/ $item->first_name,
            /*$3%s*/ $this->row_actions($actions)
        );
    }

    function column_company($item){
        
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="user-edit.php?user_id=%d&wp_http_referer=%s&role=%s">Edit</a>',$item->ID, urlencode($_SERVER["REQUEST_URI"]), $this->what),
            'delete'    => sprintf('<a href="?page=%s&action=delete&user=%d&_wpnonce=%s">Delete</a>', $_REQUEST['page'], $item->ID, wp_create_nonce('delete '.$this->what.'-'.$item->ID)),
        );
        
        //Return the title contents
        return sprintf('%1$s%2$s',
            /*$1%s*/ get_user_meta($item->ID, 'company', true),
            /*$3%s*/ $this->row_actions($actions)
        );
    }

    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item->ID                //The value of the checkbox should be the record's id
        );
    }


    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
    	switch ($this->what) {
    		case 'doctor':
    			$columns = array(
    			    'cb'        		=> '<input type="checkbox" />', //Render a checkbox instead of text
    			    'first_name'   		=> 'Imie',
    			    'last_name'  		=> 'Nazwisko',
    			    'specialization'	=> 'Specjalizacja',
    			    'mobile'			=> 'Telefon',
    			    'user_email'		=> 'E-mail',
    			);
    			break;
    		case 'coordinator':
    			$columns = array(
    			    'cb'        		=> '<input type="checkbox" />', //Render a checkbox instead of text
    			    'first_name'		=> 'Imie',
    			    'last_name'			=> 'Nazwisko',
    			    'mobile'			=> 'Telefon',
    			    'user_email'		=> 'E-mail',
    			);
    			break;
			case 'payer':
				$columns = array(
				    'cb'        		=> '<input type="checkbox" />', //Render a checkbox instead of text
				    'company'     	    => 'Nazwa',
				    'user_email'		=> 'E-mail',
				    'address'			=> 'Adres',
				    'nip'				=> 'NIP',
				);
				break;
			case 'worker':
				$columns = array(
				    'cb'        		=> '<input type="checkbox" />', //Render a checkbox instead of text
				    'company'     	    => 'Nazwa',
				    'first_name'   		=> 'Imie',
				    'last_name'  		=> 'Nazwisko',
				    'mobile'			=> 'Telefon',
				    'user_email'		=> 'E-mail',
				    'address'			=> 'Adres',
				    'nip'				=> 'NIP',
				);
				break;
    	}
        return $columns;
    }


    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
		    "company"     	=> array("company", false),
		    "first_name"    	=> array('first_name', false),
		    "last_name" 	 	=> array('last_name', false),
		    "specialization"	=> array('specialization', false),
		    "mobile"			=> array('mobile', false),
		    "user_email"		=> array('', false),
		    // "adress"			=> array('address', false),
		    // "nip"				=> array('nip', false),
        );
        return $sortable_columns;
    }


    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Usuń'
        );
        return $actions;
    }


    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {
        
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            // wp_die('Items deleted (or they would be if we had items to delete)!');
            // if(wp_verify_nonce($_REQUEST['_wpnonce'], 'delete-'.$this->what.'-'.$_REQUEST['user'])) {
                if(is_array($_REQUEST['user'])) {
                    foreach ($_REQUEST['user'] as $us) {
                        wp_delete_user((int)$us);
                    }
                } else {
                    wp_delete_user((int)$_REQUEST['user'] );
                }
            // } else {
            //     wp_die('Problem with nonce...');
            // }
        }
        
    }


    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {
        /**
         * First, lets decide how many records per page to show
         */
        $per_page = '25';
        
        
        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();
                
        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */
        $current_page = $this->get_pagenum();
        

        $this->args['number'] = $per_page;
        $this->args['offset'] = ($current_page-1)*$per_page;

        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         * 
         * In a real-world situation involving a database, you would probably want 
         * to handle sorting by passing the 'orderby' and 'order' values directly 
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        if (!empty($_REQUEST['orderby'])) 
        	$this->args['orderby'] = $_REQUEST['orderby'];
            $this->args['meta_key'] = $_REQUEST['orderby'];
        if (!empty($_REQUEST['order'])) 
        	$this->args['order'] = $_REQUEST['order'];
        $this->args['query_id'] = 'wps_last_name';
        $data = new WP_User_Query($this->args);
        $ndata = $data->get_results();

        // print('<pre>');var_dump($data);print('</pre>');
        
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = $data->get_total();
        
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        // $data = array_slice($ndata,(($current_page-1)*$per_page),$per_page);
        
        
        
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $ndata;
        
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }


}

add_action( 'pre_user_query', 'wps_pre_user_query' ); 
function wps_pre_user_query( &$query ) {
    global $wpdb;
    if ( isset($query->query_vars['query_id'] ) && 'wps_last_name' == $query->query_vars['query_id'] )
        $query->query_orderby = str_replace('user_login', "$wpdb->usermeta.meta_value", $query->query_orderby ); 
}

add_action('admin_menu', 'mpf_menu');
function mpf_menu()
{   
  // editor + administrator = moderate_comments;
  add_menu_page('Lekarze', 'Lekarze', 'manage_options', 'doctors', function() {custom_list('doctor');});
  add_menu_page('Płatnicy', 'Płatnicy', 'manage_options', 'payers', function() {custom_list('payer');});
  add_menu_page('Koordynatorzy', 'Koordynatorzy', 'administrator', 'coordinators',  function() {custom_list('coordinator');});
  add_menu_page('Pracownicy', 'Pracownicy', 'administrator', 'workers',  function() {custom_list('worker');});
  // submenu with calback
 
  add_submenu_page('doctors', 'Dodaj lekarza', 'Dodaj lekarza', 'manage_options', 'user-new.php?role=doctor');
  add_submenu_page('payers', 'Dodaj płatnika', 'Dodaj płatnika', 'manage_options', 'user-new.php?role=payer');
  add_submenu_page('coordinators', 'Dodaj koordynatora', 'Dodaj koordynatora', 'manage_options', 'user-new.php?role=coordinator');
  add_submenu_page('workers', 'Dodaj pracownika', 'Dodaj pracownika', 'manage_options', 'user-new.php?role=worker');

} 

function custom_list($what) {
	switch ($what) {
		case 'worker':
			$page_title = 'Pracownicy';
			break;
		case 'coordinator':
			$page_title = 'Koordynatorzy';
			break;
		case 'payer':
			$page_title = 'Płatnicy';
			break;
		case 'doctor':
			$page_title = 'Lekarze';
			break;
		case 'worker':
			$page_title = "Pracownicy";
			break;
		default:
			$page_title = 'Lista';
	}


	//Create an instance of our package class...
    $list_Table = new Custom_List($what);
    //Fetch, prepare, sort, and filter our data...
    $list_Table->prepare_items();
    
    ?>
    <div class="wrap">
        
        <div id="icon-users" class="icon32"><br/></div>
        <h2><?php print($page_title) ?> <a href="user-new.php?role=<?php print($what); ?>" class="add-new-h2">Dodaj nowego</a></h2>
        
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="users-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $list_Table->search_box('Wyszukaj', 'sid'); ?>
            <?php $list_Table->display() ?>
        </form>
        
    </div>
    <?php
}

add_action('profile_update', 'profile_customizer_update', 10, 1);
add_action ('user_register', "profile_customizer_update", 10, 1);
add_action( "user_new_form_tag", "profile_customizer_add" );
add_action("user_edit_form_tag", "profile_customizer_add");


function profile_customizer_add(){ 
?>
	>
	<input type="hidden" name="role" value="<?php print($_REQUEST['role']); ?>" />
	
    <?php
/*        $metabox = array(
        'args' => array(
            'data_path'=>'http://localhost/medica/wp-content/plugins/UiGEN-Core/global-data/uigen-alpacaform/arguments/',
            'schema_file'=>'kontrakt-schema.json',
            'options' => '{}'
            ) 
        );
        alpacaform_box($post, $metabox);*/
    ?>


	<script type="text/javascript">
		var role = '<?php print($_REQUEST["role"]); ?>';
		if(role != '') {
			jQuery(document).ready(function($) {
				$('#role').val(role);
				$('#role').parent().parent().hide();
				$('#url').parent().parent().hide();
                $('h3').each(function(index, element) {
                    element.remove();
                });
                $('#color-picker').parent().parent().hide();
                $('#comment_shortcuts').parent().parent().parent().hide();
                $('#admin_bar_front').parent().parent().parent().parent().hide();
                $('#nickname').parent().parent().hide();
                $('#description').parent().parent().hide();
                $('#rich_editing').parent().parent().parent().hide();
				$('#user_login').parent().parent().hide();
                $('#display_name').parent().parent().hide();
				var password = '<?php print(wp_generate_password(16)); ?>';
				console.log(password);
				switch (role) {
					case 'doctor':
						$('#add-new-user').html('Dodaj nowego lekarza');
						$('#pass1').parent().parent().remove();
						$('#pass2').parent().parent().remove();
						$('#custom_fields').append('<input type="hidden" name="pass1" id="pass11" /><input type="hidden" name="pass2" id="pass22" />');
						$('#send_password').parent().parent().parent().hide();
						$('#pass11').val(password);
						$('#pass22').val(password);
						$('#custom_fields').append('<tr class="form-field"><th scope="row"><label for="mobile">Telefon</label></th><td><input type="text" name="mobile" value="<?php if(isset($_REQUEST["user_id"])) print(get_user_meta($_REQUEST['user_id'], 'mobile', true)); ?>" />');
						$('#custom_fields').append('<tr class="form-field"><th scope="row"><label for="specialization">Specjalizacja</label></th><td><input type="text" name="specialization" value="<?php if(isset($_REQUEST["user_id"])) print(get_user_meta($_REQUEST['user_id'], 'specialization', true)); ?>" />');
                        $('#custom_fields').append('<tr class="form-field"><th scope="row"><label for="wage_const">Wynagrodzenie stałe</label></th><td><input type="text" name="wage_const" value="<?php if(isset($_REQUEST["user_id"])) print(get_user_meta($_REQUEST['user_id'], 'wage_const', true)); ?>" />');
                        $('#custom_fields').append('<tr class="form-field"><th scope="row"><label for="wage_percent">Wynagrodzenie %</label></th><td><input type="text" name="wage_percent" value="<?php if(isset($_REQUEST["user_id"])) print(get_user_meta($_REQUEST['user_id'], 'wage_percent', true)); ?>" />');
                        $('#custom_fields').append('<tr class="form-field"><th scope="row"><label for="currency">Waluta<a/label></th><td><input type="text" name="currency" value="<?php if(isset($_REQUEST["user_id"])) print(get_user_meta($_REQUEST['user_id'], 'currency', true)); ?>" />');
						break;
					case 'coordinator':
					case 'worker':
						$('#add-new-user').html((role == 'coordinator') ? 'Dodaj nowego koordynatora': 'Dodaj nowego pracownika');
						$('#custom_fields').append('<tr class="form-field"><th scope="row"><label for="mobile">Telefon</label></th><td><input type="text" name="mobile" value="<?php if(isset($_REQUEST["user_id"])) print(get_user_meta($_REQUEST['user_id'], 'mobile', true)); ?>" />');
						break;
					case 'payer':
						$('#add-new-user').html('Dodaj nowego płatnika');
						$('#first_name').parent().parent().hide();
						$('#last_name').parent().parent().hide();
						$('#pass1').parent().parent().remove();
						$('#pass2').parent().parent().remove();
						$('#custom_fields').append('<input type="hidden" name="pass1" id="pass11" /><input type="hidden" name="pass2" id="pass22" />');
						$('#send_password').parent().parent().parent().hide();
						$('#pass11').val(password);
						$('#pass22').val(password);
						$('#custom_fields').append('<tr class="form-field"><th scope="row"><label for="company">Nazwa</label></th><td><input type="text" id="company" name="company" value="<?php if(isset($_REQUEST["user_id"])) print(get_user_meta($_REQUEST["user_id"], "company", true)); ?>" />');
						$('#custom_fields').append('<tr class="form-field"><th scope="row"><label for="nip">Numer NIP</label></th><td><input type="text" id="nip" name="nip" value="<?php if(isset($_REQUEST["user_id"])) print(get_user_meta($_REQUEST["user_id"], "nip", true)); ?>" />');
						$('#custom_fields').append('<tr class="form-field"><th scope="row"><label for="address">Adres</label></th><td><textarea rows=4 cols=30 name="address"><?php if(isset($_REQUEST["user_id"])) print(str_replace("\r\n", "\\n", get_user_meta($_REQUEST["user_id"], "address", true))); ?></textarea>');
						break;
				}
				$('#createuser').submit(function(e) {
					// e.preventDefault();
					$('#user_login').val($('#email').val());
                    if(role == 'payer') {
                        $('#last_name').val($('#company').val());
                    }
					// console.log($('#user_login').val())
				});
			});
		}
	</script>
	<table class="form-table">
		<tbody id="custom_fields">
		</tbody>
	</table
<?php    
}

function profile_customizer_update($user_id){
	switch ($_REQUEST['role']) {
		case 'doctor':
			update_user_meta( $user_id, 'mobile', $_REQUEST['mobile']);
			update_user_meta( $user_id, 'specialization', $_REQUEST['specialization']);
            update_user_meta( $user_id, 'wage_const', $_REQUEST['wage_const']);
            update_user_meta( $user_id, 'wage_percent', $_REQUEST['wage_percent']);
            update_user_meta( $user_id, 'currency', $_REQUEST['currency']);
			break;
		case 'coordinator':
		case 'worker':
			update_user_meta( $user_id, 'mobile', $_REQUEST['mobile']);
			break;
		case 'payer':
			update_user_meta( $user_id, 'address', $_REQUEST['address']);
			update_user_meta( $user_id, 'company', $_REQUEST['company']);
			update_user_meta( $user_id, 'nip', $_REQUEST['nip']);
			break;
	}
	wp_redirect(get_bloginfo('url').'/wp-admin/admin.php?page='.$_REQUEST['role'].'s');
	exit();
}