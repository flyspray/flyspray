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
    <td><?php echo tpl_userlink($history['user_id']); ?></td>
    <td><?php echo event_description($history); ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>