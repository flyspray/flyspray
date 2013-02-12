<?php if ($details && count($histories)): ?>
<table class="userlist history">
  <tr>
    <th>{L('previousvalue')}</th>
    <th>{L('newvalue')}</th>
  </tr>
  <tr>
    <td>{!$details_previous}</td>
    <td>{!$details_new}</td>
  </tr>
</table>
<?php else: ?>
<table class="userlist history">
  <tr>
    <th>{L('eventdate')}</th>
    <th>{L('user')}</th>
    <th>{L('event')}</th>
  </tr>

  <?php foreach($histories as $history): ?>
  <tr>
    <td>{formatDate($history['event_date'], false)}</td>
    <?php if($fs->prefs['gravatars'] == 1) {?>
    <td>{!tpl_userlinkgravatar($history['user_id'], 25)} {!tpl_userlink($history['user_id'])}</td>
    <?php } else { ?>
    <td>{!tpl_userlink($history['user_id'])}</td>
    <?php } ?>
    <td>{!event_description($history)}</td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>