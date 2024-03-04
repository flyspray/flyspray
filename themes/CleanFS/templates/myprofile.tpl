<div class="box"><h3><?= eL('editmydetails') ?></h3>
<ul class="form_elements">
<li title="<?= eL('usernamenotchangeable') ?>">
<label><?= eL('username') ?></label>
<input type="text" style="border:none;background:none;width:auto;" disabled="disabled" value="<?= $user->infos['user_name'] ?>" />
</li>
</ul>
<?php $this->display('common.profile.tpl'); ?>
</div>
<div class="box">
  <h3><?= eL('myvotes') ?></h3>
<?php if(count($votes)>0): ?>
<table id="myvotes">
<thead>
<tr>
<th><?= eL('project') ?></th>
<th><?= eL('task') ?></th>
<th><?= eL('removevote') ?></th>
</tr>   
</thead>
<tbody>
<?php foreach($votes as $vote): ?>
<tr<?php echo $vote['is_closed'] ? ' class="closed"':''; ?>>
<td><?= Filters::noXSS($vote['project_title']) ?></td>
<td class="task_summary"><?= tpl_tasklink($vote) ?></td>
<td><?php echo tpl_form(Filters::noXSS(createURL('myprofile', $vote['task_id']))); ?>
<input type="hidden" name="action" value="removevote" />
<input type="hidden" name="task_id" value="<?php echo $vote['task_id'] ?>" />
<button type="submit" title="<?= eL('removevote') ?>"><span class="fa fa-trash"></span></button>
</form></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else:
  echo eL('novotes');
endif; ?>
</div>
<div class="box">
  <h3><?= eL('myreminders') ?></h3>
<?php if(count($myreminders)>0): ?>
<table id="myreminders">
<thead>
<tr>
<th><?= eL('project') ?></th>
<th><?= eL('task') ?></th>
<th><?= eL('remindinterval') ?></th>
<th><?= eL('last_sent') ?></th>
</tr>
</thead>
<tbody>
<?php foreach($myreminders as $reminder): ?>
<tr<?php echo $reminder['is_closed'] ? ' class="closed"':''; ?>>
<td><?= Filters::noXSS($reminder['project_title']) ?></td>
<td class="task_summary"><?= tpl_tasklink($reminder) ?></td>
<td><?= ($reminder['how_often']/3600) ?>h</td>
<td><?= $reminder['last_sent'] ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else:
  echo eL('noreminders');
endif; ?>
</div>
<div class="box">
  <h3><?php echo eL('permissionsforproject').' '.$proj->prefs['project_title']; ?></h3>
  <div class="permissions"><?php echo tpl_draw_perms($user->perms); ?></div>
</div>
<div class="clear"></div>
