<?php
/*
Plugin Name: Mo Slider
Plugin URI: https://moduet.com/wordpress-plugins/
Description: A WordPress plugin that displays a responsive image slider using post ids. The slider is responsive; visibly clean display and functionality on any device (mobile or desktop). 
Version: 1.0
Contributors: MoDuet
Author: MoDuet
Author URI: http://moduet.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/

$mo_slider_init = new mo_slider_init();
class mo_slider_init{
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array($this, 'bx_slider_script') );
		add_action( 'wp_enqueue_scripts', array($this, 'register_slider_styles') );
    }
	public function bx_slider_script() {
		wp_register_script( 'bxslider', plugins_url( 'vendor/bxslider/jquery.bxslider.min.js', __FILE__), array('jquery'), '1.0.0', true );	
		wp_enqueue_script('bxslider');
	}
	public function register_slider_styles() {
		wp_register_style( 'mo-bxslider-css', plugins_url( 'vendor/bxslider/jquery.bxslider.css', __FILE__ ));
		wp_enqueue_style( 'mo-bxslider-css' );
		
		wp_register_style( 'mo-slider-css', plugins_url( 'css/mo-slider.css', __FILE__ ), array(), '1.0' );
		wp_enqueue_style( 'mo-slider-css' );
	}
}
	
add_action( 'widgets_init', 'load_mo_slider' );
function load_mo_slider() {
	register_widget( 'mo_slider' );
}

class mo_slider extends WP_Widget {
	public function slider_scripts() {
		foreach( $this->slider_code_footer as $script ){
	        echo $script;
	    }
	}
	public function mo_slider() {
		$widget_ops = array( 'classname' => 'mo_slider', 'description' => __('Displays slider using post ID.', 'mo_slider') );
		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => 'mo_slider' );
		$this->WP_Widget( 'mo_slider', __('Mo Post Slider', 'mo_slider'), $widget_ops, $control_ops );
	}
	public function widget( $args, $instance ) {
		$this->slider_code_class = 'mo_slider_'.rand();
		extract( $args );
		$post_ids = $instance['post_ids'];
		$post_ids_array  = $post_ids;
		$post_ids_array = explode(" ", $post_ids_array);
		echo $before_widget;
		?>
		<div class="mo_slider_container <?php echo $this->slider_code_class; ?>">
			<?php $the_query = new WP_Query(array( 
				'post__in' => $post_ids_array,
				'orderby' => 'post__in'
			));
			while ($the_query -> have_posts()) : $the_query -> the_post(); ?>
			<div style="position: relative;" class="mo-slider-slide">
				<a href="<?php the_permalink()?>" rel="bookmark">
					<div class="mo-slider-image"><img src="<?php the_post_thumbnail_url(); ?>" /></div>
					<div class="mo-slider-content">
						<h1><?php the_title(); ?></h1>
						<?php echo the_excerpt(); ?>
					</div>
				</a>
			</div>
			<?php endwhile; ?>
		</div>
		<?php
		echo $after_widget;		
		$this->slider_code_footer[] = '<script type="text/javascript" language="JavaScript">
		jQuery(document).ready(function($) { 
				$(window).bind(\'load\', function() {
					$(\'.'.$this->slider_code_class.'\').bxSlider({
						slideWidth: 1000,
						mode: "fade",
						auto: true
					});
				});
			});
			</script>';
		add_action( 'wp_footer', array($this, 'slider_scripts') );
	}
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['post_ids'] = strip_tags( $new_instance['post_ids'] );
		return $instance;
	}
	public function form( $instance ) {
		$defaults = array( 'post_ids' => 1);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<!-- Post ID -->
		<p>
			<label for="<?php echo $this->get_field_id( 'post_ids' ); ?>">Enter Post ID:</label>
			<input id="<?php echo $this->get_field_id( 'post_ids' ); ?>" name="<?php echo $this->get_field_name( 'post_ids' ); ?>" value="<?php echo $instance['post_ids']; ?>" class="widefat" /><br /><small>Enter the post ID or IDs for the posts to include in the slider.</small>
		</p>
	<?php }
} ?>