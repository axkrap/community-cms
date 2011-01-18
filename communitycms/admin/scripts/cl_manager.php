<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2011 Stephen Just
 * @author stephenjust@users.sourceforge.net
 * @package CommunityCMS.main
 */
/**#@+
 * @ignore
 */
define('ADMIN',1);
define('SECURITY',1);
define('ROOT','../../');
/**#@-*/

$content = NULL;
include (ROOT . 'config.php');
include (ROOT . 'include.php');
include (ROOT . 'functions/admin.php');
include (ROOT . 'functions/contacts.php');

initialize('ajax');

if (!$acl->check_permission('adm_contacts_manage') || !$acl->check_permission('admin_access')) {
	die ('You do not have the necessary permissions to access this page.');
}

if (!isset($_GET['page'])) {
	die ('No page ID provided to script.');
} else {
	$page_id = $_GET['page'];
	$page_id = (int)$page_id;
}

if (!isset($_GET['action'])) {
	$_GET['action'] = NULL;
}
if (!isset($_GET['id'])) {
	$_GET['id'] = 0;
}
switch ($_GET['action']) {
	default:
		break;
	case 'add':
		if ($_GET['id'] == 0) {
			$content .= '<span class="errormessage">Error: No contact to add</span><br />'."\n";
			break;
		}
		if (contact_add_to_list($_GET['id'],$page_id)) {
			$content .= 'Successfully added contact to the list.<br />'."\n";
		} else {
			$content .= '<span class="errormessage">Error: Failed to add the contact to the list.</span><br />'."\n";
		}
		break;
	case 'remove':
		if ($_GET['id'] == 0) {
			$content .= '<span class="errormessage">Error: No contact to delete</span><br />'."\n";
			break;
		}
		if (contact_remove_from_list($_GET['id'])) {
			$content .= 'Successfully removed contact from the list.<br />'."\n";
		} else {
			$content .= '<span class="errormessage">Error: Failed to remove the contact from the list.</span><br />'."\n";
		}
		break;
	case 'order':
		if ($_GET['id'] == 0) {
			$content .= '<span class="errormessage">Error: No contact to reorder.</span><br />'."\n";
			break;
		}
		if (!contact_order_list($_GET['id'], $_GET['order'])) {
			$content .= '<span class="errormessage">Error: Failed to change contact order.</span><br />'."\n";
		}
}

// Get contact list
$contact_list_query = 'SELECT `contacts`.*, `content`.`order`, `content`.`id` AS `cnt_id`
	FROM `'.CONTACTS_TABLE.'` `contacts`, `'.CONTENT_TABLE.'` `content`
	WHERE `content`.`ref_id` = `contacts`.`id`
	AND `content`.`page_id` = '.$page_id.'
	ORDER BY `content`.`order` ASC';
$contact_list_handle = $db->sql_query($contact_list_query);
$contact_list_rows = $db->sql_num_rows($contact_list_handle);
$list_rows = array();
$contact_ids = array();
for ($i = 1; $i <= $contact_list_rows; $i++) {
    $contact_list = $db->sql_fetch_assoc($contact_list_handle);
	$current_row = array();
	$contact_ids[] = $contact_list['id'];
	$current_row[] = $contact_list['id'];
	$current_row[] = $contact_list['name'];
	if ($acl->check_permission('contacts_edit_lists')) {
		$current_row[] = '<a href="javascript:update_cl_manager_remove(\''.$contact_list['cnt_id'].'\')">Remove</a>';
	}

	$current_row[] = '<input type="text" size="3" maxlength="11" id="cl_order_'.$contact_list['cnt_id'].'" value="'.$contact_list['order'].'" onBlur="update_cl_manager_order(\''.$contact_list['cnt_id'].'\')" />';
	$list_rows[] = $current_row;
} // FOR

$label_array = array('ID','Name');
if ($acl->check_permission('contacts_edit_lists')) {
	$label_array[] = 'Delete';
}
$label_array[] = 'Order';

$content .= create_table($label_array,$list_rows);
$content .= '<input type="hidden" name="page" value="'.$page_id.'" />'."\n";
$content .= 'Add contact: '."\n";
$cl_query = 'SELECT `id`,`name`
	FROM `'.CONTACTS_TABLE.'`
	ORDER BY `name` ASC';
$cl_handle = $db->sql_query($cl_query);
if ($db->error[$cl_handle] === 1) {
	$content .= '<span class="errormessage">Error: Failed to load contacts.</span><br />'."\n";
	echo $content;
	exit;
}
$num_contacts = $db->sql_num_rows($cl_handle);
if ($num_contacts === 0) {
	$content .= 'No contacts exist. Please create some contacts.<br />'."\n";
	echo $content;
	exit;
}
$content .= '<select name="cl_add_contact" id="cl_add_contact">'."\n";
for ($i = 0; $i < $num_contacts; $i++) {
	$cl_result = $db->sql_fetch_assoc($cl_handle);
	$content .= "\t".'<option value="'.$cl_result['id'].'">'.$cl_result['name'].'</option>';
}
$content .= '</select>'."\n";
$contact_ids = array2csv($contact_ids);
$content .= '<input type="hidden" id="cl_contact_ids" value="'.$contact_ids.'" name="contact_ids" />'."\n";
$content .= '<input type="button" value="Add" onClick="update_cl_manager_add()" /><br />'."\n";

echo $content;

clean_up();
?>
