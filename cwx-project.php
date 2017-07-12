<?php
/*
Plugin Name: CWX Project
Plugin URI: https://wordpress.org/plugins/cwx-project/
Description: CWX Project is an easy to use Project Management and ToDo-List application for Wordpress Blogs, encouraging collaborative work. 
Version: 1.0.2
Author: ChromeWorx
Author URI: https://wordpress.org/support/profile/cwx-chrome
License: GPLv3 or later
*/

/**
 * Contribution made by 'cwmedia', wordpress.org user
 *
 * @package CWX Project
 */

/*
Simple Project Management and TODO-List application for Wordpress Blogs.
Copyright (C) 2014 Chrome Runner

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

// Make sure we don't expose any info if called directly
defined( 'ABSPATH' ) || exit;


define('CWXPRJ_VERSION', '1.0.1');
define('CWXPRJ__DBT', 'cwx_project');
define('CWXPRJ__URL', plugin_dir_url(__FILE__));
define('CWXPRJ__DIR', plugin_dir_path(__FILE__));
define('CWXPRJ__INC', CWXPRJ__DIR . '/inc/');


// Load Core
require_once(CWXPRJ__DIR . '/cwx-project-core.php');

// Load Help
require_once(CWXPRJ__DIR . '/cwx-project-help.php');

// Load Widget HTML Templates
require_once(CWXPRJ__DIR . '/cwx-project-tmpl-widget.php');


class cwxProject
{
	public static $timeFormat;
	public static $dateFormat;
	public static $dateFormatIn;
	public static $dateFormatOut;
	public static $dateFormatJSOut;

	public static $dbt;
	public static $dbt_meta;
	public static $dbt_users;
	public static $core;
	public static $widget;
	public static $options;
	public static $statuses = array();
	public static $categories = array();
	public static $priorityLabels;
	public static $priorityColors;
	public static $priorityColorsDefault = array(
		1 => '#ccd7ed',
		2 => '#9bbbff',
		3 => '#6095ff',
		4 => '#00cc86',
		5 => '#40be3f',
		6 => '#7bff7b',
		7 => '#dede1f',
		8 => '#fe7f00',
		9 => '#fe0000'
	);
	public static $assignable_users = array();
	public static $capabilities;
	private static $_roles_caps = array(
		'administrator' => array(
			//'cwxproj-use-widget',
			'cwxproj-use-dashwidget',
			'cwxproj-add-projects',
			'cwxproj-edit-own-projects',
			'cwxproj-edit-other-projects',
			'cwxproj-trash-own-projects',
			'cwxproj-trash-other-projects',
			'cwxproj-add-tasks',
			'cwxproj-edit-own-tasks',
			'cwxproj-edit-other-tasks',
			'cwxproj-edit-assigned-tasks',
			'cwxproj-edit-other-assigned-tasks',
			'cwxproj-trash-own-tasks',
			'cwxproj-trash-other-tasks',
			'cwxproj-self-assign',
			'cwxproj-edit-own-assignment',
			'cwxproj-edit-other-assignment',
			'cwxproj-edit-date-due',
			'cwxproj-edit-own-statuses',
			'cwxproj-edit-other-statuses',
			'cwxproj-edit-own-categories',
			'cwxproj-edit-other-categories',
			'cwxproj-trashcan',
			'cwxproj-configure',
			'cwxproj-configure-statuses',
			'cwxproj-configure-categories',
			'cwxproj-configure-metabox',
			'cwxproj-configure-colors',
			'cwxproj-configure-permissions'
		),
		
		'editor' => array(
			//'cwxproj-use-widget',
			'cwxproj-use-dashwidget',
			'cwxproj-add-projects',
			'cwxproj-edit-own-projects',
			'cwxproj-edit-other-projects',
			'cwxproj-trash-own-projects',
			'cwxproj-trash-other-projects',
			'cwxproj-add-tasks',
			'cwxproj-edit-own-tasks',
			'cwxproj-edit-other-tasks',
			'cwxproj-edit-assigned-tasks',
			'cwxproj-edit-other-assigned-tasks',
			'cwxproj-trash-own-tasks',
			'cwxproj-trash-other-tasks',
			'cwxproj-self-assign',
			'cwxproj-edit-own-assignment',
			'cwxproj-edit-other-assignment',
			'cwxproj-edit-date-due',
			'cwxproj-edit-own-statuses',
			'cwxproj-edit-other-statuses',
			'cwxproj-edit-own-categories',
			'cwxproj-edit-other-categories'
		),
		
		'author' => array(
			//'cwxproj-use-widget',
			'cwxproj-use-dashwidget',
			'cwxproj-add-projects',
			'cwxproj-edit-own-projects',
			'cwxproj-trash-own-projects',
			'cwxproj-add-tasks',
			'cwxproj-edit-own-tasks',
			'cwxproj-edit-assigned-tasks',
			'cwxproj-trash-own-tasks',
			'cwxproj-self-assign',
			'cwxproj-edit-own-statuses',
			'cwxproj-edit-own-categories'
		),
		
		'contributor' => array(
			//'cwxproj-use-widget',
			'cwxproj-use-dashwidget',
			'cwxproj-add-tasks',
			'cwxproj-edit-own-tasks',
			'cwxproj-edit-assigned-tasks',
			'cwxproj-trash-own-tasks'
		)
	);
	
	
	public function __construct()
	{
		global $wpdb;

		// Store our table names
		self::$dbt = $wpdb->prefix . CWXPRJ__DBT;
		self::$dbt_meta = $wpdb->prefix . CWXPRJ__DBT . '_meta';
		self::$dbt_users = $wpdb->users;

		// Register Hooks
		register_activation_hook(__FILE__, array($this, 'activation'));
		register_deactivation_hook(__FILE__, array($this, 'deactivation'));
		// uninstal hook registered outside
		// register_uninstall_hook(__FILE__, array('self', 'uninstall'));

		add_action('plugins_loaded', array($this, 'updateCheck'));
		add_action('plugins_loaded', array($this, 'setup'));
		if(defined('CWX_DEBUG'))
			add_action('activated_plugin', array($this, 'errorActivation'));

		if(is_admin()){
			add_action('init', array($this, 'initDashWidget'));
		}
		else {
			add_action('init', array($this, 'initSiteWidget'));
		}
	}
	
	public function errorActivation()
	{
		print(ob_get_contents());
		ob_end_flush();
		ob_start();
	}

	public static function getConfig($which)
	{
		if(self::$options && isset(self::$options[$which]))
			return self::$options[$which];
			
		return false;
	}
	
	public static function &getRoleCaps($roleid){
		if(isset(self::$_roles_caps[$roleid]))
			return self::$_roles_caps[$roleid];
		
		$rv = array();
		return $rv;
	}
	
	public static function getAssigneeIdByName($name)
	{
		$name = trim($name);
		
		foreach(self::$assignable_users as $id => $data){
			if($name == $data->user || $name == $data->name)
				return $id;
		}
		
		return null;
	}
	
	public static function getMetaIdByTitle($title, $type = 'all')
	{
		switch($type){
			case 'status':
				$meta = self::$statuses;
				break;
			case 'category':
				$meta = self::$categories;
				break;
			default:
				$meta = array_merge(self::$categories, self::$statuses);
		}
		
		foreach($meta as $id => $data){
			if($data->title == $title)
				return $id;
		}
		
		return false;
	}
	
	public static function getPostTypes()
	{
		$post_types = get_post_types();
		
		// Remove these
		$removed = array('attachment', 'revision', 'nav_menu_item');
		foreach($removed as $type){
			unset($post_types[$type]);
		}
		
		return apply_filters('cwx-project-get-post-types', $post_types, $removed);
	}
	
	public static function getFormattedDate($timestamp, $format = null)
	{
		$format = ($format !== null)? $format: self::$dateFormat;
		$tz = get_option('timezone_string');

		$date = new DateTime("@{$timestamp}");
		$date->setTimezone(
			new DateTimeZone(
				(!empty($tz))? $tz: 'UTC'
			)
		);
		
		return $date->format($format);
	}
	
	public static function isSuperAdmin()
	{
		// WP function 'is_super_admin()' returns true for all users with role
		// 'administrator' by default. We need this more strict.
		
		if(is_multisite())
			return is_super_admin();
			
		return (is_super_admin() && (int) get_current_user_id() === 1);
	}
	

	/**
	 * Plugin Hooks
	 */
	 
	public function setup()
	{
		global $wpdb;
		
		// Translations
		load_plugin_textdomain('cwx-project', false, basename(dirname(__FILE__)) . '/lang');

		// Set up translation for priorities		
		self::$priorityLabels = array(
			1 => __('1 (low)', 'cwx-project'),
			2 => __('2', 'cwx-project'),
			3 => __('3', 'cwx-project'),
			4 => __('4', 'cwx-project'),
			5 => __('5 (medium)', 'cwx-project'),
			6 => __('6', 'cwx-project'),
			7 => __('7', 'cwx-project'),
			8 => __('8', 'cwx-project'),
			9 => __('9 (high)', 'cwx-project')
		);
		
		// Set up our capabilities (for WP Roles system)
		self::$capabilities = array(
			// 'cwxproj-use-widget'				=> __('Use Site Widget', 'cwx-project'),
			'cwxproj-use-dashwidget'			=> __('Use Dashboard Widget', 'cwx-project'),
			'cwxproj-add-projects'				=> __('Add Projects', 'cwx-project'),
			'cwxproj-edit-own-projects'			=> __('Edit Own Projects', 'cwx-project'),
			'cwxproj-edit-other-projects'		=> __('Edit Others Projects', 'cwx-project'),
			'cwxproj-trash-own-projects'		=> __('Trash Own Projects', 'cwx-project'),
			'cwxproj-trash-other-projects'		=> __('Trash Others Projects', 'cwx-project'),
			'cwxproj-add-tasks'					=> __('Add Tasks', 'cwx-project'),
			'cwxproj-edit-own-tasks'			=> __('Edit Own Tasks', 'cwx-project'),
			'cwxproj-edit-other-tasks'			=> __('Edit Others Tasks', 'cwx-project'),
			'cwxproj-edit-assigned-tasks'		=> __('Edit Own Assigned Tasks', 'cwx-project'),
			'cwxproj-edit-other-assigned-tasks'	=> __('Edit Tasks Assigned to Others', 'cwx-project'),
			'cwxproj-trash-own-tasks'			=> __('Trash Own Tasks', 'cwx-project'),
			'cwxproj-trash-other-tasks'			=> __('Trash Others Tasks', 'cwx-project'),
			'cwxproj-self-assign'				=> __('Self-Assign Tasks', 'cwx-project'),
			'cwxproj-edit-own-assignment'		=> __('Edit Own Assignment', 'cwx-project'),
			'cwxproj-edit-other-assignment'		=> __('Edit Others Assignment', 'cwx-project'),
			'cwxproj-edit-date-due'				=> __('Edit Due Date', 'cwx-project'),
			'cwxproj-edit-own-statuses'			=> __('Edit Own Statuses', 'cwx-project'),
			'cwxproj-edit-other-statuses'		=> __('Edit Others Statuses', 'cwx-project'),
			'cwxproj-edit-own-categories'		=> __('Edit Own Categories', 'cwx-project'),
			'cwxproj-edit-other-categories'		=> __('Edit Others Categories', 'cwx-project'),
			'cwxproj-trashcan'					=> __('Access Trash Can', 'cwx-project'),
			'cwxproj-configure'					=> __('Configuration', 'cwx-project'),
			'cwxproj-configure-categories'		=> __('Configure Categories', 'cwx-project'),
			'cwxproj-configure-statuses'		=> __('Configure Statuses', 'cwx-project'),
			'cwxproj-configure-metabox'			=> __('Configure Task Box', 'cwx-project'),
			'cwxproj-configure-colors'			=> __('Configure Priority Colors', 'cwx-project'),
			'cwxproj-configure-permissions'		=> __('Configure Permissions', 'cwx-project')
		);
		
		// Core instance
		self::$core = new cwxProjectCore(self::$dbt, self::$dbt_meta, self::$dbt_users);

		// Fetch options
		self::$options = get_option('cwx_project_options', false);

		// Fetch categories and statuses
		$query = "SELECT m.*, u.display_name "
				."FROM %s AS m "
				."LEFT JOIN %s AS u ON (u.ID=m.creator_id) "
				."ORDER BY m.title";
				
		$rows = $wpdb->get_results(sprintf($query, self::$dbt_meta, self::$dbt_users));
		foreach($rows as $row){
			$row->title = stripslashes($row->title);
			$row->is_proposal = (bool) (!empty($row->creator_id));
			if($row->meta_type == 'category')
				self::$categories[$row->id] = $row;
			else if($row->meta_type == 'status')
				self::$statuses[$row->id] = $row;
		}
		unset($rows);

		// Get assignable users
		$qKey = $wpdb->get_blog_prefix(get_current_blog_id()) . 'capabilities';
		$qMeta = array('relation' => 'OR');
		foreach(self::$options['assignee_roles'] as $roleid){
			$qMeta[] = array(
				    'key' => $qKey,
				    'value' => $roleid,
				    'compare' => 'like'
			);
		}
		if(count($qMeta) > 1){
			$qResults = new WP_User_Query(array('meta_query' => $qMeta));
			foreach($qResults->results as $user){
				self::$assignable_users[$user->data->ID] = (object) array(
					'id' => $user->data->ID,
					'user' => $user->data->user_login,
					'name' => $user->data->display_name
				);
			}
		}

		// Set up priority colors
		if(isset($options['priority_colors']))
			self::$priorityColors = $options['priority_colors'];
		else
			self::$priorityColors = self::$priorityColorsDefault;
			
		// Set date formats (en_US)
		self::_setDateFormats();
	}

	public function initSiteWidget()
	{
		// Check user capability
		if(!self::isSuperAdmin() && !current_user_can('cwxproj-use-widget'))
			return;

		// Site Widget
		
		// Not implemented!
	}

	public function initDashWidget()
	{
		global $pagenow;

		// Check user capability
		if(!self::isSuperAdmin() && !current_user_can('cwxproj-use-dashwidget'))
			return;
		
		require_once(CWXPRJ__DIR . 'cwx-project-dashboard.php');		
		self::$widget = new cwxProjectDashWidget(self::$dbt, self::$dbt_meta);
			
		add_action('admin_enqueue_scripts', array($this, 'enqueueWidget'), 100);

		// Only for Dashboard page
		if($pagenow == 'index.php'){
			add_action('admin_print_scripts', array($this, 'printDashWidgetCSS'), 1000);
			add_action('admin_head', array(self::$widget, 'widgetHelp'), 100);
		}
		// Only for allowed post_type pages
		else if($pagenow == 'post.php' || $pagenow == 'post-new.php'){
			add_action('admin_head', array(self::$widget, 'metaboxHelp'), 100);
		}
	}

	public function activation()
	{
		if(!current_user_can('activate_plugins'))
			return;

		$plugin = isset($_REQUEST['plugin'])? $_REQUEST['plugin']: '';
		check_admin_referer("activate-plugin_{$plugin}");

		// Install DB table if needed
		$this->updateCheck();
	}

	public function deactivation()
	{
		if(!current_user_can('activate_plugins'))
			return;

		$plugin = isset($_REQUEST['plugin'])? $_REQUEST['plugin']: '';
		check_admin_referer("deactivate-plugin_{$plugin}");

		if(defined('CWX_DEBUG')){
			// Uninstall completely
			self::uninstall(true);
		}
		
		delete_option('cwx_project_version');
	}
	
	public function updateCheck()
	{
		$ver = get_option('cwx_project_version', false);
		
		if($ver === false)
			self::_initPermissions();
		
		if($ver !== CWXPRJ_VERSION)
			self::_install();
	}
	
	private static function _install()
	{
		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		$settings = '';
		if(!empty($wpdb->charset))
			$settings .= " DEFAULT CHARACTER SET {$wpdb->charset}";
		if(!empty($wpdb->collate))
			$settings .= " COLLATE {$wpdb->collate}";
		
		// Main db table
		$table = self::$dbt;
		$sql = "CREATE TABLE {$table} (
			id int(10) unsigned NOT NULL AUTO_INCREMENT,
			parent_id int(10) unsigned DEFAULT NULL,
			title varchar(100) NOT NULL,
			creator_id int(10) unsigned NOT NULL,
			date_created int(10) unsigned NOT NULL,
			date_updated int(10) unsigned NOT NULL,
			is_private tinyint(1) DEFAULT '0',
			is_done tinyint(1) DEFAULT '0',
			is_trash tinyint(1) DEFAULT '0',
			priority tinyint(3) unsigned DEFAULT NULL,
			progress float DEFAULT NULL,
			t_assignee int(10) DEFAULT NULL,
			t_status int(10) DEFAULT NULL,
			t_category int(10) DEFAULT NULL,
			t_date_due int(10) unsigned DEFAULT NULL,
			t_time_spent int(10) unsigned DEFAULT NULL,
			t_post_id int(10) unsigned DEFAULT NULL,
			t_description longtext,
			PRIMARY KEY  (id),
			KEY parent_id (parent_id),
			KEY creator_id (creator_id),
			KEY date_updated (date_updated),
			KEY is_private (is_private),
			KEY is_trash (is_trash),
			KEY title (title),
			KEY priority (priority),
			KEY progress (progress),
			KEY t_status (t_status),
			KEY t_category (t_category),
			KEY t_date_due (t_date_due)
		){$settings};";

		// Create or update table
		dbDelta($sql);

		// Meta data db table
		$table = self::$dbt_meta;
		$sql = "CREATE TABLE {$table} (
			id int(10) unsigned NOT NULL AUTO_INCREMENT,
			parent_id int(10) unsigned DEFAULT NULL,
			creator_id int(10) unsigned DEFAULT NULL,
			meta_type varchar(100) NOT NULL,
			title varchar(100) NOT NULL,
			PRIMARY KEY  (id),
			KEY parent_id (parent_id),
			KEY creator_id (creator_id),
			KEY meta_type (meta_type),
			KEY title (title)
		){$settings};";

		// Create or update table
		dbDelta($sql);

		// Set our version, required by our update management
		update_option('cwx_project_version', CWXPRJ_VERSION);
	}
	
	public static function uninstall($param = false)
	{
		global $wpdb;

		if(!current_user_can('activate_plugins'))
			return;

		if($param !== true && !defined('CWX_DEBUG')){
			check_admin_referer('bulk-plugins');
		}
		
		// Remove DB Table
		$err = $wpdb->query("DROP TABLE IF EXISTS " . self::$dbt);
		$err = $wpdb->query("DROP TABLE IF EXISTS " . self::$dbt_meta);
		
		// Remove capabilities
		self::_removePermissions();
		
		// Delete our options
		delete_option('cwx_project_version');
		delete_option('cwx_project_options');
	}
	
	private static function _initPermissions()
	{
		$options = get_option('cwx_project_options', array());
		$assignee_roles = array();
		
		// Basic setup just with WP default roles
		foreach(self::$_roles_caps as $roleid => $caps){
			$role = get_role($roleid);
			foreach($caps as $cap){
				$role->add_cap($cap);
			}
			
			if($role->has_cap('cwxproj-edit-assigned-tasks'))
				$assignee_roles[] = $roleid;	
		}

		// Update our options
		$options['assignee_roles'] = $assignee_roles;
		update_option('cwx_project_options', $options);
	}
	
	private static function _removePermissions()
	{
		global $wp_roles;
		
		$roles = array_keys($wp_roles->roles);
		$caps = array_keys((array) self::$capabilities);
		foreach($roles as $roleid){
			$role = get_role($roleid);
			foreach($caps as $cap){
				$role->remove_cap($cap);
			}
		}
	}
	
	private static function _setDateFormats()
	{
		// Unified Date Format
		self::$timeFormat = get_option('links_updated_date_format');
		self::$dateFormat = __('mm/dd/yyyy', 'cwx-project');
		$search = "/(mm?|dd?|yyy?y?)/";
				
		/**
		 * PHP 5.3 closures do not work for all WP users (PHP >= 5.2.4)
		 *
		 * Code contributed by user 'cwmedia'
		 */
		
		// PHP Date conversion
		$cb_dc = create_function('$matches', '
			switch($matches[0]){
				case \'dd\': return \'d\';
				case \'d\':  return \'j\';
				case \'mm\': return \'m\';
				case \'m\':  return \'n\';
				case \'yy\':  return \'y\';
				case \'yyyy\': return \'Y\';
			}
			return \'\';
		');
		self::$dateFormatOut = self::$dateFormatIn = preg_replace_callback($search, $cb_dc, self::$dateFormat);

		// Date format for jQ UI Datepicker
		$cb_df = create_function('$matches', '
			switch($matches[0]){
				case \'d\': return \'dd\';
				case \'m\': return \'mm\';
				case \'yyyy\':  return \'yy\';
			}
			return $matches[0];
		');
		self::$dateFormatJSOut = preg_replace_callback($search, $cb_df, self::$dateFormat);		
		
		/** Replaced by above code
		 *
		// PHP Date conversion
		self::$dateFormatOut = self::$dateFormatIn = preg_replace_callback(
			$search,
			function($matches) {
				switch($matches[0]){
					case 'dd': return 'd';
					case 'd':  return 'j';
					case 'mm': return 'm';
					case 'm':  return 'n';
					case 'yy':  return 'y';
					case 'yyyy': return 'Y';
				}
				return '';
			},
			self::$dateFormat
		);

		// Date format for jQ UI Datepicker
		self::$dateFormatJSOut = preg_replace_callback(
			$search,
			function($matches) {
				switch($matches[0]){
					case 'd': return 'dd';
					case 'm': return 'mm';
					case 'yyyy': return 'yy';
				}
				return $matches[0];
			},
			self::$dateFormat
		);
		*/
	}

	/**
	 * Enqueue Widget Scripts and Styles
	 */

	public function enqueueWidget($hook = '')
	{
		$minified = '.min';
		if(defined('WP_DEBUG') && WP_DEBUG){
			$p = CWXPRJ__DIR . 'inc/cwx-project';
			if(file_exists($p . '.css') && file_exists($p . '-rtl.css') && file_exists($p . '.js'))
				$minified = '';
		}
		
		wp_enqueue_style(
			'cwx-project-styles',
			CWXPRJ__URL . "inc/cwx-project{$minified}.css",
			array(),
			'1.0'
		);
		if(is_rtl()){
			wp_enqueue_style(
				'cwx-project-rtl-styles',
				CWXPRJ__URL . "inc/cwx-project-rtl{$minified}.css",
				array('cwx-project-styles'),
				'1.0'
			);
		}
		
		wp_enqueue_script(
			'cwx-project-script',
			CWXPRJ__URL . "inc/cwx-project{$minified}.js",
			array(
				'jquery',
				'jquery-ui-core',
				'jquery-ui-datepicker',
				'jquery-ui-autocomplete'
			),
			'1.0',
			true // in footer
		);

		$l10n = array(
			'confirmUnsaved'		=> __('There are unsaved changes in CWX Project Settings! You will lose all changes if you leave this page now.', 'cwx-project'),
			'confirmUnsavedForm'	=> __('There are unsaved changes in CWX Project! You will lose these changes if you leave the page now.', 'cwx-project'),
			'confirmTrashTitle'		=> __('Confirm Deletion', 'cwx-project'),
			'confirmTrashProject'	=> __('Move the project to trash?', 'cwx-project'),
			'confirmTrashTask'		=> __('Move the task to trash?', 'cwx-project'),
			'confirmYes'			=> __('Yes', 'cwx-project'),
			'confirmNo'				=> __('No', 'cwx-project'),
			'buttonCancel'			=> __('Cancel', 'cwx-project'),
			'buttonClose'			=> __('Close', 'cwx-project'),
			'titleProgress'			=> __('Progress: %s%', 'cwx-project'),
			'titleAssignee'			=> __('Assigned to: %s', 'cwx-project'),
			'titleDueDate'			=> __('Due Date: %s', 'cwx-project'),
		);
		wp_localize_script('cwx-project-script', 'cwxProjectL10n', $l10n);

		// Print jQ UI Datepicker regional settings as separate JS object
		if(is_admin()){
			add_action('admin_footer', array($this, '_localizeDatepicker'));
			// Settings Page
			if(isset($_REQUEST['edit']) && $_REQUEST['edit'] == 'cwx_project_dashwidget')
				$this->_enqueueConfig();
		}
		else {
			add_action('wp_header', array($this, '_localizeDatepicker'));
		}
	}

	private function _enqueueConfig()
	{
	    wp_enqueue_style('wp-color-picker');
    	wp_enqueue_script('wp-color-picker');
	}
	
	public function printDashWidgetCSS()
	{
		global $_wp_admin_css_colors;
		
		$schema_name = get_user_meta(get_current_user_id(), 'admin_color', true);
		if($schema_name == 'fresh')
			return;
			
		$schema = $_wp_admin_css_colors[$schema_name];
		
		$schema_fix = '';
		if($schema_name == 'light'){
			$schema_fix = "
	.cwx-task.open .cwx-task-row li,
	.cwx-task.open .cwx-task-row .dashicons {
		color: #444 !important;
	}
	.cwx-task.open .cwx-task-row:hover li,
	.cwx-task.open .cwx-task-row:focus li,
	.cwx-task.open .cwx-task-row:hover .dashicons,
	.cwx-task.open .cwx-task-row:focus .dashicons {
		color: #fff !important;
	}
			";
		}
		$schema_fix = apply_filters('cwx-filter-admin-colors-fix', $schema_fix, $schema_name, $schema->colors);
		
		print <<<CSS
<style type="text/css">
	.cwx-project.open .cwx-project-row {
		background: {$schema->colors[3]};
	}
	.cwx-task.open .cwx-task-row {
		background: {$schema->colors[0]};
	}
	.cwx-project .cwx-project-row:hover,
	.cwx-project .cwx-project-row:focus,
	.cwx-project.open .cwx-project-row:hover,
	.cwx-project.open .cwx-project-row:focus,
	.cwx-task-row:hover,
	.cwx-task-row:focus,
	.cwx-task.open .cwx-task-row:hover,
	.cwx-task.open .cwx-task-row:focus {
		background: {$schema->colors[1]};
	}
	li.cwx-project-folding a:hover,
	li.cwx-project-folding a:focus,
	li.cwx-project-actions a:hover,
	li.cwx-project-actions a:focus {
		border-color: {$schema->colors[2]};
	}{$schema_fix}
</style>
CSS;
	}
	
	public function _localizeDatepicker()
	{
		// Print jQ UI Datepicker regional settings
		$def = 'var jQUIDatepickerL10n = {';

		/* Translators:
		 *
		 * Copy the data from 
		 * https://github.com/jquery/jquery-ui/tree/master/ui/i18n/ <datepicker-langCode.js>
		 * Note: Some of the regional files use single quotes. Make sure that doesn't collide
		 * with your translation!
		 *
		 * You can get further information about date formatting here:
		 * http://en.wikipedia.org/wiki/Date_format_by_country
		 *
		 * Override 'firstDay' by WP setting!
		 * Override 'dateFormat' by translation file!
		 * Both are done by using a placeholder (%s) in that respective place.
		 */
		$def .= sprintf(
__('
	closeText: "Done",
	prevText: "Prev",
	nextText: "Next",
	currentText: "Today",
	monthNames: ["January","February","March","April","May","June","July","August","September","October","November","December"],
	monthNamesShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
	dayNames: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
	dayNamesShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
	dayNamesMin: ["Su","Mo","Tu","We","Th","Fr","Sa"],
	weekHeader: "Wk",
	dateFormat: "%s",
	firstDay: %s,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: ""
', 'cwx-project'), 
			self::$dateFormatJSOut,
			get_option('start_of_week', '0') // Uses WP setting! (Settings/General page)
		);

		$def .= '};';

		echo "<script type='text/javascript'>\n/* <![CDATA[ */\n{$def}\n/* ]]> */\n</script>\n";
	}
	

	/**
	 * Template Tags
	 */

	public static function userCan($cap, $cap2 = null)
	{
		if(self::isSuperAdmin())
			return true;
		else if(current_user_can($cap))
			return true;
			
		return (bool) ($cap2 && current_user_can($cap2));
	}

	public static function projectList()
	{
		self::$core->listProjects();
	}

	public static function priorityLabel($num)
	{
		if(!isset(self::$priorityLabels[$num]))
			return 'Not defined!';
			
		return self::$priorityLabels[$num];
	}

	public static function priorityColor($num)
	{
		if(!isset(self::$priorityColors[$num]))
			return '#fff';
			
		return self::$priorityColors[$num];
	}

	public static function priorityColorDefault($num)
	{
		if(!isset(self::$priorityColorsDefault[$num]))
			return '#fff';
			
		return self::$priorityColorsDefault[$num];
	}
	
	public static function getDonateButton()
	{
		echo <<<TMPL
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top"><input type="hidden" name="cmd" value="_s-xclick"><input type="hidden" name="hosted_button_id" value="G6CSWPS5UMS2Y"><input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"><img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1"></form>
TMPL;
	}
	
}
new cwxProject();


register_uninstall_hook(__FILE__, array('cwxProject', 'uninstall'));




