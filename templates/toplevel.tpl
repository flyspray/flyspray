<?php if (!count($projects)): ?>
<div class="box">
<h2>{L('allprivate')}</h2>
</div>
<?php endif; ?>

<?php foreach ($projects as $project): ?>
<div class="box">
<h2><a href="{CreateUrl('project', $project['project_id'])}">{$project['project_title']}</a></h2>
<table class="toplevel">
  <tr>
    <th><strong>{L('viewtasks')}</strong></th>
    <td>
        <a href="{$_SERVER['SCRIPT_NAME']}?do=index&amp;project={$project['project_id']}&amp;status[]=">{L('All')}</a> |
        <a href="{$_SERVER['SCRIPT_NAME']}?do=index&amp;project={$project['project_id']}&amp;status[]=open">{L('open')}</a> |
        <?php if (!$user->isAnon()): ?>
          <a href="{$_SERVER['SCRIPT_NAME']}?do=index&amp;project={$project['project_id']}&amp;dev={$user->id}">{L('assignedtome')}</a> |
          <a href="{$_SERVER['SCRIPT_NAME']}?do=index&amp;project={$project['project_id']}&amp;only_watched=1">{L('taskswatched')}</a> |
          <a href="{$_SERVER['SCRIPT_NAME']}?do=index&amp;project={$project['project_id']}&amp;opened={$user->id}">{L('tasksireported')}</a> |
        <?php endif; ?>
        <a href="{$_SERVER['SCRIPT_NAME']}?do=index&amp;project={$project['project_id']}&amp;openedfrom=-1+week">{L('recentlyopened')}</a>
    </td>
  </tr>
  <tr>
    <th><strong>{L('stats')}</strong></th>
    <td>{$stats[$project['project_id']]['open']} {L('opentasks')}, {$stats[$project['project_id']]['all']} {L('totaltasks')}.</td>
  </tr>
  <tr>
    <th><strong>{L('progress')}</strong></th>
    <td>
        {$stats[$project['project_id']]['average_done']}% {L('done')}
        <img src="{$this->get_image('percent-' . round($stats[$project['project_id']]['average_done']/10)*10)}"
                    title="{(round($stats[$project['project_id']]['average_done']/10)*10)}% {L('complete')}"
                    alt="" width="100" height="10" />
    </td>
  </tr>
  <?php if (isset($most_wanted[$project['project_id']])): ?>
  <tr>
    <th><strong>{L('mostwanted')}</strong></th>
    <td>
        <ul>
            <?php foreach($most_wanted[$project['project_id']] as $task): ?>
            <li>{!tpl_tasklink($task['task_id'])}, {$task['num_votes']} {L('vote(s)')}</li>
            <?php endforeach; ?>
        </ul>
    </td>
  </tr>
  <?php endif; ?>
  <tr>
    <th><strong>{L('feeds')}</strong></th>
    <td>
        <b>{L('rss')} 1.0</b> <a href="{$baseurl}feed.php?feed_type=rss1&amp;project={$project['project_id']}">{L('opened')}</a> - 
        <a href="{$baseurl}feed.php?feed_type=rss1&amp;topic=edit&amp;project={$project['project_id']}">{L('edited')}</a> - 
        <a href="{$baseurl}feed.php?feed_type=rss1&amp;topic=clo&amp;project={$project['project_id']}">{L('closed')}</a> | 
        <b>{L('rss')} 2.0</b> <a href="{$baseurl}feed.php?feed_type=rss2&amp;project={$project['project_id']}">{L('opened')}</a> - 
        <a href="{$baseurl}feed.php?feed_type=rss2&amp;topic=edit&amp;project={$project['project_id']}">{L('edited')}</a> -
        <a href="{$baseurl}feed.php?feed_type=rss2&amp;topic=clo&amp;project={$project['project_id']}">{L('closed')}</a> |
        <b>{L('atom')}</b> <a href="{$baseurl}feed.php?feed_type=atom&amp;project={$project['project_id']}">{L('opened')}</a> -
        <a href="{$baseurl}feed.php?feed_type=atom&amp;topic=edit&amp;project={$project['project_id']}">{L('edited')}</a> -
        <a href="{$baseurl}feed.php?feed_type=atom&amp;topic=clo&amp;project={$project['project_id']}">{L('closed')}</a>
    </td>
  </tr>
</table>
</div>
<?php endforeach; ?>
