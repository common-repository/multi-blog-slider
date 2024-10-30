<?php
/* create widget */

/**
 * Text widget class
 *
 * @since 2.8.0
 */
 
add_action('widgets_init','ots_plugin_reg_widgets');
function ots_plugin_reg_widgets()
{	
	register_widget('WP_Widget_OTSSlider');
} 

/**
 * Core class used to implement a Multi Blog Slider widget.
 *
 * @since 2.8.0
 *
 * @see WP_Widget
 */
 
class WP_Widget_OTSSlider extends WP_Widget {

	/**
	 * Sets up a new Multi Blog Slider widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 */

	public function __construct() {
		$widget_ops = array('classname' => 'widget_ots_slider', 'description' => __('Modify content slider to pull posts from another Wordpress multi-user blog in the same install based on category.','mbs'));
		$control_ops = array('width' => 400, 'height' => 350);
		parent::__construct('ots_slider', __('Multi Blog Slider','mbs'), $widget_ops, $control_ops);
	}
	
	/**
	 * Outputs the content for the current Multi Blog Slider widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Multi Blog Slider widget instance.
	 */

	public function widget( $args, $instance ) {

		/**
		 * Filter the widget title.
		 *
		 * @since 2.6.0
		 *
		 * @param string $title    The widget title. Default 'Slider'.
		 * @param array  $instance An array of the widget's settings.
		 * @param mixed  $id_base  The widget ID.
		 */
		 
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? 'Slider' : $instance['title'], $instance, $this->id_base );
		$blog_id = apply_filters( 'widget_blog_id', empty( $instance['blog_id'] ) ? '1' : $instance['blog_id'], $instance );
		$cat_id =  apply_filters( 'widget_cat_id', empty( $instance['cat_id'] ) ? array() : $instance['cat_id'], $instance );
		$animation_type =  apply_filters( 'widget_animation_type', empty( $instance['animation_type'] ) ? 'slide' : $instance['animation_type'], $instance );
		//$controlNav =  apply_filters( 'widget_controlNav', empty( $instance['controlNav'] ) ? 0 : $instance['controlNav'], $instance );
		$cat_id = implode(',',$cat_id);
		$original_blog_id = get_current_blog_id(); // get current blog
		$bids = array($blog_id); // all the blog_id's to loop through  EDIT
		$cats = array($blog_id => $cat_id); // setup a category for each blog EDIT
		
		echo $args['before_widget'];
		
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		$slider_class = 'flexslider_'.rand(100,999);
		
		?>
		<div class="<?php echo $slider_class; ?>">
			<ul class="slides">
				<?php
				
					/**
					 * Filter the arguments for the Multi Blog Slider widget.
					 *
					 * @since 2.8.0
					 *
					 * @see get_posts()
					 *
					 * @param array $args An array of arguments to retrieve the posts list.
					 */
				
					foreach($bids as $bid):
						switch_to_blog($bid); //switched to blog with blog_id $bid
						
						$posts = get_posts('&cat='.$cats[$bid].'&posts_per_page=10');
												
						if(!empty($posts)){

							foreach($posts as $post){
							
								if(has_post_thumbnail($post->ID)){
									echo "<li>";
										echo get_the_post_thumbnail($post->ID, 'ots_slider_image');
										echo '<p class="flex-caption"><a hrerf="'.get_permalink($post->ID).'">'.get_the_title($post->ID).'</a></p>';
									echo "</li>";
								} else {
									echo "<li>";
										echo '<img src="'.OTS_PLUGIN_URL.'/images/default.png" height="500" width="800"/>';
										echo '<p class="flex-caption"><a hrerf="'.get_permalink($post->ID).'">'.get_the_title($post->ID).'</a></p>';
									echo "</li>";
								}

							}
						
						} else {
							_e('No posts found','mbs');
						}

					endforeach ;
				?>
		
			</ul>
		</div>
		<script type="text/javascript">
			jQuery(window).load(function() {
				jQuery('.<?php echo $slider_class; ?>').flexslider({
					animation: "<?php echo $animation_type; ?>",
				});
			});
		</script>
		<?php 
		
