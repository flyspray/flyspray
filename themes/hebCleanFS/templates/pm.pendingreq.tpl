<div id="toolbox">
  <h3>{L('pendingrequests')}</h3>
  
  <?php if (!count($pendings)): ?>
  {L('nopendingreq')}
  <?php else: ?>
  <table class="requests">
    <tr>
      <th>{L('eventdesc')}</th>
      <th>{L('requestedby')}</th>
      <th>{L('daterequested')}</th>
      <th>{L('reasongiven')}</th>
      <th class="pm-buttons"> </th>
    </tr>
    <?php foreach ($pendings as $req): ?>
    <tr>
      <td>
      <?php if ($req['request_type'] == 1) : ?>
      {L('closetask')} -
      <a href="{CreateURL('details', $req['task_id'])}">FS#{$req['task_id']} :
        {$req['item_summary']}</a>
      <?php elseif ($req['request_type'] == 2) : ?>
      {L('reopentask')} -
      <a href="{CreateURL('details', $req['task_id'])}">FS#{$req['task_id']} :
        {$req['item_summary']}</a>
      <?php endif; ?>
      </td>
      <td>{!tpl_userlink($req['user_id'])}</td>
      <td>{formatDate($req['time_submitted'], true)}</td>
      <td>{$req['reason_given']}</td>
      <td>
        <?php if ($req['request_type'] == 1) : ?>
        <a class="button" href="{CreateUrl('details', $req['task_id'], null, array('showclose' => 1))}#formclosetask">{L('accept')}</a>
        <?php elseif ($req['request_type'] == 2) : ?>
        <a class="button" href="{$_SERVER['SCRIPT_NAME']}?do=details&amp;action=reopen&task_id={$req['task_id']}">{L('accept')}</a>
        <?php endif; ?>
        <a href="#" class="button" onclick="showhidestuff('denyform{$req['request_id']}');">{L('deny')}</a>
        <div id="denyform{$req['request_id']}" class="denyform">
          <form action="{CreateUrl('pm', 'pendingreq', $proj->id)}" method="post">
            <div>
              <input type="hidden" name="action" value="denypmreq" />
              <input type="hidden" name="req_id" value="{$req['request_id']}" />
              <label for="deny_reason{$req['request_id']}" class="inline">{L('reasonfordeinal')}</label><br />
              <textarea cols="40" rows="5" name="deny_reason" id="deny_reason{$req['request_id']}"></textarea>
              <br />
              <button type="submit">{L('deny')}</button>
            </div>
          </form>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
