<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.admin
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2007-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}

if (!acl::get()->check_permission('adm_user_edit')) {
    throw new AdminException('You do not have the necessary permissions to access this module.'); 
}

try {
    $user = new User(FormUtil::get('id'));
} catch (Exceptions\ContentNotFoundException $ex) {
    die('Unable to find the specified user in the database.');
}

if (FormUtil::get('action') == 'edit') {
    if (FormUtil::post('edit_old_pass') != "" &&
        FormUtil::post('edit_pass') == FormUtil::post('edit_pass_conf'))
    {
        try {
            $user->changePassword(FormUtil::post('edit_old_pass'), FormUtil::post('edit_pass'));
            echo 'Password changed.<br />';
        } catch (UserException $ex) {
            echo '<span class="errormessage">Failed to change password: '.$ex->getMessage().'</span><br />';
        }
    } else {
        echo 'Password not changed.<br />';
    }
    try {
        User::edit(
            FormUtil::get('id'),
            FormUtil::post('first_name'),
            FormUtil::post('surname'),
            FormUtil::post('telephone'),
            FormUtil::post('address'),
            FormUtil::post('email'),
            FormUtil::post('title'),
            FormUtil::postArray('groups'));
        echo 'Successfully updated user information.';
    } catch (\Exception $ex) {
        echo '<span class="errormessage">'.$ex->getMessage().'</span>';
    }
} else { // IF 'edit'

    // ----------------------------------------------------------------------------

    $current_name = explode(', ', $user->getName());
    if(!isset($current_name[1])) {
        $current_name[1] = null;
    }
    $tab_layout = new Tabs;
    $form = new Form;
    $form->set_target('admin.php?module=user_edit&id='.FormUtil::get('id').'&action=edit');
    $form->set_method('post');
    $form->add_password('edit_pass', 'New Password');
    $form->add_password('edit_pass_conf', 'Confirm Password');
    $form->add_password('edit_old_pass', 'Old Password');
    $form->add_text(
        'If the above password fields are filled correctly,
                    your password will be changed. Leave the password fields empty
                    if you do not want to change your password.'
    );
    $form->add_textbox('first_name', 'First Name', $current_name[1]);
    $form->add_textbox('surname', 'Surname', $current_name[0]);
    $form->add_textbox('title', 'Title/Position', $user->getTitle());
    $form->add_textbox('telephone', 'Phone Number', $user->getPhone());
    $form->add_textbox('address', 'Address', $user->getAddress());
    $form->add_textbox('email', 'Email Address', $user->getEmail());
    $group_list_query = 'SELECT * FROM ' . USER_GROUPS_TABLE . ' ORDER BY name ASC';
    $user_groups = UserGroup::getAll();
    if (count($user_groups) == 0) {
        $form->add_text(' An error may have occured. No groups were found.');
    } else {
        $group_list_id = $group_list_name = [];
        foreach ($user_groups as $user_group) {
            $group_list_id[] = $user_group->getId();
            $group_list_name[] = $user_group->getLabel();
        }
        $form->add_multiselect('groups', 'Groups', $group_list_id, $group_list_name, implode(',',$user->getGroups()), 5, 'style="height: 4em;"');
    }
    $form->add_submit('submit', 'Edit User');
    $tab_content['edit'] = $form;
    $tab_layout->add_tab('Edit User', $tab_content['edit']);
    echo $tab_layout;
}
