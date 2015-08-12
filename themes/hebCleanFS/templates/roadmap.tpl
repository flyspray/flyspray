<script type="text/javascript">

function hideAll(allTasks)
{
    for (i = 0; i < allTasks.length; i++) {
        if (!allTasks[i]) continue;
        hidestuff('dd'+ allTasks[i]);
        hidestuff('hide'+ allTasks[i]);
        showstuff('expand'+ allTasks[i], 'inline');
    }
}

function showAll(allTasks)
{
    for (i = 0; i < allTasks.length; i++) {
        if (!allTasks[i]) continue;
        showstuff('dd'+ allTasks[i]);
        hidestuff('expand'+ allTasks[i]);
        showstuff('hide'+ allTasks[i], 'inline');
    }
}
</script>

<?php foreach($data as $milestone): ?>

<script type="text/javascript">
allTasks<?php echo Filters::noXSS($milestone['id']); ?> = [<?php foreach($milestone['open_tasks'] as $task): echo $task['task_id'] . ','; endforeach; ?>];
</script>

<div class="box roadmap">
<h3><?php echo Filters::noXSS(L('roadmapfor')); ?> <?php echo Filters::noXSS($milestone['name']); ?>

    <?php if (count($milestone['open_tasks'])): ?>
    <small class="DoNotPrint">
      <a href="javascript:showAll(allTasks<?php echo Filters::noXSS($milestone['id']); ?>)"><?php echo Filters::noXSS(L('expandall')); ?></a> |
      <a href="javascript:hideAll(allTasks<?php echo Filters::noXSS($milestone['id']); ?>)"><?php echo Filters::noXSS(L('collapseall')); ?></a>
    </small>
    <?php endif; ?>
</h3>
<div class="progress_bar_container" style="width: 250px;">
	<span><?php echo Filters::noXSS($milestone['percent_complete']); ?>%</span>
	<div class="progress_bar" style="width:<?php echo Filters::noXSS($milestone['percent_complete']); ?>%"></div>
</div>
<p style="margin-top: 5px;"><?php echo Filters::noXSS($milestone['percent_complete']); ?><?php echo Filters::noXSS(L('of')); ?>

   <a href="<?php echo Filters::noXSS($baseurl); ?>index.php?do=index&amp;tasks=&amp;project=<?php echo Filters::noXSS($proj->id); ?>&amp;due=<?php echo Filters::noXSS($milestone['id']); ?>&amp;status[]=">
     <?php echo Filters::noXSS(count($milestone['all_tasks'])); ?> <?php echo Filters::noXSS(L('tasks')); ?>

   </a> <?php echo Filters::noXSS(L('completed')); ?>

   <?php if(count($milestone['open_tasks'])): ?>
   <a href="<?php echo Filters::noXSS($baseurl); ?>index.php?do=index&amp;tasks=&amp;project=<?php echo Filters::noXSS($proj->id); ?>&amp;due=<?php echo Filters::noXSS($milestone['id']); ?>"><?php echo Filters::noXSS(count($milestone['open_tasks'])); ?> <?php echo Filters::noXSS(L('opentasks')); ?>:</a>
   <?php endif; ?>
</p>

<?php if(count($milestone['open_tasks'])): ?>
<dl class="roadmap">
    <?php foreach($milestone['open_tasks'] as $task): ?>
      <dt class="severity<?php echo Filters::noXSS($task['task_severity']); ?>">
        <?php echo tpl_tasklink($task['task_id']); ?>

        <small class="DoNotPrint">
          <a id="expand<?php echo Filters::noXSS($task['task_id']); ?>" href="javascript:showstuff('dd<?php echo Filters::noXSS($task['task_id']); ?>');hidestuff('expand<?php echo Filters::noXSS($task['task_id']); ?>');showstuff('hide<?php echo Filters::noXSS($task['task_id']); ?>', 'inline')"><?php echo Filters::noXSS(L('expand')); ?></a>
          <a class="hide" id="hide<?php echo Filters::noXSS($task['task_id']); ?>" href="javascript:hidestuff('dd<?php echo Filters::noXSS($task['task_id']); ?>');hidestuff('hide<?php echo Filters::noXSS($task['task_id']); ?>');showstuff('expand<?php echo Filters::noXSS($task['task_id']); ?>', 'inline')"><?php echo Filters::noXSS(L('collapse')); ?></a>
        </small>
      </dt>
      <dd id="dd<?php echo Filters::noXSS($task['task_id']); ?>" style="display: none;">
        <?php echo TextFormatter::render($task['detailed_desc'], false, 'rota', $task['task_id'], $task['content']); ?>

        <br style="position:absolute;" />
      </dd>
    <?php endforeach; ?>
</dl>

<?php endif; ?>
</div>
<?php endforeach; ?>

<?php if (!count($data)): ?>
<div class="box roadmap">
<p><em><?php echo Filters::noXSS(L('noroadmap')); ?></em></p>
</div>
<?php else: ?>
<p><a href="<?php echo Filters::noXSS(CreateURL('roadmap', $proj->id, null, array('txt' => 'true'))); ?>">
<!--<img src="<?php echo Filters::noXSS($this->get_image('mime/text')); ?>" alt="" />--> <?php echo Filters::noXSS(L('textversion')); ?></a></p>
<?php endif; ?>
