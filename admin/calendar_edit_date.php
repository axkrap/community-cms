<?php
/**
 * Community CMS
 * $Id$
 *
 * @copyright Copyright (C) 2007-2009 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.admin
 */
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
	die ('You cannot access this page directly.');
}

$content = NULL;
switch ($_GET['action']) {
	case 'edit':
		$title = (isset($_POST['title'])) ? addslashes($_POST['title']) : NULL;
		$author = (isset($_POST['author'])) ? addslashes($_POST['author']) : NULL;
		$category = (isset($_POST['category'])) ? $_POST['category'] : NULL;
		$start_time = (isset($_POST['stime'])) ? $_POST['stime'] : NULL;
		$end_time = (isset($_POST['etime'])) ? $_POST['etime'] : NULL;
		$date = (isset($_POST['date'])) ? (int)$_POST['date'] : NULL;

		// Format date for insertion...
		$event_date = (isset($_POST['date'])) ? $_POST['date'] : date('d/m/Y');
		if (!eregi('^[0-1][0-9]/[0-3][0-9]/[1-2][0-9]{3}$',$event_date)) {
			$content .= 'Invalidly formatted date. Use MM/DD/YYYY format.<br />'."\n";
			break;
		}
		$event_date_parts = explode('/',$event_date);
		$year = $event_date_parts[2];
		$month = $event_date_parts[0];
		$day = $event_date_parts[1];

		$ar_content = (isset($_POST['content'])) ? addslashes($_POST['content']) : NULL;
		$location = (isset($_POST['location'])) ? $_POST['location'] : NULL;
		$hide = (isset($_POST['hide'])) ? checkbox($_POST['hide']) : 0;
		$image = (isset($_POST['image'])) ? $_POST['image'] : NULL;
		$id = (int)$_POST['id'];
		if ($start_time == "" || $end_time == "" || $year == "" || $title == "") {
			$content = 'One or more fields was not filled out. Please complete the fields marked with a star and resubmit.<br />'."\n";
			break;
		}
		$stime = explode('-',$start_time);
		$etime = explode('-',$end_time);
		$start_time = parse_time($start_time);
		$end_time = parse_time($end_time);
		if (!$start_time || !$end_time || $start_time > $end_time) {
			$content .= "You did not fill out one or more of the times properly. Please fix the problem and resubmit.";
			break;
		}
		$edit_date_query = 'UPDATE ' . CALENDAR_TABLE . "
			SET category='$category',starttime='$start_time',
			endtime='$end_time',year='$year',month='$month',day='$day',
			header='$title',description='$ar_content',location='$location',
			author='$author',image='$image',hidden='$hide' WHERE id = $id LIMIT 1";
		$edit_date = $db->sql_query($edit_date_query);
		if ($db->error[$edit_date] === 1) {
			$content = 'Failed to edit date information.<br />';
		} else {
			$content = 'Successfully edited date information. '.log_action('Edited date entry on '.$day.'/'.$month.'/'.$year.' \''.stripslashes($title).'\'');
		}
		break;

// ----------------------------------------------------------------------------

	default:
		$get_date_query = 'SELECT * FROM ' . CALENDAR_TABLE . '
			WHERE id = '.(int)$_GET['id'].' LIMIT 1';
		$get_date_handle = $db->sql_query($get_date_query);
		if ($db->sql_num_rows($get_date_handle) == 0) {
			$content = 'Could not find the requested calendar entry.<br />'."\n";
			break;
		}
		$date = $db->sql_fetch_assoc($get_date_handle);
		$content = '<form method="POST" action="?module=calendar_edit_date&action=edit">
			<h1>Edit Calendar Date</h1>
			<table class="admintable">
			<input type="hidden" name="author" value="'.stripslashes($_SESSION['name']).'" />
			<input type="hidden" name="id" value="'.stripslashes($_GET['id']).'" />
			<tr><td width="150" class="row1">*Heading:</td><td class="row1">
			<input type="text" name="title" value="'.stripslashes($date['header']).'" /></td></tr>
			<tr><td width="150" class="row2">Category:</td><td class="row2">
			<select name="category">';
		$category_list_query = 'SELECT cat_id,label FROM ' . CALENDAR_CATEGORY_TABLE . '
			ORDER BY cat_id ASC';
		$category_list_handle = $db->sql_query($category_list_query);
		if ($db->error[$category_list_handle]) {
			$content .= '<option value="error" >'.$db->_print_error_query($category_list_handle).'</option>';
		}
		for ($b = 1; $b <= $db->sql_num_rows($category_list_handle); $b++) {
			$category_list = $db->sql_fetch_assoc($category_list_handle);
			if ($date['category'] == $category_list['cat_id']) {
				$content .= '<option value="'.$category_list['cat_id'].'" selected />'
					.stripslashes($category_list['label']).'</option>';
			} else {
				$content .= '<option value="'.$category_list['cat_id'].'" />'
					.stripslashes($category_list['label']).'</option>';
			}
		}
		$temp = explode(':',$date['starttime']);
		$start_time_temp = mktime((int)$temp[0],(int)$temp[1]);
		$starttime = date($site_info['time_format'],$start_time_temp);
		unset($start_time_temp);
		$temp = explode(':',$date['endtime']);
		$end_time_temp = mktime((int)$temp[0],(int)$temp[1]);
		$endtime = date($site_info['time_format'],$end_time_temp);
		unset($end_time_temp);
		$hidden = checkbox($date['hidden'],1);
		unset($temp);
		$content .= '</select></td></tr>
			<tr><td width="150" class="row1">*Start Time:</td>
			<td class="row1"><input type="text" name="stime"
			value="'.$starttime.'" /></td></tr>
			<tr><td width="150" class="row2">*End Time:</td>
			<td class="row2"><input type="text" name="etime"
			value="'.$endtime.'" /></td></tr>
			<tr><td width="150" class="row1">*Date:</td>
			<td class="row1"><input type="text" name="date" class="datepicker" value="'.$date['month'].'/'.$date['day'].'/'.$date['year'].'" maxlength="10" /></td></tr>
			<tr><td class="row2" valign="top">Description:</td>
			<td class="row2"><textarea name="content" rows="30">'.stripslashes($date['description']).'</textarea></td></tr>
			<tr><td width="150" class="row1">Location:</td><td class="row1"><input type="text" name="location" value="'.stripslashes($date['location']).'" /></td></tr>
			<tr><td width="150" valign="top" class="row2">Image:</td><td class="row2">'.file_list('newsicons',2,$date['image']).'</td></tr>
			<tr><td width="150" class="row1">Hidden:</td><td class="row1"><input type="checkbox" name="hide" '.$hidden.' /></td></tr>
			<tr><td width="150" class="row2">&nbsp;</td><td class="row2"><input type="submit" value="Submit" /></td></tr>
			</table>
			</form>';
		break;
}
?>