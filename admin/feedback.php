<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2007-2012 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */

namespace CommunityCMS;

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}

if (SysConfig::get()->getValue('admin_email') == null) {
    throw new AdminException('You need to set an admin email in the website configuration to use this feature.'); 
}

if (FormUtil::get('send') == 1) {
    $to = 'communitycms-feedback@lists.sourceforge.net';
    switch (FormUtil::post('topic')) {
    default:
        $subject = 'Other comment';
        break;
    case 'bug':
        $subject = 'Bug Report';
        break;
    case 'feature':
        $subject = 'Feature Request';
        break;
    }
    $subject .= ' from '.$_SERVER['SERVER_ADDR'].' ('.$_SERVER['SERVER_NAME'].')';
    $message = FormUtil::post('content');
    $headers = 'From: '.SysConfig::get()->getValue('admin_email').''."\r\n".
    'X-Mailer: PHP/' . phpversion();
    if(mail($to, $subject, $message, $headers)) {
        echo 'Message sent.';
    } else {
        echo 'Failed to send message.';
    }
}

echo '<h1>Send Feedback</h1>'."\n\r";
echo 'Use the form below to send feedback to the people in charge of'."\n\r".
    'developing Community CMS. They would appreciate any feedback regarding'."\n\r".
    'the content management system or any of its features. Remember, never'."\n\r".
    'insert your password into this form. The developers will never ask for it.'."\n\r".
    'Keep in mind that the message you submit will immediately be sent to a'."\n\r".
    'private mailing list that may or may not be completely secure. Also, to'."\n\r".
    'assist with domain specific problems, the address of your website will'."\n\r".
    'also be sent with your message.'."\n\r";

$form = new Form;
$form->set_method('post');
$form->set_target('admin.php?module=feedback&send=1');
$form->add_select('topic', 'Topic', array('bug','feature','comment'), array('Bug Report','Feature Request','Other Comment'));
$form->add_textarea('content', 'Content', null, 'class="mceNoEditor" rows="10" cols="60"');
$form->add_submit('submit', 'Send Message');
echo $form;
