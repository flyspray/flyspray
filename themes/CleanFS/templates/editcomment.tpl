<div class="box">
	<div class="comment_container">
		<div class="comment_avatar"><?php echo tpl_userlinkavatar($user->id, $fs->prefs['max_avatar_size'], 'av_comment'); ?></div>
		<div class="comment">
			<div class="comment_header">
				<div class="comment_header_actions">
					<?php
						$theuser = new User($comment['user_id']);
						if (!$theuser->isAnon()) {
							if ($theuser->perms('is_admin')) {
								$rank = 'Admin';
							}
							else if ($theuser->perms('manage_project')) {
								$rank = 'Project Manager';
							}
							else {
								$rank = '';
							}

							if (!empty($rank)) {
								echo '<span class="comment_header_usertype">'.Filters::noXSS($rank).'</span>';
							}
						}
					?>
				</div>
				<div class="comment_header_infos"><?php echo tpl_userlink($comment['user_id']); ?> <?php echo Filters::noXSS(L('commentedon')); ?> <?php echo Filters::noXSS(formatDate($comment['date_added'], true)); ?></div>
			</div>
			<div class="commenttext">
				<?php echo tpl_form(CreateUrl('details', $comment['task_id'], 'multipart/form-data')); ?>
				<input type="hidden" name="action" value="editcomment" />
				<input type="hidden" name="task_id" value="<?php echo Filters::noXSS($comment['task_id']); ?>" />
				<input type="hidden" name="comment_id" value="<?php echo Filters::noXSS($comment['comment_id']); ?>" />
				<input type="hidden" name="previous_text" value="<?php echo Filters::noXSS($comment['comment_text']); ?>" />
				<?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
					<div class="hide preview" id="preview"></div>
					<button tabindex="9" type="button" onclick="showPreview('comment_text', '<?php echo Filters::noJsXSS($baseurl); ?>', 'preview')"><?php echo Filters::noXSS(L('preview')); ?></button>
				<?php endif; ?>
				<?php echo TextFormatter::textarea('comment_text', 10, 72, array('id' => 'comment_text'), $comment['comment_text']); ?>
				<div id="addlinkbox">
					<?php $links = $proj->listLinks($comment['comment_id'], $comment['task_id']);
					$this->display('common.editlinks.tpl', 'links', $links); ?>
					<?php if ($user->perms('create_attachments')): ?>
						<button id="addlinkbox_addalink" tabindex="10" type="button" onclick="addLinkField('addlinkbox')">
						<?php echo Filters::noXSS(L('addalink')); ?>
						</button>
						<button id="addlinkbox_addanotherlink" tabindex="10" style="display: none" type="button" onclick="addLinkField('addlinkbox')">
						<?php echo Filters::noXSS(L('addanotherlink')); ?>
						</button>
						<span style="display: none">
						<input tabindex="8" class="text" type="text" size="28" maxlength="100" name="userlink[]" />
						<a href="javascript://" tabindex="9" onclick="removeLinkField(this, 'addlinkbox');"><?php echo Filters::noXSS(L('remove')); ?></a><br />
						</span>
						<noscript>
						<input tabindex="8" class="text" type="text" size="28" maxlength="100" name="userlink[]" />
						</noscript>
					<?php endif; ?>
				</div>
				<div id="uploadfilebox">
					<?php $attachments = $proj->listAttachments($comment['comment_id'], $comment['task_id']);
					$this->display('common.editattachments.tpl', 'attachments', $attachments); ?>
					<?php if ($user->perms('create_attachments')): ?>
						<button id="uploadfilebox_attachafile" tabindex="7" type="button" onclick="addUploadFields()">
							<?php echo Filters::noXSS(L('uploadafile')); ?> (<?php echo Filters::noXSS(L('max')); ?> <?php echo Filters::noXSS($fs->max_file_size); ?> <?php echo Filters::noXSS(L('MiB')); ?>)
						</button>
						<button id="uploadfilebox_attachanotherfile" tabindex="7" style="display: none" type="button" onclick="addUploadFields()">
							<?php echo Filters::noXSS(L('attachanotherfile')); ?> (<?php echo Filters::noXSS(L('max')); ?> <?php echo Filters::noXSS($fs->max_file_size); ?> <?php echo Filters::noXSS(L('MiB')); ?>)
						</button>
						<span style="display: none;"><!-- this span is shown/copied in javascript when adding files -->
							<input tabindex="5" class="file" type="file" size="55" name="userfile[]" />
							<a href="javascript://" tabindex="6" onclick="removeUploadField(this);"><?php echo Filters::noXSS(L('remove')); ?></a><br />
						</span>
						<noscript>
							<input tabindex="5" class="file" type="file" size="55" name="userfile[]" />
						</noscript>
					<?php endif; ?>
					</div>
				<button accesskey="s" tabindex="9" type="submit" class="positive"><?php echo Filters::noXSS(L('saveeditedcomment')); ?></button>
				<a class="button" href="<?php echo Filters::noXSS(CreateUrl('details', $comment['task_id'])); ?>"><?php echo Filters::noXSS(L('canceledit')); ?></a>
				</form>
			</div>
		</div>
	</div>
</div>
