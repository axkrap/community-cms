SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Table structure for table `comcms_calendar`
--

CREATE TABLE IF NOT EXISTS `comcms_calendar` (
  `id` int(11) NOT NULL auto_increment,
  `category` int(11) NOT NULL,
  `starttime` time NOT NULL,
  `endtime` time NOT NULL,
  `year` int(4) NOT NULL,
  `month` int(2) NOT NULL,
  `day` int(2) NOT NULL,
  `header` text NOT NULL,
  `description` text,
  `location` text,
  `author` text,
  `image` int(11) default NULL,
  `hidden` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `category` (`category`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `comcms_calendar_categories`
--

CREATE TABLE IF NOT EXISTS `comcms_calendar_categories` (
  `id` int(11) NOT NULL auto_increment,
  `label` text NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `comcms_calendar_categories`
--

INSERT INTO `comcms_calendar_categories` (`id`, `label`, `description`) VALUES
(0, 'Default Category', ''),
(1, 'Other', '');

-- --------------------------------------------------------

--
-- Table structure for table `comcms_config`
--

CREATE TABLE IF NOT EXISTS `comcms_config` (
  `name` text NOT NULL,
  `url` text NOT NULL,
  `comment` text NOT NULL,
  `template` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `comcms_config`
--

INSERT INTO `comcms_config` (`name`, `url`, `comment`, `template`, `active`) VALUES
('Community CMS Default', 'http://localhost/', 'Sourceforge.net', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `comcms_files`
--

CREATE TABLE IF NOT EXISTS `comcms_files` (
  `id` int(11) NOT NULL auto_increment,
  `type` int(11) NOT NULL,
  `label` text NOT NULL,
  `path` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


--
-- Table structure for table `comcms_news`
--

CREATE TABLE IF NOT EXISTS `comcms_news` (
  `id` int(11) NOT NULL auto_increment,
  `page` int(11) default NULL,
  `name` text,
  `description` text,
  `author` text,
  `date` timestamp NULL default NULL on update CURRENT_TIMESTAMP,
  `image` text default NULL,
  PRIMARY KEY  (`id`),
  KEY `page` (`page`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `comcms_news`
--

INSERT INTO `comcms_news` (`id`, `page`, `name`, `description`, `author`, `date`, `image`) VALUES
(0, 1, 'Welcome to Community CMS ALPHA!', '<p>Welcome to Community CMS, the web content system aimed at non-profit organizations and communities. The CMS features a news bulletin board, a calendar, a system for displaying newsletters, and an administration system to make editing your content easy. Now you can edit content too! It works really well.</p>', 'Administrator', '2008-06-20 22:25:38', NULL),
(1, 1, 'AJAX Front-end Content Editing Beta', '<p>Currently in development (but nearly finished): editing contend directly from the front page. BETA available. With this functionality, the admin editing page will not be the only way to edit content. This process is now fully functional! You can even edit from the backend!</p>', 'Administrator', '2008-08-16 12:49:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `comcms_newsletters`
--

CREATE TABLE IF NOT EXISTS `comcms_newsletters` (
  `id` int(11) NOT NULL auto_increment,
  `page` int(11) NOT NULL,
  `year` int(4) NOT NULL default '2008',
  `month` int(2) NOT NULL default '1',
  `label` text character set utf8 collate utf8_unicode_ci,
  `path` text character set utf8 collate utf8_unicode_ci,
  `hidden` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `comcms_notebook`
--

CREATE TABLE IF NOT EXISTS `comcms_notebook` (
  `id` int(11) NOT NULL auto_increment,
  `message` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


--
-- Table structure for table `comcms_pages`
--

CREATE TABLE IF NOT EXISTS `comcms_pages` (
  `id` int(11) NOT NULL auto_increment,
  `title` text NOT NULL,
  `type` int(11) NOT NULL,
  `menu` tinyint(1) NOT NULL,
  `list` int(6) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `comcms_pages`
--

INSERT INTO `comcms_pages` (`id`, `title`, `type`, `menu`, `list`) VALUES
(1, 'Home', 1, 1, 0),
(2, 'Calendar', 3, 1, 1),
(3, 'Newsletters', 2, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `comcms_pagetypes`
--

CREATE TABLE IF NOT EXISTS `comcms_pagetypes` (
  `id` int(4) NOT NULL auto_increment,
  `name` tinytext NOT NULL,
  `description` text NOT NULL,
  `author` tinytext NOT NULL,
  `filename` tinytext NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `comcms_pagetypes`
--

INSERT INTO `comcms_pagetypes` (`id`, `name`, `description`, `author`, `filename`) VALUES
(1, 'News', 'A simple news posting system that acts as the main message centre for Community CMS', 'stephenjust', ''),
(2, 'Newsletter List', 'This pagetype creates a dynamic list of newsletters, sorted by date. It is most useful for a monthly newsletter scenario.', 'stephenjust', ''),
(3, 'Calendar', 'A complex date management system supporting a full month view, week view, day view, and an event view. This pagetype by default displays the current month.', 'stephenjust', ''),
(4, 'Contacts', 'A page where all users whose information is set to be visible will be shown', 'stephenjust', 'contacts.php');

-- --------------------------------------------------------

--
-- Table structure for table `comcms_templates`
--

CREATE TABLE IF NOT EXISTS `comcms_templates` (
  `id` int(3) NOT NULL auto_increment,
  `path` text NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `author` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `comcms_templates`
--

INSERT INTO `comcms_templates` (`id`, `path`, `name`, `description`, `author`) VALUES
(1, 'templates/default/', 'Community CMS Default Template', 'Default template.', 'Stephen J');

-- --------------------------------------------------------

--
-- Table structure for table `comcms_messages`
--

CREATE TABLE IF NOT EXISTS `comcms_messages` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`recipient` INT(5) NOT NULL DEFAULT '1',
	`message` TEXT NOT NULL,
	PRIMARY KEY (`id`)
	) ENGINE = MYISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `comcms_users`
--

CREATE TABLE IF NOT EXISTS `comcms_users` (
  `id` int(5) NOT NULL auto_increment,
  `type` int(2) NOT NULL default '1',
  `username` text NOT NULL,
  `password` text NOT NULL,
  `realname` text NOT NULL,
  `title` text NULL,
  `phone` text NOT NULL,
  `email` text NOT NULL,
  `address` text NOT NULL,
  `phone_hide` BOOL NOT NULL default '1',
  `email_hide` BOOL NOT NULL default '1',
  `address_hide` BOOL NOT NULL default '1',
  `hide` BOOL NOT NULL default '0',
  `message` BOOL NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `comcms_users`
--

INSERT INTO `comcms_users` (`id`, `type`, `username`, `password`, `realname`, `phone`, `email`, `address`, `phone_hide`, `email_hide`, `address_hide`, `message`) VALUES
(1, 1, 'admin', '5f4dcc3b5aa765d61d8327deb882cf99', 'Administrator', '555-555-5555', 'admin@example.com','Unknown',1,1,1,1),
(2, 0, 'user', '5f4dcc3b5aa765d61d8327deb882cf99', 'Default User', '555-555-5555', 'user@example.com','Unknown',1,1,1,0);