		switch_to_blog( $original_blog_id ); //switched back to current blog 
		
		echo $args['after_widget'];
	}
	
	/**
	 * Handles updating settings for the current Multi Blog Slider widget instance.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings to save.
	 */
	 
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		if ( $new_instance['blog_id'] ) {
			$instance['blog_id'] = $new_instance['blog_id'];
		} else {
			$instance['blog_id'] = '1';
		}
		
		$instance['cat_id'] =  $new_instance['cat_id'] ;
		//$instance['animation_type'] =  sanitize_text_field($new_instance['animation_type']) ;
		
		return $instance;
	}
	
	/**
	 * Outputs the settings form for the Multi Blog Slider widget.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array $instance Current settings.
	 */
	 
	public function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'blog_id' => '', 'cat_id' => array( '' ), 'animation_type' => '', 'controlNav' => '' ) );
		$blogs = wp_get_sites();
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','mbs'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('blog_id'); ?>"><?php _e('Blog Id:','mbs'); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('blog_id'); ?>" name="<?php echo $this->get_field_name('blog_id'); ?>">
				<?php
					$selected = '';
					foreach( $blogs as $blog ) { ?>
							<option <?php selected( $instance['blog_id'], $blog['blog_id'] ); ?> value="<?php echo $blog['blog_id']?>"><?php echo $blog['path']; ?></option>	
						<?php
					}
				?>
			</select>
		</p>
		<script>
			jQuery(document).ready(function(){
				jQuery('#<?php echo $this->get_field_id('blog_id'); ?>').live('change',function(){
					console.log(jQuery(this).val());
					var blog_id = jQuery(this).val();
					var data = {
						'action': 'current_blog_category',
						'blog_id': blog_id
					};

					// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
					jQuery.post(ajaxurl, data, function(response) {
						jQuery('#<?php echo $this->get_field_id('cat_id'); ?>').html(response);
					});
				});
			});
		</script>
		<p>
			<label for="<?php echo $this->get_field_id('cat_id'); ?>"><?php _e('Category','mbs'); ?></label>
			<select multiple class="widefat" id="<?php echo $this->get_field_id('cat_id'); ?>" name="<?php echo $this->get_field_name('cat_id'); ?>[]">
				<?php
					$bid = (isset( $instance['blog_id'] ) && $instance['blog_id'] != '' ) ? $instance['blog_id'] : 1 ;
					$original_blog_id = get_current_blog_id(); // get current blog
					switch_to_blog($bid);

					$cats = get_categories();
					
					foreach( $cats as $cat ) { ?>
							<option <?php if( is_array($instance['cat_id']) && in_array( $cat->term_id , $instance['cat_id'] ) ){ echo 'selected'; } ?> value="<?php echo $cat->term_id; ?>"><?php echo $cat->name; ?></option>	
						<?php
					}
					
					switch_to_blog( $original_blog_id ); //switched back to current blog
				?>
			</select>
		</p>
		<h2><?php echo __( 'Slider Settings:' , 'mbs' );?></h2>
		<p>
			<label for="<?php echo $this->get_field_id('animation_type'); ?>"><?php _e('Animation Type:','mbs'); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('animation_type'); ?>" name="<?php echo $this->get_field_name('animation_type'); ?>">
				<option value="fade" <?php selected( $instance['animation_type'], 'fade' ); ?>><?php echo __('Fade', 'mbs'); ?></option>
				<option value="slide" <?php selected( $instance['animation_type'], 'slide' ); ?>><?php echo __('Slide', 'mbs'); ?></option>
			</select>
		</p>
		<!--<p>
			<label for="<?php echo $this->get_field_id('controlNav'); ?>"><?php _e('Control Navigation:','mbs'); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('controlNav'); ?>" name="<?php echo $this->get_field_name('controlNav'); ?>">
				<option value="1" <?php selected( $instance['controlNav'], 1 ); ?>><?php echo __('Yes', 'mbs'); ?></option>
				<option value="0" <?php selected( $instance['controlNav'], 0 ); ?>><?php echo __('No', 'mbs'); ?></option>
			</select>
		</p>-->
		<?php
	}
}
/* EOF */
?>