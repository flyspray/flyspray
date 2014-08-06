<!--<h3><?php echo Filters::noXSS($proj->prefs['project_title']); ?> :: <?php echo Filters::noXSS(L('newtask')); ?></h3>-->

<form enctype="multipart/form-data" action="<?php echo Filters::noXSS(CreateUrl('newtask', $proj->id)); ?>" method="post">
  <div id="actionbar">
    <button class="button positive main" accesskey="s" type="submit"><?php echo Filters::noXSS(L('addthistask')); ?></button>
    <div class="clear"></div>
  </div>
  <div id="taskdetails">
      <div id="taskfields">
        <ul class="form_elements slim">
          <li>
            <label for="tasktype"><?php echo Filters::noXSS(L('tasktype')); ?></label>
            <select name="task_type" id="tasktype">
              <?php echo tpl_options($proj->listTaskTypes(), Req::val('task_type')); ?>

            </select>
          </li>
  
          <li>
            <label for="category"><?php echo Filters::noXSS(L('category')); ?></label>
            <select class="adminlist" name="product_category" id="category">
              <?php echo tpl_options($proj->listCategories(), Req::val('product_category')); ?>

            </select>
          </li>
  
          <li>
            <label for="status"><?php echo Filters::noXSS(L('status')); ?></label>
            <select id="status" name="item_status" <?php echo tpl_disableif(!$user->perms('modify_all_tasks')); ?>>
              <?php echo tpl_options($proj->listTaskStatuses(), Req::val('item_status', ($user->perms('modify_all_tasks') ? STATUS_NEW : STATUS_UNCONFIRMED))); ?>

            </select>
          </li>
  
          <?php if ($user->perms('modify_all_tasks')): ?>
          <li>
            <label><?php echo Filters::noXSS(L('assignedto')); ?></label>
            <?php if ($user->perms('modify_all_tasks')): ?>
            <?php $this->display('common.multiuserselect.tpl'); ?>
            <?php endif; ?>
          </li>
          <?php endif; ?>
  
          <li>
            <label for="os"><?php echo Filters::noXSS(L('operatingsystem')); ?></label>
            <select id="os" name="operating_system">
              <?php echo tpl_options($proj->listOs(), Req::val('operating_system')); ?>

            </select>
          </li>
  
          <li>
            <label for="severity"><?php echo Filters::noXSS(L('severity')); ?></label>
            <select onchange="getElementById('edit_summary').className = 'summary severity' + this.value;
                              getElementById('itemsummary').className = 'text severity' + this.value;"
                              id="severity" class="adminlist" name="task_severity">
              <?php echo tpl_options($fs->severities, Req::val('task_severity', 2)); ?>

            </select>
          </li>
  
          <li>
            <label for="priority"><?php echo Filters::noXSS(L('priority')); ?></label>
            <select id="priority" name="task_priority" <?php echo tpl_disableif(!$user->perms('modify_all_tasks')); ?>>
              <?php echo tpl_options($fs->priorities, Req::val('task_priority', 2)); ?>

            </select>
          </li>
  
          <li>
            <label for="reportedver"><?php echo Filters::noXSS(L('reportedversion')); ?></label>
            <select class="adminlist" name="product_version" id="reportedver">
              <?php echo tpl_options($proj->listVersions(false, 2), Req::val('product_version')); ?>

            </select>
          </li>
  
          <li>
            <label for="dueversion"><?php echo Filters::noXSS(L('dueinversion')); ?></label>
            <select id="dueversion" name="closedby_version" <?php echo tpl_disableif(!$user->perms('modify_all_tasks')); ?>>
              <option value="0"><?php echo Filters::noXSS(L('undecided')); ?></option>
              <?php echo tpl_options($proj->listVersions(false, 3), Req::val('closedby_version')); ?>

            </select>
          </li>
  
          <?php if ($user->perms('modify_all_tasks')): ?>
          <li>
            <label for="due_date"><?php echo Filters::noXSS(L('duedate')); ?></label>
            <?php echo tpl_datepicker('due_date', '', Req::val('due_date')); ?>

          </li>
          <?php endif; ?>
  
          <?php if ($user->perms('manage_project')): ?>
          <li>
            <label for="private"><?php echo Filters::noXSS(L('private')); ?></label>
            <?php echo tpl_checkbox('mark_private', Req::val('mark_private', 0), 'private'); ?>

          </li>
          <?php endif; ?>
        </ul>
      </div>
  
      <div id="taskdetailsfull">
        <!--<h3 class="taskdesc"><?php echo Filters::noXSS(L('details')); ?></h3>-->
        <h2 class="severity<?php echo Filters::noXSS(Req::val('task_severity', 2)); ?> summary" id="edit_summary">
          <label for="itemsummary"><?php echo Filters::noXSS(L('summary')); ?></label>
          <input id="itemsummary" class="text severity<?php echo Filters::noXSS(Req::val('task_severity', 2)); ?>" type="text" value="<?php echo Filters::noXSS(Req::val('item_summary')); ?>"
            name="item_summary" size="80" maxlength="100" />
        </h2>
  
        <?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
        <div class="hide preview" id="preview"></div>
        <?php endif; ?>
        <?php echo TextFormatter::textarea('detailed_desc', 15, 70, array('id' => 'details'), Req::val('detailed_desc', $proj->prefs['default_task'])); ?>

  
      <p class="buttons">
          <?php if ($user->isAnon()): ?>
          <label class="inline" for="anon_email"><?php echo Filters::noXSS(L('youremail')); ?></label><input type="text" class="text" id="anon_email" name="anon_email" size="30"  value="<?php echo Filters::noXSS(Req::val('anon_email')); ?>" /><br />
          <?php endif; ?>
          <?php if (!$user->perms('modify_all_tasks')): ?>
          <input type="hidden" name="item_status"   value="1" />
          <input type="hidden" name="task_priority" value="2" />
          <?php endif; ?>
          <input type="hidden" name="action" value="newtask.newtask" />
          <input type="hidden" name="project_id" value="<?php echo Filters::noXSS($proj->id); ?>" />
          <!--<button accesskey="s" type="submit"><?php echo Filters::noXSS(L('addthistask')); ?></button>-->
          <?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
          <button tabindex="9" type="button" onclick="showPreview('details', '<?php echo Filters::noJsXSS($baseurl); ?>', 'preview')"><?php echo Filters::noXSS(L('preview')); ?></button>
          <?php endif; ?>
  
          <?php if (!$user->isAnon()): ?>
          &nbsp;&nbsp;<input class="text" type="checkbox" id="notifyme" name="notifyme"
          value="1" checked="checked" />&nbsp;<label class="inline left" for="notifyme"><?php echo Filters::noXSS(L('notifyme')); ?></label>
          <?php endif; ?>
      </p>
  
        <?php if ($user->perms('create_attachments')): ?>
        <div id="uploadfilebox">
          <span style="display: none"><?php // this span is shown/copied in javascript when adding files ?>
            <input tabindex="5" class="file" type="file" size="55" name="userfile[]" />
              <a href="javascript://" tabindex="6" onclick="removeUploadField(this, 'uploadfilebox');"><?php echo Filters::noXSS(L('remove')); ?></a><br />
          </span>
          <noscript>
            <span>
              <input tabindex="5" class="file" type="file" size="55" name="userfile[]" />
                <a href="javascript://" tabindex="6" onclick="removeUploadField(this, 'uploadfilebox');"><?php echo Filters::noXSS(L('remove')); ?></a><br />
            </span>
          </noscript>
        </div>
        <button id="uploadfilebox_attachafile" tabindex="7" type="button" onclick="addUploadFields('uploadfilebox')">
          <?php echo Filters::noXSS(L('uploadafile')); ?> (<?php echo Filters::noXSS(L('max')); ?> <?php echo Filters::noXSS($fs->max_file_size); ?> <?php echo Filters::noXSS(L('MiB')); ?>)
        </button>
        <button id="uploadfilebox_attachanotherfile" tabindex="7" style="display: none" type="button" onclick="addUploadFields('uploadfilebox')">
           <?php echo Filters::noXSS(L('attachanotherfile')); ?> (<?php echo Filters::noXSS(L('max')); ?> <?php echo Filters::noXSS($fs->max_file_size); ?> <?php echo Filters::noXSS(L('MiB')); ?>)
        </button>

<div id="addlinkbox">
    <span style="display: none">
	 <input tabindex="8" class="text" type="text" size="28" maxlength="100" name="userlink[]" />
	 <a href="javascript://" tabindex="9" onclick="removeLinkField(this, 'addlinkbox');"><?php echo Filters::noXSS(L('remove')); ?></a><br />
    </span>
    <noscript>
	 <span>
	       <input tabindex="8" class="text" type="text" size="28" maxlength="100" name="userlink[]" />
	       <a href="javascript://" tabindex="9" onclick="removeLinkField(this, 'addlinkbox');"><?php echo Filters::noXSS(L('remove')); ?></a><br />
	 </span>
    </noscript>
</div>
<button id="addlinkbox_addalink" tabindex="10" type="button" onclick="addLinkField('addlinkbox')">
	<?php echo Filters::noXSS(L('addalink')); ?>
</button>
<button id="addlinkbox_addanotherlink" tabindex="10" style="display: none" type="button" onclick="addLinkField('addlinkbox')">
	<?php echo Filters::noXSS(L('addanotherlink')); ?>
</button>
        <?php endif; ?>
  
      </div>
  
    <div class="clear"></div>
  </div>
</form>
