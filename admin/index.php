<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	define('ROOT','./');
	if($_GET['action'] == 'new_log') {
		$log_message = strip_tags($_POST['message']);
		if(strlen($log_message) > 5) {
			log_action($log_message);
			}
		} // IF 'new_log'

// ----------------------------------------------------------------------------

	// Display log messages
	$log_message_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'logs log, '.$CONFIG['db_prefix'].'users user WHERE log.user_id = user.id ORDER BY log.date DESC LIMIT 5';
	$log_message_handle = $db->query($log_message_query);
	if(!$log_message_handle) {
		$content .= 'Failed to read log messages. '.mysqli_error($db).'<br />';
		}
	$num_messages = $log_message_handle->num_rows;
	$content = '<h1>Administration</h1>';
	$content .= '<h3>Most Recent Activity:</h3>
<table class="log_messages">
<tr>
<th>Date</th><th>Action</th><th>User</th><th>IP</th>
</tr>';
	$rowtype = 1;
	if($num_messages == 0) {
		$content .= '<tr class="row1">
<td colspan="4">No log messages.</td>
</tr>';
		}
	for ($i = 1; $i <= $num_messages; $i++) {
		$log_message = $log_message_handle->fetch_assoc();
		$content .= '<tr class="row'.$rowtype.'">
<td>'.$log_message['date'].'</td><td>'.$log_message['action'].'</td><td>'.$log_message['realname'].'</td><td>'.long2ip($log_message['ip_addr']).'</td>
</tr>';
		if($rowtype == 1) {
			$rowtype = 2;
			} else {
			$rowtype = 1;
			}
		} // FOR $i
	$content .= '</table>';
	$content .= '<form method="post" action="?action=new_log"><input type="text" name="message" /><input type="submit" value="Add Message" /></form>';

// ----------------------------------------------------------------------------

	$content .= '<h3>User Summary:</h3>';
	$user_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'users ORDER BY id DESC';
	$user_handle = $db->query($user_query);
	if($user_handle) {
		$user = $user_handle->fetch_assoc();
		$content .= 'Number of users: '.$user_handle->num_rows.'<br />
Newest user: '.$user['username'];
		}

// ----------------------------------------------------------------------------

	$content .= '<h3>Database Summary:</h3>
Database Version: '.$site_info['db_version'].'<br />
MySQL Version: '.$db->get_server_info(); 
?>