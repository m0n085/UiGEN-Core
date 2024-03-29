<?php
/* 
USAGE EXAMPLE
'slot1' => array(		
	'type' => 'controller',
	'name' => 'tdc_get_loop', 
	'tpl_part' => 'excerpt-content',
	'custom_tpl_part' => 'metaname',		// get tplpart name from metavalue by metaname
	'tpl_part_queue' => array('name1','name2','name3'), 
	'query_args' => array( 'post_type' => 'post' , 'posts_per_page' => -1)
)
'slot2' => array(	
	'post_id' => 35,
	'type' => 'tpl-part',		
	'tpl_start'=>'<div class="row">',
	'tpl_part' => 'promo',
	'tpl_end'=>'</div>',
),

 */
class ThemeDisplayController {
	
	public $current_slot;
	public $grids_path = '/theme-template-parts/grids';
	public $post_id; // object ID
	public $pstp_reg_id;  // register object ID - into acces properties
	public $tpl_model; // 
	public $args;
	public $slotsHandler;
	public $loopCounter;

	public $postFormObject;
	
	// redirection argument
	public $unredirectID;//
	
	// permisions to callback method from slot=>name parameter - TODO
	private $callback_permisions = array('tdc_get_loop');
	
	// loop user data model
	private $loop_data;

	public $html;
	public $string;

	public $db_row;

	public function __construct($args){
		$this -> args = $args;


		// INIT DEBUGER
		if($_GET['debug']=='true'){
			//require_once ABSPATH . 'wp-content/plugins/UiGEN-Core/core-files/uigen-debuger.php';
			require_once ABSPATH . 'wp-content/plugins/UiGEN-Core/core-files/backbone-test.php';
			//wp_register_script( 'jquery-ui', '//code.jquery.com/ui/1.10.4/jquery-ui.js');
			wp_enqueue_script( 'jquery-ui-sortable' ); 
			wp_enqueue_script( 'jquery-ui-droppable' ); 
		}
		
	}

	// --------------------------------
	// MAIN DISPLAY CONTROLLERS
	// --------------------------------
	public function tdc_get_grid( $grid_name , $args , $slotsHandler = false, $postFormObject = false ){



		if( $_GET['debug'] == 'true' ){
			if($slotsHandler == false){
				echo '<pre class="alert alert-warning" style="margin:20px"><b>SLOT HANDLER INFO</b><br>Slot handler is not defined</pre>'; 
	
			}
			
			/* Debuger */
			//decorate_debuged_page_header( $grid_name, $args );

		}
		
		$this -> postFormObject = $postFormObject;
		// TODO: Remove args field if
		if (!is_array($this->args)) {
		 	$this -> args = $args;
		 	$this -> slotsHandler = $slotsHandler;
		 	
		}
		if($grid_name == 'false'){
			echo '<pre class="alert alert-warning" style="margin:20px"><b>GRID ERROR</b><br><br>Grid name is false</pre>'; die(); 
		}else{
			$grid_filename = TEMPLATEPATH . $this -> grids_path .'/grid-'.$grid_name.'.php';
			if(file_exists($grid_filename )==false){
				echo '<pre class="alert alert-warning" style="margin:20px"><b>GRID ERROR</b><br><br>'.$grid_filename.'<br/><b>I dont find grid file or you dont name it corectly :/ </b></pre>';
			}else{
				require($grid_filename);
			}
		}
		
	}
	// --------------------------------
	public function tdc_slots_handler($handler){			
		if($this -> slotsHandler == NULL){
			echo '<pre class="alert alert-warning">ERROR<br><br>You defined into grid tdc_slots_handler but you dont add slotsHandler array prop</pre>';
		}else{
			if($this -> slotsHandler[$handler] != NULL){
				foreach ($this -> slotsHandler[$handler] as $key=>$value) {				
					$this -> tdc_get_slot($value);
				}
			}
		}
	}


