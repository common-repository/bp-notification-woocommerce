function live_product_remove_notification_webcaffe(action_id, item_id, adminUrl){
    jQuery('#'+action_id).children(".product-delete-noti").html("");
    jQuery('#'+action_id ).children(".product-loader-noti").show(); 

    jQuery.ajax({
        type: 'post',
        url: adminUrl,
        data: { action: "live_product_remove_notification_webcaffe", action_id:action_id, item_id:item_id },
        success:
        function(data) {
        	jQuery('#'+action_id).parent().hide();
        	jQuery('#ab-pending-notifications').html(jQuery('#ab-pending-notifications').html() - 1);
        }
     });  
}