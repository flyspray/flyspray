<h3><?php echo Filters::noXSS(L('editcomment')); ?></h3>
<div class="box">
<form action="<?php echo Filters::noXSS(CreateUrl('details', $comment['task_id'])); ?>" enctype="multipart/form-data" method="post">
    <div>
    <p><?php echo Filters::noXSS(L('commentby')); ?> <?php echo Filters::noXSS($comment['real_name']); ?> - <?php echo Filters::noXSS(formatDate($comment['date_added'], true)); ?></p>
    <?php 
    $attachments = $proj->listAttachments($comment['comment_id']);
    $this->display('common.editattachments.tpl', 'attachments', $attachments); 
    
    if ($user->perms('create_attachments')): ?>
      <div id="uploadfilebox">
        <span style="display: none"><?php // this span is shown/copied in javascript when adding files ?>
          <input tabindex="5" class="file" type="file" size="55" name="userfile[]" />
            <a href="javascript://" tabindex="6" onclick="removeUploadField(this);"><?php echo Filters::noXSS(L('remove')); ?></a><br />
        </span>
        <noscript>
          <span>
            <input tabindex="5" class="file" type="file" size="55" name="userfile[]" />
              <a href="javascript://" tabindex="6" onclick="removeUploadField(this);"><?php echo Filters::noXSS(L('remove')); ?></a><br />
          </span>   
        </noscript> 
      </div>
      <button id="uploadfilebox_attachafile" tabindex="7" type="button" onclick="addUploadFields()">
        <?php echo Filters::noXSS(L('uploadafile')); ?>

      </button>
      <button id="uploadfilebox_attachanotherfile" tabindex="7" style="display: none" type="button" onclick="addUploadFields()">
         <?php echo Filters::noXSS(L('attachanotherfile')); ?> (<?php echo Filters::noXSS(L('max')); ?> <?php echo Filters::noXSS($fs->max_file_size); ?> <?php echo Filters::noXSS(L('MiB')); ?>)
      </button>
    <?php endif; ?>

    <?php
    $links = $proj->listLinks($comment['comment_id']);
    $this->display('common.editlinks.tpl', 'links', $links);

    if ($user->perms('create_attachments')): ?>
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
    
    <?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
    <div class="hide preview" id="preview"></div>
    <?php endif; ?>
    <?php echo TextFormatter::textarea('comment_text', 10, 72, array('id' => 'comment_text'), $comment['comment_text']); ?>


    <input type="hidden" name="action" value="editcomment" />
    <input type="hidden" name="task_id" value="<?php echo Filters::noXSS($comment['task_id']); ?>" />
    <input type="hidden" name="comment_id" value="<?php echo Filters::noXSS($comment['comment_id']); ?>" />
    <input type="hidden" name="previous_text" value="<?php echo Filters::noXSS($comment['comment_text']); ?>" />
    <button type="submit"><?php echo Filters::noXSS(L('saveeditedcomment')); ?></button>
    <?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
    <button tabindex="9" type="button" onclick="showPreview('comment_text', '<?php echo Filters::noJsXSS($baseurl); ?>', 'preview')"><?php echo Filters::noXSS(L('preview')); ?></button>
    <?php endif; ?>
    </div>
</form>
</div>
