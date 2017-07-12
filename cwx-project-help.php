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


class cwxProjectHelp
{

	/**
	 * Widget Help
	 */

	public static function printWidgetHelp()
	{
		$tabs = array(
			'overview'	=> __('Overview', 'cwx-project'),
			'trash'		=> __('Trash Can', 'cwx-project'),
			'settings'	=> __('Settings', 'cwx-project'),
			'projects'	=> __('Projects', 'cwx-project'),
			'tasks'		=> __('Tasks', 'cwx-project')
		);

		if(!isset($_REQUEST['view']) || $_REQUEST['view'] == 'settings')
			unset($tabs['trash']);
		if(!isset($_REQUEST['view']) || $_REQUEST['view'] == 'trash')
			unset($tabs['settings']);

		print('<div id="cwx-project-admin-help">'."\n");
		
		self::_printWidgetHelpTabbar($tabs);
		self::_printWidgetHelpSheets($tabs);

		print("</div>\n");
	}
	
	public static function getWidgetHelpSidebar()
	{
		return null;
	}
	
	private static function _printWidgetHelpTabbar($tabs)
	{
	?>
		<div class="cwx-tab-container cwx-clearfix" style="width: 100%;">
			<div class="cwx-tab-bar">
				<ul class="cwx-tab-strip cwx-clearfix" style="left: 0px;">
				<?php foreach($tabs as $id => $label) : ?>
					<li id="cwx-help-tab-<?php echo esc_attr($id); ?>" class="cwx-tab<?php if($id == 'overview') echo esc_attr(' cwx-tab-active'); ?>" tabindex="0">
						<?php echo esc_html($label); ?>
					</li>
				<?php endforeach; ?>
				</ul><!-- .cwx-tab-strip -->
			</div><!-- .cwx-tab-bar -->
			<div class="cwx-tab-scroller">
				<span class="cwx-tab-scroll-left" tabindex="0">
					<span class="dashicons dashicons-arrow-left-alt2"></span>
				</span>
				<span class="cwx-tab-scroll-right" tabindex="0">
					<span class="dashicons dashicons-arrow-right-alt2"></span>
				</span>
			</div><!-- .cwx-tab-scroller -->
		</div><!-- .cwx-tab-container -->
	<?php
	}
	
	private static function _printWidgetHelpSheets($tabs)
	{
		foreach($tabs as $id => $label) :
	?>
		<div id="cwx-help-sheet-<?php echo esc_attr($id); ?>" class="cwx-tab-sheet<?php if($id == 'overview') echo esc_attr(' cwx-sheet-active'); ?>">
			<?php self::_printWidgetHelpSheet($id); ?>
		</div><!-- #cwx-help-sheet-<?php echo esc_attr($id); ?>.cwx-tab-sheet -->
	<?php
		endforeach;
	}

	private static function _printWidgetHelpSheet($id)
	{
		switch($id){
			case 'overview': self::_printWidgetHelpOverview(); break;
			case 'projects': self::_printWidgetHelpProjects(); break;
			case 'tasks': self::_printWidgetHelpTasks(); break;
			case 'trash': self::_printWidgetHelpTrash(); break;
			case 'settings': self::_printWidgetHelpSettings(); break;
		}	
	}

	private static function _printWidgetHelpOverview()
	{
		printf(
			'<p>%1$s</p><p>%2$s</p><p>%3$s</p><p>%4$s</p>',
			__('Welcome to the CWX Project Plugin! It helps you organize your daily work by providing Task or ToDo lists, grouped by Project folders.', 'cwx-project'),
			__('After the first activation of CWX Project, there will be no Projects in the list, so, the first thing you want to do is add a Project. There is a Plus-button on the bottom of the widget that opens a form that lets you add a new Project. (Please note: If you check the Private box, only you can see and use the Project, otherwise all permitted user can see and or use the Project. Read more about Projects under the Projects tab.)', 'cwx-project'),
			__('Another button on the bottom of the widget is the Trash Can, which opens the Trash Can page of CWX Project. There you can restore previously trashed items or delete them permanently. Note, this button is only available to users that have permission to access the Trash Can.', 'cwx-project'),
			__('The last button on the bottom of the widget is the Cog, which opens the settings for CWX Project. There you can manage categories, statuses, user permissions and other stuff. Note, this button is only available to users that have permission to access the settings page.', 'cwx-project')
		);
	?>
	<?php
	}

