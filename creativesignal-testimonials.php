<?php
/**
Plugin Name: CreativeSignal Testimonials
Plugin URI: http://creative-signal.com/wordpress/wordpress-plugins/creativesignal-testimonials
Description: Display testimonials on your website in various ways and effects. Add a testimonial widget to your website. Display a testimonial anywhere by tag code. Categorize testimonials and display different categories on different places
Author: CreativeSignal Team
Version: 1.0.1
Author URI: http://creative-signal.com/
Email: creativesignalteam@gmail.com
@copyright  Copyright (C) 2012 creative-signal.com. All Rights Reserved.
@license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

global $wpdb, $wp_version, $creativeSignalTestimonialInstance;
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'creativesignal-defines.php';
include_once WP_CS_TESTIMONIALS_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'libraries' .DIRECTORY_SEPARATOR . 'mine' . DIRECTORY_SEPARATOR . 'utils.php';
class CreativeSignalTestimonial
{
	/**
	 *
	 * Contructor of class, PHP 4 compatible contruction for backward capability
	 */
	function CreativeSignalTestimonial()
	{
		add_action('admin_menu', array($this, 'CreativeSignalTestimonial_Add_To_Admin_Menu'));
		add_action('admin_enqueue_scripts', array($this, 'CreativeSignalTestimonial_Admin_Media'));

		register_activation_hook(__FILE__, array($this, 'CreativeSignalTestimonial_Activate'));
		register_deactivation_hook(__FILE__, array($this, 'CreativeSignalTestimonial_Deactivate'));
		add_action('wp_enqueue_scripts', array($this, 'CreativeSignalTestimonial_Media'));
	}
	/******************************************* SHORTCODE BEGIN *****************************/

	/**
	 *
	 * Shortcode
	 * @param array $atts a set of attributes of array
	 */
	function CreativeSignalTestimonial_ShortCode($atts)
	{
		$html = '';
		$objUtils			= new CreativeSignalTestimonialUtils();
		$objCSTestimonial 	= new CreativeSignalTestimonial;
		extract(shortcode_atts(array(
		      'text_length' => '9999',
		      'category' => ''), $atts));

		$items 	= $objCSTestimonial->CreativeSignalTestimonial_getTestimonialsByCategoryID($category);
		$random = $objUtils->randomString();
		$id		= 'creativesignal-testimonial-shortcode-' . $random;
		if (count($items))
		{
			$html .= '<script type="text/javascript">';
			$html .= '(function($){';
			$html .= '$(document).ready(function ($) {';
			$html .= '$("#' . $id . '").cycle({
										fx: "fade",
				    					speed:  500 });';
			$html .= '});';
			$html .= '})(jQuery);';
			$html .= '</script>';
			$html .= '<div class="creativesignal-testimonial-fade" id="' . $id . '">';
			foreach ($items as $item)
			{
				if ($item->image == '')
				{
					$item->image = WP_CREATIVE_SIGNAL_TESTIMONIAL_MAINURL . '/wp-content/plugins/creativesignal-testimonial/assets/images/no-photo.jpg';
				}
				$html .= '<div class="creativesignal-testimonial-box">';
				$html .= '<p class="title">' . $item->title . '</p>';
				$html .= '<p class="image"><img width="60" height="60" src="' . $item->image . '" /></p>';
				$html .= '<p class="content">' . $objUtils->wordLimiter($item->desc, $text_length). '</p>';
				$html .= '<p class="author_name">' . $item->author_name;
				if ($item->uri != '')
				{
					$html .= '<br /><a href="http://' . $item->uri . '" target="_blank">' . $item->uri . '</a></p>';
				}
				$html .= '</div>';
			}
			$html .= '</div>';
			return $html;
		}
		else
		{
			$html .= '<div id="a-test"></div><div class="creativesignal-testimonial-alert-box">';
			$html .= '<div class="header">';
			$html .= __('CreativeSignal Testimonials');
			$html .= '</div>';
			$html .= '<div class="body">';
			$html .= __('Nothing found.');
			$html .= '</div>';
			$html .= '<div class="footer">';
			$html .= '</div>';
			$html .= '</div>';
			return $html;
		}

	}
	/******************************************* SHORTCODE END *****************************/

	/******************************************* INSTALL SECTION BEGIN *****************************/

	/**
	 *
	 * Install the necessary tables for running the plugin properly
	 */
	function CreativeSignalTestimonial_Activate()
	{
		global $wpdb;
		if ($wpdb->get_var("SHOW TABLES LIKE '". WP_CREATIVE_SIGNAL_TESTIMONIAL_TABLE . "'") != WP_CREATIVE_SIGNAL_TESTIMONIAL_TABLE)
		{
			$query = "CREATE TABLE IF NOT EXISTS `" . WP_CREATIVE_SIGNAL_TESTIMONIAL_TABLE . "` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `image` text not null,
					  `uri` text not null,
					  `target` varchar(50) not null default '',
					  `title` varchar(250) not null default '',
					  `author_name` varchar(250) not null default '',
					  `desc` text not null,
					  `ordering` int(11) not null default 0,
					  `visible` varchar(50) not null default '',
					  `category_alias` varchar(250) not null default '',
					  `category_name` varchar(250) not null default '',
					  `date` varchar(250) not null default '',
					  PRIMARY KEY (`id`)
					) DEFAULT CHARSET=utf8;";

			$wpdb->query($query);
		}

	}

	/**
	 *
	 * Uninstall all tables
	 */
	function CreativeSignalTestimonial_Deactivate()
	{
		delete_option('widget_creativesignal_testimonial_widget');
	}
	/******************************************* INSTALL SECTION END *****************************/

	/******************************************* ADMIN SECTION BEGIN *****************************/

	/**
	 * Add testimonial menu item to admin menu.
	 */
	function CreativeSignalTestimonial_Add_To_Admin_Menu()
	{
		add_menu_page('CS Testimonials', 'CS Testimonials', 'manage_options', 'creativesignal-testimonial/creativesignal-testimonials.php', array($this, 'CreativeSignalTestimonial_Admin_Options'), WP_CREATIVE_SIGNAL_TESTIMONIAL_MAINURL . '/wp-content/plugins/creativesignal-testimonial/assets/images/icons/16x16/testimonials.png', '99');
	}

	/**
	 * Add the necessary files CSS & JavaScript to page header
	 */
	function CreativeSignalTestimonial_Admin_Media()
	{
	 	wp_register_style('cs-testimonials-css-admin', WP_CREATIVE_SIGNAL_TESTIMONIAL_MAINURL . '/wp-content/plugins/creativesignal-testimonial/assets/css/creativesignal-testimonial-admin.css');
	 	wp_register_script('cs-testimonials-js-admin', WP_CREATIVE_SIGNAL_TESTIMONIAL_MAINURL . '/wp-content/plugins/creativesignal-testimonial/assets/js/creativesignal-testimonial-admin.js');
	 	wp_register_script('cs-testimonials-js-datatables', WP_CREATIVE_SIGNAL_TESTIMONIAL_MAINURL . '/wp-content/plugins/creativesignal-testimonial/assets/js/jquery.dataTables.min.js');

		wp_enqueue_style('cs-testimonials-css-admin');
		wp_enqueue_script('cs-testimonials-js-admin');
		wp_enqueue_script('cs-testimonials-js-datatables');
	}

	/**
	 * Add the necessary files CSS & JavaScript to page header
	 */
	function CreativeSignalTestimonial_Media()
	{
	 	wp_enqueue_script("jquery");
		wp_register_script('ws-testimonials-js-zyclelite', WP_CREATIVE_SIGNAL_TESTIMONIAL_MAINURL . '/wp-content/plugins/creativesignal-testimonial/assets/js/jquery.cycle.lite.js');
		wp_enqueue_script('ws-testimonials-js-zyclelite');

		wp_register_style('cs-testimonials-css', WP_CREATIVE_SIGNAL_TESTIMONIAL_MAINURL . '/wp-content/plugins/creativesignal-testimonial/assets/css/creativesignal-testimonial.css');
		wp_enqueue_style('cs-testimonials-css');
	}

	/**
	 * CallBack function of CreativeSignal Testimonials menu
	 * Render form to create a new testimonial
	 */
	function CreativeSignalTestimonial_Admin_Options()
	{
		$id		= @$_GET["cid"];
		$post 	= @$_POST;
		$list	= '';
		$html	= '';
		$script = '';

		if (count($post))
		{
			if (!$id && $post['task'] == 'add')
			{
				$this->CreativeSignalTestimonial_InsertTestimonial($post);
			}
			elseif ($post["cid"] && $post['task'] == 'edit')
			{
				$this->CreativeSignalTestimonial_UpdateTestimonial($post);
			}
			else
			{
				$this->CreativeSignalTestimonial_DeleteTestimonial($post);
			}
		}

		if (!$id || is_null($id))
		{
			$items = $this->CreativeSignalTestimonial_getTestimonials();
			$list .= '<legend>' . __('Testimonial List') . '</legend>';
			$list .= '<table class="wp-list-table widefat fixed pages" id="table-creativesignal-testimonial-list">';
			$list .= '<thead>';
			$list .= '<tr>';
			$list .= '<th width="5%">' . __('#');
			$list .= '</th>';
			$list .= '<th width="15%">' . __('Category');
			$list .= '</th>';
			$list .= '<th width="20%">' . __('Title');
			$list .= '</th>';
			$list .= '<th width="40%">' . __('Shortcode');
			$list .= '</th>';
			$list .= '<th width="5%">' . __('Order');
			$list .= '</th>';
			$list .= '<th width="5%">' . __('Visible');
			$list .= '</th>';
			$list .= '<th width="10%">' . __('Actions');
			$list .= '</th>';
			$list .= '</tr>';
			$list .= '</thead>';
			$list .= '<tbody>';

			if (count($items))
			{
				$index = 1;
				foreach ($items as $item)
				{
					$list .= '<tr>';
					$list .= '<td>' . $index;
					$list .= '</td>';
					$list .= '<td>' . $item->category_name;
					$list .= '</td>';
					$list .= '<td>' . '<a href="options-general.php?page=creativesignal-testimonial/creativesignal-testimonials.php&cid=' . $item->id . '">' . $item->title .'</a>';
					$list .= '</td>';
					$list .= '<td>[cs-testimonials text_length="9999" category="' . $item->category_alias . '"]';
					$list .= '</td>';
					$list .= '<td>' . $item->ordering;
					$list .= '</td>';
					$list .= '<td>' . $item->visible;
					$list .= '</td>';
					$list .= '<td><a href="options-general.php?page=creativesignal-testimonial/creativesignal-testimonials.php&cid=' . $item->id . '">' . __('Edit') . '</a> &nbsp; <a href="javascript:void(0);" onclick="objCSTAdmin.deleteTestimonial(' . $item->id . ');">' . __('Delete') . '</a>';
					$list .= '</td>';
					$list .= '</tr>';
					$index++;
				}
			}

			$list .= '</tbody>';
			$list .= '</table>';
		}
		else
		{
			$data = $this->CreativeSignalTestimonial_getTestimonial($id);
			if (is_null($data))
			{
				$data = new stdClass;
				$data->id 			= 0;
				$data->image 		= '';
				$data->uri 			= '';
				$data->target 		= '';
				$data->author_name 	= '';
				$data->title	 	= '';
				$data->desc 		= '';
				$data->ordering 	= '';
				$data->visible 		= '';
				$data->category 	= '';
				$data->date 		= '';
			}
			else
			{
				$data = $data[0];
			}
		}

		add_thickbox();
		wp_enqueue_script('media-upload');

		$imageLibraryURL = get_upload_iframe_src( 'image', null, 'library' );
		$imageLibraryURL = remove_query_arg( 'TB_iframe', $imageLibraryURL );
		$imageLibraryURL = add_query_arg(array('TB_iframe' => 1), $imageLibraryURL );

		$html  .= '<div class="creativesignal-testimonial-clr"></div>';
		$html  .= '<form class="form-horizontal" action="'. admin_url('options-general.php?page=creativesignal-testimonial/creativesignal-testimonials.php') . '" name="frm-creativesignal-testimonial" method="post">';
		$html  .= '<legend>' . ((!$id || is_null($id)) ? __('Add New Testimonial') : __('Testimonial Details')) . '</legend>';
		$html  .= '<div class="control-group">';
		$html  .= '<label class="control-label">' . __('Title') . '*</label>';
		$html  .= '<div class="controls">';
		$html  .= '<input type="text" name="title" id="title" value="' . $data->title . '" size="120" />';
		$html  .= '</div>';
		$html  .= '</div>';

		$html  .= '<div class="control-group">';
		$html  .= '<label class="control-label">' . __('Author name') . '*</label>';
		$html  .= '<div class="controls">';
		$html  .= '<input type="text" name="author_name" id="author_name" value="' . $data->author_name . '" size="120" />';
		$html  .= '</div>';
		$html  .= '</div>';

		$html  .= '<div class="control-group">';
		$html  .= '<label class="control-label">' . __('Testimonial') . '*</label>';
		$html  .= '<div class="controls">';
		$html  .= '<textarea name="desc" id="desc" cols="117" rows="5">' . $data->desc . '</textarea>';
		$html  .= '</div>';
		$html  .= '</div>';

		$html  .= '<div class="control-group">';
		$html  .= '<label class="control-label">' . __('Image') . '</label>';
		$html  .= '<div class="controls">';
		$html  .= '<input type="text" name="image" id="image" value="' . $data->image . '" size="120" />&nbsp;<a id="choose-from-library-link" class="button thickbox" href="' . esc_url($imageLibraryURL) . '">' . __('Choose Image') . '</a>';
		$html  .= '<br />' . __('Click on button Choose Image, copy image\'s URL and paste it in this field');
		$html  .= '</div>';
		$html  .= '</div>';

		$html  .= '<div class="control-group">';
		$html  .= '<label class="control-label">' . __('Website Address') . '</label>';
		$html  .= '<div class="controls">';
		$html  .= '<input type="text" name="uri" id="uri" value="' . $data->uri . '" size="120" />';
		$html  .= '<br />' . __('For example: www.your-website-address.com');
		$html  .= '</div>';
		$html  .= '</div>';

		$html  .= '<div class="control-group">';
		$html  .= '<label class="control-label">' . __('Target Options') . '*</label>';
		$html  .= '<div class="controls">';
		$html  .= '<select name="target" id="target">
	            	<option value="_blank"' . (($data->target == '_blank') ? ' selected' : '') . '>_blank</option>
	            	<option value="_parent"' . (($data->target == '_parent') ? 'selected' : '') . '>_parent</option>
	         		</select>';
		$html  .= '</div>';
		$html  .= '</div>';

		$html  .= '<div class="control-group">';
		$html  .= '<label class="control-label">' . __('Select Category') . '*</label>';
		$html  .= '<div class="controls">';
		$html  .= $this->CreativeSignalTestimonial_createComboBoxCategory($data->category_alias);
		if (!$id || is_null($id))
		{
			$html  .= '&nbsp;' . __('Or create new category') . ' <input name="new_category" type="text" id="new_category" value="" size="50" />';
		}
		$html  .= '</div>';
		$html  .= '</div>';

		$html  .= '<div class="control-group">';
		$html  .= '<label class="control-label">' . __('Visible') . '*</label>';
		$html  .= '<div class="controls">';
		$html  .= '<select name="visible" id="visible">
	            	<option value="yes"' . (($data->visible == 'yes') ? 'selected' : '') . '>' . __('Yes') . '</option>
	            	<option value="no"' . (($data->visible == 'no') ? 'selected' : '') . '>' . __('No') . '</option>
	         		</select>';
		$html  .= '</div>';
		$html  .= '</div>';

		$html  .= '<div class="control-group">';
		$html  .= '<label class="control-label">' . __('Order') . '</label>';
		$html  .= '<div class="controls">';
		$html  .= '<input type="number" name="ordering" id="ordering" value="' . (int) $data->ordering . '" size="10" />';
		$html  .= '</div>';
		$html  .= '</div>';

		$html  .= '<div class="control-group">';
		$html  .= '<label class="control-label"></label>';
		$html  .= '<div class="controls">';
		$html  .= '<button type="button" class="button-primary" onclick="objCSTAdmin.submitForm();">' . ((!$id || is_null($id)) ? __('Add') : __('Save changes')) . '</button>';
		if ($id)
		{
			$html  .= '&nbsp;<button type="button" class="button" onclick="window.location=\'' . admin_url('options-general.php?page=creativesignal-testimonial/creativesignal-testimonials.php') . '\';">' . __('Cancel') . '</button>';
		}
		$html  .= '</div>';
		$html  .= '</div>';
		$html  .= '<input type="hidden" name="cid" id="cid" value="' . $id . '" />';
		$html  .= '<input type="hidden" name="task" id="task" value="' . ((!$id) ? 'add' : 'edit') . '" />';
		$html  .= '</form>';

		$script = '<script type="text/javascript">
			var objCSTAdmin;
			(function($)
			{
				$(document).ready(function() {
				 	objCSTAdmin = new $.CreativeSignalTestimonialAdmin();
				 	$("#table-creativesignal-testimonial-list").dataTable();
				});

			})(jQuery);
			</script>';
		echo '<div id="creativesignal-testimonial-admin-form">' . $list . $html . $script . '</div>';
	}
	/******************************************** ADMIN SECTION END *****************************/

	/******************************************** ADVANCE FUNCTIONS BEGIN *****************************/
	/**
	 *
	 * Update a specified testimonial
	 * @param array $post
	 *
	 * @return @return int|false Number of rows affected/selected or false on error
	 */
	function CreativeSignalTestimonial_UpdateTestimonial($post)
	{
		global $wpdb;
		$objUtils 		= new CreativeSignalTestimonialUtils();
		$category 		= trim($post['category']);
		$category		= $objUtils->decodeJSON($category);
		$category_alias = mysql_real_escape_string($category->category_alias);
		$category_name 	= mysql_real_escape_string($category->category_name);

		$query = "UPDATE " . WP_CREATIVE_SIGNAL_TESTIMONIAL_TABLE . ""
							. " SET `image` = '" . mysql_real_escape_string(trim($post['image']))
							. "', `uri` = '" . mysql_real_escape_string(trim($post['uri']))
							. "', `target` = '" . mysql_real_escape_string(trim($post['target']))
							. "', `author_name` = '" . trim($post['author_name'])
							. "', `title` = '" . trim($post['title'])
							. "', `desc` = '" . trim($post['desc'])
							. "', `ordering` = '" . (int) mysql_real_escape_string(trim($post['ordering']))
							. "', `visible` = '" . mysql_real_escape_string(trim($post['visible']))
							. "', `category_alias` = '" . $category_alias
							. "', `category_name` = '" . $category_name
							. "' WHERE `id` = '" . (int) $post['cid']
							. "'";
		return $wpdb->query($query);
	}

	/**
	 *
	 * Insert a new testimonial to database
	 * @param array $post
	 *
	 * @return @return int|false Number of rows affected/selected or false on error
	 */
	function CreativeSignalTestimonial_InsertTestimonial($post)
	{
		global $wpdb;
		$objUtils 	= new CreativeSignalTestimonialUtils();
		if (!empty($post['new_category']))
		{
			$category_alias = $objUtils->stringURLSafe(mysql_real_escape_string(trim($post['new_category'])));
			$category_name = mysql_real_escape_string(trim($post['new_category']));
		}
		else
		{
			$category 		= trim($post['category']);
			$category		= $objUtils->decodeJSON($category);
			$category_alias = mysql_real_escape_string($category->category_alias);
			$category_name 	= mysql_real_escape_string($category->category_name);
		}

		$query = "INSERT INTO " . WP_CREATIVE_SIGNAL_TESTIMONIAL_TABLE . ""
					. " SET `image` = '" . mysql_real_escape_string(trim($post['image']))
					. "', `uri` = '" . mysql_real_escape_string(trim($post['uri']))
					. "', `target` = '" . mysql_real_escape_string(trim($post['target']))
					. "', `author_name` = '" . trim($post['author_name'])
					. "', `title` = '" . trim($post['title'])
					. "', `desc` = '" . trim($post['desc'])
					. "', `ordering` = '" . (int) mysql_real_escape_string(trim($post['ordering']))
					. "', `visible` = '" . mysql_real_escape_string(trim($post['visible']))
					. "', `category_alias` = '" . $category_alias
					. "', `category_name` = '" . $category_name
					. "', `date` = '" . strtotime(date("Y-m-d H:i:s"))
					. "'";
		return $wpdb->query($query);
	}

	/**
	 *
	 * Delete a new testimonial from database
	 * @param array $post
	 *
	 * @return @return true|false
	 */

	function CreativeSignalTestimonial_DeleteTestimonial($post)
	{
		global $wpdb;
		return $wpdb->query("DELETE FROM " . WP_CREATIVE_SIGNAL_TESTIMONIAL_TABLE . " WHERE id = " . (int) $post['cid']);
	}
	/**
	 *
	 * Get all testimonial items in database
	 *
	 * @return mixed Database query results
	 */
	function CreativeSignalTestimonial_getTestimonials()
	{
		global $wpdb;
		$data = $wpdb->get_results("SELECT * FROM " . WP_CREATIVE_SIGNAL_TESTIMONIAL_TABLE . " ORDER BY `ordering`");
		return $data;
	}

	/**
	 *
	 * Get a testimonial item by its id
	 *
	 * @param int $item ID of testimonial item
	 *
	 * @return mixed Database query results
	 */
	function CreativeSignalTestimonial_getTestimonial($item)
	{
		global $wpdb;
		$data = $wpdb->get_results("SELECT * FROM " . WP_CREATIVE_SIGNAL_TESTIMONIAL_TABLE . " WHERE `id` = " . (int) $item);
		return $data;
	}

	/**
	 *
	 * Get a testimonial item by category id
	 *
	 * @param int $item ID of testimonial item
	 *
	 * @return mixed Database query results
	 */
	function CreativeSignalTestimonial_getTestimonialsByCategoryID($categoryID)
	{
		global $wpdb;
		if (!empty($categoryID))
		{
			$data = $wpdb->get_results("SELECT * FROM " . WP_CREATIVE_SIGNAL_TESTIMONIAL_TABLE . " WHERE `visible` = \"yes\" AND `category_alias` = \"" . trim($categoryID) . "\" ORDER BY ordering");
		}
		else
		{
			$data = $wpdb->get_results("SELECT * FROM " . WP_CREATIVE_SIGNAL_TESTIMONIAL_TABLE . " WHERE `visible` = \"yes\" ORDER BY ordering");
		}
		return $data;
	}
	/**
	 *
	 * Create a comboxBox to list all categories available
	 * @param string $selected The value will be marked with attribute "selected" in comboBox
	 */
	function CreativeSignalTestimonial_createComboBoxCategory($selected, $id = '', $name = '', $json = true)
	{
		global $wpdb;
		$objUtils = new CreativeSignalTestimonialUtils();
		$query 	= "SELECT category_alias, category_name FROM " . WP_CREATIVE_SIGNAL_TESTIMONIAL_TABLE .  ' GROUP BY category_alias';
		$items	= $wpdb->get_results($query);
		$html = '<select name="' . (($name == '') ? 'category' : $name) . '" id="' . (($id == '') ? 'category' : $id) . '">';
		$html .= '<option value="">-- ' . __('Select category') . ' --</option>';
		if (count($items))
		{
			foreach ($items as $item)
			{
				$array = array();
				$array['category_alias'] 	= $item->category_alias;
				$array['category_name'] 	= $item->category_name;
				$html .= '<option value="' . (($json) ? htmlspecialchars($objUtils->encodeJSON($array), ENT_COMPAT, 'UTF-8') : htmlspecialchars($item->category_alias)) . '" '. (($item->category_alias == $selected) ? ' selected="selected"' : '') . '>' . $item->category_name . '</option>';
			}
		}
		$html .= '</select>';
		return $html;
	}