	// --------------------------------
	public function tdc_get_slot($slotName){	

		$this -> current_slot = $slotName;
		@$slot = $this -> args[$slotName];

		if($slot != NULL){	

			if($_GET['debug']=='true'){

				/* Debuger */
				//decorate_slot('start',$slotName,$slot);


			}		
			
			// Add custom code before template code
			if(@$slot['tpl_start'] != '') echo $slot['tpl_start'];
			
			if( $slot['type']  == 'controller' ){
				$functionName =  $slot['name'];
				call_user_func_array(array($this, $functionName), array($slot));
			}
			if( $slot['type'] == 'tpl-part' ){
	
					$this -> tdc_get_content($slot);

			}
			
			// Add custom code after template code
			if(@$slot['tpl_end'] != '')  echo $slot['tpl_end'] ;

			// debuger ----------------------------
			if($_GET['debug']=='true'){
				
				/* Debuger */
				//decorate_slot('end',$slotName,$slot);
				
			}
			// ------------------------------------

		}else{
			//echo '<br>----------------------<br>NIEZIDENTYFIKOWAŁEM NAZWY SLOTA<br>----------------------<br>';
		}
	}
	// ------------------------------------------------
	// TPL PARTS CONTROLLERS
	// ------------------------------------------------
	public function tdc_get_content($slot){
		
	
		global $post;


		// set_specyfic post ID	
		if(@$slot['post_id'] != ''){
			$post = get_post($slot['post_id']); 
		}	

		// tpl_path is empty
		if(@$slot['tpl_path'] == ''){
			$slot['tpl_path'] = 'theme-template-parts/content/content';
		}		
		
		// get tplpart name from metavalue by metaname
		if(@$slot['custom_tpl_part'] != ''){
			$slot['tpl_part'] = get_post_meta($post->ID, $slot['custom_tpl_part'], true);			
		}


		// get tplpart name from queue
		if(@$slot['tpl_part_queue'] != ''){	
			
			if($this -> loopCounter >= sizeof($slot['tpl_part_queue'])){
				$queue_nr =  sizeof(@$slot['tpl_part_queue'])-1;
				$slot['tpl_part'] = $slot['tpl_part_queue'][$queue_nr]; 
			}else{
				$queue_nr = $this -> loopCounter;
				$slot['tpl_part'] = $slot['tpl_part_queue'][$queue_nr]; 
			};
		}

		
		// check: What da fuck is display guardian ????
		$displayGuardian = $this -> tdc_list_owner_rules($slot);

		// -------------------------------------------------------
		// -------------------------------------------------------
		// DISPLAY ELEMENT
		// -------------------------------------------------------
		if($displayGuardian == true){

			/*// debuger ----------------------------
			if($_GET['debug']=='true'){
				decorate_template_parts('start');
			}*/
			

			if(@$slot['string'] != ''){
				$this -> string = $slot['string'];
			}
			
			if(@$slot['html'] != ''){
				echo $this -> html = $slot['html']; 		
			}else{	
				
				get_template_part($slot['tpl_path'] , $slot['tpl_part']);
				
			}

			/*// debuger ----------------------------
			if($_GET['debug']=='true'){
				decorate_template_parts('stop');
			}*/
			
		}
		// -------------------------------------------------------
		// -------------------------------------------------------

	}

