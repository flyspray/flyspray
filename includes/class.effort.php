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
    public function addEffort($effort_to_add)
    {
        global $db;

        $add = explode(':',$effort_to_add);

        if(!isset($add[1]))
        {
            $add[1]=0;
        }

        $effort = ($add[0] * 60 * 60) + ($add[1]*60);

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
}
