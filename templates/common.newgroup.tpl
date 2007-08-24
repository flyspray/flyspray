<fieldset><legend>{L('createnewgroup')}</legend>

<form action="{CreateUrl($do, 'newgroup', $proj->id)}" method="post" id="newgroup">
  <table class="box">
    <tr>
      <td><label for="groupname">{L('groupname')}</label></td>
      <td><input id="groupname" class="required text" type="text" value="{Req::val('group_name')}" name="group_name" size="20" maxlength="20" /></td>
    </tr>
    <tr>
      <td><label for="groupdesc">{L('description')}</label></td>
      <td><input id="groupdesc" class="text" type="text" value="{Req::val('group_desc')}" name="group_desc" size="50" maxlength="100" /></td>
    </tr>
    <tr>
      <td><label for="manageproject">{L('projectmanager')}</label></td>
      <td>{!tpl_checkbox('manage_project', Req::val('manage_project'), 'manageproject')}</td>
    </tr>
    <tr>
      <td><label for="viewtasks">{L('viewtasks')}</label></td>
      <td>{!tpl_checkbox('view_tasks', Req::val('view_tasks', Req::val('action') != 'newgroup.newgroup'), 'viewtasks')}</td>
    </tr>
    <tr>
      <td><label for="opennewtasks">{L('opennewtasks')}</label></td>
      <td>{!tpl_checkbox('open_new_tasks', Req::val('open_new_tasks', Req::val('action') != 'newgroup.newgroup'), 'opennewtasks')}</td>
    </tr>
    <tr>
      <td><label for="modifyowntasks">{L('modifyowntasks')}</label></td>
      <td>{!tpl_checkbox('modify_own_tasks', Req::val('modify_own_tasks'), 'modifyowntasks')}</td>
    </tr>
    <tr>
      <td><label for="modifyalltasks">{L('modifyalltasks')}</label></td>
      <td>{!tpl_checkbox('modify_all_tasks', Req::val('modify_all_tasks'), 'modifyalltasks')}</td>
    </tr>
    <tr>
      <td><label for="viewcomments">{L('viewcomments')}</label></td>
      <td>{!tpl_checkbox('view_comments', Req::val('view_comments', Req::val('action') != 'newgroup.newgroup'), 'viewcomments')}</td>
    </tr>
    <tr>
      <td><label for="addcomments">{L('addcomments')}</label></td>
      <td>{!tpl_checkbox('add_comments', Req::val('add_comments'), 'addcomments')}</td>
    </tr>
    <tr>
      <td><label for="editowncomments">{L('editowncomments')}</label></td>
      <td>{!tpl_checkbox('edit_own_comments', Req::val('edit_own_comments'), 'editowncomments')}</td>
    </tr>
    <tr>
      <td><label for="editcomments">{L('editcomments')}</label></td>
      <td>{!tpl_checkbox('edit_comments', Req::val('edit_comments'), 'editcomments')}</td>
    </tr>
    <tr>
      <td><label for="deletecomments">{L('deletecomments')}</label></td>
      <td>{!tpl_checkbox('delete_comments', Req::val('delete_comments'), 'deletecomments')}</td>
    </tr>
    <tr>
      <td><label for="createattachments">{L('createattachments')}</label></td>
      <td>{!tpl_checkbox('create_attachments', Req::val('create_attachments'), 'createattachments')}</td>
    </tr>
    <tr>
      <td><label for="deleteattachments">{L('deleteattachments')}</label></td>
      <td>{!tpl_checkbox('delete_attachments', Req::val('delete_attachments'), 'deleteattachments')}</td>
    </tr>
    <tr>
      <td><label for="viewhistory">{L('viewhistory')}</label></td>
      <td>{!tpl_checkbox('view_history', Req::val('view_history', Req::val('action') != 'newgroup.newgroup'), 'viewhistory')}</td>
    </tr>
    <tr>
      <td><label for="closeowntasks">{L('closeowntasks')}</label></td>
      <td>{!tpl_checkbox('close_own_tasks', Req::val('close_own_tasks'), 'closeowntasks')}</td>
    </tr>
    <tr>
      <td><label for="closeothertasks">{L('closeothertasks')}</label></td>
      <td>{!tpl_checkbox('close_other_tasks', Req::val('close_other_tasks'), 'closeothertasks')}</td>
    </tr>
    <tr>
      <td><label for="assigntoself">{L('assigntoself')}</label></td>
      <td>{!tpl_checkbox('assign_to_self', Req::val('assign_to_self'), 'assigntoself')}</td>
    </tr>
    <tr>
      <td><label for="assignotherstoself">{L('assignotherstoself')}</label></td>
      <td>{!tpl_checkbox('assign_others_to_self', Req::val('assign_others_to_self'), 'assignotherstoself')}</td>
    </tr>
    <tr>
      <td><label for="addtoassignees">{L('addtoassignees')}</label></td>
      <td>{!tpl_checkbox('add_to_assignees', Req::val('add_to_assignees'), 'addtoassignees')}</td>
    </tr>
    <tr>
      <td><label for="viewreports">{L('viewreports')}</label></td>
      <td>{!tpl_checkbox('view_reports', Req::val('view_reports', Req::val('action') != 'newgroup.newgroup'), 'viewreports')}</td>
    </tr>
    <tr>
      <td><label for="canvote">{L('canvote')}</label></td>
      <td>{!tpl_checkbox('add_votes', Req::val('add_votes', Req::val('action') != 'newgroup.newgroup'), 'canvote')}</td>
    </tr>
    <tr>
      <td><label for="editassignments">{L('editassignments')}</label></td>
      <td>{!tpl_checkbox('edit_assignments', Req::val('edit_assignments'), 'editassignments')}</td>
    </tr>
    <tr>
      <td><label for="show_as_assignees">{L('showasassignees')}</label></td>
      <td>{!tpl_checkbox('show_as_assignees', Req::val('show_as_assignees'), 'show_as_assignees')}</td>
    </tr>
    <?php if (!$proj->id): ?>
    <tr>
      <td><label for="groupopen">{L('groupenabled')}</label></td>
      <td>{!tpl_checkbox('group_open', Req::val('group_open', Req::val('action') != 'newgroup.newgroup'), 'groupopen')}</td>
    </tr>
    <?php endif; ?>
    <tr>
      <td colspan="2" class="buttons">
        <input type="hidden" name="action" value="<?php if ($proj->id): ?>pm<?php else: ?>admin<?php endif; ?>.newgroup" />
        <input type="hidden" name="project_id" value="{Req::val('project')}" />
        <button type="submit">{L('addthisgroup')}</button>
      </td>
    </tr>
  </table>
</form>
</fieldset>
