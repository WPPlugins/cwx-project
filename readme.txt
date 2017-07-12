=== CWX Project ===
Contributors: CWX-Chrome
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=G6CSWPS5UMS2Y
Tags: projects, project management, project manager, manager, management, organizer, todo, to do, todo list, to do list, tasks, task lists, task management, task manager, time tracking, scheduler, widget, dashboard, dashboard widget, collaboration
Requires at least: 3.8
Tested up to: 4.0
Stable tag: 1.0.2
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

CWX Project is an easy to use Project Management and ToDo-List application for Wordpress Blogs, encouraging collaborative work. 

== Description ==

CWX Project is an easy to use Project Management and ToDo-List application for Wordpress Blogs, encouraging collaborative work. 

= Manage your Time, don't waste it! =

Everyone of us uses software that steals time we'd rather put into something else. Sure, we need tools to keep our computers and phones running, but do they get us closer to a real goal? No, the tools are mostly just consuming precious time.

CWX Project has been made to get you closer to your goals without wasting your time. Its user interface is quick and intuitive, and it is where it should be: right on the Dashboard of WordPress. It tries to keep management tasks, needed for itself, as low as possible.

= For the Blogger =
Are you a busy blogger? Have you ever lost overview over what you wanted to blog about? You need to organize your blogging life - or even beyond. Why not try CWX Project?

= For Multi Author Blogs =
Loosely managed multi author Blogs are going nowhere, right? ToDo-List applications that are too complicated, too ugly, too whatever, won't be used much or not at all. It's about time to set everyones focus back on the important stuff. Try CWX Project and see what it can do for your Blog.

= For the Developer =
Most developers use one or the other ToDo-List application to not forget something important, be it a minor or a major task to do. CWX Project is not specialized on a developers daily requirements - there might be more features developers need -, but there is hardly anything easier and quicker to use than CWX Project. Check it out today!

= Still not convinced? Check the list of highlights =

* Project folders
* Tasks (ToDo-Lists) organized in Project folders
* Custom categories, user proposed categories, category management
* Custom status, user proposed status, status management
* Task priorities (numbers 1 - 9 with individual colors, customizable)
* Project and task progress bars
* Assign users to tasks
* Due dates
* Time spent in minutes, hours and days
* Detailed description of tasks
* Permission management (uses roles and capabilities)
* Assign and edit tasks for posts, pages and/or any other post type
* Accessibility: Screen Reader support, fully keyboard controllable
* Translation ready and Right To Left reading support
* Responsive Design
* and more ...

= Browser compatibility =
**CWX Project requires JavaScript to be enabled!**

* Windows: Chrome, Firefox, Safari 5.1.7 and IE 11/10/9
* Linux: Chrome, Firefox
* Android: Chrome, Firefox, Browser, Dolphin
* (OSX/iOS: untested, please report if it works)
* Other browsers may or may not work

**Known Issues:**

* IE 9 as well as some other browsers do not show autocomplete boxes.
* Some browsers, such as Safari 5.1.7 and Android Browser render progress bars, but don't support them

== Installation ==

1. Install CWX Project either via the WordPress.org plugin directory, or by uploading the complete folder 'cwx-project' to the plugins directory of your server.
2. Activate CWX Project through the 'Plugins' menu in WordPress.
3. Navigate to the Dashboard of WordPress.
4. Move the Widget to a place, where it can be seen instantly.
5. Read the Help (differs on every page that contains plugin components).
6. Configure CWX Project and create your first Project and Tasks.

== Frequently Asked Questions ==

= Why doesn't it work in my browser? =

If you have disabled JavaScript in your browser, CWX Project will refuse to work.

IE 8 and older are not supported. Support for IE 8 has been dropped on Jun., 1st 2014.

Other browsers that do not work with CWX Project are too old and/or have insufficient support for HTML5 and/or CSS3. CWX Project uses HTML5 for markup, but it has fallbacks for browsers that don't understand it, so it's very unlikely a show-stopper. However, some browsers do not handle the HTML5 progress-tag right, which looks ugly. CWX Project uses CSS3, but implements fallbacks for cases a browser doesn't know a certain property or value. The most problematic one is 'width: calc();', which is used to compute the dynamic width of project and task titles (the fallback here is just 'width: 45%;', which can lead to ugly looking rows).

= Something does not work right, what should I do? =

Post a little report under the support tab of the plugin page. It's very likely that the issue will be fixed within reasonable time.

For a first release it's only normal to have bugs. If you report the bugs, they will be eliminated much faster.

= What about feature xyz? Will it be implemented sometimes soon? =

Well, if I promised to implement a feature, I definately will. But understand, my time is somewhat limited. Besides that, it depends on how many people are interested in the plugin. Time will tell.

= I want to change the CSS/JavaScript. Where is the source code? =

At the moment CWX Project only ships with minified versions of the CSS and JavaScript files. I'm not yet sure about a release with the source files included. I promise, their code is very clean and does not contain anything I want to hide. I have my reasons, sorry!

== Screenshots ==

1. Form to create a new project on the Dashboard Widget of CWX Project.

2. First project expanded, showing its tasks; further projects below.

3. A task expanded, showing its properties.

4. Editing a project folder.

5. The trash can view of the Dashboard Widget, with delete action selected.

6. The trash can view of the Dashboard Widget, with restore action selected.

7. The settings view of the Dashboard Widget, showing the permissions tab.

8. The settings view of the Dashboard Widget, showing the priorities tab.

9. The settings view of the Dashboard Widget, showing the post types tab.

10. The settings view of the Dashboard Widget, showing the categories tab. The statuses tab looks very similar.

11. The Task box on an Edit Post page, attaching a task to the *Post*. Workflow is from the top left to the bottom. On the right the task has been created. The Task box can be shown for any available post type (see figure 9).

== Localizations ==

**CWX Project comes with the following translations:**

* English (en_US, default)
* German (de_DE)

*Please provide your translation if you have made one. It will be packed into the next release and you will be credited. Thank you!*

== Changelog ==

= 1.0.2 =
* Fixed a showstopper that was introduced in version 1.0.1
* Replaced PHP 5.3 functions by code compatible with PHP 5.2.4 - PHP 5.3 is not required anymore.

= 1.0.1 =
* Security hardened for plugin activation/deactivation and uninstall.
* Fixed bug in '_install' method.
* Fixed issues with '*printf' functions, replaced by 'strtr'.
* Fixed Task progress in title tag not showing if 0%, after editing task.
* Fixed creator name not showing for newly added tasks.
* Improved sorting; Tasks and Projects that are done, are now last in order (after page reload).
* Accessibility: Improved tabbing on settings page; tab through with shift + ctrl + left/right.
* Accessibility: Added keyboard control to settings/permissions sheet; navigate roles with alt + page up/down
* Updated the Help text for settings page to reflect changes.

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.2 =
Maintenance update. This update is required! Version 1.0.1 is not fully functional. Please see the changelog.

= 1.0.1 =
Maintenance update. It is strongly recommended to install this update. Please see the changelog.

= 1.0 =
No upgrade available.

