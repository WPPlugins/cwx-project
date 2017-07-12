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

// Make sure we don't expose any info if called directly
defined( 'ABSPATH' ) || exit;


class cwxProjectCore
{

	private $_dbt;
	private $_dbmeta;
	private $_dbusers;


	public function __construct($dbt, $dbt_meta, $dbt_users)
	{
		// DB table name
		$this->_dbt = $dbt;
		$this->_dbmeta = $dbt_meta;
		$this->_dbusers = $dbt_users;
		
		// Set up hooks
		add_action('wp_ajax_cwx_task_list', array($this, 'listTasks'));
		add_action('wp_ajax_cwx_task_edit', array($this, 'editTask'));
		add_action('wp_ajax_cwx_task_save', array($this, 'saveTaskData'));
		add_action('wp_ajax_cwx_task_add', array($this, 'addTask'));
		add_action('wp_ajax_cwx_task_trash', array($this, 'trashTask'));

		add_action('wp_ajax_cwx_project_list', array($this, 'listProjects'));
		add_action('wp_ajax_cwx_project_edit', array($this, 'editProject'));
		add_action('wp_ajax_cwx_project_trash', array($this, 'trashProject'));
		
		add_action('wp_ajax_cwx_project_add_meta', array($this, 'addMetaEntry'));
		add_action('wp_ajax_cwx_project_save_settings', array($this, 'saveSettings'));
		add_action('wp_ajax_cwx_project_trash_list', array($this, 'listTrashCan'));
		add_action('wp_ajax_cwx_project_trash_action', array($this, 'updateTrashCan'));
		
		// Posts
		add_action('wp_trash_post', array($this, 'trashPostTask'));
		add_action('untrashed_post', array($this, 'untrashPostTask'));
		add_action('before_delete_post', array($this, 'deletePostTask'));
	}
	
	
	/**
	 * Task Handlers
	 */
	 
	public function printTask($id)
	{
		global $wpdb;
		
		if(!is_int($id))
			return;

		$query = $wpdb->prepare(
			"SELECT p.*, u.user_nicename, u.display_name "
			."FROM {$this->_dbt} AS p "
			."JOIN {$this->_dbusers} AS u ON (p.creator_id=u.ID) "
			."WHERE p.id=%d", 
			$id
		);
			
		if(($row = $wpdb->get_row($query))){
			$perms = $this->_getTaskPerms((int) $row->creator_id, (int) $row->t_assignee);
			$this->_prepareRow($row);

			print(strtr(
				cwxProjectTemplates::printTaskForm($row, $perms),
				$this->_getTaskL10n()
			));
		}
	}

	public function listTasks()
	{
		global $wpdb;

		try {
			if(empty($_REQUEST['id']))
				throw new Exception(__('Invalid request.', 'cwx-project'));
			$id = (int) $_REQUEST['id'];
			
			$query = $wpdb->prepare(
				"SELECT p.*, u.user_nicename, u.display_name "
				."FROM {$this->_dbt} AS p "
				."JOIN {$this->_dbusers} AS u ON (p.creator_id=u.ID) "
				."WHERE is_trash=0 AND parent_id=%d "
					."AND (p.is_private=0 OR (p.is_private=1 AND p.creator_id=%d)) "
				."ORDER BY p.is_done, p.priority DESC, p.title",
				$id, get_current_user_id()
			);

			if(!($rows = $wpdb->get_results($query)))
				throw new Exception('empty');

			foreach($rows as $row){
				$perms = $this->_getTaskPerms((int) $row->creator_id, (int) $row->t_assignee);
				$this->_prepareRow($row);
				
				print(strtr(
					str_replace(
						'%%task-form%%',
						cwxProjectTemplates::printTaskForm($row, $perms),
						cwxProjectTemplates::printTask($row, $perms)
					),
					$this->_getTaskL10n()
				));
			}
		}
		catch(Exception $e){
			print('message::' . $e->getMessage());
		}
			
		exit;
	}
	
	public function editTask()
	{
		global $wpdb;
		
		try {
			// We need the task id
			if(!isset($_REQUEST['cwx_task_id']))
				throw new Exception(__('Invalid request.', 'cwx-project'));
			$id = (int) $_REQUEST['cwx_task_id'];
			
			// Check nonce
			if(!isset($_REQUEST['_nonce']) || !wp_verify_nonce($_REQUEST['_nonce'], 'task-nonce-'.$id))
				throw new Exception(__('Invalid request.', 'cwx-project'));

			// Check if the task exists
			$row = $wpdb->get_row($wpdb->prepare(
				"SELECT creator_id, t_assignee, priority FROM {$this->_dbt} WHERE id=%d", $id
			));
			if(!$row)
				throw new Exception(__('Attempt to modify an unknown task! Update failed.', 'cwx-project'));

			// Check Permissions
			$user_id = get_current_user_id();
			if($row->creator_id == $user_id || $row->t_assignee == $user_id)
				$this->_verifyCapability('cwxproj-edit-own-tasks');
			else
				$this->_verifyCapability('cwxproj-edit-other-tasks');

			// Sanitize task title
			if(empty($_REQUEST['cwx_task_title']))
				throw new Exception(__('The tasks title must not be empty! Update failed.', 'cwx-project'));

			$title = sanitize_text_field($_REQUEST['cwx_task_title']);

			// Sanitize priority
			$priority = (int) $row->priority;
			if(isset($_REQUEST['cwx_task_priority'])){
				$priority = (int) $_REQUEST['cwx_task_priority'];
				if($priority < 1)
					$priority = 1;
				else if($priority > 9)
					$priority = 9;
			}
			
			// Update the task in DB
			$wpdb->update(
				$this->_dbt,
				array(
					'date_updated'	=> time(),
					'title' 		=> $title,
					'priority' 		=> $priority,
					'is_private'	=> false
				),
				array('id' => $id),
				array('%d', '%s', '%d', '%d'),
				array('%d')
			);

			$result = array();
			
			$result['messages'] = sprintf(
				__("The task '%s' has been updated.", 'cwx-project'), $title
			);

			$row = $wpdb->get_row($wpdb->prepare(
				"SELECT * FROM {$this->_dbt} WHERE id=%d", $id
			));
			$this->_prepareRow($row);
			$result['data'] = $row;
			
			die(json_encode($result));
		}
		catch(Exception $e){
			die(json_encode(array('error' => $e->getMessage())));
		}
	}
	
