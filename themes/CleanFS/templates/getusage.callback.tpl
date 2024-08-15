<table class="tasklist">
<thead>
<tr>
	<th class="id"><?= eL('task') ?></th>
	<th class="summary"><?= eL('summary') ?></th>
</tr>
</thead>
<tbody>
<?php foreach($tasks as $task): ?>
<tr>
	<td class="task_id"><?= $task['task_id'] ?></td>
	<td class="task_summary"><?= Filters::noXSS($task['item_summary']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
