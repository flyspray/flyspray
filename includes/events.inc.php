<?php

// XXX  be aware: make sure you quote correctly using qstr()
// the variables used in the $where parameter, since statement is
// executed AS IS.

function get_events($task_id, $where = '')
{
    global $db;
    return $db->Query("SELECT h.*,
                      tt1.tasktype_name AS task_type1,
                      tt2.tasktype_name AS task_type2,
                      los1.os_name AS operating_system1,
                      los2.os_name AS operating_system2,
                      lc1.category_name AS product_category1,
                      lc2.category_name AS product_category2,
                      p1.project_title AS project_id1,
                      p2.project_title AS project_id2,
                      lv1.version_name AS product_version1,
                      lv2.version_name AS product_version2,
                      ls1.status_name AS item_status1,
                      ls2.status_name AS item_status2,
                      lr.resolution_name,
                      c.date_added AS c_date_added,
                      c.user_id AS c_user_id,
                      att.orig_name, att.file_desc

                FROM  {history} h

            LEFT JOIN {list_tasktype} tt1 ON tt1.tasktype_id = h.old_value AND h.field_changed='task_type'
            LEFT JOIN {list_tasktype} tt2 ON tt2.tasktype_id = h.new_value AND h.field_changed='task_type'

            LEFT JOIN {list_os} los1 ON los1.os_id = h.old_value AND h.field_changed='operating_system'
            LEFT JOIN {list_os} los2 ON los2.os_id = h.new_value AND h.field_changed='operating_system'

            LEFT JOIN {list_category} lc1 ON lc1.category_id = h.old_value AND h.field_changed='product_category'
            LEFT JOIN {list_category} lc2 ON lc2.category_id = h.new_value AND h.field_changed='product_category'
            
            LEFT JOIN {list_status} ls1 ON ls1.status_id = h.old_value AND h.field_changed='item_status'
            LEFT JOIN {list_status} ls2 ON ls2.status_id = h.new_value AND h.field_changed='item_status'
            
            LEFT JOIN {list_resolution} lr ON lr.resolution_id = h.new_value AND h.event_type = 2

            LEFT JOIN {projects} p1 ON p1.project_id = h.old_value AND h.field_changed='project_id'
            LEFT JOIN {projects} p2 ON p2.project_id = h.new_value AND h.field_changed='project_id'
            
            LEFT JOIN {comments} c ON c.comment_id = h.field_changed AND h.event_type = 5
            
            LEFT JOIN {attachments} att ON att.attachment_id = h.new_value AND h.event_type = 7

            LEFT JOIN {list_version} lv1 ON lv1.version_id = h.old_value
                      AND (h.field_changed='product_version' OR h.field_changed='closedby_version')
            LEFT JOIN {list_version} lv2 ON lv2.version_id = h.new_value
                      AND (h.field_changed='product_version' OR h.field_changed='closedby_version')

                WHERE h.task_id = ? $where
             ORDER BY event_date ASC, event_type ASC", array($task_id));
}

function event_description($history) {
    $return = '';
    global $fs, $baseurl, $details;
    
    $translate = array('item_summary' => 'summary', 'project_id' => 'attachedtoproject',
                       'task_type' => 'tasktype', 'product_category' => 'category', 'item_status' => 'status',
                       'task_priority' => 'priority', 'operating_system' => 'operatingsystem', 'task_severity' => 'severity',
                       'product_version' => 'reportedversion', 'mark_private' => 'visibility');

    $new_value = $history['new_value'];
    $old_value = $history['old_value'];

    switch($history['event_type']) {
    case '3':  //Field changed
            if (!$new_value && !$old_value) {
                $return .= L('taskedited');
                break;
            }
        
            $field = $history['field_changed'];
            switch ($field) {
                case 'item_summary':
                case 'project_id':
                case 'task_type':
                case 'product_category':
                case 'item_status':
                case 'task_priority':
                case 'operating_system':
                case 'task_severity':
                case 'product_version':
                    if($field == 'task_priority') {
                        $old_value = $fs->priorities[$old_value];
                        $new_value = $fs->priorities[$new_value];
                    } elseif($field == 'task_severity') {
                        $old_value = $fs->severities[$old_value];
                        $new_value = $fs->severities[$new_value];
                    } elseif($field != 'item_summary') {                        
                        $old_value = $history[$field . '1'];
                        $new_value = $history[$field . '2'];
                    }
                    $field = L($translate[$field]);
                    break;
                case 'closedby_version':
                    $field = L('dueinversion');
                    $old_value = ($old_value == '0') ? L('undecided') : $history['product_version1'];
                    $new_value = ($new_value == '0') ? L('undecided') : $history['product_version2'];
                    break;
                 case 'due_date':
                    $field = L('duedate');
                    $old_value = formatDate($old_value, false, L('undecided'));
                    $new_value = formatDate($new_value, false, L('undecided'));
                    break;
                case 'percent_complete':
                    $field = L('percentcomplete');
                    $old_value .= '%';
                    $new_value .= '%';
                    break;
                case 'mark_private':
                    $field = L($translate[$field]);
                    if ($old_value == 1) {
                        $old_value = L('private');
                    } else {
                        $old_value = L('public');
                    }
                    if ($new_value == 1) {
                        $new_value = L('private');
                    } else {
                        $new_value = L('public');
                    }
                    break;
                case 'detailed_desc':
                    $field = "<a href=\"javascript:getHistory('{$history['task_id']}', '$baseurl', 'history', '{$history['history_id']}');showTabById('history', true);\">" . L('details') . '</a>';
                    if (!empty($details)) {
                        $details_previous = TextFormatter::render($old_value);
                        $details_new =  TextFormatter::render($new_value);
                    }
                    $old_value = '';
                    $new_value = '';
                    break;
            }
            $return .= L('fieldchanged').": {$field}";
            if ($old_value || $new_value) {
                 $return .= " ({$old_value} &rarr; {$new_value})";
            }
            break;
    case '1':      //Task opened
            $return .= L('taskopened');
            break;
    case '2':      //Task closed
            $return .= L('taskclosed');
            $return .= " ({$history['resolution_name']}";
            if (!empty($old_value)) {
                 $return .= ': ' . TextFormatter::render($old_value, true);
            }
            $return .= ')';
            break;
    case '4':      //Comment added
            $return .= '<a href="#comments">' . L('commentadded') . '</a>';
            break;
    case '5':      //Comment edited
            $return .= "<a href=\"javascript:getHistory('{$history['task_id']}', '$baseurl', 'history', '{$history['history_id']}');\">".L('commentedited')."</a>";
            if ($history['c_date_added']) {
                 $return .= " (".L('commentby').' ' . tpl_userlink($history['c_user_id']) . " - " . formatDate($history['c_date_added'], true) . ")";
            }
            if ($details) {
                 $details_previous = TextFormatter::render($old_value);
                 $details_new      = TextFormatter::render($new_value);
            }
            break;
    case '6':     //Comment deleted
            $return .= "<a href=\"javascript:getHistory('{$history['task_id']}', '$baseurl', 'history', '{$history['history_id']}');\">".L('commentdeleted')."</a>";
            if ($new_value != '' && $history['field_changed'] != '') {
                 $return .= " (".L('commentby').' ' . tpl_userlink($new_value) . " - " . formatDate($history['field_changed'], true) . ")";
            }
            if (!empty($details)) {
                 $details_previous = TextFormatter::render($old_value);
                 $details_new = '';
            }
            break;
    case '7':    //Attachment added
            $return .= L('attachmentadded');
            if ($history['orig_name']) {
                 $return .= ": <a href=\"{$baseurl}?getfile={$new_value}\">{$history['orig_name']}</a>";
                 if ($history['file_desc'] != '') {
                      $return .= " ({$history['file_desc']})";
                 }
            }
            break;
    case '8':    //Attachment deleted
            $return .= L('attachmentdeleted') . ": {$new_value}";
            break;
    case '9':    //Notification added
            $return .= L('notificationadded') . ': ' . tpl_userlink($new_value);
            break;
    case '10':  //Notification deleted
            $return .= L('notificationdeleted') . ': ' . tpl_userlink($new_value);
            break;
    case '11':  //Related task added
            $return .= L('relatedadded') . ': ' . tpl_tasklink($new_value);
            break;
    case '12':  //Related task deleted
            $return .= L('relateddeleted') . ': ' . tpl_tasklink($new_value);
            break;
    case '13':  //Task reopened
            $return .= L('taskreopened');
            break;
    case '14':  //Task assigned
            if (empty($old_value)) {
                $users = explode(' ', trim($new_value));
                $users = array_map('tpl_userlink', $users);
                $return .= L('taskassigned').' ';
                $return .= implode(', ', $users);
            } elseif (empty($new_value)) {
                 $return .= L('assignmentremoved');
            } else {
                 $users = explode(' ', trim($new_value));
                 $users = array_map('tpl_userlink', $users);
                 $return .= L('taskreassigned').' ';
                 $return .= implode(', ', $users);
            }
            break;
    case '17': //Reminder added
            $return .= L('reminderadded') . ': ' . tpl_userlink($new_value);
            break;
    case '18': //Reminder deleted
            $return .= L('reminderdeleted') . ': ' . tpl_userlink($new_value);
            break;
    case '19': //User took ownership
            $return .= L('ownershiptaken') . ': ' . tpl_userlink($new_value);
            break;
    case '20': //User requested task closure
            $return .= L('closerequestmade') . ' - ' . $new_value;
            break;
    case '21': //User requested task
            $return .= L('reopenrequestmade') . ' - ' . $new_value;
            break;
    case '22': // Dependency added
            $return .= L('depadded') . ' ' . tpl_tasklink($new_value);
            break;
    case '23': // Dependency added to other task
            $return .= L('depaddedother') . ' ' . tpl_tasklink($new_value);
            break;
    case '24': // Dependency removed
            $return .= L('depremoved') . ' ' . tpl_tasklink($new_value);
            break;
    case '25': // Dependency removed from other task
            $return .= L('depremovedother') . ' ' . tpl_tasklink($new_value);
            break;
    // 26 and 27 replaced by 0 (mark_private)
    case '28': // PM request denied
            $return .= L('pmreqdenied') . ' - ' . $new_value;
            break;
    case '29': // User added to assignees list
            $return .= L('addedtoassignees');
            break;    
    case '30': // user created
            $return .= L('usercreated');
            break;
    case '31': // user deleted
            $return .= L('userdeleted');
            break;
    }
    
    if (isset($details_previous)) $GLOBALS['details_previous'] = $details_previous;
    if (isset($details_new))      $GLOBALS['details_new'] = $details_new;
    
    return $return;    
}

?>
