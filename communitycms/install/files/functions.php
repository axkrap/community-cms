<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2010 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.install
 */

/**
 * Check if a library of functions is present
 *
 * This function allows more complex verification without turning the install
 * file into a mess. Tests for specific library versions could be tested for
 * later, or tests for specific functions that would impair functionality if
 * not present.
 *
 * @param string $library Library name
 * @return boolean
 */
function check_library($library) {
	switch ($library) {
		default:
			return false;
			break;

		case 'mysqli':
			if (function_exists('mysqli_connect')) {
				return true;
			} else {
				return false;
			}
			break;

		case 'postgresql':
			if (function_exists('pg_connect')) {
				return true;
			} else {
				return false;
			}
			break;

		case 'gd':
			if (function_exists('imageCreateTrueColor')) {
				return true;
			} else {
				return false;
			}
	}
}

/**
 * Write to config.php
 *
 * Using this function allows the formatting of the configuration file to be
 * separated from the installation script and removes the need to maintain
 * the formatting for the configuration file in multiple locations.
 *
 * @param string $engine Database engine
 * @param string $host Database host
 * @param integer $port Database host port
 * @param string $database_name Database name
 * @param string $database_user Database user
 * @param string $password Database user's password
 * @param string $table_prefix Prefix for database tables
 * @return boolean
 */
function config_file_write($engine,$host,$port,$database_name,
		$database_user,$password,$table_prefix) {
	// Validate parameters
	if (!is_numeric($port)) {
		return false;
	}
	$port = (int)$port;
	$engine = addslashes($engine);
	$host = addslashes($host);
	$database_name = addslashes($database_name);
	$database_user = addslashes($database_user);
	$password = addslashes($password);
	$table_prefix = addslashes($table_prefix);

	$config_file = ROOT.'config.php';

	if (!file_exists($config_file)) {
		return false;
	}

	$file_handle = fopen($config_file,'w');
	if (!$file_handle) {
		// Failed to open file for writing
		return false;
	}

	$config_file = <<< END
<?php
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}
// Turn of 'register_globals'
ini_set('register_globals',0);
\$CONFIG['SYS_PATH'] = 'Unused'; // Path to Community CMS on server
\$CONFIG['db_engine'] = '$engine'; // Database Engine
\$CONFIG['db_host'] = '$host'; // Database server host (usually localhost)
\$CONFIG['db_host_port'] = $port; // Database server port (default 3306 for mysqli)
\$CONFIG['db_user'] = '$database_user'; // Database user
\$CONFIG['db_pass'] = '$password'; // Database password
\$CONFIG['db_name'] = '$database_name'; // Database
\$CONFIG['db_prefix'] = '$table_prefix'; // Database table prefix

// Set the value below to '1' to disable Community CMS
\$CONFIG['disabled'] = 0;
?>
END;
	if (fwrite($file_handle,$config_file)) {
		fclose($file_handle);
		return true;
	} else {
		fclose($file_handle);
		return false;
	}
}
?>
