<div id="toolbox">
  <h3>{$proj->prefs['project_title']} : {L('preferences')}</h3>

  <form action="{CreateUrl('pm', 'prefs', $proj->id)}" method="post">
  <ul id="submenu">
   <li><a href="#general">{L('general')}</a></li>
   <li><a href="#lookandfeel">{L('lookandfeel')}</a></li>
   <li><a href="#notifications">{L('notifications')}</a></li>
   <li><a href="#feeds">{L('feeds')}</a></li>
  </ul>

  <div id="general" class="tab">
      <ul class="form_elements wide">
        <li>
          <label for="projecttitle">{L('projecttitle')}</label>
          <input id="projecttitle" name="project_title" class="text" type="text" size="40" maxlength="100"
            value="{Post::val('project_title', $proj->prefs['project_title'])}" />
        </li>

        <li>
          <label for="defaultcatowner">{L('defaultcatowner')}</label>
          {!tpl_userselect('default_cat_owner', Post::val('default_cat_owner', $proj->prefs['default_cat_owner']), 'defaultcatowner')}
        </li>

        <li>
          <label for="langcode">{L('language')}</label>
          <select id="langcode" name="lang_code">
            {!tpl_options(Flyspray::listLangs(), Post::val('lang_code', $proj->prefs['lang_code']), true)}
          </select>
        </li>

        <li>
          <label for="intromesg">{L('intromessage')}</label>
          <?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
          <div class="hide preview" id="preview"></div>
          <?php endif; ?>
          {!TextFormatter::textarea('intro_message', 8, 70, array('accesskey' => 'r', 'tabindex' => 8, 'id' => 'intromesg'), Post::val('intro_message', $proj->prefs['intro_message']))}
          <br />
          <?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
          <button tabindex="9" type="button" onclick="showPreview('intromesg', '{#$baseurl}', 'preview')">{L('preview')}</button>
          <?php endif; ?>
        </li>

        <li>
          <label for="default_task">{L('defaulttask')}</label>
          <?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
          <div class="hide preview" id="preview_taskdesc"></div>
          <?php endif; ?>
          {!TextFormatter::textarea('default_task', 8, 70, array('accesskey' => 'r', 'tabindex' => 8, 'id' => 'default_task'), Post::val('default_task', $proj->prefs['default_task']))}
          <br />
          <?php if (defined('FLYSPRAY_HAS_PREVIEW')): ?>
          <button tabindex="9" type="button" onclick="showPreview('default_task', '{#$baseurl}', 'preview_taskdesc')">{L('preview')}</button>
          <?php endif; ?>
        </li>

        <li>
          <label for="isactive">{L('isactive')}</label>
          {!tpl_checkbox('project_is_active', Post::val('project_is_active', $proj->prefs['project_is_active']), 'isactive')}
        </li>

        <li>
          <label>{!tpl_checkbox('delete_project', null)} {L('deleteproject')}</label>
          <select name="move_to">{!tpl_options(array_merge(array(0 => L('none')), Flyspray::listProjects()), null, false, null, (string) $proj->id)}</select>
        </li>

        <li>
          <label for="othersview">{L('othersview')}</label>
          {!tpl_checkbox('others_view', Post::val('others_view', $proj->prefs['others_view']), 'othersview')}
        </li>

        <li>
          <label for="anon_open">{L('allowanonopentask')}</label>
          {!tpl_checkbox('anon_open', Post::val('anon_open', $proj->prefs['anon_open']), 'anon_open')}
        </li>

        <li>
          <label for="comment_closed">{L('allowclosedcomments')}</label>
          {!tpl_checkbox('comment_closed', Post::val('comment_closed', $proj->prefs['comment_closed']), 'comment_closed')}
        </li>

        <li>
          <label for="auto_assign">{L('autoassign')}</label>
          {!tpl_checkbox('auto_assign', Post::val('auto_assign', $proj->prefs['auto_assign']), 'auto_assign')}
        </li>
      </ul>
    </div>

    <div id="lookandfeel" class="tab">
      <ul class="form_elements wide">
        <li>
          <label for="themestyle">{L('themestyle')}</label>
          <select id="themestyle" name="theme_style">
            {!tpl_options(Flyspray::listThemes(), Post::val('theme_style', $proj->prefs['theme_style']), true)}
          </select>
        </li>

        <li>
          <label for="default_entry">{L('defaultentry')}</label>
          <select id="default_entry" name="default_entry">
            {!tpl_options(array('index' => L('tasklist'), 'toplevel' => L('toplevel'), 'roadmap' => L('roadmap')), Post::val('default_entry', $proj->prefs['default_entry']))}
          </select>
        </li>
  
        <li>
          <label>{L('visiblecolumns')}</label>
          <?php // Set the selectable column names
          $columnnames = array('id', 'tasktype', 'category', 'severity',
          'priority', 'summary', 'dateopened', 'status', 'openedby', 'private',
          'assignedto', 'lastedit', 'reportedin', 'dueversion', 'duedate',
          'comments', 'attachments', 'progress', 'dateclosed', 'os', 'votes');
          $selectedcolumns = explode(' ', Post::val('visible_columns', $proj->prefs['visible_columns']));
          ?>
          {!tpl_double_select('visible_columns', $columnnames, $selectedcolumns, true)}
        </li>
      </ul>
    </div>

    <div id="notifications" class="tab">
      <ul class="form_elements">
        <li>
          <label for="notify_subject">{L('notifysubject')}</label>
          <input id="notify_subject" class="text" name="notify_subject" type="text" size="40" value="{Post::val('notify_subject', $proj->prefs['notify_subject'])}" />
          <br /><span class="note">{L('notifysubjectinfo')}</span>
        </li>

        <li>
          <label for="emailaddress">{L('emailaddress')}</label>
          <input id="emailaddress" name="notify_email" class="text" type="text" value="{Post::val('notify_email', $proj->prefs['notify_email'])}" />
        </li>

        <li>
          <label for="jabberid">{L('jabberid')}</label>
          <input id="jabberid" class="text" name="notify_jabber" type="text" value="{Post::val('notify_jabber', $proj->prefs['notify_jabber'])}" />
        </li>

        <li>
          <label for="notify_reply">{L('replyto')}</label>
          <input id="notify_reply" name="notify_reply" class="text" type="text" value="{Post::val('notify_reply', $proj->prefs['notify_reply'])}" />
        </li>

        <li>
          <label for="notify_types">{L('notifytypes')}</label>
          <select id="notify_types" size="10" multiple="multiple" name="notify_types[]">
          {!tpl_options(array(0 => L('none'),
                              NOTIFY_TASK_OPENED     => L('taskopened'),
                              NOTIFY_TASK_CHANGED    => L('pm.taskchanged'),
                              NOTIFY_TASK_CLOSED     => L('taskclosed'),
                              NOTIFY_TASK_REOPENED   => L('pm.taskreopened'),
                              NOTIFY_DEP_ADDED       => L('pm.depadded'),
                              NOTIFY_DEP_REMOVED     => L('pm.depremoved'),
                              NOTIFY_COMMENT_ADDED   => L('commentadded'),
                              NOTIFY_ATT_ADDED       => L('attachmentadded'),
                              NOTIFY_REL_ADDED       => L('relatedadded'),
                              NOTIFY_OWNERSHIP       => L('ownershiptaken'),
                              NOTIFY_PM_REQUEST      => L('pmrequest'),
                              NOTIFY_PM_DENY_REQUEST => L('pmrequestdenied'),
                              NOTIFY_NEW_ASSIGNEE    => L('newassignee'),
                              NOTIFY_REV_DEP         => L('revdepadded'),
                              NOTIFY_REV_DEP_REMOVED => L('revdepaddedremoved'),
                              NOTIFY_ADDED_ASSIGNEES => L('assigneeadded')),
                              Post::val('notify_types', Flyspray::int_explode(' ', $proj->prefs['notify_types'])))}
          </select>
        </li>
      </ul>
    </div>

    <div id="feeds" class="tab">
      <ul class="form_elements">
        <li>
          <label for="feed_description">{L('feeddescription')}</label>
          <input id="feed_description" class="text" name="feed_description" type="text" value="{Post::val('feed_description', $proj->prefs['feed_description'])}" />
        </li>

        <li>
          <label for="feed_img_url">{L('feedimgurl')}</label>
          <input id="feed_img_url" class="text" name="feed_img_url" type="text" value="{Post::val('feed_img_url', $proj->prefs['feed_img_url'])}" />
        </li>
      </ul>
    </div>

    <div class="tbuttons">
      <input type="hidden" name="action" value="pm.updateproject" />
      <input type="hidden" name="project_id" value="{$proj->id}" />
      <button type="submit">{L('saveoptions')}</button>

      <button type="reset">{L('resetoptions')}</button>
    </div>
  </form>

</div>
