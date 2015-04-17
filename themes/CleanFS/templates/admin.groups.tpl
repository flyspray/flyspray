<div id="toolbox">
  <ul id="submenu">
   <li><a href="#users_tab"><?php echo Filters::noXSS(L('users')); ?></a></li>
   <li><a href="#groups_tab"><?php echo Filters::noXSS(L('globalgroups')); ?></a></li>
  </ul>
  <div id="users_tab" class="tab">
    <a class="button" href="<?php echo Filters::noXSS(CreateURL('admin', 'newuser', $proj->id)); ?>"><i class="good fa fa-user-plus fa-lg fa-fw"></i><?php echo L('newuser'); ?></a>
    <a class="button" href="<?php echo Filters::noXSS(CreateURL('admin', 'newuserbulk', $proj->id)); ?>"><i class="good fa fa-user-times fa-lg fa-fw"></i><?php echo L('newuserbulk'); ?></a>
    <a class="button" href="<?php echo Filters::noXSS(CreateURL('admin', 'editallusers', $proj->id)); ?>"><i class="fa fa-group fa-lg fa-fw"></i><?php echo L('editallusers'); ?></a>
    <div class="groupedit">
<!--
    <form action="<?php echo Filters::noXSS($baseurl); ?>index.php" method="get">
            <label for="selectgroup"><?php echo Filters::noXSS(L('editgroup')); ?></label>
            <select name="id" id="selectgroup"><?php echo tpl_options(Flyspray::ListGroups()); ?></select>
            <button type="submit"><?php echo Filters::noXSS(L('edit')); ?></button>
            <input type="hidden" name="do" value="admin" />
            <input type="hidden" name="area" value="editgroup" />
            <input type="hidden" name="project" value="<?php echo $proj->id; ?>" />
    </form>
-->
      <form action="<?php echo Filters::noXSS($baseurl); ?>index.php" method="get">
              <label for="edit_user"><?php echo Filters::noXSS(L('edituser')); ?></label>
              <?php echo tpl_userselect('user_name', '', 'edit_user'); ?>
              <button type="submit"><?php echo Filters::noXSS(L('edit')); ?></button>
              <input type="hidden" name="do" value="admin" />
              <input type="hidden" name="area" value="users" />
              <input type="hidden" name="project" value="<?php echo $proj->id; ?>" />
      </form>
    </div>
  </div>
  <div id="groups_tab" class="tab">
<div><a class="button" href="<?php echo Filters::noXSS(CreateURL('admin', 'newgroup', $proj->id)); ?>"><i class="fa fa-group fa-lg fa-fw"></i><?php echo Filters::noXSS(L('newgroup')); ?></a></div>

<?php
$perm_fields = array(
'is_admin', 'manage_project', 'view_tasks',
'open_new_tasks', 'modify_own_tasks', 'modify_all_tasks', 'edit_assignments',
'view_comments', 'add_comments', 'edit_comments', 'delete_comments',
'create_attachments', 'delete_attachments',
'view_history', 'close_own_tasks', 'close_other_tasks',
'assign_to_self', 'assign_others_to_self', 'view_reports',
'add_votes', 'edit_own_comments', 'view_estimated_effort',
'track_effort', 'view_current_effort_done', 'add_multiple_tasks',
'view_roadmap'
);

$yesno = array(
	'<td style="color:#ccc" title="'.eL('no').'">-</td>',
	'<td title="'.eL('yes').'"><i class="good fa fa-check fa-lg"></i></td>'
);

# 20150307 peterdd: This a temporary hack
$i=0;
$html='<table class="permcols"><tr>';
$projpermnames='';

foreach ($groups as $group){
	#print_r($group);
	$html .= '<td><table class="perms"><thead>
	<tr>'.
	($i==0? '<th>'.L('groupmembers').'</th>':'').
	'<td>'.$group['users'].'</td>
	</tr>
	<tr>'.
	($i==0? '<th>'.L('group').' </th>' : '').
	'<th><a class="button" style="white-space:nowrap" title="'.eL('editgroup').'" href="?id='.$group['group_id'].'&amp;do=admin&amp;area=editgroup">'.$group['group_name'].'<i class="fa fa-pencil fa-lg fa-fw"></i></a></th>
	</tr>
	<tr>'.
	($i==0? '<th>'.L('description').'</th>' : '').
	'<td style="height:6em;overflow:hidden;width:10em">'.$group['group_desc'].'</td></tr>
	</thead><tbody>';
	foreach ($group as $key => $val) {
		if (!is_numeric($key) && in_array($key, $perm_fields)) {
			$html .= '<tr>';
			$html .= $i==0 ? '<th style="max-width:300px;white-space:nowrap">'.eL(str_replace('_','',$key)).'</th>' : '';
			$html .= ($group['is_admin'] && $val==0)? '<td title="'.eL('yes').' permission granted because of is_admin">- (<i class="fa fa-check"></i>)</td>':$yesno[$val];
			$html .= '</tr>';
			$projpermnames .= $i==1 ? '<tr><td>'.eL(str_replace('_','',$key)).'</td></tr>' : '';
		}
	}
	$html.= '</tbody></table></td>';
	$i++;
}
$html.='</tr></table>
<style>
.permcols th, .permcols td {padding:0;margin:0;}
.perms, .permcols {border-collapse:collapse;}
.perms thead{border-bottom:1px solid #999;}
.perms th, .perms td {max-width:120px;max-height:24px;height:24px;text-overflow:ellipsis;overflow:hidden;border:none;padding:2px;}
.perms td{text-align:center;}
.perms th{text-align:right;}
</style>';

echo $html;
?>
</div>
</div>
