<?php if (!$task_details['is_closed']):

$reminder_intervals = [ 'hours' => 3600, 'days' => 86400, 'weeks' => 604800 ];

$desc_intervals = $reminder_intervals;
arsort($desc_intervals);

?>
  <div id="remind" class="tab">
  <?php echo tpl_form(Filters::noXSS(CreateUrl('details', $task_details['task_id'])).'#remind'); ?>
    <?php if (count($reminders)): ?>
    <table id="reminders" class="userlist">
    <thead>
    <tr>
      <th>
        <a class="toggle_selected" title="<?php echo Filters::noXSS(L('toggleselected')); ?>"
        href="javascript:ToggleSelected('reminders')"></a>
      </th>
      <th><?php echo Filters::noXSS(L('user')); ?></th>
      <th><?php echo Filters::noXSS(L('startat')); ?></th>
      <th><?php echo Filters::noXSS(L('frequency')); ?></th>
      <th><?php echo Filters::noXSS(L('message')); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($reminders as $row): ?>
    <tr>
      <td class="ttcolumn">
        <input type="checkbox" name="reminder_id[]" <?php echo tpl_disableif(!$user->can_edit_task($task_details)); ?> value="<?php echo Filters::noXSS($row['reminder_id']); ?>" />
      </td>
     <td><?php echo tpl_userlink($row['user_id']); ?></td>
     <td><?php echo Filters::noXSS(formatDate($row['start_time'])); ?></td>
     <?php
      // Work out the unit of time to display
      unset($how_often, $r_h, $r_m, $r_s);

      foreach ($desc_intervals as $intvl_unit => $intvl_time) {
        if ($row['how_often'] % $intvl_time == 0) {
          $how_often = floor($row['how_often'] / $intvl_time) . ' ' . L($intvl_unit);
          break;
        }
      }

      if (!isset($how_often)) {
        $r_d = floor($row['how_often'] / 86400);
        $r_h = floor(($row['how_often'] - ($r_d * 86400)) / 3600);
        $r_m = floor(($row['how_often'] - ($r_d * 86400) - ($r_h * 3600)) / 60);
        $r_s = (($row['how_often'] - ($r_d * 86400) - ($r_h * 3600) - $r_m * 60)) % 60;

        //$how_often = sprintf("%1d " . L('days') . " %02d:%02d:%02d", $r_d, $r_h, $r_m, $r_s);
        $how_often = sprintf("%1d %s %02d:%02d:%02d", $r_d, L('days'), $r_h, $r_m, $r_s);
      }
     ?>
     <td><?php echo Filters::noXSS($how_often); ?></td>
     <td><?php echo TextFormatter::render($row['reminder_message']); ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
    <tr><td colspan="5">
      <input type="hidden" name="action" value="deletereminder" />
      <input type="hidden" name="task_id" value="<?php echo Filters::noXSS($task_details['task_id']); ?>" />
      <button type="submit"><?php echo Filters::noXSS(L('remove')); ?></button></td>
    </tr>
    </tfoot>
  </table>
  <?php endif; ?>  
  </form>

  <fieldset><legend><?php echo Filters::noXSS(L('addreminder')); ?></legend>
  <?php echo tpl_form(Filters::noXSS(CreateUrl('details', $task_details['task_id'])).'#remind',null,null,null,'id="formaddreminder"'); ?>
    <div>
      <input type="hidden" name="action" value="details.addreminder" />
      <input type="hidden" name="task_id" value="<?php echo Filters::noXSS($task_details['task_id']); ?>" />
      <label class="default multisel" for="reminder_user"><?php echo Filters::noXSS(L('remindthisuser')); ?></label>
      <?php echo tpl_userselect('reminder_user', Req::val('reminder_user'), 'reminder_user'); ?>
      <br />
      <label for="reminder_repeat"><?php echo Filters::noXSS(L('thisoften')); ?></label>
      <input class="text" type="text" value="<?php echo Filters::noXSS(Req::val('reminder_repeat')); ?>" id="reminder_repeat" name="reminder_repeat" size="3" maxlength="3" />
      <select class="adminlist" name="reminder_interval">
        <?php echo tpl_options(array(3600 => L('hours'), 86400 => L('days'), 604800 => L('weeks')), Req::val('reminder_interval')); ?>
      </select>
      <br />
      <?php echo tpl_datepicker('reminder_start_date', L('startat'), Req::val('reminder_start_date', formatDate(time()))); ?>
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
