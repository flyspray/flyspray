<div class="box">
	<h2><?php echo Filters::noXSS(L('register')); ?></h2>

	<form action="index.php?do=oauth" method="post" id="registernewuser">
		<ul class="form_elements">
			<li class="required">
				<label for="username"><?php echo Filters::noXSS(L('username')); ?></label>
				<div class="valuewrap">
					<input class="text" value="<?php echo Filters::noXSS($username); ?>" id="user_name" name="username" type="text" size="20" maxlength="32" class="fi-medium" />
					<span class="note"><?php echo Filters::noXSS(L('validusername')); ?></span>
					<span class="errormessage"><?php echo Filters::noXSS(L('usernametaken')); ?></span>
				</div>
			</li>
		</ul>

		<div class="buttons">
			<button type="submit" name="buSubmit" id="buSubmit"><?php echo Filters::noXSS(L('register')); ?></button>
		</div>
	</form>
</div>
