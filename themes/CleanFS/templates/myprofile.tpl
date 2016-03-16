<div class="box"><h3><?php echo Filters::noXSS(L('editmydetails')); ?></h3>
<?php $this->display('common.profile.tpl'); ?>
</div>
<div class="box"><h3><?php echo L('myvotes'); ?></h3>
<?php if(count($votes)>0): ?>
<table id="myvotes">
<thead>
<tr>
<th><?php echo L('project'); ?></th>
<th><?php echo L('task'); ?></th>
<th><?php echo L('removevote'); ?></th>
</tr>   
</thead>
<tbody>
<?php foreach($votes as $vote): ?>
<tr<?php echo $vote['is_closed'] ? ' class="closed"':''; ?>>
<td><?php echo $vote['project_title']; ?></td>
<td><?php echo $vote['item_summary']; ?></td> 
<td><?php echo tpl_form(Filters::noXSS(CreateURL('myprofile', $vote['task_id'])));?>
<input type="hidden" name="action" value="removevote" />
<input type="hidden" name="task_id" value="<?php echo $vote['task_id']?>" />
<button type="submit" title="<?php echo eL('removevote'); ?>"><span class="fa fa-trash"></span></button>
</form></td>
<td></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else:
  echo L('novotes');
endif; ?>
</div>
<div class="box"><h3><?php echo eL('permissionsforproject').' '.$proj->prefs['project_title']; ?></h3><?php echo tpl_draw_perms($user->perms); ?></div>
<div class="clear"></div>
