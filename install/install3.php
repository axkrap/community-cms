<?php
  $root = '../';
  $security = 1;
  $nav_bar = "<div align='center'><span style='color: #00CC00;'>Check file permissions</span><hr />\n<span style='color: #00CC00;'>Configure settings</span><hr />\n<span style='color: #CCCC00;'>Download/save config file</span></div>\n";
  $content = "<h1>Installing...</h1>\n";
  $content = $content."Right now, the site name value you entered is not recognized by the installer. To configure the system further, you must use a program such as phpMyAdmin to edit site information.";
  include('../include.php');
	$connect = mysql_connect($_POST['dbhost'],$_POST['dbuser'],$_POST['dbpass']);
	if (!$connect) {
		$content = 'One or more fields was filled out incorrectly. Please hit your browser\'s back button and correct the mistake.';
		} else {
		// Try to open the database that is used by Community CMS.
		$select_db = mysql_select_db($_POST['dbname'],$connect);
		if(!$select_db) {
			$content = 'One or more fields was filled out incorrectly. Please hit your browser\'s back button and correct the mistake.';
			}
		}
  if(!connect) { $content = 'One or more fields was filled out incorrectly. Please hit your browser\'s back button and correct the mistake.'; } else {
    $handle = fopen('./schema/MySQL.sql', "r");
    $query = fread($handle, filesize('./schema/MySQL.sql'));
    fclose($handle);
    $query = explode(';',$query);
    $i = 1;
    $f = count($query);
    while ($i <= $f) {
    mysql_query($query[$i],$connect);
    $i++;
    }
  $content = $content.'<br />Config File:<br />
  <textarea rows="20" cols="120"><?php
	// Security Check
	if ($security != 1) {
		die (\'You cannot access this page directly.\');
		}

	// Communtiy CMS Configuration file
	//
	// Eventually, we will have an install script.
	// For now though, manually configure.

	$CONFIG[\'SYS_PATH\'] = \'\';	// Path to Community CMS on server
	$CONFIG[\'db_host\'] = \''.$_POST['dbhost'].'\';		// MySQL server host (usually localhost)
	$CONFIG[\'db_user\'] = \''.$_POST['dbuser'].'\';			// MySQL database user
	$CONFIG[\'db_pass\'] = \''.$_POST['dbpass'].'\';			// MySQL database password
	$CONFIG[\'db_name\'] = \''.$_POST['dbname'].'\';		// MySQL database
	$CONFIG[\'db_prefix\'] = \''.$_POST['dbpfix'].'\';		// Prefix for database tables (must be "comcms_")

	// Set the value below to \'1\' to disable Community CMS
	$CONFIG[\'disabled\'] = 0;
?></textarea><br />
Install Successful. Please copy the above text to your config.php file and delete the install folder.';} 
?>