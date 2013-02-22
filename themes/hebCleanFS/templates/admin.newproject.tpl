<div id="toolbox">
  <h3>{L('createnewproject')}</h3>
  <form action="{CreateURL('admin', 'newproject')}" method="post">
    <div>
      <input type="hidden" name="action" value="admin.newproject" />
      <input type="hidden" name="area" value="newproject" />
    </div>
    <ul class="form_elements">
      <li>
        <label for="projecttitle">{L('projecttitle')}</label>
        <input id="projecttitle" name="project_title" value="{Req::val('project_title')}" type="text" class="required text" size="40" maxlength="100" />
      </li>
      
      <li>
        <label for="themestyle">{L('themestyle')}</label>
        <select id="themestyle" name="theme_style">
          {!tpl_options(Flyspray::listThemes(), Req::val('theme_style', $proj->prefs['theme_style']), true)}
        </select>
      </li>
      
      <li>
        <label for="langcode">{L('language')}</label>
        <select id="langcode" name="lang_code">
          {!tpl_options(Flyspray::listLangs(), Req::val('lang_code', $fs->prefs['lang_code']), true)}
        </select>
      </li>
      
      <li>
        <label for="intromesg">{L('intromessage')}</label>
        <?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
          <div class="hide preview" id="preview"></div>
        <?php endif; ?>
        {!TextFormatter::textarea('intro_message', 8, 70, array('accesskey' => 'r', 'tabindex' => 8, 'id' => 'intromesg'), Req::val('intro_message', $proj->prefs['intro_message']))}
        <br />
        <?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
          <button tabindex="9" type="button" onclick="showPreview('intromesg', '{#$baseurl}', 'preview')">{L('preview')}</button>
        <?php endif; ?>
      </li>
      
      <li>
        <label for="othersview">{L('othersview')}</label>
        {!tpl_checkbox('others_view', Req::val('others_view', Req::val('action') != 'admin.newproject'), 'othersview')}
      </li>
      
      <li>
        <label for="anonopen">{L('allowanonopentask')}</label>
        {!tpl_checkbox('anon_open', Req::val('anon_open'), 'anonopen')}
      </li>
      
      <li>
        <td class="buttons" colspan="2"><button type="submit">{L('createthisproject')}</button></td>
      </li>
    </ul>
  </form>
</div>
