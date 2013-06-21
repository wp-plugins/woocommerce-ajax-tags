<?php
/*
Plugin Name: WooCommerce Ajax Tags
Plugin URI: http://wordpress.org/extend/plugins/woocommerce-ajax-tags/
Description: WooCommerce Ajax Tags adds an AJAX tag widget to your WooCommerce shop.
Author: Bart Pluijms
Author URI: http://www.geev.nl/
Version: 0.0.1
*/
class WooCommerceAjaxTagsWidget extends WP_Widget
{
function WooCommerceAjaxTagsWidget()
{
	$widget_ops = array('classname' => 'WooCommerceAjaxTagsWidget', 'description' => __( 'WooCommerce Ajax Tags sorts products based on tags', 'woo-atags' ) );
    $this->WP_Widget('WooCommerceAjaxTagsWidget', __( 'WooCommerce Ajax Tags', 'woo-atags' ), $widget_ops);
}

function form($instance)
{
    $instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
	$display_type = isset( $instance['display_type'] ) ? (bool) $instance['display_type'] : false;
?>
	<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'woo-atags') ?></label>
		<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php if (isset ( $instance['title'])) echo esc_attr( $instance['title'] );?>"  /></p>
	</p>
   	
<?php
}
 
function update($new_instance, $old_instance)
{
    $instance = $old_instance;
	$instance['title'] = strip_tags(stripslashes($new_instance['title']));
    return $instance;
}

function widget($args, $instance)
{	
	extract($args, EXTR_SKIP);
	global $woocommerce;
	
	
	$attribute_taxonomies = $woocommerce->get_attribute_taxonomies();
	if ( $attribute_taxonomies ) {
		foreach ( $attribute_taxonomies as $tax ) {

		   	$attribute = sanitize_title( $tax->attribute_name );
		   	$taxonomy = $woocommerce->attribute_taxonomy_name( $attribute );

			// create an array of product attribute taxonomies
			$_attributes_array[] = $taxonomy;
		}
	}
	
	if ( !is_post_type_archive('product') && !is_tax( array_merge( $_attributes_array, array('product_cat', 'product_tag') ) )) return;
	if( is_tax('product_tag')) return;
	echo $before_widget;
	
	if (!empty($instance['title'])) echo $before_title . $instance['title'] .$after_title;

	
	// Tag list
	$tags=get_terms('product_tag');
	
	$html = '<ul class="woo-ajax-tags taglist">';
	foreach ( $tags as $tag ) {
		$term_id=(int)$tag->term_id;
		$tag_link=get_term_link($term_id, 'product_tag');
		$html.="<li><input type=checkbox id='{$tag->slug}' class=tags name=tag value='{$tag->slug}'> <label for='{$tag->slug}'>{$tag->name}</label></li>";
	}
	$html .= '</ul>';
	echo $html;
    echo $after_widget;
  
}

}

/**
* Contents Wrapper
*
* Helps us know what elements to update with new content
**/
if(!function_exists('add_before_products_div')) { 
add_action('woocommerce_before_shop_loop','add_before_products_div');
add_action('woocommerce_after_shop_loop','add_after_products_div');
function add_before_products_div() {
	echo '<section id="products">';
}
function add_after_products_div() {
	echo '</section>';
}
}

function woo_ajax_tags_scripts() { ?>
	<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery(function() {
			jQuery('.woo-ajax-tags .tags').click(function() {
				var allVals = [];
				jQuery('.woo-ajax-tags .tags:checked').each(function() {
					allVals.push(jQuery(this).val());
				});

	 if(allVals=="") { var pathname = window.location.pathname; } else { pathname = '/product-tag/'+allVals; }
	 var max = 0;
		max = jQuery('#products').outerHeight();
		jQuery('#products').fadeOut("fast", function() {
			jQuery('#products').html('<center style="min-height:'+max+'px;"><p>Loading...<br><?php 	echo '<img src="' . plugins_url( 'img/loading.gif' , __FILE__ ) . '"  alt="'.__('Loading...', 'woo-atags') .'">';?></p></center>');
			jQuery('#products').css({'height':max}).fadeIn("slow", function() {});
		});
		jQuery('#products').load(pathname+'/#products #products');
	jQuery(this).addClass('clicked');
		});
	});
});
</script>
<?php }
add_action('wp_footer','woo_ajax_tags_scripts');

load_plugin_textdomain('woo-atags', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' ); 
add_action( 'widgets_init', create_function('', 'return register_widget("WooCommerceAjaxTagsWidget");') );
?>