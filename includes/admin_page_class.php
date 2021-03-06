<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2010-2012 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */

namespace CommunityCMS;

use CommunityCMS\Component\AdminNavComponent;
use CommunityCMS\Component\DebugInfoComponent;

/**
 * Assist in generating admin pages
 *
 * @package CommunityCMS.admin
 */
class AdminPage extends Page
{
    /**
     * Module
     * @var string
     */
    public static $module = "";

    /**
     * display_header - Print the page header
     */
    public static function display_header() 
    {
        $template = new Template;
        $template->loadAdminFile('header');

        // Include javascript
        // Don't cache compressed TinyMCE when debugging
        if (DEBUG === 1) {
            $mce_disk_cache = 'false';
        } else {
            $mce_disk_cache = 'true';
        }

        // Make sure modified javascript is reloaded
        $admin_js_mtime = filemtime('./admin/scripts/admin.js');

        $scripts = '<link type="text/css"
			href="./scripts/jquery-ui/jquery-ui.css" rel="stylesheet" />
                        <script data-main="./scripts/cms" src="./scripts/require.js"></script>
			<script language="javascript" type="text/javascript"
			src="./scripts/ajax.js"></script>
			<script language="javascript" type="text/javascript"
			src="./admin/scripts/admin.js?t='.$admin_js_mtime.'"></script>';
        $template->scripts = $scripts;
        unset($scripts);

        // Include StyleSheets
        $css_include = '<link rel="StyleSheet" type="text/css" href="'.$template->path.'style.css" />';
        $template->css_include = $css_include;
        unset($css_include);

        // Display icon bar
        $icon_bar = null;
        if (acl::get()->check_permission('adm_feedback')) {
            $icon_bar .= '<a href="admin.php?module=feedback">
				<img src="<!-- $IMAGE_PATH$ -->send_feedback.png" alt="Send Feedback"
				border="0px" width="32px" height="32px" /></a>';
        }
        if (acl::get()->check_permission('adm_help')) {
            $icon_bar .= '<a href="admin.php?module=help">
				<img src="<!-- $IMAGE_PATH$ -->help.png" alt="Help"
				border="0px" width="32px" height="32px" /></a>';
        }
        $icon_bar .= '<a href="index.php?login=2">
			<img src="<!-- $IMAGE_PATH$ -->log_out.png" alt="Log Out"
			border="0px" width="32px" height="32px" /></a>';
        $template->icon_bar = $icon_bar;

        echo $template;
        unset($template);
    }

    public static function display_admin() 
    {
        $template_page = new Template;
        $template_page->loadAdminFile();

        $nav = new AdminNavComponent();
        $template_page->nav_bar = $nav->render();
        $template_page->nav_login = Page::displayLoginBox();
        $template_page_bottom = $template_page->split('content');
        echo $template_page;
        unset($template_page);
        ob_start();
        try {
            include ROOT.'admin/'.AdminPage::$module.'.php';
        }
        catch (AdminException $e) {
            echo '<span class="errormessage">'.$e->getMessage().'</span><br />';
        }
        $template_page_bottom->content = ob_get_clean();
        echo $template_page_bottom;
        unset($template_page_bottom);
    }
    
    public static function display_debug() 
    {
        $c = new DebugInfoComponent();
        echo $c->render();
    }

    public static function display_footer() 
    {
        $template = new Template;
        $template->loadAdminFile('footer');
        $template->footer = 'Powered by Community CMS';
        echo $template;
    }
    
    public static function setModule($module) 
    {
        if ($module === null) { $module = 'index'; 
        }
        if (!preg_match('/^[a-z_]+$/i', $module)) {
            throw new \Exception('Invalid admin module.'); 
        }
        if (!file_exists(ROOT.'admin/'.$module.'.php')) { 
            throw new Expcetion('Admin module '.$module.' does not exist.'); 
        }
        
        AdminPage::$module = $module;
    }
}
