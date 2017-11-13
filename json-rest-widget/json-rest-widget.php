<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Plugin Name:   JSON feed via REST Widget
 * Plugin URI:    https://www.rekow.ch
 * Description:   A widget that renders external JSON content as returned by the WordPress REST API.
 * Version:       1.0
 * Author:        Nils Rekow
 * Author URI:    https://www.rekow.ch
 */
class json_rest_widget extends WP_Widget {
	var $title = 'Feed';
	var $url   = 'http://www.rekow.ch/wp-json/wp/v2/posts?order=DESC&per_page=3';
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$widget_ops = array('classname' => 'json_rest_widget', 'description' => __('A widget that renders external JSON content as returned by the WordPress REST API.', 'wp_widget_plugin'));
		$control_ops = array();
		parent::__construct(false, $name = __('JSON feed via REST Widget', 'json_rest_widget'), $widget_ops, $control_ops);
	}
	
	
	/**
	 * The widget itself (e.g. frontend functionality).
	 *
	 * {@inheritDoc}
	 * @see WP_Widget::widget()
	 */
	public function widget($args, $instance) {
		extract( $args );
		
		wp_enqueue_style( 'json-feed-rest');
		
		if (isset($instance['title']) && !empty($instance['title']) && isset($instance['url']) && !empty($instance['url'])) {
			$this->title = strip_tags($instance['title']);
			$this->url = strip_tags($instance['url']);
		}
		
		echo $before_widget;
		if (!empty($this->title)) {
			echo $before_title . $this->title . $after_title;
		}
		echo $this->_getContents();
		echo $after_widget;
	}
		
		
	/**
	 * Widget's update function. Is triggered when clicking "Save" button.
	 *
	 * {@inheritDoc}
	 * @see WP_Widget::update()
	 */
	public function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['url'] = strip_tags($new_instance['url']);
		return $instance;
	}
		
		
	/**
	 * Widget's settings form (e.g. backend function).
	 *
	 * {@inheritDoc}
	 * @see WP_Widget::form()
	 */
	public function form($instance) {
		if (isset($instance['title']) && !empty($instance['title']) && isset($instance['url']) && !empty($instance['url'])) {
			$this->title = strip_tags($instance['title']);
			$this->url = strip_tags($instance['url']);
		}?>
		<p>
			<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title');?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php echo $this->title;?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('url');?>"><?php _e('URL');?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id('url');?>" name="<?php echo $this->get_field_name('url');?>" type="text" value="<?php echo $this->url;?>" />
		</p><?php
	}
	
	
	/**
	 * Reads webpage and returns a list of links.
	 *
	 * @return array
	 */
	private function _getContents() {
		$links = '';
		
		if ($json = file_get_contents($this->url)) {
			$arr = json_decode($json, TRUE);
			foreach ($arr as $entry) {
				if (isset($entry['title']) && isset($entry['link'])) {
					$shorttitle = $entry['title'];
					
					if (strlen($shorttitle) > 50) {
						$shorttitle = substr($shorttitle, 0, 50) . '...';
					}
					
					$links .= '<div class="json-feed-arrow"></div><p class="json-feed-post"><a href="' . $entry['link'] . '" title="' . $entry['title'] . '" target="_blank">' . $shorttitle . '</a></p>';
				}
				
			}
		}
		
		return $links;
	}
}

add_action('wp_enqueue_scripts', create_function('', 'return wp_register_style("json-feed-rest", plugin_dir_url( __FILE__ ) . "/css/json-rest-widget.css");'));
add_action('widgets_init', create_function('', 'return register_widget("json_rest_widget");'));