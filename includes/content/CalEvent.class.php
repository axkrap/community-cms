<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.main
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2013-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

class CalEvent
{
    private $id = 0;
    private $title;
    private $description;
    private $start_time;
    private $end_time;
    private $location;
    private $category;
    private $author;
    private $image;
    private $publish;
    private $location_hidden;
    private $category_hidden;

    /**
     * Create calendar event entry
     * @param string  $title
     * @param string  $description
     * @param string  $author
     * @param string  $start_time
     * @param string  $end_time
     * @param string  $date
     * @param int     $category
     * @param boolean $category_hide
     * @param string  $location
     * @param boolean $location_hide
     * @param int     $image
     * @param boolean $hide
     * @return \CalEvent
     * @throws CalEventException
     */
    public static function create($title, $description, $author, $start_time,
        $end_time, $date, $category, $category_hide, $location, $location_hide, $image, $hide
    ) {
        acl::get()->require_permission('date_create');
        if (!$title) {
            throw new CalEventException('Event heading must not be blank.');
        }
        $start = CalEvent::convertInputToDatetime($date, $start_time);
        $end = CalEvent::convertInputToDatetime($date, $end_time);
        if (strtotime($start) > strtotime($end)) {
            throw new CalEventException('Invalid start or end time. Your event cannot end before it begins.');
        }
        
        try {
            DBConn::get()->query(
                sprintf(
                    'INSERT INTO `%s` '
                    . '(`category`, `category_hide`, `start`, `end`, `header`, '
                    . '`description`, `location`, `location_hide`, `author`, `image`, `hidden`) VALUES '
                    . '(:category, :category_hide, :start, :end, :header, '
                    . ':description, :location, :location_hide, :author, :image, :hide)', CALENDAR_TABLE
                ),
                array(':category' => $category,
                ':category_hide' => (int) $category_hide,
                ':start' => $start, ':end' => $end,
                ':header' => $title,
                ':description' => StringUtils::removeComments($description),
                ':location' => $location,
                ':location_hide' => (int) $location_hide,
                ':author' => $author, ':image' => $image,
                ':hide' => (int) $hide)
            );
        
            try {
                CalLocation::save($location);
            } catch (CalLocationException $e) {
                Debug::get()->addMessage('Failed to save event location.');
            }
            $insert_id = DBConn::get()->lastInsertId();
            Log::addMessage(sprintf("New date entry on %s, '%s'", date('d/m/Y', strtotime($start)), $title));
            return new CalEvent($insert_id);
        } catch (Exceptions\DBException $ex) {
            echo $ex;
            throw new CalEventException('Failed to create event.');
        }
    }

    /**
     * Get the number of events in a given date range
     * @param int $start_range Unix timestamp of start of range
     * @param int $end_range Unix timestamp of end of range
     * @return int
     * @throws CalEventException
     */
    public static function count($start_range, $end_range)
    {
        $start = date("Y-m-d H:i:s", $start_range);
        $end = date("Y-m-d H:i:s", $end_range);
        $query = "SELECT `id` FROM `".CALENDAR_TABLE."` WHERE `start` <= :end_range AND `start` >= :start_range";

        try {
            return DBConn::get()->query($query, [":start_range" => $start, ":end_range" => $end], DBConn::ROW_COUNT);
        } catch (Exceptions\DBException $ex) {
            throw new CalEventException("Failed to perform operation.", $ex);
        }
    }

    /**
     * Get all of the events within a given date range
     * @param int $start_range Unix timestamp of start of range
     * @param int $end_range Unix timestamp of end of range
     * @return \CommunityCMS\CalEvent Array of events
     * @throws CalEventException
     */
    public static function getRange($start_range, $end_range)
    {
        $start = date("Y-m-d H:i:s", $start_range);
        $end = date("Y-m-d H:i:s", $end_range);
        $query = "SELECT `id` FROM `".CALENDAR_TABLE."` WHERE `start` <= :end_range AND `start` >= :start_range ORDER BY `start` ASC, `end` DESC";

        try {
            $ids = DBConn::get()->query($query, [":start_range" => $start, ":end_range" => $end], DBConn::FETCH_ALL);

            $events = array();
            foreach ($ids as $id) {
                $events[] = new CalEvent($id['id']);
            }

            return $events;
        } catch (Exceptions\DBException $ex) {
            throw new CalEventException("Failed to retrieve events.", $ex);
        }
    }

    /**
     * Delete all of the events within a given date range
     * @param int $start_range Unix timestamp of start of range
     * @param int $end_range Unix timestamp of end of range
     * @throws CalEventException
     */
    public static function deleteRange($start_range, $end_range)
    {
        $count = self::count($start_range, $end_range);
        $start = date("Y-m-d H:i:s", $start_range);
        $end = date("Y-m-d H:i:s", $end_range);
        $query = "DELETE FROM `".CALENDAR_TABLE."` WHERE `start` <= :end_range AND `start` >= :start_range";

        try {
            DBConn::get()->query($query, [":start_range" => $start, ":end_range" => $end], DBConn::NOTHING);
            Log::addMessage("Deleted $count calendar events.");
        } catch (Exceptions\DBException $ex) {
            throw new CalEventException("Failed to delete calendar events.", $ex);
        }
    }

