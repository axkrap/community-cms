<?php
// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}
$root = "./";
$content = NULL;
$date = date('Y-m-d H:i:s');
if ($_GET['action'] == 'delete') {
    $read_article_query = 'SELECT news.id,news.name,page.title FROM '.$CONFIG['db_prefix'].'news news, '.$CONFIG['db_prefix'].'pages page WHERE news.id = '.$_GET['id'].' AND news.page = page.id LIMIT 1';
    $read_article_handle = $db->query($read_article_query);
    if (!$read_article_handle) {
        $content .= 'Failed to read article information.<br /> '.mysqli_error($db);
    }
    if ($read_article_handle->num_rows == 1) {
        $delete_article_query = 'DELETE FROM '.$CONFIG['db_prefix'].'news WHERE id = '.$_GET['id'];
        $delete_article = $db->query($delete_article_query);
        if (!$delete_article) {
            $content .= 'Failed to delete article. <br />'.mysqli_error($db);
        } else {
            $read_article = $read_article_handle->fetch_assoc();
            $content .= 'Successfully deleted article. <br />'.log_action('Deleted news article \''.stripslashes($read_article['name']).'\' from \''.addslashes($read_article['title']).'\'');
        }
    } else {
        $content .= 'Could not find the article you asked to delete.<br />';
    }
} // IF 'delete'

// ----------------------------------------------------------------------------

$tab_layout = new tabs;

$page_list = '<select name="page">';
$page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages 
    WHERE type = 1 ORDER BY list ASC';
$page_query_handle = $db->query($page_query);
for ($i = 1; $i <= $page_query_handle->num_rows; $i++) {
    $page = $page_query_handle->fetch_assoc();
    if (!isset($_POST['page'])) {
        $_POST['page'] = $page['id'];
    }
    if ($page['id'] == $_POST['page']) {
        $page_list .= '<option value="'.$page['id'].'" selected />'.
            $page['title'].'</option>';
    } else {
        $page_list .= '<option value="'.$page['id'].'" />'.
            $page['title'].'</option>';
    }
    $pages[$i] = $page['id'];
} // FOR $i
if ($_POST['page'] == 0) {
    $no_page = 'selected';
} else {
    $no_page = NULL;
}
if ($_POST['page'] == '*') {
    $all_page = 'selected';
} else {
    $all_page = NULL;
}
$page_list .= '<option value="0" '.$no_page.'>No Page</option>
    <option value="*" '.$all_page.'>All Pages</option>
    </select>';
$tab_content['manage'] = '<table class="admintable">
    <tr><th colspan="4"><form method="POST" action="admin.php?module=news">'.
    $page_list.'<input type="submit" value="Change Page" /></form></th></tr>
    <tr><th width="30">ID</th><th>Title:</th><th colspan="2"></th></tr>';
// Get page list in the order defined in the database. First is 0.
if ($_POST['page'] == '*') {
    $page_list_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'news ORDER BY id ASC';
} else {
    $page_list_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'news WHERE page = '.stripslashes($_POST['page']).' ORDER BY id ASC';
}
$page_list_handle = $db->query($page_list_query);
$page_list_rows = $page_list_handle->num_rows;
if ($page_list_rows == 0) {
    $tab_content['manage'] .= '<tr><td></td><td>There are no articles on this
        page.</td><td></td><td></td></tr>';
}
for ($i = 1; $i <= $page_list_rows; $i++) {
    $page_list = $page_list_handle->fetch_assoc();
    $tab_content['manage'] .= '<tr><td>'.$page_list['id'].'</td><td>'.
        stripslashes($page_list['name']).'</td><td>
        <a href="?module=news&action=delete&id='.$page_list['id'].'">
        <img src="<!-- $IMAGE_PATH$ -->delete.png" alt="Delete" width="16px"
        height="16px" border="0px" /></a></td><td>
        <a href="?module=news_edit_article&id='.$page_list['id'].'">
        <img src="<!-- $IMAGE_PATH$ -->edit.png" alt="Edit" width="16px"
        height="16px" border="0px" /></a></td></tr>';
		} // FOR
$tab_content['manage'] .= '</table>';
$tab_layout->add_tab('Manage News',$tab_content['manage']);

$date = date('Y-m-d H:i:s');
if ($_GET['action'] == 'new') {
    // Clean up variables.
    $title = addslashes($_POST['title']);
    $title = str_replace('"','&quot;',$title);
    $title = str_replace('<','&lt;',$title);
    $title = str_replace('>','&gt;',$title);
    $content = addslashes($_POST['content']);
    $author = addslashes($_POST['author']);
    $image = addslashes($_POST['image']);
    $page = addslashes($_POST['page']);
    $showdate = $_POST['date_params'];
    if(strlen($image) <= 3) {
        $image = NULL;
    }
    $new_article_query = 'INSERT INTO '.$CONFIG['db_prefix']."news (page,name,description,author,image,date,showdate) VALUES ($page,'$title','$content','$author','$image','$date','$showdate')";
    $new_article = $db->query($new_article_query);
    if(!$new_article) {
        $content .= 'Failed to add article. <br />';
    } else {
        $content .= 'Successfully added article. <br />'.log_action('New news article \''.$title.'\'');
    }
}
$form = new form;
$form->set_target('admin.php?module=news&amp;action=new');
$form->set_method('post');
$form->add_textbox('title','Heading');
$form->add_hidden('author',$_SESSION['name']);
$form->add_textarea('content','Content',NULL,'rows="20"');
$form->add_page_list('page','Page',1,1);
$form->add_icon_list('image','Image','newsicons');
$form->add_select('date_params','Date Settings',array(0,1,2),array('Hide','Show','Show Mini'),2);
$form->add_submit('submit','Create Article');
$tab_content['create'] = $form;
$tab_layout->add_tab('Create Article',$tab_content['create']);

$content .= $tab_layout;
?>