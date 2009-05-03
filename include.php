<?php
// Security Check
if (@SECURITY != 1) {
    die ('You cannot access this page directly.');
}
if(!eregi('^[/\.]+$',ROOT)) {
    die ('Invalid ROOT value.');
}
if(!include_once(ROOT.'functions/main.php')) {
    err_page(2001);
}
if(!include_once(ROOT.'functions/mysql.php')) {
    err_page(2001);
}
require(ROOT . 'includes/db/db.php');
if(!include_once(ROOT.'functions/page_class.php')) {
    err_page(2001);
}
if(!include_once(ROOT.'classes.php')) {
    err_page(2001);
}
if(!include_once(ROOT.'functions/display.php')) {
    err_page(2001);
}
if(!include_once(ROOT.'functions/blocks.php')) {
    err_page(2001);
}
if(!include_once(ROOT.'functions/login.php')) {
    err_page(2001);
}
if(!include_once(ROOT.'functions/files.php')) {
    err_page(2001);
}
if(!include_once(ROOT.'functions/forms.php')) {
    err_page(2001);
}
if(!include_once(ROOT.'functions/poll.php')) {
    err_page(2001);
}
?>