	public function saveTaskData()
	{
		global $wpdb;
		
		try {
			// We need the task id
			if(!isset($_REQUEST['cwx_task_id']))
				throw new Exception(__('Invalid request.', 'cwx-project'));
			$id = (int) $_REQUEST['cwx_task_id'];
			
			// Check nonce
			if(!isset($_REQUEST['_nonce']) || !wp_verify_nonce($_REQUEST['_nonce'], 'task-nonce-'.$id))
				throw new Exception(__('Invalid request.', 'cwx-project'));

			$meta = $this->updateTaskData($id);

			$result = array();
			
			$result['messages'] = __("The task has been updated.", 'cwx-project');

			$row = $wpdb->get_row($wpdb->prepare(
				"SELECT * FROM {$this->_dbt} WHERE id=%d", $id
			));
			$this->_prepareRow($row);
			$result['data'] = $row;
			$result['data']->meta = $meta;
			
			die(json_encode($result));
		}
		catch(Exception $e){
			die(json_encode(array('error' => $e->getMessage())));
		}
	}
	
	public function updateTaskData($id, $post_id = null, $task_title = null)
	{
		global $wpdb;
		
		try {
			// Check if the task exists
			$row = $wpdb->get_row($wpdb->prepare(
				"SELECT creator_id, t_assignee FROM {$this->_dbt} WHERE id=%d", $id
			));
			if(!$row)
				throw new Exception(__('Attempt to modify an unknown task! Update failed.', 'cwx-project'));

			// Check Permissions
			$user_id = get_current_user_id();
			if($row->creator_id == $user_id || $row->t_assignee == $user_id)
				$this->_verifyCapability('cwxproj-edit-own-tasks');
			else
				$this->_verifyCapability('cwxproj-edit-other-tasks');

			// Sanitize and prepare data
			$data = $format = array();
			
			// Assignee
			if(isset($_REQUEST['cwx_task_assignee'])){
				$assignee = trim(sanitize_text_field($_REQUEST['cwx_task_assignee']));
				$data['t_assignee'] = cwxProject::getAssigneeIdByName($assignee);
				if($data['t_assignee'] !== null)
					 $format[] = '%d';
				else
					$format[] = null;
			}
			
			// Due Date
			if(isset($_REQUEST['cwx_task_datedue'])){
				$raw = sanitize_text_field($_REQUEST['cwx_task_datedue']);
				/* PHP 5.3+ only
				if(($date = DateTime::createFromFormat(cwxProject::$dateFormatIn, $raw)) !== false){
					$date->setTime(0, 0, 0);
					$data['t_date_due'] = (int) $date->getTimestamp();
					$format[] = '%d';
				}
				*/
				// For PHP 5.2.x, not as flexible and safe as above code
				if(($date = date_create($raw)) !== false){
					$date->setTime(0, 0, 0);
					$data['t_date_due'] = (int) $date->format('U');
					$format[] = '%d';
				}
				else {
					$data['t_date_due'] = null;
					$format[] = null;
				}
			}

			// Progress
			if(isset($_REQUEST['cwx_task_progress'])){
				$data['progress'] = (int) $_REQUEST['cwx_task_progress'];
				if($data['progress'] > 100)
					$data['progress'] = 100;
				else if($data['progress'] < 0)
					$data['progress'] = 0;
				$format[] = '%d';
				
				$data['is_done'] = ($data['progress'] == 100)? 1: 0;
				$format[] = '%d';
			}
			
			// Time Spent
			$time = 0;
			if(isset($_REQUEST['cwx_task_timespent_d']))
				$time += ((int) $_REQUEST['cwx_task_timespent_d'] * 1440);
			if(isset($_REQUEST['cwx_task_timespent_h']))
				$time += ((int) $_REQUEST['cwx_task_timespent_h'] * 60);
			if(isset($_REQUEST['cwx_task_timespent_m']))
				$time += (int) $_REQUEST['cwx_task_timespent_m'];
			$data['t_time_spent'] = $time;
			$format[] = '%d';

			// Store meta changes
			$meta = array('status' => array(), 'category' => array());

			// Status (convert to id, create if needed)
			if(isset($_REQUEST['cwx_task_status'])){
				$tmp = sanitize_text_field($_REQUEST['cwx_task_status']);
				
				if(!empty($tmp)){
					$meta['status'] = $this->_maybeAddMeta('status', $tmp);
					$data['t_status'] = (int) $meta['status']['id'];
					$format[] = '%d';
				}
				else {
					$data['t_status'] = null;
					$format[] = null;
				}
			}

			// Category (convert to id, create if needed)
			if(isset($_REQUEST['cwx_task_category'])){
				$tmp = sanitize_text_field($_REQUEST['cwx_task_category']);

				if(!empty($tmp)){
					$meta['category'] = $this->_maybeAddMeta('category', $tmp);
					$data['t_category'] = (int) $meta['category']['id'];
					$format[] = '%d';
				}
				else {
					$data['t_category'] = null;
					$format[] = null;
				}
			}

			// Description
			if(isset($_REQUEST['cwx_task_description'])){
				$raw = str_replace(
					array("\n", "\t"),
					array('%%NL%%', '%%TAB%%'),
					$_REQUEST['cwx_task_description']
				);
				$data['t_description'] = str_replace(
					array('%%NL%%', '%%TAB%%'),
					array("\n", "\t"),
					sanitize_text_field($raw)
				);
				if(!empty($data['t_description'])){
					$format[] = '%s';
				}
				else {
					$data['t_description'] = null;
					$format[] = null;
				}
			}

			// Associated Post ('cwx-project-taskid' is handled in dashboard source)
			$data['t_post_id'] = $post_id;
			if($post_id !== null)
				$format[] = '%d';
			else
				$format[] = null;
				
			// Associated Post Title + post type
			if($task_title !== null){
				$data['title'] = $task_title;
				$format[] = '%s';
			}
				
			// Update Date
			$data['date_updated'] = time();
			$format[] = '%d';
			
			// Update the task in DB with our fixed 'update' method
			$this->_dbUpdate(
				$this->_dbt,
				$data,
				array('id' => $id),
				$format,
				array('%d')
			);
			
			return $meta;
		}
		catch(Exception $e){
			throw $e;
		}
	}

