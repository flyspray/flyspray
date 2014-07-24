<div id="related" class="tab">
  
  <div class="related">
    <form method="post" action="<?php echo Filters::noXSS(CreateUrl('details', $task_details['task_id'])); ?>#related" >
      <table id="tasks_related" class="userlist">
        <tr>
          <th>
            <a class="toggle_selected" href="javascript:ToggleSelected('tasks_related')">
              <!--<img title="<?php echo Filters::noXSS(L('toggleselected')); ?>" alt="<?php echo Filters::noXSS(L('toggleselected')); ?>" src="<?php echo Filters::noXSS($this->get_image('kaboodleloop')); ?>" width="16" height="16" />-->
            </a>
          </th>
          <th><?php echo Filters::noXSS(L('tasksrelated')); ?> (<?php echo Filters::noXSS(count($related)); ?>)</th>
        </tr>
        <?php
          foreach ($related as $row):
        ?>
        <tr>
          <td class="ttcolumn">
            <input type="checkbox" name="related_id[]" <?php echo tpl_disableif(!$user->can_edit_task($task_details)); ?> value="<?php echo Filters::noXSS($row['related_id']); ?>" /></td>
          <td><?php echo tpl_tasklink($row); ?></td>
        </tr>
        <?php endforeach; ?>
        <tr>
          <td colspan="2">
            <input type="hidden" name="action" value="remove_related" />
            <input type="hidden" name="task_id" value="<?php echo Filters::noXSS($task_details['task_id']); ?>" />
            <button type="submit"><?php echo Filters::noXSS(L('remove')); ?></button>
          </td>
        </tr> 
      </table>
    </form>
  </div>
    
  <div class="related">
    <table id="duplicate_tasks" class="userlist">
      <tr>
        <th><?php echo Filters::noXSS(L('duplicatetasks')); ?> (<?php echo Filters::noXSS(count($duplicates)); ?>)</th>
      </tr>
      <?php foreach ($duplicates as $row): ?>
      <tr><td><?php echo tpl_tasklink($row); ?></td></tr>
      <?php endforeach; ?>
    </table>
  </div>

  <?php if ($user->can_edit_task($task_details) && !$task_details['is_closed']): ?>
  <form class="clear" action="<?php echo Filters::noXSS(CreateUrl('details', $task_details['task_id'])); ?>#related" method="post" id="formaddrelatedtask">
    <div>
      <input type="hidden" name="action" value="details.add_related" />
      <input type="hidden" name="task_id" value="<?php echo Filters::noXSS($task_details['task_id']); ?>" />
      <label><?php echo Filters::noXSS(L('addnewrelated')); ?>

        <input name="related_task" id="related_task_input" type="text" class="text" size="10" maxlength="10" /></label>
      <button type="submit" onclick="return checkok('<?php echo Filters::noJsXSS($baseurl); ?>javascript/callbacks/checkrelated.php?related_task=' + $('related_task_input').value + '&amp;project=<?php echo Filters::noXSS($proj->id); ?>', '<?php echo Filters::noJsXSS(L('relatedproject')); ?>', 'formaddrelatedtask')"><?php echo Filters::noXSS(L('add')); ?></button>
    </div>
  </form>
  <?php endif; ?>
</div>
