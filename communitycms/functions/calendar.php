<?php
/**
 * Community CMS
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
// Security Check
if (@SECURITY != 1) {
	die ('You cannot access this page directly.');
}

// ----------------------------------------------------------------------------

/**
 * Create calendar event entry
 * @global acl $acl
 * @global db $db
 * @global debug $debug
 * @global Log $log
 * @param string $title
 * @param string $description
 * @param string $author
 * @param string $start_time
 * @param string $end_time
 * @param string $date
 * @param integer $category
 * @param string $location
 * @param string $image
 * @param boolean $hide
 * @return integer See return codes in embedded comment
 */
function event_create($title,$description,$author,$start_time,$end_time,
		$date,$category,$location,$image,$hide) {
	global $acl;
	global $db;
	global $debug;
	global $log;

	/*
	 * Return codes:
	 * 1 - Success
	 * 2 - Insufficient permissions
	 * 3 - Invalidly formatted date
	 * 4 - Missing field
	 * 5 - Incorrect start or end time
	 * 6 - Query failed
	 */
	if (!$acl->check_permission('date_create')) {
		$debug->add_trace('Insufficient permissions',true);
		return 2;
	}

	// Add location to list of saved locations
	location_add($location);

	$location = addslashes($location);
	$title = addslashes($title);
	$description = addslashes(remove_comments($description));
	$author = addslashes($author);

	if ($date == '') {
		$date = date('d/m/Y');
	}
	if (!preg_match('#^[0-1][0-9]/[0-3][0-9]/[1-2][0-9]{3}$#',$date)) {
		$debug->add_trace('Invalid date format',true);
		return 3;
	}
	$event_date_parts = explode('/',$date);
	$year = $event_date_parts[2];
	$month = $event_date_parts[0];
	$day = $event_date_parts[1];

	if ($start_time == "" || $end_time == "" || $year == "" || $title == "") {
		$debug->add_trace('One or more fields was not filled out',true);
		return 4;
	}
	$stime = explode('-',$start_time);
	$etime = explode('-',$end_time);
	$start_time = parse_time($start_time);
	$end_time = parse_time($end_time);
	if (!$start_time || !$end_time || $start_time > $end_time) {
		$debug->add_trace('Invalid event times',true);
		return 5;
	}
	$create_date_query = 'INSERT INTO ' . CALENDAR_TABLE . '
		(category,starttime,endtime,year,month,day,header,description,
		location,author,image,hidden)
		VALUES ("'.$category.'","'.$start_time.'","'.$end_time.'",
		'.$year.','.$month.','.$day.',"'.$title.'","'.$description.'",
		"'.$location.'","'.$author.'","'.$image.'",'.$hide.')';
	$create_date = $db->sql_query($create_date_query);
	if ($db->error[$create_date] === 1) {
		return 6;
	}
	$log->new_message('New date entry on '.$day.'/'.$month.'/'
		.$year.' \''.stripslashes($title).'\'');
	return 1;
}

/**
 * Create a calendar event category
 * @global db $db
 * @global debug $debug
 * @global Log $log
 * @param string $label Name of category
 * @param string $icon Name of PNG icon file (minus .png extension)
 * @param string $description Unused currently
 * @return boolean 
 */
