<?php
//TODO: Move to a more sensible place!
function ConvertSeconds ($seconds)
{
    $days = floor ($seconds / 86400);
    if ($days > 1) // 2 days+, we need days to be in plural
    {
        return $days . ' days ' . gmdate ('H:i', $seconds);
    }
    else if ($days > 0) // 1 day+, day in singular
    {
        return $days . ' day ' . gmdate ('H:i', $seconds);
    }

    return gmdate ('H:i', $seconds);
}

/**
 * Class effort
 *
 * Task level Effort Tracking functionality.
 */
class effort
{
    const FORMAT_HOURS_MINUTES = 0; // Default value in database
    const FORMAT_HOURS = 1;
    const FORMAT_MINUTES = 2;
    const FORMAT_DAYS = 3;
    const FORMAT_DAYS_HOURS = 4;
    const FORMAT_DAYS_HOURS_MINUTES = 5;

    private $_task_id;
    private $_userId;
    public $details;

    /**
     * Class Constructor: Requires the user id and task id as all effort is in context of the task.
     *
     * @param $task_id
     * @param $user_id
     */
    public function __construct($task_id,$user_id)
    {
        $this->_task_id = $task_id;
        $this->_userId = $user_id;
    }

    /**
     * Manually add effort to the effort table for this issue / user.
     *
     * @param $effort_to_add int Amount of Effort in hours to add to effort table.
     */
    public function addEffort($effort_to_add, $proj)
    {
        global $db;

        $effort = self::EditStringToSeconds($effort_to_add, $proj);
        if ($effort === FALSE) {
            Flyspray::show_error(L('invalideffort'));
            return;
        }

        $db->Query('INSERT INTO  {effort}
                                         (task_id, date_added, user_id,start_timestamp,end_timestamp,effort)
                                 VALUES  ( ?, ?, ?, ?,?,? )',
            array   ($this->_task_id, time(), $this->_userId,time(),time(),$effort));
    }

    /**
     * Starts tracking effort for the current user against the current issue.
     *
     * @return bool Returns Success or Failure of the action.
     */
    public function startTracking()
    {
        global $db;

        //check if the user is already tracking time against this task.
        $result = $db->Query('SELECT * FROM {effort} WHERE task_id ='.$this->_task_id.' AND user_id='.$this->_userId.' AND end_timestamp IS NULL;');
        if($db->CountRows($result)>0)
        {
            return false;
        }
        else
        {
                $db->Query('INSERT INTO  {effort}
                                         (task_id, date_added, user_id,start_timestamp)
                                 VALUES  ( ?, ?, ?, ? )',
                                 array   ($this->_task_id, time(), $this->_userId,time()));

                return true;
        }
    }

    /**
     * Stops tracking the current tracking request and then updates the actual hours field on the table, this
     * is useful as both stops constant calculation from start/end timestamps and provides a quick aggregation
     * method as we only need to deal with one field.
     */
    public function stopTracking()
    {
        global $db;

        $time = time();


        $sql = $db->Query('SELECT start_timestamp FROM {effort}  WHERE user_id='.$this->_userId.' AND task_id='.$this->_task_id.' AND end_timestamp IS NULL;');
        $result = $db->FetchRow($sql);
        $start_time = $result[0];
        $effort = $time - $start_time;

        $sql = $db->Query("UPDATE {effort} SET end_timestamp = ".$time.",effort = ".$effort." WHERE user_id=".$this->_userId." AND task_id=".$this->_task_id." AND end_timestamp IS NULL;");
    }

    /**
     * Removes any outstanding tracking requests for this task for this user, as a user can only have
     * one tracking request at any time, this should only ever return a row count of one.
     */
    public function cancelTracking()
    {
        global $db;

        $db->Query('DELETE FROM {effort}  WHERE user_id='.$this->_userId.' AND task_id='.$this->_task_id.' AND end_timestamp IS NULL;');

    }

    public function populateDetails()
    {
        global $db;

        $this->details = $db->Query('SELECT * FROM {effort} WHERE task_id ='.$this->_task_id.';');
    }
    
    public static function SecondsToString($seconds, $proj) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds - ($hours * 3600)) / 60);
        return sprintf('%01u:%02u', $hours, $minutes);
    }

    public static function SecondsToEditString($seconds, $proj) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds - ($hours * 3600)) / 60);
        return sprintf('%01u:%02u', $hours, $minutes);
    }

    public static function EditStringToSeconds($string, $proj) {
        if (!isset($string) || empty($string)) {
            return 0;
        }
        
        $factor = ($proj->prefs['hours_is_manday'] > 0 ? $proj->prefs['hours_is_manday'] : 86400);
        
        // Only a single number and project uses a display/edit format that
        // has working days. Assume the user expressed time in (working) days.
        // Note: accepts 0xff and several other formats also...
        if (is_numeric($string) &&
           ($proj->prefs['effort_format'] == self::FORMAT_DAYS ||
            $proj->prefs['effort_format'] == self::FORMAT_DAYS_HOURS ||
            $proj->prefs['effort_format'] == self::FORMAT_DAYS_HOURS_MINUTES)) {
            $effort = floor(($string + 0) * $factor);
        }
        // Only a single number and project uses a display/edit format that
        // has only hours. Assume the user expressed time in hours.
        elseif (is_numeric($string) && $proj->prefs['effort_format'] == self::FORMAT_HOURS) {
            $effort = floor(($string + 0) * 3600);
        }
        else {
            $matches = array();
            if (preg_match('/^((\d+)\w)?(\d+)(:(\d{2}))?$/', $string, $matches) !== 1) {
                return FALSE;
            }

            if (!isset($matches[1])) {
                $matches[1] = 0;
            }
            
            if (!isset($matches[5])) {
                $matches[5] = 0;
            } else {
                if ($matches[5] > 59) {
                    return FALSE;
                }
            }

            $effort = ($matches[1] * $factor) + ($matches[3] * 60 * 60) + ($matches[5] * 60);
        }
        return $effort;
    }
}
