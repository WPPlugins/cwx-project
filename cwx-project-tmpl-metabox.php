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
?>
<?php
	global $post;

	$task_id = (int) get_post_meta($post->ID, 'cwx-project-taskid', true);
	$post_type = get_post_type_object(get_post_type($post->ID));
?>
<div id="cwx-project-mbx">
<div class="cwx-project-widget cwx-task-data">
<?php if(empty($task_id)) : ?>

	<label for="cwx-task-project" style="width: auto;"><strong><?php 
		printf(
			__('Create a Task for this %s in Project:', 'cwx-project'), 
			$post_type->labels->singular_name
		); 
	?></strong></label>
	<select id="cwx-task-project" name="cwx_task_project" size="1" style="width: 100%; margin-top: 8px;" />
		<option value=""><?php _e('Do not create a Task', 'cwx-project'); ?></option>
		<?php if(current_user_can('cwxproj-add-projects')) : ?>
		<option value="new" class="hide-if-no-js"><?php _e('Create new Project', 'cwx-project'); ?></option>
		<?php endif; ?>
		<optgroup label="<?php _e('Available Projects', 'cwx-project'); ?>">
			<?php $this->printProjectsAsOptions(); ?>
		</optgroup>
	</select>
	<?php if(current_user_can('cwxproj-add-projects')) : ?>
	<div id="cwx-task-new-project" style="display: none;">
		<label class="screen-reader-text"><?php _e('Title of the new Project', 'cwx-project'); ?></label>
		<input type="text" name="cwx_task_new_project" value="" placeholder="<?php _e('Project Title', 'cwx-project'); ?>" style="width: 100%;" />
		
		<?php if(cwxProject::isSuperAdmin() || (
				current_user_can('cwxproj-edit-own-projects') &&
				current_user_can('cwxproj-trash-own-projects') &&
				current_user_can('cwxproj-add-tasks') &&
				current_user_can('cwxproj-edit-own-tasks') &&
				current_user_can('cwxproj-trash-own-tasks')
			)) :
		?>
		<label style="width: auto;">
			<input type="checkbox" name="cwx_task_project_private" />
			<?php _e('Create as Private', 'cwx-project'); ?>
		</label>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<?php if(current_user_can('cwxproj-self-assign')) : ?>
	<div id="cwx-selfassing-option" style="display: none; margin-top: 16px;">
		<label style="width: auto;">
			<input type="checkbox" name="cwx_task_selfassign" />&nbsp;
			<?php _e('Assign myself to the Task', 'cwx-project'); ?>
		</label>
	</div>
	<?php endif; ?>
<?php else :
	$this->metaboxPrintTask($task_id);
?>

	<datalist id="cwx-assignable-users">
		<select style="display: none">
		<?php if(cwxProject::userCan('cwxproj-edit-own-assignment', 'cwxproj-edit-other-assignment')) : ?>
		<?php foreach(cwxProject::$assignable_users as $item) : ?>
			<option value="<?php echo esc_attr($item->name); ?>"><?php echo esc_attr($item->name); ?></option>
		<?php endforeach; ?>
		<?php endif; ?>
		</select>
	</datalist>
	<datalist id="cwx-categories" data-meta-type="category">
		<select style="display: none" data-meta-type="category">
		<?php foreach(cwxProject::$categories as $item) : ?>
			<option value="<?php echo esc_attr($item->title); ?>"><?php echo esc_attr($item->title); ?></option>
		<?php endforeach; ?>
		</select>
	</datalist>
	<datalist id="cwx-statuses" data-meta-type="status">
		<select style="display: none" data-meta-type="status">
		<?php foreach(cwxProject::$statuses as $item) : ?>
			<option value="<?php echo esc_attr($item->title); ?>"><?php echo esc_attr($item->title); ?></option>
		<?php endforeach; ?>
		</select>
	</datalist>
<?php endif; ?>
<input type="hidden" name="_project_nonce" value="<?php echo wp_create_nonce('_project_task'); ?>" />
</div><!-- .cwx-project-widget -->
</div><!-- #cwx-project-mbx -->

