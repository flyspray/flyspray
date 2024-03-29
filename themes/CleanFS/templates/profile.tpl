<fieldset class="box"><legend><?php echo Filters::noXSS(L('profile')); ?> <?php echo Filters::noXSS($theuser->infos['real_name']); ?> (<?php echo Filters::noXSS($theuser->infos['user_name']); ?>)</legend>
<table id="profile">
  <tr>
    <th><?php echo Filters::noXSS(L('realname')); ?></th>
    <td><?php echo Filters::noXSS($theuser->infos['real_name']); ?></td>
  </tr>
  <?php if ((!$user->isAnon() && !$fs->prefs['hide_emails'] && !$theuser->infos['hide_my_email']) || $user->perms('is_admin')): ?>
  <tr>
    <th><?php echo Filters::noXSS(L('emailaddress')); ?></th>
    <td><a href="mailto:<?php echo Filters::noXSS($theuser->infos['email_address']); ?>"><?php echo Filters::noXSS($theuser->infos['email_address']); ?></a></td>
  </tr>
  <?php endif; ?>
  <?php if (!empty($fs->prefs['jabber_server']) && (( !$user->isAnon() && !$fs->prefs['hide_emails'] && !$theuser->infos['hide_my_email']) || $user->perms('is_admin')) ): ?>
  <tr>
    <th><?php echo Filters::noXSS(L('jabberid')); ?></th>
    <td><a href="xmpp:<?php echo Filters::noXSS($theuser->infos['jabber_id']); ?>"><?php echo Filters::noXSS($theuser->infos['jabber_id']); ?></a></td>
  </tr>
  <?php endif; ?>
  <tr>
    <th><?php echo Filters::noXSS(L('globalgroup')); ?></th>
    <td><?php echo Filters::noXSS($groups[Flyspray::array_find('group_id', $theuser->infos['global_group'], $groups)]['group_name']); ?></td>
  </tr>
  <?php if ($proj->id): ?>
  <tr>
    <th><?php echo Filters::noXSS(L('projectgroup')); ?></th>
    <td>
    <?php if ($user->perms('manage_project')): ?>
    <?php echo tpl_form(Filters::noXSS($baseurl).'index.php?do=user&amp;id='.Filters::noXSS($theuser->id)); ?>
      <select id="projectgroupin" class="adminlist" name="project_group_in">
        <?php $sel = $theuser->perms('project_group') == '' ? 0 : $theuser->perms('project_group'); ?>
        <?php echo tpl_options(array_merge($project_groups, array(0 => array('group_name' => L('none'), 0 => 0, 'group_id' => 0, 1 => L('none')))), $sel); ?>
      </select>
      <input type="hidden" name="old_group_id" value="<?php echo Filters::noXSS($theuser->perms('project_group')); ?>" />
      <input type="hidden" name="action" value="admin.edituser" />
      <input type="hidden" name="user_id" value="<?php echo Filters::noXSS($theuser->id); ?>" />
      <input type="hidden" name="project_id" value="<?php echo $proj->id; ?>" />
      <input type="hidden" name="onlypmgroup" value="1" />
      <button type="submit"><?php echo Filters::noXSS(L('update')); ?></button>
    </form>
    <?php else: ?>
      <?php if ($theuser->perms('project_group')): ?>
      <?php echo Filters::noXSS($project_groups[Flyspray::array_find('group_id', $theuser->perms('project_group'), $project_groups)]['group_name']); ?>
      <?php else: ?>
      <?php echo Filters::noXSS(L('none')); ?>
      <?php endif; ?>
    <?php endif; ?>
    </td>
  </tr>
  <?php endif; ?>
  <tr>
    <th><a href="<?php echo CreateURL('tasklist', 0, null, array('opened'=>$theuser->id, 'status[]'=>'')); ?>"><?php echo Filters::noXSS(L('tasksopened')); ?></a></th>
    <td><a href="<?php echo CreateURL('tasklist', 0, null, array('opened'=>$theuser->id, 'status[]'=>'')); ?>"><?php echo Filters::noXSS($tasks); ?></a></td>
  </tr>
  <tr>
    <th><a href="<?php echo CreateURL('tasklist', 0, null, array('dev'=>$theuser->id)); ?>"><?php echo Filters::noXSS(L('assignedto')); ?></a></th>
    <td><a href="<?php echo CreateURL('tasklist', 0, null, array('dev'=>$theuser->id)); ?>"><?php echo Filters::noXSS($assigned); ?></a></td>
  </tr>
  <tr>
    <th><?php echo Filters::noXSS(L('comments')); ?></th>
    <td><?php echo Filters::noXSS($comments); ?></td>
  </tr>
  <?php if ($theuser->infos['register_date']): ?>
  <tr>
    <th><?php echo Filters::noXSS(L('regdate')); ?></th>
    <td><?php echo Filters::noXSS(formatDate($theuser->infos['register_date'])); ?></td>
  </tr>
  <?php endif; ?>
</table>
</fieldset>
<div><?php if($user->perms('is_admin')): ?><a href="<?php echo CreateURL('edituser', $theuser->id); ?>" class="button"><?php echo L('edituser'); ?></a><?php endif; ?></div>