	public function trashTask()
	{
		global $wpdb;

		try {
			// Check Task ID
			if(empty($_REQUEST['id']))
				throw new Exception(__('Invalid request.', 'cwx-project'));
			$id = (int) $_REQUEST['id'];

			// Check nonce
			if(!isset($_REQUEST['_nonce']) || !wp_verify_nonce($_REQUEST['_nonce'], 'task-nonce-'.$id))
				throw new Exception(__('Invalid request.', 'cwx-project'));

			// Check if the task exists
			$row = $wpdb->get_row($wpdb->prepare(
				"SELECT creator_id, t_assignee, t_post_id FROM {$this->_dbt} WHERE id=%d", $id
			));
			if(!$row)
				throw new Exception(__('Attempt to move an unknown task to trash!', 'cwx-project'));
			
			// Check user capabilities
			$this->_verifyMoreCapabilities(
				$row->creator_id,
				'cwxproj-trash-own-tasks', 
				'cwxproj-trash-other-tasks'
			);
			
			// Update associated Post
			if($row->t_post_id)
				delete_post_meta($row->t_post_id, 'cwx-project-taskid', $id);
		
			$wpdb->update(
				$this->_dbt,
				array(
					'is_trash' => 1,
					'date_updated' => time()
				),
				array('id' => $id),
				array('%d', '%d'),
				array('%d')
			);
			
			die(json_encode(array('success' => $id)));
		}
		catch(Exception $e){
			die(json_encode(array('error' => $e->getMessage())));
		}
	}

	public function addTask()
	{
		global $wpdb;

		try {
			// Check user capabilities
			$this->_verifyCapability('cwxproj-add-tasks');

			// Check project ID
			if(empty($_REQUEST['project_id']))
				throw new Exception(__('Invalid request.', 'cwx-project'));
			$pid = (int) $_REQUEST['project_id'];

			// Check nonce
			if(!isset($_REQUEST['_nonce']) || !wp_verify_nonce($_REQUEST['_nonce'], 'project-nonce-'.$pid))
				throw new Exception(__('Invalid request.', 'cwx-project'));

			// Create the task in DB
			$tm = time();
			$wpdb->insert(
				$this->_dbt,
				array(
					'parent_id'		=> $pid,
					'title'			=> __('New Task', 'cwx-project'),
					'creator_id'	=> get_current_user_id(),
					'date_created'	=> $tm,
					'date_updated'	=> $tm,
					'priority'		=> 5,
					'is_private'	=> true
				),
				array('%d', '%s', '%d', '%d', '%d', '%d', '%d')
			);
			
			// Response
			$id = $wpdb->insert_id;
			$row = $this->_getEmptyRow($id, $pid, $tm, __('New Task', 'cwx-project'));
			$perms = $this->_getTaskPerms((int) $row->creator_id, (int) $row->t_assignee);
			$result['message'] = __("A new task has been created.", 'cwx-project');
			$result['template'] = strtr(
				// Compile Template
				str_replace(
					'%%task-form%%',
					cwxProjectTemplates::printTaskForm($row, $perms),
					cwxProjectTemplates::printTask($row, $perms)
				),
				$this->_getTaskL10n()
			);

			die(json_encode($result));
		}
		catch(Exception $e){
			die(json_encode(array('error' => $e->getMessage())));
		}
	}

	private function _getTaskL10n()
	{
		return array(
			// Task row
			'%%<task>%%'		=> __('Task', 'cwx-project'),
			'%%<priority>%%'	=> __('Priority', 'cwx-project'),
			// Task form
			// Labels
			'%%<created-by>%%'	=> __('Created by', 'cwx-project'), // %3$s
			'%%<created-on>%%'	=> __('Created on', 'cwx-project'),
			'%%<updated-on>%%'	=> __('Updated on', 'cwx-project'),
			'%%<assignee>%%'	=> __('Assignee', 'cwx-project'),
			'%%<due-date>%%'	=> __('Due Date', 'cwx-project'),
			'%%<progress>%%'	=> __('Progress', 'cwx-project'),
			'%%<time-spent>%%'	=> __('Time Spent', 'cwx-project'),
			'%%<status>%%'		=> __('Status', 'cwx-project'),
			'%%<category>%%'	=> __('Category', 'cwx-project'),
			'%%<description>%%'	=> __('Description', 'cwx-project'), // %12$s
			// End Labels
			'%%<date-format>%%'			=> sprintf(__('Date Format: %s', 'cwx-project'), cwxProject::$dateFormat),
			'%%<progress-unit>%%'		=> __('Progress for this task in percent', 'cwx-project'),
			'%%<time-spent-format>%%'	=> __('Days : Hours : Minutes', 'cwx-project')
		);
	}
	
	
	/**
	 * Project Handlers
	 */

	public function getProjects()
	{
		global $wpdb;

		$user_id = (int) get_current_user_id();
		if(!$user_id)
			return false;
		
		// What is this for? If we want a single Project, we need a different query
		$query = "SELECT p.*, "
					."COUNT(t.parent_id) AS num_tasks, "
					."SUM(t.progress) AS total_progress, "
					."MIN(t.t_date_due) AS date_due "
				."FROM {$this->_dbt} AS p "
				."LEFT JOIN {$this->_dbt} AS t ON (t.parent_id=p.id AND t.is_trash=0) "
				."WHERE p.is_trash=0 AND p.parent_id IS NULL "
					."AND (p.is_private=0 OR (p.is_private=1 AND p.creator_id={$user_id})) "
				."GROUP BY p.id, t.parent_id "
				."ORDER BY p.is_done, p.title";

		return $wpdb->get_results($query);
	}

