<?php $project_count = count($projects);

/* If user has no projects, just redirect them to the index page of All Projects */
if (!$project_count): ?>
	<meta http-equiv="Refresh" content="0;url=/index.php?project=0&do=index" />
<?php endif; ?>
<?php if ($projinactive > 0) { ?>
<div id="actionbar">
	<a id="toggle_inactive" class="button"><?php echo Filters::noXSS(L('showinactive')); ?><span class="fas fa-eye"></span></a>
</div>
<?php } ?>
<div id="projects">
<?php
# $projects are now sorted active first, then inactive
$lastprojectactive=1;
foreach ($projects as $project): ?>
<?php $lastprojectactive=$project['project_is_active']; ?>

<div id="project_<?php echo $project['project_id']; ?>" class="box<?php if ($project_count == 1) echo ' single-project'; if ($project['project_is_active'] == 0) echo ' project-inactive'; ?>"<?php if ($project['project_is_active'] == 0) echo ' style="display: none;"'; ?>>
<?php if($user->can_view_project($project['project_id'])): ?>
	<h2><a href="<?php echo Filters::noXSS(CreateUrl('project', $project['project_id'])); ?>"><?php echo Filters::noXSS($project['project_title']); ?></a></h2>
<?php else: ?>
	<h2><?php echo Filters::noXSS($project['project_title']); ?></h2>
<?php endif; ?>

	<div class="projectboxsectionswrap">
<?php if($user->can_view_project($project['project_id'])): ?>
		<div class="box projectboxsection projectsection-tasks">
			<h3><?php echo Filters::noXSS(L('viewtasks')); ?></h3>

			<div class="project-buttons">
				<a href="<?php echo CreateURL('tasklist', $project['project_id'], null, array('status[]'=>'')); ?>" class="button"><?php echo Filters::noXSS(L('All')); ?></a>
				<a href="<?php echo CreateURL('tasklist', $project['project_id'], null, array('status[]'=>'open')); ?>" class="button"><?php echo Filters::noXSS(L('open')); ?></a>
				<a href="<?php echo CreateURL('tasklist', $project['project_id'], null, array('openedfrom'=>'-1 week')); ?>" class="button"><?php echo Filters::noXSS(L('recentlyopened')); ?></a>
			<?php if (!$user->isAnon()): ?>
				<a href="<?php echo CreateURL('tasklist', $project['project_id'], null, array('dev'=>$user->id, 'devsm'=>'userid')); ?>" class="button"><?php echo Filters::noXSS(L('assignedtome')); ?></a>
				<a href="<?php echo CreateURL('tasklist', $project['project_id'], null, array('only_watched'=>1)); ?>" class="button"><?php echo Filters::noXSS(L('taskswatched')); ?></a>
				<a href="<?php echo CreateURL('tasklist', $project['project_id'], null, array('opened'=>$user->id, 'openedsm'=>'userid')); ?>" class="button"><?php echo Filters::noXSS(L('tasksireported')); ?></a>
			<?php endif; ?>

			</div>
		</div>
<?php endif; ?>

<?php if($projprefs[$project['project_id']]['others_viewroadmap'] || ($user->perms('view_roadmap', $project['project_id'])) ): ?>
		<div class="box projectboxsection projectsection-stats">
			<h3><?php echo Filters::noXSS(L('stats')); ?></h3>
			<div>
				<p><?php echo Filters::noXSS($stats[$project['project_id']]['open']); ?> <?php echo Filters::noXSS(L('opentasks')); ?>, <?php echo Filters::noXSS($stats[$project['project_id']]['all']); ?> <?php echo Filters::noXSS(L('totaltasks')); ?>.</p>
				<p><?php echo Filters::noXSS($stats[$project['project_id']]['average_done']); ?>% <?php echo Filters::noXSS(L('done')); ?>.</p>

<?php $progressbar_value = $stats[$project['project_id']]['average_done']; ?>
				<div class="progress_bar_container">
					<span><?php echo Filters::noXSS($stats[$project['project_id']]['average_done']); ?>%</span>
					<div class="progress_bar" style="width:<?php echo Filters::noXSS($stats[$project['project_id']]['average_done']); ?>%"></div>
				</div>
			</div>
		</div>
<?php endif; ?>

<?php if($user->can_view_project($project['project_id'])): ?>
	<?php if ($project_count > 0 and isset($most_wanted[$project['project_id']])): ?>
		<div class="box projectboxsection projectsection-mostwanted">
			<h3><?php echo Filters::noXSS(L('mostwanted')); ?></h3>

			<ul>
		<?php foreach($most_wanted[$project['project_id']] as $task): ?>
				<li><?php echo tpl_tasklink($task['task_id']); ?>, <?php echo Filters::noXSS($task['num_votes']); ?>  <?php echo ($task['num_votes']==1) ? Filters::noXSS(L('vote')) : Filters::noXSS(L('votes')); ?></li>
		<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php if ($project_count == 1 and isset($assigned_to_myself[$project['project_id']])): ?>

		<div class="box projectboxsection projectsection-assignedtome">
			<h3><?php echo Filters::noXSS(L('assignedtome')); ?></h3>

			<ul>
		<?php foreach($assigned_to_myself[$project['project_id']] as $task): ?>
				<li><?php echo tpl_tasklink($task['task_id']); ?></li>
		<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
