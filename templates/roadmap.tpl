<?php foreach($data as $milestone): ?>

<div class="box roadmap">
<h3>{L('roadmapfor')} {$milestone['name']}
    <?php if (count($milestone['open_tasks'])): ?>
    <small class="DoNotPrint">
      <a href="javascript:<?php foreach($milestone['open_tasks'] as $task): ?>showstuff('dd{$task['task_id']}');<?php endforeach; ?>">{L('expandall')}</a> |
      <a href="javascript:<?php foreach($milestone['open_tasks'] as $task): ?>hidestuff('dd{$task['task_id']}');<?php endforeach; ?>">{L('collapseall')}</a>
    </small>
    <?php endif; ?>
</h3>

<p><img src="{$this->get_image('percent-' . round($milestone['percent_complete']/10)*10)}"
				title="{(round($milestone['percent_complete']/10)*10)}% {L('complete')}"
				alt="" width="200" height="20" />
</p>

<p>{$milestone['percent_complete']} {L('of')}
   <a href="{$_SERVER['SCRIPT_NAME']}?tasks=&amp;project={$proj->id}&amp;due={$milestone['id']}&amp;status[]=">
     {count($milestone['all_tasks'])} {L('tasks')}
   </a> {L('completed')}
   <?php if(count($milestone['open_tasks'])): ?>
   <a href="{$_SERVER['SCRIPT_NAME']}?tasks=&amp;project={$proj->id}&amp;due={$milestone['id']}">{count($milestone['open_tasks'])} {L('opentasks')}:</a>
   <?php endif; ?>
</p>

<?php if(count($milestone['open_tasks'])): ?>
<dl class="roadmap">
    <?php foreach($milestone['open_tasks'] as $task):
          if(!$user->can_view_task($task)) continue; ?>
      <dt class="severity{$task['task_severity']}">
        {!tpl_tasklink($task['task_id'])}
        <small class="DoNotPrint">
          <a id="expand{$task['task_id']}" href="javascript:showstuff('dd{$task['task_id']}');hidestuff('expand{$task['task_id']}');showstuff('hide{$task['task_id']}', 'inline')">{L('expand')}</a>
          <a class="hide" id="hide{$task['task_id']}" href="javascript:hidestuff('dd{$task['task_id']}');hidestuff('hide{$task['task_id']}');showstuff('expand{$task['task_id']}', 'inline')">{L('collapse')}</a>
        </small>
      </dt>
      <dd id="dd{$task['task_id']}" >
        {!TextFormatter::render(substr($task['detailed_desc'], 0, 500) . ((strlen($task['detailed_desc']) > 500) ? '...' : ''),
                         false, 'rota', $task['task_id'], $task['content'])}
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
<p><a href="{CreateURL('roadmap', $proj->id, null, array('txt' => 'true'))}"><img src="{$this->get_image('mime/text')}" alt="" /> {L('textversion')}</a></p>
<?php endif; ?>
