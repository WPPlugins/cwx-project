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
<div id="cwx-project-config" class="cwx-project-widget">
	<input type="hidden" name="action" value="cwx_project_save_settings" />
	<input type="hidden" name="_meta_nonce" value="<?php echo wp_create_nonce('cwx-project-meta'); ?>" />
	
	<div class="cwx-project-header">
		<span class="dashicons dashicons-admin-generic"></span>
		<span class="cwx-project-title-settings"><?php _e('Settings', 'cwx-project'); ?></span>
		<span class="cwx-project-actions-settings">
			<a href="<?php echo esc_url(admin_url()); ?>" class="cwx-cancel button button-secondary"><?php _e('Close', 'cwx-project'); ?></a>
			&nbsp;
			<input type="submit" class="button button-primary" value="<?php _e('Apply Changes', 'cwx-project'); ?>">
		</span>
		<div class="clear"></div>
		<div class="cwx-error"></div>
	</div>
	
	<div class="cwx-tab-container cwx-clearfix">
		<div class="cwx-tab-bar">
			<ul class="cwx-tab-strip cwx-clearfix">
				<?php
				if(current_user_can('cwxproj-configure-categories')) : ?>
				<li id="cwx-config-tab-categories" class="cwx-tab cwx-tab-active" tabindex="0">
					<?php _e('Categories', 'cwx-project'); ?>
				</li>
				<?php endif;
				if(current_user_can('cwxproj-configure-statuses')) : ?>
				<li id="cwx-config-tab-statuses" class="cwx-tab" tabindex="0">
					<?php _e('Statuses', 'cwx-project'); ?>
				</li>
				<?php endif;
				if(current_user_can('cwxproj-configure-metabox')) : ?>
				<li id="cwx-config-tab-metabox" class="cwx-tab" tabindex="0">
					<?php _e('Post Types', 'cwx-project'); ?>
				</li>
				<?php endif;
				if(current_user_can('cwxproj-configure-colors')) : ?>
				<li id="cwx-config-tab-priorities" class="cwx-tab" tabindex="0">
					<?php _e('Priorities', 'cwx-project'); ?>
				</li>
				<?php endif;
				if(is_super_admin() || current_user_can('cwxproj-configure-permissions')) : ?>
				<li id="cwx-config-tab-permissions" class="cwx-tab" tabindex="0">
					<?php _e('Permissions', 'cwx-project'); ?>
				</li>
				<?php endif; ?>
			</ul>
		</div>
		<div class="cwx-tab-scroller">
			<span class="cwx-tab-scroll-left" tabindex="0">
				<span class="dashicons dashicons-arrow-left-alt2"></span>
			</span>
			<span class="cwx-tab-scroll-right" tabindex="0">
				<span class="dashicons dashicons-arrow-right-alt2"></span>
			</span>
		</div>
	</div>
	
	<?php if(current_user_can('cwxproj-configure-categories')) : ?>
	<div id="cwx-config-sheet-categories" class="cwx-tab-sheet cwx-sheet-active" data-list-id="cwx-categories" data-meta-type="category">
		<table id="cwx-project-categories" class="cwx-project-meta-list">
			<tr class"cwx-project-meta-header">
				<th class="cwx-project-meta-title"><?php _e('Category Name', 'cwx-project'); ?></th>
				<th class="cwx-project-meta-target" title="<?php _e('Move Tasks to Category (also see the note below)', 'cwx-project'); ?>"><?php _e('Move Tasks *', 'cwx-project'); ?></th>
				<th class="cwx-project-meta-proposal" title="<?php _e('Proposed Category (check to accept)', 'cwx-project'); ?>"><span class="dashicons dashicons-lightbulb"></span></th>
				<th class="cwx-project-meta-delete" title="<?php _e('Delete Category (you may use Move Tasks)', 'cwx-project'); ?>"><span class="dashicons dashicons-trash"></span></th>
			</tr>
			<?php foreach(cwxProject::$categories as $key => $data) : ?>
			<tr id="cwx-project-meta-<?php echo esc_attr($data->id); ?>" class="cwx-project-meta-row">
				<td class="cwx-project-meta-title">
					<input type="hidden" name="cwx_meta_type[<?php echo esc_attr($data->id); ?>]" value="category" />
					<input type="text" name="cwx_meta_title[<?php echo esc_attr($data->id); ?>]" value="<?php echo esc_attr($data->title); ?>" size="5" />
				</td>
				<td class="cwx-project-meta-target">
					<input type="text" name="cwx_meta_moveto[<?php echo esc_attr($data->id); ?>]" value="" list="cwx-categories" size="5" />
				</td>
				<td class="cwx-project-meta-proposal">
				<?php if($data->is_proposal) : ?>
					<input type="checkbox" name="cwx_meta_proposal[<?php echo esc_attr($data->id); ?>]" />
				<?php else : ?>
					&nbsp;
				<?php endif; ?>
				</td>
				<td class="cwx-project-meta-delete">
					<input type="checkbox" name="cwx_meta_delete[<?php echo esc_attr($data->id); ?>]" />
				</td>
			</tr>
			<?php endforeach; ?>
		</table>
		<div class="cwx-message cwx-no-entries"<?php echo (empty(cwxProject::$categories))? ' style="display:block"': ''; ?>>
			<p><?php _e('No categories yet!', 'cwx-project'); ?></p>
		</div>
		<div class="cwx-project-new-category cwx-project-new-item">
			<label>
				<span class="screen-reader-text"><?php _e('New Category:', 'cwx-project'); ?></span>
				<input type="text" name="cwx_new_meta_title" value="" placeholder="<?php _e('New Category', 'cwx-project'); ?>" />
				<input type="button" class="cwx-add-item button button-secondary" value="<?php _e('Add Category', 'cwx-project'); ?>" />
			</label>
			<p><?php _e('* Tasks of the Category will be moved to the Category entered here.<br />* If you opt to delete the Category, you can use the move function to move Tasks to the entered Category before deletion.<br />* If you enter a Category name that does not yet exist, the Category will be created and Tasks will be moved to the new Category.', 'cwx-project'); ?></p>
		</div>
	</div><!-- #cwx-config-sheet-categories -->
	<?php endif; ?>

	<?php if(current_user_can('cwxproj-configure-statuses')) : ?>
	<div id="cwx-config-sheet-statuses" class="cwx-tab-sheet" data-list-id="cwx-statuses" data-meta-type="status">
		<table id="cwx-project-statuses" class="cwx-project-meta-list">
			<tr class"cwx-project-meta-header">
				<th class="cwx-project-meta-title"><?php _e('Status Name', 'cwx-project'); ?></th>
				<th class="cwx-project-meta-target" title="<?php _e('Change the Status of Tasks to entered Status', 'cwx-project'); ?>"><?php _e('New Task Status *', 'cwx-project'); ?></th>
				<th class="cwx-project-meta-proposal" title="<?php _e('Proposed Status (check to accept)', 'cwx-project'); ?>"><span class="dashicons dashicons-lightbulb"></span></th>
				<th class="cwx-project-meta-delete" title="<?php _e('Delete Status', 'cwx-project'); ?>"><span class="dashicons dashicons-trash"></span></th>
			</tr>
			<?php foreach(cwxProject::$statuses as $key => $data) : ?>
			<tr id="cwx-project-meta-<?php echo esc_attr($data->id); ?>" class="cwx-project-meta-row">
				<td class="cwx-project-meta-title">
					<input type="hidden" name="cwx_meta_type[<?php echo esc_attr($data->id); ?>]" value="status" />
					<input type="text" name="cwx_meta_title[<?php echo esc_attr($data->id); ?>]" value="<?php echo esc_attr($data->title); ?>" size="5" />
				</td>
				<td class="cwx-project-meta-target">
					<input type="text" name="cwx_meta_moveto[<?php echo esc_attr($data->id); ?>]" value="" list="cwx-statuses" size="5" />
				</td>
				<td class="cwx-project-meta-proposal">
				<?php if($data->is_proposal) : ?>
					<input type="checkbox" name="cwx_meta_proposal[<?php echo esc_attr($data->id); ?>]" />
				<?php else : ?>
					&nbsp;
				<?php endif; ?>
				</td>
				<td class="cwx-project-meta-delete">
					<input type="checkbox" name="cwx_meta_delete[<?php echo esc_attr($data->id); ?>]" />
				</td>
			</tr>
			<?php endforeach; ?>
		</table>
		<div class="cwx-message cwx-no-entries"<?php echo (empty(cwxProject::$statuses))? ' style="display:block"': ''; ?>>
			<p><?php _e('No statuses yet!', 'cwx-project'); ?></p>
		</div>
		<div class="cwx-project-new-category cwx-project-new-item">
			<label>
				<span class="screen-reader-text"><?php _e('New Status:', 'cwx-project'); ?></span>
				<input type="text" name="cwx_new_meta_title" value="" placeholder="<?php _e('New Status', 'cwx-project'); ?>" />
				<input type="button" class="cwx-add-item button button-secondary" value="<?php _e('Add Status', 'cwx-project'); ?>" />
			</label>
			<p><?php _e('* Changes Tasks with current Status to the entered Status.<br />* If you opt to delete the Status, you can use the function to assign a new Status to Tasks before deletion.<br />* If you enter a Status name that does not yet exist, the Status will be created and Tasks will be assigned to the new Status.', 'cwx-project'); ?></p>
		</div>
	</div><!-- #cwx-config-sheet-statuses -->
	<?php endif; ?>

	<?php if(current_user_can('cwxproj-configure-metabox')) : ?>
	<div id="cwx-config-sheet-metabox" class="cwx-tab-sheet">
		<p><?php _e('Check the Post Types you want a Task Metabox for. The Metabox will appear on Pages where you edit posts of the post types you select here.', 'cwx-project'); ?></p>
		
		<ul class="cwx-project-posttypes">
		<?php
			$post_types = cwxProject::getPostTypes();
			if(($sel_types = cwxProject::getConfig('enabled_post_types')) === false)
				$sel_types = array();
			
			foreach($post_types as $post_type) :
				$obj = get_post_type_object($post_type);
				$label= $obj->labels->name;
				$sel = (in_array($post_type, $sel_types))? ' checked="checked"': '';
		?>
			<li>
				<label>
					<input type="checkbox" name="cwx_project_posttype[]" value="<?php echo esc_attr($post_type); ?>"<?php echo $sel; ?> />
					<strong><?php echo esc_attr($label); ?></strong>
				</label>
			</li>
		<?php endforeach; ?>
		</ul>
	</div><!-- #cwx-config-sheet-metabox -->
	<?php endif; ?>

	<?php if(current_user_can('cwxproj-configure-colors')) : ?>
	<div id="cwx-config-sheet-priorities" class="cwx-tab-sheet">
		<ul class="cwx-project-priorities">
		<?php for($i = 1; $i <= 9; $i++) : ?>
			<li>
				<label for="cwx-priority-<?php echo esc_attr($i); ?>"><?php echo esc_attr(cwxProject::priorityLabel($i)); ?></label>
				<input type="text" id="cwx-priority-<?php echo esc_attr($i); ?>" class="cwx-color-control" name="cwx_project_priority[<?php echo esc_attr($i); ?>]" value="<?php echo esc_attr(cwxProject::priorityColor($i)); ?>" data-default-color="<?php echo esc_attr(cwxProject::priorityColorDefault($i)); ?>" />
			</li>
		<?php endfor; ?>
		</ul>
	</div><!-- #cwx-config-sheet-priorities -->
	<?php endif; ?>

	<?php if(is_super_admin() || current_user_can('cwxproj-configure-permissions')) : ?>
	<div id="cwx-config-sheet-permissions" class="cwx-tab-sheet">
	<?php
		global $wp_roles;
		$roles = $wp_roles->role_names;
		$caps = cwxProject::$capabilities;
	?>
		<ul class="cwx-role-names">
			<?php $idx = 0; foreach($roles as $rkey => $rname) : ?>
			<li id="<?php echo esc_attr($rkey); ?>-item" class="cwx-role-name-item<?php echo (!$idx)? ' cwx-item-active': ''; ?>" tabindex="0">
				<h4><?php echo esc_html($rname); ?></h4>
			</li>
			<?php $idx++; endforeach; ?>
		</ul>
		<?php $idx = 0; foreach($roles as $rkey => $rname) : ?>
		<ul id="<?php echo esc_attr($rkey); ?>-list" class="cwx-list-capabilities<?php echo (!$idx)? ' cwx-list-active': ''; ?>">
			<?php foreach($caps as $ckey => $clabel) : $role = get_role($rkey); ?>
			<li><label>
				<input type="checkbox" name="cwx_project_perms[<?php echo esc_attr($rkey); ?>][<?php echo esc_attr($ckey); ?>]"<?php echo ($role->has_cap($ckey))? ' checked="checked"': ''; ?> />
				<span><?php echo esc_html($clabel); ?></span>
			</label></li>
			<?php $idx++; endforeach; ?>
		</ul>
		<?php $idx++; endforeach; ?>
		<div class="clear"></div>
	</div><!-- #cwx-config-sheet-permissions -->
	<?php endif; ?>
</div>

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

<div id="cwx-project-meta-template">
	<table class="cwx-project-meta-list">
		<tr id="cwx-project-meta-%id" class="cwx-project-meta-row cwx-project-meta-new">
			<input type="hidden" name="cwx_meta_type[%id]" value="" />
			<td class="cwx-project-meta-title">
				<input type="hidden" name="cwx_meta_type[%id]" value="" />
				<input type="text" name="cwx_meta_title[%id]" value="" />
			</td>
			<td class="cwx-project-meta-target">
				<input type="text" name="cwx_meta_moveto[%id]" value="" list="" />
			</td>
			<td class="cwx-project-meta-proposal">
				&nbsp;<input type="hidden" name="cwx_meta_proposal[%id]" value="0" />
			</td>
			<td class="cwx-project-meta-delete">
				<input type="checkbox" name="cwx_meta_delete[%id]" />
			</td>
		</tr>
	</table>
</div><!-- #cwx-project-meta-template -->

