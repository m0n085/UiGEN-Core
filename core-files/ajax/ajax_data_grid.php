<?php
require_once("../../../../../wp-load.php");

function ui_save_data_grid($obj){
	if ( current_user_can( 'manage_options' ) ) {
    //var_dump($obj['yaml']);
    file_put_contents( ABSPATH . 'wp-content/plugins/UiGEN-Core/global-data/uigen-data-grids/'.$obj['filename'] , Spyc::YAMLDump( $obj['yaml'] ));


  }else{
    echo '<span>Error:ui_save_data_grid is admin method. Login as admin</span>';
  }
}










$cb = $_POST['callback'];

if( 

  ($cb == 'ui_save_data_grid') 
 

){

  $obj = array($_POST['args']);
  call_user_func_array($cb,$obj);

}else{

  echo '<h1> U`3 lick`y ??? </h1>';

}
?>