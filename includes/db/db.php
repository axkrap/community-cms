<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2013 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.database
 */
// Security Check
if (@SECURITY != 1) {
    die ('You cannot access this page directly.');
}

/**
 * Primary Data-Base Abstraction Layer class
 * @package CommunityCMS.database
 */
abstract class db {
    /**
     * Data-Base system to use
     */
    var $dbms = '';
    /**
     * Name of Data-Base connection
     */
    var $db_connect_id = '';
    /**
     * Database connection resource
     */
    var $connect = NULL;
    /**
     * Stores number of executed queries
     */
    var $query_count = 1;
    /**#@+
     * Stores an array of all executed queries
	 * @var array
     */
    var $query = array();
	var $query_text = array();
    var $error = array();
	var $errormsgs = array();
	/**#@-*/

	function print_query_stats() {
		return '<p>Number of queries: '.($this->query_count - 1).'</p>';
	}

	function print_queries() {
		$queries = $this->query_text;
		$return = NULL;
		foreach($queries AS $querynum => $query) {
			if ($this->error[$querynum] === 1) {
				$return .= '<p><span style="color: #CC0000;">'.$query.'</span></p>'."\n";
				if (isset($this->errormsgs[$querynum])) {
					$return .= '<p><em>'.$this->errormsgs[$querynum].'</em></p>'."\n";
				}
			} else {
				$return .= '<p>'.$query.'</p>'."\n";
			}
		}
		return $return;
	}

	/**#@+
	 * Abstract functions defined by child classes
	 */
    abstract function sql_connect();
	abstract function sql_server_info();
	abstract function sql_query($query);
	abstract function sql_num_rows($query);
	abstract function sql_affected_rows($query);
	abstract function sql_fetch_assoc($query);
	abstract function sql_fetch_row($query);
	abstract function sql_escape_string($string);
	abstract function sql_insert_id($table,$field);
	abstract function sql_prepare($name,$query);
	abstract function sql_prepare_exec($name,$variables,$datatypes);
	abstract function sql_prepare_close($name);
    abstract function sql_close();
	/**#@-*/
}

class SQLException extends Exception {}

require(ROOT.'includes/db/db_'.$CONFIG['db_engine'].'.php');
$db_class = 'db_'.$CONFIG['db_engine'];
$db = new $db_class;
?>