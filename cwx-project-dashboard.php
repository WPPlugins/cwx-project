<?php
/**
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


class cwxProjectDashWidget
{
	private $_dbt;
	private $_dbt_meta;

	public function __construct($dbt, $dbt_meta)
	{
		global $pagenow;
		
		$this->_dbt = $dbt;
		$this->_dbt_meta = $dbt_meta;
		
		// Only for Dashboard page
		if($pagenow == 'index.php'){
			add_action('wp_dashboard_setup', array($this, 'widgetCreate'));
		}
		// Only for allowed post_type pages
		else if($pagenow == 'post.php' || $pagenow == 'post-new.php'){
			add_action('add_meta_boxes', array($this, 'metaboxCreate'));
			add_action('save_post', array($this, 'metaboxUpdate'));
		}
	}

	
	/**
	 * Dashboard Widget
	 */

	public function widgetHelp()
	{
		$screen = get_current_screen();
		
		$screen->add_help_tab(array( 
			'id' => 'cwx-project-dashwidget-help',
			'title' => 'CWX Project',
			'content' => '',
			'callback' => array('cwxProjectHelp', 'printWidgetHelp')
		));
		
		$screen->set_help_sidebar(
			$screen->get_help_sidebar() . cwxProjectHelp::getWidgetHelpSidebar()
		);
	}

	public function widgetDisplay()
	{
		cwxProjectTemplates::printWidget();
	}

	public function widgetConfig()
	{
		if(!cwxProject::isSuperAdmin() && !current_user_can('cwxproj-use-dashwidget'))
			return;

		if(isset($_REQUEST['view']) && $_REQUEST['view'] == 'trash' && (cwxProject::isSuperAdmin() || current_user_can('cwxproj-trashcan'))){
			cwxProjectTemplates::printTrash();
		}
		else if(cwxProject::isSuperAdmin() || current_user_can('cwxproj-configure')){
			add_filter('cwx-project-post-types', array('cwxProject', 'getPostTypes'));
			include(CWXPRJ__DIR . 'cwx-project-tmpl-config.php');
		}
	}
	
	public function widgetCreate()
	{
		if(!cwxProject::isSuperAdmin() && !current_user_can('cwxproj-use-dashwidget'))
			return;
			
		if(cwxProject::isSuperAdmin() || current_user_can('cwxproj-configure') || current_user_can('cwxproj-trashcan')){
			wp_add_dashboard_widget(
				'cwx_project_dashwidget',		// Slug
				'CWX Project',					// Title
				array($this, 'widgetDisplay'),	// Display the Widget
				array($this, 'widgetConfig')	// Display Configuration Form
			);
		}
		else {
			wp_add_dashboard_widget(
				'cwx_project_dashwidget',		// Slug
				'CWX Project',					// Title
				array($this, 'widgetDisplay')	// Display the Widget
			);
		}
	}

	
	/**
	 * Posts Metabox
	 */
	
	public function printProjectsAsOptions()
	{
		if(($rows = cwxProject::$core->getProjects()) === false)
			return;
			
		foreach($rows as $row){
			$title = esc_attr($row->title);
			print("<option value=\"{$row->id}\">{$title}</option>");
		}
	}

	public function metaboxPrintTask($task_id)
	{
		cwxProject::$core->printTask($task_id);
	}

	public function metaboxUpdate($post_id)
	{
		global $wpdb;
		
		$post_id = (int) $post_id;
		
		if(!isset($_REQUEST['_project_nonce']))
			return;
		if(!current_user_can('edit_post', $post_id) || !wp_verify_nonce($_REQUEST['_project_nonce'], '_project_task'))
			return;
			
		// Get enabled post types
		if(($post_types = cwxProject::getConfig('enabled_post_types')) === false)
			$post_types = array();
			
		$post_type = sanitize_text_field($_REQUEST['post_type']);
		if(!in_array($post_type, $post_types))
			return;
		
		$tm = time();
		$user_id = (int) get_current_user_id();
		$task_id = (int) get_post_meta($post_id, 'cwx-project-taskid', true);

		$title = sanitize_text_field(get_the_title($post_id));
		$post_type = get_post_type_object($post_type)->labels->singular_name;
		$title = sprintf(__('%1$s: %2$s', 'cwx-project'), $post_type, $title);
		
		// Create a new task on specified project
		if(!$task_id && isset($_REQUEST['cwx_task_project'])){
			// Well, only if requested by user and only if user can do that
			if(empty($_REQUEST['cwx_task_project']) || !current_user_can('cwxproj-add-tasks'))
				return;
				
			$project = null;
			$project_id = sanitize_text_field($_REQUEST['cwx_task_project']);
			
			// Attempt to create the project
			if($project_id === 'new' && current_user_can('cwxproj-add-projects')){
				if(!isset($_REQUEST['cwx_task_new_project']))
					return;
					
				$project_title = sanitize_text_field($_REQUEST['cwx_task_new_project']);
					
				// Sanitize is_private field
				$is_private = false;
				if(isset($_REQUEST['cwx_task_project_private'])){
					if(cwxProject::isSuperAdmin() || (
						current_user_can('cwxproj-edit-own-projects') &&
						current_user_can('cwxproj-trash-own-projects') &&
						current_user_can('cwxproj-add-tasks') &&
						current_user_can('cwxproj-edit-own-tasks') &&
						current_user_can('cwxproj-trash-own-tasks')
					)){
						$is_private = true;
					}
				}
				
				// Create the project in DB
				$wpdb->insert(
					$this->_dbt,
					array(
						'title'			=> $project_title,
						'creator_id'	=> $user_id,
						'date_created'	=> $tm,
						'date_updated'	=> $tm,
						'is_private'	=> $is_private
					),
					array('%s', '%d', '%d', '%d', '%d')
				);
				
				// Get new Project by its id
				$id = $wpdb->insert_id;
				if(($row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->_dbt} WHERE id=%d", $id))))
					$project = $row;
				else
					return;
			}
			// Get existing project or bail
			else if(is_numeric($project_id)){
				$query = "SELECT * "
						."FROM {$this->_dbt} "
						."WHERE is_trash=0 AND parent_id IS NULL "
							."AND (is_private=0 OR (is_private=1 AND creator_id=%d)) "
							."AND id=%d";

				if(($row = $wpdb->get_row($wpdb->prepare($query, $user_id, $project_id))))
					$project = $row;
				else
					return;
			}
			// Cannot fulfill the request because no project available
			else {
				return;
			}
			
			// We have a project now, to which we can add the task			
			$data =	array(
				'parent_id'		=> (int) $project->id,
				'title'			=> $title,
				't_post_id'		=> $post_id,
				'creator_id'	=> $user_id,
				'date_created'	=> $tm,
				'date_updated'	=> $tm,
				'priority'		=> 5,
				'is_private'	=> false
			);
			$format = array('%d', '%s', '%d', '%d', '%d', '%d');

			// Do we want to self-assign the task?
			if(isset($_REQUEST['cwx_task_selfassign'])){
				$data['t_assignee'] = $user_id;
				$format[] = '%d';
			}
			
			$wpdb->insert($this->_dbt, $data, $format);

			// Get new Task id and add as post meta 
			$id = (int) $wpdb->insert_id;
			
			if(!add_post_meta($post_id, 'cwx-project-taskid', $id, true))
				update_post_meta($post_id, 'cwx-project-taskid', $id);
		}
		// Update Task Data
		else {
			// We need the task id
			if(!isset($_REQUEST['cwx_task_id']))
				return;
			$id = (int) $_REQUEST['cwx_task_id'];

			if($id !== $task_id)
				return;

			// Update Task, using code in core 
			cwxProject::$core->updateTaskData($task_id, $post_id, $title);
		}
		// Note: Deletion is handled in core, trashTask() and others
	}

	public function metaboxHelp()
	{
		// Get enabled post types
		if(($post_types = cwxProject::getConfig('enabled_post_types')) === false)
			$post_types = array();

		$screen = get_current_screen();

		if(!in_array($screen->post_type, $post_types))
			return;
		
		$screen->add_help_tab(array( 
			'id' => 'cwx-project-metabox-help',
			'title' => sprintf('CWX Project - %s', __('Task', 'cwx-project')),
			'content' => '',
			'callback' => array('cwxProjectHelp', 'printMetaboxHelp')
		));
		
		$screen->set_help_sidebar(
			$screen->get_help_sidebar() . cwxProjectHelp::getMetaboxHelpSidebar()
		);
	}
	
	public function metaboxDisplay()
	{
		include(CWXPRJ__DIR . 'cwx-project-tmpl-metabox.php');
	}
	
	public function metaboxCreate()
	{
		// Get enabled post types
		if(($post_types = cwxProject::getConfig('enabled_post_types')) === false)
			$post_types = array();
			
		$available = cwxProject::getPostTypes();
			
		foreach($post_types as $key => $type){
			if(!in_array($type, $available))
				continue;
			
			add_meta_box(
				'cwx-project-metabox', 
				sprintf('CWX Project - %s', __('Task', 'cwx-project')),
				array($this, 'metaboxDisplay'), 
				$type, 
				'side', 
				'core', 
				null 
			);
		}
	}
	
}

