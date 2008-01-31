<div id="toolbox">
  <h3>{L('pmtoolbox')} :: {$proj->prefs['project_title']} : {L('groupmanage')}</h3>
  <fieldset class="box">
    <legend>{L('usersandgroups')}</legend>
    <?php if ($user->perms('is_admin')): ?>
    <p>
      <img src="{$this->get_image('personal')}" alt="" class="middle" /> <a href="{CreateURL('admin', 'newuser', $proj->id)}">{L('newuser')}</a>
    </p>
    <?php endif; ?>
    <p>
      <img src="{$this->get_image('kuser')}" alt="" class="middle" /> <a href="{CreateURL('pm', 'newgroup', $proj->id)}">{L('newgroup')}</a>
    </p>

    <div class="groupedit">
    <form action="{$baseurl}index.php" method="get">
        <div>
            <label for="selectgroup">{L('editgroup')}</label>
            <select name="id" id="selectgroup">{!tpl_options(Flyspray::ListGroups($proj->id))}</select>
            <button type="submit">{L('edit')}</button>
            <input type="hidden" name="do" value="pm" />
            <input type="hidden" name="area" value="editgroup" />
        </div>
    </form>
    
    <form action="{$baseurl}index.php" method="get">
        <div>
            <label for="edit_user">{L('edituser')}</label>
            {!tpl_userselect('user_name', '', 'edit_user')}               
            <button type="submit">{L('edit')}</button>

            <input type="hidden" name="do" value="user" />
        </div>
    </form>
    </div>
  </fieldset>
</div>
