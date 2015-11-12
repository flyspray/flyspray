<div id="comments" class="tab">
	<?php foreach($comments as $comment): ?>
		<div class="comment_container" id="<?php echo 'comment' . $comment['comment_id']; ?>">
			<div class="comment_avatar"><?php echo tpl_userlinkavatar($comment['user_id'], $fs->prefs['max_avatar_size'], 'av_comment'); ?></div>
			<div class="comment">
				<div class="comment_header">
					<div class="comment_header_actions">
						<?php echo tpl_form(CreateUrl('details', $task_details['task_id'])); ?>
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
						<?php if ($user->perms('edit_comments') || ($user->perms('edit_own_comments') && $comment['user_id'] == $user->id)): ?>
							<a href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>?do=editcomment&amp;task_id=<?php echo Filters::noXSS($task_details['task_id']); ?>&amp;id=<?php echo Filters::noXSS($comment['comment_id']); ?>" title="<?php echo Filters::noXSS(L('edit')); ?>"><span class="fa fa-pencil fa-lg" style="margin-right: 10px;"></span></a>
						<?php endif; ?>
						<?php if ($user->perms('delete_comments')): ?>
							<input type="hidden" name="action" value="details.deletecomment"/>
							<input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>"/>
							<?php $confirm = (isset($comment_attachments[$comment['comment_id']])) ? sprintf(L('confirmdeletecomment'), L('attachementswilldeleted')) : sprintf(L('confirmdeletecomment'), '')  ?>
							<button type="submit" class="fakelinkbutton" onclick="return confirm('<?php echo Filters::noJsXSS($confirm); ?>');" title="<?php echo Filters::noXSS(L('delete')); ?>"><span class="fa fa-remove fa-lg"></span></button>
						<?php endif; ?>
						</form>
					</div>
					<div class="comment_header_infos"><?php echo tpl_userlink($comment['user_id']); ?> <?php echo Filters::noXSS(L('commentedon')); ?> <?php echo Filters::noXSS(formatDate($comment['date_added'], true)); ?></div>
				</div>
				<div class="commenttext">
					<?php echo TextFormatter::render($comment['comment_text'], 'comm', $comment['comment_id'], $comment['content']); ?>
					<?php if (isset($comment_links[$comment['comment_id']])) {
						$this->display('common.links.tpl', 'links', $comment_links[$comment['comment_id']]);
					}?>
					<?php if (isset($comment_attachments[$comment['comment_id']])) {
						$this->display('common.attachments.tpl', 'attachments', $comment_attachments[$comment['comment_id']]);
					}?>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
	<?php if ($user->perms('add_comments') && (!$task_details['is_closed'] || $proj->prefs['comment_closed'])): ?>
		<div class="comment_container">
			<div class="comment_avatar"><?php echo tpl_userlinkavatar($user->id, $fs->prefs['max_avatar_size'], 'av_comment'); ?></div>
			<div class="comment">
				<div class="comment_header">
					<?php echo Filters::noXSS(L('addcomment')); ?>
				</div>
				<div class="commenttext">
					<?php echo tpl_form(CreateUrl('details', $task_details['task_id']), 'comment', 'post', 'multipart/form-data'); ?>
					<?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
						<div class="hide preview" id="preview"></div>
					<?php endif; ?>
					<input type="hidden" name="action" value="details.addcomment" />
					<input type="hidden" name="task_id" value="<?php echo Filters::noXSS(Req::val('task_id', $task_details['task_id'])); ?>" />
					<?php if (!$watched): ?>
						<?php echo tpl_checkbox('notifyme', Req::val('notifyme', !(Req::val('action') == 'details.addcomment')), 'notifyme'); ?> <label class="left" for="notifyme"><?php echo Filters::noXSS(L('notifyme')); ?></label>
					<?php endif; ?>
					<?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
						<button tabindex="9" type="button" onclick="showPreview('comment_text', '<?php echo Filters::noJsXSS($baseurl); ?>', 'preview')"><?php echo Filters::noXSS(L('preview')); ?></button>
					<?php endif; ?>
					<?php echo TextFormatter::textarea('comment_text', 10, 72, array('accesskey' => 'r', 'tabindex' => 8, 'id' => 'comment_text')); ?>
					<div id="addlinkbox">
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
							<span>
								<input tabindex="8" class="text" type="text" size="28" maxlength="100" name="userlink[]" />
								<a href="javascript://" tabindex="9" onclick="removeLinkField(this, 'addlinkbox');"><?php echo Filters::noXSS(L('remove')); ?></a><br />
							</span>
						</noscript>
					</div>
					<?php if ($user->perms('create_attachments')): ?>
						<div id="uploadfilebox">
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
								<span>
									<input tabindex="5" class="file" type="file" size="55" name="userfile[]" />
									<a href="javascript://" tabindex="6" onclick="removeUploadField(this);"><?php echo Filters::noXSS(L('remove')); ?></a><br />
								</span>
							</noscript>
						</div>
					<?php endif; ?>
					<button class="button positive" accesskey="s" tabindex="9" type="submit"><?php echo Filters::noXSS(L('addcomment')); ?></button>
					</form>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>
