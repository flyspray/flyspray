<?php $project_count = count($projects);

/* If user has no projects, just redirect them to the index page of All Projects */
if (!$project_count): ?>
  <meta http-equiv="Refresh" content="0;url=/index.php?project=0&do=index" />
<?php endif; ?>
<style type="text/css">
.activity::after {
content: "\25B4";
position: absolute;
right: -3px;
bottom:-5px;
text-align: right;
color:#c00;
}
.activity img{
padding-right:1px;
background-color:#c00;
}
.activity {
display: block;
position: relative;
width: 160px;
}
#s_inactive {display:none;}
#s_inactive ~ .box {display: none;}
#s_inactive:checked ~ .box {display: inline-block;}
</style>
<?php
# $projects are now sorted active first, then inactive
$lastprojectactive=1;
foreach ($projects as $project): ?>
  <?php if( count($projects)>1 && $lastprojectactive==1 && $project['project_is_active']==0) : ?>
    <div style="clear:both;padding-top:20px;border-bottom:1px solid #999;"></div>
    <input type="checkbox" id="s_inactive" />
    <label class="button" style="display:block;width:100px;" for="s_inactive"><?php echo Filters::noXSS(L('showinactive')); ?></label>
  <?php endif; ?>
  <?php $lastprojectactive=$project['project_is_active']; ?>

<div class="box<?php if ($project_count == 1) echo ' single-project' ?>">
<h2><a href="<?php echo Filters::noXSS(CreateUrl('project', $project['project_id'])); ?>"><?php echo Filters::noXSS($project['project_title']); ?></a></h2>

<table class="toplevel">
  <tr>
    <th><?php echo Filters::noXSS(L('viewtasks')); ?></th>
    <td>
      <a href="<?php echo CreateURL('tasklist', $project['project_id'], null, array('status[]'=>'')); ?>"><?php echo Filters::noXSS(L('All')); ?></a> -
      <a href="<?php echo CreateURL('tasklist', $project['project_id'], null, array('status[]'=>'open')); ?>"><?php echo Filters::noXSS(L('open')); ?></a> -
      <a href="<?php echo CreateURL('tasklist', $project['project_id'], null, array('openedfrom'=>'-1 week')); ?>"><?php echo Filters::noXSS(L('recentlyopened')); ?></a>
      <?php if (!$user->isAnon()): ?>
        <br />
        <a href="<?php echo CreateURL('tasklist', $project['project_id'], null, array('dev'=>$user->id, 'devsm'=>'userid')); ?>"><?php echo Filters::noXSS(L('assignedtome')); ?></a> -
        <a href="<?php echo CreateURL('tasklist', $project['project_id'], null, array('only_watched'=>1)); ?>"><?php echo Filters::noXSS(L('taskswatched')); ?></a> -
        <a href="<?php echo CreateURL('tasklist', $project['project_id'], null, array('opened'=>$user->id, 'openedsm'=>'userid')); ?>"><?php echo Filters::noXSS(L('tasksireported')); ?></a>
      <?php endif; ?>
    </td>
    
    <?php if ($project_count == 1 and isset($most_wanted[$project['project_id']])): ?>
    <td rowspan="4">
      <strong><?php echo Filters::noXSS(L('mostwanted')); ?></strong>
        <ul>
            <?php foreach($most_wanted[$project['project_id']] as $task): ?>
            <li><?php echo tpl_tasklink($task['task_id']); ?>, <?php echo Filters::noXSS($task['num_votes']); ?>  <?php echo ($task['num_votes']==1) ? Filters::noXSS(L('vote')) : Filters::noXSS(L('votes')); ?></li>
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
  <tr>
    <th><?php echo Filters::noXSS(L('activity')); ?></th>
  	<td><span class="activity" title="red line=today"><img width="160px" height="25px" src="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?line=0066CC&amp;do=activity&amp;project_id=<?php echo Filters::noXSS($project['project_id']); ?>&amp;graph=project"/></span></td>
  </tr>
  <?php if (!$user->isAnon()): ?> 
  <tr>
    <th><?php echo Filters::noXSS(L('myactivity')); ?></th>
  	<td><span class="activity" title="red line=today"><img width="160px" height="25px" src="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?line=0066CC&amp;do=activity&amp;user_id=<?php echo Filters::noXSS($user->id); ?>&amp;project_id=<?php echo Filters::noXSS($project['project_id']); ?>&amp;graph=user"/></span></td>
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
  <?php
        if ($projprefs[$project['project_id']]['use_effort_tracking']) {
        $total_estimated = 0;
        $actual_effort = 0;

        foreach($stats[$project['project_id']]['tasks'] as $task) {
            $total_estimated += $task['estimated_effort'];
            $effort = new effort($task['task_id'],0);
            $effort->populateDetails();

            foreach($effort->details as $details) {
                $actual_effort += $details['effort'];
            }
            $effort = null;
        }

  ?>
  <?php if ($user->perms('view_estimated_effort', $project['project_id'])) { ?>
  <tr>
      <th>
          <?php echo Filters::noXSS(L('estimatedeffortopen')); ?>
      </th>
      <td>
          <?php echo effort::SecondsToString($total_estimated, $proj->prefs['hours_per_manday'], $proj->prefs['estimated_effort_format']); ?>
      </td>
  </tr>
  <?php } ?>
  <?php if ($user->perms('view_current_effort_done', $project['project_id'])) { ?>
  <tr>
      <th>
          <?php echo Filters::noXSS(L('currenteffortdoneopen')); ?>
      </th>
      <td>
          <?php echo effort::SecondsToString($actual_effort, $proj->prefs['hours_per_manday'], $proj->prefs['current_effort_done_format']); ?>
      </td>
  </tr>
  <?php } ?>
  <?php } ?>
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
