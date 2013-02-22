<div id="toolbox">
  <h3>{L('usersandgroups')}</h3>
  <p>
    <!--<img src="{$this->get_image('personal')}" alt="" class="middle" /> --><a href="{CreateURL('admin', 'newuser', $proj->id)}">{L('newuser')}</a>
  </p>
  <p>
    <!--<img src="{$this->get_image('kuser')}" alt="" class="middle" />--> <a href="{CreateURL('admin', 'newgroup', $proj->id)}">{L('newgroup')}</a>
  </p>
  
  <div class="groupedit">
    <form action="{$baseurl}index.php" method="get">
        <ul class="form_elements">
          <li>
            <label for="selectgroup">{L('editgroup')}</label>
            <select name="id" id="selectgroup">{!tpl_options(Flyspray::ListGroups())}</select>
            <button type="submit">{L('edit')}</button>
            <input type="hidden" name="do" value="admin" />
            <input type="hidden" name="area" value="editgroup" />
          </li>
        </ul>
    </form>
    
    <form action="{$baseurl}index.php" method="get">
        <ul class="form_elements">
          <li>
            <label for="edit_user">{L('edituser')}</label>
            {!tpl_userselect('user_name', '', 'edit_user')}       
            <button type="submit">{L('edit')}</button>
    
            <input type="hidden" name="do" value="admin" />
            <input type="hidden" name="area" value="users" />
          </li>
        </ul>
    </form> 
  </div>
</div>
