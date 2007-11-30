<h3>{$proj->prefs['project_title']} :: {L('newtask')}</h3>

<div id="taskdetails">
    <form enctype="multipart/form-data" action="{CreateUrl('newtask', $proj->id)}" method="post">
    <h2 class="severity{Req::val('task_severity', 2)} summary" id="edit_summary">
      <label for="itemsummary">{L('summary')}</label>
      <input id="itemsummary" class="text severity{Req::val('task_severity', 2)}" type="text" value="{Req::val('item_summary')}"
        name="item_summary" size="80" maxlength="100" />
    </h2>

    <table><tr><td id="taskfieldscell"><?php // small layout table ?>

    <div id="taskfields">
      <table>
        <tr>
          <td><label for="tasktype">{L('tasktype')}</label></td>
          <td>
            <select name="task_type" id="tasktype">
              {!tpl_options($proj->listTaskTypes(), Req::val('task_type'))}
            </select>
          </td>
        </tr>

        <tr>
          <td><label for="category">{L('category')}</label></td>
          <td>
            <select class="adminlist" name="product_category" id="category">
              {!tpl_options($proj->listCategories(), Req::val('product_category'))}
            </select>
          </td>
        </tr>
        <tr>
          <td><label for="status">{L('status')}</label></td>
          <td>
            <select id="status" name="item_status" {!tpl_disableif(!$user->perms('modify_all_tasks'))}>
              {!tpl_options($proj->listTaskStatuses(), Req::val('item_status', ($user->perms('modify_all_tasks') ? STATUS_NEW : STATUS_UNCONFIRMED)))}
            </select>
          </td>
        </tr>
        <?php if ($user->perms('modify_all_tasks')): ?>
        <tr>
          <td>
            <label>{L('assignedto')}</label>
          </td>
          <td>
            <?php if ($user->perms('modify_all_tasks')): ?>
            <?php $this->display('common.multiuserselect.tpl'); ?>
            <?php endif; ?>
          </td>
        </tr>
        <?php endif; ?>
        <tr>
          <td><label for="os">{L('operatingsystem')}</label></td>
          <td>
            <select id="os" name="operating_system">
              {!tpl_options($proj->listOs(), Req::val('operating_system'))}
            </select>
          </td>
        </tr>
        <tr>
          <td><label for="severity">{L('severity')}</label></td>
          <td>
            <select onchange="getElementById('edit_summary').className = 'summary severity' + this.value;
                              getElementById('itemsummary').className = 'text severity' + this.value;"
                              id="severity" class="adminlist" name="task_severity">
              {!tpl_options($fs->severities, Req::val('task_severity', 2))}
            </select>
          </td>
        </tr>
        <tr>
          <td><label for="priority">{L('priority')}</label></td>
          <td>
            <select id="priority" name="task_priority" {!tpl_disableif(!$user->perms('modify_all_tasks'))}>
              {!tpl_options($fs->priorities, Req::val('task_priority', 2))}
            </select>
          </td>
        </tr>
        <tr>
          <td><label for="reportedver">{L('reportedversion')}</label></td>
          <td>
            <select class="adminlist" name="product_version" id="reportedver">
              {!tpl_options($proj->listVersions(false, 2), Req::val('product_version'))}
            </select>
          </td>
        </tr>
        <tr>
          <td><label for="dueversion">{L('dueinversion')}</label></td>
          <td>
            <select id="dueversion" name="closedby_version" {!tpl_disableif(!$user->perms('modify_all_tasks'))}>
              <option value="0">{L('undecided')}</option>
              {!tpl_options($proj->listVersions(false, 3), Req::val('closedby_version'))}
            </select>
          </td>
        </tr>
        <?php if ($user->perms('modify_all_tasks')): ?>
        <tr>
          <td><label for="due_date">{L('duedate')}</label></td>
          <td id="duedate">
            {!tpl_datepicker('due_date', '', Req::val('due_date'))}
          </td>
        </tr>
        <?php endif; ?>
        <?php if ($user->perms('manage_project')): ?>
            <tr>
              <td><label for="private">{L('private')}</label></td>
              <td>
                {!tpl_checkbox('mark_private', Req::val('mark_private', 0), 'private')}
              </td>
            </tr>
        <?php endif; ?>
      </table>
    </div>

    </td><td style="width:100%">

    <div id="taskdetailsfull">
      <h3 class="taskdesc">{L('details')}</h3>
      <?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
      <div class="hide preview" id="preview"></div>
      <?php endif; ?>
      {!TextFormatter::textarea('detailed_desc', 15, 70, array('id' => 'details'), Req::val('detailed_desc', $proj->prefs['default_task']))}
      <?php if ($user->perms('create_attachments')): ?>
      <div id="uploadfilebox">
        <span style="display: none"><?php // this span is shown/copied in javascript when adding files ?>
          <input tabindex="5" class="file" type="file" size="55" name="userfile[]" />
            <a href="javascript://" tabindex="6" onclick="removeUploadField(this, 'uploadfilebox');">{L('remove')}</a><br />
        </span>
        <noscript>
          <span>
            <input tabindex="5" class="file" type="file" size="55" name="userfile[]" />
              <a href="javascript://" tabindex="6" onclick="removeUploadField(this, 'uploadfilebox');">{L('remove')}</a><br />
          </span>
        </noscript>
      </div>
      <button id="uploadfilebox_attachafile" tabindex="7" type="button" onclick="addUploadFields('uploadfilebox')">
        {L('uploadafile')} ({L('max')} {$fs->max_file_size} {L('MiB')})
      </button>
      <button id="uploadfilebox_attachanotherfile" tabindex="7" style="display: none" type="button" onclick="addUploadFields('uploadfilebox')">
         {L('attachanotherfile')} ({L('max')} {$fs->max_file_size} {L('MiB')})
      </button>
      <?php endif; ?>

    <p class="buttons">
        <?php if ($user->isAnon()): ?>
        <label class="inline" for="anon_email">{L('youremail')}</label><input type="text" class="text" id="anon_email" name="anon_email" size="30"  value="{Req::val('anon_email')}" /><br />
        <?php endif; ?>
        <?php if (!$user->perms('modify_all_tasks')): ?>
        <input type="hidden" name="item_status"   value="1" />
        <input type="hidden" name="task_priority" value="2" />
        <?php endif; ?>
        <input type="hidden" name="action" value="newtask.newtask" />
        <input type="hidden" name="project_id" value="{$proj->id}" />
        <button accesskey="s" type="submit">{L('addthistask')}</button>
        <?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
        <button tabindex="9" type="button" onclick="showPreview('details', '{#$baseurl}', 'preview')">{L('preview')}</button>
        <?php endif; ?>

        <?php if (!$user->isAnon()): ?>
        &nbsp;&nbsp;<input class="text" type="checkbox" id="notifyme" name="notifyme"
        value="1" checked="checked" />&nbsp;<label class="inline left" for="notifyme">{L('notifyme')}</label>
        <?php endif; ?>
    </p>
    </div>

    </td></tr></table>

  </form>

  <div class="clear"></div>
</div>