    /**
     * Create CalEvent instance
     * @param int $id
     * @throws CalEventException
     */
    public function __construct($id) 
    {
        assert(is_numeric($id));

        $result = DBConn::get()->query(
            sprintf('SELECT * FROM `%s` WHERE `id` = :id', CALENDAR_TABLE),
            array(':id' => $id), DBConn::FETCH
        );
        if (!$result) {
            throw new CalEventException('Event not found.');
        }

        $this->id = $id;
        $this->title = $result['header'];
        $this->description = $result['description'];
        $this->start_time = $result['start'];
        $this->end_time = $result['end'];
        $this->category = $result['category'];
        $this->category_hidden = $result['category_hide'];
        $this->image = $result['image'];
        $this->location = $result['location'];
        $this->location_hidden = $result['location_hide'];
        $this->publish = !$result['hidden'];
        $this->author = $result['author'];
    }

    /**
     * Delete calendar event
     * @throws CalEventException
     */
    function delete() 
    {
        assert($this->id);
        try {
            DBConn::get()->query(
                sprintf(
                    'DELETE FROM `%s` WHERE `id` = :id',
                    CALENDAR_TABLE
                ),
                array(':id' => $this->id)
            );
            Log::addMessage(sprintf("Deleted calendar date '%s'.", $this->title));
            $this->id = 0;
        } catch (Exceptions\DBException $ex) {
            throw new CalEventException('Failed to delete event.');
        }
    }

    /**
     * Edit an event entry
     * @param string  $title
     * @param string  $description
     * @param string  $author
     * @param date    $start
     * @param date    $end
     * @param integer $category
     * @param boolean $category_hide
     * @param string  $location
     * @param boolean $location_hide
     * @param string  $image
     * @param boolean $hide
     * @throws CalEventException 
     */
    public function edit($title, $description, $author, $start, $end, $category, $category_hide, $location, $location_hide, $image, $hide) 
    {
        acl::get()->require_permission('acl_calendar_edit_date');

        CalLocation::save($location);

        $query = "UPDATE `".CALENDAR_TABLE." "
            . "SET `category` = :category, `category_hide` = :category_hide, "
            . "`start` = :start, `end` = :end, "
            . "`header` = :header, `description` = :description, "
            . "`location` = :location, `location_hide` = :location_hide, "
            . "`author` = :author, `image` = :image, `hidden` = :hide "
            . "WHERE `id` = :id LIMIT 1";
        try {
            DBConn::get()->query($query, [
                ":category" => $category,
                ":category_hide" => $category_hide,
                ":start" => date('Y-m-d H:i:s', $start),
                ":end" => date('Y-m-d H:i:s', $end),
                ":header" => HTML::schars($title),
                ":description" => StringUtils::removeComments($description),
                ":location" => HTML::schars($location),
                ":location_hide" => $location_hide,
                ":author" => HTML::schars($author),
                ":image" => HTML::schars($image),
                ":hidden" => $hide,
                ":id" => $this->id
            ], DBConn::NOTHING);
            Log::addMessage(sprintf("Edited date entry on %s '%s'",
                date('Y-m-d', $start), $title));
        } catch (Exceptions\DBException $ex) {
            throw new CalEventException(
                'An error occurred while updating the event record: '.$ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function getCategoryHide() 
    {
        return $this->category_hidden;
    }

    /**
     * Get list of event categories
     * @return array
     * @throws CalEventException
     */
    public static function getCategoryList() 
    {
        $query = 'SELECT `cat_id` AS `id`,`label`
			FROM `'.CALENDAR_CATEGORY_TABLE.'`
			ORDER BY `cat_id` ASC';
        try {
            $results = DBConn::get()->query($query, [], DBConn::FETCH_ALL);
        } catch (Exceptions\DBException $ex) {
            throw new CalEventException('Error reading category list.');
        }

        return $results;
    }

    /**
     * Get category
     * @return string
     */
    public function getCategory() 
    {
        $cat = new CalCategory($this->category);
        return $cat->getName();
    }

    public function getCategoryID() 
    {
        return $this->category;
    }

    public function getDescription() 
    {
        return $this->description;
    }

    /**
     * Get event end time
     * @return int
     */
    public function getEnd() 
    {
        return strtotime($this->end_time);
    }

    public function getHidden() 
    {
        return !$this->publish;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Get ID
     * @return int
     * @throws CalEventException
     */
    public function getId() 
    {        
        return $this->id;
    }

    /**
     * Get event image
     * @return string
     */
    public function getImage() 
    {
        return $this->image;
    }

    /**
     * Get event location
     * @return string
     */
    public function getLocation() 
    {
        return HTML::schars($this->location);
    }

    public function getLocationHide() 
    {
        return $this->location_hidden;
    }

    /**
     * Get event start time
     * @return integer
     */
    public function getStart() 
    {
        return strtotime($this->start_time);
    }

    /**
     * Get title
     * @return string
     * @throws CalEventException
     */
    public function getTitle() 
    {
        return HTML::schars($this->title);
    }

    /**
     * Convert user input to a MySQL compatible datetime string.
     * @param string $date_string
     * @param string $time_string
     * @return string
     * @throws CalEventException
     */
    private static function convertInputToDatetime($date_string, $time_string) 
    {
        if (!$date_string) {
            $date_string = date('m/d/Y');
        }

        $date = StringUtils::parseDate($date_string);
        if ($date == 0) {
            throw new CalEventException('Your event\'s date was formatted invalidly. It should be in the format mm/dd/yyyy.');
        }

        $time = StringUtils::parseTime($time_string);

        return sprintf('%s %s', gmdate("Y-m-d", $date), $time);
    }
}

class CalEventException extends \Exception
{
}
