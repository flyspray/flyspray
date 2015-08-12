<?php $project_count = count($projects);

if (!$project_count): ?>
<div class="box">
<h2><?php echo Filters::noXSS(L('allprivate')); ?></h2>
</div>
<?php endif; ?>
<?php
foreach ($projects as $project): ?>
<div class="box<?php if ($project_count == 1) echo ' single-project' ?>">
<h2><a href="<?php echo Filters::noXSS(CreateUrl('project', $project['project_id'])); ?>"><?php echo Filters::noXSS($project['project_title']); ?></a></h2>

<table class="toplevel">
  <tr>
    <th><?php echo Filters::noXSS(L('viewtasks')); ?></th>
    <td>
        <a href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=index&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>&amp;status[]="><?php echo Filters::noXSS(L('All')); ?></a> -
        <a href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=index&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>&amp;status[]=open"><?php echo Filters::noXSS(L('open')); ?></a> -
        <a href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=index&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>&amp;openedfrom=-1+week"><?php echo Filters::noXSS(L('recentlyopened')); ?></a>
        <?php if (!$user->isAnon()): ?>
          <br />
          <a href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=index&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>&amp;dev=<?php echo Filters::noXSS($user->id); ?>"><?php echo Filters::noXSS(L('assignedtome')); ?></a> -
          <a href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=index&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>&amp;only_watched=1"><?php echo Filters::noXSS(L('taskswatched')); ?></a> -
          <a href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=index&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>&amp;opened=<?php echo Filters::noXSS($user->id); ?>"><?php echo Filters::noXSS(L('tasksireported')); ?></a>
        <?php endif; ?>
    </td>
    
    <?php if ($project_count == 1 and isset($most_wanted[$project['project_id']])): ?>
    <td rowspan="4">
      <strong><?php echo Filters::noXSS(L('mostwanted')); ?></strong>
        <ul>
            <?php foreach($most_wanted[$project['project_id']] as $task): ?>
            <li><?php echo tpl_tasklink($task['task_id']); ?>, <?php echo Filters::noXSS($task['num_votes']); ?> <?php echo Filters::noXSS(L('vote(s)')); ?></li>
            <?php endforeach; ?>
        </ul>
    </td>
    <?php endif; ?>
    <?php if ($project_count == 1 and isset($assigned_to_myself[$project['project_id']])): ?>
    <td rowspan="4">
      <strong><?php echo Filters::noXSS(L('assignedtome')); ?></strong>
        <ul>
            <?php foreach($assigned_to_myself[$project['project_id']] as $task): ?>
            <li><?php echo tpl_tasklink($task['task_id']); ?></li>
            <?php endforeach; ?>
        </ul>
    </td>
    <?php endif; ?>

  </tr>
  <?php if (!$user->isAnon()): ?>
  <tr>
    <th><?php echo Filters::noXSS(L('activity')); ?></th>
  	<td><img src="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=activity&amp;user_id=<?php echo Filters::noXSS($user->id); ?>&amp;project_id=<?php echo Filters::noXSS($project['project_id']); ?>&amp;graph=project"/></td>
  </tr>
 
  <tr>
    <th><?php echo Filters::noXSS(L('myactivity')); ?></th>
  	<td><img src="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=activity&amp;user_id=<?php echo Filters::noXSS($user->id); ?>&amp;project_id=<?php echo Filters::noXSS($project['project_id']); ?>&amp;graph=user"/></td>
  </tr>
  <?php endif; ?>
  <?php if ($user->isAnon()): ?>
    <tr>
    <th><?php echo Filters::noXSS(L('activity')); ?></th>
  	<td><img src="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=activity&amp;project_id=<?php echo Filters::noXSS($project['project_id']); ?>"/></td>
  </tr>
  <?php endif; ?>
  <tr>
    <th><?php echo Filters::noXSS(L('stats')); ?></th>
    <td><?php echo Filters::noXSS($stats[$project['project_id']]['open']); ?> <?php echo Filters::noXSS(L('opentasks')); ?>, <?php echo Filters::noXSS($stats[$project['project_id']]['all']); ?> <?php echo Filters::noXSS(L('totaltasks')); ?>.</td>
  </tr>
  <tr>
    <th><?php echo Filters::noXSS(L('progress')); ?></th>
    <td>
        <?php echo Filters::noXSS($stats[$project['project_id']]['average_done']); ?>% <?php echo Filters::noXSS(L('done')); ?>

        <?php $progressbar_value = $stats[$project['project_id']]['average_done']; ?>

        <div class="progress_bar_container">
          <span><?php echo Filters::noXSS($stats[$project['project_id']]['average_done']); ?>%</span>
          <div class="progress_bar" style="width:<?php echo Filters::noXSS($stats[$project['project_id']]['average_done']); ?>%"></div>
        </div>        
    </td>
  </tr>
  <tr>
    <th><?php echo Filters::noXSS(L('feeds')); ?></th>
    <td>
        <b><?php echo Filters::noXSS(L('rss')); ?> 1.0</b> <a href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=rss1&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>"><?php echo Filters::noXSS(L('opened')); ?></a> - 
        <a href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=rss1&amp;topic=edit&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>"><?php echo Filters::noXSS(L('edited')); ?></a> - 
        <a href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=rss1&amp;topic=clo&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>"><?php echo Filters::noXSS(L('closed')); ?></a>
        <br />
        <b><?php echo Filters::noXSS(L('rss')); ?> 2.0</b> <a href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=rss2&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>"><?php echo Filters::noXSS(L('opened')); ?></a> - 
        <a href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=rss2&amp;topic=edit&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>"><?php echo Filters::noXSS(L('edited')); ?></a> -
        <a href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=rss2&amp;topic=clo&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>"><?php echo Filters::noXSS(L('closed')); ?></a>
        <br />
        <b><?php echo Filters::noXSS(L('atom')); ?></b> <a href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=atom&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>"><?php echo Filters::noXSS(L('opened')); ?></a> -
        <a href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=atom&amp;topic=edit&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>"><?php echo Filters::noXSS(L('edited')); ?></a> -
        <a href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=atom&amp;topic=clo&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>"><?php echo Filters::noXSS(L('closed')); ?></a>
    </td>
  </tr>
</table>
</div>
<?php
endforeach;
?>

<div class="clear"></div>