	public function listProjects()
	{
		global $wpdb;

		try {
			$user_id = (int) get_current_user_id();
			if(!$user_id)
				throw new Exception(__('Invalid request.', 'cwx-project'));
			
			$query = "SELECT p.*, "
						."COUNT(t.parent_id) AS num_tasks, "
						."SUM(t.progress) AS total_progress, "
						."MIN(t.t_date_due) AS date_due "
					."FROM {$this->_dbt} AS p "
					."LEFT JOIN {$this->_dbt} AS t ON (t.parent_id=p.id AND t.is_trash=0) "
					."WHERE p.is_trash=0 AND p.parent_id IS NULL "
						."AND (p.is_private=0 OR (p.is_private=1 AND p.creator_id={$user_id})) "
					."GROUP BY p.id, t.parent_id "
				."ORDER BY p.is_done, p.title";

			if(!($rows = $wpdb->get_results($query)))
				throw new Exception('empty');

			// HTML output
			foreach($rows as $row){
				// Compute and set some stuff for projects
				$row->num_tasks = (int) $row->num_tasks;
				if($row->total_progress && $row->num_tasks)
					$row->progress = (float) ($row->total_progress / $row->num_tasks);
				if($row->date_due)
					$row->t_date_due = (int) $row->date_due;

				// Clean up unwanted data	
				unset($row->total_progress);
				unset($row->date_due);
				
				// Output
				$perms = $this->_getProjectPerms((int) $row->creator_id);
				$this->_prepareRow($row);
				print(strtr(
					cwxProjectTemplates::printProject($row, $perms),
					$this->_getProjectL10n()
				));
			}
		}
		catch(Exception $e){
			// Plain text output
			print('message::' . $e->getMessage());
		}
		
		exit;
	}

	public function trashProject()
	{
		global $wpdb;

		try {
			// Check ID
			if(empty($_REQUEST['id']))
				throw new Exception(__('Invalid request.', 'cwx-project'));
			$id = (int) $_REQUEST['id'];

			// Check nonce
			if(!isset($_REQUEST['_nonce']) || !wp_verify_nonce($_REQUEST['_nonce'], 'project-nonce-'.$id))
				throw new Exception(__('Invalid request.', 'cwx-project'));

			// Check if the project exists
			$row = $wpdb->get_row($wpdb->prepare(
				"SELECT creator_id FROM {$this->_dbt} WHERE id=%d", $id
			));
			if(!$row)
				throw new Exception(__('Attempt to move an unknown project to trash!', 'cwx-project'));
			
			// Check user capabilities
			$this->_verifyMoreCapabilities(
				$row->creator_id, 
				'cwxproj-trash-own-projects', 
				'cwxproj-trash-other-projects'
			);
		
			// Mark project and its tasks as trashed
			$wpdb->query($wpdb->prepare(
				 "UPDATE {$this->_dbt} SET "
				."is_trash=1, date_updated=%d "
				."WHERE (id=%d AND parent_id IS NULL) OR parent_id=%d",
				time(), $id, $id
			));
			
			die(json_encode(array('success' => $id)));
		}
		catch(Exception $e){
			die(json_encode(array('error' => $e->getMessage())));
		}
	}
	
