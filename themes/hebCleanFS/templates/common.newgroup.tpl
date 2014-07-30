<form action="<?php echo Filters::noXSS(CreateUrl($do, 'newgroup', $proj->id)); ?>" method="post" id="newgroup">
    
    <ul class="form_elements">
      <li class="required">
        <label for="groupname"><?php echo Filters::noXSS(L('groupname')); ?> *</label>
        <input id="groupname" class="required text" type="text" value="<?php echo Filters::noXSS(Req::val('group_name')); ?>" name="group_name" size="20" maxlength="20" />
      </li>   
      
      <li>   
        <label for="groupdesc"><?php echo Filters::noXSS(L('description')); ?></label>
        <input id="groupdesc" class="text" type="text" value="<?php echo Filters::noXSS(Req::val('group_desc')); ?>" name="group_desc" size="50" maxlength="100" />
      </li>   
      
      <li>   
        <label for="manageproject"><?php echo Filters::noXSS(L('projectmanager')); ?></label>
        <?php echo tpl_checkbox('manage_project', Req::val('manage_project'), 'manageproject'); ?>

      </li>   
      
      <li>   
        <label for="viewtasks"><?php echo Filters::noXSS(L('viewtasks')); ?></label>
        <?php echo tpl_checkbox('view_tasks', Req::val('view_tasks', Req::val('action') != 'newgroup.newgroup'), 'viewtasks'); ?>

      </li>   
      
      <li>   
        <label for="opennewtasks"><?php echo Filters::noXSS(L('opennewtasks')); ?></label>
        <?php echo tpl_checkbox('open_new_tasks', Req::val('open_new_tasks', Req::val('action') != 'newgroup.newgroup'), 'opennewtasks'); ?>

      </li>   
      
      <li>   
        <label for="modifyowntasks"><?php echo Filters::noXSS(L('modifyowntasks')); ?></label>
        <?php echo tpl_checkbox('modify_own_tasks', Req::val('modify_own_tasks'), 'modifyowntasks'); ?>

      </li>   
      
      <li>   
        <label for="modifyalltasks"><?php echo Filters::noXSS(L('modifyalltasks')); ?></label>
        <?php echo tpl_checkbox('modify_all_tasks', Req::val('modify_all_tasks'), 'modifyalltasks'); ?>

      </li>   
      
      <li>   
        <label for="viewcomments"><?php echo Filters::noXSS(L('viewcomments')); ?></label>
        <?php echo tpl_checkbox('view_comments', Req::val('view_comments', Req::val('action') != 'newgroup.newgroup'), 'viewcomments'); ?>

      </li>   
      
      <li>   
        <label for="addcomments"><?php echo Filters::noXSS(L('addcomments')); ?></label>
        <?php echo tpl_checkbox('add_comments', Req::val('add_comments'), 'addcomments'); ?>

      </li>   
      
      <li>   
        <label for="editowncomments"><?php echo Filters::noXSS(L('editowncomments')); ?></label>
        <?php echo tpl_checkbox('edit_own_comments', Req::val('edit_own_comments'), 'editowncomments'); ?>

      </li>   
      
      <li>   
        <label for="editcomments"><?php echo Filters::noXSS(L('editcomments')); ?></label>
        <?php echo tpl_checkbox('edit_comments', Req::val('edit_comments'), 'editcomments'); ?>

      </li>   
      
      <li>   
        <label for="deletecomments"><?php echo Filters::noXSS(L('deletecomments')); ?></label>
        <?php echo tpl_checkbox('delete_comments', Req::val('delete_comments'), 'deletecomments'); ?>

      </li>   
      
      <li>   
        <label for="createattachments"><?php echo Filters::noXSS(L('createattachments')); ?></label>
        <?php echo tpl_checkbox('create_attachments', Req::val('create_attachments'), 'createattachments'); ?>

      </li>   
      
      <li>   
        <label for="deleteattachments"><?php echo Filters::noXSS(L('deleteattachments')); ?></label>
        <?php echo tpl_checkbox('delete_attachments', Req::val('delete_attachments'), 'deleteattachments'); ?>

      </li>   
      
      <li>   
        <label for="viewhistory"><?php echo Filters::noXSS(L('viewhistory')); ?></label>
        <?php echo tpl_checkbox('view_history', Req::val('view_history', Req::val('action') != 'newgroup.newgroup'), 'viewhistory'); ?>

      </li>   
      
      <li>   
        <label for="closeowntasks"><?php echo Filters::noXSS(L('closeowntasks')); ?></label>
        <?php echo tpl_checkbox('close_own_tasks', Req::val('close_own_tasks'), 'closeowntasks'); ?>

      </li>   
      
      <li>   
        <label for="closeothertasks"><?php echo Filters::noXSS(L('closeothertasks')); ?></label>
        <?php echo tpl_checkbox('close_other_tasks', Req::val('close_other_tasks'), 'closeothertasks'); ?>

      </li>   
      
      <li>   
        <label for="assigntoself"><?php echo Filters::noXSS(L('assigntoself')); ?></label>
        <?php echo tpl_checkbox('assign_to_self', Req::val('assign_to_self'), 'assigntoself'); ?>

      </li>   
      
      <li>   
        <label for="assignotherstoself"><?php echo Filters::noXSS(L('assignotherstoself')); ?></label>
        <?php echo tpl_checkbox('assign_others_to_self', Req::val('assign_others_to_self'), 'assignotherstoself'); ?>

      </li>   
      
      <li>   
        <label for="addtoassignees"><?php echo Filters::noXSS(L('addtoassignees')); ?></label>
        <?php echo tpl_checkbox('add_to_assignees', Req::val('add_to_assignees'), 'addtoassignees'); ?>

      </li>   
      
      <li>   
        <label for="viewreports"><?php echo Filters::noXSS(L('viewreports')); ?></label>
        <?php echo tpl_checkbox('view_reports', Req::val('view_reports', Req::val('action') != 'newgroup.newgroup'), 'viewreports'); ?>

      </li>   
      
      <li>   
        <label for="canvote"><?php echo Filters::noXSS(L('canvote')); ?></label>
        <?php echo tpl_checkbox('add_votes', Req::val('add_votes', Req::val('action') != 'newgroup.newgroup'), 'canvote'); ?>

      </li>   
      
      <li>   
        <label for="editassignments"><?php echo Filters::noXSS(L('editassignments')); ?></label>
        <?php echo tpl_checkbox('edit_assignments', Req::val('edit_assignments'), 'editassignments'); ?>

      </li>   
      
      <li>   
        <label for="show_as_assignees"><?php echo Filters::noXSS(L('showasassignees')); ?></label>
        <?php echo tpl_checkbox('show_as_assignees', Req::val('show_as_assignees'), 'show_as_assignees'); ?>

      </li>   
      
      <?php if (!$proj->id): ?>
      
      <li>   
        <label for="groupopen"><?php echo Filters::noXSS(L('groupenabled')); ?></label>
        <?php echo tpl_checkbox('group_open', Req::val('group_open', Req::val('action') != 'newgroup.newgroup'), 'groupopen'); ?>

      </li>   
      
      <?php endif; ?>
    </ul>      
    
      <input type="hidden" name="action" value="<?php if ($proj->id): ?>pm<?php else: ?>admin<?php endif; ?>.newgroup" />
      <input type="hidden" name="project_id" value="<?php echo Filters::noXSS(Req::val('project')); ?>" />
      <button type="submit"><?php echo Filters::noXSS(L('addthisgroup')); ?></button>
    
</form>
