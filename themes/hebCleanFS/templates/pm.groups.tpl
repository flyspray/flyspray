<div id="toolbox">
  <h3><?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?php echo Filters::noXSS(L('groupmanage')); ?></h3>
  <?php if ($user->perms('is_admin')): ?>
  <p>
    <!--<img src="<?php echo Filters::noXSS($this->get_image('personal')); ?>" alt="" class="middle" />-->
    <a href="<?php echo Filters::noXSS(CreateURL('admin', 'newuser', $proj->id)); ?>"><?php echo Filters::noXSS(L('newuser')); ?></a>
  </p>
  <?php endif; ?>
  <p>
    <!--<img src="<?php echo Filters::noXSS($this->get_image('kuser')); ?>" alt="" class="middle" />-->
    <a href="<?php echo Filters::noXSS(CreateURL('pm', 'newgroup', $proj->id)); ?>"><?php echo Filters::noXSS(L('newgroup')); ?></a>
  </p>
  
  <div class="groupedit">
  <form action="<?php echo Filters::noXSS($baseurl); ?>index.php" method="get">
    <ul class="form_elements">
      <li>
        <label for="selectgroup"><?php echo Filters::noXSS(L('editgroup')); ?></label>
        <select name="id" id="selectgroup"><?php echo tpl_options(Flyspray::ListGroups($proj->id)); ?></select>
        <button type="submit"><?php echo Filters::noXSS(L('edit')); ?></button>
        <input type="hidden" name="do" value="pm" />
        <input type="hidden" name="area" value="editgroup" />
      </li>
    </ul>
  </form>
  
  <form action="<?php echo Filters::noXSS($baseurl); ?>index.php" method="get">
    <ul class="form_elements">
      <li>
        <label for="edit_user"><?php echo Filters::noXSS(L('edituser')); ?></label>
        <?php echo tpl_userselect('user_name', '', 'edit_user'); ?>               
        <button type="submit"><?php echo Filters::noXSS(L('edit')); ?></button>
  
        <input type="hidden" name="do" value="user" />
      </li>
    </ul>
  </form>
  </div>
</div>
