<fieldset id="events"><legend>{L('eventlog')}</legend>
  <form action="{$baseurl}index.php" method="get">
    <table id="event1">
      <tr>
        <td><label for="events[]">{L('events')}</label></td>
        <td>
            <select name="events[]" multiple="multiple" id="events[]" size="{count($events)+count($user_events)+2}">
            <optgroup label="{L('Tasks')}">
            {!tpl_options($events, Req::val('events'))}
            </optgroup>
            <optgroup label="{L('users')}">
            {!tpl_options($user_events, Req::val('events'))}
            </optgroup>
            </select>    
        </td>
        <td>
            <div>
                <label class="inline" for="fromdate">{L('from')}</label>
                {!tpl_datepicker('fromdate')}
                {!tpl_datepicker('todate', L('to'))}
            </div>
            
            <div>
                <label for="event_number">{L('show')}</label>
                <select name="event_number" id="event_number">
                 {!tpl_options(array(-1 => L('all'), 10 => 10, 20 => 20, 50 => 50, 100 => 100, 200 => 200), Req::val('event_number', 20))}
                </select>
                {L('events')}
            </div>
        </td>
      </tr>
    </table>
    
    <input type="hidden" name="project" value="{$proj->id}" />
    <input type="hidden" name="do" value="reports" />
    <button type="submit" name="submit">{L('show')}</button>
  </form>
  
  <?php if ($histories): ?>
  <div id="tasklist">
  <table id="tasklist_table">
   <thead>
    <tr>
      <th>
        <a href="{CreateURL('reports', null, null, array('sort' => (Req::val('order') == 'type' && $sort == 'DESC') ? 'asc' : 'desc', 'order' => 'type') + $_GET)}">
          {L('event')}
        </a>
      </th>
      <th>
        <a href="{CreateURL('reports', null, null, array('sort' => (Req::val('order') == 'user' && $sort == 'DESC') ? 'asc' : 'desc', 'order' => 'user') + $_GET)}">
          {L('user')}
        </a>
      </th>
      <th>
        <a href="{CreateURL('reports', null, null, array('sort' => (Req::val('order') == 'date' && $sort == 'DESC') ? 'asc' : 'desc', 'order' => 'date') + $_GET)}">
          {L('eventdate')}
        </a>
      </th>
      <th>{L('summary')}</th>
    </tr>
   </thead>
    <?php foreach ($histories as $history): ?>
      <?php if (isset($events[$history['event_type']])): ?>
    <tr class="severity1"><?php /* just for different colors */ ?>
      <td>{$events[$history['event_type']]}</td>
      <?php else: ?>
    <tr class="severity2">
      <td>{$user_events[$history['event_type']]}</td>
      <?php endif; ?>
      <td>{!tpl_userlink($history['user_id'])}</td>
      <td>{formatDate($history['event_date'], true)}</td>
      <?php if ($history['event_type'] > 29):
            $user_data = unserialize($history['new_value']); ?>
      <td>
        <a href="javascript:showhidestuff('h{$history['history_id']}')">{L('detailedinfo')}</a>
        <div class="hide popup" id="h{$history['history_id']}">
          <table>
            <tr>
              <th>{L('username')}</th>
              <td>{$user_data['user_name']}</td>
            </tr>
            <tr>
              <th>{L('realname')}</th>
              <td>{$user_data['real_name']}</td>
            </tr>
            <tr>
              <th>{L('email')}</th>
              <td>{$user_data['email_address']}</td>
            </tr>
            <tr>
              <th>{L('jabber')}</th>
              <td>{$user_data['jabber_id']}</td>
            </tr>
          </table>
        </div>
      </td>
      <?php else: ?>
      <td>{!tpl_tasklink($history)}</td>
      <?php endif; ?>
    </tr>
    <?php endforeach; ?>
  </table>
  </div>
  <?php endif; ?>
</fieldset>