	private static function _printWidgetHelpProjects()
	{
		printf(
			 '<p>%1$s</p>'
			.'<p style="font-weight:bold; font-size:1.1em;">%2$s</p>'
			.'<ul><li><strong>%3$s</strong> - %4$s</li></ul>'
			.'<p style="font-weight:bold; font-size:1.1em;">%5$s</p>'
			.'<p><strong>%6$s</strong> - %7$s</p>'
			.'<p><strong>%8$s</strong> - %9$s</p>'
			.'<p><strong>%10$s</strong> - %11$s</p>',
			__('CWX Project organizes Tasks in Project folders, which are the top-level items. Project folders only have two properties, their titles and a status of public or private. However, Project folders have a progress bar, showing the total progress of contained Tasks. Contained Tasks can be opened by clicking on the Project row or by putting the focus on the Project row, then hitting the Enter-key. On the right, Project folders offer up to three action buttons, depending on user permissions.', 'cwx-project'),
			// 2
			__('Internal Statuses / Legend', 'cwx-project'), 
			// 3, 4
			__('Italic Title', 'cwx-project'),
			__('An italic title means the project is private to <em>you</em>.', 'cwx-project'),
			// 5
			__('Project Actions', 'cwx-project'), 
			// 6, 7
			sprintf(__('Add Task (%s)', 'cwx-project'), '<span class="dashicons dashicons-plus"></span>'), 
			__('The Add Task button allows you to create new Tasks for the associated Project. The new Task is not publicly visible until it has been updated/saved for the first time.', 'cwx-project'),
			// 8, 9
			sprintf(__('Edit Project (%s)', 'cwx-project'), '<span class="dashicons dashicons-edit"></span>'), 
			__('The Edit Project button switches the Project row into edit mode, so you can alter the title of the project. Any click outside of the row, or if the input focus leaves the row, changes will be submitted and edit mode will be left. If the Esc-key is hit, changes will be undone and edit mode will be left.', 'cwx-project'),
			// 10, 11
			sprintf(__('Trash Project (%s)', 'cwx-project'), '<span class="dashicons dashicons-trash"></span>'), 
			__('With the Trash button, Projects can be moved to the Trash Can without permanently deleting them and after a confirmation. Projects can be restored from the Trash Can page of CWX Project, but only if permitted.', 'cwx-project')
		);
	}

