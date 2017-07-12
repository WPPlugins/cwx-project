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


class cwxProjectTemplates
{

	// Prints the Widget 'main' template
	public static function printWidget()
	{
	?>
	<div id="cwx-project-main" class="cwx-project-widget">
		<div class="cwx-message cwx-no-js">
			<p>
				<strong><?php _e('This Widget requires JavaScript', 'cwx-project'); ?></strong><br />
				<em><?php _e('Please enable JavaScript in your browser.', 'cwx-project'); ?></em>
			</p>
		</div>

		<div id="cwx-project-filters">
			<h4><?php _e('Filters', 'cwx-project'); ?></h4>
			<button id="cwx-apply-filters" class="button button-primary"><?php _e('Apply Filters', 'cwx-project'); ?></button>
			<div class="clear"></div>
			<div class="cwx-separator"></div>
			<div class="cwx-floater">
				<label for="cwx-filter-projects"><?php _e('Projects', 'cwx-project'); ?></label>
				<select id="cwx-filter-projects" size="1">
					<option value=""><?php _e('All', 'cwx-project'); ?></option>
					<option value="public"><?php _e('Public', 'cwx-project'); ?></option>
					<option value="private"><?php _e('Private', 'cwx-project'); ?></option>
				</select>
			</div>
			<div class="cwx-floater">
				<div class="cwx-floater">
					<label for="cwx-filter-search"><?php _e('Search', 'cwx-project'); ?></label>
					<input type="text" id="cwx-filter-search" value="" placeholder="<?php _e('Task Title', 'cwx-project'); ?>" size="10" />
				</div>
				<div class="cwx-floater cwx-floater-right">
					<label for="cwx-filter-assignee"><?php _e('Assignee', 'cwx-project'); ?></label>
					<input type="text" id="cwx-filter-assignee" value="" placeholder="<?php _e('Assignee', 'cwx-project'); ?>" list="cwx-assignable-users" size="10" />
				</div>
				<div class="clear"></div>
				<div class="cwx-floater">
					<label for="cwx-filter-status"><?php _e('Status', 'cwx-project'); ?></label>
					<input type="text" id="cwx-filter-status" value="" placeholder="<?php _e('Status', 'cwx-project'); ?>" list="cwx-statuses" size="10" />
				</div>
				<div class="cwx-floater cwx-floater-right">
					<label for="cwx-filter-category"><?php _e('Category', 'cwx-project'); ?></label>
					<input type="text" id="cwx-filter-category" value="" placeholder="<?php _e('Category', 'cwx-project'); ?>" list="cwx-categories" size="10" />
				</div>
			</div>
			<div class="clear"></div>
		</div>

		<div id="cwx-project-list">
			<ul id="cwx-project-wrap"><li></li></ul>
			<div class="cwx-message cwx-no-projects">
				<?php printf(
					"<p><strong>%s</strong><br /><em>%s</em></p>\n",
					__('No Projects found', 'cwx-project'),
					__('Add new Projects or adjust the filters.', 'cwx-project')
				); ?>
			</div>
			<div class="cwx-message cwx-loading">
				<span class="spinner"></span> &nbsp;<?php _e('Loading', 'cwx-project'); ?>&nbsp;&hellip;
			</div>
		</div><!-- #cwx-project-list -->

		<?php if(cwxProject::userCan('cwxproj-add-projects')) : ?>
		<div id="cwx-project-new">
			<p><strong><?php _e('New Project', 'cwx-project'); ?></strong></p>
			<div class="cwx-error"></div>
			<form id="cwx-project-new-form" method="POST" action="<?php echo esc_url(admin_url()); ?>">
				<input type="hidden" name="action" value="cwx_project_edit" />
				<input type="hidden" name="_nonce" value="<?php echo wp_create_nonce('project-nonce-new'); ?>" />
				<input type="hidden" name="cwx_project_id" value="0" />

				<label class="screen-reader-text"><?php _e('Project Title:', 'cwx-project'); ?></label>
				<input type="text" name="cwx_project_title" value="" placeholder="<?php _e('Project Title', 'cwx-project'); ?>" aria-required="true" />

				<?php
					// Allow private projects? 
					if(cwxProject::isSuperAdmin() || (
						current_user_can('cwxproj-edit-own-projects') &&
						current_user_can('cwxproj-trash-own-projects') &&
						current_user_can('cwxproj-add-tasks') &&
						current_user_can('cwxproj-edit-own-tasks') &&
						current_user_can('cwxproj-trash-own-tasks')
					)) :
				?>
				<label title="<?php _e('Make the project private', 'cwx-project'); ?>">
					<input type="checkbox" name="cwx_project_is_private" />
					<?php _e('Private', 'cwx-project'); ?>
				</label>
				<?php endif; ?>

				<input type="submit" class="button button-primary" value="<?php _e('Create Project', 'cwx-project'); ?>" />
				<input type="button" class="button button-cancel" value="<?php _e('Cancel', 'cwx-project'); ?>" />
			</form>
		</div><!-- #cwx-project-new -->
		<?php endif; ?>

		<div id="cwx-project-tools">
			<ul class="cwx-clearfix">
				<?php if(is_admin()) : ?>
				<li id="cwx-donate">
					<?php cwxProject::getDonateButton(); ?>
				</li>
				<?php endif; ?>
				<?php if(cwxProject::userCan('cwxproj-add-projects')) : ?>
				<li id="cwx-add-project" class="hide-if-no-js">
					<button id="cwx-new-project" class="button"><?php 
						printf(
							'<span class="dashicons dashicons-plus"></span> %s',
							__('Project', 'cwx-project')
						); 
					?></button>
				</li>
				<?php endif; ?>
				<?php if(is_admin()) : ?>
				<?php if(cwxProject::userCan('cwxproj-trashcan')) : ?>
				<li id="cwx-project-trash" class="hide-if-no-js">
					<a href="<?php echo esc_url(admin_url('?edit=cwx_project_dashwidget&amp;view=trash#cwx_project_dashwidget')); ?>" id="cwx-trash-button" class="button" title="<?php _e('Trash &hellip;', 'cwx-project'); ?>"><?php 
						printf(
							'<span class="dashicons dashicons-trash"></span><span class="cwx-button-label"> %s</span>',
							__('Trash &hellip;', 'cwx-project')
						);
					?></a>
				</li>
				<?php endif; ?>
				<?php if(cwxProject::userCan('cwxproj-configure') || cwxProject::userCan('cwxproj-configure-permissions')) : ?>
				<li id="cwx-dash-configure">
					<a href="<?php echo esc_url(admin_url('?edit=cwx_project_dashwidget&amp;view=settings#cwx_project_dashwidget')); ?>" id="cwx-configure-button" class="button" title="<?php _e('Settings &hellip;', 'cwx-project'); ?>"><?php 
						printf(
							'<span class="dashicons dashicons-admin-generic"></span><span class="cwx-button-label"> %s</span>',
							__('Settings &hellip;', 'cwx-project')
						);
					?></a>
				</li>
				<?php endif; ?>
				<?php endif; // is_admin() ?>
			</ul>
			<div class="clear"></div>
		</div><!-- #cwx-project-tools -->
		
		<div id="cwx-confirm-delete" class="cwx-modal-frame">
			<div class="cwx-modal-dialog">
				<div class="cwx-modal-header">
					<span class="dashicons dashicons-trash"></span>
					<h1><?php _e('Confirm Operation', 'cwx-project'); ?></h1>
				</div>
				<div class="cwx-modal-content">
					<p><strong><?php _e('Do you want to trash the Item?', 'cwx-project'); ?></strong>
					<br />
					<em><?php _e('This is not a permanent deletion!', 'cwx-project'); ?></em></p>
					<p class="cwx-check-autoconfirm"><label>
						<input type="checkbox" id="cwx-do-not-show-again" />
						<?php _e('Do not ask again.', 'cwx-project'); ?>
					</label></p>
				</div>
				<div class="cwx-modal-buttons">
					<input type="button" id="cwx-confirm-yes" value="<?php _e('Yes', 'cwx-project'); ?>" />
					<input type="button" id="cwx-confirm-no" value="<?php _e('No', 'cwx-project'); ?>" />
				</div>
			</div>
		</div>

		<datalist id="cwx-assignable-users">
			<select style="display: none">
			<?php if(cwxProject::userCan('cwxproj-edit-own-assignment', 'cwxproj-edit-other-assignment')) : ?>
			<?php foreach(cwxProject::$assignable_users as $data) : ?>
				<option value="<?php echo esc_attr($data->name); ?>"><?php echo esc_attr($data->name); ?></option>
			<?php endforeach; ?>
			<?php endif; ?>
			</select>
		</datalist>
		<datalist id="cwx-categories" data-meta-type="category">
			<select style="display: none" data-meta-type="category">
			<?php foreach(cwxProject::$categories as $data) : ?>
				<option value="<?php echo esc_attr($data->title); ?>"><?php echo esc_attr($data->title); ?></option>
			<?php endforeach; ?>
			</select>
		</datalist>
		<datalist id="cwx-statuses" data-meta-type="status">
			<select style="display: none" data-meta-type="status">
			<?php foreach(cwxProject::$statuses as $data) : ?>
				<option value="<?php echo esc_attr($data->title); ?>"><?php echo esc_attr($data->title); ?></option>
			<?php endforeach; ?>
			</select>
		</datalist>
	</div>
	<?php
	}
	