function event_cat_create($label,$icon,$description = NULL) {
	global $db;
	global $debug;
	global $log;

	$label = addslashes($label);
	if (strlen($label) < 1) {
		$debug->add_trace('Category name is too short',true);
		return false;
	}
	if (strlen($icon) < 1) {
		$debug->add_trace('Icon selection is invalid',true);
		return false;
	}
	$query = 'INSERT INTO `'.CALENDAR_CATEGORY_TABLE.'`
		(`label`,`colour`)
		VALUES
		(\''.$label.'\',\''.$icon.'\')';
	$handle = $db->sql_query($query);
	if($db->error[$handle] === 1) {
		$debug->add_trace('Failed to create category',true);
		return false;
	}
	$log->new_message('Created event category \''.stripslashes($label).'\'');
	return true;
}

/**
 * Add a location to the list of saved locations
 * @global acl $acl
 * @global db $db
 * @global debug $debug
 * @global Log $log
 * @param string $location
 * @return boolean Success
 */
function location_add($location) {
	global $acl;
	global $db;
	global $debug;
	global $log;

	$location = addslashes($location);

	// Check if location saving is disabled
	if (get_config('calendar_save_locations') != 1) {
		return false;
	}
	if (strlen($location) < 2) {
		$debug->add_trace('No location given',false);
		return false;
	}

	$check_dupe_query = 'SELECT `value` FROM `'.LOCATION_TABLE.'`
		WHERE `value` = \''.$location.'\'';
	$check_dupe_handle = $db->sql_query($check_dupe_query);
	if ($db->error[$check_dupe_handle] === 1) {
		$debug->add_trace('Failed to check for duplicate entries',true);
		return false;
	}
	if ($db->sql_num_rows($check_dupe_handle) != 0) {
		$debug->add_trace('Location \''.$location.'\' already exists',false);
		return false;
	}
	$new_loc_query = 'INSERT INTO `'.LOCATION_TABLE.'`
		(`value`) VALUES (\''.$location.'\')';
	$new_loc_handle = $db->sql_query($new_loc_query);
	if ($db->error[$new_loc_handle] === 1) {
		$debug->add_trace('Failed to create new location',true);
		return false;
	}
	$log->new_message('Created new location \''.$location.'\'');
	return true;
}

// ----------------------------------------------------------------------------

/**
 * delete_category - Delete a calendar category entry
 * @global db $db
 * @global debug $debug
 * @param integer $id
 * @return boolean
 */
function delete_category($id) {
	global $db;
	global $debug;
	// Validate parameters
	if (!is_numeric($id)) {
		$debug->add_trace('Invalid ID given',true);
		return false;
	}

	$check_if_last_query = 'SELECT * FROM `'.CALENDAR_CATEGORY_TABLE.'` LIMIT 2';
	$check_if_last_handle = $db->sql_query($check_if_last_query);
	if ($db->error[$check_if_last_handle] === 1) {
		$debug->add_trace('Failed to check if you are trying to delete the last category',false);
		return false;
	}
	if ($db->sql_num_rows($check_if_last_handle) == 1) {
		$debug->add_trace('Cannot delete last entry',true);
		return false;
	}

	$check_category_query = 'SELECT * FROM `'. CALENDAR_CATEGORY_TABLE .'`
		WHERE `cat_id` = '.$id.' LIMIT 1';
	$check_category_handle = $db->sql_query($check_category_query);
	if ($db->error[$check_category_handle] === 1) {
		$debug->add_trace('Failed to read category information. Does it exist?',false);
		return false;
	}
	if ($db->sql_num_rows($check_category_handle) == 1) {
		$delete_category_query = 'DELETE FROM `'.CALENDAR_CATEGORY_TABLE.'`
			WHERE `cat_id` = '.$id;
		$delete_category = $db->sql_query($delete_category_query);
		if ($db->error[$delete_category] === 1) {
			$debug->add_trace('Failed to perform delete operation',true);
			return false;
		} else {
			$check_category = $db->sql_fetch_assoc($check_category_handle);
			log_action('Deleted category \''.$check_category['label'].'\'');
			return true;
		}
	} else {
		return false;
	}
}

// ----------------------------------------------------------------------------

/**
 * delete_date - Delete a calendar entry
 * @global db $db
 * @global debug $debug
 * @param integer $id
 * @return boolean
 */
function delete_date($id) {
	global $db;
	global $debug;
	// Validate parameters
	if (!is_numeric($id)) {
		$debug->add_trace('Invalid ID given',true);
		return false;
	}

	$read_date_info_query = 'SELECT * FROM ' . CALENDAR_TABLE . '
		WHERE `id` = '.$id;
	$read_date_info_handle = $db->sql_query($read_date_info_query);
	if ($db->error[$read_date_info_handle] === 1) {
		$debug->add_trace('Failed to read date information. Does it exist?',false);
		return false;
	} else {
		$del_query = 'DELETE FROM ' . CALENDAR_TABLE . '
			WHERE `id` = '.$id;
		$del_handle = $db->sql_query($del_query);
		$read_date_info = $db->sql_fetch_assoc($read_date_info_handle);
		if ($db->error[$del_handle] === 1) {
			return false;
		} else {
			log_action('Deleted calendar date \''.$read_date_info['header'].'\'');
			return true;
		}
	}
}

// ----------------------------------------------------------------------------

// FIXME: This doesn't work yet
function monthcal_get_date($day,$month,$year,$template) {
	global $calendar_settings;
	global $db;
	global $debug;
	global $page;

	$dates_query = 'SELECT * FROM `' . CALENDAR_TABLE . '` `date`,
		`' . CALENDAR_CATEGORY_TABLE . '` `cat`
		WHERE `date`.`month` = \''.$month.'\' AND `date`.`year` = \''.$year.'\'
		AND `date`.`day` = \''.$day.'\' AND `date`.`category` =
		`cat`.`cat_id` ORDER BY `starttime` ASC';
	$dates_handle = $db->sql_query($dates_query);
	unset($dates_query);

	if ($db->error[$dates_handle] === 1) {
		$debug->add_trace('Failed to read date information',true);
		return 'Error';
	}
	if ($db->sql_num_rows($dates_handle) > 0) {
		$template->day_number = '<a href="?'.$page->url_reference
			.'&amp;view=day&amp;m='.$month.'&amp;y='.$year.'&amp;d='
			.$day.'" class="day_number">'.$day.'</a>';
	} else {
		$template->day_number = $day;
	}
	$dates = NULL;
	for ($i = 1; $i <= $db->sql_num_rows($dates_handle); $i++) {
		$day_info = $db->sql_fetch_assoc($dates_handle);
		if($day_info['colour'] == '') {
			$day_info['colour'] = 'red';
		}
		$dates .= '<a href="?'.$page->url_reference.'&amp;view=event&amp;'
			.'a='.$day_info['id'].'" class="calendar_event">';
		if ($calendar_settings['month_show_cat_icons'] == 1) {
			$dates .= '<img src="<!-- $IMAGE_PATH$ -->icon_'.$day_info['colour'].'.png"'
			.' width="10px" height="10px" alt="'.$day_info['label'].'" border="0px" />';
		}
		if ($calendar_settings['month_show_stime'] == 1) {
			$stime_tmp = explode(':',$day_info['starttime']);
			$stime_tmp = mktime($stime_tmp[0],$stime_tmp[1]);
			$dates .= '<span class="calendar_event_starttime">'.date('g:ia',$stime_tmp).'</span> ';
		}
		$dates .= $day_info['header'].'</a><br />'."\n";
	}
	$template->day_events = $dates;
	return (string)$template;
}

function monthcal_day_strings($template) {
	if (!is_object($template)) {
		return $template;
	}

	// Insert date labels
	// Settings:
	// calendar_month_day_format
	// 1 - Use full name
	// 0 - Use abbreviation
	if (get_config('calendar_month_day_format') == 1) {
		$template->cal_sunday = 'Sunday';
		$template->cal_monday = 'Monday';
		$template->cal_tuesday = 'Tuesday';
		$template->cal_wednesday = 'Wednesday';
		$template->cal_thursday = 'Thursday';
		$template->cal_friday = 'Friday';
		$template->cal_saturday = 'Saturday';
	} else {
		$template->cal_sunday = 'Sun';
		$template->cal_monday = 'Mon';
		$template->cal_tuesday = 'Tues';
		$template->cal_wednesday = 'Wed';
		$template->cal_thursday = 'Thurs';
		$template->cal_friday = 'Fri';
		$template->cal_saturday = 'Sat';
	}
	return $template;
}
?>
