<?php 
/*
Plugin Name: BP notification woocommerce
Plugin URI: http://webcaffe.ir
Description: Send notification buddypress for all member when publish woocommerce product .
Version: 1.1
Author: asghar hatampoor
Author URI: http://webcaffe.ir

*/

if ( !defined( 'ABSPATH' ) ) exit;
define('BP_NOT_WOO_PACH', plugin_dir_path(__FILE__));
define('BP_NOT_WOO_URL', plugin_dir_url(__FILE__));

require_once ( BP_NOT_WOO_PACH . 'bp-not-woo-admin.php' );

function bp_woo_load_textdomain() {
    load_plugin_textdomain('bp-woo', false, dirname(plugin_basename(__FILE__)) . "/languages/");
}
add_action('init', 'bp_woo_load_textdomain');


function load_styles_webcaffe() {
    if(!is_user_logged_in())
        return;          
            wp_register_style( 'bp-woo',BP_NOT_WOO_URL.'css/bp-woo.css', array(),'20141113','all' );
            wp_enqueue_style( 'bp-woo' );
        }
add_action( 'wp_print_styles', 'load_styles_webcaffe' );	
add_action( 'admin_print_styles', 'load_styles_webcaffe' );	
	
function load_js_webcaffe() {
    if(!is_user_logged_in())
        return;           
           wp_enqueue_script("bp-woo-js",BP_NOT_WOO_URL."js/bp-woo.js",array("jquery"));            
        }
add_action( 'wp_print_scripts', 'load_js_webcaffe' );		
add_action('admin_enqueue_scripts', 'load_js_webcaffe');

define("BP_PRODUCT_NOTIFIER_SLUG","pro_notification");

function bp_product_setup_globals() {	
	global $bp, $current_blog;
    $bp->bp_product=new stdClass();
    $bp->bp_product->id = 'bp_product';
    $bp->bp_product->slug = BP_PRODUCT_NOTIFIER_SLUG;
    $bp->bp_product->notification_callback = 'product_format_notifications_webcaffe';//show the notification   
    $bp->active_components[$bp->bp_product->id] = $bp->bp_product->id;
			
            do_action( 'bp_product_setup_globals' );
    }
            add_action( 'bp_setup_globals', 'bp_product_setup_globals' );

function product_send_notification_webcaffe($id){
    global $bp, $wpdb ,$bpnotwoo;
    $savedPost = get_post($id);
	$options = get_option( '_not_woo_meta' );
    $meta_desc = $options['woo_grid_class'];
	if ( ! empty( $meta_desc ) ) { 
    if($savedPost->post_status == "publish" && $savedPost->post_type=="product" && !wp_is_post_revision($id) && 'friends' !== $meta_desc){
      
        foreach( $wpdb->get_col( "SELECT ID FROM $wpdb->users" ) as $user_id):
                   bp_core_add_notification($savedPost->ID,  $user_id , $bp->bp_product->id, 'new_product_'.$savedPost->ID, $savedPost->post_author);         
        endforeach;		          
    }
	if(function_exists("friends_get_friend_user_ids") && $savedPost->post_status == "publish" && $savedPost->post_type=="product" && !wp_is_post_revision($id) && 'all' !== $meta_desc ){
        $friends = friends_get_friend_user_ids($savedPost->post_author);
        foreach($friends as $friend):        
                   bp_core_add_notification($savedPost->ID,  $friend , $bp->bp_product->id, 'new_product_'.$savedPost->ID, $savedPost->post_author);         
        endforeach;
	}
		}
}
add_action('save_post','product_send_notification_webcaffe');


function product_format_notifications_webcaffe( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {

    do_action( 'product_format_notifications_webcaffe', $action, $item_id,  $total_items, $format );    
    $createdPost = get_post($item_id);
       // $creator = get_userdata($secondary_item_id); 
		
		$args = array( 'post_type' => 'product',  'posts_per_page' => 1, 'orderby' =>'date','order' => 'DESC' );
        $loop = new WP_Query( $args );
		if (has_post_thumbnail( $item_id ,$loop->post->ID )){
		$img= get_the_post_thumbnail($item_id , apply_filters( 'single_product_small_thumbnail_size', 'shop_thumbnail' )); 
		}else{ 
		$img= '<img  style=" width:45px; height:35px;"  src="'.woocommerce_placeholder_img_src('shop_thumbnail').'"  />'; 
		}
        $text = '<div id="'.$action.'"class="notification-pro">
				<a href="#" class="product-delete-noti" title="remove" onclick="live_product_remove_notification_webcaffe(\''.$action.'\',\''.$item_id.'\', \''.admin_url( 'admin-ajax.php' ).'\'); return false;">x</a>
   		        <span class="product-loader-noti"></span>
		        <div class="product-img-noti"><a title="'.$createdPost->post_title.'"href="'.get_permalink( $item_id ).'"> '.$img.'</a></div>
				<div class="product-noti">
				'.$createdPost->post_title.' '. __("is a new product", "bp-woo").'
                <a class="ab-item" title="'.$createdPost->post_title.'"href="'.get_permalink( $item_id ).'"> '.__("check it out!", "bp-woo").'
                </a> 
				</div>
				</div>';
    
	if($format=='string') {
		return $text; 
	} else {
		return array(			
			'text' => $text,			
		);
	}
	return false;
	
	
}

function product_remove_notification_webcaffe($savedPost){
    global $bp; 
	$savedPost = get_post($id);	
    $user_id=$bp->loggedin_user->id;
    $item_id=$_POST['item_id'];
    $component_name='bp_product';
    $component_action='new_product_'.$savedPost->ID;        
    bp_core_delete_notifications_by_item_id ($user_id,  $item_id, $component_name, $component_action);  
	}
add_action('woocommerce_single_product_summary','product_remove_notification_webcaffe',10,2);

function live_product_remove_notification_webcaffe(){
    global $bp; 	
    $user_id=$bp->loggedin_user->id;
    $item_id=$_POST['item_id'];
    $component_name='bp_product';
    $component_action=$_POST['action_id'];        
    bp_core_delete_notifications_by_item_id ($user_id,  $item_id, $component_name, $component_action); 
    die(); 	
	}
add_action('wp_ajax_live_product_remove_notification_webcaffe','live_product_remove_notification_webcaffe');

?>