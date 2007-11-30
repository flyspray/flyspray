<div id="toolbox">
  <h3>{L('admintoolboxlong')} :: {L('createnewproject')}</h3>
  <fieldset class="box">
    <legend>{L('newproject')}</legend>
    <form action="{CreateURL('admin', 'newproject')}" method="post">
      <div>
        <input type="hidden" name="action" value="admin.newproject" />
        <input type="hidden" name="area" value="newproject" />
      </div>
      <table class="box">
        <tr>
          <td><label for="projecttitle">{L('projecttitle')}</label></td>
          <td><input id="projecttitle" name="project_title" value="{Req::val('project_title')}" type="text" class="required text" size="40" maxlength="100" /></td>
        </tr>
        <tr>
          <td><label for="themestyle">{L('themestyle')}</label></td>
          <td>
            <select id="themestyle" name="theme_style">
              {!tpl_options(Flyspray::listThemes(), Req::val('theme_style', $proj->prefs['theme_style']), true)}
            </select>
          </td>
        </tr>
        <tr>
          <td><label for="langcode">{L('language')}</label></td>
          <td>
            <select id="langcode" name="lang_code">
              {!tpl_options(Flyspray::listLangs(), Req::val('lang_code', $fs->prefs['lang_code']), true)}
            </select>
          </td>
        </tr>
        <tr>
          <td><label for="intromesg">{L('intromessage')}</label></td>
          <td>
            <?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
            <div class="hide preview" id="preview"></div>
            <?php endif; ?>
            {!TextFormatter::textarea('intro_message', 8, 70, array('accesskey' => 'r', 'tabindex' => 8, 'id' => 'intromesg'), Req::val('intro_message', $proj->prefs['intro_message']))}
            <br />
            <?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
            <button tabindex="9" type="button" onclick="showPreview('intromesg', '{#$baseurl}', 'preview')">{L('preview')}</button>
            <?php endif; ?>
          </td>
        </tr>
        <tr>
          <td><label for="othersview">{L('othersview')}</label></td>
          <td>{!tpl_checkbox('others_view', Req::val('others_view', Req::val('action') != 'admin.newproject'), 'othersview')}</td>
        </tr>
        <tr>
          <td><label for="anonopen">{L('allowanonopentask')}</label></td>
          <td>{!tpl_checkbox('anon_open', Req::val('anon_open'), 'anonopen')}</td>
        </tr>
        <tr>
          <td class="buttons" colspan="2"><button type="submit">{L('createthisproject')}</button></td>
        </tr>
      </table>
    </form>
  </fieldset>
</div>
