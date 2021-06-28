<table class="tasklist">
<thead>
<tr>
  <th><?= eL('task') ?></th>
  <th><?= eL('summary') ?></th>
</tr>
</thead>
<tbody>
<?php foreach($tasks as $task): ?>
<tr>
  <td><?= $task['task_id'] ?></td>
  <td><?= Filters::noXSS($task['item_summary']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
