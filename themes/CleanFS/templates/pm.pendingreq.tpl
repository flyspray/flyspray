<div id="toolbox">
  <h3><?= eL('pendingrequests') ?></h3>
  
  <?php if (!count($pendings)): ?>
  <?= eL('nopendingreq') ?>
  <?php else: ?>
  <table class="requests">
  <thead>
    <tr>
      <th><?= eL('eventdesc') ?></th>
      <th><?= eL('requestedby') ?></th>
      <th><?= eL('daterequested') ?></th>
      <th><?= eL('reasongiven') ?></th>
      <th class="pm-buttons"> </th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($pendings as $req): ?>
    <tr>
      <td>
      <?php if ($req['request_type'] == 1) : ?>
      <?= eL('closetask') ?> -
      <a href="<?php echo Filters::noXSS(createURL('details', $req['task_id'])); ?>">FS#<?php echo Filters::noXSS($req['task_id']); ?> :
        <?php echo Filters::noXSS($req['item_summary']); ?></a>
      <?php elseif ($req['request_type'] == 2) : ?>
      <?= eL('reopentask') ?> -
      <a href="<?php echo Filters::noXSS(createURL('details', $req['task_id'])); ?>">FS#<?php echo Filters::noXSS($req['task_id']); ?> :
        <?php echo Filters::noXSS($req['item_summary']); ?></a>
      <?php endif; ?>
      </td>
      <td><?php echo tpl_userlink($req['user_id']); ?></td>
      <td><?php echo Filters::noXSS(formatDate($req['time_submitted'], true)); ?></td>
      <td><?php echo Filters::noXSS($req['reason_given']); ?></td>
      <td>
        <?php if ($req['request_type'] == 1) : ?>
        <a class="button" href="#" onclick="showhidestuff('closeform<?php echo Filters::noXSS($req['request_id']); ?>');"><?= eL('accept') ?> ...</a>
        <div id="closeform<?php echo Filters::noXSS($req['request_id']); ?>" class="denyform">
        <?php echo tpl_form(Filters::noXSS(createURL('pm', 'pendingreq', $proj->id))); ?>
            <div>
                <input type="hidden" name="action" value="details.close"/>
                <input type="hidden" name="task_id" value="<?php echo Filters::noXSS($req['task_id']); ?>"/>
                <select class="adminlist" name="resolution_reason" onmouseup="event.stopPropagation();">
                    <option value="0"><?= eL('selectareason') ?></option>
                    <?php echo tpl_options($proj->listResolutions(), Req::val('resolution_reason')); ?>
                </select>
                <button type="submit"><?= eL('closetask') ?></button>
                <br/>
                <label class="default text" for="closure_comment"><?= eL('closurecomment') ?></label>
                <textarea class="text" id="closure_comment" name="closure_comment" rows="3"
                          cols="25"><?php echo Filters::noXSS(Req::val('closure_comment')); ?></textarea>
                <label><?php echo tpl_checkbox('mark100', Req::val('mark100', !(Req::val('action') == 'details.close'))); ?>&nbsp;<?= eL('mark100') ?></label>
            </div>
        </form>
        </div>
        <?php elseif ($req['request_type'] == 2) : ?>
        <?php echo tpl_form(Filters::noXSS(createUrl('pm', 'pendingreq', $proj->id)), null, null, null, 'style="display:inline"'); ?>
        <input type="hidden" name="action" value="reopen" />
        <input type="hidden" name="task_id" value="<?php echo Filters::noXSS($req['task_id']); ?>">
        <input type="submit" class="button" value="<?= eL('accept') ?>">
        </form> 
        <?php endif; ?>
        <a href="#" class="button" onclick="showhidestuff('denyform<?php echo Filters::noXSS($req['request_id']); ?>');"><?= eL('deny') ?> ...</a>
        <div id="denyform<?php echo Filters::noXSS($req['request_id']); ?>" class="denyform">
          <?php echo tpl_form(Filters::noXSS(createUrl('pm', 'pendingreq', $proj->id))); ?>
            <div>
              <input type="hidden" name="action" value="denypmreq" />
              <input type="hidden" name="req_id" value="<?php echo Filters::noXSS($req['request_id']); ?>" />
              <label for="deny_reason<?php echo Filters::noXSS($req['request_id']); ?>" class="inline"><?= eL('reasonfordeinal') ?></label><br />
              <textarea cols="40" rows="5" name="deny_reason" id="deny_reason<?php echo Filters::noXSS($req['request_id']); ?>"></textarea>
              <br />
              <button type="submit"><?= eL('deny') ?></button>
            </div>
          </form>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
  </table>
  <?php endif; ?>
</div>
