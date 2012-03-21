<form action="{CreateUrl($do, 'newgroup', $proj->id)}" method="post" id="newgroup">
    
    <ul class="form_elements">
      <li class="required">
        <label for="groupname">{L('groupname')} *</label>
        <input id="groupname" class="required text" type="text" value="{Req::val('group_name')}" name="group_name" size="20" maxlength="20" />
      </li>   
      
      <li>   
        <label for="groupdesc">{L('description')}</label>
        <input id="groupdesc" class="text" type="text" value="{Req::val('group_desc')}" name="group_desc" size="50" maxlength="100" />
      </li>   
      
      <li>   
        <label for="manageproject">{L('projectmanager')}</label>
        {!tpl_checkbox('manage_project', Req::val('manage_project'), 'manageproject')}
      </li>   
      
      <li>   
        <label for="viewtasks">{L('viewtasks')}</label>
        {!tpl_checkbox('view_tasks', Req::val('view_tasks', Req::val('action') != 'newgroup.newgroup'), 'viewtasks')}
      </li>   
      
      <li>   
        <label for="opennewtasks">{L('opennewtasks')}</label>
        {!tpl_checkbox('open_new_tasks', Req::val('open_new_tasks', Req::val('action') != 'newgroup.newgroup'), 'opennewtasks')}
      </li>   
      
      <li>   
        <label for="modifyowntasks">{L('modifyowntasks')}</label>
        {!tpl_checkbox('modify_own_tasks', Req::val('modify_own_tasks'), 'modifyowntasks')}
      </li>   
      
      <li>   
        <label for="modifyalltasks">{L('modifyalltasks')}</label>
        {!tpl_checkbox('modify_all_tasks', Req::val('modify_all_tasks'), 'modifyalltasks')}
      </li>   
      
      <li>   
        <label for="viewcomments">{L('viewcomments')}</label>
        {!tpl_checkbox('view_comments', Req::val('view_comments', Req::val('action') != 'newgroup.newgroup'), 'viewcomments')}
      </li>   
      
      <li>   
        <label for="addcomments">{L('addcomments')}</label>
        {!tpl_checkbox('add_comments', Req::val('add_comments'), 'addcomments')}
      </li>   
      
      <li>   
        <label for="editowncomments">{L('editowncomments')}</label>
        {!tpl_checkbox('edit_own_comments', Req::val('edit_own_comments'), 'editowncomments')}
      </li>   
      
      <li>   
        <label for="editcomments">{L('editcomments')}</label>
        {!tpl_checkbox('edit_comments', Req::val('edit_comments'), 'editcomments')}
      </li>   
      
      <li>   
        <label for="deletecomments">{L('deletecomments')}</label>
        {!tpl_checkbox('delete_comments', Req::val('delete_comments'), 'deletecomments')}
      </li>   
      
      <li>   
        <label for="createattachments">{L('createattachments')}</label>
        {!tpl_checkbox('create_attachments', Req::val('create_attachments'), 'createattachments')}
      </li>   
      
      <li>   
        <label for="deleteattachments">{L('deleteattachments')}</label>
        {!tpl_checkbox('delete_attachments', Req::val('delete_attachments'), 'deleteattachments')}
      </li>   
      
      <li>   
        <label for="viewhistory">{L('viewhistory')}</label>
        {!tpl_checkbox('view_history', Req::val('view_history', Req::val('action') != 'newgroup.newgroup'), 'viewhistory')}
      </li>   
      
      <li>   
        <label for="closeowntasks">{L('closeowntasks')}</label>
        {!tpl_checkbox('close_own_tasks', Req::val('close_own_tasks'), 'closeowntasks')}
      </li>   
      
      <li>   
        <label for="closeothertasks">{L('closeothertasks')}</label>
        {!tpl_checkbox('close_other_tasks', Req::val('close_other_tasks'), 'closeothertasks')}
      </li>   
      
      <li>   
        <label for="assigntoself">{L('assigntoself')}</label>
        {!tpl_checkbox('assign_to_self', Req::val('assign_to_self'), 'assigntoself')}
      </li>   
      
      <li>   
        <label for="assignotherstoself">{L('assignotherstoself')}</label>
        {!tpl_checkbox('assign_others_to_self', Req::val('assign_others_to_self'), 'assignotherstoself')}
      </li>   
      
      <li>   
        <label for="addtoassignees">{L('addtoassignees')}</label>
        {!tpl_checkbox('add_to_assignees', Req::val('add_to_assignees'), 'addtoassignees')}
      </li>   
      
      <li>   
        <label for="viewreports">{L('viewreports')}</label>
        {!tpl_checkbox('view_reports', Req::val('view_reports', Req::val('action') != 'newgroup.newgroup'), 'viewreports')}
      </li>   
      
      <li>   
        <label for="canvote">{L('canvote')}</label>
        {!tpl_checkbox('add_votes', Req::val('add_votes', Req::val('action') != 'newgroup.newgroup'), 'canvote')}
      </li>   
      
      <li>   
        <label for="editassignments">{L('editassignments')}</label>
        {!tpl_checkbox('edit_assignments', Req::val('edit_assignments'), 'editassignments')}
      </li>   
      
      <li>   
        <label for="show_as_assignees">{L('showasassignees')}</label>
        {!tpl_checkbox('show_as_assignees', Req::val('show_as_assignees'), 'show_as_assignees')}
      </li>   
      
      <?php if (!$proj->id): ?>
      
      <li>   
        <label for="groupopen">{L('groupenabled')}</label>
        {!tpl_checkbox('group_open', Req::val('group_open', Req::val('action') != 'newgroup.newgroup'), 'groupopen')}
      </li>   
      
      <?php endif; ?>
    </ul>      
    
      <input type="hidden" name="action" value="<?php if ($proj->id): ?>pm<?php else: ?>admin<?php endif; ?>.newgroup" />
      <input type="hidden" name="project_id" value="{Req::val('project')}" />
      <button type="submit">{L('addthisgroup')}</button>
    
</form>