	// Prints a populated Project Entry
	public static function printProject($data, $perms)
	{
		// - All values in $data are properly escaped!
		
		$action = esc_url(admin_url());
		$nonce = wp_create_nonce('project-nonce-'.$data->id);

		$proj_class = '';
		$act_edit = '<span class="dashicons dashicons-edit cwx-disabled" title="'. __('Disabled', 'cwx-project') .'"></span>';
		$act_del = '<span class="dashicons dashicons-trash cwx-disabled" title="'. __('Disabled', 'cwx-project') .'"></span>';
		$act_add = '<span class="dashicons dashicons-plus cwx-disabled" title="'. __('Disabled', 'cwx-project') .'"></span>';

		if($data->is_private)
			$proj_class .= ' cwx-private';
		else
			$proj_class .= ' cwx-public';

		if($perms['edit_project']){
			$proj_class .= ' cwx-project-owner';
			$act_edit = '<a href="#" id="cwx-edit-project-'. $data->id .'" class="cwx-edit-project" title="'. __('Edit Project', 'cwx-project') .'"><span class="dashicons dashicons-edit"></span></a>';
		}
		if($perms['trash_project']){
			$act_del = '<a href="#" id="cwx-trash-project-'. $data->id .'" class="cwx-trash-project" title="'. __('Trash Project', 'cwx-project') .'"><span class="dashicons dashicons-trash"></span></a>';
		}
		if($perms['add_tasks']){
			$act_add = '<a href="#TB_inline?width=600&height=550&inlineId=cwx-confirm-dialog" id="cwx-add-task-'. $data->id .'" class="cwx-add-task" title="'. __('Add  New Task to this Project', 'cwx-project') .'"><span class="dashicons dashicons-plus"></span></a>';
		}

		$progress = $data->progress / 100;
		$progress_str = number_format($data->progress, 2);
		$overdue = ($data->t_date_due !== null && time() > $data->t_date_due)? ' class="cwx-overdue"': '';

		$title = sprintf(
			$data->title.'%s', sprintf(__('&#13;Progress: %01.2f%%', 'cwx-project'), $data->progress)
		);

		return <<<TMPL
		<li id="cwx-project-{$data->id}" class="cwx-project cwx-container">
			<form id="cwx-project-form-{$data->id}" method="POST" action="{$action}">
				<input type="hidden" name="action" value="cwx_project_edit" />
				<input type="hidden" name="_nonce" value="{$nonce}" />
				<input type="hidden" name="cwx_project_id" value="{$data->id}" />

				<ul id="cwx-project-row-{$data->id}" class="cwx-project-row cwx-proj-folder{$proj_class}" title="{$title}" aria-label="%%<project>%%" tabindex="0">
					<li class="cwx-project-folding hilitable"><span class="dashicons folding"></span></li>
					<li class="cwx-project-title hilitable">{$data->title} </li>
					<li class="cwx-project-progress">
						<progress value="{$progress}"{$overdue}><span>{$progress_str}</span>%</progress>
					</li>
					<li class="cwx-project-actions hilitable">
						{$act_add}{$act_edit}{$act_del}
					</li>
				</ul>
			</form>
		
			<ul id="cwx-project-tasks-{$data->id}" class="cwx-tasks-container expandable"></ul>
			<div class="cwx-message cwx-no-tasks">
				%%<no-tasks>%%
			</div>
			<div class="cwx-message cwx-loading">
				<span class="spinner"></span> &nbsp;%%<loading>%%}&nbsp;&hellip;
			</div>
		</li>
TMPL;
	}
	
	// Prints a populated Task Entry
	public static function printTask($data, $perms)
	{
		// All values in $data are properly escaped!
		
		$action = esc_url(admin_url());
		$nonce = wp_create_nonce('task-nonce-'.$data->id);
		$progress = $data->progress / 100;
		
		$task_class = ''; 
		$act_edit = '<span class="dashicons dashicons-edit cwx-disabled" title="'. __('Disabled', 'cwx-project') .'"></span>';
		$act_del = '<span class="dashicons dashicons-trash cwx-disabled" title="'. __('Disabled', 'cwx-project') .'"></span>';
		if($data->is_private)
			$task_class .= ' cwx-task-private';
		if(!$data->t_assignee)
			$task_class .= ' cwx-task-unassigned';
		if($perms['edit_task']){
			$task_class .= ' cwx-task-owner';
			$act_edit = '<a href="#" id="cwx-edit-task-'. $data->id .'" class="cwx-edit-task" title="'. __('Edit Task', 'cwx-project') .'"><span class="dashicons dashicons-edit"></span></a>';
		}
		if($perms['trash_task']){
			$act_del = '<a href="#" id="cwx-trash-task-'. $data->id .'" class="cwx-trash-task" title="'. __('Trash Task', 'cwx-project') .'"><span class="dashicons dashicons-trash"></span></a>';
		}

		$prio_style = "color: {$data->priority_fg}; background: {$data->priority_bg}";
		$overdue = ($data->t_date_due !== null && time() > $data->t_date_due)? ' class="cwx-overdue"': '';
		
		$title = $data->title . sprintf(
			"&#13;%s", sprintf(__('Progress: %d%%', 'cwx-project'), $data->progress)
		);
		if($data->t_assignee)
			$title .= ("&#13;". __('Assigned to: ', 'cwx-project') . $data->assignee);
		if($data->t_date_due)
			$title .= ("&#13;". __('Due Date: ', 'cwx-project') . $data->date_due);
		
		return <<<TMPL
		<li id="cwx-task-{$data->id}" class="cwx-task cwx-container">
			<form id="cwx-task-form-{$data->id}" method="POST" action="{$action}">
				<input type="hidden" name="action" value="cwx_task_edit" />
				<input type="hidden" name="_nonce" value="{$nonce}" />
				<input type="hidden" name="cwx_task_id" value="{$data->id}" />
				<input type="hidden" name="cwx_parent_id" value="{$data->parent_id}" />

				<ul id="cwx-task-row-{$data->id}" class="cwx-task-row cwx-proj-folder{$task_class}" title="{$title}" aria-label="%%<task>%%" tabindex="0">
					<li class="cwx-task-folding hilitable"><span class="dashicons folding"></span></li>
					<li class="cwx-task-priority" title="%%<priority>%%">
						<span style="{$prio_style}">{$data->priority}</span>
					</li>
					<li class="cwx-task-title hilitable">{$data->title}</li>
					<li class="cwx-task-progress">
						<progress value="{$progress}"{$overdue}><span>{$data->progress}</span>%</progress>
					</li>
					<li class="cwx-project-actions cwx-task-actions hilitable">
						{$act_edit}{$act_del}
					</li>
				</ul>
			</form>
			<div id="cwx-task-data-{$data->id}" class="cwx-task-data expandable">
				<form id="cwx-task-data-form-{$data->id}" method="POST" action="{$action}">
					<input type="hidden" name="action" value="cwx_task_save" />
					<input type="hidden" name="_nonce" value="{$nonce}" />

			%%task-form%%
				</form>
			</div>
		</li>
TMPL;
	}

	// Prints a populated Form for Task Entries
	public static function printTaskForm($data, $perms)
	{
		// All values in $data are properly escaped!
		
		// User cannot edit anything
		if(!$perms['edit_task'])
			return self::printTaskData($data);

		$meta_nonce = wp_create_nonce('cwx-project-meta');

		$readonly = ' disabled="disabled"';
		$ro_duedate = (!$perms['edit_duedate'])? $readonly: '';
		$ro_status = (!$perms['edit_status'])? $readonly: '';
		$ro_category = (!$perms['edit_category'])? $readonly: '';
		
		$assignee = '<span class="cwx-text-control">'. $data->assignee .'</span>';
		if($perms['edit_assignment']){
			$assignee = '<input type="text" name="cwx_task_assignee" value="'. $data->assignee .'" list="cwx-assignable-users" size="13" placeholder="'. __('No one', 'cwx-project') .'" />';
		}
		else if($perms['self_assign']){
			$sel = ($data->t_assignee == get_current_user_id())? ' selected="selected"': '';
			$assignee = '<select name="cwx_task_assignee" size="1">'
						.'<option value="">'. __('No one', 'cwx-project') .'</option>'
						.'<option value="'. $data->assignee .'"'. $sel .'>'. __('Myself', 'cwx-project') .'</option>'
					   .'</select>';
		}
		
		$pholders = __('Not set', 'cwx-project');
		
		return <<<TMPL
					<input type="hidden" name="_meta_nonce" value="{$meta_nonce}" />
					<input type="hidden" name="cwx_task_id" value="{$data->id}" />
					<input type="hidden" name="cwx_parent_id" value="{$data->parent_id}" />

					<div class="cwx-form-column cwx-form-column-1">
						<div class="cwx-task-creator cwx-form-field">
							<label>%%<created-by>%%<!-- Created by --></label>
							<span class="cwx-text-control">{$data->creator}</span>
						</div>
						<div class="cwx-task-date-created cwx-form-field">
							<label>%%<created-on>%%<!-- Created on --></label>
							<span class="cwx-text-control" title="%%<created-on>%% {$data->created_dt}">{$data->created_on}</span>
						</div>
						<div class="cwx-task-date-updated cwx-form-field">
							<label>%%<updated-on>%%<!-- Updated on --></label>
							<span class="cwx-text-control" title="%%<updated-on>%% {$data->updated_dt}">{$data->updated_on}</span>
						</div>
					</div>
					<div class="cwx-form-column cwx-form-column-2">
						<div class="cwx-task-assignee cwx-form-field">
							<label>%%<assignee>%%<!-- Assignee --></label>
							{$assignee}
						</div>
						<div class="cwx-task-date-due cwx-form-field">
							<label>%%<due-date>%%<!-- Due Date --></label>
							<input type="date" name="cwx_task_datedue" value="{$data->date_due}" data-min-date="{$data->created_on}" placeholder="{$pholders}" title="%%<date-format>%%"{$ro_duedate} />
						</div>
					</div>
					<div class="clear"></div>

					<div class="cwx-form-column cwx-form-column-3">
						<div class="cwx-task-progress cwx-form-field">
							<label>%%<progress>%%<!-- Progress --></label>
							<input type="number" name="cwx_task_progress" min="0" max="100" step="1" size="3" value="{$data->progress}" data-unit="%" title="%%<progress-unit>%%" />
						</div>
						<div class="cwx-task-timespent cwx-form-field">
							<label>%%<time-spent>%%<!-- Time Spent --></label>
							<div class="cwx-duration-control cwx-control" title="%%<time-spent-format>%%">
								<input type="text" name="cwx_task_timespent_d" value="{$data->time_spent_d}" size="1" data-min="0" data-max="" /><span class="cwx-duration-sep"></span><input type="text" name="cwx_task_timespent_h" value="{$data->time_spent_h}" size="1" data-min="0" data-max="23" /><span class="cwx-duration-sep"></span><input type="text" name="cwx_task_timespent_m" value="{$data->time_spent_m}" size="1" data-min="0" data-max="59" />
							</div>
						</div>
					</div>
					<div class="cwx-form-column cwx-form-column-4">
						<div class="cwx-task-category cwx-form-field">
							<label>%%<category>%%<!-- Category --></label>
							<input type="text" name="cwx_task_category" value="{$data->category}" list="cwx-categories" placeholder="{$pholders}" size="13"{$ro_category} />
						</div>
						<div class="cwx-task-status cwx-form-field">
							<label>%%<status>%%<!-- Status --></label>
							<input type="text" name="cwx_task_status" value="{$data->status}" list="cwx-statuses" placeholder="{$pholders}" size="13"{$ro_status} />
						</div>
					</div>
					<div class="clear"></div>
				
					<div class="cwx-task-description">
						<label>%%<description>%%<!-- Description --></label>
						<textarea name="cwx_task_description" rows="5">{$data->t_description}</textarea>
					</div>
TMPL;
	}

	// Prints a populated Form for Task Entries
	public static function printTaskData($data)
	{
		$not_set = __('Not set', 'cwx-project');
		
		$assignee = (!empty($data->assignee))? $data->assignee: __('No one', 'cwx-project');
		$datedue =  (!empty($data->date_due))? $data->date_due: $not_set;
		$status =  (!empty($data->status))? $data->status: $not_set;
		$category =  (!empty($data->category))? $data->category: $not_set;
		$description =  (!empty($data->t_description))? $data->t_description: __('No description available.', 'cwx-project');
		
		return <<<TMPL
			<div id="cwx-task-data-{$data->id}" class="cwx-task-data expandable">
				<form id="cwx-task-data-form-{$data->id}">
					<div class="cwx-form-column cwx-form-column-1">
						<div class="cwx-task-creator cwx-form-field">
							<label>%3\$s<!-- Created by --></label>
							<span class="cwx-text-control">{$data->creator}</span>
						</div>				
						<div class="cwx-task-date-created cwx-form-field">
							<label>%4\$s<!-- Created on --></label>
							<span class="cwx-text-control" title="%4\$s {$data->created_dt}">{$data->created_on}</span>
						</div>
						<div class="cwx-task-date-updated cwx-form-field">
							<label>%5\$s<!-- Updated on --></label>
							<span class="cwx-text-control" title="%5\$s {$data->updated_dt}">{$data->updated_on}</span>
						</div>
					</div>
					<div class="cwx-form-column cwx-form-column-2">
						<div class="cwx-task-assignee cwx-form-field">
							<label>%6\$s<!-- Assignee --></label>
							<span class="cwx-text-control">{$assignee}</span>
						</div>
						<div class="cwx-task-date-due cwx-form-field">
							<label>%7\$s<!-- Due Date --></label>
							<span class="cwx-text-control" title="%13\$s">{$datedue}</span>
						</div>
					</div>
					<div class="clear"></div>

					<div class="cwx-form-column cwx-form-column-3">
						<div class="cwx-task-progress cwx-form-field">
							<label>%8\$s<!-- Progress --></label>
							<span class="cwx-text-control" title="%14\$s">{$data->progress}%%</span>
						</div>
						<div class="cwx-task-timespent cwx-form-field">
							<label>%9\$s<!-- Time Spent --></label>
							<span class="cwx-text-control" title="%15\$s">{$data->time_spent_d}<span class="cwx-duration-sep"></span>{$data->time_spent_h}<span class="cwx-duration-sep"></span>{$data->time_spent_m}</span>
						</div>
					</div>
					<div class="cwx-form-column cwx-form-column-4">
						<div class="cwx-task-status cwx-form-field">
							<label>%10\$s<!-- Status --></label>
							<span class="cwx-text-control">{$status}</span>
						</div>
						<div class="cwx-task-category cwx-form-field">
							<label>%11\$s<!-- Category --></label>
							<span class="cwx-text-control">{$category}</span>
						</div>
					</div>
					<div class="clear"></div>
				
					<div class="cwx-task-description cwx-form-field">
						<label>%12\$s<!-- Description --></label>
						<div class="cwx-task-desctext">{$description}</div>
					</div>
				</form>
			</div>
TMPL;
	}


	/*
	 * Trash Can
	 */

	// Prints the Widget 'trash' list template
	public static function printTrash()
	{
	?>	
	<div id="cwx-project-trash" class="cwx-project-widget">
		<input type="hidden" name="action" value="cwx_project_trash_action" />
		<input type="hidden" name="_project_nonce" value="<?php echo wp_create_nonce('_project_trash'); ?>" />

		<div class="cwx-project-header">
			<span class="dashicons dashicons-trash"></span>
			<span class="cwx-project-title-settings"><?php _e('Trash Can', 'cwx-project'); ?></span>
			<div class="clear"></div>
			<div class="cwx-error"></div>
		</div>

		<div id="cwx-trashcan">
			<div id="cwx-trash-list-wrap">
				<table id="cwx-trash-list"></table>
			</div>
			<div class="cwx-message cwx-no-entries">
				<?php printf(
					"<p><strong>%s</strong></p>\n",
					__('The Trash Can is empty!', 'cwx-project')
				); ?>
			</div>
			<div class="cwx-message cwx-loading">
				<span class="spinner"></span> &nbsp;<?php _e('Loading', 'cwx-project'); ?>&nbsp;&hellip;
			</div>
		</div><!-- #cwx-project-list -->

		<div id="cwx-project-tools">
			<ul class="cwx-clearfix">
				<li id="cwx-project-bulk-action">
					<select name="bulk_action" size="1">
						<option value=""><?php _e('- Select Action -', 'cwx-project'); ?></option>
						<option value="restore"><?php _e('Restore Selected', 'cwx-project'); ?></option>
						<option value="delete"><?php _e('Delete Selected', 'cwx-project'); ?></option>
					</select>
				</li>
				<li id="cwx-project-action">
					<input type="submit" class="button button-primary" value="<?php _e('Submit', 'cwx-project'); ?>" />
				</li>
				<li id="cwx-project-close">
					<a href="<?php echo esc_url(admin_url()); ?>" class="cwx-cancel button button-secondary"><?php _e('Close', 'cwx-project'); ?></a>
				</li>
			</ul>
			<div class="clear"></div>
		</div><!-- #cwx-project-tools -->
	</div>
	<?php
	}

	// Prints a populated Form for Task Entries
	public static function printTrashRow($data)
	{
		if($data->parent_id === null) : ?>
		<tr id="cwx-project-id-<?php echo esc_attr($data->id); ?>" class="cwx-trashrow-project" title="<?php echo esc_attr($data->updated_dt); ?>" aria-label="<?php _e('Project', 'cwx-project'); ?>">
			<td class="cwx-trash-check"><?php if($data->is_trash) : ?>
				<input type="checkbox" name="sel_project[]" value="<?php echo esc_attr($data->id); ?>" />
			<?php endif; ?></td>
			<td class="cwx-trash-title" colspan="2"><?php echo $data->title; ?></td>
			<td class="cwx-trash-date"><?php echo $data->updated; ?></td>
		</tr>
		<?php else : ?>
		<tr id="cwx-task-id-<?php echo esc_attr($row->id); ?>" class="cwx-trashrow-task" title="<?php echo esc_attr($data->updated_dt); ?>" aria-label="<?php _e('Task', 'cwx-project'); ?>">
			<td class="cwx-trash-check"></td>
			<td class="cwx-trash-check">
				<input type="checkbox" name="sel_task[<?php echo esc_attr($data->parent_id); ?>][]" value="<?php echo esc_attr($data->id); ?>" data-project-id="<?php echo esc_attr($data->parent_id); ?>" />
			</td>
			<td class="cwx-trash-title"><?php echo $data->title; ?></td>
			<td class="cwx-trash-date"><?php echo $data->updated; ?></td>
		</tr>
		<?php endif;
	}
	
}

