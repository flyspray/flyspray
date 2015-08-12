<div id="toolbox">
  <h3><?php echo Filters::noXSS(L('pendingrequests')); ?></h3>
  
  <?php if (!count($pendings)): ?>
  <?php echo Filters::noXSS(L('nopendingreq')); ?>

  <?php else: ?>
  <table class="requests">
    <tr>
      <th><?php echo Filters::noXSS(L('eventdesc')); ?></th>
      <th><?php echo Filters::noXSS(L('requestedby')); ?></th>
      <th><?php echo Filters::noXSS(L('daterequested')); ?></th>
      <th><?php echo Filters::noXSS(L('reasongiven')); ?></th>
      <th class="pm-buttons"> </th>
    </tr>
    <?php foreach ($pendings as $req): ?>
    <tr>
      <td>
      <?php if ($req['request_type'] == 1) : ?>
      <?php echo Filters::noXSS(L('closetask')); ?> -
      <a href="<?php echo Filters::noXSS(CreateURL('details', $req['task_id'])); ?>">FS#<?php echo Filters::noXSS($req['task_id']); ?> :
        <?php echo Filters::noXSS($req['item_summary']); ?></a>
      <?php elseif ($req['request_type'] == 2) : ?>
      <?php echo Filters::noXSS(L('reopentask')); ?> -
      <a href="<?php echo Filters::noXSS(CreateURL('details', $req['task_id'])); ?>">FS#<?php echo Filters::noXSS($req['task_id']); ?> :
        <?php echo Filters::noXSS($req['item_summary']); ?></a>
      <?php endif; ?>
      </td>
      <td><?php echo tpl_userlink($req['user_id']); ?></td>
      <td><?php echo Filters::noXSS(formatDate($req['time_submitted'], true)); ?></td>
      <td><?php echo Filters::noXSS($req['reason_given']); ?></td>
      <td>
        <?php if ($req['request_type'] == 1) : ?>
        <a class="button" href="<?php echo Filters::noXSS(CreateUrl('details', $req['task_id'], null, array('showclose' => 1))); ?>#formclosetask"><?php echo Filters::noXSS(L('accept')); ?></a>
        <?php elseif ($req['request_type'] == 2) : ?>
        <a class="button" href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=details&amp;action=reopen&task_id=<?php echo Filters::noXSS($req['task_id']); ?>"><?php echo Filters::noXSS(L('accept')); ?></a>
        <?php endif; ?>
        <a href="#" class="button" onclick="showhidestuff('denyform<?php echo Filters::noXSS($req['request_id']); ?>');"><?php echo Filters::noXSS(L('deny')); ?></a>
        <div id="denyform<?php echo Filters::noXSS($req['request_id']); ?>" class="denyform">
          <form action="<?php echo Filters::noXSS(CreateUrl('pm', 'pendingreq', $proj->id)); ?>" method="post">
            <div>
              <input type="hidden" name="action" value="denypmreq" />
              <input type="hidden" name="req_id" value="<?php echo Filters::noXSS($req['request_id']); ?>" />
              <label for="deny_reason<?php echo Filters::noXSS($req['request_id']); ?>" class="inline"><?php echo Filters::noXSS(L('reasonfordeinal')); ?></label><br />
              <textarea cols="40" rows="5" name="deny_reason" id="deny_reason<?php echo Filters::noXSS($req['request_id']); ?>"></textarea>
              <br />
              <button type="submit"><?php echo Filters::noXSS(L('deny')); ?></button>
            </div>
          </form>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
