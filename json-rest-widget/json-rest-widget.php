<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Plugin Name:   JSON feed via REST Widget
 * Plugin URI:    https://www.rekow.ch
 * Description:   A widget that renders external JSON content as returned by the WordPress REST API.
 * Version:       1.2
 * Author:        Nils Rekow
 * Author URI:    https://www.rekow.ch
 * License:
 * -----------------------------------------------------------------------------
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 3 of the License, or (at your option) any later
 * version. This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA
 * -----------------------------------------------------------------------------
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
		extract($args);
		
		wp_enqueue_style('json_rest_widget_style');
		
		if (isset($instance['title']) && !empty($instance['title']) && isset($instance['url']) && !empty($instance['url'])) {
			$this->title = sanitize_text_field($instance['title']);
			$this->url = sanitize_text_field($instance['url']);
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
		$instance['title'] = sanitize_text_field($new_instance['title']);
		$instance['url'] = sanitize_text_field($new_instance['url']);
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
			$this->title = sanitize_text_field($instance['title']);
			$this->url = sanitize_text_field($instance['url']);
		}?>
		<p>
			<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title');?></label><br/>
			<input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php echo $this->title;?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('url');?>"><?php _e('URL');?></label><br/>
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
			
			if (is_array($arr) && count($arr) > 0) {
				foreach ($arr as $entry) {
					if (isset($entry['title']) && isset($entry['link'])) {
						$shorttitle = $entry['title'];
						
						if (strlen($shorttitle) > 50) {
							$shorttitle = mb_substr($shorttitle, 0, 50, 'utf-8') . '...';
						}
						
						$links .= '<p class="json-feed-post"><a href="' . $entry['link'] . '" title="' . $entry['title'] . '" target="_blank">' . $shorttitle . '</a></p>';
					}
				}
			}
		}
		
		return $links;
	}
}

add_action('wp_enqueue_scripts', create_function('', 'return wp_register_style("json_rest_widget_style", plugin_dir_url( __FILE__ ) . "/css/json-rest-widget.css");'));
add_action('widgets_init', create_function('', 'return register_widget("json_rest_widget");'));