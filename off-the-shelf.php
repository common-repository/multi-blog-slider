<?php
/**
  * Plugin Name: Multi Blog Slider
  * Plugin URI: bhargavb.wordpress.com
  * Description: This plugin  modify content slider to pull posts from another Wordpress multi-user blog in the same install based on category. Go to widget section and put widget named "Multi Blog Slider" where ever you want. Also you can make some settings from "MBS Settings" from admin menu.
  * Version: 1.1.1
  * Author: Bhargav
  * Author URI: https://bhargavb.wordpress.com/
 */

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
include_once( ABSPATH . 'wp-includes/pluggable.php' );

/*  Plugin Folder Path */
define( 'OTS_DIR', plugin_dir_path( __FILE__ ) );
define( 'OTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/* include widget file */
include_once( OTS_DIR . 'ots_widget.php' );

/* add slider image size */
add_image_size( 'ots_slider_image',800,500,true );
$admin_menu_access_level = apply_filters( 'templ_admin_menu_access_level_filter',8 );
define( 'TEMPL_ACCESS_USER',$admin_menu_access_level );

/* Translate the string of plugins */
function ots_localization() {
	$locale = get_locale();
	load_textdomain( 'mbs' , plugin_dir_path( __FILE__ ) . 'languages/' . $locale . '.mo' );
}
add_action( 'init','ots_localization' );

function ots_admin_menu() {
	do_action( 'ots_admin_menu' );
}
add_action( 'admin_menu', 'ots_admin_menu' ); //Add new menu block to admin side


/* Add admin menu for MBS settings */
function ots_add_admin_menu() {
	$menu_title = __( 'MBS settings','mbs' );
	if ( function_exists( 'add_object_page' ) ) {
		add_object_page( 'Admin Menu',  $menu_title, 8, 'ots_admin_menu', 'ots_settings' ); // title of new sidebar
	} else {
		add_menu_page( 'Admin Menu',  $menu_title, 8, 'ots_admin_menu', 'ots_settings' ); // title of new sidebar
	}
}
add_action( 'ots_admin_menu', 'ots_add_admin_menu' );

/* MBS Settings page */
function ots_settings() {

	echo '<div id="icon-options-general" class="icon32 clearfix"><br></div>';
	echo "<h1 class=''>".__( 'OTS Settings', 'mbs' )."</h1>";

	echo 'Setting goes here..........';
	echo '<h2>'.__( 'If you want more flexible slider, then contact me. I will provide you that in your budget. ', 'mbs' ).'</h2>';
	echo __( 'You can contact me on my email. And my ID is ', 'mbs' ).'<a href="mailto:bn.bhandari90@gmail.com">bn.bhandari90@gmail.com</a>';
	echo '<h2>'.__( 'Put "Slider Request" as your subject so I can understand and contact you back.', 'mbs' ).'</h2>';

}

/* Script for slider */
function add_ots_style_and_scripts() {
	wp_enqueue_script( 'slider-script',OTS_PLUGIN_URL.'js/jquery.flexslider-min.js' );
	wp_enqueue_style( 'slider-style',OTS_PLUGIN_URL.'slider.css' );
}
add_action( 'wp_footer', 'add_ots_style_and_scripts' );

add_action( 'wp_ajax_current_blog_category', 'get_current_blog_categories' );
add_action( 'wp_ajax_nopriv_current_blog_category', 'get_current_blog_categories' );

function get_current_blog_categories() {
	$bid = ( isset( $_REQUEST['blog_id'] ) && $_REQUEST['blog_id'] != '' ) ? $_REQUEST['blog_id'] : 1 ;
	$original_blog_id = get_current_blog_id(); // get current blog
	switch_to_blog( $bid );

	$cats = get_categories();

	foreach( $cats as $cat ) {
		?>
			<option value="<?php echo $cat->term_id; ?>"><?php echo $cat->name; ?></option>
		<?php
	}

	switch_to_blog( $original_blog_id ); //switched back to current blog.

	wp_die(); // this is required to terminate immediately and return a proper response
}