	public function editProject()
	{
		global $wpdb;

		try {
			// We need a project id, 0 for new project
			if(!isset($_REQUEST['cwx_project_id']))
				throw new Exception(__('Invalid request.', 'cwx-project'));
			$id = (int) $_REQUEST['cwx_project_id'];
			
			// Check nonce
			$sid = (!$id)? 'new': $id;
			if(!isset($_REQUEST['_nonce']) || !wp_verify_nonce($_REQUEST['_nonce'], 'project-nonce-'.$sid))
				throw new Exception(__('Invalid request.', 'cwx-project'));

			$result = array();
			// Sanitize is_private field
			$is_private = false;
			if(isset($_REQUEST['cwx_project_is_private'])){
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
				
			// Time
			$tm = time();
			
			// Add New Project
			if(!$id){
				// Check user capabilities
				$this->_verifyCapability('cwxproj-add-projects');

				// Sanitize project title
				if(empty($_REQUEST['cwx_project_title']))
					throw new Exception(__('You must enter a title for the new project!', 'cwx-project'));

				$title = sanitize_text_field(trim($_REQUEST['cwx_project_title']));
				
				// Create the project in DB
				$wpdb->insert(
					$this->_dbt,
					array(
						'title'			=> $title,
						'creator_id'	=> get_current_user_id(),
						'date_created'	=> $tm,
						'date_updated'	=> $tm,
						'is_private'	=> $is_private
					),
					array('%s', '%d', '%d', '%d')
				);
				
				// New Project Response part
				$id = $wpdb->insert_id;
				$row = $this->_getEmptyRow($id, null, $tm, $title);
				$perms = $this->_getProjectPerms((int) $row->creator_id);
				$result['messages'] = sprintf(
					__("The new project '%s' has been created.", 'cwx-project'), $title
				);
				$result['template'] = strtr(
					cwxProjectTemplates::printProject($row, $perms),
					$this->_getProjectL10n()
				);
			}
			// Update Project
			else {
				// Check if the project exists
				$row = $wpdb->get_row($wpdb->prepare(
					"SELECT creator_id, t_assignee FROM {$this->_dbt} WHERE id=%d", $id
				));
				if(!$row)
					throw new Exception(__('Attempt to modify an unknown project! Update failed.', 'cwx-project'));
				
				// Check user capabilities
				$this->_verifyMoreCapabilities(
					$row->creator_id, 
					'cwxproj-edit-own-projects', 
					'cwxproj-edit-other-projects'
				);

				// Sanitize project title
				if(empty($_REQUEST['cwx_project_title']))
					throw new Exception(__('The projects title must not be empty! Update failed.', 'cwx-project'));

				$title = sanitize_text_field(trim($_REQUEST['cwx_project_title']));
				
				// Update the project in DB
				$wpdb->update(
					$this->_dbt,
					array(
						'date_updated'	=> $tm,
						'title' 		=> $title,
						'is_private'	=> $is_private
					),
					array('id' => $id),
					array('%d', '%s', '%d'),
					array('%d')
				);
				
				// Update Project Response part
				$result['messages'] = sprintf(
					__("The project '%s' has been updated.", 'cwx-project'), $title
				);
			}

			// Finish Response
			$row = $wpdb->get_row($wpdb->prepare(
				"SELECT * FROM {$this->_dbt} WHERE id=%d", $id
			));
			$this->_prepareRow($row);
			$result['data'] = $row;
			
			die(json_encode($result));
		}
		catch(Exception $e){
			// Error Response
			die(json_encode(array('error' => $e->getMessage())));
		}
	}

	private function _getProjectL10n()
	{
		return array(
			'%%<project>%%'		=> __('Project', 'cwx-project'),
			'%%<loading>%%'		=> __('Loading', 'cwx-project'),
			'%%<no-tasks>%%'	=> sprintf(
				"<p><strong>%s</strong><br /><em>%s</em></p>\n",
				__('No Tasks found', 'cwx-project'),
				__('Add new Tasks or adjust the filters.', 'cwx-project')
			)
		);
	}


	/**
	 * Widget Trash Can
	 */
	
	public function listTrashCan()
	{
		global $wpdb;
		
		try {
			self::_verifyCapability('cwxproj-trashcan');
			
			$query = "SELECT id, parent_id, is_trash, title, date_updated "
					."FROM {$this->_dbt} "
					."WHERE is_trash=1 OR (is_trash=0 AND parent_id IS NULL) "
					."ORDER BY parent_id, title, date_updated DESC";

			if(!($rows = $wpdb->get_results($query)))
				throw new Exception('empty');
			
			// Prepare
			$projects = array();
			foreach($rows as $row){
				$row->id = (int) $row->id;
				$row->parent_id = ($row->parent_id)? (int) $row->parent_id: null;
				$row->is_trash = (int) $row->is_trash;
				$row->title = esc_attr($row->title);
				$row->date_updated = (int) $row->date_updated;
				$row->updated = cwxProject::getFormattedDate($row->date_updated, cwxProject::$dateFormatOut);
				$row->updated_dt = sprintf(
					'Last updated on %s',
					cwxProject::getFormattedDate($row->date_updated, cwxProject::$timeFormat)
				);
				
				// Projects
				if($row->parent_id === null){
					$row->tasks = array();
					$projects[$row->id] = $row;
				}
				// Tasks
				else {
					$projects[$row->parent_id]->tasks[$row->id] = $row;
				}
			}

			// HTML output
			foreach($projects as $proj){
				if($proj->is_trash || !empty($proj->tasks)){
					cwxProjectTemplates::printTrashRow($proj);
					
					foreach($proj->tasks as $task){
						cwxProjectTemplates::printTrashRow($task);
					}
				}
			}
		}
		catch(Exception $e){
			// Plain text output
			print('message::' . $e->getMessage());
		}
		
		exit;
	}
	
	public function updateTrashCan()
	{
		global $wpdb;
		
		try {
			self::_verifyCapability('cwxproj-trashcan');

			if(!isset($_REQUEST['_project_nonce']) || !wp_verify_nonce($_REQUEST['_project_nonce'], '_project_trash'))
				throw new Exception(__('Invalid request.', 'cwx-project'));
			
			// Sanitize incoming
			$del_projects = $projects = $tasks = array();
			if(!empty($_REQUEST['sel_project'])){
				foreach($_REQUEST['sel_project'] as $key => $project_id){
					$project_id = (int) sanitize_text_field($project_id);
					if($project_id){
						$projects[$project_id] = esc_sql($project_id);
						$del_projects[$project_id] = esc_sql($project_id);
					}
				}
			}
			if(!empty($_REQUEST['sel_task'])){
				foreach($_REQUEST['sel_task'] as $project_id => $tasklist){
					$project_id = (int) sanitize_text_field($project_id);
					if($project_id && !empty($tasklist)){
						$projects[$project_id] = esc_sql($project_id);
						foreach($tasklist as $key => $task_id){
							$task_id = (int) sanitize_text_field($task_id);
							if($task_id)
								$tasks[$task_id] = esc_sql($task_id);
						}
					}
				}
			}
						
			switch($_REQUEST['bulk_action']){
				case 'restore':
					$del_projects = array();
					// Restore these Projects and Tasks by their IDs
					$ids = implode(',', array_merge($projects, $tasks));
					if(!empty($ids))
						$wpdb->query("UPDATE {$this->_dbt} SET is_trash=0 WHERE id IN ({$ids})");
					break;
				case 'delete':
					$projects = $del_projects;
					// Restore these Projects and Tasks by their IDs
					$ids = implode(',', array_merge($projects, $tasks));
					if(!empty($ids))
						$wpdb->query("DELETE FROM {$this->_dbt} WHERE id IN ({$ids})");
					// Delete Tasks of Deleted Projects
					$ids = implode(',', $del_projects);
					if(!empty($ids))
						$wpdb->query("DELETE FROM {$this->_dbt} WHERE parent_id IN ({$ids})");
					break;
				default:
					throw new Exception(__('Invalid request.', 'cwx-project'));
			}
			
			// Response
			die(json_encode(array(
				'action' => $_REQUEST['bulk_action'],
				'tasks' => $tasks, 
				'projects' => $projects, 
				'more' => $del_projects
			)));
		}
		catch(Exception $e){
			// Error Response
			die(json_encode(array('error' => $e->getMessage())));
		}
		
		exit;
	}
	
	
	/**
	 * Widget Settings
	 */

	public function saveSettings()
	{
		global $wpdb, $wp_roles;

		try {
			$this->_verifyCapability('cwxproj-configure');
			
			// Check nonce
			if(!isset($_REQUEST['widget_id']) || !isset($_REQUEST['dashboard-widget-nonce']))
				throw new Exception(__('Invalid request.', 'cwx-project'));
			$nonce = 'edit-dashboard-widget_' . $_REQUEST['widget_id'];
			if(!wp_verify_nonce($_REQUEST['dashboard-widget-nonce'], $nonce))
				throw new Exception(__('Invalid request.', 'cwx-project'));
				
			$errmsgs = array();
			$result = array('meta' => array(), 'priorities' => array());
			$options = get_option('cwx_project_options');
			
			// Meta data (categories and statuses, skip if not requested)
			if(isset($_REQUEST['cwx_meta_type'])){
				foreach($_REQUEST['cwx_meta_type'] as $id => $type){
					if(empty($type) || !is_numeric($id) || !isset($_REQUEST['cwx_meta_title'][$id]))
						continue;
					
					$id = (int) $id;
					$type = sanitize_text_field($type);
					$title = sanitize_text_field($_REQUEST['cwx_meta_title'][$id]);

					// Check capabilities
					if($type == 'status')
						$this->_verifyCapability('cwxproj-configure-statuses');
					else if($type == 'category')
						$this->_verifyCapability('cwxproj-configure-categories');
					
					$result['meta'][$id] = array('type' => $type);

					// Move tasks first if requested
					if(isset($_REQUEST['cwx_meta_moveto']) && !empty($_REQUEST['cwx_meta_moveto'][$id])){
						// Create new meta entry if required
						$new_title = sanitize_text_field($_REQUEST['cwx_meta_moveto'][$id]);
						$meta = $this->_maybeAddMeta($type, $new_title);
						
						// Update Tasks
						$wpdb->update(
							$this->_dbt,
							array("t_{$type}" => $meta['id']),
							array("t_{$type}" => $id),
							array('%d'),
							array('%d')
						);
						
						$result['meta'][$id]['moved'] = true;
						if($meta['status'] == 'created')
							$result['meta'][$id]['created'] = $meta;
					}
					
					// Delete meta
					if(isset($_REQUEST['cwx_meta_delete']) && isset($_REQUEST['cwx_meta_delete'][$id])){
						$wpdb->query($wpdb->prepare(
							"DELETE FROM {$this->_dbmeta} WHERE id=%d", $id
						));

						$result['meta'][$id]['deleted'] = true;
					}
					
					// Prepare further updates (future version might want to check if title has changed)
					$update = array('title' => $title);
					$format = array('%s');
					
					// Accept proposed meta
					if(isset($_REQUEST['cwx_meta_proposal']) && isset($_REQUEST['cwx_meta_proposal'][$id])){
						$update['creator_id'] = null;
						$format[] = null;

						$result['meta'][$id]['accepted'] = true;
					}

					$result['meta'][$id]['title'] = $title;

					// Final update for the meta entry
					$this->_dbUpdate( // This supports NULL values
						$this->_dbmeta,
						$update,
						array('id' => $id),
						$format,
						array('%d')
					);
				}
			}

			// Post types for Metabox (skip if not requested)
			$this->_verifyCapability('cwxproj-configure-metabox');

			if(isset($_REQUEST['cwx_project_posttype'])){
				$options['enabled_post_types'] = array();
				$post_types = cwxProject::getPostTypes();
					
				foreach($_REQUEST['cwx_project_posttype'] as $val){
					$val = sanitize_text_field($val);
					if(in_array($val, $post_types))
						$options['enabled_post_types'][] = $val;
				}
			}

			// Priority Colors (skip if not requested)
			$this->_verifyCapability('cwxproj-configure-colors');

			if(isset($_REQUEST['cwx_project_priority'])){
				if(!isset($options['priority_colors']))
					$options['priority_colors'] = cwxProject::$priorityColorsDefault;
					
				foreach($_REQUEST['cwx_project_priority'] as $num => $val){
					$num = (int) $num;
					$val = '#' . preg_replace("/[^0-9a-f]/", '', $val);
					
					if($num >= 1 && $num <= 9 && preg_match("/^#([0-9a-f]{3}|[0-9a-f]{6})$/", $val))
						$options['priority_colors'][$num] = $val;
					else
						$errmsgs[] = sprintf(__('Invalid color value for priority %d! Please use HTML colors (#rgb, #rrggbb).', 'cwx-project'), $num);

					$result['priorities'][$num] = $val;
				}
			}

			// Permissions
			$this->_verifyCapability('cwxproj-configure-permissions');
			
			$roles = $wp_roles->role_names;
			$caps = cwxProject::$capabilities;
			
			// Skip permission settings if not requested
			if(isset($_REQUEST['cwx_project_perms'])){
				$assignee_roles = array();

				foreach($roles as $roleid => $rname){
					// Get WP Role 
					$role = get_role($roleid);

					if(isset($_REQUEST['cwx_project_perms'][$roleid])){
						// Got the role from form, set user configured perms
						foreach($caps as $capid => $clabel){
							if(isset($_REQUEST['cwx_project_perms'][$roleid][$capid]))
								$role->add_cap($capid);
							else if($role->has_cap($capid))
								$role->remove_cap($capid);
						}
					}
					else {
						// Role has not been sent, set default perms
						$_default = &cwxProject::getRoleCaps($roleid);
						foreach($caps as $capid => $clabel){
							if(in_array($capid, $_default))
								$role->add_cap($capid);
							else if($role->has_cap($capid))
								$role->remove_cap($capid);
						}
					}
					
					if($role->has_cap('cwxproj-edit-assigned-tasks'))
						$assignee_roles[] = $roleid;
				}
				
				$options['assignee_roles'] = $assignee_roles;
			}			

			// Update our options
			update_option('cwx_project_options', $options);

			// If we have errors, respond with them
			if(!empty($errmsgs))
				die(json_encode(array('error' => $errmsgs, 'result' => $result)));
			
			// Respond with success string
			die(json_encode(array('result' => $result)));
		}
		catch(Exception $e){
			die(json_encode(array('error' => $e->getMessage())));
		}
	}

	public function addMetaEntry()
	{
		try {
			if(!current_user_can('cwxproj-configure'))
				throw new Exception(__('Permission denied!', 'cwx-project'));

			// Check nonce
			if(!wp_verify_nonce($_REQUEST['_nonce'], 'cwx-project-meta'))
				throw new Exception(__('Invalid request.', 'cwx-project'));

			// Sanitize meta type
			if(empty($_REQUEST['cwx_meta_type_n']))
				throw new Exception(__('Invalid meta type!', 'cwx-project'));

			$type = sanitize_text_field($_REQUEST['cwx_meta_type_n']);

			// Check capabilities
			if($type == 'status')
				$this->_verifyCapability('cwxproj-configure-statuses');
			else if($type == 'category')
				$this->_verifyCapability('cwxproj-configure-categories');

			// Sanitize meta title
			if(empty($_REQUEST['cwx_meta_title_n']))
				throw new Exception(__('Name cannot be empty!', 'cwx-project'));

			$title = sanitize_text_field($_REQUEST['cwx_meta_title_n']);

			$result = $this->_maybeAddMeta($type, $title);
			
			// Respond
			die(json_encode(array(
				'status'	=> $result['status'],
				'meta_id'	=> ($result['id'])? esc_attr($result['id']): null,
				'title'		=> esc_attr($result['title'])
			)));
		}
		catch(Exception $e){
			die(json_encode(array('error' => $e->getMessage())));
		}
	}
	
	private function _maybeAddMeta($type, $title)
	{
		global $wpdb;
		
		// Return status
		$status = '';
		$meta_id = null;

		// Prepare meta data for storing
		$data = array(
			'meta_type'		=> esc_sql($type),
			'title'			=> esc_sql($title),
			'creator_id'	=> get_current_user_id()
		);
		$format = array('%s', '%s', '%d');
		
		// Look up existing entry
		$row = $wpdb->get_row($wpdb->prepare(
			"SELECT id, title FROM {$this->_dbmeta} WHERE title=LOWER('%s') AND meta_type='%s'",
			strtolower($title), $type
		));
		
		// Update existing entry (maybe case or something has changed) 
		if($row){
			$meta_id = (int) $row->id;
			
			array_pop($data);
			array_pop($format);
			
			if($title !== $row->title){
				$wpdb->update(
					$this->_dbmeta, 
					$data, 
					array('id' => $meta_id),
					$format,
					array('%d')
				);

				$status = 'updated';
			}
			else {
				$status = 'existed';
			}
		}
		// Insert new meta entry			
		else {			
			if(cwxProject::isSuperAdmin() || current_user_can('cwxproj-configure-statuses') || current_user_can('cwxproj-configure-categories')){
				array_pop($data);
				array_pop($format);
			}

			$wpdb->insert($this->_dbmeta, $data, $format);

			$meta_id = $wpdb->insert_id;
			$status = 'created';
		}
		
		// Update static entry
		$data['id'] = $meta_id;
		$data['parent_id'] = null;
		if($type == 'category')
			cwxProject::$categories[$meta_id] = (object) $data;
		else if($type == 'status')
			cwxProject::$statuses[$meta_id] = (object) $data;
		
		// Return results
		return array('status' => $status, 'id' => $meta_id, 'type' => $type, 'title' => $title);
	}


	/**
	 * Post associated Methods
	 */
		
	public function trashPostTask($post_id)
	{
		global $wpdb;
		
		$id = (int) get_post_meta($post_id, 'cwx-project-taskid', true);
		
		// Check if the task exists and is not in trash
		$row = $wpdb->get_row($wpdb->prepare(
			"SELECT id, creator_id, t_assignee FROM {$this->_dbt} WHERE id=%d AND t_post_id=%d AND is_trash=0", $id, (int) $post_id
		));
		if(!$row)
			return false;
	
		$wpdb->update(
			$this->_dbt,
			array(
				'is_trash' => 1,
				'date_updated' => time()
			),
			array('id' => (int) $row->id),
			array('%d', '%d'),
			array('%d')
		);
		
		return true;
	}
		
	public function untrashPostTask($post_id)
	{
		global $wpdb;
		
		$id = (int) get_post_meta($post_id, 'cwx-project-taskid', true);
		
		// Check if the task exists and is in trash
		$row = $wpdb->get_row($wpdb->prepare(
			"SELECT id, creator_id, t_assignee FROM {$this->_dbt} WHERE id=%d AND t_post_id=%d AND is_trash=1", $id, (int) $post_id
		));
		if(!$row)
			return false;
	
		$wpdb->update(
			$this->_dbt,
			array(
				'is_trash' => 0,
				'date_updated' => time()
			),
			array('id' => (int) $row->id),
			array('%d', '%d'),
			array('%d')
		);
		
		return true;
	}
		
	public function deletePostTask($post_id)
	{
		global $wpdb;
		
		$id = (int) get_post_meta($post_id, 'cwx-project-taskid', true);
		
		// Check if the task exists and is in trash
		$row = $wpdb->get_row($wpdb->prepare(
			"SELECT id, creator_id, t_assignee FROM {$this->_dbt} WHERE id=%d AND t_post_id=%d AND is_trash=1", $id, (int) $post_id
		));
		if(!$row)
			return false;

		$wpdb->delete(
			$this->_dbt,
			array('id' => (int) $row->id),
			array('%d')
		);	
		
		return true;
	}
	
	
	/**
	 * Helper Methods
	 */
	
	private function _getProjectPerms($owner)
	{
		$user_id = get_current_user_id();
		$own_proj = ($owner == $user_id);
		
		if(cwxProject::isSuperAdmin()){
			$add_tasks = $edit_proj = $trash_proj = true;
		}
		else {
			$add_tasks = current_user_can('cwxproj-add-tasks');

			if($own_proj){
				$edit_proj = current_user_can('cwxproj-edit-own-projects');
				$trash_proj = current_user_can('cwxproj-trash-own-projects');
			}
			else {
				$edit_proj = current_user_can('cwxproj-edit-other-projects');
				$trash_proj = current_user_can('cwxproj-trash-other-projects');
			}
		}
		
		return array(
			'add_tasks'		=> $add_tasks,
			'edit_project'	=> $edit_proj,
			'trash_project'	=> $trash_proj
		);
	}
	
	private function _getTaskPerms($owner, $assignee)
	{
		$user_id = get_current_user_id();
		$own_task = ($assignee == $user_id || $owner == $user_id);
		
		if(cwxProject::isSuperAdmin()){
			$edit_task = $trash_task = $self_assign = $edit_assign = $edit_status = $edit_cat = true;
		}
		else if($own_task){
			$edit_task = (current_user_can('cwxproj-edit-own-tasks') || current_user_can('cwxproj-edit-assigned-tasks'));
			$trash_task = current_user_can('cwxproj-trash-own-tasks');
			$self_assign = current_user_can('cwxproj-self-assign');
			$edit_assign = current_user_can('cwxproj-edit-own-assignment');
			$edit_status = current_user_can('cwxproj-edit-own-statuses');
			$edit_cat = current_user_can('cwxproj-edit-own-categories');
		}
		else {
			$edit_task = (current_user_can('cwxproj-edit-other-tasks') || current_user_can('cwxproj-edit-other-assigned-tasks'));
			$trash_task = current_user_can('cwxproj-trash-other-tasks');
			$self_assign = false;
			$edit_assign = current_user_can('cwxproj-edit-other-assignment');
			$edit_status = current_user_can('cwxproj-edit-other-statuses');
			$edit_cat = current_user_can('cwxproj-edit-other-categories');
		}
		
		return array(
			'edit_task'			=> $edit_task,
			'trash_task'		=> $trash_task,
			'self_assign'		=> $self_assign,
			'edit_assignment'	=> $edit_assign,
			'edit_duedate'		=> current_user_can('cwxproj-edit-date-due'),
			'edit_status'		=> $edit_status,
			'edit_category'		=> $edit_cat
		);
	}
	
	public function isUserCapable($owner_id, $cap_own, $cap_other = null)
	{
		if(cwxProject::isSuperAdmin())
			return true;
		else if($cap_other && current_user_can($cap_other))
			return true;
		else if($owner_id == get_current_user_id() && current_user_can($cap_own))
			return true;
		
		return false;
	}
	
	private function _verifyMoreCapabilities($user_id, $cap_own, $cap_other)
	{
		// Check 'own' capability
		if($user_id == get_current_user_id())
			$this->_verifyCapability($cap_own);
		// Check 'other' capability
		else
			$this->_verifyCapability($cap_other);
	}
	
	private function _verifyCapability($cap)
	{
		// Site Admin is capable, also if user has the cap to change permissions
		if(cwxProject::isSuperAdmin() || current_user_can('cwxproj-configure-permissions'))
			return;

		if(!current_user_can($cap))
			throw new Exception(__('Insufficient permissions!', 'cwx-project'));
	}
	
	private function _prepareRow(&$row)
	{
		$row->id = (int) $row->id;
		$row->parent_id = ($row->parent_id)? (int) $row->parent_id: null;
		$row->raw_title = stripslashes($row->title);
		$row->title = esc_attr($row->raw_title);
		$row->creator_id = (int) $row->creator_id;
		$row->date_created = (int) $row->date_created;
		$row->date_updated = (int) $row->date_updated;
		$row->is_private = (bool) $row->is_private;
		$row->is_trash = (bool) $row->is_trash;
		$row->priority = (int) $row->priority;
		$row->progress = (float) $row->progress;
		$row->t_assignee = ($row->t_assignee)? (int) $row->t_assignee: null;
		$row->t_status = ($row->t_status)? (int) $row->t_status: null;
		$row->t_category = ($row->t_category)? (int) $row->t_category: null;
		$row->t_date_due = ($row->t_date_due !== null)? (int) $row->t_date_due: null;
		$row->t_time_spent = (int) $row->t_time_spent;
		$row->t_post_id = (int) $row->t_post_id;
		$row->t_description = (!empty($row->t_description))? esc_html(stripslashes($row->t_description)): null;
		// Just one assignee for now

		// Extra data
		$row->priority_bg = cwxProject::priorityColor($row->priority);
		$row->priority_fg = '#000';
		
		$row->creator = $row->assignee = ''; // __('No one', 'cwx-project');
		
		if(isset($row->display_name))
			$row->creator = esc_attr($row->display_name);

		// Test existence of assignee, might be removed from assignable users
		if($row->t_assignee && isset(cwxProject::$assignable_users[$row->t_assignee]))
			$row->assignee = esc_attr(cwxProject::$assignable_users[$row->t_assignee]->name);
		
		// Maybe use this: $row->min_date = date('Y-m-d', $row->date_updated);
		$row->min_date = date('Y-m-d', $row->date_created);
		$row->created_on = cwxProject::getFormattedDate($row->date_created, cwxProject::$dateFormatOut);
		$row->updated_on = cwxProject::getFormattedDate($row->date_updated, cwxProject::$dateFormatOut);
		$row->created_dt = cwxProject::getFormattedDate($row->date_created, cwxProject::$timeFormat);
		$row->updated_dt = cwxProject::getFormattedDate($row->date_updated, cwxProject::$timeFormat);

		if($row->t_date_due !== null)
			$row->date_due = esc_attr(date(cwxProject::$dateFormatOut, $row->t_date_due));
		else
			$row->date_due = '';
		
		$row->status = $row->category = '';
		if($row->t_status && isset(cwxProject::$statuses[$row->t_status]))
			$row->status = esc_attr(cwxProject::$statuses[$row->t_status]->title);
		if($row->t_category && isset(cwxProject::$categories[$row->t_category]))
			$row->category = esc_attr(cwxProject::$categories[$row->t_category]->title);
		
		$time = $row->t_time_spent;
		$row->time_spent_d = floor($time / 1440); // 1440 = 24 * 60
		$row->time_spent_h = floor(($time - ($row->time_spent_d * 1440)) / 60);
		$row->time_spent_m = floor($time - ($row->time_spent_d * 1440) - ($row->time_spent_h * 60));
	}
	
	private function _getEmptyRow($id = false, $parent_id = null, $tm = null, $title = '')
	{
		$priority = 5;
		if($parent_id === null)
			$priority = 0;
		if($tm === null)
			$tm = time();
			
		$row = new stdClass();
		$row->id = $id;
		$row->parent_id = $parent_id;
		$row->title = $title;
		$row->creator_id = get_current_user_id();
		$row->date_created = $tm;
		$row->date_updated = $tm;
		$row->is_private = false;
		$row->is_trash = false;
		$row->priority = $priority;
		$row->progress = 0;
		$row->t_assignee = null;
		$row->t_status = null;
		$row->t_category = null;
		$row->t_date_due = null;
		$row->t_time_spent = 0;
		$row->t_post_id = null;
		$row->t_description = null;
		
		$user = wp_get_current_user();
		$row->creator = esc_attr($user->display_name);
		$row->display_name = esc_attr($user->display_name);
		
		$this->_prepareRow($row);
		return $row;
	}
	
	// Required to fix NULL values which isn't working in $wpdb->update()
	// NOTE(!): Only a single key/value pair will be used for 'WHERE'!
	private function _dbUpdate($tbl, $data, $where, $format, $whereFormat)
	{
		global $wpdb;
		
		// Some checking
		if(empty($data))
			return;
		if(count($data) !== count($format))
			return;
			
		$idx = 0;
		$sets = array();
		foreach($data as $field => $value){
			$d = '`'.$field.'`='.$this->_dbPrepareFormat($value, $format[$idx]);
			$sets[] = $d;
			$idx++;
		}
		$sets = implode(', ', $sets);
		
		// Just a simple where
		$wKey = array_keys($where);
		$wKey = array_shift($wKey);
		$wFmt = array_shift($whereFormat);
		$wVal = $where[$wKey];
		$wOp = (is_null($where[$wKey]))? ' IS ': '=';
		$where = sprintf("WHERE `%s`%s%s", $wKey, $wOp, $this->_dbPrepareFormat($wVal, $wFmt)); 
		
		$query = "UPDATE {$tbl} SET {$sets} {$where}";
		$wpdb->query($query);
	}
	
	private function _dbPrepareFormat($value, $format)
	{
		$value = esc_sql($value);
		
		if($format === null)
			$value = 'NULL';
		else if($format === '%d')
			$value = (string) $value;
		else if($format === '%f')
			$value = (string) ((float) $value);
		else if($format === '%s')
			$value = "'{$value}'";
			
		return $value;
	}
	
}

