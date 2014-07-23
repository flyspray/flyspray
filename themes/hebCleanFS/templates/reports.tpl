<h3><?php echo Filters::noXSS(L('eventlog')); ?></h3>
<div class="box">
    <form action="<?php echo Filters::noXSS($baseurl); ?>index.php" method="get">
      <table id="event1">
        <tr>
          <td><label for="events[]"><?php echo Filters::noXSS(L('events')); ?></label></td>
          <td>
              <select name="events[]" class='eventlist' multiple="multiple" id="events[]" size="<?php echo Filters::noXSS(count($events)+count($user_events)+2); ?>">
              <optgroup label="<?php echo Filters::noXSS(L('Tasks')); ?>">
              <?php echo tpl_options($events, Req::val('events')); ?>

              </optgroup>
              <optgroup label="<?php echo Filters::noXSS(L('users')); ?>">
              <?php echo tpl_options($user_events, Req::val('events')); ?>

              </optgroup>
              </select>    
          </td>
          <td>
              <div>
                  <label class="inline" for="fromdate"><?php echo Filters::noXSS(L('from')); ?></label>
                  <?php echo tpl_datepicker('fromdate'); ?>

                  <?php echo tpl_datepicker('todate', L('to')); ?>

              </div>
              
              <div>
                  <label for="event_number"><?php echo Filters::noXSS(L('show')); ?></label>
                  <select name="event_number" id="event_number">
                   <?php echo tpl_options(array(-1 => L('all'), 10 => 10, 20 => 20, 50 => 50, 100 => 100, 200 => 200), Req::val('event_number', 20)); ?>

                  </select>
                  <?php echo Filters::noXSS(L('events')); ?>

              </div>
          </td>
        </tr>
      </table>
      
      <input type="hidden" name="project" value="<?php echo Filters::noXSS($proj->id); ?>" />
      <input type="hidden" name="do" value="reports" />
      <button type="submit" name="submit"><?php echo Filters::noXSS(L('show')); ?></button>
    </form>
    
    <?php if ($histories): ?>
    <div id="tasklist">
    <table id="tasklist_table">
     <thead>
      <tr>
        <th>
          <a href="<?php echo Filters::noXSS(CreateURL('reports', null, null, array('sort' => (Req::val('order') == 'type' && $sort == 'DESC') ? 'asc' : 'desc', 'order' => 'type') + $_GET)); ?>">
            <?php echo Filters::noXSS(L('event')); ?>

          </a>
        </th>
        <th>
          <a href="<?php echo Filters::noXSS(CreateURL('reports', null, null, array('sort' => (Req::val('order') == 'user' && $sort == 'DESC') ? 'asc' : 'desc', 'order' => 'user') + $_GET)); ?>">
            <?php echo Filters::noXSS(L('user')); ?>

          </a>
        </th>
        <th>
          <a href="<?php echo Filters::noXSS(CreateURL('reports', null, null, array('sort' => (Req::val('order') == 'date' && $sort == 'DESC') ? 'asc' : 'desc', 'order' => 'date') + $_GET)); ?>">
            <?php echo Filters::noXSS(L('eventdate')); ?>

          </a>
        </th>
        <th><?php echo Filters::noXSS(L('summary')); ?></th>
      </tr>
     </thead>
      <?php foreach ($histories as $history): ?>
        <?php if (isset($events[$history['event_type']])): ?>
      <tr class="severity1"><?php /* just for different colors */ ?>
        <td><?php echo Filters::noXSS($events[$history['event_type']]); ?></td>
        <?php else: ?>
      <tr class="severity2">
        <td><?php echo Filters::noXSS($user_events[$history['event_type']]); ?></td>
        <?php endif; ?>
        <td><?php echo tpl_userlink($history['user_id']); ?></td>
        <td><?php echo Filters::noXSS(formatDate($history['event_date'], true)); ?></td>
        <?php if ($history['event_type'] > 29):
              $user_data = unserialize($history['new_value']); ?>
        <td>
          <a href="javascript:showhidestuff('h<?php echo Filters::noXSS($history['history_id']); ?>')"><?php echo Filters::noXSS(L('detailedinfo')); ?></a>
          <div class="hide popup" id="h<?php echo Filters::noXSS($history['history_id']); ?>">
            <table>
              <tr>
                <th><?php echo Filters::noXSS(L('username')); ?></th>
                <td><?php echo Filters::noXSS($user_data['user_name']); ?></td>
              </tr>
              <tr>
                <th><?php echo Filters::noXSS(L('realname')); ?></th>
                <td><?php echo Filters::noXSS($user_data['real_name']); ?></td>
              </tr>
              <tr>
                <th><?php echo Filters::noXSS(L('email')); ?></th>
                <td><?php echo Filters::noXSS($user_data['email_address']); ?></td>
              </tr>
              <tr>
                <th><?php echo Filters::noXSS(L('jabber')); ?></th>
                <td><?php echo Filters::noXSS($user_data['jabber_id']); ?></td>
              </tr>
            </table>
          </div>
        </td>
        <?php else: ?>
        <td><?php echo tpl_tasklink($history); ?></td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>
    </table>
    </div>
    <?php endif; ?>
</div>