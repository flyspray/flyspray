<?php if (!$task_details['is_closed']): ?>
  <div id="remind" class="tab">
  
  <form method="post" action="<?php echo Filters::noXSS(CreateUrl('details', $task_details['task_id'])); ?>#remind" >
    <?php if (count($reminders)): ?>
    <table id="reminders" class="userlist">
    <tr>
      <th>
        <a class="toggle_selected" href="javascript:ToggleSelected('reminders')">
          <!--<img title="<?php echo Filters::noXSS(L('toggleselected')); ?>" alt="<?php echo Filters::noXSS(L('toggleselected')); ?>" src="<?php echo Filters::noXSS($this->get_image('kaboodleloop')); ?>" width="16" height="16" />-->
        </a>
      </th>
      <th><?php echo Filters::noXSS(L('user')); ?></th>
      <th><?php echo Filters::noXSS(L('startat')); ?></th>
      <th><?php echo Filters::noXSS(L('frequency')); ?></th>
      <th><?php echo Filters::noXSS(L('message')); ?></th>
    </tr>
    
    <?php foreach ($reminders as $row): ?>
    <tr>
      <td class="ttcolumn">
        <input type="checkbox" name="reminder_id[]" <?php echo tpl_disableif(!$user->can_edit_task($task_details)); ?> value="<?php echo Filters::noXSS($row['reminder_id']); ?>" />
      </td>
     <td><?php echo tpl_userlink($row['user_id']); ?></td>
     <td><?php echo Filters::noXSS(formatDate($row['start_time'])); ?></td>
     <?php
      // Work out the unit of time to display
      if ($row['how_often'] < 86400) {
          $how_often = $row['how_often'] / 3600 . ' ' . L('hours');
      } elseif ($row['how_often'] < 604800) {
          $how_often = $row['how_often'] / 86400 . ' ' . L('days');
      } else {
          $how_often = $row['how_often'] / 604800 . ' ' . L('weeks');
      }
     ?>
     <td><?php echo Filters::noXSS($how_often); ?></td>
     <td><?php echo TextFormatter::render($row['reminder_message'], true); ?></td>
  </tr>
    <?php endforeach; ?>
    <tr><td colspan="4">
      <input type="hidden" name="action" value="deletereminder" />
      <input type="hidden" name="task_id" value="<?php echo Filters::noXSS($task_details['task_id']); ?>" />
      <button type="submit"><?php echo Filters::noXSS(L('remove')); ?></button></td></tr>
  </table>
  <?php endif; ?>  
  </form>

  <fieldset><legend><?php echo Filters::noXSS(L('addreminder')); ?></legend>
  <form action="<?php echo Filters::noXSS(CreateUrl('details', $task_details['task_id'])); ?>#remind" method="post" id="formaddreminder">
    <div>
      <input type="hidden" name="action" value="details.addreminder" />
      <input type="hidden" name="task_id" value="<?php echo Filters::noXSS($task_details['task_id']); ?>" />

        <label class="default multisel" for="to_user_id"><?php echo Filters::noXSS(L('remindthisuser')); ?></label>
        <?php echo tpl_userselect('to_user_id', Req::val('to_user_id'), 'to_user_id'); ?>

      <br />

      <label for="timeamount1"><?php echo Filters::noXSS(L('thisoften')); ?></label>
      <input class="text" type="text" value="<?php echo Filters::noXSS(Req::val('timeamount1')); ?>" id="timeamount1" name="timeamount1" size="3" maxlength="3" />
      <select class="adminlist" name="timetype1">
        <?php echo tpl_options(array(3600 => L('hours'), 86400 => L('days'), 604800 => L('weeks')), Req::val('timetype1')); ?>

      </select>

      <br />

      <?php echo tpl_datepicker('timeamount2', L('startat'), Req::val('timeamount2', formatDate(time()))); ?>


      <br />
      <textarea class="text" name="reminder_message"
        rows="10" cols="72"><?php echo Filters::noXSS(Req::val('reminder_message', L('defaultreminder') . "\n\n" . CreateURL('details', $task_details['task_id']))); ?></textarea>
      <br />
      <button type="submit"><?php echo Filters::noXSS(L('addreminder')); ?></button>
    </div>
  </form>
  </fieldset>
</div>
<?php endif; ?>
