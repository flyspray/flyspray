<?php if ($details && count($histories)): ?>
<table class="userlist history">
  <tr>
    <th><?php echo Filters::noXSS(L('previousvalue')); ?></th>
    <th><?php echo Filters::noXSS(L('newvalue')); ?></th>
  </tr>
  <tr>
    <td><?php echo $details_previous; ?></td>
    <td><?php echo $details_new; ?></td>
  </tr>
</table>
<?php else: ?>
<table class="userlist history">
  <tr>
    <th><?php echo Filters::noXSS(L('eventdate')); ?></th>
    <th><?php echo Filters::noXSS(L('user')); ?></th>
    <th><?php echo Filters::noXSS(L('event')); ?></th>
  </tr>

  <?php foreach($histories as $history): ?>
  <tr>
    <td><?php echo Filters::noXSS(formatDate($history['event_date'], false)); ?></td>
    <?php if($fs->prefs['enable_avatars'] == 1) { ?>
    <td><?php echo tpl_userlinkavatar($history['user_id'], $fs->prefs['max_avatar_size'] / 2, 'left', '0px 5px 0px 0px'); ?> <?php echo tpl_userlink($history['user_id']); ?></td>
    <?php } else { ?>
    <td><?php echo tpl_userlink($history['user_id']); ?></td>
    <?php } ?>
    <td><?php echo event_description($history); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>
