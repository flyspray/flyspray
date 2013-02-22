<div id="comments" class="tab">
  <?php foreach($comments as $comment): ?>
  <div class="comment_container">
    <em>
      <a class="commentlink" name="comment{$comment['comment_id']}" id="comment{$comment['comment_id']}"
        href="{CreateURL('details', $task_details['task_id'])}#comment{$comment['comment_id']}">
        <!--<img src="{$this->get_image('comment')}"-->
        <!--  title="{L('commentlink')}" alt="" />-->
      </a>
      <!--{L('commentby')}--> {!tpl_userlink($comment['user_id'])} <br />
      {formatDate($comment['date_added'], true)}
      
      <br />
      
      <span class="DoNotPrint">
        <?php if ($user->perms('edit_comments') || ($user->perms('edit_own_comments') && $comment['user_id'] == $user->id)): ?>
        <!--&mdash;-->
        <a href="{$_SERVER['SCRIPT_NAME']}?do=editcomment&amp;task_id={$task_details['task_id']}&amp;id={$comment['comment_id']}">
          {L('edit')}</a>
        <?php endif; ?>
    
        <?php if ($user->perms('delete_comments')): ?>
         |
        <a href="{$_SERVER['SCRIPT_NAME']}?do=details&amp;action=details.deletecomment&amp;task_id={$task_details['task_id']}&amp;comment_id={$comment['comment_id']}"
          onclick="return confirm('{#L('confirmdeletecomment')}');">
          {L('delete')}</a>
        <?php endif ?>
      </span>

    </em>
    
    <div class="comment">
    <?php if(isset($comment_changes[$comment['date_added']])): ?>
    <ul class="comment_changes">
    <?php foreach($comment_changes[$comment['date_added']] as $change): ?>
      <li>{!event_description($change)}</li>
    <?php endforeach; ?>
    </ul>
    <?php endif; ?>
    <div class="commenttext">
      {!TextFormatter::render($comment['comment_text'], false, 'comm', $comment['comment_id'], $comment['content'])}</div>
      <?php if (isset($comment_attachments[$comment['comment_id']])) {
                $this->display('common.attachments.tpl', 'attachments', $comment_attachments[$comment['comment_id']]);
            }
      ?>
    </div>
    
    
    <div class="clear"></div>
  </div>

  <?php endforeach; ?>

  <?php if ($user->perms('add_comments') && (!$task_details['is_closed'] || $proj->prefs['comment_closed'])): ?>
  <h4>{L('addcomment')}</h4>
  <form enctype="multipart/form-data" action="{CreateUrl('details', $task_details['task_id'])}" method="post">
    <div>
      <?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
      <div class="hide preview" id="preview"></div>
      <?php endif; ?>
      <input type="hidden" name="action" value="details.addcomment" />
      <input type="hidden" name="task_id" value="{Req::val('task_id', $task_details['task_id'])}" />
      <?php if ($user->perms('create_attachments')): ?>
      <div id="uploadfilebox">
        <span style="display: none;"><?php // this span is shown/copied in javascript when adding files ?>
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
        {L('uploadafile')} ({L('max')} {$fs->max_file_size} {L('MiB')})
      </button>
      <button id="uploadfilebox_attachanotherfile" tabindex="7" style="display: none" type="button" onclick="addUploadFields()">
         {L('attachanotherfile')} ({L('max')} {$fs->max_file_size} {L('MiB')})
      </button>
      <?php endif; ?>
      {!TextFormatter::textarea('comment_text', 10, 72, array('accesskey' => 'r', 'tabindex' => 8, 'id' => 'comment_text'))}

      <button tabindex="9" type="submit">{L('addcomment')}</button>
      <?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
      <button tabindex="9" type="button" onclick="showPreview('comment_text', '{#$baseurl}', 'preview')">{L('preview')}</button>
      <?php endif; ?>
      <?php if (!$watched): ?>
      {!tpl_checkbox('notifyme', Req::val('notifyme', !(Req::val('action') == 'details.addcomment')), 'notifyme')} <label class="left" for="notifyme">{L('notifyme')}</label>
      <?php endif; ?>
    </div>
  </form>
  <?php endif; ?>
</div>
