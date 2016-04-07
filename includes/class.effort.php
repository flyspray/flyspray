<?php

/**
 * Class effort
 *
 * Task level Effort Tracking functionality.
 */
class effort
{
    const FORMAT_HOURS_COLON_MINUTES = 0; // Default value in database
    const FORMAT_HOURS_SPACE_MINUTES = 1;
    const FORMAT_HOURS_PLAIN = 2;
    const FORMAT_HOURS_ONE_DECIMAL = 3;
    const FORMAT_MINUTES = 4;
    const FORMAT_DAYS_PLAIN = 5;
    const FORMAT_DAYS_ONE_DECIMAL = 6;
    const FORMAT_DAYS_PLAIN_HOURS_PLAIN = 7;
    const FORMAT_DAYS_PLAIN_HOURS_ONE_DECIMAL = 8;
    const FORMAT_DAYS_PLAIN_HOURS_COLON_MINUTES = 9;
    const FORMAT_DAYS_PLAIN_HOURS_SPACE_MINUTES = 10;

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
     * @param $effort_to_add int amount of effort in hh:mm to add to effort table.
     */
    public function addEffort($effort_to_add, $proj)
    {
        global $db;

        # note: third parameter seem useless, not used by EditStringToSeconds().., maybe drop it..
        $effort = self::EditStringToSeconds($effort_to_add, $proj->prefs['hours_per_manday'], $proj->prefs['estimated_effort_format']);
        if ($effort === FALSE) {
            Flyspray::show_error(L('invalideffort'));
            return false;
        }

        # quickfix to avoid useless table entries.
        if($effort==0){ 
            Flyspray::show_error(L('zeroeffort'));
            return false;
        } else{
            $db->Query('INSERT INTO {effort}
                (task_id, date_added, user_id,start_timestamp,end_timestamp,effort)
                VALUES  ( ?, ?, ?, ?,?,? )',
                array($this->_task_id, time(), $this->_userId,time(),time(),$effort)
            );
            return true;
        }
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
        $seconds = $time - $start_time;
        
        // Round to full minutes upwards.
        $effort = ($seconds % 60 == 0 ? $seconds : floor($seconds / 60) * 60 + 60);
 
        $sql = $db->Query("UPDATE {effort} SET end_timestamp = ".$time.",effort = ".$effort." WHERE user_id=".$this->_userId." AND task_id=".$this->_task_id." AND end_timestamp IS NULL;");
    }

    /**
     * Removes any outstanding tracking requests for this task for this user.
     */
    public function cancelTracking()
    {
        global $db;
    
        # 2016-07-04: also remove invalid finished 0 effort entries that were accidently possible up to Flyspray 1.0-rc
        $db->Query('DELETE FROM {effort}
            WHERE user_id='.$this->_userId.'
            AND task_id='.$this->_task_id.'
            AND (
                end_timestamp IS NULL
                OR (start_timestamp=end_timestamp AND effort=0)
            );'
        );
    }

    public function populateDetails()
    {
        global $db;

        $this->details = $db->Query('SELECT * FROM {effort} WHERE task_id ='.$this->_task_id.';');
    }
    
