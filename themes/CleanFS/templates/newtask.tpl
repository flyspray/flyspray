<!--<h3><?php echo Filters::noXSS($proj->prefs['project_title']); ?> :: <?= eL('newtask') ?></h3>-->
<?php
    if (!isset($supertask_id)) {
        $supertask_id = 0;
    }
?>
  <script type="text/javascript">
	function checkContent()
	{
		var instance;
		for(instance in CKEDITOR.instances){
			CKEDITOR.instances[instance].updateElement();
		}
		var summary = document.getElementById("itemsummary").value;
		if(summary.trim().length == 0){
			return true;
		}
		var detail = document.getElementById("details").value;
    		var project_id = document.getElementsByName('project_id')[0].value;

		var xmlHttp = new XMLHttpRequest();
		xmlHttp.open("POST", "<?php echo Filters::noXSS($baseurl); ?>js/callbacks/searchtask.php", false);
		xmlHttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		xmlHttp.send("summary=" + summary + "&detail=" + detail +"&project_id=" + project_id);
		if(xmlHttp.status === 200) {
			if(xmlHttp.responseText > 0) {
				var res = confirm("There is already a similar task, do you still want to create?");
				return res;
			}
			return true;
		}
		return false;
	}
  </script>
<?php echo tpl_form(Filters::noXSS(createUrl('newtask', $proj->id, $supertask_id)), 'newtask', 'post', 'multipart/form-data', 'onsubmit="return checkContent()"'); ?>
  <input type="hidden" name="supertask_id" value="<?php echo Filters::noXSS($supertask_id); ?>" />
  <div id="actionbar"><div class="clear"></div></div>
  <?php 
  # Grab fields wanted for this project so we can only show those we want
  $fields = explode( ' ', $proj->prefs['visible_fields'] );
  ?>
  <div id="taskdetails">
      <div id="taskfields">
        <ul class="form_elements slim">
          <!-- Task Type -->
          <?php if (in_array('tasktype', $fields)) { ?>
            <li>
          <?php } else { ?>
            <li style="display:none">
          <?php } ?>
            <label for="tasktype"><?= eL('tasktype') ?></label>
            <select name="task_type" id="tasktype">
              <?php echo tpl_options($proj->listTaskTypes(), Req::val('task_type')); ?>
            </select>
          </li>

          <!-- Category -->
          <?php if (in_array('category', $fields)) { ?>
            <li>
          <?php } else { ?>
            <li style="display:none">
          <?php } ?>
            <label for="category"><?= eL('category') ?></label>
            <select class="adminlist" name="product_category" id="category">
              <?php echo tpl_options($proj->listCategories(), Req::val('product_category')); ?>
            </select>
          </li>

          <!-- Status -->
          <?php if (in_array('status', $fields)) { ?>
            <li>
          <?php } else { ?>
            <li style="display:none">
          <?php } ?>
            <label for="status"><?= eL('status') ?></label>
            <select id="status" name="item_status" <?php echo tpl_disableif(!$user->perms('modify_all_tasks')); ?>>
              <?php echo tpl_options($proj->listTaskStatuses(), Req::val('item_status', ($user->perms('modify_all_tasks') ? STATUS_NEW : STATUS_UNCONFIRMED))); ?>
            </select>
          </li>

          <?php if ($user->perms('modify_all_tasks')): ?>
          <!-- Assigned To -->
          <?php if (in_array('assignedto', $fields)) { ?>
            <li>
          <?php } else { ?>
            <li style="display:none">
          <?php } ?>
            <label><?= eL('assignedto') ?></label>
            <?php if ($user->perms('modify_all_tasks')): ?>
            <?php $this->display('common.multiuserselect.tpl'); ?>
            <?php endif; ?>
          </li>
          <?php endif; ?>

          <!-- os -->
          <?php if (in_array('os', $fields)) { ?>
            <li>
          <?php } else { ?>
            <li style="display:none">
          <?php } ?>
            <label for="os"><?= eL('operatingsystem') ?></label>
            <select id="os" name="operating_system">
              <?php echo tpl_options($proj->listOs(), Req::val('operating_system')); ?>
            </select>
          </li>

          <!-- Severity -->
          <?php if (in_array('severity', $fields)) { ?>
            <li>
          <?php } else { ?>
            <li style="display:none">
          <?php } ?>
            <label for="severity"><?= eL('severity') ?></label>
            <select onchange="getElementById('edit_summary').className = 'summary severity' + this.value;
                              getElementById('itemsummary').className = 'text severity' + this.value;"
                              id="severity" class="adminlist" name="task_severity">
              <?php echo tpl_options($fs->severities, Req::val('task_severity', 2)); ?>
            </select>
          </li>

          <!-- Priority-->
          <?php if (in_array('priority', $fields)) { ?>
            <li>
          <?php } else { ?>
            <li style="display:none">
          <?php } ?>
            <label for="priority"><?= eL('priority') ?></label>
            <select id="priority" name="task_priority" <?php echo tpl_disableif(!$user->perms('modify_all_tasks')); ?>>
              <?php echo tpl_options($fs->priorities, Req::val('task_priority', 4)); ?>
            </select>
          </li>

          <!-- Reported Version-->
          <?php if (in_array('reportedin', $fields)) { ?>
            <li>
          <?php } else { ?>
            <li style="display:none">
          <?php } ?>
            <label for="reportedver"><?= eL('reportedversion') ?></label>
            <select class="adminlist" name="product_version" id="reportedver">
              <?php echo tpl_options($proj->listVersions(false, 2), Req::val('product_version')); ?>
            </select>
          </li>

          <!-- Due Version -->
          <?php if (in_array('dueversion', $fields)) { ?>
            <li>
          <?php } else { ?>
            <li style="display:none">
          <?php } ?>
            <label for="dueversion"><?= eL('dueinversion') ?></label>
            <select id="dueversion" name="closedby_version" <?php echo tpl_disableif(!$user->perms('modify_all_tasks')); ?>>
              <option value="0"><?= eL('undecided') ?></option>
              <?php echo tpl_options($proj->listVersions(false, 3),$proj->prefs['default_due_version'], false); ?>
            </select>
          </li>

          <?php if ($user->perms('modify_all_tasks')): ?>
          <!-- Due Date -->
          <?php if (in_array('duedate', $fields)) { ?>
            <li>
          <?php } else { ?>
            <li style="display:none">
          <?php } ?>
            <label for="due_date"><?= eL('duedate') ?></label>
            <?php echo tpl_datepicker('due_date', '', Req::val('due_date')); ?>
          </li>
          <?php endif; ?>

        <?php if($proj->prefs['use_effort_tracking']) {
        	if ($user->perms('view_effort')) {
        ?>
        	<li>
                <label for="estimatedeffort"><?= eL('estimatedeffort') ?></label>
                <input id="estimated_effort" name="estimated_effort" class="text" type="text" size="5" maxlength="100" value="0" />
                <?= eL('hours') ?>
        	</li>
        	<?php }
        } ?>

          <?php if ($user->perms('manage_project')): ?>
          <!-- Private -->
          <?php if (in_array('private', $fields)) { ?>
            <li>
          <?php } else { ?>
            <li style="display:none">
          <?php } ?>
            <label for="private"><?= eL('private') ?></label>
            <?php echo tpl_checkbox('mark_private', Req::val('mark_private', 0), 'private'); ?>
          </li>
          <?php endif; ?>
        </ul>
      </div>

      <div id="taskdetailsfull">
        <!--<h3 class="taskdesc"><?= eL('details') ?></h3>-->
        <label class="severity<?php echo Filters::noXSS(Req::val('task_severity', 2)); ?> summary" id="edit_summary" for="itemsummary"><?php echo Filters::noXSS(L('summary')); ?></label>
        <input id="itemsummary" required="required" placeholder="<?= eL('summary') ?>" title="<?= eL('tooltipshorttasktitle') ?>" type="text" value="<?php echo Filters::noXSS(Req::val('item_summary')); ?>"
            name="item_summary" maxlength="100" />
	<?php if ($proj->prefs['use_tags']): ?>
		<input type="checkbox" id="availtags">
		<div id="edit_tags">
			<label for="tags" title="<?= eL('tagsinfo') ?>"><?= eL('tags') ?>:</label>
			<input title="<?= eL('tagsinfo') ?>" placeholder="<?= eL('tags') ?>" type="text" name="tags" id="tags" maxlength="200" value="<?php echo Filters::noXSS(Req::val('tags','')); ?>" />
			<label for="availtags" class="button" id="availtagsshow"><i class="fa fa-plus"></i></label>
			<label for="availtags" class="button" id="availtagshide"><i class="fa fa-minus"></i></label>
		</div>
		<div id="tagrender"></div>
		<fieldset id="availtaglist">
                <legend><?= eL('tagsavail') ?></legend>
                <?php
                foreach ($taglist as $tagavail) {
                        echo tpl_tag($tagavail['tag_id']); 
                } ?>
                </fieldset>
	<?php endif; ?>
        <?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
        <div class="hide preview" id="preview"></div>
        <button tabindex="9" type="button" onclick="showPreview('details', '<?php echo Filters::noJsXSS($baseurl); ?>', 'preview')"><?php echo Filters::noXSS(L('preview')); ?></button>
        <?php endif; ?>
        <?php echo TextFormatter::textarea('detailed_desc', 15, 70, array('id' => 'details'), Req::val('detailed_desc', $proj->prefs['default_task'])); ?>

      <p class="buttons">
          <?php if ($user->isAnon()): ?>
          <label class="inline" for="anon_email"><?= eL('youremail') ?></label><input type="text" class="text" id="anon_email" name="anon_email" size="30" required="required" value="<?php echo Filters::noXSS(Req::val('anon_email')); ?>" /><br />
          <?php endif; ?>
          <?php if (!$user->perms('modify_all_tasks')): ?>
          <input type="hidden" name="item_status"   value="1" />
          <input type="hidden" name="task_priority" value="2" />
          <?php endif; ?>
          <input type="hidden" name="action" value="newtask.newtask" />
          <input type="hidden" name="project_id" value="<?php echo Filters::noXSS($proj->id); ?>" />
          <?php if (!$user->isAnon()): ?>
          &nbsp;&nbsp;<input class="text" type="checkbox" id="notifyme" name="notifyme"
          value="1" checked="checked" />&nbsp;<label class="inline left" for="notifyme"><?= eL('notifyme') ?></label>
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
          <?= eL('uploadafile') ?> (<?= eL('max') ?> <?php echo Filters::noXSS($fs->max_file_size); ?> <?= eL('MiB') ?>)
        </button>
        <button id="uploadfilebox_attachanotherfile" tabindex="7" style="display: none" type="button" onclick="addUploadFields('uploadfilebox')">
           <?= eL('attachanotherfile') ?> (<?= eL('max') ?> <?php echo Filters::noXSS($fs->max_file_size); ?> <?= eL('MiB') ?>)
        </button>

        <div id="addlinkbox">
    <span style="display: none">
	 <input tabindex="8" class="text" type="text" size="28" maxlength="150" name="userlink[]" />
	 <a href="javascript://" tabindex="9" onclick="removeLinkField(this, 'addlinkbox');"><?= eL('remove') ?></a><br />
    </span>
    <noscript>
	 <span>
	       <input tabindex="8" class="text" type="text" size="28" maxlength="150" name="userlink[]" />
	       <a href="javascript://" tabindex="9" onclick="removeLinkField(this, 'addlinkbox');"><?= eL('remove') ?></a><br />
	 </span>
    </noscript>
</div>
<button id="addlinkbox_addalink" tabindex="10" type="button" onclick="addLinkField('addlinkbox')"><?= eL('addalink') ?></button>
<button id="addlinkbox_addanotherlink" tabindex="10" style="display:none" type="button" onclick="addLinkField('addlinkbox')"><?= eL('addanotherlink') ?></button>
        <?php endif; ?>

<button class="button positive" style="display:block;margin-top:20px" accesskey="s" type="submit"><?= eL('addthistask') ?></button>
      </div>

    <div class="clear"></div>
  </div>
</form>