	private static function _printWidgetHelpTasks()
	{
		printf(
			 '<p>%1$s</p>'
			.'<p style="font-weight:bold; font-size:1.1em;">%2$s</p>'
			.'<ul><li><strong>%3$s</strong> - %4$s</li><li><strong>%5$s</strong> - %6$s</li><li><strong>%7$s</strong> - %8$s</li></ul>'
			.'<p style="font-weight:bold; font-size:1.1em;">%9$s</p>'
			.'<p><strong>%10$s</strong> - %11$s</p>'
			.'<p><strong>%12$s</strong> - %13$s</p>'
			.'<p style="font-weight:bold; font-size:1.1em;">%14$s</p>'
			.'<p>%15$s</p>'
			.'<p><strong>%16$s</strong> - %17$s</p>'
			.'<p><strong>%18$s</strong> - %19$s</p>',
			__('Tasks in CWX Project are organized in Project folders. Besides a title and priority on its main row, a Task has several other properties that can be used optionally. These properties can be opened by clicking on the Tasks row or by putting the focus on the Task row, then hitting the Enter-key. On the right of a Task row there are two action buttons, depending on user permissions.', 'cwx-project'),
			// 2
			__('Internal Statuses / Legend', 'cwx-project'), 
			// 3 - 8
			__('Italic Title', 'cwx-project'),
			__('An italic title means, <em>you</em> are the the creator or an assignee of the Task.', 'cwx-project'),
			__('Bold Title', 'cwx-project'),
			__('A bold title means, the Task is not yet assigned to anyone.', 'cwx-project'),
			__('Underlined Title', 'cwx-project'),
			__('An underlined title means, the Task is not yet published and <em>you</em> need to make changes and save it.', 'cwx-project'),
			// 9
			__('Task Actions', 'cwx-project'), 
			// 10, 11
			sprintf(__('Edit Task (%s)', 'cwx-project'), '<span class="dashicons dashicons-edit"></span>'), 
			__('The Edit Task button switches the Task row into edit mode, so you can alter the priority and title of the Task. Any click outside of the row, or if the input focus leaves the row, changes will be submitted and edit mode will be left. If the Esc-key is hit, changes will be undone and edit mode will be left.', 'cwx-project'),
			// 12, 13
			sprintf(__('Trash Task (%s)', 'cwx-project'), '<span class="dashicons dashicons-trash"></span>'), 
			__('With the Trash button, Task can be moved to the Trash Can without permanently deleting them and after a confirmation. Trashed Tasks can be restored from the Trash Can page of CWX Project, but only if permitted.', 'cwx-project'),
			// 14
			__('Task Properties', 'cwx-project'), 
			// 15
			__('By clicking on a Task row, or hitting the Enter-key on a focussed row, the Task will reveal its properties. Depending on your permissions, you can edit the properties. While most of these properties are self-explanatory, some need an explanation &hellip;', 'cwx-project'),
			// 16, 17
			__('Assignee', 'cwx-project'),
			__('CWX Project automatically detects which users can be Assignees of Tasks. For more comfort in choosing a user, you can type any number of characters in the input field and pick a user from the list that appears. If you enter a users name that cannot be an assignee, no user will be assigned.', 'cwx-project'),
			// 18, 19
			__('Statuses and Categories', 'cwx-project'),
			__('In Status and Category fields you can enter any Status or Category name or pick one from the list that appears. If you enter a name that does not yet exists, the Status or Category will be created as a proposal in the system. Such proposals are available temporarily until an administrator (or a user with permission to manage Statuses/Categories) accepts the proposed item or deletes it.', 'cwx-project')
		);
	}

	private static function _printWidgetHelpTrash()
	{
		printf(
			 '<p>%1$s</p>'
			.'<p>%2$s</p>'
			.'<p><strong>%3$s</strong> - %4$s</p>'
			.'<p><strong>%5$s</strong> - %6$s</p>',
			__('The CWX Project Widget does not allow to permanently delete any items. Instead, these items will be moved to the Trash Can. This page lists all items that are currently in the Trash Can. The Trash Can offers you to Restore or permanently Delete the items.', 'cwx-project'),
			__('Please note, that all Projects are listed here, trashed or not, that contain trashed Tasks. This is to let you know to which Project trashed Task belong to. Projects that have not been trashed, do not have a checkbox and can neither be deleted nor restored.', 'cwx-project'),
			// 3, 4
			__('Restoring', 'cwx-project'),
			__('To restore one or more items, set a checkmark on the item(s) you want to restore. If you choose to restore a Task of a Project that is trashed, the Project will be restored automatically as well. When you have checked the desired items, navigate to the bottom of the list and select Restore Selected from the select box and hit the Submit button.', 'cwx-project'),
			// 5, 6
			__('Deleting', 'cwx-project'),
			__('To delete one or more items, set a checkmark on the item(s) you want to delete. If you choose to delete a Project, all of its Tasks will be deleted automatically as well. When you have checked the desired items, navigate to the bottom of the list and select Delete Selected from the select box and hit the Submit button. Please note that there will be no further confimation dialog and the items will be deleted permanently. Be careful!', 'cwx-project')
		);
	}

