<div id="toolbox">
<h2><?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?php echo Filters::noXSS(L('groupmanage')); ?></h2>
<?php if ($user->perms('is_admin')): ?><a class="button" href="<?php echo CreateURL('admin', 'newuser', $proj->id); ?>"><i class="fa fa-user-plus fa-lg fa-fw"></i> <?php echo Filters::noXSS(L('newuser')); ?></a><?php endif; ?>
<a class="button" href="<?php echo Filters::noXSS(CreateURL('pm', 'newgroup', $proj->id)); ?>"><i class="fa fa-group fa-lg fa-fw"></i><?php echo Filters::noXSS(L('newgroup')); ?></a>
  
<form style="display:inline-block" action="<?php echo Filters::noXSS($baseurl); ?>index.php" method="get">
<label for="edit_user"><?php echo Filters::noXSS(L('edituser')); ?></label>
<?php echo tpl_userselect('user_name', '', 'edit_user'); ?>               
<button type="submit"><?php echo Filters::noXSS(L('edit')); ?></button>
<input type="hidden" name="do" value="user" />
<input type="hidden" name="project" value="<?php echo $proj->id; ?>" />
</form>
<?php
# 'group_open 'is not relevant for project groups, so lets not add it here.
$perm_fields = array(
'is_admin',
'manage_project',
'view_tasks',
'view_groups_tasks', # TODO: What is the definition of "group's task" and how does it effect project views?
'view_own_tasks',    # TODO: What is the definition of "own task" and how does it effect project views?
'open_new_tasks',
'add_multiple_tasks',
'modify_own_tasks',
'modify_all_tasks',
'create_attachments',
'delete_attachments',
'assign_to_self',
'assign_others_to_self',
'edit_assignments',
'close_own_tasks',
'close_other_tasks',
'view_roadmap',
'view_history',
'view_reports',
'add_votes',
'view_comments',
'add_comments',
'edit_comments',
'edit_own_comments',
'delete_comments',
'view_estimated_effort',
'view_current_effort_done',
'track_effort'
);

$yesno = array(
  '<td style="color:#ccc" title="'.eL('no').'">-</td>',
  '<td title="'.eL('yes').'"><i class="good fa fa-check fa-lg"></i></td>'
);

$merge=array_merge($groups,$globalgroups);

$perms=array();
$gmembers='';
$gnames='';
$gdesc='';
$cols='';
foreach ($merge as $group){
	$cols.='<col class="group g'.$group['group_id'].($group['project_id']==0?' globalgroup':'').($group['project_id']==0 && $group['group_open']==0?' inactive':'').'"></col>';
	$gmembers.='<td>'.$group['users'].'</td>';
	if($group['project_id']!=0) {
		$gnames.='<td><a class="button" title="'.eL('editgroup').'" href="'.(CreateURL('editgroup', $group['group_id'], 'pm')).'">'.$group['group_name'].'<i class="fa fa-pencil fa-lg fa-fw"></i></a></td>';
	} else {
		$gnames.='<th title="'.eL('globalgroup').'">'.$group['group_name'].'</th>';
	}
	$gdesc.='<td>'.$group['group_desc'].'</td>';
	foreach ($group as $key => $val) {
		if (!is_numeric($key) && in_array($key, $perm_fields)) {
			$perms[$key][]=$val;
		}
	}
}
?>
<style>
.perms {border-collapse:collapse;margin-top:20px;display:block;}
.perms tbody tr:hover {background-color:#eee;}
.perms td, .perms th{border:1px solid #999;}
.perms thead th, .perms thead td {text-align:center;}
.perms tbody th{text-align:right;}
.perms tbody td{width:100px;text-align:center;}
.perms tbody span i:first-child {color: #090;}
.group.globalgroup {background-color:#ddd;}
.group.globalgroup.inactive {background-color:#ccc;}
</style>
<table class="perms">
<colgroup>
<col></col>
<?php echo $cols; ?>
</colgroup>
<thead>
<tr>
<th><?php echo L('groupmembers'); ?></th>
<?php echo $gmembers; ?>
</tr>
<tr>
<th><?php echo L('group'); ?></th>
<?php echo $gnames; ?>
</tr>
<tr>
<th><?php echo L('description'); ?></th>
<?php echo $gdesc; ?>
</tr>
</thead>
<tbody>
<?php foreach ($perm_fields as $p): ?>
<tr<?php 
# TODO view_own_tasks
echo ( ($p=='view_tasks' || $p=='view_groups_tasks' || $p=='view_own_tasks')  && $proj->prefs['others_view']) ? ' class="everybody"':'';
echo ($p=='view_roadmap'   && $proj->prefs['others_viewroadmap']) ?' class="everybody"':'';
echo ($p=='open_new_tasks' && $proj->prefs['anon_open']) ?         ' class="everybody"':'';
?>>
<th><?php echo eL(str_replace('_', '', $p)); ?></th>
<?php
require_once('permicons.tpl');
$i=0; 

foreach($perms[$p] as $val){
  if ($perms['is_admin'][$i]==1 && $val == 0){
    if(isset($permicons[$p])){
      echo '<td title="'.eL('yes').' - Permission granted because of is_admin">( '.$permicons[$p].' )</td>';
    }else{
      echo $yesno[1];
    }
  } elseif($val==1 && isset($permicons[$p])){
    echo '<td>'.$permicons[$p].'</td>';
  } else{
    echo $yesno[$val];
  }
  $i++;
} 
?>
</tr> 
<?php endforeach; ?>
</tbody>
</table>
</div>
