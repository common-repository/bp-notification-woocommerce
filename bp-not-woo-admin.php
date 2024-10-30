<?php 
class Bp_Not_Meta_Boxes {
          public function __construct() {
          $this->setup_hooks();    
    }
          private function setup_hooks() {		
		  add_action( 'add_meta_boxes', array( $this, 'admin_ui_edit_not' ) );		
		  add_action( 'save_post',  array( $this, 'admin_ui_save_not'), 10, 1 );				
	}
          public function admin_ui_edit_not( $post_type ) {		
			add_meta_box( 
			'webcaffe_not_woo_bp', 
			__( 'Notification For User', 'bp-woo' ), 
			array( &$this, 'admin_ui_metabox_not_woo'), 
			get_current_screen()->id, 
			'side', 
			'core' 
		    );
		   
    }
        public function admin_ui_metabox_not_woo($post) {
		$options = get_option('_not_woo_meta')
        ?>   
        <label><?php _e("Choose send notification for:", "bp-woo");?>  </label>    
        <select name="woo_grid_class" id="woo_grid_class" >
          <option value=""><?php _e("select", "bp-woo");?></option>
          <option value="all" <?php selected( $options, 'all' ); ?>><?php _e("All user", "bp-woo");?></option>
          <option value="friends" <?php selected( $options, 'friends' ); ?>><?php _e("My friends", "bp-woo");?></option>      
         </select>
        <?php
	}
	
function admin_ui_save_not() {
	global $bpnotwoo;
    if(isset($_POST["woo_grid_class"])){
        //UPDATE:       
		$options['woo_grid_class']   = isset($_POST['woo_grid_class']) ? $_POST['woo_grid_class'] : '';
		update_option('_not_woo_meta', $options);
    }
  }

}
 global $bpnotwoo;
    $bpnotwoo = new Bp_Not_Meta_Boxes();	
?>