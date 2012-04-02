<?php $project_count = count($projects);

if (!$project_count): ?>
<div class="box">
<h2>{L('allprivate')}</h2>
</div>
<?php endif; ?>
<?php
foreach ($projects as $project): ?>
<div class="box<?php if ($project_count == 1) echo ' single-project' ?>">
<h2><a href="{CreateUrl('project', $project['project_id'])}">{$project['project_title']}</a></h2>

<table class="toplevel">
  <tr>
    <th><strong>{L('viewtasks')}</strong></th>
    <td>
        <a href="{$_SERVER['SCRIPT_NAME']}?do=index&amp;project={$project['project_id']}&amp;status[]=">{L('All')}</a> -
        <a href="{$_SERVER['SCRIPT_NAME']}?do=index&amp;project={$project['project_id']}&amp;status[]=open">{L('open')}</a> -
        <a href="{$_SERVER['SCRIPT_NAME']}?do=index&amp;project={$project['project_id']}&amp;openedfrom=-1+week">{L('recentlyopened')}</a>
        <?php if (!$user->isAnon()): ?>
          <br />
          <a href="{$_SERVER['SCRIPT_NAME']}?do=index&amp;project={$project['project_id']}&amp;dev={$user->id}">{L('assignedtome')}</a> -
          <a href="{$_SERVER['SCRIPT_NAME']}?do=index&amp;project={$project['project_id']}&amp;only_watched=1">{L('taskswatched')}</a> -
          <a href="{$_SERVER['SCRIPT_NAME']}?do=index&amp;project={$project['project_id']}&amp;opened={$user->id}">{L('tasksireported')}</a>
        <?php endif; ?>
    </td>
    
    <?php if ($project_count == 1 and isset($most_wanted[$project['project_id']])): ?>
    <td rowspan="4">
      <strong>{L('mostwanted')}</strong>
        <ul>
            <?php foreach($most_wanted[$project['project_id']] as $task): ?>
            <li>{!tpl_tasklink($task['task_id'])}, {$task['num_votes']} {L('vote(s)')}</li>
            <?php endforeach; ?>
        </ul>
    </td>
    <?php endif; ?>
    <?php if ($project_count == 1 and isset($assigned_to_myself[$project['project_id']])): ?>
    <td rowspan="4">
      <strong>{L('assignedtome')}</strong>
        <ul>
            <?php foreach($assigned_to_myself[$project['project_id']] as $task): ?>
            <li>{!tpl_tasklink($task['task_id'])}</li>
            <?php endforeach; ?>
        </ul>
    </td>
    <?php endif; ?>

  </tr>
  <?php if (!$user->isAnon()): ?>
  <tr>
  	<th><strong>Activity</strong>
  	<td><img src="{$_SERVER['SCRIPT_NAME']}?do=activity&amp;user_id={$user->id}&amp;project_id={$project['project_id']}&amp;graph=project"/></td>
  </tr>
 
  <tr>
  	<th><strong>My Activity</strong>
  	<td><img src="{$_SERVER['SCRIPT_NAME']}?do=activity&amp;user_id={$user->id}&amp;project_id={$project['project_id']}&amp;graph=user"/></td>
  </tr>
  <?php endif; ?>
  <?php if ($user->isAnon()): ?>
    <tr>
  	<th><strong>Activity</strong>
  	<td><img src="{$_SERVER['SCRIPT_NAME']}?do=activity&amp;project_id={$project['project_id']}"/></td>
  </tr>
  <?php endif; ?>
  <tr>
    <th><strong>{L('stats')}</strong></th>
    <td>{$stats[$project['project_id']]['open']} {L('opentasks')}, {$stats[$project['project_id']]['all']} {L('totaltasks')}.</td>
  </tr>
  <tr>
    <th><strong>{L('progress')}</strong></th>
    <td>
        {$stats[$project['project_id']]['average_done']}% {L('done')}
        <?php $progressbar_value = $stats[$project['project_id']]['average_done']; ?>

        <div class="progress_bar_container">
          <span>{$stats[$project['project_id']]['average_done']}%</span>
          <div class="progress_bar" style="width:{$stats[$project['project_id']]['average_done']}%"></div>
        </div>        
    </td>
  </tr>
  <tr>
    <th><strong>{L('feeds')}</strong></th>
    <td>
        <b>{L('rss')} 1.0</b> <a href="{$baseurl}feed.php?feed_type=rss1&amp;project={$project['project_id']}">{L('opened')}</a> - 
        <a href="{$baseurl}feed.php?feed_type=rss1&amp;topic=edit&amp;project={$project['project_id']}">{L('edited')}</a> - 
        <a href="{$baseurl}feed.php?feed_type=rss1&amp;topic=clo&amp;project={$project['project_id']}">{L('closed')}</a>
        <br />
        <b>{L('rss')} 2.0</b> <a href="{$baseurl}feed.php?feed_type=rss2&amp;project={$project['project_id']}">{L('opened')}</a> - 
        <a href="{$baseurl}feed.php?feed_type=rss2&amp;topic=edit&amp;project={$project['project_id']}">{L('edited')}</a> -
        <a href="{$baseurl}feed.php?feed_type=rss2&amp;topic=clo&amp;project={$project['project_id']}">{L('closed')}</a>
        <br />
        <b>{L('atom')}</b> <a href="{$baseurl}feed.php?feed_type=atom&amp;project={$project['project_id']}">{L('opened')}</a> -
        <a href="{$baseurl}feed.php?feed_type=atom&amp;topic=edit&amp;project={$project['project_id']}">{L('edited')}</a> -
        <a href="{$baseurl}feed.php?feed_type=atom&amp;topic=clo&amp;project={$project['project_id']}">{L('closed')}</a>
    </td>
  </tr>
</table>
</div>
<?php
endforeach;
?>

<div class="clear"></div>
