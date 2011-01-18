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
		}
		if (contact_add_to_list($_GET['id'],$page_id)) {
			$content .= 'Successfully added contact to the list.<br />'."\n";
		} else {
			$content .= '<span class="errormessage">Error: Failed to add the contact to the list.</span><br />'."\n";
		}
		break;
}

// Get contact list
$contact_list_query = 'SELECT `contacts`.*, `content`.`order`
	FROM `'.CONTACTS_TABLE.'` `contacts`, `'.CONTENT_TABLE.'` `content`
	WHERE `content`.`ref_id` = `contacts`.`id`
	AND `content`.`page_id` = '.$page_id.'
	ORDER BY `content`.`order` ASC';
$contact_list_handle = $db->sql_query($contact_list_query);
$contact_list_rows = $db->sql_num_rows($contact_list_handle);
$list_rows = array();
for ($i = 1; $i <= $contact_list_rows; $i++) {
    $contact_list = $db->sql_fetch_assoc($contact_list_handle);
	$current_row = array();
	$current_row[] = '<input type="checkbox" name="item_'.$contact_list['id'].'" />';
	$current_row[] = $contact_list['id'];
	$current_row[] = $contact_list['name'];

	$current_row[] = '<input type="text" size="3" maxlength="11" name="pri-'.$contact_list['id'].'" value="'.$contact_list['order'].'" />';
	$list_rows[] = $current_row;
} // FOR

$label_array = array('','ID','Name','Order');

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
$content .= '<input type="button" value="Add" onClick="update_cl_manager_add()" />'."\n";

echo $content;

clean_up();
?>
