<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Plugin Name:   JSON feed via REST Widget
 * Plugin URI:    https://www.rekow.ch
 * Description:   A widget that renders external JSON content as returned by the WordPress REST API.
 * Version:       1.2.2
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
	var $maxlength = 75;
	var $title     = 'Feed';
	var $url       = 'http://www.rekow.ch/blog/wp-json/wp/v2/posts?order=desc&per_page=3';
	
	
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
		
		if (isset($instance['url']) && !empty($instance['url'])) {
			$this->url = sanitize_text_field($instance['url']);
			
			if (isset($instance['title']) && !empty($instance['title'])) {
				$this->title = sanitize_text_field($instance['title']);
			}
			
			if (isset($instance['maxlength']) && is_numeric($instance['maxlength'])) {
				$this->maxlength = sanitize_text_field($instance['maxlength']);
			}
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
		$instance['maxlength'] = sanitize_text_field($new_instance['maxlength']);
		
		return $instance;
	}
	
	
	/**
	 * Widget's settings form (e.g. backend function).
	 *
	 * {@inheritDoc}
	 * @see WP_Widget::form()
	 */
	public function form($instance) {
		if (isset($instance['url']) && !empty($instance['url'])) {
			$this->url = sanitize_text_field($instance['url']);
			
			if (isset($instance['title']) && !empty($instance['title'])) {
				$this->title = sanitize_text_field($instance['title']);
			}
			
			if (isset($instance['maxlength']) && is_numeric($instance['maxlength'])) {
				$this->maxlength = sanitize_text_field($instance['maxlength']);
			}
		}?>
		<p>
			<label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title');?></label><br/>
			<input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php echo $this->title;?>" placeholder="<?php _e('Feed');?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('url');?>"><?php _e('URL');?></label><br/>
			<input class="widefat" id="<?php echo $this->get_field_id('url');?>" name="<?php echo $this->get_field_name('url');?>" type="text" value="<?php echo $this->url;?>" placeholder="<?php _e('https://example.com/wp-json/wp/v2/...');?>" required />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('maxlength');?>"><?php _e('Length');?></label><br/>
			<input class="widefat" id="<?php echo $this->get_field_id('maxlength');?>" name="<?php echo $this->get_field_name('maxlength');?>" type="text" value="<?php echo $this->maxlength;?>" placeholder="<?php _e('Maximum length of each entry');?>"/>
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
						$longtitle = $shorttitle = $entry['title'];
						
						if (is_array($longtitle) && isset($longtitle['rendered'])) {
							$longtitle = $shorttitle = $longtitle['rendered'];
						}
						
						if (strlen($longtitle) > $this->maxlength) {
							$shorttitle = mb_substr($longtitle, 0, $this->maxlength, 'utf-8') . '...';
						}
						
						$links .= '<p class="json-feed-post"><a href="' . $entry['link'] . '" title="' . $longtitle . '" target="_blank">' . $shorttitle . '</a></p>';
					}
				}
			}
		}
		
		return $links;
	}
}


/**
 * Init plugin
 */
function enqueue_json_stylesheet() {
	return wp_register_style("json_rest_widget_style", plugin_dir_url( __FILE__ ) . "/css/json-rest-widget.css");
}

function init_json_widget() {
	return register_widget("json_rest_widget");
}

add_action('wp_enqueue_scripts', 'enqueue_json_stylesheet');
add_action('widgets_init', 'init_json_widget');