	private static function _printWidgetHelpSettings()
	{
		printf(
			 '<p>%1$s</p>'
			.'<p>%2$s</p>'
			.'<p>%3$s</p>'
			.'<p><strong>%4$s</strong> - %5$s</p>'
			.'<p><strong>%6$s</strong> - %7$s</p>'
			.'<p><strong>%8$s</strong> - %9$s</p>'
			.'<p><strong>%10$s</strong> - %11$s</p>',
			__('Settings for CWX Project offer you to manage Task Statuses, Task Categories and User Permissions (based on Wordpress Roles), customize the colors of Task Priorities and to set which type of posts show the CWX Project - Task box on their edit pages. If you get the option to change these settings depends on your current role and permissions.', 'cwx-project'),
			__('All of these settings are organized in Tabs. Depending on your device and/or window size, some of these Tabs may be hidden. In that case you can scroll the Tabs with the arrow buttons next to them. You can always use the key combination [shift] + [ctrl] + [left]/[right] to navigate the Tabs.', 'cwx-project'),
			__('After you have made any changes, you can hit the Apply Changes button at any time you want, no matter on which tab you are - all changes on all tabs will be saved and applied immediately.', 'cwx-project'),
			// 4, 5
			__('Statuses and Categories', 'cwx-project'),
			__('You can create new Task Statuses and Categories on the Settings page, but usually, you or other users create them right in the Tasks. In the latter case, they are stored as proposals and you will see a checkbox in the column that shows a bulb. You may set a checkmark on the proposed item to accept it. On the column with the trash can, you can mark items you want to delete (permanently). Finally, on the column Move Tasks, you can move Tasks assigned to the Status or Category to another Status or Category, which is especially useful if you choose to delete the item.', 'cwx-project'),
			// 6, 7
			__('Post Types', 'cwx-project'),
			__('The Post Types tab gives you options to choose which types of posts you want the CWX Project Task box to show on. This box allows you and other users (with permission) to associate the Post with a Task.', 'cwx-project'),
			// 8, 9
			__('Priorities', 'cwx-project'),
			__('On the Priorities tab, you can change the colors for priorities. Simply click on the Select Color buttons and pick a new color on the color picker.', 'cwx-project'),
			// 10, 11
			__('Permissions', 'cwx-project'),
			__('The Permissions tab lists all roles currently available in the WordPress installation as well as all capabilities (permissions) available for CWX Project. To change permissions for a certain role, click on the role name, then check the permissions you want for that role and unckeck those you do not want. You can use the keyboard to navigate through roles with [alt] + [page up]/[page down].', 'cwx-project')
		);
	}
	

	/**
	 * Metabox Help
	 */

	public static function printMetaboxHelp()
	{
		global $post;
		$type = get_post_type_object($post->post_type)->labels->singular_name;
		
		printf(
			 '<p>%1$s</p>'
			.'<p style="font-weight:bold; font-size:1.1em;">%2$s</p>'
			.'<p>%3$s</p>'
			.'<p style="font-weight:bold; font-size:1.1em;">%4$s</p>'
			.'<p>%5$s</p>'
			.'<p><strong>%6$s</strong> - %7$s</p>'
			.'<p><strong>%8$s</strong> - %9$s</p>',
			sprintf(__('You may attach a CWX Project Task to this %1$s. If you do so, you can alter all the Tasks properties as you normally would in the main CWX Project Widget. The first thing you need to do is create a new Task for this %1$s and save the %1$s. Then, after this page has reloaded, the properties of the Task will be shown and some are editable, depending on your permissions.', 'cwx-project'),
				$type),
			// 2
			__('Creating a new Task', 'cwx-project'), 
			// 3
			sprintf(__('To create a new Task for this %1$s, navigate to the CWX Project - Task meta box. There you choose a Project or to create a new Project (if your permissions allow you to). In the latter case, you may also choose to create a private Project (no one else but you can see it). Finally, you can assign yourself to the Task (again, if your permissions allow that). When you are ready, save the %1$s.', 'cwx-project'),
				$type),
			// 4
			__('Task Properties', 'cwx-project'), 
			// 5
			__('Depending on your permissions, you can edit the properties. While most of these properties are self-explanatory, some need an explanation &hellip;', 'cwx-project'),
			// 6, 7
			__('Assignee', 'cwx-project'),
			__('CWX Project automatically detects which users can be Assignees of Tasks. For more comfort in choosing a user, you can type any number of characters in the input field and pick a user from the list that appears. If you enter a users name that cannot be an assignee, no user will be assigned.', 'cwx-project'),
			// 8, 9
			__('Statuses and Categories', 'cwx-project'),
			__('In Status and Category fields you can enter any Status or Category name or pick one from the list that appears. If you enter a name that does not yet exists, the Status or Category will be created as a proposal in the system. Such proposals are available temporarily until an administrator (or a user with permission to manage Statuses/Categories) accepts the proposed item or deletes it.', 'cwx-project')
		);
	}
	
	public static function getMetaboxHelpSidebar()
	{
		return null;
	}
	
}