	public function tdc_include_content($slot){
		if(@$slot['tpl_path'] == ''){
			$slot['tpl_path'] = 'theme-template-parts/content/content';
		}		
		
		if(@$slot['post_id'] != ''){
			$post = get_post($slot['post_id']); 
		}

		//var_dump($this -> content);
		if($this -> content != ""){
			include(TEMPLATEPATH . '/'. $slot['tpl_path'] . '-' . $slot['tpl_part'] . '.php');
		}else{
			print($this -> content);
		}
	}
	// ------------------------------------------------
	public function tdc_get_loop($slot){	
		global $post;
		$temp_post = $post;
		

		/* EXCERPTIONS */
		
		if($slot['query_args']['author'] == 'current'){
			$slot['query_args']['author'] = get_current_user_id();
		}

		//var_dump($slot['query_args']) ;
		/* QUERY */
		query_posts( $slot['query_args'] );

		$this -> loopCounter = 0;
		if ( have_posts() ) { 
			while ( have_posts() ) : the_post(); 
				//echo '<pre>';
				//var_dump($slot);
				//echo '</pre>';					
				$this -> tdc_get_content($slot);	
				$this -> loopCounter ++;		
			endwhile; 
				//twentytwelve_content_nav( 'nav-below' ); <- ciekawe po co to jest ?
		}else{			
	
			get_template_part( 'theme-template-parts/content/content' , 'empty-query' ); 
		}
		$post = $temp_post;
	}
	// ------------------------------------------------
	public function tdc_get_user_loop($slot){	
		global $wp_query;
		global $author;
		//global $post;
		//global $wp_query;
		//
		$user_query = new WP_User_Query(  $slot['query_args'] );

		// Get the results
		$authors = $user_query->get_results();

		$this -> loopCounter = 0;

		if ( ! empty( $user_query->results ) ) {
	    foreach ($authors as $author)
	    {
				$this -> loop_data = $author;
				$this -> tdc_get_content($slot);	
				$this -> loopCounter ++;		
		}
		}else{
			get_template_part( 'theme-template-parts/content/content' , 'empty-user-query' ); 
		}
	}
	// ------------------------------------------------
	public function tdc_get_search_loop($slot){	
		global $post;
		global $wp_query;
		$thesearch = get_search_query(); 

		$args = array_merge( $wp_query->query, $slot['query_args']);

		query_posts( $args );
		$this -> loopCounter = 0;
		if ( have_posts() ) : 
			while ( have_posts() ) : the_post(); 					
				$this -> tdc_get_content($slot);	
				$this -> loopCounter ++;		
			endwhile; 
				//twentytwelve_content_nav( 'nav-below' ); <- ciekawe po co to jest ?
			else : 				
				get_template_part( 'theme-template-parts/content/content' , 'empty-query' ); 
		endif;
	}
	// ------------------------------------------------
	public function tdc_get_db_loop($slot){


		//$myresults = $wpdb->get_results("SELECT * FROM ".$tableName." WHERE user_id = ".$current_user->ID."".$req_nr);
		//$myresults = $wpdb->get_results("SELECT * FROM ".$tableName." WHERE offer_id IN (".$posts_ids_Array.") ".$worker_id.$req_nr);

		// http://www.mysqlperformanceblog.com/2013/10/22/the-power-of-mysqls-group_concat/

		global $wpdb;	
		$tableName = $wpdb->prefix . $slot['db_query_args']['table'];
     	$myresults = $wpdb->get_results("SELECT DISTINCT set_id, GROUP_CONCAT(DISTINCT product_id ORDER BY product_id) AS child_id_list FROM ".$tableName." group by set_id ");
     	
/*		echo '<pre>';
		var_dump($myresults);
		echo '<pre>';*/



     	foreach ($myresults  as $value) {
     		$this -> db_row = $value;
     		$this -> tdc_get_content($slot);	
     		
		}
	}
	// ------------------------------------------------
	public function tdc_get_dbQuery($slot,$content=true){	

		global $wpdb;		
		if($slot['query_args']['meta_query'][0] != ''){
			$query = "
			SELECT
				posts.*
			FROM
				$wpdb->posts posts
			INNER JOIN
				$wpdb->postmeta meta1 ON posts.ID = meta1.post_ID
			WHERE
				posts.post_type = '".$slot['query_args']['post_type']."' AND
				posts.post_status = 'publish' AND
				meta1.meta_key = '".$slot['query_args']['meta_query'][0]['key']."' AND
				meta1.meta_value = '".$slot['query_args']['meta_query'][0]['value']."' 	
				ORDER BY posts.post_date ASC			  
			";
		}else{
			$query = "
			SELECT
				posts.*
			FROM
				$wpdb->posts posts
				
			WHERE
				posts.post_type = '".$slot['query_args']['post_type']."' AND
				posts.post_status = 'publish' 
				ORDER BY posts.post_date ASC
				";	
				
		}
	
		$posts = $wpdb->get_results( $query , object );
		if($content == false){
			return $posts;
		}
		foreach ( $posts as $db_post ){				
				$slot['post_id'] = $db_post -> ID;
				//$this -> tdc_include_content($slot);				
				$this -> tdc_get_content($slot);
		} 
		
	}
	public function buildForm(){

		

		if($this -> postFormObject != null){
			
			// depreciated - old endpointparser method
			//$edit_id = $this->args[$this -> current_slot]['eid'];
			
			// new edit id
			$edit_id = $_GET['eid'];
			
			// debuger ----------------------------
			if($_GET['debug']=='true'){
				//decorate_slot('start','form-slot',key($this->args[$this -> current_slot]));
			}
			

			$accesGuardian = false;

			// jeśli jest edit id to:
			if($edit_id != ''){
				// SPRAWDZ ADMINA I WŁASCICIELA OBIEKTU
				$accesGuardian = $this -> tdc_list_owner_rules($slot);
			}else{
				$accesGuardian = true;
			}

			if($accesGuardian == true){
				$this -> postFormObject -> addSendForm($edit_id);
			}

			// debuger ----------------------------
			if($_GET['debug']=='true'){
				//decorate_slot('end','form-slot',key($this->args[$this -> current_slot]));
			}
			

		}else{
			
			// depreciated warning - now dont use tdc_get_grid without SPD data
			// debuger ----------------------------
			/*	if($_GET['debug']=='true'){
				echo '<pre><p><b>FORM WARNING</b></p>You try send form without $SPD data <br>set grid as example: $TDC -> tdc_get_grid($args["grid"],$args,$SPD);<br/><div style="font-size:11px; padding-top:10px">Error generated from display-controller.class</div></pre>';
	
			}*/
		}

	}


	// ------------------------------------------------
	public function tdc_parentID_redirect($relatedPostID){
		global $post;
		$this->unredirectID = $post->ID;
		$post = get_post($relatedPostID);
	}	
	public function tdc_unredirect(){
		global $post;		
		$post = get_post($this->unredirectID);
		$this->unredirectID = "";
	}
	private function tdc_list_owner_rules($slot){

		$displayGuardian = true;
		//var_dump($slot['rules']);
		// !!! nie występuje parametr rules we flow i raczej nie powinien występować.

		// SKORZYSTAJ Z UPRAWNIEN OBIEKTU --- NIE ZRÓB TO NA SZTYWNO - OBIEKT EDYTUJE TYLKO WŁAŚCICIEL ALBO ADMIN

		// SPRAWDZ CZY RULES WYSTĘPUJE PODCZAS HASHOWANIA

		if(@$slot['rules'] == 'owners_rules'){
			$displayGuardian = false;

			// --------------------------------
			global $current_user;
			$user_roles = $current_user->roles;
			$user_role = array_shift($user_roles);
			$checkUser = $this->loop_data;
			//echo $user_role;

			// jesli user to admin
			if($user_role == 'administrator'){	
				$displayGuardian = true;
			}

			// jesli obiekt nalezy do zalogowanego usera
			if($current_user->ID == $checkUser->ID){
				$displayGuardian = true;
			}
			// --------------------------------
			
		}
		return $displayGuardian;
	}
}
?>
