<?php
/**
 * Community CMS
 *
 * @copyright Copyright (C) 2009-2013 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.admin
 */

namespace CommunityCMS;

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}

class AdminCalLocationModule extends AdminModule
{
    protected $permission = 'adm_calendar_locations';
    
    /**
     * Handle adding and deleting actions
     * @param string $event
     * @param array  $params
     * @throws AdminException
     * @throws CalLocationException
     */
    public function onLoadEvent($event, $params) 
    {
        try {
            switch ($event) {
            default:
                break;

            case 'new':
                if (!isset($params['location'])) {
                    throw new CalLocationException('No location given.'); 
                }
                
                CalLocation::save($_POST['location']);
                echo 'Successfully created new location entry.<br />'."\n";
                break;

            case 'delete':
                if (!isset($params['loc_del'])) {
                    throw new CalLocationException('There is no location selected for deletion.'); 
                }

                CalLocation::delete($_POST['loc_del']);
                echo 'Deleted location.<br />';
                break;
            }
        } catch (CalLocationException $e) {
            echo '<span class="errormessage">'.$e->getMessage().'</span><br />'."\n";
        }
    }
    
    /**
     * Display calendar location module
     */
    public function display() 
    {
        $all_locations = CalLocation::getAll();
        
        $tab_content['manage'] = null;
        $tab_content['manage'] .= '<form method="post" action="?module=calendar_locations&action=new">
		New Location: <input type="text" name="location" /><input type="submit" value="Create" /></form>';
        $tab_content['manage'] .= '<form method="post" action="?module=calendar_locations&action=delete">
		<table class="admintable">
		<tr><th width="1px">&nbsp;</th><th>Location:</th></tr>';
        $delete_disabled = null;
        if (count($all_locations) == 0) {
            $tab_content['manage'] .= '<tr><td colspan="2" class="row1">There are no saved locations.</td></tr>';
            $delete_disabled = ' disabled';
        } else {
            foreach ($all_locations AS $location) {
                $tab_content['manage'] .= '<tr><td>
					<input type="radio" name="loc_del" value="'.$location['id'].'" /></td>
					<td>'.HTML::schars($location['value']).'</td></tr>';
            }
        }
        $tab_content['manage'] .= '<tr><td colspan="2">
			<input type="submit" value="Delete" '.$delete_disabled.'/></td></tr>
		</table>
		</form>';
        $this->layout->add_tab('Manage Locations', $tab_content['manage']);

        echo $this->layout;
    }
}

$module = new AdminCalLocationModule();
$module->onLoadEvent(FormUtil::get('action'), $_POST);
$module->display();