/******************************************** ADVANCE FUNCTIONS END *****************************/
}

class CreativeSignalTestimonial_Widget extends WP_Widget
{
	/**
	 * Register widget with WordPress.
	 */
	function CreativeSignalTestimonial_Widget()
	{
		add_action('wp_enqueue_scripts', array($this, 'CreativeSignalTestimonial_Widget_Media'));

		/* Widget settings. */
		$widget_ops = array('classname' => 'widget_creativesignal_testimonial', 'description' => 'CreativeSignal Testimonial Widget.' );
		/* Widget control settings. */
		$control_ops = array('id_base' => 'creativesignal_testimonial_widget');

		/* Create the widget. */
		$this->WP_Widget('creativesignal_testimonial_widget', 'CreativeSignal Testimonials Widget', $widget_ops, $control_ops);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	function widget($args, $instance)
	{
		$objCSTestimonial = new CreativeSignalTestimonial;
		$objUtils		  = new CreativeSignalTestimonialUtils();
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);
		$category = $instance['category'];
		$textLength = (int) $instance['text_length'];

		echo $before_widget;

		if (!empty($title))
		{
			echo $before_title . $title . $after_title;
		}
		$items 	= $objCSTestimonial->CreativeSignalTestimonial_getTestimonialsByCategoryID($category);
		$id 	= 'creativesignal-testimonial-scroll-' . $this->number;
		?>
		<div class="creativesignal-testimonial-scroll" id="<?php echo $id; ?>">
		<?php
		if (count($items))
		{
		?>
		<script type="text/javascript">
		(function($){
			$(document).ready(function ($) {
				$("#<?php echo $id; ?>").creativeSignalTestimonial();
			});
		})(jQuery);
		</script>
		<?php

			foreach ($items as $item)
			{
		?>
			<div class="creativesignal-testimonial-box">
				<p class="title"><?php echo $item->title; ?></p>
				<?php
				if ($item->image == '')
				{
					$item->image = WP_CREATIVE_SIGNAL_TESTIMONIAL_MAINURL . '/wp-content/plugins/creativesignal-testimonial/assets/images/no-photo.jpg';
				}
				?>
				<p class="image"><img width="60" height="60" src="<?php echo $item->image; ?>" /></p>
				<p class="content"><?php echo $objUtils->wordLimiter($item->desc, $textLength); ?></p>
				<p class="author_name"><?php echo $item->author_name; ?>
				<?php if ($item->uri != '') {?>
				<br /><a href="http://<?php echo $item->uri; ?>" target="_blank"><?php echo $item->uri; ?></a></p>
				<?php } ?>
			</div>
			<span class="creativesignal-testimonial-separator"></span>
		<?php
			}
		}
		else
		{
			$html .= '<div class="creativesignal-testimonial-alert-box">';
			$html .= '<div class="header">';
			$html .= __('CreativeSignal Testimonials');
			$html .= '</div>';
			$html .= '<div class="body">';
			$html .= __('Nothing found.');
			$html .= '</div>';
			$html .= '<div class="footer">';
			$html .= '</div>';
			$html .= '</div>';
			echo $html;
		}
		?>
		</div>
		<?php
		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	function update($new_instance, $old_instance)
	{
		$instance = array();
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['text_length'] = strip_tags($new_instance['text_length']);
		$instance['category'] = strip_tags($new_instance['category']);
		return $instance;
	}
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	function form($instance)
	{
		$objCSTestimonial = new CreativeSignalTestimonial;

		if (isset($instance['title']))
			$title = $instance['title'];
		else
			$title = '';

		if (isset($instance['text_length']))
			$textLength = $instance['text_length'];
		else
			$textLength = '9999';

		if (isset($instance['category']))
		{
			$category = $objCSTestimonial->CreativeSignalTestimonial_createComboBoxCategory($instance['category'], $this->get_field_id('category'), $this->get_field_name('category'), false);
		}
		else
		{
			$category = $objCSTestimonial->CreativeSignalTestimonial_createComboBoxCategory('', $this->get_field_id('category'), $this->get_field_name('category'), false);
		}
		?>
		<div class="control-group">
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<div class="controls" style="margin-top: 5px;">
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
			</div>
		</div>
		<div class="control-group" style="margin-top: 10px;">
			<label for="<?php echo $this->get_field_id('text_length'); ?>"><?php _e('Text Length:'); ?></label>
			<div class="controls" style="margin-top: 5px;">
				<input class="widefat" id="<?php echo $this->get_field_id('text_length'); ?>" name="<?php echo $this->get_field_name('text_length'); ?>" type="number" value="<?php echo esc_attr($textLength); ?>" />
			</div>
		</div>
		<div class="control-group" style="margin-top: 10px;">
			<label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category:'); ?></label>
			<div class="controls" style="margin-top: 5px;">
				<?php echo $category; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add the necessary files CSS & JavaScript to page header
	 */
	function CreativeSignalTestimonial_Widget_Media()
	{
	 	wp_register_script('cs-testimonials-widget-js-autoscroll', WP_CREATIVE_SIGNAL_TESTIMONIAL_MAINURL . '/wp-content/plugins/creativesignal-testimonial/assets/js/jquery.autoscroll.js');
	 	wp_register_script('cs-testimonials-widget-js', WP_CREATIVE_SIGNAL_TESTIMONIAL_MAINURL . '/wp-content/plugins/creativesignal-testimonial/assets/js/creativesignal-testimonial.js');
		wp_enqueue_script('cs-testimonials-widget-js-autoscroll');
		wp_enqueue_script('cs-testimonials-widget-js');

		wp_register_style('cs-testimonials-css', WP_CREATIVE_SIGNAL_TESTIMONIAL_MAINURL . '/wp-content/plugins/creativesignal-testimonial/assets/css/creativesignal-testimonial.css');
		wp_enqueue_style('cs-testimonials-css');
	}
}
// register CreativeSignalTestimonial_Widget widget
add_action('widgets_init', create_function('', 'register_widget( "creativesignaltestimonial_widget" );'));

$creativeSignalTestimonialInstance = new CreativeSignalTestimonial;
add_shortcode('cs-testimonials', array('CreativeSignalTestimonial', 'CreativeSignalTestimonial_ShortCode'));