    public static function SecondsToString($seconds, $factor, $format) {
        if ($seconds == 0) {
            return '';
        }
        
        $factor = ($factor == 0 ? 86400 : $factor);

        switch ($format) {
            case self::FORMAT_HOURS_COLON_MINUTES:
                $seconds = ($seconds % 60 == 0 ? $seconds : floor($seconds / 60) * 60 + 60);
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds - ($hours * 3600)) / 60);
                return sprintf('%01u:%02u', $hours, $minutes);
                break;
            case self::FORMAT_HOURS_SPACE_MINUTES:
                $seconds = ($seconds % 60 == 0 ? $seconds : floor($seconds / 60) * 60 + 60);
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds - ($hours * 3600)) / 60);
                if ($hours == 0) {
                    return sprintf('%u %s', $minutes, L('minuteabbrev'));
                } else {
                    return sprintf('%u %s %u %s', $hours, L('hourabbrev'), $minutes, L('minuteabbrev'));
                }
                break;
            case self::FORMAT_HOURS_PLAIN:
                $hours = ceil($seconds / 3600);
                return sprintf('%01u %s', $hours, ($hours == 1 ? L('hoursingular') : L('hourplural')));
                break;
            case self::FORMAT_HOURS_ONE_DECIMAL:
                $hours = round(ceil($seconds * 10 / 3600) / 10, 1);
                return sprintf('%01.1f %s', $hours, ($hours == 1 ? L('hoursingular') : L('hourplural')));
                break;
            case self::FORMAT_MINUTES:
                $minutes = ceil($seconds / 60);
                return sprintf('%01u %s', $minutes, L('minuteabbrev'));
                break;
            case self::FORMAT_DAYS_PLAIN:
                $days = ceil($seconds / $factor);
                return sprintf('%01u %s', $days, ($days == 1 ? L('manday') : L('mandays')));
                break;
            case self::FORMAT_DAYS_ONE_DECIMAL:
                $days = round(ceil($seconds * 10 / $factor) / 10, 1);
                return sprintf('%01.1f %s', $days, ($days == 1 ? L('manday') : L('mandays')));
                break;
            case self::FORMAT_DAYS_PLAIN_HOURS_PLAIN:
                $days = floor($seconds / $factor);
                $hours = ceil(($seconds - ($days * $factor)) / 3600);
                if ($days == 0) {
                    return sprintf('%1u %s', $hours, L('hourabbrev'));
                } else {
                    return sprintf('%u %s %1u %s', $days, L('mandayabbrev'), $hours, L('hourabbrev'));
                }
                break;
            case self::FORMAT_DAYS_PLAIN_HOURS_ONE_DECIMAL:
                $days = floor($seconds / $factor);
                $hours = round(ceil(($seconds - ($days * $factor)) * 10 / 3600) / 10, 1);
                if ($days == 0) {
                    return sprintf('%01.1f %s', $hours, L('hourabbrev'));
                } else {
                    return sprintf('%u %s %01.1f %s', $days, L('mandayabbrev'), $hours, L('hourabbrev'));
                }
                break;
            case self::FORMAT_DAYS_PLAIN_HOURS_COLON_MINUTES:
                $seconds = ($seconds % 60 == 0 ? $seconds : floor($seconds / 60) * 60 + 60);
                $days = floor($seconds / $factor);
                $hours = floor(($seconds - ($days * $factor)) / 3600);
                $minutes = floor(($seconds - (($days * $factor) + ($hours * 3600))) / 60);
                if ($days == 0) {
                    return sprintf('%01u:%02u', $hours, $minutes);
                } else {
                    return sprintf('%u %s %01u:%02u', $days, L('mandayabbrev'), $hours, $minutes);
                }
                break;
            case self::FORMAT_DAYS_PLAIN_HOURS_SPACE_MINUTES:
                $seconds = ($seconds % 60 == 0 ? $seconds : floor($seconds / 60) * 60 + 60);
                $days = floor($seconds / $factor);
                $hours = floor(($seconds - ($days * $factor)) / 3600);
                $minutes = floor(($seconds - (($days * $factor) + ($hours * 3600))) / 60);
                if ($days == 0) {
                    return sprintf('%u %s %u %s', $hours, L('hourabbrev'), $minutes, L('minuteabbrev'));
                } else {
                    return sprintf('%u %s %u %s %u %s', $days, L('mandayabbrev'), $hours, L('hourabbrev'), $minutes, L('minuteabbrev'));
                }
                break;
            default:
                $seconds = ($seconds % 60 == 0 ? $seconds : floor($seconds / 60) * 60 + 60);
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds - ($hours * 3600)) / 60);
                return sprintf('%01u:%02u', $hours, $minutes);
        }
    }

    public static function SecondsToEditString($seconds, $factor, $format) {
        $factor = ($factor == 0 ? 86400 : $factor);

        // Adjust seconds to be evenly dividable by 60, so
        // 3595 -> 3600, floor can be safely used for minutes in formats
        // and the result will be 1:00 instead of 0:60 (if ceil would be used).
        
        $seconds = ($seconds % 60 == 0 ? $seconds : floor($seconds / 60) * 60 + 60);
        
        switch ($format) {
            case self::FORMAT_HOURS_COLON_MINUTES:
            case self::FORMAT_HOURS_SPACE_MINUTES:
            case self::FORMAT_HOURS_PLAIN:
            case self::FORMAT_HOURS_ONE_DECIMAL:
            case self::FORMAT_MINUTES:
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds - ($hours * 3600)) / 60);
                return sprintf('%01u:%02u', $hours, $minutes);
                break;
            case self::FORMAT_DAYS_PLAIN:
            case self::FORMAT_DAYS_ONE_DECIMAL:
            case self::FORMAT_DAYS_PLAIN_HOURS_PLAIN:
            case self::FORMAT_DAYS_PLAIN_HOURS_ONE_DECIMAL:
            case self::FORMAT_DAYS_PLAIN_HOURS_COLON_MINUTES:
            case self::FORMAT_DAYS_PLAIN_HOURS_SPACE_MINUTES:
                $days = floor($seconds / $factor);
                $hours = floor(($seconds - ($days * $factor)) / 3600);
                $minutes = floor(($seconds - ($hours * 3600)) / 60);
                if ($days == 0) {
                    return sprintf('%01u:%02u', $hours, $minutes);
                } else {
                    return sprintf('%u %02u:%02u', $days, $hours, $minutes);
                }
                break;
            default:
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds - (($days * $factor) + ($hours * 3600))) / 60);
                return sprintf('%01u:%02u', $hours, $minutes);
        }
    }

    public static function EditStringToSeconds($string, $factor, $format) {
        if (!isset($string) || empty($string)) {
            return 0;
        }
        
        $factor = ($factor == 0 ? 86400 : $factor);
        
        $matches = array();
        if (preg_match('/^((\d+)\s)?(\d+)(:(\d{2}))?$/', $string, $matches) !== 1) {
            return FALSE;
        }

        if (!isset($matches[2])) {
            $matches[2] = 0;
        }
            
        if (!isset($matches[5])) {
            $matches[5] = 0;
        } else {
            if ($matches[5] > 59) {
                return FALSE;
            }
        }
            
        $effort = ($matches[2] * $factor) + ($matches[3] * 3600) + ($matches[5] * 60);
        return $effort;
    }
}
