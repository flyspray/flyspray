<div id="toolbox">
  <h3><?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?php echo Filters::noXSS(L('groupmanage')); ?></h3>
  <?php if ($user->perms('is_admin')): ?>
  <p>
    <!--<img src="<?php echo Filters::noXSS($this->get_image('personal')); ?>" alt="" class="middle" />-->
    <a href="<?php echo Filters::noXSS(CreateURL('admin', 'newuser', $proj->id)); ?>"><?php echo Filters::noXSS(L('newuser')); ?></a>
  </p>
  <?php endif; ?>
  <p>
    <!--<img src="<?php echo Filters::noXSS($this->get_image('kuser')); ?>" alt="" class="middle" />-->
    <a href="<?php echo Filters::noXSS(CreateURL('pm', 'newgroup', $proj->id)); ?>"><?php echo Filters::noXSS(L('newgroup')); ?></a>
  </p>
  
  <div class="groupedit">
  <form action="<?php echo Filters::noXSS($baseurl); ?>index.php" method="get">
    <ul class="form_elements">
      <li>
        <label for="selectgroup"><?php echo Filters::noXSS(L('editgroup')); ?></label>
        <select name="id" id="selectgroup"><?php echo tpl_options(Flyspray::ListGroups($proj->id)); ?></select>
        <button type="submit"><?php echo Filters::noXSS(L('edit')); ?></button>
        <input type="hidden" name="do" value="pm" />
        <input type="hidden" name="area" value="editgroup" />
        <input type="hidden" name="project" value="<?php echo $proj->id; ?>" />
      </li>
    </ul>
  </form>
  <form action="<?php echo Filters::noXSS($baseurl); ?>index.php" method="get">
    <ul class="form_elements">
      <li>
        <label for="edit_user"><?php echo Filters::noXSS(L('edituser')); ?></label>
        <?php echo tpl_userselect('user_name', '', 'edit_user'); ?>               
        <button type="submit"><?php echo Filters::noXSS(L('edit')); ?></button>
  
        <input type="hidden" name="do" value="user" />
        <input type="hidden" name="project" value="<?php echo $proj->id; ?>" />
      </li>
    </ul>
  </form>
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

# 20150727 peterdd: This a temporary hack
$i = 0;
$html = 'TODO: Show also global group permissions as hint for understanding permission system better for project managers.
<table class="permcols"><tr>';
$projpermnames = '';

$merge=array_merge($groups,$globalgroups);

foreach ($merge as $group){
  $html .= '<td><table class="perms'.($group['project_id']==0 ? ' globalgroup':'').'"><thead>
  <tr>'.
  ($i == 0 ? '<th>'.L('groupmembers').'</th>' : '').
  '<td>'.$group['users'].'</td>
  </tr> 
  <tr>'.
  ($i == 0 ? '<th>'.L('group').' </th>' : '').
  '<th>'.($group['project_id']==0 ? $group['group_name'] : '<a class="button" style="white-space:nowrap" title="'.eL('editgroup')
.'" href="?id='.$group['group_id'].'&amp;do=pm&amp;area=editgroup&amp;project='.$group['project_id'].'">'.$group['group_name']
.'<i class="fa fa-pencil fa-lg fa-fw"></i></a>').'</th>
  </tr>
  <tr>'.
  ($i == 0 ? '<th>'.L('description').'</th>' : '').
  '<td>'.$group['group_desc'].'</td></tr>
  </thead><tbody>';
  foreach ($group as $key => $val) {
    if (!is_numeric($key) && in_array($key, $perm_fields)) {
      $html .= '<tr>';
      $html .= $i == 0 ? '<th style="max-width:300px;white-space:nowrap">'.eL(str_replace('_', '', $key)).'</th>' : '';
      $html .= ($group['is_admin'] && $val == 0)? '<td title="'.eL('yes').' - Permission granted because of is_admin">(<i class="fa fa-check"></i>)</td>' : $yesno[$val];
      $html .= '</tr>';
      $projpermnames .= $i == 1 ? '<tr><td>'.eL(str_replace('_', '', $key)).'</td></tr>' : '';
    }
  }
  $html .= '</tbody></table></td>';
  $i++;
}
$html .= '</tr></table>
<style>
.permcols th, .permcols td {padding:0;margin:0;}
.perms, .permcols {border-collapse:collapse;}
.perms thead{border-bottom:1px solid #999;}
.perms th, .perms td {max-width:120px;max-height:24px;height:24px;text-overflow:ellipsis;overflow:hidden;border:none;padding:2px;}
.perms td{text-align:center;}
.perms th{text-align:right;}
.perms.globalgroup {background-color:#eee;}
.perms tr:nth-child(3) td {height:3em;overflow:hidden;text-overflow:ellipsis;display:block;}
</style>';
echo $html;
?>
  </div>
</div>
