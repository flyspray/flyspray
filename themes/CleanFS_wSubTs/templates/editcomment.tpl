<h3>{L('editcomment')}</h3>
<div class="box">
<form action="{CreateUrl('details', $comment['task_id'])}" enctype="multipart/form-data" method="post">
    <div>
    <p>{L('commentby')} {$comment['real_name']} - {formatDate($comment['date_added'], true)}</p>
    <?php 
    $attachments = $proj->listAttachments($comment['comment_id']);
    $this->display('common.editattachments.tpl', 'attachments', $attachments); 
    
    if ($user->perms('create_attachments')): ?>
      <div id="uploadfilebox">
        <span style="display: none"><?php // this span is shown/copied in javascript when adding files ?>
          <input tabindex="5" class="file" type="file" size="55" name="userfile[]" />
            <a href="javascript://" tabindex="6" onclick="removeUploadField(this);">{L('remove')}</a><br />
        </span>
        <noscript>
          <span>
            <input tabindex="5" class="file" type="file" size="55" name="userfile[]" />
              <a href="javascript://" tabindex="6" onclick="removeUploadField(this);">{L('remove')}</a><br />
          </span>   
        </noscript> 
      </div>
      <button id="uploadfilebox_attachafile" tabindex="7" type="button" onclick="addUploadFields()">
        {L('uploadafile')}
      </button>
      <button id="uploadfilebox_attachanotherfile" tabindex="7" style="display: none" type="button" onclick="addUploadFields()">
         {L('attachanotherfile')} ({L('max')} {$fs->max_file_size} {L('MiB')})
      </button>
    <?php endif; ?>
    
    <?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
    <div class="hide preview" id="preview"></div>
    <?php endif; ?>
    {!TextFormatter::textarea('comment_text', 10, 72, array('id' => 'comment_text'), $comment['comment_text'])}

    <input type="hidden" name="action" value="editcomment" />
    <input type="hidden" name="task_id" value="{$comment['task_id']}" />
    <input type="hidden" name="comment_id" value="{$comment['comment_id']}" />
    <input type="hidden" name="previous_text" value="{$comment['comment_text']}" />
    <button type="submit">{L('saveeditedcomment')}</button>
    <?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
    <button tabindex="9" type="button" onclick="showPreview('comment_text', '{#$baseurl}', 'preview')">{L('preview')}</button>
    <?php endif; ?>
    </div>
</form>
</div>