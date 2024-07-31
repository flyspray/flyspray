<div id="toolbox" class="toolbox_<?php echo $area; ?>">
	<h2><?php echo Filters::noXSS(L('createnewproject')); ?></h2>
	<?php echo tpl_form(CreateURL('admin', 'newproject')); ?>

	<ul class="form_elements">
		<li class="required">
			<label for="projecttitle"><?php echo Filters::noXSS(L('projecttitle')); ?></label>
			<div class="valuewrap">
				<input id="projecttitle" name="project_title" value="<?php echo Filters::noXSS(Req::val('project_title')); ?>" type="text" class="required text fi-x-large" size="40" maxlength="100" />
			</div>
		</li>
		<li>
			<label for="themestyle"><?php echo Filters::noXSS(L('themestyle')); ?></label>
			<div class="valuewrap">
				<select id="themestyle" name="theme_style">
				<?php echo tpl_options(Flyspray::listThemes(), Req::val('theme_style', $proj->prefs['theme_style']), true); ?>
				</select>
			</div>
		</li>
		<li>
			<label for="langcode"><?php echo Filters::noXSS(L('language')); ?></label>
			<div class="valuewrap">
				<select id="langcode" name="lang_code">
				<?php echo tpl_options(Flyspray::listLangs(), Req::val('lang_code', $fs->prefs['lang_code']), true); ?>
				</select>
			</div>
		</li>
		<li class="wide-element">
			<label for="intromesg"><?php echo Filters::noXSS(L('intromessage')); ?></label>
				<div class="valuewrap">
					<div class="richtextwrap">
					<?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
						<div class="hide preview" id="preview"></div>
						<button tabindex="8" type="button" onclick="showPreview('intromesg', '<?php echo Filters::noJsXSS($baseurl); ?>', 'preview')"><?php echo Filters::noXSS(L('preview')); ?></button>
					<?php endif; ?>
					<?php echo TextFormatter::textarea('intro_message', 9, 70, array('accesskey' => 'r', 'tabindex' => 8, 'id' => 'intromesg', 'class' => 'richtext txta-large'), Req::val('intro_message', $proj->prefs['intro_message'])); ?>
					<?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
					<?php endif; ?>
					</div>
				</div>
		</li>
		<li>
			<label for="othersview"><?php echo Filters::noXSS(L('othersview')); ?></label>
			<div class="valuewrap">
			<?php echo tpl_checkbox('others_view', Req::val('others_view', 0), 'othersview'); ?>
			</div>
		</li>
		<li>
			<label for="othersviewroadmap"><?php echo Filters::noXSS(L('othersviewroadmap')); ?></label>
			<div class="valuewrap">
			<?php echo tpl_checkbox('others_viewroadmap', Req::val('others_viewroadmap', 0), 'othersviewroadmap'); ?>
			</div>
		</li>
		<li>
			<label for="anonopen"><?php echo Filters::noXSS(L('allowanonopentask')); ?></label>
			<div class="valuewrap">
			<?php echo tpl_checkbox('anon_open', Req::val('anon_open'), 'anonopen'); ?>
			</div>
		</li>
		<li>
			<label for="disp_intro"><?php echo Filters::noXSS(L('dispintro')); ?></label>
			<div class="valuewrap">
			<?php echo tpl_checkbox('disp_intro', Req::val('disp_intro', 0), 'disp_intro'); ?>
			</div>
		</li>
	</ul>

	<div class="buttons">
		<input type="hidden" name="action" value="admin.newproject" />
		<input type="hidden" name="area" value="newproject" />
		<button type="submit" class="positive"><?php echo Filters::noXSS(L('createthisproject')); ?></button>
	</div>
	</form>
</div>
