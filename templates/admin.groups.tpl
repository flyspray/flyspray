<div id="toolbox">
  <h3>{L('admintoolboxlong')} :: {L('usersandgroups')}</h3>
  <fieldset class="box">
    <legend>{L('usersandgroups')}</legend>
    <p>
      <img src="{$this->get_image('personal')}" alt="" class="middle" /> <a href="{CreateURL('admin', 'newuser', $proj->id)}">{L('newuser')}</a>
    </p>
    <p>
      <img src="{$this->get_image('kuser')}" alt="" class="middle" /> <a href="{CreateURL('admin', 'newgroup', $proj->id)}">{L('newgroup')}</a>
    </p>

    <div class="groupedit">
    <form action="{$baseurl}index.php" method="get">
        <div>
            <label for="selectgroup">{L('editgroup')}</label>
            <select name="id" id="selectgroup">{!tpl_options(Flyspray::ListGroups())}</select>
            <button type="submit">{L('edit')}</button>
            <input type="hidden" name="do" value="admin" />
            <input type="hidden" name="area" value="editgroup" />
        </div>
    </form>
    
    <form action="{$baseurl}index.php" method="get">
        <div>
            <label for="edit_user">{L('edituser')}</label>
            {!tpl_userselect('user_name', '', 'edit_user')}       
            <button type="submit">{L('edit')}</button>

            <input type="hidden" name="do" value="admin" />
            <input type="hidden" name="area" value="users" />
        </div>
    </form>
    </div>
  </fieldset>
</div>
