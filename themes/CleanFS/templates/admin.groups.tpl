<div id="toolbox">
  <h3><?php echo Filters::noXSS(L('usersandgroups')); ?></h3>
  <p>
    <!--<img src="<?php echo Filters::noXSS($this->get_image('personal')); ?>" alt="" class="middle" /> --><a href="<?php echo Filters::noXSS(CreateURL('admin', 'newuser', $proj->id)); ?>"><?php echo Filters::noXSS(L('newuser')); ?></a>
  </p>
  <p>
    <!--<img src="<?php echo Filters::noXSS($this->get_image('personal')); ?>" alt="" class="middle" /> --><a href="<?php echo Filters::noXSS(CreateURL('admin', 'newuserbulk', $proj->id)); ?>"><?php echo Filters::noXSS(L('newuserbulk')); ?></a>
  </p>
  <p>
    <!--<img src="<?php echo Filters::noXSS($this->get_image('kuser')); ?>" alt="" class="middle" />--> <a href="<?php echo Filters::noXSS(CreateURL('admin', 'newgroup', $proj->id)); ?>"><?php echo Filters::noXSS(L('newgroup')); ?></a>
  </p>
  <p>
    <!--<img src="<?php echo Filters::noXSS($this->get_image('personal')); ?>" alt="" class="middle" /> --><a href="<?php echo Filters::noXSS(CreateURL('admin', 'editallusers', $proj->id)); ?>"><?php echo Filters::noXSS(L('editallusers')); ?></a>
  </p>
  
  <div class="groupedit">
    <form action="<?php echo Filters::noXSS($baseurl); ?>index.php" method="get">
        <ul class="form_elements">
          <li>
            <label for="selectgroup"><?php echo Filters::noXSS(L('editgroup')); ?></label>
            <select name="id" id="selectgroup"><?php echo tpl_options(Flyspray::ListGroups()); ?></select>
            <button type="submit"><?php echo Filters::noXSS(L('edit')); ?></button>
            <input type="hidden" name="do" value="admin" />
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
    
            <input type="hidden" name="do" value="admin" />
            <input type="hidden" name="area" value="users" />
          </li>
        </ul>
    </form> 
  </div>
</div>
