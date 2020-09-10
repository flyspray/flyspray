<script>
function toggleCheckbox(id)
{
	var el = document.getElementById(id);
	if (el != null) {
		if (el.checked) {
			el.checked = false;
		} else {
			el.checked = true;
		}
	}
}
</script>
<form action="<?php echo Filters::noXSS(createURL($do, 'editallusers'));?>" method="get">
<input type="hidden" name="do" value="admin" />
<input type="hidden" name="area" value="editallusers" />
<fieldset>
<legend>Columns</legend>
<div class="warning">Note: Choosing the "statistics" option here can result in a slow SQL query depending on your amount of existing tasks and users! The other options are fast.</div>
<select name="showfields[]" multiple="multiple" size="3">
<option value="-">---basic---</option>
<option value="stats"<?php echo $showstats? ' selected="selected"':'';?>>statistics</option>
<option value="ltf"<?php echo $showltf? ' selected="selected"':'';?>>language, timezone, dateformat</option>
</select>
</fieldset>
<style>
label.userstatus {border:1px solid #ccc; padding:4px; margin:3px; max-width:100px; border-radius:3px;}
input[value=""]:checked ~ label#s_all {background-color:#ddd;}
input[value="1"]:checked ~ label#s_enabled {background-color:#cf6;}
input[value="0"]:checked ~ label#s_disabled {background-color:#f90;}
</style>
<fieldset>
<legend>Filter</legend>
<input type="radio" id="status_all" name="status" value=""<?= Get::val('status')=='' ? ' checked="checked"':'' ?>>
<label class="userstatus" id="s_all" for="status_all">all users</label>
<input type="radio" id="status_enabled" name="status" value="1"<?= Get::val('status')==='1' ? ' checked="checked"':'' ?>>
<label class="userstatus" id="s_enabled" for="status_enabled">enabled users only</label>
<input type="radio" id="status_disabled" name="status" value="0"<?= Get::val('status')==='0' ? ' checked="checked"':'' ?>>
<label class="userstatus" id="s_disabled" for="status_disabled">disabled users only</label>
</fieldset>
<button type="submit">Show selected fields</button>
</form>
<?php if ($usercount): ?>
<div class="pagination">
	<span><?php echo sprintf('Showing Users %d - %d of %d', $offset + 1, ($offset + $perpage > $usercount ? $usercount : $offset + $perpage), $usercount); ?></span>
	<?php echo pagenums($pagenum, $perpage, $usercount, 'admin', 'editallusers'); ?>
</div>
<?php 
if ($do == 'admin'): echo tpl_form(Filters::noXSS(createURL($do, 'editallusers')), null, null, null, 'id="editallusers"');
               else: echo tpl_form(Filters::noXSS($_SERVER['SCRIPT_NAME']), null, null, null, 'id="editallusers"');
endif;
if ($do == 'admin'): ?>
	<input type="hidden" name="action" value="admin.editallusers" />
	<input type="hidden" name="do" value="admin" />
	<input type="hidden" name="area" value="editallusers" />
<?php endif; ?>
<table class="bulkedituser">
	<thead>
	<tr class="account_header">
		<th></th>
		<th><?= eL('realname') ?></th>
		<th><?= eL('username') ?></th>
		<th><?= eL('emailaddress') ?></th>
		<th><?= eL('jabberid') ?></th>
                <th><?= eL('regdate') ?></th>
		<th><?= eL('lastlogin') ?></th>
<?php if($showstats): ?>
		<th>opened_by</th>
		<th>closed_by</th>
		<th>last_edited_by</th>
		<th>assigned</th>
		<th>comments</th>
		<th>votes</th>
<?php endif; ?>
<?php if($showltf): ?>
		<th><?= eL('language') ?></th>
		<th><?= eL('timezone') ?></th>
		<th><?= eL('dateformat') ?></th>
		<th><?= eL('dateformat_extended') ?></th>
<?php endif; ?>
	</tr>
	</thead>
	<tbody>
<?php foreach ($users as $usr): ?>
<tr class="<?php echo ($usr['account_enabled']) ? 'account_enabled':'account_disabled'; ?>" onclick="toggleCheckbox('<?php echo $usr['user_id']; ?>')">
	<td><input id="<?php echo $usr['user_id'] ?>" onclick="event.stopPropagation()" type="checkbox" name="checkedUsers[]" value="<?php echo $usr['user_id']; ?>"></td>
	<td><a href="<?php echo createURL('edituser', $usr['user_id'] ); ?>"><?php echo Filters::noXSS($usr['real_name']); ?></a></td>
	<td><?php echo $usr['user_name']; ?></td>
	<td<?= ($usr['notify_type']==0 || $usr['notify_type']==2) ? ' class="inactive"':''; ?>><?php echo Filters::noXSS($usr['email_address']); ?></td>
	<td<?= ($usr['notify_type']==0 || $usr['notify_type']==1) ? ' class="inactive"':''; ?>><?php echo Filters::noXSS($usr['jabber_id']); ?></td>
	<td><?php echo formatDate($usr['register_date']); ?></td>
	<td><?php echo formatDate($usr['last_login']); ?></td>
<?php if($showstats): ?>
	<td><?php echo $usr['countopen']>0 ? $usr['countopen']:''; ?></td>
	<td><?php echo $usr['countclose']>0 ? $usr['countclose']:''; ?></td>
	<td><?php echo $usr['countlastedit']>0 ? $usr['countlastedit']:''; ?></td>
	<td><?php echo $usr['countassign']>0 ? $usr['countassign']:''; ?></td>
	<td><?php echo $usr['countcomments']>0 ? $usr['countcomments']:''; ?></td>
	<td><?php echo $usr['countvotes']>0 ? $usr['countvotes']:''; ?></td>
<?php endif; ?>
<?php if($showltf): ?>
	<td><?php echo Filters::noXSS($usr['lang_code']); ?></td>
	<td><?php echo Filters::noXSS($usr['time_zone']); ?></td>
	<td><?php echo Filters::noXSS($usr['dateformat']); ?></td>
	<td><?php echo Filters::noXSS($usr['dateformat_extended']); ?></td>
<?php endif; ?>
	</tr>
<?php endforeach; ?>
	</tbody>
</table>

<button type="submit" id="buSubmit" name="enable"><?= eL('enableaccounts') ?></button>
<button type="submit" id="buSubmit" name="disable"><?= eL('disableaccounts') ?></button>
<button type="submit" id="buSubmit" name="delete"><?= eL('deleteaccounts') ?></button>

<!-- TODO Should still add these to bulk edit, but hasn't been done yet
<ul class="form_elements">
<li class="required">
      <label for="notify_type"><?= eL('notifications') ?></label>
      <select id="notify_type" name="notify_type">
        <?php echo tpl_options($fs->getNotificationOptions(), Req::val('notify_type')); ?>

      </select>
</li>
<li>
      <label for="time_zone"><?= eL('timezone') ?></label>
      <select id="time_zone" name="time_zone">
        <?php
          $times = array();
          for ($i = -12; $i <= 13; $i++) {
            $times[$i] = L('GMT') . (($i == 0) ? ' ' : (($i > 0) ? '+' . $i : $i));
          }
        ?>
        <?php echo tpl_options($times, Req::val('time_zone', 0)); ?>

      </select>
</li>

    <?php if (isset($groups)): ?>
    <li>
      <label for="groupin"><?= eL('globalgroup') ?></label>
      <select id="groupin" class="adminlist" name="group_in">
        <?php echo tpl_options($groups, Req::val('group_in')); ?>

      </select>
    </li>
    <?php endif; ?>

  </ul>
  <p><button type="submit" id="buSubmit"><?= eL('updateaccounts') ?></button></p>
-->
</form>
<div class="pagination">
	<span><?php echo sprintf('Showing Users %d - %d of %d', $offset + 1, ($offset + $perpage > $usercount ? $usercount : $offset + $perpage), $usercount); ?></span>
	<?php echo pagenums($pagenum, $perpage, $usercount, 'admin', 'editallusers'); ?>
</div>
<?php else: ?>
	<div class="noresult"><strong><?= eL('noresults') ?></strong></div>
<?php endif; ?>