<?php endif; ?>

<?php
# lets say if someone can view normal tasks of a project, then activity graphs are allowed.
if($user->can_view_project($project['project_id']) ): ?>
		<div class="box projectboxsection projectsection-activity">
			<h3><?php echo Filters::noXSS(L('activity')); ?></h3>

			<h4><?php echo Filters::noXSS(L('allactivity')); ?></h4>
			<div class="activity" title="red line=today"><img width="160" height="25" src="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?line=0066CC&amp;do=activity&amp;project_id=<?php echo Filters::noXSS($project['project_id']); ?>&amp;graph=project"/></div>

	<?php if (!$user->isAnon()): ?>
			<h4><?php echo Filters::noXSS(L('myactivity')); ?></h4>

			<div class="activity" title="red line=today"><img width="160" height="25" src="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?line=0066CC&amp;do=activity&amp;user_id=<?php echo Filters::noXSS($user->id); ?>&amp;project_id=<?php echo Filters::noXSS($project['project_id']); ?>&amp;graph=user"/></div>
	<?php endif; ?>

		</div>
<?php endif; ?>

<?php if($projprefs[$project['project_id']]['use_effort_tracking']) :
	$total_estimated = 0;
	$actual_effort = 0;

	if(isset($stats[$project['project_id']]['tasks'])) :
		foreach($stats[$project['project_id']]['tasks'] as $task) {
			$total_estimated += $task['estimated_effort'];
			$effort = new effort($task['task_id'],0);
			$effort->populateDetails();

			foreach($effort->details as $details) {
				$actual_effort += $details['effort'];
			}
			$effort = null;
		}
	endif;
?>
		<div class="box projectboxsection projectsection-efforttracking">
			<h3><?php echo Filters::noXSS(L('efforttracking')); ?></h3>
<?php
	if ($user->perms('view_estimated_effort', $project['project_id'])) : ?>
			<h4><?php echo Filters::noXSS(L('estimatedeffortopen')); ?></h4>
			<p><?php echo effort::SecondsToString($total_estimated, $proj->prefs['hours_per_manday'], $proj->prefs['estimated_effort_format']); ?></p>
<?php
	endif; ?>
<?php
	if ($user->perms('view_current_effort_done', $project['project_id'])) : ?>
			<h4><?php echo Filters::noXSS(L('currenteffortdoneopen')); ?></h4>
			<p><?php echo effort::SecondsToString($actual_effort, $proj->prefs['hours_per_manday'], $proj->prefs['current_effort_done_format']); ?></p>
<?php
	endif; ?>
		</div>
<?php endif; ?>

<?php if($projprefs[$project['project_id']]['others_view']==1): ?>
		<div class="box projectboxsection projectsection-feeds">
			<h3><?php echo Filters::noXSS(L('feeds')); ?></h3>
			<div class="projectfeedswrap">
				<div class="projectfeeds">
					<h4><?php echo Filters::noXSS(L('rss')); ?> 1.0</h4>
					<div>
						<a href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=rss1&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>"><?php echo Filters::noXSS(L('opened')); ?></a>
						<a href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=rss1&amp;topic=edit&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>"><?php echo Filters::noXSS(L('edited')); ?></a>
						<a href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=rss1&amp;topic=clo&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>"><?php echo Filters::noXSS(L('closed')); ?></a>
					</div>
				</div>
				<div class="projectfeeds">
					<h4><?php echo Filters::noXSS(L('rss')); ?> 2.0</h4>
					<div>
						<a href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=rss2&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>"><?php echo Filters::noXSS(L('opened')); ?></a>
						<a href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=rss2&amp;topic=edit&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>"><?php echo Filters::noXSS(L('edited')); ?></a>
						<a href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=rss2&amp;topic=clo&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>"><?php echo Filters::noXSS(L('closed')); ?></a>
					</div>
				</div>
				<div class="projectfeeds">
					<h4><?php echo Filters::noXSS(L('atom')); ?></h4>
					<div>
						<a href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=atom&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>"><?php echo Filters::noXSS(L('opened')); ?></a>
						<a href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=atom&amp;topic=edit&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>"><?php echo Filters::noXSS(L('edited')); ?></a>
						<a href="<?php echo Filters::noXSS($baseurl); ?>feed.php?feed_type=atom&amp;topic=clo&amp;project=<?php echo Filters::noXSS($project['project_id']); ?>"><?php echo Filters::noXSS(L('closed')); ?></a>
					</div>
				</div>
			</div>
		</div>
<?php endif; ?>
	</div>
</div>
<?php
endforeach;
?>
</div>
