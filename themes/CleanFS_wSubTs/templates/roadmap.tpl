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
allTasks{$milestone['id']} = [<?php foreach($milestone['open_tasks'] as $task): echo $task['task_id'] . ','; endforeach; ?>];
</script>

<div class="box roadmap">
<h3>{L('roadmapfor')} {$milestone['name']}
    <?php if (count($milestone['open_tasks'])): ?>
    <small class="DoNotPrint">
      <a href="javascript:showAll(allTasks{$milestone['id']})">{L('expandall')}</a> |
      <a href="javascript:hideAll(allTasks{$milestone['id']})">{L('collapseall')}</a>
    </small>
    <?php endif; ?>
</h3>
<div class="progress_bar_container" style="width: 250px;">
	<span>{$milestone['percent_complete']}%</span>
	<div class="progress_bar" style="width:{$milestone['percent_complete']}%"></div>
</div>
<p style="margin-top: 5px;">{$milestone['percent_complete']}{L('of')}
   <a href="{$baseurl}index.php?do=index&amp;tasks=&amp;project={$proj->id}&amp;due={$milestone['id']}&amp;status[]=">
     {count($milestone['all_tasks'])} {L('tasks')}
   </a> {L('completed')}
   <?php if(count($milestone['open_tasks'])): ?>
   <a href="{$baseurl}index.php?do=index&amp;tasks=&amp;project={$proj->id}&amp;due={$milestone['id']}">{count($milestone['open_tasks'])} {L('opentasks')}:</a>
   <?php endif; ?>
</p>

<?php if(count($milestone['open_tasks'])): ?>
<dl class="roadmap">
    <?php foreach($milestone['open_tasks'] as $task): ?>
      <dt class="severity{$task['task_severity']}">
        {!tpl_tasklink($task['task_id'])}
        <small class="DoNotPrint">
          <a id="expand{$task['task_id']}" href="javascript:showstuff('dd{$task['task_id']}');hidestuff('expand{$task['task_id']}');showstuff('hide{$task['task_id']}', 'inline')">{L('expand')}</a>
          <a class="hide" id="hide{$task['task_id']}" href="javascript:hidestuff('dd{$task['task_id']}');hidestuff('hide{$task['task_id']}');showstuff('expand{$task['task_id']}', 'inline')">{L('collapse')}</a>
        </small>
      </dt>
      <dd id="dd{$task['task_id']}" style="display: none;">
        {!TextFormatter::render($task['detailed_desc'], false, 'rota', $task['task_id'], $task['content'])}
        <br style="position:absolute;" />
      </dd>
    <?php endforeach; ?>
</dl>

<?php endif; ?>
</div>
<?php endforeach; ?>

<?php if (!count($data)): ?>
<div class="box roadmap">
<p><em>{L('noroadmap')}</em></p>
</div>
<?php else: ?>
<p><a href="{CreateURL('roadmap', $proj->id, null, array('txt' => 'true'))}">
<!--<img src="{$this->get_image('mime/text')}" alt="" />--> {L('textversion')}</a></p>
<?php endif; ?>
