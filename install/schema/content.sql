INSERT INTO `<!-- $DB_PREFIX$ -->acl` (`acl_id`, `group`, `value`) VALUES
(1, 1, 1);;
INSERT INTO `<!-- $DB_PREFIX$ -->calendar_categories` (`label`, `colour`, `description`) VALUES
('Default Category', 'red', ''),
('Other', 'yellow', '');;
INSERT INTO `<!-- $DB_PREFIX$ -->config` (`config_name`, `config_value`) VALUES
('admin_email','<!-- $ADMIN_EMAIL$ -->'),
('calendar_month_day_format','1'),
('calendar_default_view','month'),
('calendar_month_show_cat_icons','1'),
('calendar_month_show_stime','1'),
('calendar_month_time_sep',' '),
('calendar_save_locations','1'),
('comment','Downloaded from SourceForge.net'),
('contacts_display_mode','card'),
('cookie_name','cms_session'),
('cookie_path','/'),
('db_version', '0.05'),
('footer','<a href="http://sourceforge.net"><img src="http://sflogo.sourceforge.net/sflogo.php?group_id=223968&amp;type=1" width="88" height="31" border="0" type="image/png" alt="SourceForge.net Logo" /></a><br />Powered by Community CMS'),
('gallery_app','built-in'),
('home','1'),
('news_default_date_setting','1'),
('news_default_publish_value','0'),
('news_num_articles','10'),
('news_show_author','1'),
('news_show_edit_time','0'),
('password_expire','0'),
('site_active','1'),
('site_name','<!-- $SITE_NAME$ -->'),
('site_template','1'),
('site_url','http://localhost/'),
('time_format','h:i A'),
('tel_format','(###) ###-####');;
INSERT INTO `<!-- $DB_PREFIX$ -->dir_props` (`directory`, `property`, `value`) VALUES
('newsicons','icons_only',1);;
INSERT INTO `<!-- $DB_PREFIX$ -->news` (`page`, `name`, `description`, `author`, `date`, `image`) VALUES
(1, 'Welcome to Community CMS ALPHA!', '<p>Welcome to Community CMS, the web content system aimed at non-profit organizations and communities. The CMS features a news bulletin board, a calendar, a system for displaying newsletters, a contact information managing tool, and an administration system to make editing your content easy. To see what\'s new in this release, click <a href="http://communitycms.sourceforge.net/whatsnew-0.6.html">here</a>.</p>', 'Administrator', '2008-06-20 22:25:38', NULL);;
INSERT INTO `<!-- $DB_PREFIX$ -->pages` (text_id, title, meta_desc, type, menu, list, hidden) VALUES
('home', 'Home', '', 1, 1, 0, 0),
('calendar', 'Calendar', '', 3, 1, 1, 0),
('newsletters', 'Newsletters', '', 2, 1, 2, 0);;
INSERT INTO `<!-- $DB_PREFIX$ -->page_groups` (`label`) VALUES
('Default Group');;
INSERT INTO `<!-- $DB_PREFIX$ -->pagetypes` (`id`, `name`, `description`, `author`, `filename`, `class`) VALUES
(1, 'News', 'A simple news posting system that acts as the main content distribution system for Community CMS', 'stephenjust', 'news.php', 'NewsPage'),
(2, 'Newsletter List', 'This pagetype creates a dynamic list of newsletters, sorted by timestamp. It is most useful for a monthly newsletter scenario.', 'stephenjust', 'newsletter.php', 'GenericPage'),
(3, 'Calendar', 'A complex timestamp management system supporting a full month view, day view, and an event view. This pagetype by default displays the current month.', 'stephenjust', 'calendar.php', 'GenericPage'),
(4, 'Contacts', 'A page where all users whose information is set to be visible will be shown', 'stephenjust', 'contacts.php', 'GenericPage');;
INSERT INTO `<!-- $DB_PREFIX$ -->templates` (`id`, `path`, `name`, `description`, `author`) VALUES
(1, 'templates/default/', 'Community CMS Default Template', 'Default template.', 'Stephen J');;
INSERT INTO `<!-- $DB_PREFIX$ -->user_groups`
(`name`,`label_format`) VALUES
('Administrator','font-weight: bold; color: #009900;');;
INSERT INTO `<!-- $DB_PREFIX$ -->users`
(id, type, username, password, groups, realname, phone, email, address) VALUES
(1, 1, '<!-- $ADMIN_USER$ -->', '<!-- $ADMIN_PWD$ -->', '1', 'Administrator', '555-555-5555', 'admin@example.com','Unknown'),
(2, 0, 'user', '5f4dcc3b5aa765d61d8327deb882cf99', NULL, 'Default User', '555-555-5555', 'user@example.com','Unknown')