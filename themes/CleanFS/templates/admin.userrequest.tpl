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
      <th><?php echo Filters::noXSS(L('emailaddress')); ?></th>
      <th class="pm-buttons"> </th>
    </tr>
    <?php foreach ($pendings as $req): ?>
    <tr>
      <td>
      New User Request
      </td>
      <td><?php echo tpl_userlink($req['submitted_by']); ?></td>
      <td><?php echo Filters::noXSS(formatDate($req['time_submitted'], true)); ?></td>
      <td>get email adress later</td>
      <td>
        <form style="display:inline" action="<?php echo Filters::noXSS(CreateUrl('edituser', $req['submitted_by'])); ?>" method="post">
	<a class="button" href="#" onclick="this.parentNode.submit();"><?php echo Filters::noXSS(L('accept')); ?></a>
	<input type="hidden" name="action" value="approve.user"/>
	<input type="hidden" name="user_id" value="<?php echo $req['submitted_by']; ?>"/>
	<input type="hidden" name="account_enabled" value="1"/>
	</form>
        <a href="#" class="button" onclick="showhidestuff('denyform<?php echo Filters::noXSS($req['request_id']); ?>');"><?php echo Filters::noXSS(L('deny')); ?></a>
        <div id="denyform<?php echo Filters::noXSS($req['request_id']); ?>" class="denyform">
          <form action="" method="post">
            <div>
              <input type="hidden" name="action" value="denyuserreq" />
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
