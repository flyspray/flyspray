<h2><?php echo Filters::noXSS(L('lostpw')); ?></h2>

<div class="box">
	<p><?php echo Filters::noXSS(L('lostpwexplain')); ?></p>

	<?php echo tpl_form(Filters::noXSS(createUrl('lostpw'))); ?>
	<ul class="form_elements">
		<li class="required">
			<label for="user_name"><?php echo Filters::noXSS(L('username')); ?></label>
			<div class="valuewrap">
				<input class="text fi-medium" type="text" value="<?php echo Filters::noXSS(Req::val('user_name')); ?>" name="user_name" id="user_name" size="20" maxlength="32" />
			</div>
		</li>
	</ul>

	<div class="buttons">
		<button type="submit"><?php echo Filters::noXSS(L('sendlink')); ?></button>
		<input type="hidden" name="action" value="lostpw.sendmagic" />
	</div>
	</form>
</div>
